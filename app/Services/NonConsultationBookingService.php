<?php

namespace App\Services;

use App\Models\ClinicBookingDiscountCode;
use App\Models\Doctor;
use App\Models\DoctorBookingDiscountCode;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\ServiceOrder;
use App\Models\BookingService as BookingServiceModel;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class NonConsultationBookingService
{
    public function __construct(
        protected GuestPatientService $guestPatientService,
        protected HospitalEmailNotificationService $emailService,
        protected PublicBookingService $publicBookingService,
    ) {
    }

    /**
     * @return array{service_order: ServiceOrder, invoice: ?Invoice}
     */
    public function createFromPublicBooking(array $data, Doctor $doctor, BookingServiceModel $service, ?int $departmentId, bool $isClinicFlow = false): array
    {
        if (! Schema::hasTable('service_orders')) {
            throw ValidationException::withMessages([
                'booking' => ['Service orders are not set up on this server yet. Please contact support or run database migrations.'],
            ]);
        }

        if (! $service->isNonConsultation()) {
            throw ValidationException::withMessages([
                'service_id' => ['This service is not configured for non-consultation booking.'],
            ]);
        }

        $result = DB::transaction(function () use ($data, $doctor, $service, $departmentId, $isClinicFlow) {
            $data = array_merge($data, normalize_public_booking_address_fields($data));

            $listPrice = (float) ($service->getPriceForDoctor($doctor->id) ?? $service->default_price ?? 0);
            $discountCodeId = null;
            $clinicDiscountCodeId = null;
            $discountAmount = 0;
            $rawCode = $isClinicFlow
                ? ClinicBookingDiscountCode::normalizeCode((string) ($data['discount_code'] ?? ''))
                : DoctorBookingDiscountCode::normalizeCode((string) ($data['discount_code'] ?? ''));

            if ($listPrice > 0 && $rawCode !== '') {
                if ($isClinicFlow && Schema::hasTable('clinic_booking_discount_codes')) {
                    $code = ClinicBookingDiscountCode::query()
                        ->where('department_id', $departmentId)
                        ->where('code', $rawCode)
                        ->with('bookingServices')
                        ->lockForUpdate()
                        ->first();
                    if (! $code || ! $code->isUsableForBooking($service->id)) {
                        throw ValidationException::withMessages([
                            'discount_code' => ['This discount code is not valid for this booking.'],
                        ]);
                    }
                    $discountAmount = $code->computeDiscountAmount($listPrice);
                    $clinicDiscountCodeId = $code->id;
                } elseif (! $isClinicFlow && Schema::hasTable('doctor_booking_discount_codes')) {
                    $code = DoctorBookingDiscountCode::query()
                        ->where('doctor_id', $doctor->id)
                        ->where('code', $rawCode)
                        ->with('bookingServices')
                        ->lockForUpdate()
                        ->first();
                    if (! $code || ! $code->isUsableForBooking($service->id)) {
                        throw ValidationException::withMessages([
                            'discount_code' => ['This discount code is not valid for this booking.'],
                        ]);
                    }
                    $discountAmount = $code->computeDiscountAmount($listPrice);
                    $discountCodeId = $code->id;
                }
            }

            $payableFee = round(max(0, $listPrice - $discountAmount), 2);

            if ($payableFee <= 0) {
                return $this->createImmediateOrder($data, $doctor, $service, $departmentId, $listPrice, $discountAmount, $discountCodeId, $clinicDiscountCodeId);
            }

            return $this->createPendingOrder($data, $doctor, $service, $departmentId, $listPrice, $discountAmount, $discountCodeId, $clinicDiscountCodeId);
        });

        if ($result['service_order']->status === ServiceOrder::STATUS_PAID) {
            $patient = Patient::find($result['service_order']->patient_id);
            if ($patient) {
                $this->sendNotifications($result['service_order']->fresh(['doctor', 'service', 'department']), $patient);
            }
        }

        return $result;
    }

    public function previewDoctorDiscount(int $doctorId, int $serviceId, string $discountCodeRaw): array
    {
        return app(PublicBookingService::class)->previewDoctorBookingDiscount($doctorId, $serviceId, $discountCodeRaw);
    }

    public function previewClinicDiscount(int $departmentId, int $serviceId, string $discountCodeRaw): array
    {
        return app(ClinicBookingService::class)->previewClinicBookingDiscount($departmentId, $serviceId, $discountCodeRaw);
    }

    /**
     * Finalize a pending service order when its invoice is paid (e.g. after Stripe redirect without session).
     */
    public function finalizeServiceOrderForPaidInvoice(Invoice $invoice): ?ServiceOrder
    {
        if (! Schema::hasTable('service_orders')) {
            return null;
        }

        $invoice->refresh();
        $isPaid = $invoice->status === 'paid'
            || $invoice->payments()->where('status', 'completed')->exists();

        if (! $isPaid) {
            return null;
        }

        $order = ServiceOrder::query()
            ->where('invoice_id', $invoice->id)
            ->where('status', ServiceOrder::STATUS_PENDING_PAYMENT)
            ->first();

        if (! $order) {
            return ServiceOrder::query()
                ->where('invoice_id', $invoice->id)
                ->where('status', ServiceOrder::STATUS_PAID)
                ->first();
        }

        try {
            return $this->finalizeAfterPayment($order);
        } catch (\Exception $e) {
            Log::error('Failed to finalize service order for paid invoice', [
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function finalizeAfterPayment(ServiceOrder $order): ServiceOrder
    {
        if ($order->status !== ServiceOrder::STATUS_PENDING_PAYMENT) {
            throw new \Exception('Order is not awaiting payment.');
        }
        if ($order->isExpired()) {
            $order->markExpired();
            throw new \Exception('Order has expired.');
        }

        return DB::transaction(function () use ($order) {
            $order->load(['doctor', 'service', 'invoice', 'patient']);
            $this->recordDiscountRedemption($order);

            $order->update([
                'status' => ServiceOrder::STATUS_PAID,
                'paid_at' => now(),
                'booking_token' => null,
            ]);

            $patient = $order->patient ?? Patient::find($order->patient_id);
            if ($patient) {
                $this->sendNotifications($order, $patient);
            }

            return $order->fresh(['doctor', 'service', 'patient', 'department']);
        });
    }

    /**
     * @return array{service_order: ServiceOrder, invoice: ?Invoice}
     */
    private function createImmediateOrder(
        array $data,
        Doctor $doctor,
        BookingServiceModel $service,
        ?int $departmentId,
        float $listPrice,
        float $discountAmount,
        ?int $discountCodeId,
        ?int $clinicDiscountCodeId
    ): array {
        $patient = $this->createOrUpdatePatient($data, $departmentId);

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateOrderNumber(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $departmentId,
            'service_id' => $service->id,
            'patient_data' => $this->patientDataSnapshot($data),
            'notes' => $data['notes'] ?? null,
            'list_price' => $listPrice,
            'discount_amount' => $discountAmount,
            'fee' => 0,
            'doctor_booking_discount_code_id' => $discountCodeId,
            'clinic_booking_discount_code_id' => $clinicDiscountCodeId,
            'status' => ServiceOrder::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->recordDiscountRedemption($order);

        return ['service_order' => $order, 'invoice' => null];
    }

    /**
     * @return array{service_order: ServiceOrder, invoice: Invoice}
     */
    private function createPendingOrder(
        array $data,
        Doctor $doctor,
        BookingServiceModel $service,
        ?int $departmentId,
        float $listPrice,
        float $discountAmount,
        ?int $discountCodeId,
        ?int $clinicDiscountCodeId
    ): array {
        $payableFee = round(max(0, $listPrice - $discountAmount), 2);
        $patient = $this->createOrUpdatePatient($data, $departmentId);

        $order = ServiceOrder::create([
            'order_number' => ServiceOrder::generateOrderNumber(),
            'booking_token' => ServiceOrder::generateBookingToken(),
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $departmentId,
            'service_id' => $service->id,
            'patient_data' => $this->patientDataSnapshot($data),
            'notes' => $data['notes'] ?? null,
            'list_price' => $listPrice,
            'discount_amount' => $discountAmount,
            'fee' => $payableFee,
            'doctor_booking_discount_code_id' => $discountCodeId,
            'clinic_booking_discount_code_id' => $clinicDiscountCodeId,
            'status' => ServiceOrder::STATUS_PENDING_PAYMENT,
            'expires_at' => now()->addHours(24),
        ]);

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
            'description' => $service->name,
        ];
        if ($discountCodeId && Schema::hasColumn('invoices', 'doctor_booking_discount_code_id')) {
            $invoicePayload['doctor_booking_discount_code_id'] = $discountCodeId;
        }
        if ($clinicDiscountCodeId && Schema::hasColumn('invoices', 'clinic_booking_discount_code_id')) {
            $invoicePayload['clinic_booking_discount_code_id'] = $clinicDiscountCodeId;
        }

        $invoice = Invoice::create($invoicePayload);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_type' => 'lab_test',
            'item_name' => $service->name,
            'description' => $service->name,
            'quantity' => 1,
            'unit_price' => $listPrice,
            'total_price' => $listPrice,
        ]);

        $order->update(['invoice_id' => $invoice->id]);
        $invoice->generatePaymentToken();

        return ['service_order' => $order, 'invoice' => $invoice->fresh()];
    }

    private function createOrUpdatePatient(array $data, ?int $departmentId): Patient
    {
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

        $this->publicBookingService->applyPatientDataFromBooking($patient, $data, $departmentId);
        $this->syncPatientEmailFromBooking($patient, $data);

        return $patient->fresh();
    }

    private function patientDataSnapshot(array $data): array
    {
        return array_filter([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => isset($data['email']) ? trim((string) $data['email']) : null,
            'phone' => $data['phone'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private function syncPatientEmailFromBooking(Patient $patient, array $data): void
    {
        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if ($patient->email !== $email) {
            $patient->email = $email;
            $patient->save();
        }
    }

    private function recordDiscountRedemption(ServiceOrder $order): void
    {
        $invoice = $order->invoice;
        if (! $invoice) {
            if ($order->doctor_booking_discount_code_id && Schema::hasTable('doctor_booking_discount_codes')) {
                DoctorBookingDiscountCode::whereKey($order->doctor_booking_discount_code_id)->increment('uses_count');
            }
            if ($order->clinic_booking_discount_code_id && Schema::hasTable('clinic_booking_discount_codes')) {
                ClinicBookingDiscountCode::whereKey($order->clinic_booking_discount_code_id)->increment('uses_count');
            }

            return;
        }

        if ($invoice->doctor_booking_discount_code_id
            && Schema::hasColumn('invoices', 'discount_code_redemption_recorded_at')
            && $invoice->discount_code_redemption_recorded_at === null
            && Schema::hasTable('doctor_booking_discount_codes')) {
            DoctorBookingDiscountCode::whereKey($invoice->doctor_booking_discount_code_id)->increment('uses_count');
            $invoice->update(['discount_code_redemption_recorded_at' => now()]);
        }

        if ($invoice->clinic_booking_discount_code_id
            && Schema::hasColumn('invoices', 'clinic_booking_discount_code_id')
            && Schema::hasTable('clinic_booking_discount_codes')) {
            $col = Schema::hasColumn('invoices', 'clinic_discount_code_redemption_recorded_at')
                ? 'clinic_discount_code_redemption_recorded_at'
                : null;
            if ($col && $invoice->{$col} === null) {
                ClinicBookingDiscountCode::whereKey($invoice->clinic_booking_discount_code_id)->increment('uses_count');
                $invoice->update([$col => now()]);
            } elseif (! $col) {
                ClinicBookingDiscountCode::whereKey($invoice->clinic_booking_discount_code_id)->increment('uses_count');
            }
        }
    }

    private function sendNotifications(ServiceOrder $order, Patient $patient): void
    {
        try {
            $this->emailService->sendNonConsultationBookingConfirmation($order, $patient);
        } catch (\Exception $e) {
            Log::error('Failed to send non-consultation booking confirmation', ['error' => $e->getMessage()]);
        }

        try {
            $this->emailService->notifyDoctorNewServiceOrder($order, $patient);
        } catch (\Exception $e) {
            Log::error('Failed to notify doctor of service order', ['error' => $e->getMessage()]);
        }

        $this->createStaffNotifications($order, $patient);
    }

    private function createStaffNotifications(ServiceOrder $order, Patient $patient): void
    {
        try {
            $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
            $serviceName = $order->service?->name ?? 'Service';

            $notificationData = [
                'type' => UserNotification::TYPE_APPOINTMENT,
                'category' => UserNotification::CATEGORY_APPOINTMENT,
                'title' => 'New service order',
                'message' => "New order for {$serviceName} from {$patientName}. Please contact the patient.",
                'priority' => 'high',
                'action_url' => staffServiceOrderUrl('show', $order),
                'related_patient_id' => $patient->id,
                'related_doctor_id' => $order->doctor_id,
                'data' => [
                    'order_number' => $order->order_number,
                    'source' => 'public_booking_non_consultation',
                ],
            ];

            if ($order->doctor && $order->doctor->user_id) {
                $doctorUser = User::find($order->doctor->user_id);
                if ($doctorUser && $doctorUser->is_active) {
                    UserNotification::create(array_merge($notificationData, ['user_id' => $doctorUser->id]));
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed to create staff notification for service order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
