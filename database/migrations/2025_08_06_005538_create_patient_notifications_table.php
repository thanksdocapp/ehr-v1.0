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
        Schema::create('patient_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('type', 50)->index(); // appointment, prescription, lab_result, billing, medical_record, system
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data like appointment_id, billing_id, etc.
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('action_url')->nullable(); // URL to take action on notification
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            
            // Hospital-specific fields
            $table->string('category', 50)->nullable()->index(); // medical, administrative, billing, appointment
            $table->foreignId('related_appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('related_patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->foreignId('related_doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->string('notification_channel', 20)->default('web'); // web, email, sms, push
            $table->boolean('email_sent')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('scheduled_at')->nullable(); // For scheduled notifications
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['patient_id', 'is_read']);
            $table->index(['type', 'created_at']);
            $table->index(['category', 'priority']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_notifications');
    }
};
