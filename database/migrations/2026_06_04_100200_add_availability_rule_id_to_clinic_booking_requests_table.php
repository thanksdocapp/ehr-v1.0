<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clinic flow binds the consumed availability rule at acceptance time (per-doctor re-validation).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_booking_requests') || Schema::hasColumn('clinic_booking_requests', 'availability_rule_id')) {
            return;
        }

        Schema::table('clinic_booking_requests', function (Blueprint $table) {
            $table->foreignId('availability_rule_id')
                ->nullable()
                ->constrained('doctor_availability_rules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('clinic_booking_requests') || !Schema::hasColumn('clinic_booking_requests', 'availability_rule_id')) {
            return;
        }

        Schema::table('clinic_booking_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignKey('availability_rule_id');
        });
    }
};
