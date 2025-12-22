<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PendingBooking;
use App\Models\BookingService as BookingServiceModel;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\GuestPatientService;
use App\Services\HospitalEmailNotificationService;
use App\Services\WherebyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicBookingService
{
    protected $guestPatientService;
    protected $emailService;
    protected $wherebyService;

    public function __construct(GuestPatientService $guestPatientService, HospitalEmailNotificationService $emailService, WherebyService $wherebyService)
    {
        $this->guestPatientService = $guestPatientService;
        $this->emailService = $emailService;
        $this->wherebyService = $wherebyService;
    }

    /**
     * Create an appointment from public booking.
     * For paid services: Creates a pending booking and invoice, patient is created after payment.
     * For free services: Creates patient and appointment immediately.
     *
     * @param array $data
     * @return array
     */
    public function createFromPublicBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Get doctor and service first to determine department
            $doctor = Doctor::findOrFail($data['doctor_id']);
            $service = BookingServiceModel::find($data['service_id'] ?? null);

            // Determine the department/clinic for this booking
            $departmentId = $data['department_id'] ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;

            // Calculate fee
            $fee = 0;
            if ($service) {
                $fee = $service->getPriceForDoctor($doctor->id) ?? 0;
            }

            // If fee is 0, create patient and appointment immediately (no payment needed)
            if ($fee <= 0) {
                return $this->createImmediateBooking($data, $doctor, $service, $departmentId);
            }

            // For paid services, create pending booking and invoice only
            // Patient and appointment will be created after payment
            return $this->createPendingBooking($data, $doctor, $service, $departmentId, $fee);
        });
    }

    /**
     * Create immediate booking (for free services).
     * Creates patient, appointment, and billing immediately.
     */
    private function createImmediateBooking(array $data, Doctor $doctor, ?BookingServiceModel $service, ?int $departmentId)
    {
        // Find or create patient
        $patient = $this->guestPatientService->findOrCreateGuest([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        // Update patient with additional data
        $this->updatePatientData($patient, $data, $departmentId);

        // Generate appointment number
        $appointmentNumber = $this->generateAppointmentNumber();

        // Create appointment
        $isOnline = isset($data['consultation_type']) && $data['consultation_type'] === 'online';
        $useWhereby = $isOnline && $this->wherebyService->isEnabled();

        $appointmentData = [
            'appointment_number' => $appointmentNumber,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $departmentId,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'type' => $data['type'] ?? 'consultation',
            'status' => 'pending',
            'reason' => $data['reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'fee' => 0,
            'is_online' => $isOnline,
            // If Whereby is enabled and this is an online consult, mark platform up-front so
            // the AppointmentObserver can skip sending an email before the meeting link exists.
            'meeting_platform' => $useWhereby ? 'whereby' : null,
        ];

        if (Schema::hasColumn('appointments', 'service_id')) {
            $appointmentData['service_id'] = $service?->id;
        }
        if (Schema::hasColumn('appointments', 'created_from')) {
            $appointmentData['created_from'] = 'Public Booking Link';
        }

        $appointment = Appointment::create($appointmentData);

        // Create Whereby meeting if online and enabled
        if ($useWhereby) {
            try {
                $this->wherebyService->createMeetingForAppointment($appointment);
                $appointment->refresh(); // Reload to get the updated meeting_link
                Log::info('Whereby meeting created for public booking', [
                    'appointment_id' => $appointment->id,
                    'meeting_link' => $appointment->meeting_link,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create Whereby meeting', ['error' => $e->getMessage()]);
            }
        }

        // Create billing (zero amount, marked as paid)
        $createdBy = User::where('role', 'admin')->orWhere('is_admin', true)->first()?->id ?? 1;

        $billing = Billing::create([
            'bill_number' => Billing::generateBillNumber(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'billing_date' => now(),
            'due_date' => now()->addDays(7),
            'type' => 'consultation',
            'description' => $service ? $service->name : 'Appointment Consultation',
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'balance' => 0,
            'status' => 'paid',
            'created_by' => $createdBy,
        ]);

        // Send notifications
        $this->sendBookingNotifications($appointment, $patient, $doctor);

        return [
            'appointment' => $appointment,
            'invoice' => null,
            'billing' => $billing,
            'pending_booking' => null,
        ];
    }

    /**
     * Create pending booking (for paid services).
     * Creates pending booking record and invoice, but NO patient/appointment yet.
     */
    private function createPendingBooking(array $data, Doctor $doctor, ?BookingServiceModel $service, ?int $departmentId, float $fee)
    {
        // Store patient data for later creation
        $patientData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'notes' => $data['notes'] ?? null,
            'consultation_type' => $data['consultation_type'] ?? 'in_person',
            'consent_share_with_gp' => $data['consent_share_with_gp'] ?? false,
            'gp_name' => $data['gp_name'] ?? null,
            'gp_email' => $data['gp_email'] ?? null,
            'gp_phone' => $data['gp_phone'] ?? null,
            'gp_address' => $data['gp_address'] ?? null,
        ];

        /**
         * IMPORTANT:
         * The `invoices.patient_id` column is NOT NULL in this codebase, so we must
         * have a real patient before creating an invoice.
         *
         * We still keep a PendingBooking record so appointment/billing can be created
         * after payment, but we create (or find) the guest patient up-front.
         */
        $patient = $this->guestPatientService->findOrCreateGuest([
            'first_name' => $patientData['first_name'],
            'last_name' => $patientData['last_name'],
            'email' => $patientData['email'],
            'phone' => $patientData['phone'],
            'date_of_birth' => $patientData['date_of_birth'] ?? null,
            'gender' => $patientData['gender'] ?? null,
            'address' => $patientData['address'] ?? null,
        ]);

        // Update patient with any additional data we captured in the booking flow
        $this->updatePatientData($patient, $patientData, $departmentId);

        // Create pending booking
        $pendingBooking = PendingBooking::create([
            'booking_token' => PendingBooking::generateBookingToken(),
            'doctor_id' => $doctor->id,
            'service_id' => $service?->id,
            'department_id' => $departmentId,
            'appointment_date' => $data['appointment_date'],
            'appointment_time' => $data['appointment_time'],
            'is_online' => isset($data['consultation_type']) && $data['consultation_type'] === 'online',
            'notes' => $data['notes'] ?? null,
            'patient_data' => $patientData,
            'fee' => $fee,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(24), // Booking expires in 24 hours
        ]);

        // Create invoice for payment (patient exists even though appointment/billing is deferred)
        $invoice = Invoice::create([
            'billing_id' => null, // No billing yet
            'patient_id' => $patient->id,
            'appointment_id' => null, // No appointment yet
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => $fee,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $fee,
            'status' => 'pending',
            'description' => $service ? $service->name : 'Appointment Consultation',
        ]);

        // Create invoice item
        $serviceName = $service ? $service->name : 'Appointment Consultation';
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'consultation',
            'item_name' => $serviceName,
            'description' => $serviceName,
            'quantity' => 1,
            'unit_price' => $fee,
            'total_price' => $fee,
        ]);

        // Link invoice to pending booking
        $pendingBooking->update(['invoice_id' => $invoice->id]);

        // Generate payment token
        $this->ensurePaymentToken($invoice);

        Log::info('Pending booking created - awaiting payment', [
            'pending_booking_id' => $pendingBooking->id,
            'invoice_id' => $invoice->id,
            'fee' => $fee,
            'patient_email' => $patientData['email'],
        ]);

        return [
            'appointment' => null, // No appointment yet
            'invoice' => $invoice,
            'billing' => null, // No billing yet
            'pending_booking' => $pendingBooking,
        ];
    }

    /**
     * Finalize booking after payment is completed.
     * Creates patient, appointment, billing from pending booking data.
     *
     * @param PendingBooking $pendingBooking
     * @return array
     */
    public function finalizeBookingAfterPayment(PendingBooking $pendingBooking)
    {
        if ($pendingBooking->status !== 'pending_payment') {
            throw new \Exception('Booking is not in pending payment status');
        }

        if ($pendingBooking->isExpired()) {
            $pendingBooking->markExpired();
            throw new \Exception('Booking has expired');
        }

        return DB::transaction(function () use ($pendingBooking) {
            $patientData = $pendingBooking->patient_data;
            $doctor = $pendingBooking->doctor;
            $service = $pendingBooking->service;
            $invoice = $pendingBooking->invoice;

            // Prefer patient already linked to invoice (new flow); fallback to old flow if missing
            $patient = null;
            if ($invoice && !empty($invoice->patient_id)) {
                $patient = Patient::find($invoice->patient_id);
            }
            if (!$patient) {
                $patient = $this->guestPatientService->findOrCreateGuest([
                    'first_name' => $patientData['first_name'],
                    'last_name' => $patientData['last_name'],
                    'email' => $patientData['email'],
                    'phone' => $patientData['phone'],
                    'date_of_birth' => $patientData['date_of_birth'] ?? null,
                    'gender' => $patientData['gender'] ?? null,
                    'address' => $patientData['address'] ?? null,
                ]);
            }

            // Update patient with additional data
            $this->updatePatientData($patient, $patientData, $pendingBooking->department_id);

            // Generate appointment number
            $appointmentNumber = $this->generateAppointmentNumber();

            // Create appointment
            $useWhereby = $pendingBooking->is_online && $this->wherebyService->isEnabled();
            $appointmentData = [
                'appointment_number' => $appointmentNumber,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $pendingBooking->department_id,
                'appointment_date' => $pendingBooking->appointment_date,
                'appointment_time' => $pendingBooking->appointment_time,
                'type' => 'consultation',
                'status' => 'confirmed', // Auto-confirm since payment is done
                'notes' => $pendingBooking->notes,
                'fee' => $pendingBooking->fee,
                'is_online' => $pendingBooking->is_online,
                // Same reasoning as above: ensure observer doesn't email before meeting link exists.
                'meeting_platform' => $useWhereby ? 'whereby' : null,
            ];

            if (Schema::hasColumn('appointments', 'service_id')) {
                $appointmentData['service_id'] = $service?->id;
            }
            if (Schema::hasColumn('appointments', 'created_from')) {
                $appointmentData['created_from'] = 'Public Booking Link';
            }

            $appointment = Appointment::create($appointmentData);

            // Create Whereby meeting if online and enabled
            if ($useWhereby) {
                try {
                    $this->wherebyService->createMeetingForAppointment($appointment);
                    $appointment->refresh(); // Reload to get the updated meeting_link
                    Log::info('Whereby meeting created for paid booking', [
                        'appointment_id' => $appointment->id,
                        'meeting_link' => $appointment->meeting_link,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create Whereby meeting', ['error' => $e->getMessage()]);
                }
            }

            // Create billing
            $createdBy = User::where('role', 'admin')->orWhere('is_admin', true)->first()?->id ?? 1;

            $billing = Billing::create([
                'bill_number' => Billing::generateBillNumber(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appointment->id,
                'billing_date' => now(),
                'due_date' => now()->addDays(7),
                'type' => 'consultation',
                'description' => $service ? $service->name : 'Appointment Consultation',
                'subtotal' => $pendingBooking->fee,
                'discount' => 0,
                'tax' => 0,
                'total_amount' => $pendingBooking->fee,
                'paid_amount' => $pendingBooking->fee,
                'balance' => 0,
                'status' => 'paid',
                'created_by' => $createdBy,
                'payment_method' => 'card',
                'payment_reference' => 'ONLINE_PAYMENT',
                'paid_at' => now(),
            ]);

            // Update invoice with patient and appointment IDs
            if ($invoice) {
                $invoice->update([
                    'patient_id' => $patient->id,
                    'appointment_id' => $appointment->id,
                    'billing_id' => $billing->id,
                    'status' => 'paid',
                    'paid_date' => now(),
                ]);
            }

            // Mark pending booking as completed
            $pendingBooking->markCompleted();

            // Send notifications
            $this->sendBookingNotifications($appointment, $patient, $doctor);

            Log::info('Booking finalized after payment', [
                'pending_booking_id' => $pendingBooking->id,
                'appointment_id' => $appointment->id,
                'patient_id' => $patient->id,
            ]);

            return [
                'appointment' => $appointment,
                'patient' => $patient,
                'billing' => $billing,
                'invoice' => $invoice,
            ];
        });
    }

    /**
     * Find pending booking by invoice.
     */
    public function findPendingBookingByInvoice(Invoice $invoice): ?PendingBooking
    {
        return PendingBooking::where('invoice_id', $invoice->id)
            ->where('status', 'pending_payment')
            ->first();
    }

    /**
     * Update patient with additional data.
     */
    private function updatePatientData(Patient $patient, array $data, ?int $departmentId)
    {
        $patientUpdateData = [];

        if (!empty($data['date_of_birth'])) {
            $patientUpdateData['date_of_birth'] = $data['date_of_birth'];
        }
        if (!empty($data['gender'])) {
            $patientUpdateData['gender'] = $data['gender'];
        }

        // Handle GP consent
        if (!empty($data['consent_share_with_gp'])) {
            $patientUpdateData['consent_share_with_gp'] = true;
            if (!empty($data['gp_name'])) $patientUpdateData['gp_name'] = $data['gp_name'];
            if (!empty($data['gp_email'])) $patientUpdateData['gp_email'] = $data['gp_email'];
            if (!empty($data['gp_phone'])) $patientUpdateData['gp_phone'] = $data['gp_phone'];
            if (!empty($data['gp_address'])) $patientUpdateData['gp_address'] = $data['gp_address'];
        }

        // Assign to department
        if ($departmentId && !$patient->department_id) {
            $patientUpdateData['department_id'] = $departmentId;
        }

        if (!empty($patientUpdateData)) {
            $patient->update($patientUpdateData);
        }

        // Attach to departments pivot table
        if ($departmentId && !$patient->departments()->where('departments.id', $departmentId)->exists()) {
            $hasPrimary = $patient->departments()->wherePivot('is_primary', true)->exists();
            $patient->departments()->attach($departmentId, [
                'is_primary' => !$hasPrimary,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Ensure invoice has a payment token.
     */
    private function ensurePaymentToken(Invoice $invoice)
    {
        $invoice->generatePaymentToken();
        $invoice->refresh();

        if (empty($invoice->payment_token)) {
            try {
                $manualToken = bin2hex(random_bytes(32));
                $invoice->update([
                    'payment_token' => $manualToken,
                    'payment_token_expires_at' => now()->addDays(90),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to set payment token', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Send booking notifications.
     */
    private function sendBookingNotifications(Appointment $appointment, Patient $patient, Doctor $doctor)
    {
        // Send confirmation email to patient
        try {
            $this->emailService->sendAppointmentConfirmation($appointment);
        } catch (\Exception $e) {
            Log::error('Failed to send appointment confirmation email', ['error' => $e->getMessage()]);
        }

        // Send notification to doctor
        try {
            $this->emailService->notifyDoctorNewAppointment($appointment, $doctor);
        } catch (\Exception $e) {
            Log::error('Failed to send doctor notification email', ['error' => $e->getMessage()]);
        }

        // Create in-app notifications
        try {
            $this->createPublicBookingNotifications($appointment, $patient);
        } catch (\Exception $e) {
            Log::error('Failed to create public booking notifications', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate unique appointment number.
     */
    private function generateAppointmentNumber()
    {
        do {
            $number = 'A' . date('Ymd') . strtoupper(Str::random(4));
        } while (Appointment::where('appointment_number', $number)->exists());

        return $number;
    }

    /**
     * Create notifications for new pending public booking.
     */
    private function createPublicBookingNotifications(Appointment $appointment, Patient $patient)
    {
        $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y');
        $appointmentTime = \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A');

        $notificationData = [
            'type' => UserNotification::TYPE_APPOINTMENT,
            'category' => UserNotification::CATEGORY_APPOINTMENT,
            'title' => 'New Public Booking - Payment Completed',
            'message' => "New appointment booking from {$patientName} on {$appointmentDate} at {$appointmentTime}.",
            'priority' => 'high',
            'action_url' => route('staff.appointments.show', $appointment->id),
            'related_appointment_id' => $appointment->id,
            'related_patient_id' => $patient->id,
            'related_doctor_id' => $appointment->doctor_id,
            'data' => [
                'appointment_number' => $appointment->appointment_number,
                'source' => 'public_booking',
                'is_guest' => $patient->is_guest ?? false,
            ],
        ];

        // Notify all admin users
        $adminUsers = User::where(function($query) {
            $query->where('is_admin', true)->orWhere('role', 'admin');
        })->where('is_active', true)->get();

        foreach ($adminUsers as $admin) {
            UserNotification::create(array_merge($notificationData, ['user_id' => $admin->id]));
        }

        // Notify the doctor
        if ($appointment->doctor && $appointment->doctor->user_id) {
            $doctorUser = User::find($appointment->doctor->user_id);
            if ($doctorUser && $doctorUser->is_active) {
                UserNotification::create(array_merge($notificationData, [
                    'user_id' => $doctorUser->id,
                    'title' => 'New Appointment Booking',
                    'message' => "You have a new appointment booking from {$patientName} on {$appointmentDate} at {$appointmentTime}.",
                ]));
            }
        }

        // Notify department staff
        if ($appointment->department_id) {
            $departmentStaff = User::where('department_id', $appointment->department_id)
                ->where('is_active', true)
                ->where('role', '!=', 'admin')
                ->where(function($q) { $q->where('is_admin', false)->orWhereNull('is_admin'); })
                ->get();

            foreach ($departmentStaff as $staff) {
                if ($appointment->doctor && $appointment->doctor->user_id == $staff->id) continue;

                UserNotification::create(array_merge($notificationData, [
                    'user_id' => $staff->id,
                    'title' => 'New Public Booking in Your Department',
                    'message' => "New appointment booking from {$patientName} in your department on {$appointmentDate} at {$appointmentTime}.",
                ]));
            }
        }
    }
}
