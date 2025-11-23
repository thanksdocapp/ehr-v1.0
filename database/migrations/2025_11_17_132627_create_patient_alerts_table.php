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
        Schema::create('patient_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('type', 50)->index(); // clinical, safeguarding, behaviour, communication, admin, medication
            $table->string('code', 100)->index(); // e.g. drug_allergy, child_safeguarding
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'info'])->default('medium')->index();
            $table->string('title', 255);
            $table->text('description');
            $table->boolean('restricted')->default(false); // true = limited to Admin + authorised roles
            $table->boolean('active')->default(true)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['patient_id', 'active']);
            $table->index(['patient_id', 'severity']);
            $table->index(['type', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_alerts');
    }
};
