<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('record_type', ['consultation', 'diagnosis', 'prescription', 'lab_result', 'follow_up', 'discharge']);
            $table->string('diagnosis')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->json('vital_signs')->nullable(); // blood_pressure, temperature, pulse, etc.
            $table->date('follow_up_date')->nullable();
            $table->boolean('is_private')->default(false);
            $table->timestamps();
            
            $table->index(['patient_id', 'created_at']);
            $table->index(['doctor_id', 'created_at']);
            $table->index('record_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
