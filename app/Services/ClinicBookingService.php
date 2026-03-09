<?php

namespace App\Services;

use App\Models\ClinicBookingRequest;
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
     *
     * @return array{invoice: Invoice, pending_clinic_booking: PendingClinicBooking}
     */
    public function createPendingFromClinicBooking(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $departmentId = $data['department_id'];
            $service = BookingService::find($data['service_id'] ?? null);

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
                'notes' => $data['notes'] ?? null,
                'consultation_type' => $data['consultation_type'] ?? 'in_person',
                'consent_share_with_gp' => $data['consent_share_with_gp'] ?? false,
                'gp_name' => $data['gp_name'] ?? null,
                'gp_email' => $data['gp_email'] ?? null,
                'gp_phone' => $data['gp_phone'] ?? null,
                'gp_address' => $data['gp_address'] ?? null,
            ];

            $patient = $this->guestPatientService->findOrCreateGuest([
                'first_name' => $patientData['first_name'],
                'last_name' => $patientData['last_name'],
                'email' => $patientData['email'],
                'phone' => $patientData['phone'],
                'date_of_birth' => $patientData['date_of_birth'] ?? null,
                'gender' => $patientData['gender'] ?? null,
                'address' => $patientData['address'] ?? null,
            ]);

            $pendingBooking = PendingClinicBooking::create([
                'booking_token' => PendingClinicBooking::generateBookingToken(),
                'department_id' => $departmentId,
                'service_id' => $service?->id,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'notes' => $data['notes'] ?? null,
                'patient_data' => $patientData,
                'fee' => $fee,
                'status' => 'pending_payment',
                'expires_at' => now()->addHours(24),
            ]);

            $serviceName = $service ? $service->name : 'Clinic Consultation';
            $invoice = Invoice::create([
                'billing_id' => null,
                'patient_id' => $patient->id,
                'appointment_id' => null,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now()->addDays(7),
                'subtotal' => $fee,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $fee,
                'status' => 'pending',
                'description' => $serviceName,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'consultation',
                'item_name' => $serviceName,
                'description' => $serviceName,
                'quantity' => 1,
                'unit_price' => $fee,
                'total_price' => $fee,
            ]);

            $pendingBooking->update(['invoice_id' => $invoice->id]);
            $invoice->generatePaymentToken();
            $invoice->refresh();

            Log::info('Pending clinic booking created - awaiting payment', [
                'pending_clinic_booking_id' => $pendingBooking->id,
                'invoice_id' => $invoice->id,
                'fee' => $fee,
            ]);

            return [
                'invoice' => $invoice,
                'pending_clinic_booking' => $pendingBooking,
            ];
        });
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

        $data = [
            'department_id' => $pending->department_id,
            'service_id' => $pending->service_id,
            'appointment_date' => $pending->appointment_date->format('Y-m-d'),
            'appointment_time' => $timeStr,
            'first_name' => $pending->patient_data['first_name'] ?? '',
            'last_name' => $pending->patient_data['last_name'] ?? '',
            'email' => $pending->patient_data['email'] ?? '',
            'phone' => $pending->patient_data['phone'] ?? '',
            'date_of_birth' => $pending->patient_data['date_of_birth'] ?? null,
            'gender' => $pending->patient_data['gender'] ?? null,
            'notes' => $pending->patient_data['notes'] ?? null,
            'consultation_type' => $pending->patient_data['consultation_type'] ?? 'in_person',
            'consent_share_with_gp' => $pending->patient_data['consent_share_with_gp'] ?? false,
            'gp_name' => $pending->patient_data['gp_name'] ?? null,
            'gp_email' => $pending->patient_data['gp_email'] ?? null,
            'gp_phone' => $pending->patient_data['gp_phone'] ?? null,
            'gp_address' => $pending->patient_data['gp_address'] ?? null,
        ];

        $clinicRequest = $this->createFromClinicBooking($data);
        $pending->markCompleted();

        return $clinicRequest;
    }

    /**
     * Create a clinic booking request (pending doctor acceptance).
     */
    public function createFromClinicBooking(array $data): ClinicBookingRequest
    {
        return DB::transaction(function () use ($data) {
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
                'notes' => $data['notes'] ?? null,
                'consultation_type' => $data['consultation_type'] ?? 'in_person',
                'consent_share_with_gp' => $data['consent_share_with_gp'] ?? false,
                'gp_name' => $data['gp_name'] ?? null,
                'gp_email' => $data['gp_email'] ?? null,
                'gp_phone' => $data['gp_phone'] ?? null,
                'gp_address' => $data['gp_address'] ?? null,
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
    }

    /**
     * Doctor accepts a clinic booking request. Creates patient + appointment, marks request as accepted.
     */
    public function acceptRequest(ClinicBookingRequest $request, Doctor $doctor): Appointment
    {
        return DB::transaction(function () use ($request, $doctor) {
            // Lock and verify still pending (use fresh lock)
            $request = ClinicBookingRequest::where('id', $request->id)
                ->where('status', 'pending_acceptance')
                ->lockForUpdate()
                ->firstOrFail();
            if ($request->status !== 'pending_acceptance') {
                throw new \RuntimeException('This booking has already been accepted by another doctor.');
            }

            $patientData = $request->patient_data;
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

            $request->update([
                'status' => 'accepted',
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
            ]);

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
