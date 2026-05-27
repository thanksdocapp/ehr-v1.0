<?php

namespace App\Services;

use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Payment;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BookingPaymentsService
{
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
        return Payment::query()->completed();
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
                    });
            });
        });
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
        } elseif ($inv->pendingClinicBookings()->exists()) {
            return 'Clinic booking checkout';
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

        return '—';
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

        $doctors = Doctor::byDepartment($departmentId)->active()->get();
        if ($doctors->count() !== 1) {
            return null;
        }

        return $doctors->first();
    }
}
