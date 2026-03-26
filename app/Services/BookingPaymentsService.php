<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BookingPaymentsService
{
    /**
     * Completed payments on invoices linked to clinical / booking activity:
     * appointment, pending public booking, billing record, or booking discount codes.
     */
    public function completedBookingPaymentsBase(): Builder
    {
        return Payment::query()
            ->completed()
            ->whereHas('invoice', function ($q) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('appointment_id')
                        ->orWhereHas('pendingBookings')
                        ->orWhereNotNull('billing_id')
                        ->orWhereNotNull('doctor_booking_discount_code_id')
                        ->orWhereNotNull('clinic_booking_discount_code_id');
                });
            });
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
}
