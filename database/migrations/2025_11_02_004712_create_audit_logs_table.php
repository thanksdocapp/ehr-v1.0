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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable(); // Store name in case user is deleted
            $table->string('event_type'); // login, logout, create, update, delete, view
            $table->string('auditable_type')->nullable(); // Model class name (e.g., App\Models\Patient)
            $table->unsignedBigInteger('auditable_id')->nullable(); // Model ID
            $table->string('description'); // Human-readable description
            $table->text('old_values')->nullable(); // JSON of old values (for updates)
            $table->text('new_values')->nullable(); // JSON of new values (for creates/updates)
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('user_id');
            $table->index('event_type');
            $table->index('auditable_type');
            $table->index('auditable_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
