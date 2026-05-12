<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSettlement;
use App\Services\DoctorSettlementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DoctorSettlementsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $settlements = DoctorSettlement::query()
            ->where('doctor_id', $doctor->id)
            ->with('lines')
            ->orderByDesc('period_end')
            ->paginate(15);

        return view('staff.doctor-settlements.index', compact('settlements', 'doctor'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        return view('staff.doctor-settlements.create', compact('doctor'));
    }

    public function store(Request $request, DoctorSettlementService $service): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'period_type' => 'required|in:weekly,monthly',
            'reference_date' => 'required|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        try {
            $ref = Carbon::parse($validated['reference_date']);
            $service->createDraftSettlement(
                $doctor,
                $validated['period_type'],
                $ref,
                $validated['notes'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('staff.doctor-settlements.index')
            ->with('success', 'Settlement draft created from paid billings in that period.');
    }

    public function show(DoctorSettlement $doctorSettlement): View
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();
        if ((int) $doctorSettlement->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        $doctorSettlement->load(['lines.billing', 'doctor.user']);

        return view('staff.doctor-settlements.show', compact('doctorSettlement', 'doctor'));
    }

    public function submit(DoctorSettlement $doctorSettlement, DoctorSettlementService $service): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();
        if ((int) $doctorSettlement->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        try {
            $service->submitForReview($doctorSettlement);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('staff.doctor-settlements.show', $doctorSettlement)
            ->with('success', 'Settlement submitted to administration for review.');
    }

    public function recalculate(DoctorSettlement $doctorSettlement, DoctorSettlementService $service): RedirectResponse
    {
        $user = Auth::user();
        if ($user->role !== 'doctor') {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();
        if ((int) $doctorSettlement->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        if (! $doctorSettlement->isDraft()) {
            return redirect()->back()->with('error', 'Only draft settlements can be recalculated.');
        }

        try {
            $service->recalculateLinesFromPayments($doctorSettlement);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('staff.doctor-settlements.show', $doctorSettlement)
            ->with('success', 'Line items and total were updated from completed payments in this period.');
    }
}
