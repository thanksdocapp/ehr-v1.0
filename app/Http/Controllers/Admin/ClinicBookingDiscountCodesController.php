<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\ClinicBookingDiscountCode;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorBookingDiscountCode;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ClinicBookingDiscountCodesController extends Controller
{
    private function assertTableExists(): ?\Illuminate\Http\RedirectResponse
    {
        if (!Schema::hasTable('clinic_booking_discount_codes')) {
            return redirect()->back()
                ->with('error', 'Clinic discount codes require a database migration. Run: php artisan migrate');
        }

        return null;
    }

    /**
     * Services that appear on public clinic booking for this department (per-doctor booking_services rows).
     *
     * @return Collection<int, BookingService>
     */
    private function bookingServicesForDepartment(Department $department): Collection
    {
        $ids = collect();
        foreach (Doctor::byDepartment($department->id)->active()->get() as $doctor) {
            if (!$doctor->user_id) {
                continue;
            }
            BookingService::query()
                ->where('created_by', $doctor->user_id)
                ->where('is_active', true)
                ->pluck('id')
                ->each(fn ($id) => $ids->push((int) $id));
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return BookingService::query()
            ->whereIn('id', $ids->unique()->values()->all())
            ->orderBy('name')
            ->get();
    }

    public function index(Department $department)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $codes = ClinicBookingDiscountCode::query()
            ->where('department_id', $department->id)
            ->with('bookingService')
            ->orderByDesc('is_active')
            ->orderBy('code')
            ->get();

        $doctorCodes = collect();
        if (Schema::hasTable('doctor_booking_discount_codes')) {
            $doctorIds = Doctor::byDepartment($department->id)->active()->pluck('id');
            if ($doctorIds->isNotEmpty()) {
                $doctorCodes = DoctorBookingDiscountCode::query()
                    ->whereIn('doctor_id', $doctorIds)
                    ->with(['bookingServices', 'bookingService', 'doctor'])
                    ->orderByDesc('is_active')
                    ->orderBy('code')
                    ->get();
            }
        }

        return view('admin.clinic-booking-discount-codes.index', compact('department', 'codes', 'doctorCodes'));
    }

    public function create(Department $department)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $services = $this->bookingServicesForDepartment($department);

        return view('admin.clinic-booking-discount-codes.create', compact('department', 'services'));
    }

    public function store(Request $request, Department $department)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $codeNormalized = ClinicBookingDiscountCode::normalizeCode((string) $request->input('code', ''));

        $request->merge([
            'code' => $codeNormalized,
            'max_uses' => $request->filled('max_uses') ? $request->input('max_uses') : null,
        ]);

        $allowedServiceIds = $this->bookingServicesForDepartment($department)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('clinic_booking_discount_codes', 'code')->where('department_id', $department->id),
            ],
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'booking_service_id' => [
                'nullable',
                'exists:booking_services,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($allowedServiceIds): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if ($allowedServiceIds === []) {
                        $fail('No bookable services are configured for this clinic yet. Leave service blank or add doctor services first.');

                        return;
                    }
                    if (!in_array((int) $value, $allowedServiceIds, true)) {
                        $fail('Choose a service this clinic offers on public booking.');
                    }
                },
            ],
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        }

        ClinicBookingDiscountCode::create([
            'department_id' => $department->id,
            'code' => $codeNormalized,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'booking_service_id' => $validated['booking_service_id'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.departments.clinic-booking-discount-codes.index', $department)
            ->with('success', 'Clinic booking discount code created.');
    }

    public function edit(Department $department, ClinicBookingDiscountCode $clinicBookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $this->authorizeCode($clinicBookingDiscountCode, $department);
        $services = $this->bookingServicesForDepartment($department);

        return view('admin.clinic-booking-discount-codes.edit', compact('department', 'clinicBookingDiscountCode', 'services'));
    }

    public function update(Request $request, Department $department, ClinicBookingDiscountCode $clinicBookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $this->authorizeCode($clinicBookingDiscountCode, $department);

        $codeNormalized = ClinicBookingDiscountCode::normalizeCode((string) $request->input('code', ''));

        $request->merge([
            'code' => $codeNormalized,
            'max_uses' => $request->filled('max_uses') ? $request->input('max_uses') : null,
        ]);

        $allowedServiceIds = $this->bookingServicesForDepartment($department)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('clinic_booking_discount_codes', 'code')
                    ->where('department_id', $department->id)
                    ->ignore($clinicBookingDiscountCode->id),
            ],
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'booking_service_id' => [
                'nullable',
                'exists:booking_services,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($allowedServiceIds): void {
                    if ($value === null || $value === '') {
                        return;
                    }
                    if ($allowedServiceIds === []) {
                        $fail('No bookable services are configured for this clinic yet. Leave service blank or add doctor services first.');

                        return;
                    }
                    if (!in_array((int) $value, $allowedServiceIds, true)) {
                        $fail('Choose a service this clinic offers on public booking.');
                    }
                },
            ],
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        if ($validated['discount_type'] === 'percent' && (float) $validated['discount_value'] > 100) {
            return back()->withErrors(['discount_value' => 'Percentage cannot exceed 100.'])->withInput();
        }

        $newMax = $validated['max_uses'] ?? null;
        if ($newMax !== null && $newMax < $clinicBookingDiscountCode->uses_count) {
            return back()->withErrors([
                'max_uses' => 'Max uses must be at least '.$clinicBookingDiscountCode->uses_count.' (already redeemed that many times).',
            ])->withInput();
        }

        $clinicBookingDiscountCode->update([
            'code' => $codeNormalized,
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'booking_service_id' => $validated['booking_service_id'] ?? null,
            'max_uses' => $validated['max_uses'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.departments.clinic-booking-discount-codes.index', $department)
            ->with('success', 'Discount code updated.');
    }

    public function destroy(Department $department, ClinicBookingDiscountCode $clinicBookingDiscountCode)
    {
        if ($redirect = $this->assertTableExists()) {
            return $redirect;
        }

        $this->authorizeCode($clinicBookingDiscountCode, $department);
        $clinicBookingDiscountCode->delete();

        return redirect()->route('admin.departments.clinic-booking-discount-codes.index', $department)
            ->with('success', 'Discount code deleted.');
    }

    private function authorizeCode(ClinicBookingDiscountCode $code, Department $department): void
    {
        if ((int) $code->department_id !== (int) $department->id) {
            abort(403);
        }
    }
}
