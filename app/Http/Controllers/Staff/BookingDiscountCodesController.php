<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorBookingDiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class BookingDiscountCodesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function doctorForUser(): Doctor
    {
        return Doctor::where('user_id', Auth::id())->firstOrFail();
    }

    private function assertTableExists(): ?\Illuminate\Http\RedirectResponse
    {
        if (!Schema::hasTable('doctor_booking_discount_codes')) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Discount codes require a database migration. Run: php artisan migrate');
        }

        return null;
    }

    /**
     * Services this doctor may book publicly — same rule as patient-facing doctor link and admin discount codes.
     *
     * @return Collection<int, BookingService>
     */
    private function bookingServicesForDoctor(Doctor $doctor): Collection
    {
        return BookingService::query()
            ->where('is_active', true)
            ->ordered()
            ->get()
            ->filter(fn (BookingService $svc) => $svc->isAvailableForDoctor($doctor->id))
            ->values();
    }

    public function index()
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $doctor = $this->doctorForUser();
        $codes = DoctorBookingDiscountCode::query()
            ->where('doctor_id', $doctor->id)
            ->with('bookingService')
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        return view('staff.booking-discount-codes.index', compact('doctor', 'codes'));
    }

    public function create()
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $doctor = $this->doctorForUser();
        $services = $this->bookingServicesForDoctor($doctor);

        return view('staff.booking-discount-codes.create', compact('doctor', 'services'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $doctor = $this->doctorForUser();

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
            'booking_service_id' => 'nullable|exists:booking_services,id',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        }

        if (!empty($validated['booking_service_id'])) {
            $svc = BookingService::find($validated['booking_service_id']);
            if (!$svc || !$svc->isAvailableForDoctor($doctor->id)) {
                return back()->withErrors([
                    'booking_service_id' => 'Select a service you offer on your booking link, or leave blank for all services.',
                ])->withInput();
            }
        }

        DoctorBookingDiscountCode::create([
            'doctor_id' => $doctor->id,
            'code' => $codeNormalized,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'booking_service_id' => $validated['booking_service_id'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('staff.booking-discount-codes.index')
            ->with('success', 'Discount code created. Patients can enter it on the review step before they pay.');
    }

    public function edit(DoctorBookingDiscountCode $bookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $doctor = $this->doctorForUser();
        $this->authorizeCode($bookingDiscountCode, $doctor);

        $services = $this->bookingServicesForDoctor($doctor);

        return view('staff.booking-discount-codes.edit', compact('doctor', 'bookingDiscountCode', 'services'));
    }

    public function update(Request $request, DoctorBookingDiscountCode $bookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $doctor = $this->doctorForUser();
        $this->authorizeCode($bookingDiscountCode, $doctor);

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
                    ->ignore($bookingDiscountCode->id),
            ],
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'booking_service_id' => 'nullable|exists:booking_services,id',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        }

        $newMax = $validated['max_uses'] ?? null;
        if ($newMax !== null && $newMax < $bookingDiscountCode->uses_count) {
            return back()->withErrors([
                'max_uses' => 'Max uses must be at least '.$bookingDiscountCode->uses_count.' (already redeemed that many times).',
            ])->withInput();
        }

        if (!empty($validated['booking_service_id'])) {
            $svc = BookingService::find($validated['booking_service_id']);
            if (!$svc || !$svc->isAvailableForDoctor($doctor->id)) {
                return back()->withErrors([
                    'booking_service_id' => 'Select a service you offer on your booking link, or leave blank for all services.',
                ])->withInput();
            }
        }

        $bookingDiscountCode->update([
            'code' => $codeNormalized,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'booking_service_id' => $validated['booking_service_id'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('staff.booking-discount-codes.index')
            ->with('success', 'Discount code updated.');
    }

    public function destroy(DoctorBookingDiscountCode $bookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $doctor = $this->doctorForUser();
        $this->authorizeCode($bookingDiscountCode, $doctor);

        $bookingDiscountCode->delete();

        return redirect()->route('staff.booking-discount-codes.index')
            ->with('success', 'Discount code deleted.');
    }

    private function authorizeCode(DoctorBookingDiscountCode $code, Doctor $doctor): void
    {
        if ((int) $code->doctor_id !== (int) $doctor->id) {
            abort(403);
        }
    }
}
