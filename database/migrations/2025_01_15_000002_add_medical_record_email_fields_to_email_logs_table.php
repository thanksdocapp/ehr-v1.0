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
        // Check if table exists before proceeding
        if (!Schema::hasTable('email_logs')) {
            return; // Table doesn't exist yet, skip this migration
        }

        Schema::table('email_logs', function (Blueprint $table) {
            $table->foreignId('patient_id')->nullable()->after('recipient_email')->constrained('patients')->onDelete('cascade');
            $table->foreignId('medical_record_id')->nullable()->after('patient_id')->constrained('medical_records')->onDelete('cascade');
            $table->foreignId('sent_by')->nullable()->after('medical_record_id')->constrained('users')->onDelete('set null');
            $table->enum('email_type', ['general', 'medical_record', 'appointment', 'prescription', 'lab_result'])->default('general')->after('sent_by');
            $table->boolean('contains_phi')->default(false)->after('email_type');
            $table->boolean('is_encrypted')->default(false)->after('contains_phi');
            $table->string('encryption_method')->nullable()->after('is_encrypted');
            $table->string('share_link_token')->nullable()->unique()->after('encryption_method');
            $table->timestamp('share_link_expires_at')->nullable()->after('share_link_token');
            $table->string('content_hash')->nullable()->after('share_link_expires_at');
            $table->json('delivery_status')->nullable()->after('content_hash'); // SMTP response, message ID, etc.
            $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            $table->timestamp('opened_at')->nullable()->after('delivered_at');
            $table->timestamp('clicked_at')->nullable()->after('opened_at');
            $table->integer('attachment_count')->default(0)->after('clicked_at');
            $table->bigInteger('total_attachment_size')->default(0)->after('attachment_count'); // in bytes
            
            $table->index('patient_id');
            $table->index('medical_record_id');
            $table->index('sent_by');
            $table->index('email_type');
            $table->index('share_link_token');
            $table->index('content_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists before proceeding
        if (!Schema::hasTable('email_logs')) {
            return; // Table doesn't exist, skip this migration
        }

        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['medical_record_id']);
            $table->dropForeign(['sent_by']);
            $table->dropIndex(['patient_id']);
            $table->dropIndex(['medical_record_id']);
            $table->dropIndex(['sent_by']);
            $table->dropIndex(['email_type']);
            $table->dropIndex(['share_link_token']);
            $table->dropIndex(['content_hash']);
            
            $table->dropColumn([
                'patient_id',
                'medical_record_id',
                'sent_by',
                'email_type',
                'contains_phi',
                'is_encrypted',
                'encryption_method',
                'share_link_token',
                'share_link_expires_at',
                'content_hash',
                'delivery_status',
                'delivered_at',
                'opened_at',
                'clicked_at',
                'attachment_count',
                'total_attachment_size'
            ]);
        });
    }
};

