<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Billing;
use App\Models\DoctorBookingDiscountCode;
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
use Illuminate\Validation\ValidationException;

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
            $data = array_merge($data, normalize_public_booking_address_fields($data));

            // Get doctor and service first to determine department
            $doctor = Doctor::findOrFail($data['doctor_id']);
            $service = BookingServiceModel::find($data['service_id'] ?? null);

            // Determine the department/clinic for this booking
            $departmentId = $data['department_id'] ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;

            // List price (before discount)
            $listPrice = 0;
            if ($service) {
                $listPrice = (float) ($service->getPriceForDoctor($doctor->id) ?? 0);
            }

            $discountCodeId = null;
            $discountAmount = 0;
            $rawCode = DoctorBookingDiscountCode::normalizeCode((string) ($data['discount_code'] ?? ''));

            if ($listPrice > 0 && $rawCode !== '' && Schema::hasTable('doctor_booking_discount_codes')) {
                $code = DoctorBookingDiscountCode::query()
                    ->where('doctor_id', $doctor->id)
                    ->where('code', $rawCode)
                    ->with('bookingServices')
                    ->lockForUpdate()
                    ->first();

                if (!$code || !$code->isUsableForBooking($service?->id)) {
                    throw ValidationException::withMessages([
                        'discount_code' => ['This discount code is not valid for this booking.'],
                    ]);
                }

                $discountAmount = $code->computeDiscountAmount($listPrice);
                $discountCodeId = $code->id;
            } elseif ($listPrice > 0 && $rawCode !== '' && !Schema::hasTable('doctor_booking_discount_codes')) {
                throw ValidationException::withMessages([
                    'discount_code' => ['Discount codes are not available right now. Please try again without a code.'],
                ]);
            }

            $payableFee = round(max(0, $listPrice - $discountAmount), 2);

            // Free service or fully discounted: no payment needed
            if ($payableFee <= 0) {
                $result = $this->createImmediateBooking($data, $doctor, $service, $departmentId);
                if ($discountCodeId !== null && Schema::hasTable('doctor_booking_discount_codes')) {
                    DoctorBookingDiscountCode::whereKey($discountCodeId)->increment('uses_count');
                }

                return $result;
            }

            // Paid balance: pending booking + invoice
            return $this->createPendingBooking(
                $data,
                $doctor,
                $service,
                $departmentId,
                $listPrice,
                $discountAmount,
                $discountCodeId
            );
        });
    }

    /**
     * Preview a doctor booking discount on the review step (read-only: no locks, no use count).
     *
     * @return array{ok: bool, list_price?: float, discount_amount?: float, amount_due?: float, message?: string}
     */
    public function previewDoctorBookingDiscount(int $doctorId, int $serviceId, string $discountCodeRaw): array
    {
        $rawCode = DoctorBookingDiscountCode::normalizeCode($discountCodeRaw);
        if ($rawCode === '') {
            return ['ok' => false, 'message' => 'Enter a discount code.'];
        }

        if (!Schema::hasTable('doctor_booking_discount_codes')) {
            return ['ok' => false, 'message' => 'Discount codes are not available right now.'];
        }

        $doctor = Doctor::find($doctorId);
        $service = BookingServiceModel::find($serviceId);
        if (!$doctor || !$service) {
            return ['ok' => false, 'message' => 'Invalid booking selection.'];
        }

        $listPrice = (float) ($service->getPriceForDoctor($doctor->id) ?? 0);
        if ($listPrice <= 0) {
            return ['ok' => false, 'message' => 'There is no fee to apply a discount to.'];
        }

        $code = DoctorBookingDiscountCode::query()
            ->where('doctor_id', $doctor->id)
            ->where('code', $rawCode)
            ->with('bookingServices')
            ->first();

        if (!$code || !$code->isUsableForBooking($service->id)) {
            return ['ok' => false, 'message' => 'This discount code is not valid for this booking.'];
        }

        $discountAmount = $code->computeDiscountAmount($listPrice);
        $amountDue = round(max(0, $listPrice - $discountAmount), 2);

        return [
            'ok' => true,
            'list_price' => $listPrice,
            'discount_amount' => $discountAmount,
            'amount_due' => $amountDue,
        ];
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
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'guardian_name' => $data['guardian_name'] ?? null,
            'guardian_phone' => $data['guardian_phone'] ?? null,
        ]);

        // Update patient with additional data
        $this->updatePatientData($patient, $data, $departmentId);

        // Generate appointment number
        $appointmentNumber = $this->generateAppointmentNumber();

        // Create appointment
        $consultationType = $data['consultation_type'] ?? 'in_person';
        $validTypes = ['in_person', 'online', 'telephone'];
        if (!in_array($consultationType, $validTypes, true)) {
            $consultationType = 'in_person';
        }
        $isOnline = $consultationType === 'online';
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
            'consultation_type' => $consultationType,
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
    private function createPendingBooking(
        array $data,
        Doctor $doctor,
        ?BookingServiceModel $service,
        ?int $departmentId,
        float $listPriceFee,
        float $discountAmount,
        ?int $discountCodeId
    ) {
        $payableFee = round(max(0, $listPriceFee - $discountAmount), 2);
        // Store patient data for later creation
        $patientData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
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
            'city' => $patientData['city'] ?? null,
            'state' => $patientData['state'] ?? null,
            'postal_code' => $patientData['postal_code'] ?? null,
            'country' => $patientData['country'] ?? null,
            'guardian_name' => $patientData['guardian_name'] ?? null,
            'guardian_phone' => $patientData['guardian_phone'] ?? null,
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
            'fee' => $payableFee,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(24), // Booking expires in 24 hours
        ]);

        // Create invoice for payment (patient exists even though appointment/billing is deferred)
        $invoicePayload = [
            'billing_id' => null, // No billing yet
            'patient_id' => $patient->id,
            'appointment_id' => null, // No appointment yet
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'subtotal' => $listPriceFee,
            'tax_amount' => 0,
            'discount_amount' => $discountAmount,
            'total_amount' => $payableFee,
            'status' => 'pending',
            'description' => $service ? $service->name : 'Appointment Consultation',
        ];
        if ($discountCodeId !== null && Schema::hasColumn('invoices', 'doctor_booking_discount_code_id')) {
            $invoicePayload['doctor_booking_discount_code_id'] = $discountCodeId;
        }
        $invoice = Invoice::create($invoicePayload);

        // Create invoice item
        $serviceName = $service ? $service->name : 'Appointment Consultation';
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'consultation',
            'item_name' => $serviceName,
            'description' => $serviceName,
            'quantity' => 1,
            'unit_price' => $listPriceFee,
            'total_price' => $listPriceFee,
        ]);

        // Link invoice to pending booking
        $pendingBooking->update(['invoice_id' => $invoice->id]);

        // Generate payment token
        $this->ensurePaymentToken($invoice);

        Log::info('Pending booking created - awaiting payment', [
            'pending_booking_id' => $pendingBooking->id,
            'invoice_id' => $invoice->id,
            'list_price' => $listPriceFee,
            'payable_fee' => $payableFee,
            'discount_amount' => $discountAmount,
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
            $patientData = array_merge(
                $pendingBooking->patient_data ?? [],
                normalize_public_booking_address_fields($pendingBooking->patient_data ?? [])
            );
            $doctor = $pendingBooking->doctor;
            $service = $pendingBooking->service;
            $invoice = $pendingBooking->invoice;

            if ($invoice
                && $invoice->doctor_booking_discount_code_id
                && Schema::hasColumn('invoices', 'discount_code_redemption_recorded_at')
                && $invoice->discount_code_redemption_recorded_at === null
                && Schema::hasTable('doctor_booking_discount_codes')) {
                $discountCode = DoctorBookingDiscountCode::query()
                    ->whereKey($invoice->doctor_booking_discount_code_id)
                    ->lockForUpdate()
                    ->first();
                if ($discountCode) {
                    $discountCode->increment('uses_count');
                }
                $invoice->update(['discount_code_redemption_recorded_at' => now()]);
            }

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
                    'city' => $patientData['city'] ?? null,
                    'state' => $patientData['state'] ?? null,
                    'postal_code' => $patientData['postal_code'] ?? null,
                    'country' => $patientData['country'] ?? null,
                ]);
            }

            // Update patient with additional data
            $this->updatePatientData($patient, $patientData, $pendingBooking->department_id);

            // Generate appointment number
            $appointmentNumber = $this->generateAppointmentNumber();

            // Create appointment
            $useWhereby = $pendingBooking->is_online && $this->wherebyService->isEnabled();
            $consultationType = $patientData['consultation_type'] ?? ($pendingBooking->is_online ? 'online' : 'in_person');
            if (!in_array($consultationType, ['in_person', 'online', 'telephone'], true)) {
                $consultationType = $pendingBooking->is_online ? 'online' : 'in_person';
            }
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
                'consultation_type' => $consultationType,
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

            $billingDiscount = $invoice ? (float) $invoice->discount_amount : 0;
            $billing = Billing::create([
                'bill_number' => Billing::generateBillNumber(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appointment->id,
                'billing_date' => now(),
                'due_date' => now()->addDays(7),
                'type' => 'consultation',
                'description' => $service ? $service->name : 'Appointment Consultation',
                'subtotal' => $invoice ? (float) $invoice->subtotal : $pendingBooking->fee,
                'discount' => $billingDiscount,
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

        if (!empty($data['address'])) {
            $patientUpdateData['address'] = $data['address'];
        }
        if (!empty($data['city'])) {
            $patientUpdateData['city'] = $data['city'];
        }
        if (!empty($data['state'])) {
            $patientUpdateData['state'] = $data['state'];
        }
        if (!empty($data['postal_code'])) {
            $patientUpdateData['postal_code'] = $data['postal_code'];
        }
        if (!empty($data['country'])) {
            $patientUpdateData['country'] = $data['country'];
        }
        if (!empty($data['guardian_name'])) {
            $patientUpdateData['guardian_name'] = $data['guardian_name'];
        }
        if (!empty($data['guardian_phone'])) {
            $patientUpdateData['guardian_phone'] = $data['guardian_phone'];
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
        $appointmentDate = formatDateUk($appointment->appointment_date);
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
