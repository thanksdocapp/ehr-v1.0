<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\BookingPaymentsService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingPaymentsController extends Controller
{
    public function index(Request $request, BookingPaymentsService $service): View
    {
        $totalAmount = $service->totalAmountForBookingPaymentRows($request);
        $rows = $service->paginateBookingPaymentRows($request, 30);

        $doctors = Doctor::query()->with('user')->orderBy('last_name')->orderBy('first_name')->get();
        $departments = Department::query()->where('is_active', true)->orderBy('name')->get();

        $bookingPaymentsService = $service;

        return view('admin.booking-payments.index', compact('rows', 'doctors', 'departments', 'totalAmount', 'bookingPaymentsService'));
    }

    public function exportPdf(Request $request, BookingPaymentsService $service): StreamedResponse
    {
        $totalAmount = $service->totalAmountForBookingPaymentRows($request);
        $rows = $service->collectBookingPaymentRows($request);

        $html = view('admin.booking-payments.pdf', [
            'rows' => $rows,
            'bookingPaymentsService' => $service,
            'totalAmount' => $totalAmount,
            'filterSummary' => $this->filterSummaryForExport($request),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = $this->bookingPaymentsExportFilename($request, 'pdf');

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename);
    }

    public function exportCsv(Request $request, BookingPaymentsService $service): StreamedResponse
    {
        $rows = $service->collectBookingPaymentRows($request);

        $filename = $this->bookingPaymentsExportFilename($request, 'csv');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows, $service) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Date',
                'Amount',
                'Method',
                'Source',
                'Invoice / order',
                'Doctor',
                'Clinic (capture)',
                'Booking capture label',
                'Booking capture evidence',
                'Patient',
                'Appointment',
                'Comments',
            ]);

            foreach ($rows as $row) {
                $comments = $service->commentsForRow($row);
                $capture = $service->bookingCaptureForRow($row);
                $sortAt = $row->sortAt();

                fputcsv($file, [
                    $sortAt ? formatDateTimeUkAmPm($sortAt) : '—',
                    number_format($row->amount(), 2, '.', ''),
                    $service->methodLabelForRow($row),
                    $service->labelForRow($row),
                    $service->invoiceLabelForRow($row),
                    $service->doctorNameForRow($row) ?? '—',
                    $capture['clinic_name'] ?? '—',
                    $capture['primary_label'] ?? '—',
                    $capture['evidence_line'] ?? '—',
                    $service->patientNameForRow($row),
                    $service->appointmentSlotLabelForRow($row),
                    $comments !== '' ? $comments : '—',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Safe download filename: booking_payments_{from}_to_{to}[_doctor_{slug}][_clinic_{slug}].{ext}
     */
    private function bookingPaymentsExportFilename(Request $request, string $extension): string
    {
        $from = $request->filled('from') ? $request->string('from') : 'start';
        $to = $request->filled('to') ? $request->string('to') : 'end';
        $segments = ['booking_payments', $from, 'to', $to];

        if ($request->filled('doctor_id')) {
            $doctor = Doctor::with('user')->find($request->integer('doctor_id'));
            $label = $doctor
                ? (string) ($doctor->user->name ?? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? '')))
                : '';
            $slug = Str::slug($label, '-');
            if ($slug === '') {
                $slug = 'id-'.$request->integer('doctor_id');
            }
            $segments[] = 'doctor';
            $segments[] = $slug;
        }

        if ($request->filled('department_id')) {
            $department = Department::find($request->integer('department_id'));
            $label = $department?->name ?? '';
            $slug = Str::slug($label, '-');
            if ($slug === '') {
                $slug = 'id-'.$request->integer('department_id');
            }
            $segments[] = 'clinic';
            $segments[] = $slug;
        }

        $base = implode('_', $segments);
        $base = substr($base, 0, 180);

        return $base.'.'.ltrim($extension, '.');
    }

    private function filterSummaryForExport(Request $request): string
    {
        $parts = [];
        if ($request->filled('doctor_id')) {
            $d = Doctor::with('user')->find($request->integer('doctor_id'));
            $parts[] = 'Doctor: '.($d ? ($d->user->name ?? trim(($d->first_name ?? '').' '.($d->last_name ?? ''))) : '—');
        }
        if ($request->filled('department_id')) {
            $dept = Department::find($request->integer('department_id'));
            $parts[] = 'Clinic: '.($dept?->name ?? '—');
        }
        if ($request->filled('from')) {
            $parts[] = 'From: '.$request->string('from');
        }
        if ($request->filled('to')) {
            $parts[] = 'To: '.$request->string('to');
        }

        return $parts !== [] ? implode(' · ', $parts) : 'All completed payments (no filters)';
    }
}
