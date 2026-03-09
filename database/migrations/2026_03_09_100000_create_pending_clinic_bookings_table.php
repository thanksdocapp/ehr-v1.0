<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pending_clinic_bookings')) {
            Schema::create('pending_clinic_bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_token', 64)->unique();
                $table->foreignId('department_id')->constrained()->onDelete('cascade');
                $table->foreignId('service_id')->nullable()->constrained('booking_services')->onDelete('set null');

                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->text('notes')->nullable();
                $table->json('patient_data');
                $table->decimal('fee', 10, 2)->default(0);

                $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('status', ['pending_payment', 'payment_failed', 'completed', 'expired', 'cancelled'])->default('pending_payment');
                $table->timestamp('expires_at');

                $table->timestamps();

                $table->index(['booking_token']);
                $table->index(['status', 'expires_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_clinic_bookings');
    }
};
