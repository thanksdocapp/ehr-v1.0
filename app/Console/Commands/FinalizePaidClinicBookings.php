<?php

namespace App\Console\Commands;

use App\Services\ClinicBookingService;
use Illuminate\Console\Command;

class FinalizePaidClinicBookings extends Command
{
    protected $signature = 'clinic-bookings:finalize-paid
                            {--dry-run : List how many rows would be processed without changing data}';

    protected $description = 'Finalize all clinic bookings that were paid but never completed (repair + safety net)';

    public function handle(ClinicBookingService $clinicBookingService): int
    {
        if ($this->option('dry-run')) {
            $count = \App\Models\PendingClinicBooking::query()
                ->whereNotNull('invoice_id')
                ->whereIn('status', ['pending_payment', 'expired'])
                ->whereHas('invoice', function ($q) {
                    $q->where('status', 'paid')
                        ->orWhereHas('payments', fn ($p) => $p->where('status', 'completed'));
                })
                ->count();

            $this->info("Would attempt to finalize {$count} paid clinic booking(s).");

            return self::SUCCESS;
        }

        $stats = $clinicBookingService->finalizeAllStuckPaidClinicBookings();

        $this->info("Finalized: {$stats['finalized']}, skipped: {$stats['skipped']}, failed: {$stats['failed']}");

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
