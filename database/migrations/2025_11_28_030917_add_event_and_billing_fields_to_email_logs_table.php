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

        // Check if columns exist BEFORE using them in ->after()
        $hasEmailType = Schema::hasColumn('email_logs', 'email_type');
        $hasPatientId = Schema::hasColumn('email_logs', 'patient_id');
        $hasMetadata = Schema::hasColumn('email_logs', 'metadata');
        $hasBillingId = Schema::hasColumn('email_logs', 'billing_id');
        $hasInvoiceId = Schema::hasColumn('email_logs', 'invoice_id');
        $hasPaymentId = Schema::hasColumn('email_logs', 'payment_id');
        $hasEvent = Schema::hasColumn('email_logs', 'event');
        
        Schema::table('email_logs', function (Blueprint $table) use ($hasEmailType, $hasMetadata, $hasPatientId, $hasBillingId, $hasInvoiceId, $hasPaymentId, $hasEvent) {
            // Add event field for tracking email types/events
            if (!$hasEvent) {
                if ($hasEmailType) {
                    $table->string('event')->nullable()->after('email_type');
                } elseif ($hasMetadata) {
                    $table->string('event')->nullable()->after('metadata');
                } else {
                    $table->string('event')->nullable();
                }
            }
            
            // Add patient_id if it doesn't exist
            if (!$hasPatientId) {
                $table->foreignId('patient_id')->nullable()->after('recipient_email')->constrained('patients')->onDelete('cascade');
            }
            
            // Add billing and payment related fields
            if (!$hasBillingId) {
                $placementColumn = $hasPatientId ? 'patient_id' : 'recipient_email';
                $table->foreignId('billing_id')->nullable()->after($placementColumn)->constrained('billings')->onDelete('cascade');
            }
            
            if (!$hasInvoiceId) {
                $placementColumn = $hasBillingId ? 'billing_id' : ($hasPatientId ? 'patient_id' : 'recipient_email');
                $table->foreignId('invoice_id')->nullable()->after($placementColumn)->constrained('invoices')->onDelete('cascade');
            }
            
            if (!$hasPaymentId) {
                $placementColumn = $hasInvoiceId ? 'invoice_id' : ($hasBillingId ? 'billing_id' : ($hasPatientId ? 'patient_id' : 'recipient_email'));
                $table->foreignId('payment_id')->nullable()->after($placementColumn)->constrained('payments')->onDelete('cascade');
            }
        });
        
        // Add indexes separately to avoid issues if columns already exist
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes('email_logs');
        
        Schema::table('email_logs', function (Blueprint $table) use ($indexesFound) {
            // Only add indexes if they don't already exist
            if (!isset($indexesFound['email_logs_event_index']) && Schema::hasColumn('email_logs', 'event')) {
                $table->index('event');
            }
            if (!isset($indexesFound['email_logs_patient_id_index']) && Schema::hasColumn('email_logs', 'patient_id')) {
                $table->index('patient_id');
            }
            if (!isset($indexesFound['email_logs_billing_id_index']) && Schema::hasColumn('email_logs', 'billing_id')) {
                $table->index('billing_id');
            }
            if (!isset($indexesFound['email_logs_invoice_id_index']) && Schema::hasColumn('email_logs', 'invoice_id')) {
                $table->index('invoice_id');
            }
            if (!isset($indexesFound['email_logs_payment_id_index']) && Schema::hasColumn('email_logs', 'payment_id')) {
                $table->index('payment_id');
            }
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
