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
        Schema::create('about_stats', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // FontAwesome class
            $table->string('value'); // The statistic value/number
            $table->string('prefix')->nullable(); // e.g., "+", "$", etc.
            $table->string('suffix')->nullable(); // e.g., "%", "K+", "M+", etc.
            $table->string('color', 50)->default('#0d6efd'); // Background/text color
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_stats');
    }
};
