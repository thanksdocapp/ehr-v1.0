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
        Schema::create('doctor_availability_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->date('exception_date');
            $table->enum('type', ['blocked', 'modified'])->default('blocked');
            $table->time('start_time')->nullable(); // For modified hours
            $table->time('end_time')->nullable();   // For modified hours
            $table->string('reason')->nullable();
            $table->boolean('is_all_day')->default(true);
            $table->timestamps();

            // Prevent duplicate entries for same doctor and date
            $table->unique(['doctor_id', 'exception_date'], 'dr_avail_exc_unique');

            // Index for quick lookups
            $table->index(['doctor_id', 'exception_date', 'type'], 'dr_avail_exc_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_availability_exceptions');
    }
};
