<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorSettlement;
use App\Services\DoctorSettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorSettlementsController extends Controller
{
    public function index(Request $request): View
    {
        $query = DoctorSettlement::query()
            ->with(['doctor.user'])
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
