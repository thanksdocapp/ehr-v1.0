<?php

namespace App\Console\Commands;

use App\Models\PendingClinicBooking;
use App\Services\ClinicBookingService;
use Illuminate\Console\Command;

class FinalizePaidClinicBookings extends Command
{
    protected $signature = 'clinic-bookings:finalize-paid
                            {--invoice= : Only finalize for this invoice number (e.g. INV2026060080)}
                            {--dry-run : List rows that would be finalized without changing data}';

    protected $description = 'Finalize clinic bookings that were paid but never completed (e.g. lost session after Stripe redirect)';

    public function handle(ClinicBookingService $clinicBookingService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $invoiceNumber = $this->option('invoice');

        $query = PendingClinicBooking::query()
            ->where('status', 'pending_payment')
            ->whereNotNull('invoice_id')
            ->with(['invoice.payments', 'department']);

        if ($invoiceNumber) {
            $query->whereHas('invoice', fn ($q) => $q->where('invoice_number', $invoiceNumber));
        }

        $pendings = $query->get();
        if ($pendings->isEmpty()) {
            $this->info('No pending clinic bookings awaiting finalization.');

            return self::SUCCESS;
        }

        $finalized = 0;
        $skipped = 0;

        foreach ($pendings as $pending) {
            $invoice = $pending->invoice;
            if (! $invoice) {
                $this->warn("Pending #{$pending->id}: invoice missing, skipped.");
                $skipped++;

                continue;
            }

            $isPaid = $invoice->status === 'paid'
                || $invoice->payments->contains(fn ($p) => $p->status === 'completed');

            if (! $isPaid) {
                $this->line("Pending #{$pending->id} ({$invoice->invoice_number}): invoice not paid, skipped.");
                $skipped++;

                continue;
            }

            $patientName = trim(
                ($pending->patient_data['first_name'] ?? '').' '.($pending->patient_data['last_name'] ?? '')
            );

            if ($dryRun) {
                $this->info("Would finalize: {$invoice->invoice_number} — {$patientName} @ {$pending->department?->name}");
                $finalized++;

                continue;
            }

            try {
                $request = $clinicBookingService->finalizeClinicBookingAfterPayment($pending);
                $this->info("Finalized: {$invoice->invoice_number} → request {$request->request_number}");
                $finalized++;
            } catch (\Throwable $e) {
                $this->error("Failed {$invoice->invoice_number}: {$e->getMessage()}");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? 'Would finalize' : 'Finalized').": {$finalized}, skipped: {$skipped}");

        return self::SUCCESS;
    }
}
