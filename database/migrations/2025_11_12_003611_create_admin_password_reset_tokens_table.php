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
        Schema::create('admin_password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->foreignId('reset_by_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->boolean('invalidate_sessions')->default(false);
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'used']);
            $table->index(['token']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_password_reset_tokens');
    }
};
