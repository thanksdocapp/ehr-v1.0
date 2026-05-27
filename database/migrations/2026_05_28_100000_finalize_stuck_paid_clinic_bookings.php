<?php

use App\Services\ClinicBookingService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pending_clinic_bookings')) {
            return;
        }

        try {
            $stats = app(ClinicBookingService::class)->finalizeAllStuckPaidClinicBookings();
            Log::info('Migration: finalized stuck paid clinic bookings', $stats);
        } catch (\Throwable $e) {
            Log::error('Migration: failed to finalize stuck paid clinic bookings', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function down(): void
    {
        // Data repair only; no rollback.
    }
};
