<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional traceability: record which availability rule a pending booking is holding.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pending_bookings') || Schema::hasColumn('pending_bookings', 'availability_rule_id')) {
            return;
        }

        Schema::table('pending_bookings', function (Blueprint $table) {
            $table->foreignId('availability_rule_id')
                ->nullable()
                ->constrained('doctor_availability_rules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pending_bookings') || !Schema::hasColumn('pending_bookings', 'availability_rule_id')) {
            return;
        }

        Schema::table('pending_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignKey('availability_rule_id');
        });
    }
};
