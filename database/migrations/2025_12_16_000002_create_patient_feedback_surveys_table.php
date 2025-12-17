<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_feedback_surveys', function (Blueprint $table) {
            $table->id();

            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();

            // Patient ID is NULL if the patient chose anonymous submission
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();

            // Store token as a hash (do not store plaintext token)
            $table->string('token_hash', 64)->unique();
            // Store encrypted plaintext token so we can email/resend links later
            $table->text('token_encrypted');

            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('submitted_at')->nullable();

            // Lightweight metadata for audit/reporting (no PHI)
            $table->json('meta')->nullable();

            $table->timestamps();

            // One survey per appointment (future resends still use the same survey/token)
            $table->unique('appointment_id');

            $table->index(['doctor_id', 'submitted_at']);
            $table->index(['appointment_id']);
            $table->index(['sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_feedback_surveys');
    }
};


