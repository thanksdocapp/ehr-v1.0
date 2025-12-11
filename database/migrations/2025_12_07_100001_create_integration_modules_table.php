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
        // Integration Modules - Main configuration table
        if (Schema::hasTable('integration_modules')) {
            return; // All tables already exist, skip migration
        }

        Schema::create('integration_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name: "Randox Lab Tests"
            $table->string('slug')->unique(); // randox, quincy, vista_health
            $table->string('provider'); // Provider class name
            $table->enum('type', ['lab_tests', 'prescriptions', 'imaging', 'pharmacy', 'other']);
            $table->text('description')->nullable();
            $table->string('logo')->nullable(); // Path to provider logo
            $table->string('website')->nullable(); // Provider website
            $table->boolean('is_active')->default(false);
            $table->boolean('is_configured')->default(false);
            $table->json('config')->nullable(); // API keys, endpoints, etc (encrypted)
            $table->json('settings')->nullable(); // Module-specific settings
            $table->json('capabilities')->nullable(); // What this module can do
            $table->string('api_version')->nullable();
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });

        // Integration Requests - Track all requests to external services
        Schema::create('integration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_module_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('request_type'); // order, cancel, status_check, result_fetch
            $table->string('external_reference')->nullable(); // External system reference ID
            $table->string('internal_reference')->unique(); // Our reference ID
            $table->enum('status', ['pending', 'submitted', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->json('request_data')->nullable(); // What we sent
            $table->json('response_data')->nullable(); // What we received
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['integration_module_id', 'status']);
            $table->index(['patient_id', 'status']);
            $table->index('external_reference');
        });

        // Lab Test Orders (Randox specific)
        Schema::create('lab_test_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('external_order_id')->nullable();
            $table->json('tests_requested'); // Array of test codes/names
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->enum('status', ['draft', 'ordered', 'sample_collected', 'processing', 'results_ready', 'reviewed', 'cancelled'])->default('draft');
            $table->text('clinical_notes')->nullable();
            $table->text('special_instructions')->nullable();
            $table->date('fasting_required')->nullable();
            $table->timestamp('sample_collected_at')->nullable();
            $table->timestamp('results_received_at')->nullable();
            $table->json('results')->nullable(); // Structured results data
            $table->string('results_pdf_path')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
        });

        // Prescription Orders (Quincy specific)
        Schema::create('prescription_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('external_order_id')->nullable();
            $table->string('pharmacy_id')->nullable(); // Selected pharmacy
            $table->string('pharmacy_name')->nullable();
            $table->json('medications'); // Array of medications with details
            $table->enum('status', ['draft', 'submitted', 'accepted', 'dispensing', 'ready', 'collected', 'delivered', 'cancelled', 'rejected'])->default('draft');
            $table->enum('delivery_method', ['collection', 'delivery'])->default('collection');
            $table->text('delivery_address')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
        });

        // Imaging/Scan Orders (Vista Health specific)
        Schema::create('imaging_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('external_order_id')->nullable();
            $table->string('scan_type'); // MRI, CT, X-Ray, Ultrasound, etc.
            $table->string('body_part'); // Head, Chest, Spine, etc.
            $table->enum('priority', ['routine', 'urgent', 'emergency'])->default('routine');
            $table->enum('status', ['draft', 'referred', 'scheduled', 'completed', 'reported', 'reviewed', 'cancelled'])->default('draft');
            $table->text('clinical_indication')->nullable(); // Why the scan is needed
            $table->text('clinical_history')->nullable();
            $table->text('special_instructions')->nullable();
            $table->boolean('contrast_required')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->string('location')->nullable(); // Imaging center location
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('report_received_at')->nullable();
            $table->text('report')->nullable(); // Radiologist report
            $table->string('report_pdf_path')->nullable();
            $table->json('images')->nullable(); // DICOM viewer links or paths
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
        });

        // Integration Webhooks Log
        Schema::create('integration_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_module_id')->constrained()->onDelete('cascade');
            $table->string('event_type');
            $table->json('payload');
            $table->enum('status', ['received', 'processed', 'failed'])->default('received');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['integration_module_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_webhooks');
        Schema::dropIfExists('imaging_orders');
        Schema::dropIfExists('prescription_orders');
        Schema::dropIfExists('lab_test_orders');
        Schema::dropIfExists('integration_requests');
        Schema::dropIfExists('integration_modules');
    }
};
