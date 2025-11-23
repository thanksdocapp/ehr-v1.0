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
        Schema::create('lab_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
            $table->string('report_number')->unique();
            $table->string('test_type'); // blood, urine, x-ray, mri, etc.
            $table->string('test_name');
            $table->date('test_date');
            $table->json('results')->nullable(); // test results as key-value pairs
            $table->json('reference_values')->nullable(); // normal ranges
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->string('technician_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable(); // path to PDF/image file
            $table->timestamps();
            
            $table->index(['patient_id', 'test_date']);
            $table->index(['doctor_id', 'test_date']);
            $table->index(['test_type', 'status']);
            $table->index('report_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_reports');
    }
};
