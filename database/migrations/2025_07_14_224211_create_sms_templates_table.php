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
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('message');
            $table->string('description')->nullable();
            $table->string('category', 50)->default('general');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->json('variables')->nullable();
            $table->string('sender_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status']);
            $table->index(['category']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
