<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BookingService;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\ServiceOrder;
use App\Services\NonConsultationBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait HandlesNonConsultationPublicBooking
{
    private function nonConsultationBookingContextKey(): string
    {
        return 'non_consultation_booking_ctx';
    }

    private function nonConsultationBookingDataKey(): string
    {
        return 'non_consultation_booking_data';
    }

    protected function redirectToNonConsultationFlow(Request $request, BookingService $service)
    {
        $doctor = null;
        $departmentId = $request->department_id ? (int) $request->department_id : null;

        if ($request->doctor_id) {
            $doctor = Doctor::findOrFail($request->doctor_id);
            if (! $this->serviceIsBookableForDoctor($service->id, $doctor->id)) {
                return redirect()->back()->with('error', 'Selected service is not available for this doctor.');
            }
        } elseif ($departmentId) {
            $doctor = $this->resolveClinicDoctorForService($departmentId, $service->id);
            if (! $doctor) {
                return redirect()->back()->with('error', 'This service is not available at this clinic.');
            }
        } else {
            return redirect()->back()->with('error', 'Please select a doctor or clinic.');
        }

        $isClinicFlow = $departmentId && ! $request->doctor_id;
        $bookingDob = session($this->publicBookingDobSessionKey());
        if (! $bookingDob) {
            session([$this->publicBookingPendingSessionKey() => [
                'flow' => 'non_consultation',
                'doctor_id' => $doctor->id,
                'department_id' => $departmentId,
                'service_id' => $service->id,
            ]]);

            $department = $departmentId ? Department::find($departmentId) : null;

            return view('public-booking.slot-date-of-birth', [
                'flow' => 'non_consultation',
                'department' => $department,
                'doctor' => $doctor,
                'service' => $service,
                'step' => 2,
            ]);
        }

        session([$this->nonConsultationBookingContextKey() => [
            'flow' => $isClinicFlow ? 'clinic' : 'doctor',
            'doctor_id' => $doctor->id,
            'department_id' => $departmentId ?? $doctor->primaryDepartment()?->id,
            'service_id' => $service->id,
        ]]);

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service, $bookingDob)) {
            return $redirect;
        }

        return redirect(publicBookingNonConsultationUrl('patient-details'));
    }

    protected function resolveClinicDoctorForService(int $departmentId, int $serviceId): ?Doctor
    {
        $doctors = Doctor::byDepartment($departmentId)->active()->get()
            ->filter(fn (Doctor $d) => $this->serviceIsBookableForDoctor($serviceId, $d->id));

        if ($doctors->isEmpty()) {
            return null;
        }

        $service = BookingService::find($serviceId);
        $best = null;
        $minPrice = PHP_FLOAT_MAX;
        foreach ($doctors as $doctor) {
            $price = (float) ($service?->getPriceForDoctor($doctor->id) ?? $service?->default_price ?? 0);
            if ($price < $minPrice) {
                $minPrice = $price;
                $best = $doctor;
            }
        }

        return $best ?? $doctors->first();
    }

    public function showNonConsultationPatientDetails(Request $request)
    {
        $this->checkBookingEnabled();
        $ctx = $this->nonConsultationBookingContext();
        if (! $ctx) {
            return $this->sessionExpiredResponse();
        }

        $doctor = Doctor::findOrFail($ctx['doctor_id']);
        $service = BookingService::findOrFail($ctx['service_id']);
        $departmentId = $ctx['department_id'] ?? null;

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service)) {
            return $redirect;
        }

        return view('public-booking.non-consultation-patient-details', [
            'doctor' => $doctor,
            'service' => $service,
            'department_id' => $departmentId,
            'department' => $departmentId ? Department::find($departmentId) : null,
            'step' => 3,
            'bookingDobYmd' => session($this->publicBookingDobSessionKey()),
        ]);
    }

    public function nonConsultationReview(Request $request)
    {
        $this->checkBookingEnabled();
        $ctx = $this->nonConsultationBookingContext();
        if (! $ctx) {
            return $this->sessionExpiredResponse();
        }

        $validator = Validator::make($request->all(), array_merge([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'consent' => 'required|accepted',
            'notes' => 'required|string|max:10000',
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'required_if:consent_share_with_gp,1|nullable|string|max:255',
            'gp_email' => 'required_if:consent_share_with_gp,1|nullable|email|max:255',
            'gp_phone' => 'required_if:consent_share_with_gp,1|nullable|string|max:20',
            'gp_address' => 'required_if:consent_share_with_gp,1|nullable|string|max:500',
        ], $this->publicBookingAddressValidationRules(), $this->publicBookingGuardianFieldRules($request)));

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $service = BookingService::findOrFail($ctx['service_id']);
        $doctor = Doctor::findOrFail($ctx['doctor_id']);
        $dobYmd = parseDateInput($request->date_of_birth);
        session([$this->publicBookingDobSessionKey() => $dobYmd]);

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service, $dobYmd)) {
            return $redirect;
        }

        $patientData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'notes', 'gender', 'consent_share_with_gp',
            'gp_name', 'gp_email', 'gp_phone', 'gp_address',
            'address', 'address_line_2', 'city', 'state', 'postal_code', 'country',
            'guardian_name', 'guardian_phone',
        ]);
        $patientData['date_of_birth'] = $dobYmd;
        if (! empty($ctx['department_id'])) {
            $patientData['department_id'] = $ctx['department_id'];
        }

        session([$this->nonConsultationBookingDataKey() => $patientData]);

        return redirect(publicBookingNonConsultationUrl('review'));
    }

    public function showNonConsultationReview(Request $request)
    {
        $this->checkBookingEnabled();
        $ctx = $this->nonConsultationBookingContext();
        $patientData = session($this->nonConsultationBookingDataKey());
        if (! $ctx || ! is_array($patientData)) {
            return $this->sessionExpiredResponse();
        }

        $doctor = Doctor::findOrFail($ctx['doctor_id']);
        $service = BookingService::findOrFail($ctx['service_id']);
        $price = $service->getPriceForDoctor($doctor->id);

        return view('public-booking.non-consultation-review', [
            'doctor' => $doctor,
            'service' => $service,
            'patient_data' => $patientData,
            'price' => $price,
            'department_id' => $ctx['department_id'] ?? null,
            'is_clinic_flow' => ($ctx['flow'] ?? '') === 'clinic',
            'step' => 4,
        ]);
    }

    public function nonConsultationConfirm(Request $request)
    {
        $this->checkBookingEnabled();
        $ctx = $this->nonConsultationBookingContext();
        $patientData = session($this->nonConsultationBookingDataKey());
        if (! $ctx || ! is_array($patientData)) {
            return redirect()->back()->with('error', 'Your session expired. Please start again.');
        }

        $doctor = Doctor::findOrFail($ctx['doctor_id']);
        $service = BookingService::findOrFail($ctx['service_id']);
        $isClinic = ($ctx['flow'] ?? '') === 'clinic';

        $data = array_merge($patientData, [
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'department_id' => $ctx['department_id'] ?? null,
            'discount_code' => $request->discount_code,
        ]);

        try {
            $result = app(NonConsultationBookingService::class)->createFromPublicBooking(
                $data,
                $doctor,
                $service,
                $ctx['department_id'] ?? null,
                $isClinic
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Non-consultation booking failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Unable to complete booking. Please try again.')->withInput();
        }

        $order = $result['service_order'];
        session()->forget([$this->nonConsultationBookingContextKey(), $this->nonConsultationBookingDataKey(), 'booking_data']);

        if ($result['invoice'] && $result['invoice']->payment_token) {
            session(['pending_service_order_token' => $order->booking_token]);

            return redirect()->route('public.billing.pay', ['token' => $result['invoice']->payment_token]);
        }

        return redirect(publicBookingNonConsultationUrl('success', ['orderNumber' => $order->order_number]));
    }

    public function nonConsultationSuccess(Request $request, string $orderNumber)
    {
        $this->checkBookingEnabled();
        $order = ServiceOrder::where('order_number', $orderNumber)
            ->with(['doctor', 'service', 'patient'])
            ->firstOrFail();

        return view('public-booking.non-consultation-success', compact('order'));
    }

    public function previewNonConsultationDoctorDiscount(Request $request)
    {
        $ctx = $this->nonConsultationBookingContext();
        if (! $ctx) {
            return response()->json(['ok' => false, 'message' => 'Session expired.'], 422);
        }

        $result = app(NonConsultationBookingService::class)->previewDoctorDiscount(
            (int) $ctx['doctor_id'],
            (int) $ctx['service_id'],
            (string) $request->input('discount_code', '')
        );

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    public function previewNonConsultationClinicDiscount(Request $request)
    {
        $ctx = $this->nonConsultationBookingContext();
        if (! $ctx || empty($ctx['department_id'])) {
            return response()->json(['ok' => false, 'message' => 'Session expired.'], 422);
        }

        $result = app(NonConsultationBookingService::class)->previewClinicDiscount(
            (int) $ctx['department_id'],
            (int) $ctx['service_id'],
            (string) $request->input('discount_code', '')
        );

        return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
    }

    private function nonConsultationBookingContext(): ?array
    {
        $ctx = session($this->nonConsultationBookingContextKey());

        return is_array($ctx) && ! empty($ctx['doctor_id']) && ! empty($ctx['service_id']) ? $ctx : null;
    }

    protected function handleNonConsultationAfterSlotDob(array $pending): ?\Illuminate\Http\RedirectResponse
    {
        if (($pending['flow'] ?? '') !== 'non_consultation') {
            return null;
        }

        $doctor = Doctor::findOrFail($pending['doctor_id']);
        $service = BookingService::findOrFail($pending['service_id']);

        session([$this->nonConsultationBookingContextKey() => [
            'flow' => ! empty($pending['department_id']) ? 'clinic' : 'doctor',
            'doctor_id' => $doctor->id,
            'department_id' => $pending['department_id'] ?? $doctor->primaryDepartment()?->id,
            'service_id' => $service->id,
        ]]);

        return redirect(publicBookingNonConsultationUrl('patient-details'));
    }
}
