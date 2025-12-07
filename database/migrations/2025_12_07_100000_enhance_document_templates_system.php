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
        // Create document categories table
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#667eea'); // Hex color
            $table->string('icon', 50)->default('fa-folder');
            $table->enum('type', ['letter', 'form', 'both'])->default('both');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('document_categories')->onDelete('set null');
        });

        // Enhance document_templates table
        Schema::table('document_templates', function (Blueprint $table) {
            // Category and organization
            $table->unsignedBigInteger('category_id')->nullable()->after('type');
            $table->text('description')->nullable()->after('name');
            $table->string('icon', 50)->default('fa-file-alt')->after('description');

            // Versioning
            $table->integer('version')->default(1)->after('is_active');
            $table->unsignedBigInteger('parent_template_id')->nullable()->after('version');
            $table->boolean('is_latest')->default(true)->after('parent_template_id');

            // Access control
            $table->json('allowed_roles')->nullable()->after('is_latest'); // ['admin', 'doctor', 'nurse']
            $table->json('allowed_departments')->nullable()->after('allowed_roles'); // [1, 2, 3]

            // Clinical metadata
            $table->boolean('requires_signature')->default(false)->after('allowed_departments');
            $table->boolean('requires_witness')->default(false)->after('requires_signature');
            $table->boolean('is_confidential')->default(false)->after('requires_witness');
            $table->integer('retention_days')->nullable()->after('is_confidential'); // Document retention period

            // Usage tracking
            $table->integer('usage_count')->default(0)->after('retention_days');
            $table->timestamp('last_used_at')->nullable()->after('usage_count');

            // Favorites
            $table->json('favorited_by')->nullable()->after('last_used_at'); // User IDs

            // Tags for search
            $table->json('tags')->nullable()->after('favorited_by');

            // Print settings
            $table->json('print_settings')->nullable()->after('tags'); // paper_size, orientation, margins

            // Foreign keys
            $table->foreign('category_id')->references('id')->on('document_categories')->onDelete('set null');
            $table->foreign('parent_template_id')->references('id')->on('document_templates')->onDelete('set null');
        });

        // Enhance patient_documents table
        Schema::table('patient_documents', function (Blueprint $table) {
            // Priority and urgency
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('status');

            // Approval workflow
            $table->enum('approval_status', ['not_required', 'pending', 'approved', 'rejected'])->default('not_required')->after('priority');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');

            // Additional signatures
            $table->json('additional_signatures')->nullable()->after('signed_at'); // [{user_id, name, role, signed_at, signature_path}]
            $table->unsignedBigInteger('witness_id')->nullable()->after('additional_signatures');
            $table->timestamp('witnessed_at')->nullable()->after('witness_id');

            // Related records
            $table->unsignedBigInteger('appointment_id')->nullable()->after('witnessed_at');
            $table->unsignedBigInteger('encounter_id')->nullable()->after('appointment_id');

            // Notes and collaboration
            $table->text('internal_notes')->nullable()->after('encounter_id');
            $table->json('revision_history')->nullable()->after('internal_notes'); // Track changes

            // Expiry
            $table->date('valid_from')->nullable()->after('revision_history');
            $table->date('valid_until')->nullable()->after('valid_from');

            // External reference
            $table->string('external_reference', 100)->nullable()->after('valid_until');

            // Confidentiality
            $table->boolean('is_confidential')->default(false)->after('external_reference');

            // Favorites
            $table->json('favorited_by')->nullable()->after('is_confidential');

            // Foreign keys
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('witness_id')->references('id')->on('users')->onDelete('set null');
        });

        // Create document_template_favorites table for quick access
        Schema::create('document_template_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('template_id');
            $table->timestamps();

            $table->unique(['user_id', 'template_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('document_templates')->onDelete('cascade');
        });

        // Create document_settings table
        Schema::create('document_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, templates, pdf, email
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default document settings
        $this->seedDefaultSettings();
    }

    /**
     * Seed default document settings.
     */
    protected function seedDefaultSettings(): void
    {
        $settings = [
            // General settings
            ['key' => 'document_numbering_format', 'value' => 'DOC-{YEAR}-{SEQ}', 'type' => 'string', 'group' => 'general', 'description' => 'Format for document numbers'],
            ['key' => 'document_numbering_sequence', 'value' => '1', 'type' => 'integer', 'group' => 'general', 'description' => 'Current document sequence number'],
            ['key' => 'require_approval_for_letters', 'value' => 'false', 'type' => 'boolean', 'group' => 'general', 'description' => 'Require approval before finalizing letters'],
            ['key' => 'require_approval_for_forms', 'value' => 'false', 'type' => 'boolean', 'group' => 'general', 'description' => 'Require approval before finalizing forms'],
            ['key' => 'auto_generate_pdf', 'value' => 'true', 'type' => 'boolean', 'group' => 'general', 'description' => 'Automatically generate PDF when document is finalized'],
            ['key' => 'allow_void_after_send', 'value' => 'false', 'type' => 'boolean', 'group' => 'general', 'description' => 'Allow voiding documents after they have been sent'],

            // Template settings
            ['key' => 'default_letter_category', 'value' => '', 'type' => 'string', 'group' => 'templates', 'description' => 'Default category for new letter templates'],
            ['key' => 'default_form_category', 'value' => '', 'type' => 'string', 'group' => 'templates', 'description' => 'Default category for new form templates'],
            ['key' => 'enable_template_versioning', 'value' => 'true', 'type' => 'boolean', 'group' => 'templates', 'description' => 'Enable version tracking for templates'],
            ['key' => 'max_template_versions', 'value' => '10', 'type' => 'integer', 'group' => 'templates', 'description' => 'Maximum versions to keep per template'],

            // PDF settings
            ['key' => 'pdf_paper_size', 'value' => 'A4', 'type' => 'string', 'group' => 'pdf', 'description' => 'Default paper size for PDFs'],
            ['key' => 'pdf_orientation', 'value' => 'portrait', 'type' => 'string', 'group' => 'pdf', 'description' => 'Default orientation for PDFs'],
            ['key' => 'pdf_include_header', 'value' => 'true', 'type' => 'boolean', 'group' => 'pdf', 'description' => 'Include clinic header in PDFs'],
            ['key' => 'pdf_include_footer', 'value' => 'true', 'type' => 'boolean', 'group' => 'pdf', 'description' => 'Include page footer in PDFs'],
            ['key' => 'pdf_header_logo', 'value' => '', 'type' => 'string', 'group' => 'pdf', 'description' => 'Logo to use in PDF headers'],
            ['key' => 'pdf_footer_text', 'value' => 'This document is confidential and intended for the recipient only.', 'type' => 'string', 'group' => 'pdf', 'description' => 'Footer text for PDFs'],
            ['key' => 'pdf_watermark_draft', 'value' => 'true', 'type' => 'boolean', 'group' => 'pdf', 'description' => 'Add DRAFT watermark to draft documents'],

            // Email settings
            ['key' => 'email_from_name', 'value' => '', 'type' => 'string', 'group' => 'email', 'description' => 'From name for document emails'],
            ['key' => 'email_reply_to', 'value' => '', 'type' => 'string', 'group' => 'email', 'description' => 'Reply-to address for document emails'],
            ['key' => 'email_default_subject', 'value' => 'Document from {clinic_name}', 'type' => 'string', 'group' => 'email', 'description' => 'Default email subject'],
            ['key' => 'email_include_tracking', 'value' => 'true', 'type' => 'boolean', 'group' => 'email', 'description' => 'Include tracking pixel in emails'],
            ['key' => 'signature_request_expiry_days', 'value' => '7', 'type' => 'integer', 'group' => 'email', 'description' => 'Days until signature request expires'],
        ];

        foreach ($settings as $setting) {
            \DB::table('document_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys first
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['witness_id']);

            $table->dropColumn([
                'priority', 'approval_status', 'approved_by', 'approved_at', 'approval_notes',
                'additional_signatures', 'witness_id', 'witnessed_at',
                'appointment_id', 'encounter_id', 'internal_notes', 'revision_history',
                'valid_from', 'valid_until', 'external_reference', 'is_confidential', 'favorited_by'
            ]);
        });

        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['parent_template_id']);

            $table->dropColumn([
                'category_id', 'description', 'icon', 'version', 'parent_template_id', 'is_latest',
                'allowed_roles', 'allowed_departments', 'requires_signature', 'requires_witness',
                'is_confidential', 'retention_days', 'usage_count', 'last_used_at',
                'favorited_by', 'tags', 'print_settings'
            ]);
        });

        Schema::dropIfExists('document_settings');
        Schema::dropIfExists('document_template_favorites');
        Schema::dropIfExists('document_categories');
    }
};
