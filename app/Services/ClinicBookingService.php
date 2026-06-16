<?php

namespace App\Services;

use App\Models\ClinicBookingRequest;
use App\Models\ClinicBookingDiscountCode;
use App\Models\Department;
use App\Models\DoctorBookingDiscountCode;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PendingClinicBooking;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\DoctorAvailabilityRule;
use App\Models\PendingBooking;
use App\Models\Appointment;
use App\Models\BookingService;
use App\Services\GuestPatientService;
use App\Services\HospitalEmailNotificationService;
use App\Services\WherebyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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
            $doctorDiscountCodeId = null;
            $discountAmount = 0;
            $rawCode = DoctorBookingDiscountCode::normalizeCode((string) ($data['discount_code'] ?? ''));

            if ($listPrice > 0 && $rawCode !== '') {
                $resolved = $this->resolveClinicBookingDiscount(
                    $departmentId,
                    $service?->id,
                    $rawCode,
                    $listPrice,
                    true
                );

                if ($resolved === null) {
                    throw ValidationException::withMessages([
                        'discount_code' => ['This discount code is not valid for this booking.'],
                    ]);
                }

                $discountAmount = $resolved['discount_amount'];
                $discountCodeId = $resolved['clinic_booking_discount_code_id'];
                $doctorDiscountCodeId = $resolved['doctor_booking_discount_code_id'];
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
                $this->incrementClinicFlowDiscountUses($discountCodeId, $doctorDiscountCodeId);

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
            if ($doctorDiscountCodeId !== null && Schema::hasColumn('invoices', 'doctor_booking_discount_code_id')) {
                $invoicePayload['doctor_booking_discount_code_id'] = $doctorDiscountCodeId;
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
        $rawCode = DoctorBookingDiscountCode::normalizeCode($discountCodeRaw);
        if ($rawCode === '') {
            return ['ok' => false, 'message' => 'Enter a discount code.'];
        }

        if (! Schema::hasTable('clinic_booking_discount_codes') && ! Schema::hasTable('doctor_booking_discount_codes')) {
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

        $resolved = $this->resolveClinicBookingDiscount($departmentId, $service->id, $rawCode, $listPrice, false);
        if ($resolved === null) {
            return ['ok' => false, 'message' => 'This discount code is not valid for this booking.'];
        }

        $discountAmount = $resolved['discount_amount'];
        $amountDue = round(max(0, $listPrice - $discountAmount), 2);

        return [
            'ok' => true,
            'list_price' => $listPrice,
            'discount_amount' => $discountAmount,
            'amount_due' => $amountDue,
        ];
    }

    /**
     * Finalize a paid clinic checkout by invoice (does not rely on browser session after Stripe redirect).
     */
    public function finalizeClinicBookingForPaidInvoice(Invoice $invoice): ?ClinicBookingRequest
    {
        $invoice->refresh();

        $isPaid = $invoice->status === 'paid'
            || $invoice->payments()->where('status', 'completed')->exists();

        if (! $isPaid) {
            return null;
        }

        $pending = PendingClinicBooking::query()
            ->where('invoice_id', $invoice->id)
            ->orderByDesc('id')
            ->first();

        if (! $pending) {
            $this->assignPatientClinicAndDoctorFromPaidInvoice($invoice, skipFinalize: true);

            if ($invoice->appointment_id) {
                $request = ClinicBookingRequest::query()
                    ->where('appointment_id', $invoice->appointment_id)
                    ->where('status', 'accepted')
                    ->first();
                if ($request) {
                    $this->ensureClinicAndDoctorAssigned($request);
                }

                return $request;
            }

            return null;
        }

        if ($pending->status === 'completed') {
            $request = $this->repairPendingClinicBooking($pending);
            $this->assignPatientClinicAndDoctorFromPaidInvoice($invoice, skipFinalize: true);
            if ($request) {
                $this->ensureClinicAndDoctorAssigned($request);
            }

            return $request;
        }

        if (! in_array($pending->status, ['pending_payment', 'expired'], true)) {
            return null;
        }

        try {
            $request = $this->finalizeClinicBookingAfterPayment($pending, allowExpiredWhenPaid: true);
        } catch (\Throwable $e) {
            Log::error('Failed to finalize clinic booking for paid invoice', [
                'invoice_id' => $invoice->id,
                'pending_clinic_booking_id' => $pending->id,
                'error' => $e->getMessage(),
            ]);

            $request = null;
        }

        $this->assignPatientClinicAndDoctorFromPaidInvoice($invoice, skipFinalize: true);

        if ($request) {
            $this->ensureClinicAndDoctorAssigned($request);
        }

        return $request ?? ClinicBookingRequest::query()
            ->whereHas('patient', fn ($q) => $q->whereKey($invoice->patient_id))
            ->when($pending->department_id, fn ($q) => $q->where('department_id', $pending->department_id))
            ->where('status', 'accepted')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Assign patient + booking/appointment to clinic and default doctor from paid clinic checkout.
     */
    public function assignPatientClinicAndDoctorFromPaidInvoice(Invoice $invoice, bool $skipFinalize = false): bool
    {
        if (! $this->invoiceIsPaid($invoice)) {
            return false;
        }

        $invoice->loadMissing(['pendingClinicBookings.department', 'patient', 'appointment']);

        $pcb = $invoice->pendingClinicBookings->sortByDesc('id')->first();
        if (! $pcb?->department_id) {
            return false;
        }

        $departmentId = (int) $pcb->department_id;
        $doctor = $this->defaultDoctorForDepartment($departmentId);
        $changed = false;

        $patient = $invoice->patient;
        if (! $patient) {
            $pd = $pcb->patient_data ?? [];
            $email = strtolower(trim((string) ($pd['email'] ?? '')));
            if ($email !== '') {
                $patient = Patient::query()
                    ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                    ->first();
            }
        }

        if ($patient) {
            if ($this->syncPatientToClinic($patient, $departmentId, $doctor)) {
                $changed = true;
            }
            if (! $invoice->patient_id) {
                $invoice->update(['patient_id' => $patient->id]);
                $changed = true;
            }
        }

        $request = $this->findClinicBookingRequestForPaidCheckout($invoice, $pcb, $patient);
        if (! $request && ! $skipFinalize) {
            $request = $this->finalizeClinicBookingForPaidInvoice($invoice);
            if ($request) {
                $changed = true;
            }
        } elseif ($request && $this->ensureClinicAndDoctorAssigned($request)) {
            $changed = true;
        }

        if ($invoice->appointment) {
            $apptUpdates = [];
            if ($doctor && ! $invoice->appointment->doctor_id) {
                $apptUpdates['doctor_id'] = $doctor->id;
            }
            if (! $invoice->appointment->department_id) {
                $apptUpdates['department_id'] = $departmentId;
            }
            if ($apptUpdates !== []) {
                $invoice->appointment->update($apptUpdates);
                $changed = true;
            }
            if ($patient && $this->syncPatientToClinic($patient, $departmentId, $doctor)) {
                $changed = true;
            }
        }

        return $changed;
    }

    protected function findClinicBookingRequestForPaidCheckout(
        Invoice $invoice,
        PendingClinicBooking $pcb,
        ?Patient $patient
    ): ?ClinicBookingRequest {
        $departmentId = (int) $pcb->department_id;

        if ($invoice->appointment_id) {
            $byAppt = ClinicBookingRequest::query()
                ->where('appointment_id', $invoice->appointment_id)
                ->first();
            if ($byAppt) {
                return $byAppt;
            }
        }

        if ($patient) {
            $byPatient = ClinicBookingRequest::query()
                ->where('patient_id', $patient->id)
                ->where('department_id', $departmentId)
                ->orderByDesc('id')
                ->first();
            if ($byPatient) {
                return $byPatient;
            }
        }

        $email = strtolower(trim((string) (($pcb->patient_data ?? [])['email'] ?? '')));
        if ($email === '') {
            return null;
        }

        return ClinicBookingRequest::query()
            ->where('department_id', $departmentId)
            ->where(function ($q) use ($email, $pcb) {
                $q->where('patient_data->email', ($pcb->patient_data ?? [])['email'] ?? $email)
                    ->orWhereRaw('LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(patient_data, "$.email")))) = ?', [$email]);
            })
            ->orderByDesc('id')
            ->first();
    }

    protected function syncPatientToClinic(Patient $patient, int $departmentId, ?Doctor $doctor): bool
    {
        $changed = false;

        if (! $patient->departments()->where('departments.id', $departmentId)->exists()) {
            $patient->departments()->attach($departmentId, [
                'is_primary' => $patient->departments()->count() === 0,
            ]);
            $changed = true;
        }

        if ((int) $patient->department_id !== $departmentId) {
            if (! $patient->department_id) {
                $patient->department_id = $departmentId;
                $patient->save();
                $changed = true;
            }
        }

        if ($doctor && ! $patient->created_by_doctor_id) {
            $patient->created_by_doctor_id = $doctor->id;
            $patient->save();
            $changed = true;
        }

        return $changed;
    }

    protected function resolveDepartmentIdForRequest(ClinicBookingRequest $request): ?int
    {
        if ($request->department_id) {
            return (int) $request->department_id;
        }

        if ($request->appointment?->department_id) {
            return (int) $request->appointment->department_id;
        }

        $capture = app(PatientBookingSourceService::class)->clinicBookingRequestCapture($request);
        if ($capture['department_id']) {
            return (int) $capture['department_id'];
        }

        if ($capture['clinic_name']) {
            $dept = Department::query()
                ->where('name', $capture['clinic_name'])
                ->first();
            if ($dept) {
                return (int) $dept->id;
            }
        }

        if ($request->patient_id) {
            $invoice = Invoice::query()
                ->where('patient_id', $request->patient_id)
                ->whereHas('pendingClinicBookings')
                ->with('pendingClinicBookings')
                ->orderByDesc('created_at')
                ->first();
            $deptId = $invoice?->pendingClinicBookings->first()?->department_id;
            if ($deptId) {
                return (int) $deptId;
            }
        }

        $email = strtolower(trim((string) ($request->patient_data['email'] ?? '')));
        if ($email !== '') {
            $pcb = PendingClinicBooking::query()
                ->whereHas('invoice', fn ($q) => $q->where('status', 'paid')
                    ->orWhereHas('payments', fn ($p) => $p->where('status', 'completed')))
                ->where(function ($q) use ($email, $request) {
                    $q->where('patient_data->email', $request->patient_data['email'] ?? $email)
                        ->orWhereRaw('LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(patient_data, "$.email")))) = ?', [$email]);
                })
                ->orderByDesc('id')
                ->first();

            if ($pcb?->department_id) {
                return (int) $pcb->department_id;
            }
        }

        return null;
    }

    /**
     * Finalize every paid clinic checkout that never completed (batch repair).
     *
     * @return array{finalized: int, repaired: int, skipped: int, failed: int, assigned: int}
     */
    public function finalizeAllStuckPaidClinicBookings(): array
    {
        $stats = ['finalized' => 0, 'repaired' => 0, 'skipped' => 0, 'failed' => 0, 'assigned' => 0];

        if (! Schema::hasTable('pending_clinic_bookings')) {
            return $stats;
        }

        PendingClinicBooking::query()
            ->whereNotNull('invoice_id')
            ->whereIn('status', ['pending_payment', 'expired'])
            ->with(['invoice.payments'])
            ->orderBy('id')
            ->chunkById(100, function ($pendings) use (&$stats) {
                foreach ($pendings as $pending) {
                    $this->processStuckPaidPending($pending, $stats, countAsRepair: false);
                }
            });

        // Paid checkout marked completed but never created accepted clinic request + appointment.
        PendingClinicBooking::query()
            ->whereNotNull('invoice_id')
            ->where('status', 'completed')
            ->with(['invoice.payments'])
            ->orderBy('id')
            ->chunkById(100, function ($pendings) use (&$stats) {
                foreach ($pendings as $pending) {
                    $this->processStuckPaidPending($pending, $stats, countAsRepair: true);
                }
            });

        $assignStats = $this->backfillMissingClinicAndDoctorAssignments();
        $stats['assigned'] = $assignStats['updated'];
        $stats['patients_assigned'] = $assignStats['patients_assigned'] ?? 0;

        return $stats;
    }

    /**
     * Primary doctor in department, else sole active doctor, else first active doctor.
     */
    public function defaultDoctorForDepartment(int $departmentId): ?Doctor
    {
        if ($departmentId <= 0) {
            return null;
        }

        $active = Doctor::query()
            ->byDepartment($departmentId)
            ->active()
            ->orderBy('id')
            ->get();

        if ($active->count() === 1) {
            return $active->first();
        }

        if ($active->isNotEmpty()) {
            $primary = Doctor::query()
                ->byDepartment($departmentId)
                ->active()
                ->whereHas('departments', function ($q) use ($departmentId) {
                    $q->where('departments.id', $departmentId)
                        ->where('doctor_department.is_primary', true);
                })
                ->orderBy('id')
                ->first();

            return $primary ?? $active->first();
        }

        return Doctor::query()
            ->byDepartment($departmentId)
            ->orderBy('id')
            ->first();
    }

    /**
     * Backfill accepted bookings (and paid pending) with clinic + default doctor from checkout capture.
     *
     * @return array{updated: int, skipped: int, failed: int}
     */
    public function backfillMissingClinicAndDoctorAssignments(): array
    {
        $stats = ['updated' => 0, 'skipped' => 0, 'failed' => 0, 'patients_assigned' => 0];

        if (! Schema::hasTable('clinic_booking_requests')) {
            return $stats;
        }

        Invoice::query()
            ->whereHas('pendingClinicBookings')
            ->where(function ($q) {
                $q->where('status', 'paid')
                    ->orWhereHas('payments', fn ($p) => $p->where('status', 'completed'));
            })
            ->with(['pendingClinicBookings.department', 'patient', 'appointment'])
            ->orderBy('id')
            ->chunkById(50, function ($invoices) use (&$stats) {
                foreach ($invoices as $invoice) {
                    try {
                        if ($this->assignPatientClinicAndDoctorFromPaidInvoice($invoice, skipFinalize: false)) {
                            $stats['patients_assigned']++;
                        }
                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        Log::error('Paid clinic invoice patient/clinic assign failed', [
                            'invoice_id' => $invoice->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        ClinicBookingRequest::query()
            ->whereIn('status', ['accepted', 'pending_acceptance'])
            ->where(function ($q) {
                $q->whereNull('doctor_id')
                    ->orWhereNull('department_id')
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'accepted')
                            ->whereHas('appointment', fn ($a) => $a->whereNull('doctor_id'));
                    });
            })
            ->with(['appointment', 'patient', 'department'])
            ->orderBy('id')
            ->chunkById(50, function ($requests) use (&$stats) {
                foreach ($requests as $request) {
                    try {
                        if ($this->ensureClinicAndDoctorAssigned($request)) {
                            $stats['updated']++;
                        } else {
                            $stats['skipped']++;
                        }
                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        Log::error('Clinic booking clinic/doctor backfill failed', [
                            'clinic_booking_request_id' => $request->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        return $stats;
    }

    /**
     * Assign clinic (department) and default doctor when inferrable from checkout / capture.
     * Auto-accepts paid pending requests with a default doctor.
     */
    public function ensureClinicAndDoctorAssigned(ClinicBookingRequest $request): bool
    {
        $request->refresh();
        $request->loadMissing(['department', 'doctor.user', 'appointment.doctor', 'appointment.department', 'patient']);

        $beforeDoctorId = $request->doctor_id;
        $beforeDeptId = $request->department_id;
        $beforeApptDoctorId = $request->appointment?->doctor_id;

        $departmentId = $this->resolveDepartmentIdForRequest($request);
        if ($departmentId && ! $request->department_id) {
            $request->update(['department_id' => $departmentId]);
            $request->refresh();
        }

        if (! $departmentId) {
            return false;
        }

        $doctor = $request->resolvedDoctor();
        if (! $doctor) {
            $doctor = $this->defaultDoctorForDepartment($departmentId);
        }

        if (! $doctor) {
            return $beforeDoctorId !== $request->doctor_id
                || $beforeDeptId !== $request->department_id;
        }

        if ($request->status === 'pending_acceptance') {
            $invoice = $this->findPaidInvoiceForClinicRequest($request, $departmentId);
            if ($invoice) {
                try {
                    $this->acceptRequest($request, $doctor, $doctor->user_id, true);

                    return true;
                } catch (\Throwable $e) {
                    Log::warning('Clinic booking auto-accept during assign failed; setting doctor on request', [
                        'clinic_booking_request_id' => $request->id,
                        'doctor_id' => $doctor->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if (! $request->doctor_id) {
                $request->update(['doctor_id' => $doctor->id]);
                $request->refresh();

                return true;
            }
        }

        $changed = false;
        $requestUpdates = [];
        if (! $request->department_id) {
            $requestUpdates['department_id'] = $departmentId;
        }
        if (! $request->doctor_id) {
            $requestUpdates['doctor_id'] = $doctor->id;
        }
        if ($requestUpdates !== []) {
            $request->update($requestUpdates);
            $changed = true;
        }

        if ($request->appointment_id && $request->appointment) {
            $apptUpdates = [];
            if (! $request->appointment->doctor_id) {
                $apptUpdates['doctor_id'] = $doctor->id;
            }
            if (! $request->appointment->department_id) {
                $apptUpdates['department_id'] = $departmentId;
            }
            if ($apptUpdates !== []) {
                $request->appointment->update($apptUpdates);
                $changed = true;
            }
        }

        $patient = $request->patient;
        if (! $patient && $request->patient_id) {
            $patient = Patient::find($request->patient_id);
        }
        if ($patient && $this->syncPatientToClinic($patient, $departmentId, $doctor)) {
            $changed = true;
        }

        $request->refresh();

        return $changed
            || $beforeDoctorId !== $request->doctor_id
            || $beforeDeptId !== $request->department_id
            || $beforeApptDoctorId !== $request->appointment?->doctor_id;
    }

    protected function findPaidInvoiceForClinicRequest(ClinicBookingRequest $request, int $departmentId): ?Invoice
    {
        if ($request->patient_id) {
            $invoice = Invoice::query()
                ->where('patient_id', $request->patient_id)
                ->whereHas('pendingClinicBookings', fn ($q) => $q->where('department_id', $departmentId))
                ->with('payments')
                ->orderByDesc('created_at')
                ->first();
            if ($invoice && $this->invoiceIsPaid($invoice)) {
                return $invoice;
            }
        }

        $email = trim((string) ($request->patient_data['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        $invoice = Invoice::query()
            ->whereHas('pendingClinicBookings', function ($q) use ($departmentId, $email, $request) {
                $q->where('department_id', $departmentId)
                    ->where('patient_data->email', $request->patient_data['email'] ?? $email);
            })
            ->with('payments')
            ->orderByDesc('created_at')
            ->first();

        return $invoice && $this->invoiceIsPaid($invoice) ? $invoice : null;
    }

    /**
     * Rebuild accepted clinic booking + appointment from a paid pending checkout row.
     */
    public function repairPendingClinicBooking(PendingClinicBooking $pending): ?ClinicBookingRequest
    {
        $invoice = $pending->invoice;
        if (! $invoice || ! $this->invoiceIsPaid($invoice)) {
            return null;
        }

        $existing = $this->findAcceptedRequestForPending($pending);
        if ($existing) {
            $this->backfillInvoiceAndPatientFromRequest($invoice, $existing, $pending);
            $this->ensureClinicAndDoctorAssigned($existing);

            return $existing->refresh();
        }

        try {
            $data = $this->pendingRecordToBookingData($pending);
            $request = $this->createFromClinicBooking($data);

            if ($pending->status !== 'completed') {
                $pending->markCompleted();
            }

            $this->backfillInvoiceAndPatientFromRequest($invoice, $request, $pending);
            $this->ensureClinicAndDoctorAssigned($request);

            Log::info('Repaired paid clinic checkout into accepted booking', [
                'pending_clinic_booking_id' => $pending->id,
                'invoice_id' => $invoice->id,
                'clinic_booking_request_id' => $request->id,
                'request_number' => $request->request_number,
            ]);

            return $request;
        } catch (\Throwable $e) {
            Log::error('Failed to repair pending clinic booking', [
                'pending_clinic_booking_id' => $pending->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @param  array{finalized: int, repaired: int, skipped: int, failed: int}  $stats
     */
    protected function processStuckPaidPending(
        PendingClinicBooking $pending,
        array &$stats,
        bool $countAsRepair
    ): void {
        $invoice = $pending->invoice;
        $isPaid = $invoice
            && ($invoice->status === 'paid'
                || $invoice->payments->contains(fn ($p) => $p->status === 'completed'));

        if (! $isPaid) {
            $stats['skipped']++;

            return;
        }

        try {
            if ($countAsRepair) {
                $existing = $this->findAcceptedRequestForPending($pending);
                if ($existing) {
                    $this->backfillInvoiceAndPatientFromRequest($invoice, $existing, $pending);
                    if ($this->ensureClinicAndDoctorAssigned($existing)) {
                        $stats['repaired']++;
                    } else {
                        $stats['skipped']++;
                    }

                    return;
                }
            }

            $request = $this->finalizeClinicBookingForPaidInvoice($invoice);
            if (! $request) {
                $stats['skipped']++;

                return;
            }

            $this->ensureClinicAndDoctorAssigned($request);

            if ($countAsRepair) {
                $stats['repaired']++;
            } else {
                $stats['finalized']++;
            }
        } catch (\Throwable $e) {
            $stats['failed']++;
            Log::error('Batch finalize paid clinic booking failed', [
                'pending_clinic_booking_id' => $pending->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function findAcceptedRequestForPending(PendingClinicBooking $pending): ?ClinicBookingRequest
    {
        $invoice = $pending->invoice;
        $pd = $pending->patient_data ?? [];
        $email = strtolower(trim((string) ($pd['email'] ?? '')));

        $base = ClinicBookingRequest::query()
            ->where('department_id', $pending->department_id)
            ->where('status', 'accepted')
            ->whereDate('appointment_date', $pending->appointment_date);

        if ($invoice?->patient_id) {
            $match = (clone $base)->where('patient_id', $invoice->patient_id)->first();
            if ($match) {
                return $match;
            }
        }

        if ($email !== '') {
            return (clone $base)
                ->where(function ($q) use ($pd, $email) {
                    $q->where('patient_data->email', $pd['email'] ?? '')
                        ->orWhereRaw('LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(patient_data, "$.email")))) = ?', [$email]);
                })
                ->first();
        }

        return null;
    }

    protected function backfillInvoiceAndPatientFromRequest(
        Invoice $invoice,
        ClinicBookingRequest $request,
        PendingClinicBooking $pending
    ): void {
        if ($request->appointment_id && ! $invoice->appointment_id) {
            $invoice->update(['appointment_id' => $request->appointment_id]);
        }

        if ($request->patient_id && ! $invoice->patient_id) {
            $invoice->update(['patient_id' => $request->patient_id]);
        } elseif ($invoice->patient_id && ! $request->patient_id) {
            $request->update(['patient_id' => $invoice->patient_id]);
        }

        $patient = Patient::find($request->patient_id ?? $invoice->patient_id);
        if ($patient && $pending->department_id) {
            if (! $patient->departments()->where('departments.id', $pending->department_id)->exists()) {
                $isPrimary = $patient->departments()->count() === 0;
                $patient->departments()->attach($pending->department_id, ['is_primary' => $isPrimary]);
            }
            if (! $patient->department_id) {
                $patient->department_id = $pending->department_id;
                $patient->save();
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function pendingRecordToBookingData(PendingClinicBooking $pending): array
    {
        $timeStr = $pending->appointment_time instanceof \DateTimeInterface
            ? $pending->appointment_time->format('H:i')
            : substr((string) $pending->appointment_time, 0, 5);

        $pd = $pending->patient_data ?? [];

        return [
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
    }

    public function invoiceIsPaid(Invoice $invoice): bool
    {
        $invoice->refresh();

        return $invoice->status === 'paid'
            || $invoice->payments()->where('status', 'completed')->exists();
    }

    /**
     * Finalize clinic booking after payment. Creates ClinicBookingRequest.
     */
    public function finalizeClinicBookingAfterPayment(
        PendingClinicBooking $pending,
        bool $allowExpiredWhenPaid = false
    ): ClinicBookingRequest {
        if (! in_array($pending->status, ['pending_payment', 'expired'], true)) {
            throw new \Exception('Clinic booking is not awaiting finalization');
        }

        if ($pending->isExpired() && ! $allowExpiredWhenPaid) {
            if ($pending->status !== 'expired') {
                $pending->markExpired();
            }
            throw new \Exception('Clinic booking has expired');
        }

        if ($pending->status === 'expired' && $allowExpiredWhenPaid) {
            $pending->update(['status' => 'pending_payment']);
            $pending->refresh();
        }

        $data = $this->pendingRecordToBookingData($pending);

        return DB::transaction(function () use ($pending, $data) {
            $invoice = $pending->invoice;

            if ($invoice
                && Schema::hasColumn('invoices', 'discount_code_redemption_recorded_at')
                && $invoice->discount_code_redemption_recorded_at === null) {
                if ($invoice->clinic_booking_discount_code_id
                    && Schema::hasTable('clinic_booking_discount_codes')) {
                    $discountCode = ClinicBookingDiscountCode::query()
                        ->whereKey($invoice->clinic_booking_discount_code_id)
                        ->lockForUpdate()
                        ->first();
                    if ($discountCode) {
                        $discountCode->increment('uses_count');
                    }
                } elseif ($invoice->doctor_booking_discount_code_id
                    && Schema::hasTable('doctor_booking_discount_codes')) {
                    $discountCode = DoctorBookingDiscountCode::query()
                        ->whereKey($invoice->doctor_booking_discount_code_id)
                        ->lockForUpdate()
                        ->first();
                    if ($discountCode) {
                        $discountCode->increment('uses_count');
                    }
                }
                $invoice->update(['discount_code_redemption_recorded_at' => now()]);
            }

            $clinicRequest = $this->createFromClinicBooking($data);
            $pending->markCompleted();

            if ($invoice && $clinicRequest->appointment_id && ! $invoice->appointment_id) {
                $invoice->update(['appointment_id' => $clinicRequest->appointment_id]);
            }

            return $clinicRequest;
        });
    }

    /**
     * Create a clinic booking request (pending doctor acceptance).
     * When the clinic has a default doctor (primary, sole, or first active), auto-assign and accept.
     */
    public function createFromClinicBooking(array $data): ClinicBookingRequest
    {
        [$request, $defaultDoctor] = DB::transaction(function () use ($data) {
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

            $defaultDoctor = $this->defaultDoctorForDepartment($departmentId);
            if (! $defaultDoctor) {
                $this->notifyDoctorsOfNewRequest($request);
            }

            return [$request, $defaultDoctor];
        });

        if ($defaultDoctor) {
            try {
                $this->acceptRequest($request, $defaultDoctor, $defaultDoctor->user_id, true);
            } catch (\Throwable $e) {
                Log::error('Clinic booking auto-accept failed; falling back to manual acceptance flow', [
                    'clinic_booking_request_id' => $request->id,
                    'doctor_id' => $defaultDoctor->id,
                    'error' => $e->getMessage(),
                ]);
                try {
                    $this->notifyDoctorsOfNewRequest($request);
                    $this->emailService->notifyClinicDoctorsNewBookingRequest($request);
                } catch (\Throwable $notifyEx) {
                    Log::error('Clinic booking fallback doctor notifications failed', [
                        'clinic_booking_request_id' => $request->id,
                        'error' => $notifyEx->getMessage(),
                    ]);
                }
            }

            return $request->refresh();
        }

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
     * Cancel a pending clinic booking request (admin). Releases the slot for new bookings.
     */
    public function cancelRequest(ClinicBookingRequest $request, ?string $reason = null, ?int $cancelledByUserId = null): ClinicBookingRequest
    {
        return DB::transaction(function () use ($request, $reason, $cancelledByUserId) {
            $request = ClinicBookingRequest::where('id', $request->id)
                ->where('status', 'pending_acceptance')
                ->lockForUpdate()
                ->firstOrFail();

            $noteSuffix = "\n\n[Cancelled ".now()->format('Y-m-d H:i').']';
            if ($cancelledByUserId) {
                $user = \App\Models\User::find($cancelledByUserId);
                if ($user) {
                    $noteSuffix .= ' by '.$user->name;
                }
            }
            if ($reason !== null && trim($reason) !== '') {
                $noteSuffix .= "\nReason: ".trim($reason);
            }

            $request->update([
                'status' => 'cancelled',
                'notes' => trim(($request->notes ?? '').$noteSuffix),
            ]);

            Log::info('Clinic booking request cancelled', [
                'request_id' => $request->id,
                'request_number' => $request->request_number,
                'cancelled_by_user_id' => $cancelledByUserId,
            ]);

            return $request->refresh();
        });
    }

    /**
     * Doctor accepts a clinic booking request. Creates patient + appointment, marks request as accepted.
     */
    public function acceptRequest(
        ClinicBookingRequest $request,
        Doctor $doctor,
        ?int $acceptedByUserId = null,
        bool $autoAccepted = false
    ): Appointment {
        return DB::transaction(function () use ($request, $doctor, $acceptedByUserId, $autoAccepted) {
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

            // Re-validate the physical slot against THIS accepting doctor: lock the doctor (single
            // resource), ensure the time is free across all modalities, and confirm the requested
            // modality is actually possible for this doctor. Capture the availability rule consumed.
            $availabilityRuleId = $this->validateClinicSlotForDoctor($request, $doctor, $service);

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

            // Server-side modality: the service's modality for the accepting doctor is authoritative.
            $resolvedConsultationType = $request->consultation_type ?? 'in_person';
            if (config('booking.modality_rules_enabled', true)
                && $service
                && !(method_exists($service, 'isNonConsultation') && $service->isNonConsultation())) {
                $resolvedConsultationType = DoctorAvailabilityRule::normalizeModality(
                    $service->getConsultationTypeForDoctor($doctor->id)
                );
            }
            $isOnline = $resolvedConsultationType === 'online';
            $useWhereby = $isOnline && $this->wherebyService->isEnabled();

            $appointmentPayload = [
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
                'consultation_type' => $resolvedConsultationType,
                'notes' => $request->notes,
                'created_from' => $autoAccepted ? 'Clinic Booking (Auto-assigned)' : 'Clinic Booking (Doctor Accepted)',
                // Set meeting_platform so Observer skips email until we have the meeting link
                'meeting_platform' => $useWhereby ? 'whereby' : null,
            ];
            if ($availabilityRuleId && Schema::hasColumn('appointments', 'availability_rule_id')) {
                $appointmentPayload['availability_rule_id'] = $availabilityRuleId;
            }

            $appointment = Appointment::create($appointmentPayload);

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
                'fee' => $fee,
            ];
            if (Schema::hasColumn('clinic_booking_requests', 'accepted_by_user_id')) {
                $acceptedPayload['accepted_by_user_id'] = $acceptedByUserId;
            }
            if (Schema::hasColumn('clinic_booking_requests', 'accepted_at')) {
                $acceptedPayload['accepted_at'] = now();
            }
            if (Schema::hasColumn('clinic_booking_requests', 'auto_accepted')) {
                $acceptedPayload['auto_accepted'] = $autoAccepted;
            }
            if ($availabilityRuleId && Schema::hasColumn('clinic_booking_requests', 'availability_rule_id')) {
                $acceptedPayload['availability_rule_id'] = $availabilityRuleId;
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

    /**
     * Validate a clinic request's slot against the accepting doctor and return the availability rule
     * consumed (or null). Locks the doctor row to serialize bookings for that practitioner.
     *
     * @throws ValidationException when the slot is taken or the modality is not possible for the doctor.
     */
    protected function validateClinicSlotForDoctor(ClinicBookingRequest $request, Doctor $doctor, ?BookingService $service): ?int
    {
        $dateStr = $request->appointment_date instanceof \DateTimeInterface
            ? $request->appointment_date->format('Y-m-d')
            : (string) $request->appointment_date;
        $timeStr = $request->appointment_time instanceof \DateTimeInterface
            ? $request->appointment_time->format('H:i:s')
            : (string) $request->appointment_time;

        // Serialize all bookings for this practitioner.
        Doctor::whereKey($doctor->id)->lockForUpdate()->first();

        $duration = $service ? (int) ($service->getDurationForDoctor($doctor->id) ?? 30) : 30;
        if ($duration <= 0) {
            $duration = 30;
        }

        $slotStart = Carbon::parse($dateStr . ' ' . $timeStr);
        $slotEnd = $slotStart->copy()->addMinutes($duration);

        // Block if an existing appointment for this doctor overlaps (any modality — shared resource).
        $appointments = Appointment::with('service')
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $dateStr)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->get();
        foreach ($appointments as $appointment) {
            $apptStart = Carbon::parse(
                $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s')
            );
            $apptDuration = (int) ($appointment->estimated_duration ?? 0);
            if ($apptDuration <= 0 && $appointment->service_id) {
                $apptDuration = (int) ($appointment->service->getDurationForDoctor($doctor->id) ?? 30);
            }
            if ($apptDuration <= 0) {
                $apptDuration = 30;
            }
            $apptEnd = $apptStart->copy()->addMinutes($apptDuration);
            if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
                throw ValidationException::withMessages([
                    'appointment_time' => ['This time slot is no longer available for the selected doctor.'],
                ]);
            }
        }

        // Block if an in-progress pending booking holds the slot for this doctor.
        if (config('booking.modality_rules_enabled', true) && config('booking.lock_pending_bookings', true)) {
            $pendings = PendingBooking::pendingPayment()
                ->with('service')
                ->where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $dateStr)
                ->get();
            foreach ($pendings as $pending) {
                $pStart = Carbon::parse(
                    ($pending->appointment_date instanceof \DateTimeInterface ? $pending->appointment_date->format('Y-m-d') : (string) $pending->appointment_date)
                    . ' '
                    . ($pending->appointment_time instanceof \DateTimeInterface ? $pending->appointment_time->format('H:i:s') : substr((string) $pending->appointment_time, 0, 8))
                );
                $pDuration = ($pending->service_id && $pending->service)
                    ? (int) ($pending->service->getDurationForDoctor($pending->doctor_id) ?? 30)
                    : 30;
                if ($pDuration <= 0) {
                    $pDuration = 30;
                }
                $pEnd = $pStart->copy()->addMinutes($pDuration);
                if ($slotStart->lt($pEnd) && $slotEnd->gt($pStart)) {
                    throw ValidationException::withMessages([
                        'appointment_time' => ['This time slot is being held by another booking in progress.'],
                    ]);
                }
            }
        }

        if (!config('booking.modality_rules_enabled', true)) {
            return null;
        }

        // Confirm the requested modality is possible for this doctor and capture the rule.
        $modality = DoctorAvailabilityRule::normalizeModality(
            $service
                ? $service->getConsultationTypeForDoctor($doctor->id)
                : ($request->consultation_type ?? null)
        );

        $dayName = strtolower($slotStart->format('l'));
        $rules = $doctor->availabilityRules()->active()->forDay($dayName)->get();
        if ($rules->isEmpty()) {
            // Doctor has no modality rules yet (not backfilled/narrowed): keep legacy behaviour.
            return null;
        }

        $slotStartMinutes = (int) $slotStart->format('H') * 60 + (int) $slotStart->format('i');
        $slotEndMinutes = (int) $slotEnd->format('H') * 60 + (int) $slotEnd->format('i');

        foreach ($rules as $rule) {
            if (!$rule->supportsModality($modality)) {
                continue;
            }
            $parts = explode(':', (string) $rule->start_time);
            $ruleStart = (int) ($parts[0] ?? 0) * 60 + (int) ($parts[1] ?? 0);
            $parts = explode(':', (string) $rule->end_time);
            $ruleEnd = (int) ($parts[0] ?? 0) * 60 + (int) ($parts[1] ?? 0);
            if ($ruleStart <= $slotStartMinutes && $ruleEnd >= $slotEndMinutes) {
                return $rule->id;
            }
        }

        throw ValidationException::withMessages([
            'appointment_time' => ['The selected doctor is not available for this consultation type at this time.'],
        ]);
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

    /**
     * @return array{discount_amount: float, clinic_booking_discount_code_id: ?int, doctor_booking_discount_code_id: ?int}|null
     */
    private function resolveClinicBookingDiscount(
        int $departmentId,
        ?int $serviceId,
        string $normalizedCode,
        float $listPrice,
        bool $lock
    ): ?array {
        if ($normalizedCode === '' || $listPrice <= 0) {
            return null;
        }

        if (Schema::hasTable('clinic_booking_discount_codes')) {
            $clinicQuery = ClinicBookingDiscountCode::query()
                ->where('department_id', $departmentId)
                ->where('code', $normalizedCode);
            if ($lock) {
                $clinicQuery->lockForUpdate();
            }
            $clinicCode = $clinicQuery->first();
            if ($clinicCode && $clinicCode->isUsableForBooking($serviceId)) {
                return [
                    'discount_amount' => $clinicCode->computeDiscountAmount($listPrice),
                    'clinic_booking_discount_code_id' => $clinicCode->id,
                    'doctor_booking_discount_code_id' => null,
                ];
            }
        }

        $doctorCode = DoctorBookingDiscountCode::findUsableForClinicDepartment(
            $departmentId,
            $normalizedCode,
            $serviceId,
            $lock
        );
        if ($doctorCode) {
            return [
                'discount_amount' => $doctorCode->computeDiscountAmount($listPrice),
                'clinic_booking_discount_code_id' => null,
                'doctor_booking_discount_code_id' => $doctorCode->id,
            ];
        }

        return null;
    }

    private function incrementClinicFlowDiscountUses(?int $clinicCodeId, ?int $doctorCodeId): void
    {
        if ($clinicCodeId !== null && Schema::hasTable('clinic_booking_discount_codes')) {
            ClinicBookingDiscountCode::whereKey($clinicCodeId)->increment('uses_count');
        }
        if ($doctorCodeId !== null && Schema::hasTable('doctor_booking_discount_codes')) {
            DoctorBookingDiscountCode::whereKey($doctorCodeId)->increment('uses_count');
        }
    }
}
