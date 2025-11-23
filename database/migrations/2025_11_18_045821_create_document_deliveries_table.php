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
        Schema::create('document_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_document_id')->constrained('patient_documents')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade'); // Redundant but convenient
            $table->foreignId('sent_by')->constrained('users')->onDelete('restrict');
            $table->enum('recipient_type', ['patient', 'third_party']); // patient or third_party
            $table->string('recipient_name')->nullable(); // For third party
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable(); // If SMS/other used later
            $table->string('channel')->default('email'); // e.g. email, portal, print
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable(); // If you track opens
            $table->json('meta')->nullable(); // e.g. mail provider message id
            $table->timestamps();
            
            // Indexes
            $table->index('patient_document_id');
            $table->index('patient_id');
            $table->index('status');
            $table->index('recipient_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_deliveries');
    }
};
