<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AuditSettlementExcludedPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settlements:audit-excluded-payments
                            {--from= : Start date (Y-m-d) for payment_date filter}
                            {--to= : End date (Y-m-d) for payment_date filter}
                            {--limit=200 : Maximum rows to display}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit completed payments excluded from doctor settlements because invoice.billing_id is missing.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $limit = max(1, (int) $this->option('limit'));

        $fromDate = null;
        $toDate = null;

        if (! empty($from)) {
            try {
                $fromDate = Carbon::parse((string) $from)->startOfDay();
            } catch (\Throwable $e) {
                $this->error("Invalid --from date: {$from}. Use Y-m-d.");
                return self::FAILURE;
            }
        }

        if (! empty($to)) {
            try {
                $toDate = Carbon::parse((string) $to)->endOfDay();
            } catch (\Throwable $e) {
                $this->error("Invalid --to date: {$to}. Use Y-m-d.");
                return self::FAILURE;
            }
        }

        if ($fromDate && $toDate && $fromDate->gt($toDate)) {
            $this->error('Invalid range: --from cannot be later than --to.');
            return self::FAILURE;
        }

        $baseQuery = Payment::query()
            ->where('status', 'completed')
            ->when($fromDate, function ($query) use ($fromDate) {
                $query->where('payment_date', '>=', $fromDate);
            })
            ->when($toDate, function ($query) use ($toDate) {
                $query->where('payment_date', '<=', $toDate);
            })
            ->where(function ($query) {
                $query->whereNull('invoice_id')
                    ->orWhereHas('invoice', function ($invoiceQuery) {
                        $invoiceQuery->whereNull('billing_id');
                    });
            });

        $excludedCount = (clone $baseQuery)->count();
        $excludedAmount = (float) (clone $baseQuery)->sum('amount');

        $rows = (clone $baseQuery)
            ->with(['invoice', 'invoice.pendingBookings.doctor.user'])
            ->orderByDesc('payment_date')
            ->limit($limit)
            ->get();

        $this->info('Completed payments excluded from settlements (missing invoice.billing_id)');
        $this->line('These payments are currently skipped by the doctor settlement calculator.');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Excluded payment count', (string) $excludedCount],
                ['Excluded payment amount', number_format($excludedAmount, 2)],
            ]
        );

        if ($rows->isEmpty()) {
            $this->info('No excluded payments found for the selected range.');
            return self::SUCCESS;
        }

        $tableRows = $rows->map(function (Payment $payment): array {
            $invoice = $payment->invoice;
            $pendingBooking = $invoice?->pendingBookings->first();
            $doctorName = $pendingBooking?->doctor?->user?->name ?? 'Unknown';
            $reason = ! $payment->invoice_id
                ? 'Missing invoice_id'
                : 'Invoice has no billing_id';

            return [
                'payment_id' => (string) $payment->id,
                'invoice_id' => $payment->invoice_id ? (string) $payment->invoice_id : 'NULL',
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i') : '—',
                'amount' => number_format((float) $payment->amount, 2),
                'doctor_hint' => $doctorName,
                'reason' => $reason,
            ];
        })->toArray();

        $this->newLine();
        $this->table(
            ['Payment ID', 'Invoice ID', 'Payment Date', 'Amount', 'Doctor (Hint)', 'Reason'],
            $tableRows
        );

        if ($excludedCount > $rows->count()) {
            $this->newLine();
            $this->warn('Output truncated. Increase --limit to see more rows.');
        }

        return self::SUCCESS;
    }
}
