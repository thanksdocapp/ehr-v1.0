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
        // For SQLite compatibility, we'll handle this differently
        if (Schema::hasColumn('payments', 'payment_method')) {
            // Check if we're using SQLite
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'sqlite') {
                // For SQLite, we need to recreate the table without the constraint
                Schema::table('payments', function (Blueprint $table) {
                    $table->dropColumn('payment_method');
                });
                
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('payment_method', 50)->after('amount');
                });
            } else {
                // For MySQL/PostgreSQL, modify the column type
                Schema::table('payments', function (Blueprint $table) {
                    $table->string('payment_method', 50)->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback is complex for SQLite, so we'll just leave a comment
        // In production, you might want to backup data before running this migration
        Schema::table('payments', function (Blueprint $table) {
            // Note: Rolling back enum constraints is database-specific
            // For development, you can refresh the database if needed
        });
    }
};
