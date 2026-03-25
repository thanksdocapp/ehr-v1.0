<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorBookingDiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DoctorBookingDiscountCodesController extends Controller
{
    private function assertTableExists(): ?\Illuminate\Http\RedirectResponse
    {
        if (!Schema::hasTable('doctor_booking_discount_codes')) {
            return redirect()->back()
                ->with('error', 'Doctor booking discount codes require a database migration. Run: php artisan migrate');
        }

        return null;
    }

    /** @return Collection<int, BookingService> */
    private function bookingServicesForDoctor(Doctor $doctor): Collection
    {
        return BookingService::query()
            ->where('is_active', true)
            ->ordered()
            ->get()
            ->filter(fn (BookingService $svc) => $svc->isAvailableForDoctor($doctor->id))
            ->values();
    }

    public function index(Doctor $doctor)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $codes = DoctorBookingDiscountCode::query()
            ->where('doctor_id', $doctor->id)
            ->with(['bookingServices', 'bookingService'])
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        return view('admin.doctor-booking-discount-codes.index', compact('doctor', 'codes'));
    }

    public function create(Doctor $doctor)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $services = $this->bookingServicesForDoctor($doctor);

        return view('admin.doctor-booking-discount-codes.create', compact('doctor', 'services'));
    }

    public function store(Request $request, Doctor $doctor)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $codeNormalized = DoctorBookingDiscountCode::normalizeCode((string) $request->input('code', ''));

        $request->merge([
            'code' => $codeNormalized,
            'max_uses' => $request->filled('max_uses') ? $request->input('max_uses') : null,
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('doctor_booking_discount_codes', 'code')->where('doctor_id', $doctor->id),
            ],
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'booking_service_ids' => 'nullable|array',
            'booking_service_ids.*' => 'integer|exists:booking_services,id',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        }

        $serviceIds = DoctorBookingDiscountCode::normalizeServiceIdList($validated['booking_service_ids'] ?? null);
        foreach ($serviceIds as $sid) {
            $svc = BookingService::find($sid);
            if (!$svc || !$svc->isAvailableForDoctor($doctor->id)) {
                return back()->withErrors([
                    'booking_service_ids' => 'Select only services this doctor offers, or leave all unselected for every service.',
                ])->withInput();
            }
        }

        $code = DoctorBookingDiscountCode::create([
            'doctor_id' => $doctor->id,
            'code' => $codeNormalized,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'booking_service_id' => null,
            'max_uses' => $validated['max_uses'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (Schema::hasTable('doctor_booking_discount_code_services')) {
            $code->replaceRestrictedBookingServices($serviceIds);
        }

        return redirect()->route('admin.doctors.booking-discount-codes.index', $doctor)
            ->with('success', 'Doctor booking discount code created. It applies on this doctor’s public booking link before payment.');
    }

    public function edit(Doctor $doctor, DoctorBookingDiscountCode $doctorBookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $this->authorizeCode($doctorBookingDiscountCode, $doctor);
        $doctorBookingDiscountCode->load('bookingServices');
        $services = $this->bookingServicesForDoctor($doctor);

        return view('admin.doctor-booking-discount-codes.edit', compact('doctor', 'doctorBookingDiscountCode', 'services'));
    }

    public function update(Request $request, Doctor $doctor, DoctorBookingDiscountCode $doctorBookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $this->authorizeCode($doctorBookingDiscountCode, $doctor);

        $codeNormalized = DoctorBookingDiscountCode::normalizeCode((string) $request->input('code', ''));

        $request->merge([
            'code' => $codeNormalized,
            'max_uses' => $request->filled('max_uses') ? $request->input('max_uses') : null,
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('doctor_booking_discount_codes', 'code')
                    ->where('doctor_id', $doctor->id)
                    ->ignore($doctorBookingDiscountCode->id),
            ],
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'booking_service_ids' => 'nullable|array',
            'booking_service_ids.*' => 'integer|exists:booking_services,id',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        }

        $newMax = $validated['max_uses'] ?? null;
        if ($newMax !== null && $newMax < $doctorBookingDiscountCode->uses_count) {
            return back()->withErrors([
                'max_uses' => 'Max uses must be at least '.$doctorBookingDiscountCode->uses_count.' (already redeemed that many times).',
            ])->withInput();
        }

        $serviceIds = DoctorBookingDiscountCode::normalizeServiceIdList($validated['booking_service_ids'] ?? null);
        foreach ($serviceIds as $sid) {
            $svc = BookingService::find($sid);
            if (!$svc || !$svc->isAvailableForDoctor($doctor->id)) {
                return back()->withErrors([
                    'booking_service_ids' => 'Select only services this doctor offers, or leave all unselected for every service.',
                ])->withInput();
            }
        }

        $doctorBookingDiscountCode->update([
            'code' => $codeNormalized,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'max_uses' => $validated['max_uses'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (Schema::hasTable('doctor_booking_discount_code_services')) {
            $doctorBookingDiscountCode->replaceRestrictedBookingServices($serviceIds);
        }

        return redirect()->route('admin.doctors.booking-discount-codes.index', $doctor)
            ->with('success', 'Discount code updated.');
    }

    public function destroy(Doctor $doctor, DoctorBookingDiscountCode $doctorBookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $this->authorizeCode($doctorBookingDiscountCode, $doctor);
        $doctorBookingDiscountCode->delete();

        return redirect()->route('admin.doctors.booking-discount-codes.index', $doctor)
            ->with('success', 'Discount code deleted.');
    }

    private function authorizeCode(DoctorBookingDiscountCode $code, Doctor $doctor): void
    {
        if ((int) $code->doctor_id !== (int) $doctor->id) {
            abort(403);
        }
    }
}
