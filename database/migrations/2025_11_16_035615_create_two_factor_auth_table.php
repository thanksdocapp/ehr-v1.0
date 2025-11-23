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
        // Create table only if it doesn't exist
        if (!Schema::hasTable('two_factor_auth')) {
            Schema::create('two_factor_auth', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->boolean('enabled')->default(false);
                $table->string('method', 50)->default('email'); // 'email' or 'authenticator'
                $table->text('secret')->nullable();
                $table->text('recovery_codes')->nullable(); // JSON array
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
                
                $table->index('user_id');
            });
        } else {
            // Table exists, check if method column exists and add if missing
            if (!Schema::hasColumn('two_factor_auth', 'method')) {
                Schema::table('two_factor_auth', function (Blueprint $table) {
                    $table->string('method', 50)->default('email')->after('enabled');
                });
            }
            if (!Schema::hasColumn('two_factor_auth', 'last_used_at')) {
                Schema::table('two_factor_auth', function (Blueprint $table) {
                    $table->timestamp('last_used_at')->nullable()->after('confirmed_at');
                });
            }
        }

        // Add 2FA columns to users table if they don't exist
        if (!Schema::hasColumn('users', 'two_factor_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('two_factor_enabled')->default(false)->after('password_change_required');
                $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
                $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_auth');
        
        if (Schema::hasColumn('users', 'two_factor_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'two_factor_enabled',
                    'two_factor_secret',
                    'two_factor_recovery_codes',
                    'two_factor_confirmed_at'
                ]);
            });
        }
    }
};
