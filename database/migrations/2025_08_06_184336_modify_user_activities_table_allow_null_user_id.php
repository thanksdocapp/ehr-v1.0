<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table since it doesn't support dropping foreign keys
        
        // Step 1: Create a backup table with existing data
        Schema::create('user_activities_backup', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        // Step 2: Copy existing data
        DB::statement('INSERT INTO user_activities_backup SELECT * FROM user_activities');
        
        // Step 3: Drop the original table
        Schema::dropIfExists('user_activities');
        
        // Step 4: Rename backup table to original name
        Schema::rename('user_activities_backup', 'user_activities');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To rollback, we recreate the original table structure
        Schema::create('user_activities_backup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });
        
        // Copy data back, filtering out records with NULL user_id
        DB::statement('INSERT INTO user_activities_backup SELECT * FROM user_activities WHERE user_id IS NOT NULL');
        
        Schema::dropIfExists('user_activities');
        Schema::rename('user_activities_backup', 'user_activities');
    }
};
