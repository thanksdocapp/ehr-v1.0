<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSettlement;
use App\Services\DoctorSettlementService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorSettlementsController extends Controller
{
    public function index(Request $request): View
    {
        $query = DoctorSettlement::query()
            ->with(['doctor.user', 'doctor.departments', 'doctor.department'])
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->integer('doctor_id'));
        }

        $settlements = $query->paginate(20)->withQueryString();

        return view('admin.doctor-settlements.index', compact('settlements'));
    }

    public function show(DoctorSettlement $doctorSettlement): View
    {
        $doctorSettlement->load(['lines.billing', 'doctor.user', 'reviewedByUser']);

        return view('admin.doctor-settlements.show', compact('doctorSettlement'));
    }

    public function exportCsv(DoctorSettlement $doctorSettlement): StreamedResponse
    {
        $doctorSettlement->load(['lines.billing', 'doctor.user', 'reviewedByUser']);

        $filename = $this->settlementExportFilename($doctorSettlement, 'csv');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($doctorSettlement) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Doctor settlement request export']);
            fputcsv($file, ['Settlement ID', $doctorSettlement->id]);
            fputcsv($file, ['Doctor', $doctorSettlement->doctor->user->name ?? 'Doctor #'.$doctorSettlement->doctor_id]);
            fputcsv($file, ['Period start', formatDateUk($doctorSettlement->period_start)]);
            fputcsv($file, ['Period end', formatDateUk($doctorSettlement->period_end)]);
            fputcsv($file, ['Period type', ucfirst($doctorSettlement->period_type)]);
            fputcsv($file, ['Status', $doctorSettlement->status]);
            fputcsv($file, ['Total', number_format((float) $doctorSettlement->total_amount, 2, '.', '')]);
            fputcsv($file, ['Submitted at', $doctorSettlement->submitted_at ? formatDateTimeUkAmPm($doctorSettlement->submitted_at) : '—']);
            fputcsv($file, ['Reviewed at', $doctorSettlement->reviewed_at ? formatDateTimeUkAmPm($doctorSettlement->reviewed_at) : '—']);
            fputcsv($file, ['Reviewed by', $doctorSettlement->reviewedByUser?->name ?? '—']);
            if ($doctorSettlement->notes) {
                fputcsv($file, ['Notes', $doctorSettlement->notes]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Description', 'Billing ID', 'Bill number', 'Amount']);

            foreach ($doctorSettlement->lines as $line) {
                fputcsv($file, [
                    $line->description,
                    $line->billing_id ?? '—',
                    $line->billing?->bill_number ?? '—',
                    number_format((float) $line->amount, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(DoctorSettlement $doctorSettlement): StreamedResponse
    {
        $doctorSettlement->load(['lines.billing', 'doctor.user', 'reviewedByUser']);

        $html = view('admin.doctor-settlements.pdf', compact('doctorSettlement'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = $this->settlementExportFilename($doctorSettlement, 'pdf');

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename);
    }

    private function settlementExportFilename(DoctorSettlement $doctorSettlement, string $extension): string
    {
        $doctorName = $doctorSettlement->doctor->user->name
            ?? trim(($doctorSettlement->doctor->first_name ?? '').' '.($doctorSettlement->doctor->last_name ?? ''));
        $doctorSlug = Str::slug($doctorName, '-');
        if ($doctorSlug === '') {
            $doctorSlug = 'doctor-'.$doctorSettlement->doctor_id;
        }

        $base = sprintf(
            'doctor_settlement_%d_%s_%s_to_%s',
            $doctorSettlement->id,
            $doctorSlug,
            $doctorSettlement->period_start->format('Y-m-d'),
            $doctorSettlement->period_end->format('Y-m-d')
        );

        return substr($base, 0, 180).'.'.ltrim($extension, '.');
    }

    public function recalculate(DoctorSettlement $doctorSettlement, DoctorSettlementService $service): RedirectResponse
    {
        try {
            $service->recalculateLinesFromPayments($doctorSettlement);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.doctor-settlements.show', $doctorSettlement)
            ->with('success', 'Line items and total were rebuilt from completed payments for this period.');
    }

    public function updateStatus(Request $request, DoctorSettlement $doctorSettlement): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,paid',
            'notes' => 'nullable|string|max:5000',
        ]);

        $newStatus = $validated['status'];
        $current = $doctorSettlement->status;

        $allowed = match ($current) {
            DoctorSettlement::STATUS_SUBMITTED => [
                DoctorSettlement::STATUS_APPROVED,
                DoctorSettlement::STATUS_REJECTED,
                DoctorSettlement::STATUS_PAID,
            ],
            DoctorSettlement::STATUS_APPROVED => [
                DoctorSettlement::STATUS_PAID,
                DoctorSettlement::STATUS_REJECTED,
            ],
            default => [],
        };

        if (! in_array($newStatus, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid status transition for this settlement.');
        }

        $updates = [
            'status' => $newStatus,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ];

        if (! empty($validated['notes'])) {
            $updates['notes'] = $doctorSettlement->notes
                ? $doctorSettlement->notes."\n\n[Admin ".now()->format('Y-m-d H:i')."]\n".$validated['notes']
                : $validated['notes'];
        }

        $doctorSettlement->update($updates);

        return redirect()->route('admin.doctor-settlements.show', $doctorSettlement)
            ->with('success', 'Settlement status updated.');
    }
}
