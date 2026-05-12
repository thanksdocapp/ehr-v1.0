<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorSettlement;
use App\Models\DoctorSettlementLine;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorSettlementService
{
    public function __construct(
        private readonly BookingPaymentsService $bookingPayments
    ) {}

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function periodBounds(string $periodType, Carbon $referenceDate): array
    {
        $ref = $referenceDate->copy()->startOfDay();

        if ($periodType === DoctorSettlement::PERIOD_MONTHLY) {
            return [$ref->copy()->startOfMonth(), $ref->copy()->endOfMonth()];
        }

        return [$ref->copy()->startOfWeek(), $ref->copy()->endOfWeek()];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{billing_id: int|null, description: string, amount: float}>
     */
    public function collectLineDataForPeriod(Doctor $doctor, Carbon $periodStart, Carbon $periodEnd)
    {
        $payments = $this->bookingPayments
            ->completedPaymentsForDoctorInPeriod($doctor, $periodStart, $periodEnd)
            ->with([
                'invoice.billing',
                'invoice.appointment',
                'invoice.pendingBookings',
                'invoice.pendingClinicBookings',
                'invoice.doctorBookingDiscountCode',
                'invoice.clinicBookingDiscountCode',
            ])
            ->orderBy('payment_date')
            ->get();

        return $payments->map(function (Payment $payment) {
            $billing = $payment->invoice?->billing;
            $billPart = $billing?->bill_number
                ? 'Bill '.$billing->bill_number
                : ($billing ? 'Billing #'.$billing->id : 'Invoice #'.($payment->invoice_id ?? '—'));
            $source = $this->bookingPayments->labelForPayment($payment);

            return [
                'billing_id' => $billing?->id,
                'description' => $billPart.' — '.$source.' — '.($payment->payment_date?->format('Y-m-d') ?? ''),
                'amount' => (float) $payment->amount,
            ];
        });
    }

    public function createDraftSettlement(
        Doctor $doctor,
        string $periodType,
        Carbon $referenceDate,
        ?string $notes = null
    ): DoctorSettlement {
        [$start, $end] = $this->periodBounds($periodType, $referenceDate);

        $existing = DoctorSettlement::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('period_start', $start->toDateString())
            ->whereDate('period_end', $end->toDateString())
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('A settlement for this period already exists.');
        }

        $lines = $this->collectLineDataForPeriod($doctor, $start, $end);
        $total = round((float) $lines->sum('amount'), 2);

        return DB::transaction(function () use ($doctor, $periodType, $start, $end, $notes, $lines, $total) {
            $settlement = DoctorSettlement::create([
                'doctor_id' => $doctor->id,
                'period_type' => $periodType,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'status' => DoctorSettlement::STATUS_DRAFT,
                'total_amount' => $total,
                'notes' => $notes,
            ]);

            foreach ($lines as $row) {
                DoctorSettlementLine::create([
                    'doctor_settlement_id' => $settlement->id,
                    'billing_id' => $row['billing_id'],
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                ]);
            }

            return $settlement->load('lines');
        });
    }

    public function submitForReview(DoctorSettlement $settlement): DoctorSettlement
    {
        if (! $settlement->isDraft()) {
            throw new \InvalidArgumentException('Only draft settlements can be submitted.');
        }

        $settlement->update([
            'status' => DoctorSettlement::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return $settlement->fresh();
    }
}
