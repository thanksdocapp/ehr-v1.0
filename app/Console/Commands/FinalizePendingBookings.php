<?php

namespace App\Console\Commands;

use App\Models\PendingBooking;
use App\Services\PublicBookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FinalizePendingBookings extends Command
{
    protected $signature = 'bookings:finalize-pending
                            {--dry-run : List stuck bookings without finalizing}
                            {--booking-id= : Finalize a specific pending booking ID}';

    protected $description = 'Finalize pending bookings that have completed payments but were never converted into appointments (and thus never sent doctor notification emails)';

    public function handle(PublicBookingService $bookingService): int
    {
        $query = PendingBooking::where('status', 'pending_payment')
            ->whereHas('invoice', function ($q) {
                $q->where('status', 'paid')
                  ->orWhereHas('payments', function ($pq) {
                      $pq->where('status', 'completed');
                  });
            });

        if ($bookingId = $this->option('booking-id')) {
            $query->where('id', $bookingId);
        }

        $stuck = $query->with(['doctor', 'invoice.patient', 'service'])->get();

        if ($stuck->isEmpty()) {
            $this->info('No stuck pending bookings found.');
            return self::SUCCESS;
        }

        $this->info("Found {$stuck->count()} pending booking(s) with completed payments:");
        $this->newLine();

        $rows = $stuck->map(fn ($pb) => [
            $pb->id,
            $pb->invoice?->invoice_number ?? '—',
            $pb->patient_full_name,
            $pb->doctor?->full_name ?? $pb->doctor?->name ?? '—',
            $pb->appointment_date?->format('d M Y'),
            $pb->appointment_time,
            $pb->fee,
            $pb->created_at?->diffForHumans(),
        ]);

        $this->table(
            ['ID', 'Invoice', 'Patient', 'Doctor', 'Date', 'Time', 'Fee', 'Created'],
            $rows
        );

        if ($this->option('dry-run')) {
            $this->warn('Dry-run mode — no changes made.');
            return self::SUCCESS;
        }

        $finalized = 0;
        $failed = 0;

        foreach ($stuck as $pendingBooking) {
            try {
                $result = $bookingService->finalizeBookingAfterPayment($pendingBooking);

                $appointmentNumber = $result['appointment']->appointment_number ?? '?';
                $this->info("  ✓ Booking #{$pendingBooking->id} → Appointment {$appointmentNumber} (doctor notified)");

                Log::info('Pending booking recovered via artisan command', [
                    'pending_booking_id' => $pendingBooking->id,
                    'appointment_id' => $result['appointment']->id ?? null,
                ]);

                $finalized++;
            } catch (\Exception $e) {
                $this->error("  ✗ Booking #{$pendingBooking->id}: {$e->getMessage()}");

                Log::error('Failed to recover pending booking via artisan command', [
                    'pending_booking_id' => $pendingBooking->id,
                    'error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done: {$finalized} finalized, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
