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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_number')->unique(); // Custom appointment ID
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('type', ['consultation', 'followup', 'emergency', 'checkup', 'surgery'])->default('consultation');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'rescheduled'])->default('pending');
            $table->text('reason')->nullable(); // Reason for appointment
            $table->text('symptoms')->nullable();
            $table->text('notes')->nullable(); // Doctor's notes
            $table->decimal('fee', 8, 2)->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('meeting_link')->nullable(); // For online consultations
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->text('prescription')->nullable();
            $table->text('diagnosis')->nullable();
            $table->json('vital_signs')->nullable(); // BP, temperature, etc. as JSON
            $table->text('follow_up_instructions')->nullable();
            $table->date('next_appointment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
