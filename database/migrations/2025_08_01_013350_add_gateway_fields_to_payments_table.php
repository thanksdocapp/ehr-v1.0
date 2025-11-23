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
        Schema::table('payments', function (Blueprint $table) {
            // Add new gateway fields only if they don't exist
            if (!Schema::hasColumn('payments', 'payment_gateway')) {
                $table->string('payment_gateway')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('transaction_id');
            }
            
            // Add index for payment_gateway if column was added
            if (!Schema::hasColumn('payments', 'payment_gateway')) {
                $table->index('payment_gateway');
            }
        });
        
        // Update the payment_method enum to allow more values
        // SQLite doesn't support MODIFY COLUMN, so we'll skip this for now
        // The model validation will handle the allowed payment methods
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Only drop columns that exist and were added by this migration
            if (Schema::hasColumn('payments', 'payment_gateway')) {
                $table->dropIndex(['payment_gateway']);
                $table->dropColumn('payment_gateway');
            }
            if (Schema::hasColumn('payments', 'gateway_transaction_id')) {
                $table->dropColumn('gateway_transaction_id');
            }
        });
        
        // Revert payment_method back to enum
        // SQLite doesn't support MODIFY COLUMN, so we'll skip this
    }
};
