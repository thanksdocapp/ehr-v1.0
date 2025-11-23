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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Setting key
            $table->text('value')->nullable(); // Setting value (can be JSON)
            $table->string('type')->default('string'); // string, boolean, integer, json, encrypted
            $table->string('category')->default('general'); // general, currency, features, limits
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed from frontend
            $table->timestamps();
            
            $table->index(['category', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
