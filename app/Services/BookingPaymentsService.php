<?php

namespace App\Services;

use App\Data\BookingPaymentRow;
use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BookingPaymentsService
{
    public function __construct(
        protected PatientBookingSourceService $bookingSourceService
    ) {}

    /**
     * Same inference as patient profile “Public booking source” (invoice + checkout clinic name).
     *
     * @return array{
     *     primary_label: string,
     *     clinic_name: ?string,
     *     doctor_name: ?string,
     *     department_id: ?int,
     *     evidence_line: ?string,
     *     invoice_number: ?string
     * }
     */
    public function bookingCaptureForPayment(Payment $payment): array
    {
        $inv = $payment->invoice;
        if (! $inv instanceof Invoice) {
            return [
                'primary_label' => '—',
                'clinic_name' => null,
                'doctor_name' => null,
                'department_id' => null,
                'evidence_line' => null,
                'invoice_number' => null,
            ];
        }

        return $this->bookingSourceService->invoiceBookingCapture($inv);
    }

    public function formatCaptureForDisplay(array $capture, string $separator = ' · '): string
    {
        return $this->bookingSourceService->formatCaptureForDisplay($capture, $separator);
    }

    /**
     * Department IDs for a doctor (pivot + legacy department_id). Used to attribute
     * public clinic-booking checkout payments, which only link via pending_clinic_bookings
     * or clinic discount — not via invoices.appointment_id (often unset until backfilled).
     */
    public function departmentIdsForDoctor(Doctor $doctor): array
    {
        $deptTable = (new Department)->getTable();
        $fromPivot = $doctor->departments()->pluck($deptTable.'.id')->all();
        $legacy = $doctor->department_id ? [(int) $doctor->department_id] : [];

        return array_values(array_unique(array_merge($fromPivot, $legacy)));
    }

    /**
     * All completed patient payments (every row in `payments` is tied to an invoice).
     * Used for admin lists so legacy payments still appear even when the invoice predates
     * booking/discount linkage fields.
     */
    public function completedBookingPaymentsBase(): Builder
    {
        return Payment::query()->completed()->withoutDuplicateBillingSync();
    }

    /**
     * Same widened logic, scoped to a doctor (appointment, pending booking, billing,
     * doctor discount code, clinic discount for their clinic(s), or clinic checkout
     * pending rows for their department(s)).
     */
    public function completedPaymentsForDoctor(Doctor $doctor): Builder
    {
        $doctorId = $doctor->id;
        $departmentIds = $this->departmentIdsForDoctor($doctor);

        return Payment::query()
            ->completed()
            ->withoutDuplicateBillingSync()
            ->whereHas('invoice', function ($q) use ($doctorId, $departmentIds) {
                $q->where(function ($q2) use ($doctorId, $departmentIds) {
                    $q2->where(function ($q3) use ($doctorId) {
                        $q3->whereNotNull('appointment_id')
                            ->whereHas('appointment', fn ($a) => $a->where('doctor_id', $doctorId));
                    })
                        ->orWhere(function ($q3) use ($doctorId) {
                            $q3->whereNull('appointment_id')
                                ->whereHas('pendingBookings', fn ($pb) => $pb->where('doctor_id', $doctorId));
                        })
                        ->orWhereHas('billing', fn ($b) => $b->where('doctor_id', $doctorId))
                        ->orWhereHas('doctorBookingDiscountCode', fn ($c) => $c->where('doctor_id', $doctorId))
                        ->orWhere(function ($q3) use ($doctorId, $departmentIds) {
                            $q3->whereNotNull('clinic_booking_discount_code_id')
                                ->where(function ($q4) use ($doctorId, $departmentIds) {
                                    $q4->whereHas('appointment', fn ($a) => $a->where('doctor_id', $doctorId))
                                        ->orWhereHas('billing', fn ($b) => $b->where('doctor_id', $doctorId));
                                    if ($departmentIds !== []) {
                                        $q4->orWhereHas('clinicBookingDiscountCode', fn ($c) => $c->whereIn('department_id', $departmentIds));
                                    }
                                });
                        });

                    if ($departmentIds !== []) {
                        $q2->orWhereHas('pendingClinicBookings', fn ($pcb) => $pcb->whereIn('department_id', $departmentIds));
                    }

                    $q2->orWhereHas('serviceOrder', fn ($so) => $so->where('doctor_id', $doctorId));
                });
            });
    }

    /**
     * Completed payments for a doctor restricted to a calendar period (inclusive of start/end days).
     * Uses the same attribution rules as {@see completedPaymentsForDoctor} (appointments, billing,
     * pending bookings, clinic checkout, discount codes).
     */
    public function completedPaymentsForDoctorInPeriod(Doctor $doctor, Carbon $periodStart, Carbon $periodEnd): Builder
    {
        // Match admin booking-payments date filters (calendar days in app timezone).
        return $this->completedPaymentsForDoctor($doctor)
            ->whereDate('payment_date', '>=', $periodStart->toDateString())
            ->whereDate('payment_date', '<=', $periodEnd->toDateString());
    }

    /**
     * Keep payments whose invoice relates to the given department/clinic (matches clinicNameForBookingPayment logic).
     */
    public function restrictPaymentsToDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->whereHas('invoice', function ($inv) use ($departmentId) {
            $inv->where(function ($q) use ($departmentId) {
                $q->whereHas('appointment', fn ($a) => $a->where('department_id', $departmentId))
                    ->orWhereHas('billing', function ($b) use ($departmentId) {
                        $b->where(function ($inner) use ($departmentId) {
                            $inner->whereHas('appointment', fn ($a) => $a->where('department_id', $departmentId))
                                ->orWhereHas('doctor', function ($doc) use ($departmentId) {
                                    $doc->where(function ($dq) use ($departmentId) {
                                        $dq->where('department_id', $departmentId)
                                            ->orWhereHas('departments', function ($dep) use ($departmentId) {
                                                $dep->where('departments.id', $departmentId);
                                            });
                                    });
                                });
                        });
                    })
                    ->orWhereHas('pendingBookings', fn ($pb) => $pb->where('department_id', $departmentId))
                    ->orWhereHas('pendingClinicBookings', fn ($pcb) => $pcb->where('department_id', $departmentId))
                    ->orWhereHas('clinicBookingDiscountCode', fn ($c) => $c->where('department_id', $departmentId))
                    ->orWhereHas('doctorBookingDiscountCode.doctor', function ($doc) use ($departmentId) {
                        $doc->where(function ($dq) use ($departmentId) {
                            $dq->where('department_id', $departmentId)
                                ->orWhereHas('departments', function ($dep) use ($departmentId) {
                                    $dep->where('departments.id', $departmentId);
                                });
                        });
                    })
                    ->orWhereHas('serviceOrder', fn ($so) => $so->where('department_id', $departmentId));
            });
        });
    }

    /**
     * @return array<int, string>
     */
    public function bookingPaymentsEagerLoads(): array
    {
        return [
            'invoice.patient',
            'invoice.appointment.doctor.user',
            'invoice.appointment.department',
            'invoice.pendingBookings.doctor.user',
            'invoice.pendingBookings.department',
            'invoice.pendingClinicBookings.department',
            'invoice.billing.doctor.user',
            'invoice.billing.doctor.department',
            'invoice.billing.doctor.departments',
            'invoice.billing.appointment.doctor.user',
            'invoice.billing.appointment.department',
            'invoice.doctorBookingDiscountCode.doctor.user',
            'invoice.doctorBookingDiscountCode.doctor.department',
            'invoice.doctorBookingDiscountCode.doctor.departments',
            'invoice.clinicBookingDiscountCode.department',
            'invoice.serviceOrder.doctor.user',
            'invoice.serviceOrder.department',
            'invoice.serviceOrder.service',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function freeServiceOrderEagerLoads(): array
    {
        return [
            'patient',
            'doctor.user',
            'department',
            'service',
        ];
    }

    public function buildFilteredPaymentsQuery(Request $request): Builder
    {
        $query = $request->filled('doctor_id')
            ? $this->completedPaymentsForDoctor(Doctor::findOrFail($request->integer('doctor_id')))
            : $this->completedBookingPaymentsBase();

        if ($request->filled('department_id')) {
            $departmentId = $request->integer('department_id');
            Department::query()->where('is_active', true)->whereKey($departmentId)->firstOrFail();
            $query = $this->restrictPaymentsToDepartment($query, $departmentId);
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->string('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->string('to'));
        }

        return $query;
    }

    /**
     * Paid / fulfilled service orders for the booking-payments report (includes
     * pending_payment rows whose invoice is already paid — common after redirect).
     */
    public function serviceOrdersForReport(Request $request): Builder
    {
        if (! Schema::hasTable('service_orders')) {
            return ServiceOrder::query()->whereRaw('1 = 0');
        }

        $query = ServiceOrder::query()->where(function ($outer) {
            $outer->whereIn('status', [
                ServiceOrder::STATUS_PAID,
                ServiceOrder::STATUS_CONTACTED,
                ServiceOrder::STATUS_COMPLETED,
            ])->orWhere(function ($sub) {
                $sub->where('status', ServiceOrder::STATUS_PENDING_PAYMENT)
                    ->whereNotNull('invoice_id')
                    ->where(function ($paid) {
                        $paid->whereHas('invoice', fn ($inv) => $inv->where('status', 'paid'))
                            ->orWhereHas('invoice.payments', fn ($p) => $p->where('status', 'completed'));
                    });
            });
        });

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->integer('doctor_id'));
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        $this->applyServiceOrderReportDateFilters($query, $request);

        return $query;
    }

    /**
     * Pre–service_orders non-consultation checkouts: lab_test invoice lines without a service_orders row.
     */
    public function legacyNonConsultationPaymentsForReport(Request $request): Builder
    {
        if (! Schema::hasTable('service_orders')) {
            return Payment::query()->whereRaw('1 = 0');
        }

        $query = Payment::query()
            ->completed()
            ->withoutDuplicateBillingSync()
            ->whereHas('invoice', function ($inv) {
                $inv->whereNull('appointment_id')
                    ->whereDoesntHave('serviceOrder')
                    ->whereDoesntHave('pendingBookings')
                    ->whereDoesntHave('pendingClinicBookings')
                    ->whereHas('invoiceItems', fn ($item) => $item->where('item_type', 'lab_test'));
            });

        if ($request->filled('doctor_id')) {
            $doctorId = $request->integer('doctor_id');
            $departmentIds = $this->departmentIdsForDoctor(Doctor::findOrFail($doctorId));
            $query->whereHas('invoice', function ($inv) use ($doctorId, $departmentIds) {
                $inv->where(function ($q) use ($doctorId, $departmentIds) {
                    $q->whereHas('doctorBookingDiscountCode', fn ($c) => $c->where('doctor_id', $doctorId));
                    if ($departmentIds !== []) {
                        $q->orWhereHas('clinicBookingDiscountCode', fn ($c) => $c->whereIn('department_id', $departmentIds));
                    }
                });
            });
        }

        if ($request->filled('department_id')) {
            $departmentId = $request->integer('department_id');
            $query->whereHas('invoice', function ($inv) use ($departmentId) {
                $inv->whereHas('clinicBookingDiscountCode', fn ($c) => $c->where('department_id', $departmentId));
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->string('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->string('to'));
        }

        return $query;
    }

    /**
     * @return Collection<int, BookingPaymentRow>
     */
    public function collectBookingPaymentRows(Request $request): Collection
    {
        $seenPaymentIds = [];
        $seenOrderOnlyIds = [];
        $rows = collect();

        foreach ($this->buildFilteredPaymentsQuery($request)
            ->with($this->bookingPaymentsEagerLoads())
            ->orderByDesc('payment_date')
            ->get() as $payment) {
            $seenPaymentIds[$payment->id] = true;
            $rows->push(BookingPaymentRow::fromPayment($payment));
        }

        if (Schema::hasTable('service_orders')) {
            $orders = $this->serviceOrdersForReport($request)
                ->with($this->freeServiceOrderEagerLoads())
                ->with(['invoice.payments'])
                ->orderByDesc('paid_at')
                ->orderByDesc('created_at')
                ->get();

            foreach ($orders as $order) {
                if ($order->invoice_id) {
                    $payment = $this->primaryCompletedPaymentForInvoice((int) $order->invoice_id);
                    if ($payment && ! $this->paymentMatchesReportDateFilter($payment, $request)) {
                        continue;
                    }
                    if ($payment) {
                        if (! isset($seenPaymentIds[$payment->id])) {
                            $payment->load($this->bookingPaymentsEagerLoads());
                            $seenPaymentIds[$payment->id] = true;
                            $rows->push(BookingPaymentRow::fromPayment($payment));
                        }

                        continue;
                    }
                }

                if ($order->invoice_id) {
                    continue;
                }

                if (! in_array($order->status, [
                    ServiceOrder::STATUS_PAID,
                    ServiceOrder::STATUS_CONTACTED,
                    ServiceOrder::STATUS_COMPLETED,
                ], true)) {
                    continue;
                }

                if (isset($seenOrderOnlyIds[$order->id])) {
                    continue;
                }
                if (! $this->serviceOrderMatchesReportDateFilter($order, null, $request)) {
                    continue;
                }

                $seenOrderOnlyIds[$order->id] = true;
                $rows->push(BookingPaymentRow::fromFreeServiceOrder($order));
            }

            foreach ($this->legacyNonConsultationPaymentsForReport($request)
                ->with($this->bookingPaymentsEagerLoads())
                ->orderByDesc('payment_date')
                ->get() as $payment) {
                if (! isset($seenPaymentIds[$payment->id])) {
                    $seenPaymentIds[$payment->id] = true;
                    $rows->push(BookingPaymentRow::fromPayment($payment));
                }
            }
        }

        return $rows->sortByDesc(fn (BookingPaymentRow $row) => $row->sortAt()?->timestamp ?? 0)->values();
    }

    public function paginateBookingPaymentRows(Request $request, int $perPage = 30): LengthAwarePaginator
    {
        $rows = $this->collectBookingPaymentRows($request);
        $page = max(1, (int) $request->input('page', 1));
        $total = $rows->count();
        $slice = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function totalAmountForBookingPaymentRows(Request $request): float
    {
        return (float) $this->collectBookingPaymentRows($request)->sum(fn (BookingPaymentRow $row) => $row->amount());
    }

    public function labelForRow(BookingPaymentRow $row): string
    {
        if ($row->payment) {
            return $this->labelForPayment($row->payment);
        }

        return 'Service order';
    }

    /**
     * @return array{primary_label: string, clinic_name: ?string, doctor_name: ?string, department_id: ?int, evidence_line: ?string, invoice_number: ?string}
     */
    public function bookingCaptureForRow(BookingPaymentRow $row): array
    {
        if ($row->payment) {
            return $this->bookingCaptureForPayment($row->payment);
        }

        if ($row->serviceOrder) {
            return $this->bookingSourceService->serviceOrderBookingCapture($row->serviceOrder);
        }

        return [
            'primary_label' => '—',
            'clinic_name' => null,
            'doctor_name' => null,
            'department_id' => null,
            'evidence_line' => null,
            'invoice_number' => null,
        ];
    }

    public function doctorNameForRow(BookingPaymentRow $row): ?string
    {
        $capture = $this->bookingCaptureForRow($row);
        if ($capture['doctor_name']) {
            return $capture['doctor_name'];
        }

        if ($row->payment) {
            return $this->doctorNameForBookingPayment($row->payment);
        }

        return $this->formatDoctorName($row->serviceOrder?->doctor);
    }

    public function patientNameForRow(BookingPaymentRow $row): string
    {
        if ($row->payment) {
            return $this->patientNameForBookingPayment($row->payment);
        }

        $patient = $row->serviceOrder?->patient;
        if ($patient) {
            $n = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));

            return $n !== '' ? $n : '—';
        }

        $data = $row->serviceOrder?->patient_data ?? [];
        $n = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

        return $n !== '' ? $n : '—';
    }

    public function appointmentSlotLabelForRow(BookingPaymentRow $row): string
    {
        if ($row->payment) {
            return $this->appointmentSlotLabelForBookingPayment($row->payment);
        }

        return 'No appointment (service order)';
    }

    public function commentsForRow(BookingPaymentRow $row): string
    {
        if ($row->payment) {
            return $this->commentsForBookingPayment($row->payment);
        }

        $parts = [];
        if ($row->serviceOrder && filled($row->serviceOrder->notes)) {
            $parts[] = Str::limit(trim(strip_tags((string) $row->serviceOrder->notes)), 800);
        }
        if ($row->isFreeServiceOrder()) {
            $parts[] = 'Complimentary service order (no payment collected)';
        } elseif ($row->serviceOrder && ! $row->payment) {
            $parts[] = 'Service order (no payment row on file)';
        }

        return $parts !== [] ? implode(' | ', $parts) : '';
    }

    public function methodLabelForRow(BookingPaymentRow $row): string
    {
        if ($row->payment) {
            return $row->payment->payment_method_label;
        }

        if ($row->isFreeServiceOrder()) {
            return 'No charge';
        }

        if ($row->serviceOrder) {
            return 'Service order';
        }

        return '—';
    }

    public function invoiceLabelForRow(BookingPaymentRow $row): string
    {
        if ($row->payment?->invoice) {
            $inv = $row->payment->invoice;

            return $inv->invoice_number ?? ('#'.$inv->id);
        }

        if ($row->serviceOrder) {
            return $row->serviceOrder->order_number;
        }

        return '—';
    }

    /**
     * Human-readable source for UI (one primary reason, deterministic priority).
     */
    public function labelForPayment(Payment $payment): string
    {
        $inv = $payment->invoice;
        if (! $inv) {
            return '—';
        }

        if ($this->invoiceServiceOrder($inv)) {
            return 'Service order';
        }

        if ($inv->appointment_id) {
            return 'Appointment';
        }

        if ($inv->relationLoaded('pendingBookings')) {
            if ($inv->pendingBookings->isNotEmpty()) {
                return 'Pending booking';
            }
        } elseif ($inv->pendingBookings()->exists()) {
            return 'Pending booking';
        }

        if ($inv->relationLoaded('pendingClinicBookings')) {
            if ($inv->pendingClinicBookings->isNotEmpty()) {
                return 'Clinic booking checkout';
            }
        } else        if ($inv->pendingClinicBookings()->exists()) {
            return 'Clinic booking checkout';
        }

        if ($this->invoiceServiceOrder($inv)) {
            return 'Service order';
        }

        if ($inv->billing_id) {
            $billing = $inv->relationLoaded('billing') ? $inv->billing : $inv->billing()->first();
            if ($billing && $billing->appointment_id) {
                return 'Visit billing';
            }

            return 'Billing';
        }

        if ($inv->doctor_booking_discount_code_id) {
            return 'Doctor booking offer';
        }

        if ($inv->clinic_booking_discount_code_id) {
            return 'Clinic booking offer';
        }

        return 'Invoice';
    }

    /**
     * @return array{total_this_month: float, total_this_week: float, total_all_time: float, payment_count: int}
     */
    public function doctorBookingPaymentStats(Doctor $doctor): array
    {
        $base = $this->completedPaymentsForDoctor($doctor);

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $totalMonth = (float) (clone $base)
            ->whereBetween('payment_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $totalWeek = (float) (clone $base)
            ->whereBetween('payment_date', [$weekStart, $weekEnd])
            ->sum('amount');

        $totalAll = (float) (clone $base)->sum('amount');
        $count = (clone $base)->count();

        if (Schema::hasTable('service_orders')) {
            $freeBase = ServiceOrder::query()
                ->where('status', ServiceOrder::STATUS_PAID)
                ->whereNull('invoice_id')
                ->where('doctor_id', $doctor->id);
            $count += (clone $freeBase)->count();
        }

        return [
            'total_this_month' => round($totalMonth, 2),
            'total_this_week' => round($totalWeek, 2),
            'total_all_time' => round($totalAll, 2),
            'payment_count' => $count,
        ];
    }

    /**
     * Relating doctor for admin booking-payments list (deterministic priority).
     */
    public function doctorNameForBookingPayment(Payment $payment): ?string
    {
        $capture = $this->bookingCaptureForPayment($payment);
        if ($capture['doctor_name']) {
            return $capture['doctor_name'];
        }

        $inv = $payment->invoice;
        if (! $inv instanceof Invoice) {
            return null;
        }

        $doctor = null;

        if ($inv->appointment_id && $inv->appointment) {
            $doctor = $inv->appointment->doctor;
        }

        if (! $doctor && $inv->billing) {
            $billing = $inv->billing;
            $doctor = $billing->appointment?->doctor ?? $billing->doctor;
        }

        if (! $doctor && $inv->pendingBookings->isNotEmpty()) {
            $doctor = $inv->pendingBookings->first()?->doctor;
        }

        if (! $doctor && $inv->pendingClinicBookings->isNotEmpty()) {
            $doctor = $this->soleDoctorForDepartment($inv->pendingClinicBookings->first()?->department_id);
        }

        if (! $doctor && $inv->patient_id) {
            $acceptedRequest = $this->latestAcceptedClinicRequestForInvoice($inv);
            if ($acceptedRequest) {
                $doctor = $acceptedRequest->doctor;
            }
        }

        if (! $doctor && $inv->doctorBookingDiscountCode) {
            $doctor = $inv->doctorBookingDiscountCode->doctor;
        }

        return $this->formatDoctorName($doctor);
    }

    /**
     * Relating clinic (department) for admin booking-payments list.
     */
    public function clinicNameForBookingPayment(Payment $payment): ?string
    {
        $capture = $this->bookingCaptureForPayment($payment);
        if ($capture['clinic_name']) {
            return $capture['clinic_name'];
        }

        $inv = $payment->invoice;
        if (! $inv instanceof Invoice) {
            return null;
        }

        if ($inv->appointment_id && $inv->appointment?->department) {
            return $inv->appointment->department->name;
        }

        if ($inv->billing) {
            $billing = $inv->billing;
            if ($billing->appointment_id && $billing->appointment?->department) {
                return $billing->appointment->department->name;
            }
            if ($billing->doctor) {
                $doc = $billing->doctor;
                if ($doc->departments->isNotEmpty()) {
                    return $doc->departments->first()->name;
                }

                return $doc->department?->name;
            }
        }

        if ($inv->pendingBookings->isNotEmpty()) {
            $dept = $inv->pendingBookings->first()?->department;
            if ($dept) {
                return $dept->name;
            }
        }

        if ($inv->pendingClinicBookings->isNotEmpty()) {
            $dept = $inv->pendingClinicBookings->first()?->department;
            if ($dept) {
                return $dept->name;
            }
        }

        if ($inv->patient_id) {
            $acceptedRequest = $this->latestAcceptedClinicRequestForInvoice($inv, withDepartment: true);
            if ($acceptedRequest?->department) {
                return $acceptedRequest->department->name;
            }
        }

        if ($inv->clinicBookingDiscountCode?->department) {
            return $inv->clinicBookingDiscountCode->department->name;
        }

        if ($inv->doctorBookingDiscountCode?->doctor) {
            $doc = $inv->doctorBookingDiscountCode->doctor;
            if ($doc->departments->isNotEmpty()) {
                return $doc->departments->first()->name;
            }

            return $doc->department?->name;
        }

        return null;
    }

    /**
     * Combined comments for exports / list: payment notes, invoice notes, invoice description.
     */
    public function commentsForBookingPayment(Payment $payment): string
    {
        $parts = [];
        if (filled($payment->notes)) {
            $parts[] = Str::limit(trim(strip_tags((string) $payment->notes)), 800);
        }
        $inv = $payment->invoice;
        if ($inv) {
            if (filled($inv->notes)) {
                $parts[] = Str::limit(trim(strip_tags((string) $inv->notes)), 800);
            }
            if (filled($inv->description ?? null)) {
                $parts[] = Str::limit(trim(strip_tags((string) $inv->description)), 800);
            }
        }
        $parts = array_values(array_unique(array_filter($parts)));

        return $parts !== [] ? implode(' | ', $parts) : '';
    }

    public function patientNameForBookingPayment(Payment $payment): string
    {
        $patient = $payment->invoice?->patient;
        if (! $patient) {
            return '—';
        }
        $n = trim(($patient->first_name ?? '').' '.($patient->last_name ?? ''));

        return $n !== '' ? $n : '—';
    }

    public function appointmentSlotLabelForBookingPayment(Payment $payment): string
    {
        $inv = $payment->invoice;
        if (! $inv) {
            return '—';
        }
        if ($inv->appointment_id && $inv->appointment) {
            return $this->formatAppointmentSlotForExport($inv->appointment);
        }
        if ($inv->billing?->appointment) {
            return $this->formatAppointmentSlotForExport($inv->billing->appointment).' (billing)';
        }
        if ($inv->pendingBookings->isNotEmpty()) {
            return 'Pending booking';
        }

        if ($inv->relationLoaded('pendingClinicBookings')) {
            $pcb = $inv->pendingClinicBookings->first();
        } else {
            $pcb = $inv->pendingClinicBookings()->first();
        }
        if ($pcb) {
            return $this->formatPendingClinicBookingSlotLabel($pcb);
        }

        if ($this->invoiceServiceOrder($inv)) {
            return 'No appointment (service order)';
        }

        return '—';
    }

    private function invoiceServiceOrder(Invoice $inv): ?ServiceOrder
    {
        if ($inv->relationLoaded('serviceOrder')) {
            return $inv->serviceOrder;
        }

        if (! Schema::hasTable('service_orders')) {
            return null;
        }

        return $inv->serviceOrder()->first();
    }

    private function primaryCompletedPaymentForInvoice(int $invoiceId): ?Payment
    {
        $portal = Payment::query()
            ->where('invoice_id', $invoiceId)
            ->where('status', 'completed')
            ->where(function ($q) {
                $q->whereNull('transaction_reference')
                    ->orWhere('transaction_reference', 'not like', 'BILLING_%');
            })
            ->orderByDesc('payment_date')
            ->first();

        if ($portal) {
            return $portal;
        }

        return Payment::query()
            ->where('invoice_id', $invoiceId)
            ->where('status', 'completed')
            ->orderByDesc('payment_date')
            ->first();
    }

    private function applyServiceOrderReportDateFilters(Builder $query, Request $request): void
    {
        if ($request->filled('from')) {
            $from = $request->string('from');
            $query->where(function ($q) use ($from) {
                $q->whereDate('paid_at', '>=', $from)
                    ->orWhere(function ($q2) use ($from) {
                        $q2->whereNull('paid_at')->whereDate('created_at', '>=', $from);
                    })
                    ->orWhereHas('invoice.payments', function ($p) use ($from) {
                        $p->where('status', 'completed')->whereDate('payment_date', '>=', $from);
                    });
            });
        }

        if ($request->filled('to')) {
            $to = $request->string('to');
            $query->where(function ($q) use ($to) {
                $q->where(function ($q1) use ($to) {
                    $q1->whereNotNull('paid_at')->whereDate('paid_at', '<=', $to);
                })
                    ->orWhere(function ($q2) use ($to) {
                        $q2->whereNull('paid_at')->whereDate('created_at', '<=', $to);
                    })
                    ->orWhereHas('invoice.payments', function ($p) use ($to) {
                        $p->where('status', 'completed')->whereDate('payment_date', '<=', $to);
                    });
            });
        }
    }

    private function paymentMatchesReportDateFilter(Payment $payment, Request $request): bool
    {
        if (! $request->filled('from') && ! $request->filled('to')) {
            return true;
        }

        $date = $payment->payment_date;
        if (! $date) {
            return false;
        }

        if ($request->filled('from') && $date->toDateString() < $request->string('from')) {
            return false;
        }

        if ($request->filled('to') && $date->toDateString() > $request->string('to')) {
            return false;
        }

        return true;
    }

    private function serviceOrderMatchesReportDateFilter(
        ServiceOrder $order,
        ?Payment $payment,
        Request $request
    ): bool {
        if ($payment) {
            return $this->paymentMatchesReportDateFilter($payment, $request);
        }

        if (! $request->filled('from') && ! $request->filled('to')) {
            return true;
        }

        $date = $order->paid_at ?? $order->created_at;
        if (! $date) {
            return false;
        }

        if ($request->filled('from') && $date->toDateString() < $request->string('from')) {
            return false;
        }

        if ($request->filled('to') && $date->toDateString() > $request->string('to')) {
            return false;
        }

        return true;
    }

    private function formatAppointmentSlotForExport($appt): string
    {
        if (! $appt || ! $appt->appointment_date) {
            return '—';
        }
        $d = formatDateUk($appt->appointment_date);
        if (! empty($appt->appointment_time)) {
            $d .= ', '.formatTime($appt->appointment_time, 'g:i A');
        }

        return $d;
    }

    /**
     * @param  \App\Models\PendingClinicBooking|object  $pcb
     */
    private function formatPendingClinicBookingSlotLabel($pcb): string
    {
        if (! $pcb || ! $pcb->appointment_date) {
            return 'Clinic booking checkout';
        }
        $d = formatDateUk($pcb->appointment_date);
        if (! empty($pcb->appointment_time)) {
            $d .= ', '.formatTime($pcb->appointment_time, 'g:i A');
        }

        return $d.' (clinic checkout)';
    }

    private function formatDoctorName(?Doctor $doctor): ?string
    {
        if (! $doctor) {
            return null;
        }

        $name = $doctor->user?->name ?? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? ''));

        return $name !== '' ? $name : null;
    }

    private function latestAcceptedClinicRequestForInvoice(
        Invoice $inv,
        bool $withDepartment = false
    ): ?ClinicBookingRequest {
        if (! $inv->patient_id) {
            return null;
        }

        $pcbDeptId = $inv->pendingClinicBookings->first()?->department_id;
        $query = ClinicBookingRequest::query()
            ->where('patient_id', $inv->patient_id)
            ->where('status', 'accepted')
            ->when($pcbDeptId, fn ($q) => $q->where('department_id', $pcbDeptId));

        $query->with($withDepartment ? ['department', 'doctor.user'] : ['doctor.user']);

        $table = (new ClinicBookingRequest)->getTable();
        if (Schema::hasColumn($table, 'accepted_at')) {
            $query->orderByDesc('accepted_at');
        }

        return $query->orderByDesc('updated_at')->first();
    }

    private function soleDoctorForDepartment(?int $departmentId): ?Doctor
    {
        if (! $departmentId) {
            return null;
        }

        return app(ClinicBookingService::class)->defaultDoctorForDepartment($departmentId);
    }
}
