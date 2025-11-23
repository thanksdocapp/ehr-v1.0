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
        Schema::create('patient_email_consent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->boolean('email_consent')->default(false);
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('phi_consent')->default(false); // Special category data consent
            $table->text('consent_notes')->nullable();
            $table->timestamp('consent_date')->nullable();
            $table->timestamp('consent_expires_at')->nullable();
            $table->foreignId('consent_recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('consent_method')->nullable(); // 'verbal', 'written', 'online', 'implied'
            $table->timestamps();
            
            $table->index('patient_id');
            $table->index('email_consent');
            $table->index('phi_consent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_email_consent');
    }
};

