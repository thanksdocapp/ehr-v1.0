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
        
        // Get active services for this doctor
        $services = $this->getServicesForDoctor($doctor->id);

        return view('public-booking.service-selection', [
            'doctor' => $doctor,
            'services' => $services,
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
        
        // Get active services for this department
        $services = BookingService::active()->get();

        return view('public-booking.service-selection', [
            'department' => $department,
            'services' => $services,
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
            'department_id' => $departmentId,
            'step' => 3
        ]);
    }

    /**
     * Step 4: Review and confirm (GET - handles direct access/refresh)
     * Prevents 404 when /book/review is accessed via GET
     */
    public function showReview(Request $request)
    {
        $this->checkBookingEnabled();
        
        // Review page should only be accessible via POST with form data
        // If accessed via GET, redirect to homepage or show 404
        abort(404, 'Review page can only be accessed after completing the booking form.');
    }

    /**
     * Step 5: Confirm appointment (GET - handles direct access/refresh)
     * Prevents 404 when /book/confirm is accessed via GET
     */
    public function showConfirm(Request $request)
    {
        $this->checkBookingEnabled();
        
        // Confirm page should only be accessible via POST with form data
        // If accessed via GET, redirect to homepage or show 404
        abort(404, 'Confirmation can only be done after completing the booking form.');
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
        $this->checkBookingEnabled();

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
            $result = $this->bookingService->createFromPublicBooking($request->all());
            $appointment = $result['appointment'];
            $invoice = $result['invoice'] ?? null;

            // If invoice exists and has payment token, redirect to payment
            if ($invoice) {
                // Refresh invoice to ensure we have the latest payment_token
                $invoice->refresh();
                
                if ($invoice->payment_token) {
                    \Log::info('Redirecting to payment page', [
                        'invoice_id' => $invoice->id,
                        'appointment_id' => $appointment->id,
                        'token_preview' => substr($invoice->payment_token, 0, 10) . '...'
                    ]);
                    
                    $paymentUrl = route('public.billing.pay', ['token' => $invoice->payment_token]);
                    
                    return redirect($paymentUrl)
                        ->with('booking_appointment_number', $appointment->appointment_number);
                } else {
                    \Log::warning('Invoice created but payment token is missing', [
                        'invoice_id' => $invoice->id,
                        'appointment_id' => $appointment->id
                    ]);
                }
            }

            // Otherwise, go directly to success
            return redirect()->route('public.booking.success', [
                'appointment_number' => $appointment->appointment_number
            ]);
        } catch (\Exception $e) {
            \Log::error('Public booking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create appointment. Please try again.')
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
     * API: Get available slots for a doctor
     */
    public function getAvailableSlots(Request $request, $doctorId)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|exists:booking_services,id',
            'date' => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $slots = $this->slotAvailabilityService->getAvailableSlots(
            $doctorId,
            $request->date,
            $request->service_id
        );

        return response()->json([
            'slots' => $slots,
            'date' => $request->date
        ]);
    }

    /**
     * Get services available for a doctor.
     */
    private function getServicesForDoctor($doctorId)
    {
        // Get all active global services
        $globalServices = BookingService::active()->get();

        // Filter services that are available for this doctor
        return $globalServices->filter(function ($service) use ($doctorId) {
            return $service->isAvailableForDoctor($doctorId);
        });
    }
}

