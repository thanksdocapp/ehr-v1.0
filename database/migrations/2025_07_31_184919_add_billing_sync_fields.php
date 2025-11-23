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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('billing_id')->nullable()->after('id')->constrained('billings')->onDelete('cascade');
            $table->index('billing_id');
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_reference')->nullable()->after('transaction_id');
            $table->index('transaction_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['billing_id']);
            $table->dropColumn('billing_id');
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('transaction_reference');
        });
    }
};
