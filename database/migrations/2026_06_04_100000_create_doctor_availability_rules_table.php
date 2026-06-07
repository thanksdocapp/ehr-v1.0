<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modality-aware availability rules.
 *
 * Each row is a recurring weekly window (day + start/end time) tagged with the modality
 * it supports: in_person, online, telephone, or "all" (the compatibility value used by the
 * backfill so existing windows keep serving every modality until a doctor narrows them).
 *
 * Slot times for modality filtering are derived from these rules; the physical resource
 * (the doctor) is still locked across modalities, so any booking blocks the time for all.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('doctor_availability_rules')) {
            return;
        }

        Schema::create('doctor_availability_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week', 9); // 'monday'..'sunday' — matches existing availability keys
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('modality', ['in_person', 'online', 'telephone', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->boolean('needs_review')->default(false);
            $table->enum('source', ['backfill', 'default', 'manual'])->default('manual');
            $table->timestamps();

            $table->index(['doctor_id', 'day_of_week', 'is_active'], 'dar_doctor_day_active_idx');
            $table->index(['doctor_id', 'modality'], 'dar_doctor_modality_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availability_rules');
    }
};
