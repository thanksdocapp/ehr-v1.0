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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
            $table->string('prescription_number')->unique();
            $table->string('medication_name');
            $table->string('dosage');
            $table->string('frequency'); // e.g., "3 times daily"
            $table->string('duration'); // e.g., "7 days", "2 weeks"
            $table->text('instructions')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired', 'pending'])->default('active');
            $table->date('prescribed_date');
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'prescribed_date']);
            $table->index('prescription_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
