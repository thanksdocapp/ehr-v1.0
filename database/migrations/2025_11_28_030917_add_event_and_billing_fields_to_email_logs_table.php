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
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        Schema::table('email_logs', function (Blueprint $table) {
            // Check if email_type column exists to determine where to place event
            $hasEmailType = Schema::hasColumn('email_logs', 'email_type');
            $hasPatientId = Schema::hasColumn('email_logs', 'patient_id');
            
            // Add event field for tracking email types/events
            if ($hasEmailType) {
                $table->string('event')->nullable()->after('email_type');
            } else {
                $table->string('event')->nullable()->after('metadata');
            }
            
            // Add billing and payment related fields after patient_id if it exists, otherwise after recipient_email
            if ($hasPatientId) {
                $table->foreignId('billing_id')->nullable()->after('patient_id')->constrained('billings')->onDelete('cascade');
            } else {
                // If patient_id doesn't exist, add it first, then billing_id
                $table->foreignId('patient_id')->nullable()->after('recipient_email')->constrained('patients')->onDelete('cascade');
                $table->foreignId('billing_id')->nullable()->after('patient_id')->constrained('billings')->onDelete('cascade');
            }
            
            $table->foreignId('invoice_id')->nullable()->after('billing_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('payment_id')->nullable()->after('invoice_id')->constrained('payments')->onDelete('cascade');
            
            // Add indexes for better query performance
            $table->index('event');
            if ($hasPatientId || !$hasPatientId) {
                $table->index('patient_id');
            }
            $table->index('billing_id');
            $table->index('invoice_id');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('email_logs')) {
            return;
        }

        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropForeign(['billing_id']);
            $table->dropForeign(['invoice_id']);
            $table->dropForeign(['payment_id']);
            $table->dropIndex(['event']);
            $table->dropIndex(['billing_id']);
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['payment_id']);
            
            $table->dropColumn(['event', 'billing_id', 'invoice_id', 'payment_id']);
        });
    }
};
