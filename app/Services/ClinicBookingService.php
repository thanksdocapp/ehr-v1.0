<?php

namespace App\Services;

use App\Models\ClinicBookingRequest;
use App\Models\ClinicBookingDiscountCode;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PendingClinicBooking;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\BookingService;
use App\Services\GuestPatientService;
use App\Services\HospitalEmailNotificationService;
use App\Services\WherebyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ClinicBookingService
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
     * Create a pending clinic booking with invoice (for paid services).
     * Patient pays first; after payment, ClinicBookingRequest is created.
     * If a discount code reduces the balance to zero, creates the clinic request immediately (no invoice).
     *
     * @return array{invoice: ?Invoice, pending_clinic_booking: ?PendingClinicBooking, clinic_request?: ClinicBookingRequest}
     */
    public function createPendingFromClinicBooking(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $data = array_merge($data, normalize_public_booking_address_fields($data));

            $departmentId = $data['department_id'];
            $service = BookingService::find($data['service_id'] ?? null);

            $listPrice = 0;
            if ($service) {
                $doctors = Doctor::byDepartment($departmentId)->active()->get();
                $prices = $doctors->map(fn($d) => $service->getPriceForDoctor($d->id) ?? $service->default_price ?? 0)->filter();
                $listPrice = $prices->isEmpty() ? (float) ($service->default_price ?? 0) : (float) $prices->min();
            }

            $discountCodeId = null;
            $discountAmount = 0;
            $rawCode = ClinicBookingDiscountCode::normalizeCode((string) ($data['discount_code'] ?? ''));

            if ($listPrice > 0 && $rawCode !== '' && Schema::hasTable('clinic_booking_discount_codes')) {
                $code = ClinicBookingDiscountCode::query()
                    ->where('department_id', $departmentId)
                    ->where('code', $rawCode)
                    ->lockForUpdate()
                    ->first();

                if (!$code || !$code->isUsableForBooking($service?->id)) {
                    throw ValidationException::withMessages([
                        'discount_code' => ['This discount code is not valid for this booking.'],
                    ]);
                }

                $discountAmount = $code->computeDiscountAmount($listPrice);
                $discountCodeId = $code->id;
            } elseif ($listPrice > 0 && $rawCode !== '' && !Schema::hasTable('clinic_booking_discount_codes')) {
                throw ValidationException::withMessages([
                    'discount_code' => ['Discount codes are not available right now. Please try again without a code.'],
                ]);
            }

            $payableFee = round(max(0, $listPrice - $discountAmount), 2);

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
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
            ];

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

            if ($payableFee <= 0) {
                $clinicRequest = $this->createFromClinicBooking($data);
                if ($discountCodeId !== null && Schema::hasTable('clinic_booking_discount_codes')) {
                    ClinicBookingDiscountCode::whereKey($discountCodeId)->increment('uses_count');
                }

                return [
                    'invoice' => null,
                    'pending_clinic_booking' => null,
                    'clinic_request' => $clinicRequest,
                ];
            }

            $pendingBooking = PendingClinicBooking::create([
                'booking_token' => PendingClinicBooking::generateBookingToken(),
                'department_id' => $departmentId,
                'service_id' => $service?->id,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'notes' => $data['notes'] ?? null,
                'patient_data' => $patientData,
                'fee' => $payableFee,
                'status' => 'pending_payment',
                'expires_at' => now()->addHours(24),
            ]);

            $serviceName = $service ? $service->name : 'Clinic Consultation';
            $invoicePayload = [
                'billing_id' => null,
                'patient_id' => $patient->id,
                'appointment_id' => null,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now()->addDays(7),
                'subtotal' => $listPrice,
                'tax_amount' => 0,
                'discount_amount' => $discountAmount,
                'total_amount' => $payableFee,
                'status' => 'pending',
                'description' => $serviceName,
            ];
            if ($discountCodeId !== null && Schema::hasColumn('invoices', 'clinic_booking_discount_code_id')) {
                $invoicePayload['clinic_booking_discount_code_id'] = $discountCodeId;
            }
            $invoice = Invoice::create($invoicePayload);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'consultation',
                'item_name' => $serviceName,
                'description' => $serviceName,
                'quantity' => 1,
                'unit_price' => $listPrice,
                'total_price' => $listPrice,
            ]);

            $pendingBooking->update(['invoice_id' => $invoice->id]);
            $invoice->generatePaymentToken();
            $invoice->refresh();

            Log::info('Pending clinic booking created - awaiting payment', [
                'pending_clinic_booking_id' => $pendingBooking->id,
                'invoice_id' => $invoice->id,
                'list_price' => $listPrice,
                'payable_fee' => $payableFee,
                'discount_amount' => $discountAmount,
            ]);

            return [
                'invoice' => $invoice,
                'pending_clinic_booking' => $pendingBooking,
            ];
        });
    }

    /**
     * Preview a clinic booking discount on the review step (read-only: no locks, no use count).
     *
     * @return array{ok: bool, list_price?: float, discount_amount?: float, amount_due?: float, message?: string}
     */
    public function previewClinicBookingDiscount(int $departmentId, int $serviceId, string $discountCodeRaw): array
    {
        $rawCode = ClinicBookingDiscountCode::normalizeCode($discountCodeRaw);
        if ($rawCode === '') {
            return ['ok' => false, 'message' => 'Enter a discount code.'];
        }

        if (!Schema::hasTable('clinic_booking_discount_codes')) {
            return ['ok' => false, 'message' => 'Discount codes are not available right now.'];
        }

        $service = BookingService::find($serviceId);
        if (!$service) {
            return ['ok' => false, 'message' => 'Invalid booking selection.'];
        }

        $doctors = Doctor::byDepartment($departmentId)->active()->get();
        $prices = $doctors->map(fn($d) => $service->getPriceForDoctor($d->id) ?? $service->default_price ?? 0)->filter();
        $listPrice = $prices->isEmpty() ? (float) ($service->default_price ?? 0) : (float) $prices->min();

        if ($listPrice <= 0) {
            return ['ok' => false, 'message' => 'There is no fee to apply a discount to.'];
        }

        $code = ClinicBookingDiscountCode::query()
            ->where('department_id', $departmentId)
            ->where('code', $rawCode)
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
     * Finalize clinic booking after payment. Creates ClinicBookingRequest.
     */
    public function finalizeClinicBookingAfterPayment(PendingClinicBooking $pending): ClinicBookingRequest
    {
        if ($pending->status !== 'pending_payment') {
            throw new \Exception('Clinic booking is not in pending payment status');
        }

        if ($pending->isExpired()) {
            $pending->markExpired();
            throw new \Exception('Clinic booking has expired');
        }

        $timeStr = $pending->appointment_time instanceof \DateTimeInterface
            ? $pending->appointment_time->format('H:i')
            : substr((string) $pending->appointment_time, 0, 5);

        $pd = $pending->patient_data ?? [];
        $data = [
            'department_id' => $pending->department_id,
            'service_id' => $pending->service_id,
            'appointment_date' => $pending->appointment_date->format('Y-m-d'),
            'appointment_time' => $timeStr,
            'first_name' => $pd['first_name'] ?? '',
            'last_name' => $pd['last_name'] ?? '',
            'email' => $pd['email'] ?? '',
            'phone' => $pd['phone'] ?? '',
            'date_of_birth' => $pd['date_of_birth'] ?? null,
            'gender' => $pd['gender'] ?? null,
            'address' => $pd['address'] ?? null,
            'address_line_2' => $pd['address_line_2'] ?? null,
            'city' => $pd['city'] ?? null,
            'state' => $pd['state'] ?? null,
            'postal_code' => $pd['postal_code'] ?? null,
            'country' => $pd['country'] ?? null,
            'notes' => $pd['notes'] ?? null,
            'consultation_type' => $pd['consultation_type'] ?? 'in_person',
            'consent_share_with_gp' => $pd['consent_share_with_gp'] ?? false,
            'gp_name' => $pd['gp_name'] ?? null,
            'gp_email' => $pd['gp_email'] ?? null,
            'gp_phone' => $pd['gp_phone'] ?? null,
            'gp_address' => $pd['gp_address'] ?? null,
            'guardian_name' => $pd['guardian_name'] ?? null,
            'guardian_phone' => $pd['guardian_phone'] ?? null,
        ];

        return DB::transaction(function () use ($pending, $data) {
            $invoice = $pending->invoice;

            if ($invoice
                && $invoice->clinic_booking_discount_code_id
                && Schema::hasColumn('invoices', 'discount_code_redemption_recorded_at')
                && $invoice->discount_code_redemption_recorded_at === null
                && Schema::hasTable('clinic_booking_discount_codes')) {
                $discountCode = ClinicBookingDiscountCode::query()
                    ->whereKey($invoice->clinic_booking_discount_code_id)
                    ->lockForUpdate()
                    ->first();
                if ($discountCode) {
                    $discountCode->increment('uses_count');
                }
                $invoice->update(['discount_code_redemption_recorded_at' => now()]);
            }

            $clinicRequest = $this->createFromClinicBooking($data);
            $pending->markCompleted();

            return $clinicRequest;
        });
    }

    /**
     * Create a clinic booking request (pending doctor acceptance).
     */
    public function createFromClinicBooking(array $data): ClinicBookingRequest
    {
        $request = DB::transaction(function () use ($data) {
            $departmentId = $data['department_id'];
            $service = BookingService::find($data['service_id'] ?? null);

            // Use minimum price across doctors for display; actual fee set when doctor accepts
            $fee = 0;
            if ($service) {
                $doctors = Doctor::byDepartment($departmentId)->active()->get();
                $prices = $doctors->map(fn($d) => $service->getPriceForDoctor($d->id) ?? $service->default_price ?? 0)->filter();
                $fee = $prices->isEmpty() ? ($service->default_price ?? 0) : $prices->min();
            }

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
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_phone' => $data['guardian_phone'] ?? null,
            ];

            $request = ClinicBookingRequest::create([
                'request_number' => ClinicBookingRequest::generateRequestNumber(),
                'department_id' => $departmentId,
                'service_id' => $service?->id,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'consultation_type' => $data['consultation_type'] ?? 'in_person',
                'fee' => $fee,
                'notes' => $data['notes'] ?? null,
                'patient_data' => $patientData,
                'status' => 'pending_acceptance',
                'created_from' => 'Public Clinic Booking',
            ]);

            $this->notifyDoctorsOfNewRequest($request);

            return $request;
        });

        try {
            $this->emailService->notifyClinicDoctorsNewBookingRequest($request);
        } catch (\Throwable $e) {
            Log::error('Clinic booking request doctor emails failed', [
                'clinic_booking_request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $request;
    }

    /**
     * Doctor accepts a clinic booking request. Creates patient + appointment, marks request as accepted.
     */
    public function acceptRequest(ClinicBookingRequest $request, Doctor $doctor, ?int $acceptedByUserId = null): Appointment
    {
        return DB::transaction(function () use ($request, $doctor, $acceptedByUserId) {
            // Lock and verify still pending (use fresh lock)
            $request = ClinicBookingRequest::where('id', $request->id)
                ->where('status', 'pending_acceptance')
                ->lockForUpdate()
                ->firstOrFail();
            if ($request->status !== 'pending_acceptance') {
                throw new \RuntimeException('This booking has already been accepted by another doctor.');
            }

            $patientData = array_merge(
                $request->patient_data ?? [],
                normalize_public_booking_address_fields($request->patient_data ?? [])
            );
            $service = $request->service;

            // Find or create patient
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

            // Assign patient to the clinic so all doctors in the clinic can view and access them
            if (!$patient->departments()->where('departments.id', $request->department_id)->exists()) {
                $isPrimary = $patient->departments()->count() === 0;
                $patient->departments()->attach($request->department_id, ['is_primary' => $isPrimary]);
            }
            if (!$patient->department_id) {
                $patient->department_id = $request->department_id;
                $patient->save();
            }
            if (!$patient->created_by_doctor_id) {
                $patient->created_by_doctor_id = $doctor->id;
                $patient->save();
            }

            $fee = $service ? $service->getPriceForDoctor($doctor->id) : 0;

            $isOnline = ($request->consultation_type ?? 'in_person') === 'online';
            $useWhereby = $isOnline && $this->wherebyService->isEnabled();

            $appointment = Appointment::create([
                'appointment_number' => Appointment::generateAppointmentNumber(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $request->department_id,
                'service_id' => $request->service_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'type' => 'consultation',
                'status' => 'pending',
                'fee' => $fee,
                'is_online' => $isOnline,
                'consultation_type' => $request->consultation_type ?? 'in_person',
                'notes' => $request->notes,
                'created_from' => 'Clinic Booking (Doctor Accepted)',
                // Set meeting_platform so Observer skips email until we have the meeting link
                'meeting_platform' => $useWhereby ? 'whereby' : null,
            ]);

            // Create Whereby meeting for online consultations so video link is in confirmation email
            if ($useWhereby) {
                try {
                    $this->wherebyService->createMeetingForAppointment($appointment);
                    $appointment->refresh();
                    Log::info('Whereby meeting created for clinic booking', [
                        'appointment_id' => $appointment->id,
                        'meeting_link' => $appointment->meeting_link,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create Whereby meeting for clinic booking', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $acceptedPayload = [
                'status' => 'accepted',
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
            ];
            if (Schema::hasColumn('clinic_booking_requests', 'accepted_by_user_id')) {
                $acceptedPayload['accepted_by_user_id'] = $acceptedByUserId;
            }
            if (Schema::hasColumn('clinic_booking_requests', 'accepted_at')) {
                $acceptedPayload['accepted_at'] = now();
            }
            $request->update($acceptedPayload);

            $this->emailService->sendAppointmentConfirmation($appointment);

            if ($doctor->user_id || $doctor->email) {
                try {
                    $this->emailService->notifyDoctorNewAppointment($appointment, $doctor);
                } catch (\Exception $e) {
                    Log::error('Failed to send doctor notification for clinic booking', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Clinic booking accepted', [
                'request_id' => $request->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appointment->id,
            ]);

            return $appointment;
        });
    }

    protected function notifyDoctorsOfNewRequest(ClinicBookingRequest $request): void
    {
        $request->load('department');
        $deptName = $request->department?->name ?? 'the clinic';
        $dateStr = $request->appointment_date->format('d/m/Y');
        $timeStr = $request->appointment_time instanceof \DateTimeInterface
            ? $request->appointment_time->format('H:i')
            : substr((string) $request->appointment_time, 0, 5);

        $doctors = Doctor::byDepartment($request->department_id)->active()->get();
        foreach ($doctors as $doctor) {
            if ($doctor->user_id) {
                \App\Models\UserNotification::create([
                    'user_id' => $doctor->user_id,
                    'type' => 'clinic_booking_request',
                    'title' => 'New clinic booking request',
                    'message' => "A patient has requested an appointment at {$deptName} on {$dateStr} at {$timeStr}. Accept to add to your schedule.",
                    'data' => ['clinic_booking_request_id' => $request->id],
                    'read_at' => null,
                ]);
            }
        }
    }
}
