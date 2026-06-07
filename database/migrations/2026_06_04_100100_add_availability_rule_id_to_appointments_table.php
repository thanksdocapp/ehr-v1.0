<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link an appointment to the availability rule/slot it consumed.
 * consultation_type already records the booked modality; this records which rule was used.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('appointments') || Schema::hasColumn('appointments', 'availability_rule_id')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('availability_rule_id')
                ->nullable()
                ->constrained('doctor_availability_rules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('appointments') || !Schema::hasColumn('appointments', 'availability_rule_id')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignKey('availability_rule_id');
        });
    }
};
