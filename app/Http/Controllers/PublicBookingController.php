<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\BookingService;
use App\Models\Setting;
use App\Services\SlotAvailabilityService;
use App\Services\PublicBookingService;
use App\Services\ClinicBookingService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    protected $slotAvailabilityService;
    protected $bookingService;
    protected $clinicBookingService;

    public function __construct(SlotAvailabilityService $slotAvailabilityService, PublicBookingService $bookingService, ClinicBookingService $clinicBookingService)
    {
        $this->slotAvailabilityService = $slotAvailabilityService;
        $this->bookingService = $bookingService;
        $this->clinicBookingService = $clinicBookingService;
    }

    private function isClinicMode(): bool
    {
        return Setting::get('public_booking_mode', 'clinic') === 'clinic';
    }

    private function publicBookingDobSessionKey(): string
    {
        return 'public_booking_dob';
    }

    private function publicBookingPendingSessionKey(): string
    {
        return 'public_booking_pending';
    }

    private function clinicBookingReviewSessionKey(): string
    {
        return 'clinic_booking_review';
    }

    /**
     * Patient home address captured during public / clinic booking (Ideal Postcodes + manual fields).
     *
     * @return array<string, string>
     */
    private function publicBookingAddressValidationRules(): array
    {
        return [
            'address' => 'required|string|max:500',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:100',
        ];
    }

    /**
     * True when booking request date of birth indicates the patient is under 18.
     */
    private function patientIsMinorForBookingRequest(Request $request): bool
    {
        $raw = $request->input('date_of_birth');
        if ($raw === null || $raw === '') {
            return false;
        }
        $ymd = parseDateInput($raw);
        if (!$ymd) {
            return false;
        }
        try {
            return Carbon::parse($ymd)->age < 18;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Guardian/parent contact for minors (public booking — no ID document at booking).
     *
     * @return array<string, string>
     */
    private function publicBookingGuardianFieldRules(Request $request): array
    {
        if (!$this->patientIsMinorForBookingRequest($request)) {
            return [
                'guardian_name' => 'nullable|string|max:255',
                'guardian_phone' => 'nullable|string|max:20',
            ];
        }

        return [
            'guardian_name' => 'required|string|max:255',
            'guardian_phone' => 'required|string|max:20',
        ];
    }

    /**
     * @param  string|null  $dobYmd  Parsed Y-m-d; when null, uses session value.
     */
    private function redirectIfServiceIneligibleForPublicBooking(BookingService $service, ?string $dobYmd = null): ?\Illuminate\Http\RedirectResponse
    {
        $dobYmd = $dobYmd ?? session($this->publicBookingDobSessionKey());
        if (!$dobYmd) {
            return null;
        }
        if (!$service->isEligibleForAgeYears(Carbon::parse($dobYmd)->age)) {
            return redirect()->back()->with('error', 'This service is not available for this age.')->withInput();
        }

        return null;
    }

    public function storePublicBookingDob(Request $request)
    {
        $this->checkBookingEnabled();

        $validator = Validator::make($request->all(), [
            'date_of_birth' => 'required|date|before:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $ymd = parseDateInput($request->date_of_birth);
        session([$this->publicBookingDobSessionKey() => $ymd]);

        return back();
    }

    public function clearPublicBookingDob()
    {
        $this->checkBookingEnabled();
        session()->forget($this->publicBookingDobSessionKey());
        session()->forget($this->publicBookingPendingSessionKey());

        return back();
    }

    /**
     * After service/date/time are chosen, collect date of birth before contact details.
     */
    public function storeSlotBookingDob(Request $request)
    {
        $this->checkBookingEnabled();

        if ($request->filled('date_of_birth')) {
            $parsed = parseDateInput($request->date_of_birth);
            if ($parsed) {
                $request->merge(['date_of_birth' => $parsed]);
            }
        }

        $validator = Validator::make($request->all(), [
            'date_of_birth' => 'required|date|before:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pending = session($this->publicBookingPendingSessionKey());
        if (!is_array($pending) || empty($pending['flow']) || empty($pending['service_id'])) {
            return back()->with('error', 'Your booking session expired. Please start again.');
        }

        $ymd = $request->date_of_birth;
        $service = BookingService::findOrFail($pending['service_id']);

        if (!$service->isEligibleForAgeYears(Carbon::parse($ymd)->age)) {
            return back()->withErrors(['date_of_birth' => 'This service is not available for this age.'])->withInput();
        }

        session([$this->publicBookingDobSessionKey() => $ymd]);
        session()->forget($this->publicBookingPendingSessionKey());

        if ($pending['flow'] === 'clinic') {
            $department = Department::findOrFail($pending['department_id']);
            $slots = $this->slotAvailabilityService->getAvailableSlotsForDepartment(
                $department->id,
                $pending['appointment_date'],
                $service->id
            );
            $selectedSlot = collect($slots)->firstWhere('start', $pending['appointment_time']);
            if (!$selectedSlot) {
                return redirect()->route('public.booking.clinic', ['slug' => $department->slug])
                    ->with('error', 'Selected time slot is no longer available. Please choose again.');
            }

            return view('public-booking.clinic-patient-details', [
                'department' => $department,
                'service' => $service,
                'appointment_date' => $pending['appointment_date'],
                'appointment_time' => $pending['appointment_time'],
                'consultation_type' => $pending['consultation_type'] ?? 'in_person',
                'step' => 3,
                'bookingDobYmd' => $ymd,
            ]);
        }

        if ($pending['flow'] === 'doctor') {
            $doctor = Doctor::findOrFail($pending['doctor_id']);
            $slots = $this->slotAvailabilityService->getAvailableSlots(
                $doctor->id,
                $pending['appointment_date'],
                $service->id
            );
            $selectedSlot = collect($slots)->firstWhere('start', $pending['appointment_time']);
            if (!$selectedSlot) {
                return redirect()->route('public.booking.doctor', ['slug' => $doctor->slug])
                    ->with('error', 'Selected time slot is no longer available. Please choose again.');
            }

            $departmentId = $pending['department_id'] ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;

            return view('public-booking.patient-details', [
                'doctor' => $doctor,
                'service' => $service,
                'appointment_date' => $pending['appointment_date'],
                'appointment_time' => $pending['appointment_time'],
                'consultation_type' => $pending['consultation_type'] ?? 'in_person',
                'department_id' => $departmentId,
                'step' => 3,
                'bookingDobYmd' => $ymd,
            ]);
        }

        return back()->with('error', 'Invalid booking flow.');
    }

    /**
     * Check if public booking is enabled.
     */
    private function checkBookingEnabled()
    {
        $enabled = Setting::get('public_booking_enabled', '1');
        if ($enabled != '1') {
            abort(404, 'Online booking is currently unavailable.');
        }
    }

    /**
     * Step 1: Access via unique link - /book/{doctorSlug}
     */
    public function showDoctorBooking($slug)
    {
        $this->checkBookingEnabled();

        $doctor = Doctor::where('slug', $slug)->active()->firstOrFail();
        
        // For single doctor link, show that doctor pre-selected
        return view('public-booking.service-selection', [
            'doctor' => $doctor,
            'doctors' => collect([$doctor]), // Single doctor in collection
            'step' => 1,
            'bookingDob' => session($this->publicBookingDobSessionKey()),
        ]);
    }

    /**
     * Step 1: Access via clinic link - /book/clinic/{clinicSlug}
     * In clinic mode: patient books into clinic, doctors accept. No doctor selection.
     */
    public function showClinicBooking($slug)
    {
        $this->checkBookingEnabled();

        $department = Department::where('slug', $slug)->active()->firstOrFail();

        $bookingDob = session($this->publicBookingDobSessionKey());
        $ageYears = $bookingDob ? Carbon::parse($bookingDob)->age : null;

        if ($this->isClinicMode()) {
            $services = $this->getClinicServices($department->id, $ageYears);
            return view('public-booking.clinic-booking', [
                'department' => $department,
                'services' => $services,
                'step' => 1,
                'bookingDob' => $bookingDob,
            ]);
        }

        $doctors = Doctor::byDepartment($department->id)->active()->get();
        return view('public-booking.service-selection', [
            'department' => $department,
            'doctors' => $doctors,
            'step' => 1,
            'bookingDob' => $bookingDob,
        ]);
    }

    /**
     * Get services offered by clinic (union of services from all doctors in department).
     */
    private function getClinicServices(int $departmentId, ?int $patientAgeYears = null): array
    {
        $doctors = Doctor::byDepartment($departmentId)->active()->get();
        $servicesMap = [];

        foreach ($doctors as $doctor) {
            $doctorServices = BookingService::where('created_by', $doctor->user_id)
                ->where('is_active', true)
                ->get();
            foreach ($doctorServices as $svc) {
                if ($patientAgeYears !== null && !$svc->isEligibleForAgeYears($patientAgeYears)) {
                    continue;
                }
                $price = $svc->getPriceForDoctor($doctor->id) ?? $svc->default_price ?? 0;
                $duration = $svc->getDurationForDoctor($doctor->id) ?? $svc->default_duration_minutes ?? 60;
                $consultationType = $svc->getConsultationTypeForDoctor($doctor->id) ?? 'in_person';
                if (!isset($servicesMap[$svc->id])) {
                    $servicesMap[$svc->id] = ['id' => $svc->id, 'name' => $svc->name, 'description' => $svc->description ?? '', 'price' => $price, 'duration' => $duration, 'consultation_type' => $consultationType];
                } else {
                    $servicesMap[$svc->id]['price'] = min($servicesMap[$svc->id]['price'], $price);
                    if ($consultationType === 'online') {
                        $servicesMap[$svc->id]['consultation_type'] = 'online';
                    }
                }
            }
        }

        return array_values($servicesMap);
    }

    /**
     * Clinic flow: patient details (no doctor).
     */
    public function clinicPatientDetails(Request $request)
    {
        $this->checkBookingEnabled();

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            $dept = Department::find($request->department_id);
            if ($dept) {
                return redirect()->route('public.booking.clinic', ['slug' => $dept->slug])
                    ->withErrors($validator)->withInput();
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $department = Department::findOrFail($request->department_id);
        $service = BookingService::findOrFail($request->service_id);

        $slots = $this->slotAvailabilityService->getAvailableSlotsForDepartment(
            $department->id,
            $request->appointment_date,
            $service->id
        );

        $selectedSlot = collect($slots)->firstWhere('start', $request->appointment_time);
        if (!$selectedSlot) {
            return redirect()->back()->with('error', 'Selected time slot is no longer available.');
        }

        $bookingDob = session($this->publicBookingDobSessionKey());
        if (!$bookingDob) {
            session([$this->publicBookingPendingSessionKey() => [
                'flow' => 'clinic',
                'department_id' => $department->id,
                'service_id' => $service->id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'consultation_type' => $request->consultation_type ?? 'in_person',
            ]]);

            return view('public-booking.slot-date-of-birth', [
                'flow' => 'clinic',
                'department' => $department,
                'doctor' => null,
                'service' => $service,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'consultation_type' => $request->consultation_type ?? 'in_person',
                'step' => 2,
            ]);
        }

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service)) {
            return $redirect;
        }

        return view('public-booking.clinic-patient-details', [
            'department' => $department,
            'service' => $service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'consultation_type' => $request->consultation_type ?? 'in_person',
            'step' => 3,
            'bookingDobYmd' => $bookingDob,
        ]);
    }

    /**
     * Clinic flow: review (no doctor).
     */
    public function clinicReview(Request $request)
    {
        $this->checkBookingEnabled();

        if ($request->has('consultation_type') && $request->consultation_type === 'phone') {
            $request->merge(['consultation_type' => 'telephone']);
        }

        $validator = Validator::make($request->all(), array_merge([
            'department_id' => 'required|exists:departments,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'consent' => 'required|accepted',
            'notes' => 'required|string|max:10000',
        ], $this->publicBookingAddressValidationRules(), $this->publicBookingGuardianFieldRules($request)));

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $department = Department::findOrFail($request->department_id);
        $service = BookingService::findOrFail($request->service_id);

        $dobYmd = parseDateInput($request->date_of_birth);
        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service, $dobYmd)) {
            return $redirect;
        }
        session([$this->publicBookingDobSessionKey() => $dobYmd]);

        $slots = $this->slotAvailabilityService->getAvailableSlotsForDepartment(
            $department->id,
            $request->appointment_date,
            $service->id
        );
        $selectedSlot = collect($slots)->firstWhere('start', $request->appointment_time);
        if (!$selectedSlot) {
            return redirect()->back()->with('error', 'Selected time slot is no longer available.')->withInput();
        }

        $doctors = Doctor::byDepartment($department->id)->active()->get();
        $prices = $doctors->map(fn($d) => $service->getPriceForDoctor($d->id) ?? $service->default_price ?? 0)->filter();
        $price = $prices->isEmpty() ? ($service->default_price ?? 0) : $prices->min();

        $patientData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'notes', 'consultation_type',
            'gender', 'consent_share_with_gp', 'gp_name', 'gp_email', 'gp_phone', 'gp_address',
            'address', 'address_line_2', 'city', 'state', 'postal_code', 'country',
            'guardian_name', 'guardian_phone',
        ]);
        if ($request->has('date_of_birth') && $request->date_of_birth) {
            $patientData['date_of_birth'] = parseDateInput($request->date_of_birth);
        }
        $patientData['department_id'] = $department->id;

        session([$this->clinicBookingReviewSessionKey() => [
            'department_id' => $department->id,
            'service_id' => $service->id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'patient_data' => $patientData,
            'price' => $price,
        ]]);

        return view('public-booking.clinic-review', [
            'department' => $department,
            'service' => $service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'patient_data' => $patientData,
            'price' => $price,
            'step' => 3
        ]);
    }

    /**
     * Clinic flow: review step (GET — refresh, bookmark, or direct URL).
     */
    public function showClinicReview(Request $request)
    {
        $this->checkBookingEnabled();

        $data = session($this->clinicBookingReviewSessionKey());
        if (!$data || !isset($data['department_id'], $data['service_id'], $data['appointment_date'], $data['appointment_time'], $data['patient_data'])) {
            return response()->view('public-booking.session-expired', [
                'message' => 'Your booking session has expired. Please start a new booking.',
            ]);
        }

        $department = Department::find($data['department_id']);
        $service = BookingService::find($data['service_id']);
        if (!$department || !$service) {
            session()->forget($this->clinicBookingReviewSessionKey());

            return response()->view('public-booking.session-expired', [
                'message' => 'Your booking session has expired. Please start a new booking.',
            ]);
        }

        $dobYmd = $data['patient_data']['date_of_birth'] ?? null;
        if ($dobYmd) {
            try {
                if (!$service->isEligibleForAgeYears(Carbon::parse($dobYmd)->age)) {
                    session()->forget($this->clinicBookingReviewSessionKey());

                    return redirect()->route('public.booking.clinic', ['slug' => $department->slug])
                        ->with('error', 'This service is not available for this age.');
                }
            } catch (\Exception $e) {
                session()->forget($this->clinicBookingReviewSessionKey());

                return response()->view('public-booking.session-expired', [
                    'message' => 'Your booking session has expired. Please start a new booking.',
                ]);
            }
        }

        $slots = $this->slotAvailabilityService->getAvailableSlotsForDepartment(
            $department->id,
            $data['appointment_date'],
            $service->id
        );
        $selectedSlot = collect($slots)->firstWhere('start', $data['appointment_time']);
        if (!$selectedSlot) {
            session()->forget($this->clinicBookingReviewSessionKey());

            return redirect()->route('public.booking.clinic', ['slug' => $department->slug])
                ->with('error', 'Selected time slot is no longer available. Please choose another time.');
        }

        $doctors = Doctor::byDepartment($department->id)->active()->get();
        $prices = $doctors->map(fn($d) => $service->getPriceForDoctor($d->id) ?? $service->default_price ?? 0)->filter();
        $price = $prices->isEmpty() ? ($service->default_price ?? 0) : $prices->min();

        session([$this->clinicBookingReviewSessionKey() => array_merge($data, ['price' => $price])]);

        return view('public-booking.clinic-review', [
            'department' => $department,
            'service' => $service,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'patient_data' => $data['patient_data'],
            'price' => $price,
            'step' => 3,
        ]);
    }

    /**
     * Clinic flow: confirm and create clinic booking request.
     */
    public function clinicConfirm(Request $request)
    {
        $this->checkBookingEnabled();

        if ($request->has('consultation_type') && $request->consultation_type === 'phone') {
            $request->merge(['consultation_type' => 'telephone']);
        }

        $validator = Validator::make($request->all(), array_merge([
            'department_id' => 'required|exists:departments,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'notes' => 'required|string|max:10000',
            'discount_code' => 'nullable|string|max:64',
        ], $this->publicBookingAddressValidationRules(), $this->publicBookingGuardianFieldRules($request)));

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $department = Department::findOrFail($request->department_id);
        $service = BookingService::findOrFail($request->service_id);

        $slots = $this->slotAvailabilityService->getAvailableSlotsForDepartment(
            $department->id,
            $request->appointment_date,
            $service->id
        );
        $selectedSlot = collect($slots)->firstWhere('start', $request->appointment_time);
        if (!$selectedSlot) {
            return redirect()->back()->with('error', 'Selected time slot is no longer available.')->withInput();
        }

        $data = $request->all();
        if (isset($data['date_of_birth']) && $data['date_of_birth']) {
            $data['date_of_birth'] = parseDateInput($data['date_of_birth']);
        }

        $department = Department::findOrFail($request->department_id);
        $service = BookingService::findOrFail($request->service_id);

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service, $data['date_of_birth'] ?? null)) {
            return $redirect;
        }
        session([$this->publicBookingDobSessionKey() => $data['date_of_birth']]);

        $doctors = Doctor::byDepartment($department->id)->active()->get();
        $prices = $doctors->map(fn($d) => $service->getPriceForDoctor($d->id) ?? $service->default_price ?? 0)->filter();
        $price = $prices->isEmpty() ? ($service->default_price ?? 0) : $prices->min();

        try {
            if ($price > 0) {
                $result = $this->clinicBookingService->createPendingFromClinicBooking($data);
                if (!empty($result['clinic_request'])) {
                    $clinicRequest = $result['clinic_request'];

                    return redirect()->route('public.booking.clinic-success', ['requestNumber' => $clinicRequest->request_number])
                        ->with('request', $clinicRequest);
                }
                $invoice = $result['invoice'];
                $pending = $result['pending_clinic_booking'];

                if ($invoice && $invoice->payment_token) {
                    session(['pending_clinic_booking_token' => $pending->booking_token]);

                    return redirect()->route('public.billing.pay', ['token' => $invoice->payment_token]);
                }

                return redirect()->back()->with('error', 'Payment setup failed. Please try again.')->withInput();
            }

            $clinicRequest = $this->clinicBookingService->createFromClinicBooking($data);

            return redirect()->route('public.booking.clinic-success', ['requestNumber' => $clinicRequest->request_number])
                ->with('request', $clinicRequest);
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Clinic booking failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Failed to submit booking request. Please try again.')->withInput();
        }
    }

    /**
     * Clinic flow: success page (request received, awaiting doctor).
     */
    public function clinicSuccess($requestNumber)
    {
        session()->forget($this->publicBookingDobSessionKey());
        session()->forget($this->publicBookingPendingSessionKey());
        session()->forget($this->clinicBookingReviewSessionKey());

        $request = \App\Models\ClinicBookingRequest::where('request_number', $requestNumber)->with(['department', 'service'])->firstOrFail();
        $patientEmail = $request->patient_data['email'] ?? '';

        return view('public-booking.clinic-success', [
            'request' => $request,
            'patientEmail' => $patientEmail
        ]);
    }

    /**
     * Step 1: Access via service-specific booking link - /book/service/{serviceId}/{doctorId}
     */
    public function showServiceBooking($serviceId, $doctorId)
    {
        $this->checkBookingEnabled();

        $service = BookingService::findOrFail($serviceId);
        $doctor = Doctor::where('id', $doctorId)->active()->firstOrFail();

        // Verify service is available for this doctor
        if (!$service->isAvailableForDoctor($doctor->id)) {
            abort(404, 'This service is not available for the selected doctor.');
        }

        $bookingDob = session($this->publicBookingDobSessionKey());
        if ($bookingDob && !$service->isEligibleForAgeYears(Carbon::parse($bookingDob)->age)) {
            return redirect()->route('public.booking.doctor', ['slug' => $doctor->slug])
                ->with('warning', 'This service is not available for the age you entered. Choose another service or update your date of birth.');
        }

        // Get the doctor's department
        $department = $doctor->primaryDepartment();

        return view('public-booking.service-selection', [
            'service' => $service,
            'doctor' => $doctor,
            'doctors' => collect([$doctor]), // Single doctor pre-selected
            'department' => $department,
            'step' => 1,
            'bookingDob' => $bookingDob,
        ]);
    }

    /**
     * Step 2: Select date and time (or doctor if booking through clinic)
     */
    public function selectDateTime(Request $request)
    {
        $this->checkBookingEnabled();

        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:booking_services,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $service = BookingService::findOrFail($request->service_id);

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service)) {
            return $redirect;
        }

        // If department_id is provided but no doctor_id, show doctor selection
        if ($request->department_id && !$request->doctor_id) {
            $department = Department::findOrFail($request->department_id);
            $doctors = Doctor::byDepartment($department->id)
                ->active()
                ->get()
                ->filter(function($doctor) use ($service) {
                    return $service->isAvailableForDoctor($doctor->id);
                });

            return view('public-booking.doctor-selection', [
                'department' => $department,
                'service' => $service,
                'doctors' => $doctors,
                'step' => 2
            ]);
        }

        // If doctor_id is provided, proceed to date/time selection
        if ($request->doctor_id) {
            $doctor = Doctor::findOrFail($request->doctor_id);

            // Check if service is available for this doctor
            if (!$service->isAvailableForDoctor($doctor->id)) {
                return redirect()->back()->with('error', 'Selected service is not available for this doctor.');
            }

            return view('public-booking.date-time-selection', [
                'doctor' => $doctor,
                'service' => $service,
                'step' => 2
            ]);
        }

        return redirect()->back()->with('error', 'Please select a doctor or department.');
    }

    /**
     * Step 3: Enter patient details
     */
    public function patientDetails(Request $request)
    {
        $this->checkBookingEnabled();

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $doctor = Doctor::findOrFail($request->doctor_id);
        $service = BookingService::findOrFail($request->service_id);

        // Verify slot is still available
        $slots = $this->slotAvailabilityService->getAvailableSlots(
            $doctor->id,
            $request->appointment_date,
            $service->id
        );

        $selectedSlot = collect($slots)->firstWhere('start', $request->appointment_time);
        if (!$selectedSlot) {
            return redirect()->back()->with('error', 'Selected time slot is no longer available.');
        }

        // Get department_id from request (if booking through clinic link) or from doctor
        $departmentId = $request->department_id ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;

        $bookingDob = session($this->publicBookingDobSessionKey());
        if (!$bookingDob) {
            session([$this->publicBookingPendingSessionKey() => [
                'flow' => 'doctor',
                'doctor_id' => $doctor->id,
                'department_id' => $departmentId,
                'service_id' => $service->id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'consultation_type' => $request->consultation_type ?? 'in_person',
            ]]);

            $department = $departmentId ? Department::find($departmentId) : null;

            return view('public-booking.slot-date-of-birth', [
                'flow' => 'doctor',
                'department' => $department,
                'doctor' => $doctor,
                'service' => $service,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'consultation_type' => $request->consultation_type ?? 'in_person',
                'step' => 2,
            ]);
        }

        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service)) {
            return $redirect;
        }

        return view('public-booking.patient-details', [
            'doctor' => $doctor,
            'service' => $service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'consultation_type' => $request->consultation_type ?? 'in_person',
            'department_id' => $departmentId,
            'step' => 3,
            'bookingDobYmd' => $bookingDob,
        ]);
    }

    /**
     * Step 4: Review and confirm (GET - handles direct access/refresh)
     * Try to restore from session or redirect to booking start
     */
    public function showReview(Request $request)
    {
        $this->checkBookingEnabled();

        // Try to restore booking data from session
        $bookingData = session('booking_data');

        if ($bookingData && isset($bookingData['doctor_id'])) {
            // Restore the review page with session data
            $doctor = Doctor::find($bookingData['doctor_id']);
            $service = BookingService::find($bookingData['service_id'] ?? null);

            if ($doctor && $service) {
                $price = $service->getPriceForDoctor($doctor->id);

                return view('public-booking.review', [
                    'doctor' => $doctor,
                    'service' => $service,
                    'appointment_date' => $bookingData['appointment_date'],
                    'appointment_time' => $bookingData['appointment_time'],
                    'patient_data' => $bookingData['patient_data'] ?? [],
                    'price' => $price,
                    'step' => 4
                ]);
            }
        }

        // No valid session data - show error page instead of redirecting
        return response()->view('public-booking.session-expired', [
            'message' => 'Your booking session has expired. Please start a new booking.'
        ]);
    }

    /**
     * Step 5: Confirm appointment (GET - handles direct access/refresh)
     * Try to restore from session or redirect to booking start
     */
    public function showConfirm(Request $request)
    {
        $this->checkBookingEnabled();

        // Check if there's a recent appointment with pending payment
        $bookingAppointmentNumber = session('booking_appointment_number');

        if ($bookingAppointmentNumber) {
            $appointment = \App\Models\Appointment::where('appointment_number', $bookingAppointmentNumber)
                ->with(['billing.invoice'])
                ->first();

            if ($appointment && $appointment->billing && $appointment->billing->invoice) {
                $invoice = $appointment->billing->invoice;

                if ($invoice->payment_token && $invoice->status !== 'paid') {
                    // Redirect to payment page
                    return redirect()->route('public.billing.pay', ['token' => $invoice->payment_token]);
                }
            }

            // Appointment exists but no pending payment - show success
            if ($appointment) {
                return redirect()->route('public.booking.success', [
                    'appointmentNumber' => $appointment->appointment_number
                ]);
            }
        }

        // No valid session data - show error page instead of redirecting
        return response()->view('public-booking.session-expired', [
            'message' => 'Your booking session has expired. Please start a new booking.'
        ]);
    }

    /**
     * Step 4: Review and confirm (POST)
     */
    public function review(Request $request)
    {
        $this->checkBookingEnabled();

        $validator = Validator::make($request->all(), array_merge([
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'consent' => 'required|accepted',
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'required_if:consent_share_with_gp,1|nullable|string|max:255',
            'gp_email' => 'required_if:consent_share_with_gp,1|nullable|email|max:255',
            'gp_phone' => 'required_if:consent_share_with_gp,1|nullable|string|max:20',
            'gp_address' => 'required_if:consent_share_with_gp,1|nullable|string|max:500',
            'notes' => 'required|string|max:10000',
        ], $this->publicBookingAddressValidationRules(), $this->publicBookingGuardianFieldRules($request)));

        // Set default consultation type to in_person (doctors will decide later)
        if (!$request->has('consultation_type') || !$request->consultation_type) {
            $request->merge(['consultation_type' => 'in_person']);
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $doctor = Doctor::findOrFail($request->doctor_id);
        $service = BookingService::findOrFail($request->service_id);

        $dobYmd = parseDateInput($request->date_of_birth);
        if ($redirect = $this->redirectIfServiceIneligibleForPublicBooking($service, $dobYmd)) {
            return $redirect;
        }
        session([$this->publicBookingDobSessionKey() => $dobYmd]);

        // Verify slot is still available
        $slots = $this->slotAvailabilityService->getAvailableSlots(
            $doctor->id,
            $request->appointment_date,
            $service->id
        );

        $selectedSlot = collect($slots)->firstWhere('start', $request->appointment_time);
        if (!$selectedSlot) {
            return redirect()->back()->with('error', 'Selected time slot is no longer available.')->withInput();
        }

        // Calculate price
        $price = $service->getPriceForDoctor($doctor->id);
        
        // Get department_id from request (if booking through clinic link) or from doctor
        $departmentId = $request->department_id ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;
        
        $patientData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'notes', 'consultation_type',
            'gender', 'consent_share_with_gp',
            'gp_name', 'gp_email', 'gp_phone', 'gp_address',
            'address', 'address_line_2', 'city', 'state', 'postal_code', 'country',
            'guardian_name', 'guardian_phone',
        ]);
        
        // Convert date_of_birth from dd/mm/yyyy to Y-m-d format
        if ($request->has('date_of_birth') && $request->date_of_birth) {
            $patientData['date_of_birth'] = parseDateInput($request->date_of_birth);
        }
        if ($departmentId) {
            $patientData['department_id'] = $departmentId;
        }

        // Store booking data in session for page refresh recovery
        session([
            'booking_data' => [
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'patient_data' => $patientData,
                'department_id' => $departmentId,
            ]
        ]);

        return view('public-booking.review', [
            'doctor' => $doctor,
            'service' => $service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'patient_data' => $patientData,
            'price' => $price,
            'step' => 4
        ]);
    }

    /**
     * Step 5: Confirm and create appointment
     */
    public function confirm(Request $request)
    {
        try {
            $this->checkBookingEnabled();
        } catch (\Exception $e) {
            \Log::error('Public booking check failed', ['error' => $e->getMessage()]);
            // Stay on page with error popup instead of redirecting
            return redirect()->back()
                ->with('error', 'Online booking is currently unavailable.')
                ->withInput();
        }

        // Log incoming request for debugging
        \Log::info('Public booking confirm request received', [
            'has_doctor_id' => $request->has('doctor_id'),
            'has_service_id' => $request->has('service_id'),
            'has_csrf' => $request->has('_token'),
            'method' => $request->method(),
            'all_keys' => array_keys($request->all()),
        ]);

        // Prepare validation rules
        $rules = array_merge([
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'required_if:consent_share_with_gp,1|nullable|string|max:255',
            'gp_email' => 'required_if:consent_share_with_gp,1|nullable|email|max:255',
            'gp_phone' => 'required_if:consent_share_with_gp,1|nullable|string|max:20',
            'gp_address' => 'required_if:consent_share_with_gp,1|nullable|string|max:500',
            'notes' => 'required|string|max:10000',
            'discount_code' => 'nullable|string|max:64',
        ], $this->publicBookingAddressValidationRules());
        
        // Require date_of_birth and gender if they are provided (not empty strings)
        // They should always be provided from the patient-details form, but handle edge cases
        if ($request->has('date_of_birth') && trim($request->date_of_birth) !== '') {
            $rules['date_of_birth'] = 'required|date|before_or_equal:today';
        } else {
            // If not provided, make it nullable (for backward compatibility)
            $rules['date_of_birth'] = 'nullable|date|before_or_equal:today';
        }
        
        if ($request->has('gender') && trim($request->gender) !== '') {
            $rules['gender'] = 'required|in:male,female,other';
        } else {
            // If not provided, make it nullable (for backward compatibility)
            $rules['gender'] = 'nullable|in:male,female,other';
        }

        // Set default consultation type to in_person (doctors will decide later)
        if (!$request->has('consultation_type') || !$request->consultation_type) {
            $request->merge(['consultation_type' => 'in_person']);
        }

        // Convert date_of_birth from dd/mm/yyyy to Y-m-d format before validation
        $requestData = $request->all();
        if (isset($requestData['date_of_birth']) && $requestData['date_of_birth']) {
            $requestData['date_of_birth'] = parseDateInput($requestData['date_of_birth']);
            $request->merge(['date_of_birth' => $requestData['date_of_birth']]);
        }

        $rules = array_merge($rules, $this->publicBookingGuardianFieldRules($request));

        $validator = Validator::make($requestData, $rules);

        if ($validator->fails()) {
            \Log::warning('Public booking confirmation validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            
            // Try to redirect back to review page with errors
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        }

        $service = BookingService::findOrFail($request->service_id);
        if ($request->filled('date_of_birth')) {
            $dobYmd = $request->date_of_birth;
            if (!$service->isEligibleForAgeYears(Carbon::parse($dobYmd)->age)) {
                return redirect()->back()
                    ->with('error', 'This service is not available for this age.')
                    ->withInput();
            }
            session([$this->publicBookingDobSessionKey() => $dobYmd]);
        }

        try {
            \Log::info('Starting public booking', [
                'doctor_id' => $request->doctor_id,
                'service_id' => $request->service_id,
                'date' => $request->appointment_date,
                'time' => $request->appointment_time,
                'email' => $request->email,
            ]);

            $result = $this->bookingService->createFromPublicBooking($request->all());
            $appointment = $result['appointment'] ?? null;
            $invoice = $result['invoice'] ?? null;
            $pendingBooking = $result['pending_booking'] ?? null;

            // Clear booking data from session
            session()->forget('booking_data');

            // If this is a paid booking (pending payment), redirect to payment page
            // Patient and appointment will be created after payment
            if ($pendingBooking && $invoice) {
                $invoice->refresh();

                if ($invoice->payment_token) {
                    \Log::info('Redirecting to payment page (patient not created yet)', [
                        'pending_booking_id' => $pendingBooking->id,
                        'invoice_id' => $invoice->id,
                        'token_preview' => substr($invoice->payment_token, 0, 10) . '...'
                    ]);

                    // Store pending booking token in session for payment callback
                    session(['pending_booking_token' => $pendingBooking->booking_token]);

                    $paymentUrl = route('public.billing.pay', ['token' => $invoice->payment_token]);
                    return redirect($paymentUrl);
                } else {
                    \Log::warning('Invoice created but payment token is missing', [
                        'pending_booking_id' => $pendingBooking->id,
                        'invoice_id' => $invoice->id
                    ]);
                }
            }

            // For free services, appointment is created immediately
            if ($appointment) {
                \Log::info('Free booking created successfully', [
                    'appointment_id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                ]);

                // Store appointment number in session for recovery
                session(['booking_appointment_number' => $appointment->appointment_number]);

                return redirect()->route('public.booking.success', [
                    'appointmentNumber' => $appointment->appointment_number
                ]);
            }

            // Fallback - something went wrong
            throw new \Exception('Booking could not be processed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form and try again.');
        } catch (\Exception $e) {
            \Log::error('Public booking failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            // Show detailed error message for debugging
            $errorMsg = 'Failed to create appointment: ' . $e->getMessage();
            if (config('app.debug')) {
                $errorMsg .= ' (Line ' . $e->getLine() . ' in ' . basename($e->getFile()) . ')';
            }

            return redirect()->back()
                ->with('error', $errorMsg)
                ->withInput();
        }
    }

    /**
     * Success page
     */
    public function success($appointmentNumber)
    {
        session()->forget($this->publicBookingDobSessionKey());
        session()->forget($this->publicBookingPendingSessionKey());

        $appointment = \App\Models\Appointment::where('appointment_number', $appointmentNumber)
            ->with(['patient', 'doctor', 'service'])
            ->firstOrFail();

        return view('public-booking.success', [
            'appointment' => $appointment
        ]);
    }

    /**
     * API: Get services for a doctor
     * Only returns services created by the doctor (private services).
     */
    public function getDoctorServices($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        $services = $this->getServicesForDoctor($doctor->id);

        $dobYmd = session($this->publicBookingDobSessionKey());
        if ($dobYmd) {
            $age = Carbon::parse($dobYmd)->age;
            $services = $services->filter(fn (BookingService $s) => $s->isEligibleForAgeYears($age))->values();
        }

        $servicesData = $services->map(function($service) use ($doctor) {
            // Use getPriceForDoctor and getDurationForDoctor to get custom prices if set
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'duration' => $service->getDurationForDoctor($doctor->id) ?? $service->default_duration_minutes ?? 60,
                'price' => $service->getPriceForDoctor($doctor->id) ?? $service->default_price ?? 0,
                'consultation_type' => $service->getConsultationTypeForDoctor($doctor->id),
                'minimum_age' => $service->minimum_age,
                'maximum_age' => $service->maximum_age,
            ];
        });

        return response()->json([
            'services' => $servicesData
        ]);
    }

    /**
     * API: Get available slots for a clinic (union of all doctors in department).
     */
    public function getClinicSlots(Request $request, $departmentId)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|exists:booking_services,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'nullable|integer|min:15|max:480',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $slots = $this->slotAvailabilityService->getAvailableSlotsForDepartment(
            $departmentId,
            $request->date,
            $request->service_id,
            $request->duration
        );

        return response()->json([
            'slots' => $slots,
            'date' => $request->date
        ]);
    }

    /**
     * API: Get available slots for a doctor
     */
    public function getAvailableSlots(Request $request, $doctorId)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|exists:booking_services,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'nullable|integer|min:15|max:480',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $slots = $this->slotAvailabilityService->getAvailableSlots(
            $doctorId,
            $request->date,
            $request->service_id,
            $request->duration
        );

        return response()->json([
            'slots' => $slots,
            'date' => $request->date
        ]);
    }

    /**
     * AJAX: validate discount code on doctor booking review (does not consume a use).
     */
    public function previewDoctorBookingDiscount(Request $request)
    {
        if (Setting::get('public_booking_enabled', '1') != '1') {
            return response()->json(['ok' => false, 'message' => 'Online booking is currently unavailable.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:booking_services,id',
            'discount_code' => 'required|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->bookingService->previewDoctorBookingDiscount(
            (int) $request->doctor_id,
            (int) $request->service_id,
            (string) $request->discount_code
        );

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /**
     * AJAX: validate discount code on clinic booking review (does not consume a use).
     */
    public function previewClinicBookingDiscount(Request $request)
    {
        if (Setting::get('public_booking_enabled', '1') != '1') {
            return response()->json(['ok' => false, 'message' => 'Online booking is currently unavailable.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'service_id' => 'required|exists:booking_services,id',
            'discount_code' => 'required|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->clinicBookingService->previewClinicBookingDiscount(
            (int) $request->department_id,
            (int) $request->service_id,
            (string) $request->discount_code
        );

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /**
     * Get services available for a doctor.
     * Only returns services created by the doctor (private services).
     */
    private function getServicesForDoctor($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        
        // Only get services created by this doctor's user account (order by sort_order)
        return BookingService::where('created_by', $doctor->user_id)
            ->where('is_active', true)
            ->ordered()
            ->get();
    }
}

