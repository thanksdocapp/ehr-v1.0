<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('clinic_booking_requests')) {
            return;
        }

        if (! Schema::hasColumn('clinic_booking_requests', 'accepted_by_user_id')) {
            Schema::table('clinic_booking_requests', function (Blueprint $table) {
                $table->foreignId('accepted_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('clinic_booking_requests', 'accepted_at')) {
            Schema::table('clinic_booking_requests', function (Blueprint $table) {
                $table->timestamp('accepted_at')->nullable();
            });
        }

        if (! Schema::hasColumn('clinic_booking_requests', 'auto_accepted')) {
            Schema::table('clinic_booking_requests', function (Blueprint $table) {
                $table->boolean('auto_accepted')->default(false);
            });
        }
    }

    public function down(): void
    {
        // Non-destructive repair migration.
    }
};
