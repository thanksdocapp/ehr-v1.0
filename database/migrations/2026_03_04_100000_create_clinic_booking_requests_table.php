<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Clinic booking requests: patient books into clinic, doctors accept to assign.
     */
    public function up(): void
    {
        Schema::create('clinic_booking_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 32)->unique();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete(); // Set when converted to appointment
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete(); // Set when doctor accepts
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete(); // Set when converted

            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->string('consultation_type', 20)->default('in_person');
            $table->decimal('fee', 10, 2)->default(0);
            $table->text('notes')->nullable();

            // Patient data (JSON until patient is created)
            $table->json('patient_data');

            $table->enum('status', ['pending_acceptance', 'accepted', 'expired', 'cancelled'])->default('pending_acceptance');

            $table->string('created_from', 50)->nullable();
            $table->timestamps();

            $table->index(['department_id', 'status', 'appointment_date']);
            $table->index(['status', 'appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_booking_requests');
    }
};
