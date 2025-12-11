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
        if (!Schema::hasTable('pending_bookings')) {
            Schema::create('pending_bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_token', 64)->unique(); // Unique token for this booking
                $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
                $table->foreignId('service_id')->nullable()->constrained('booking_services')->onDelete('set null');
                $table->unsignedBigInteger('department_id')->nullable();

                // Appointment details
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->boolean('is_online')->default(false);
                $table->text('notes')->nullable();

                // Patient details (stored as JSON until payment is completed)
                $table->json('patient_data');

                // Fee information
                $table->decimal('fee', 10, 2)->default(0);

                // Payment tracking
                $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('status', ['pending_payment', 'payment_failed', 'completed', 'expired', 'cancelled'])->default('pending_payment');

                // Expiry
                $table->timestamp('expires_at');

                $table->timestamps();

                $table->index(['booking_token'], 'pb_token_idx');
                $table->index(['status', 'expires_at'], 'pb_status_expires_idx');
                $table->index(['doctor_id', 'appointment_date'], 'pb_doctor_date_idx');

                $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_bookings');
    }
};
