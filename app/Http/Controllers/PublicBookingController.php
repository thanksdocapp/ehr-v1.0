<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\BookingService;
use App\Models\Setting;
use App\Services\SlotAvailabilityService;
use App\Services\PublicBookingService;
use Illuminate\Support\Facades\Validator;

class PublicBookingController extends Controller
{
    protected $slotAvailabilityService;
    protected $bookingService;

    public function __construct(SlotAvailabilityService $slotAvailabilityService, PublicBookingService $bookingService)
    {
        $this->slotAvailabilityService = $slotAvailabilityService;
        $this->bookingService = $bookingService;
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
            'step' => 1
        ]);
    }

    /**
     * Step 1: Access via clinic link - /book/clinic/{clinicSlug}
     */
    public function showClinicBooking($slug)
    {
        $this->checkBookingEnabled();

        $department = Department::where('slug', $slug)->active()->firstOrFail();
        
        // Get active doctors for this department
        $doctors = Doctor::byDepartment($department->id)
            ->active()
            ->get();

        return view('public-booking.service-selection', [
            'department' => $department,
            'doctors' => $doctors,
            'step' => 1
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
        
        return view('public-booking.patient-details', [
            'doctor' => $doctor,
            'service' => $service,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'consultation_type' => $request->consultation_type ?? 'in_person',
            'department_id' => $departmentId,
            'step' => 3
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

        $validator = Validator::make($request->all(), [
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
            'consultation_type' => 'required|in:in_person,online',
            'consent' => 'required|accepted',
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'required_if:consent_share_with_gp,1|nullable|string|max:255',
            'gp_email' => 'required_if:consent_share_with_gp,1|nullable|email|max:255',
            'gp_phone' => 'required_if:consent_share_with_gp,1|nullable|string|max:20',
            'gp_address' => 'required_if:consent_share_with_gp,1|nullable|string|max:500',
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
            return redirect()->back()->with('error', 'Selected time slot is no longer available.')->withInput();
        }

        // Calculate price
        $price = $service->getPriceForDoctor($doctor->id);
        
        // Get department_id from request (if booking through clinic link) or from doctor
        $departmentId = $request->department_id ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;
        
        $patientData = $request->only([
            'first_name', 'last_name', 'email', 'phone', 'notes', 'consultation_type',
            'date_of_birth', 'gender', 'consent_share_with_gp',
            'gp_name', 'gp_email', 'gp_phone', 'gp_address'
        ]);
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
        $rules = [
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'required|exists:booking_services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'consultation_type' => 'required|in:in_person,online',
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'required_if:consent_share_with_gp,1|nullable|string|max:255',
            'gp_email' => 'required_if:consent_share_with_gp,1|nullable|email|max:255',
            'gp_phone' => 'required_if:consent_share_with_gp,1|nullable|string|max:20',
            'gp_address' => 'required_if:consent_share_with_gp,1|nullable|string|max:500',
        ];
        
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

        $validator = Validator::make($request->all(), $rules);

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
        
        $servicesData = $services->map(function($service) use ($doctor) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'duration' => $service->default_duration_minutes ?? 60,
                'price' => $service->default_price ?? 0,
            ];
        });

        return response()->json([
            'services' => $servicesData
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
     * Get services available for a doctor.
     * Only returns services created by the doctor (private services).
     */
    private function getServicesForDoctor($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        
        // Only get services created by this doctor's user account
        return BookingService::where('created_by', $doctor->user_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}

