<?php

namespace App\Services;

use App\Models\Billing;
use App\Models\Doctor;
use App\Models\DoctorSettlement;
use App\Models\DoctorSettlementLine;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorSettlementService
{
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
        $payments = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
            ->whereHas('invoice.billing', function ($query) use ($doctor) {
                $query->where('doctor_id', $doctor->id);
            })
            ->with(['invoice.billing'])
            ->orderBy('payment_date')
            ->get();

        return $payments->map(function (Payment $payment) {
            /** @var Billing|null $billing */
            $billing = $payment->invoice?->billing;
            $label = $billing->bill_number
                ? 'Bill '.$billing->bill_number
                : 'Billing #'.($billing?->id ?? '—');

            return [
                'billing_id' => $billing?->id,
                'description' => $label.' — payment '.($payment->payment_date?->format('Y-m-d') ?? ''),
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
