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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon')->nullable(); // Font Awesome icon class
            $table->string('image')->nullable();
            $table->string('color')->default('#0d6efd'); // Bootstrap primary color
            $table->string('head_of_department')->nullable();
            $table->string('location')->nullable(); // Floor, Room number
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('services')->nullable(); // Department services as JSON array
            $table->text('working_hours')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_emergency')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
