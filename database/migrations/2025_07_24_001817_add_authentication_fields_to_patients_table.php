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
        Schema::table('patients', function (Blueprint $table) {
            // Add authentication fields
            $table->string('password')->nullable()->after('phone');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->rememberToken()->after('is_active');
            
            // Make email required for authentication
            $table->string('email')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Remove authentication fields
            $table->dropColumn(['password', 'email_verified_at', 'remember_token']);
            
            // Make email nullable again
            $table->string('email')->nullable()->change();
        });
    }
};
