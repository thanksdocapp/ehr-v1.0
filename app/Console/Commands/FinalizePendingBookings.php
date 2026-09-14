<?php

namespace App\Console\Commands;

use App\Models\PendingBooking;
use App\Models\PendingClinicBooking;
use App\Models\ServiceOrder;
use App\Services\ClinicBookingService;
use App\Services\NonConsultationBookingService;
use App\Services\PublicBookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class FinalizePendingBookings extends Command
{
    protected $signature = 'bookings:finalize-pending
                            {--dry-run : List stuck bookings without finalizing}
                            {--booking-id= : Finalize a specific pending booking ID (doctor bookings only)}
                            {--force : Skip slot availability checks (use when the patient already paid but the slot is now taken or blocked)}
                            {--type= : Limit to a specific type: doctor, clinic, service (default: all)}';

    protected $description = 'Finalize pending bookings (doctor, clinic, and service orders) that have completed payments but were never converted into appointments or orders';

    public function handle(
        PublicBookingService $bookingService,
        ClinicBookingService $clinicBookingService,
        NonConsultationBookingService $nonConsultationService
    ): int {
        $type = $this->option('type');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $anyFound = false;
        $totalFinalized = 0;
        $totalFailed = 0;

        if (! $type || $type === 'doctor') {
            [$found, $finalized, $failed] = $this->processDoctorBookings($bookingService, $dryRun, $force);
            $anyFound = $anyFound || $found;
            $totalFinalized += $finalized;
            $totalFailed += $failed;
        }

        if (! $type || $type === 'clinic') {
            [$found, $finalized, $failed] = $this->processClinicBookings($clinicBookingService, $dryRun);
            $anyFound = $anyFound || $found;
            $totalFinalized += $finalized;
            $totalFailed += $failed;
        }

        if (! $type || $type === 'service') {
            [$found, $finalized, $failed] = $this->processServiceOrders($nonConsultationService, $dryRun);
            $anyFound = $anyFound || $found;
            $totalFinalized += $finalized;
            $totalFailed += $failed;
        }

        if (! $anyFound) {
            $this->info('No stuck pending bookings found.');

            return self::SUCCESS;
        }

        if (! $dryRun) {
            $this->newLine();
            $this->info("Total: {$totalFinalized} finalized, {$totalFailed} failed.");
        }

        return $totalFailed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array{bool, int, int} [found, finalized, failed]
     */
    private function processDoctorBookings(PublicBookingService $bookingService, bool $dryRun, bool $force): array
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
            return [false, 0, 0];
        }

        $this->info("Doctor Bookings: {$stuck->count()} pending booking(s) with completed payments:");
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

        if ($dryRun) {
            $this->warn('Dry-run mode — no changes made.');

            return [true, 0, 0];
        }

        if ($force) {
            $this->warn('Force mode — slot availability checks will be skipped.');
        }

        $finalized = 0;
        $failed = 0;

        foreach ($stuck as $pendingBooking) {
            try {
                $result = $bookingService->finalizeBookingAfterPayment($pendingBooking, $force);

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
        $this->info("Doctor bookings: {$finalized} finalized, {$failed} failed.");

        return [true, $finalized, $failed];
    }

    /**
     * @return array{bool, int, int} [found, finalized, failed]
     */
    private function processClinicBookings(ClinicBookingService $clinicBookingService, bool $dryRun): array
    {
        if (! Schema::hasTable('pending_clinic_bookings')) {
            return [false, 0, 0];
        }

        $stuck = PendingClinicBooking::whereIn('status', ['pending_payment', 'expired'])
            ->whereHas('invoice', function ($q) {
                $q->where('status', 'paid')
                    ->orWhereHas('payments', function ($pq) {
                        $pq->where('status', 'completed');
                    });
            })
            ->with(['department', 'service', 'invoice.patient'])
            ->get();

        if ($stuck->isEmpty()) {
            return [false, 0, 0];
        }

        $this->info("Clinic Bookings: {$stuck->count()} pending clinic booking(s) with completed payments:");
        $this->newLine();

        $rows = $stuck->map(fn ($pcb) => [
            $pcb->id,
            $pcb->invoice?->invoice_number ?? '—',
            $this->clinicPatientName($pcb),
            $pcb->department?->name ?? '—',
            $pcb->service?->name ?? '—',
            $pcb->appointment_date?->format('d M Y'),
            $pcb->fee,
            $pcb->created_at?->diffForHumans(),
        ]);

        $this->table(
            ['ID', 'Invoice', 'Patient', 'Clinic', 'Service', 'Date', 'Fee', 'Created'],
            $rows
        );

        if ($dryRun) {
            $this->warn('Dry-run mode — no changes made.');

            return [true, 0, 0];
        }

        $finalized = 0;
        $failed = 0;

        foreach ($stuck as $pending) {
            try {
                $request = $clinicBookingService->finalizeClinicBookingAfterPayment($pending, allowExpiredWhenPaid: true);

                $this->info("  ✓ Clinic Booking #{$pending->id} → Request #{$request->id} (accepted)");

                Log::info('Pending clinic booking recovered via artisan command', [
                    'pending_clinic_booking_id' => $pending->id,
                    'clinic_booking_request_id' => $request->id,
                ]);

                $finalized++;
            } catch (\Exception $e) {
                $this->error("  ✗ Clinic Booking #{$pending->id}: {$e->getMessage()}");

                Log::error('Failed to recover pending clinic booking via artisan command', [
                    'pending_clinic_booking_id' => $pending->id,
                    'error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $this->newLine();
        $this->info("Clinic bookings: {$finalized} finalized, {$failed} failed.");

        return [true, $finalized, $failed];
    }

    /**
     * @return array{bool, int, int} [found, finalized, failed]
     */
    private function processServiceOrders(NonConsultationBookingService $nonConsultationService, bool $dryRun): array
    {
        if (! Schema::hasTable('service_orders')) {
            return [false, 0, 0];
        }

        $stuck = ServiceOrder::whereIn('status', [ServiceOrder::STATUS_PENDING_PAYMENT, ServiceOrder::STATUS_EXPIRED])
            ->whereHas('invoice', function ($q) {
                $q->where('status', 'paid')
                    ->orWhereHas('payments', function ($pq) {
                        $pq->where('status', 'completed');
                    });
            })
            ->with(['doctor', 'service', 'department', 'invoice.patient', 'patient'])
            ->get();

        if ($stuck->isEmpty()) {
            return [false, 0, 0];
        }

        $this->info("Service Orders: {$stuck->count()} pending service order(s) with completed payments:");
        $this->newLine();

        $rows = $stuck->map(fn ($so) => [
            $so->id,
            $so->order_number ?? '—',
            $so->invoice?->invoice_number ?? '—',
            $so->patient?->full_name ?? $this->serviceOrderPatientName($so) ?? '—',
            $so->doctor?->full_name ?? $so->doctor?->name ?? '—',
            $so->service?->name ?? '—',
            $so->fee,
            $so->created_at?->diffForHumans(),
        ]);

        $this->table(
            ['ID', 'Order #', 'Invoice', 'Patient', 'Doctor', 'Service', 'Fee', 'Created'],
            $rows
        );

        if ($dryRun) {
            $this->warn('Dry-run mode — no changes made.');

            return [true, 0, 0];
        }

        $finalized = 0;
        $failed = 0;

        foreach ($stuck as $order) {
            try {
                $result = $nonConsultationService->finalizeAfterPayment($order, allowExpiredWhenPaid: true);

                $this->info("  ✓ Service Order #{$order->id} ({$order->order_number}) → Paid (notifications sent)");

                Log::info('Service order recovered via artisan command', [
                    'service_order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]);

                $finalized++;
            } catch (\Exception $e) {
                $this->error("  ✗ Service Order #{$order->id}: {$e->getMessage()}");

                Log::error('Failed to recover service order via artisan command', [
                    'service_order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $this->newLine();
        $this->info("Service orders: {$finalized} finalized, {$failed} failed.");

        return [true, $finalized, $failed];
    }

    private function clinicPatientName(PendingClinicBooking $pcb): string
    {
        $data = $pcb->patient_data ?? [];
        $name = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        return $pcb->invoice?->patient?->full_name
            ?? $pcb->invoice?->patient?->name
            ?? '—';
    }

    private function serviceOrderPatientName(ServiceOrder $order): ?string
    {
        $data = $order->patient_data ?? [];
        $name = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

        return $name !== '' ? $name : null;
    }
}
