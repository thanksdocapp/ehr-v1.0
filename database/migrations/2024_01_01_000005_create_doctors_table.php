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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Dr.'); // Dr., Prof., etc.
            $table->string('first_name');
            $table->string('last_name');
            $table->string('slug')->unique();
            $table->string('specialization');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->text('bio');
            $table->string('photo')->nullable();
            $table->string('qualification');
            $table->integer('experience_years');
            $table->json('languages')->nullable(); // Spoken languages as JSON array
            $table->json('specialties')->nullable(); // Medical specialties as JSON array
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('room_number')->nullable();
            $table->decimal('consultation_fee', 8, 2)->nullable();
            $table->json('availability')->nullable(); // Available days/times as JSON
            $table->boolean('is_available_online')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
