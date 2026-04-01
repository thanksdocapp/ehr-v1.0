<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Payment;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BookingPaymentsService
{
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
     * doctor discount code, or clinic discount when visit/billing is theirs).
     */
    public function completedPaymentsForDoctor(Doctor $doctor): Builder
    {
        $doctorId = $doctor->id;

        return Payment::query()
            ->completed()
            ->whereHas('invoice', function ($q) use ($doctorId) {
                $q->where(function ($q2) use ($doctorId) {
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
                        ->orWhere(function ($q3) use ($doctorId) {
                            $q3->whereNotNull('clinic_booking_discount_code_id')
                                ->where(function ($q4) use ($doctorId) {
                                    $q4->whereHas('appointment', fn ($a) => $a->where('doctor_id', $doctorId))
                                        ->orWhereHas('billing', fn ($b) => $b->where('doctor_id', $doctorId));
                                });
                        });
                });
            });
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

    private function formatDoctorName(?Doctor $doctor): ?string
    {
        if (! $doctor) {
            return null;
        }

        $name = $doctor->user?->name ?? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? ''));

        return $name !== '' ? $name : null;
    }
}
