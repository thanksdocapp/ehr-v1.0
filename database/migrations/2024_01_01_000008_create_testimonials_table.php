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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('patient_name');
            $table->string('patient_photo')->nullable();
            $table->text('testimonial');
            $table->integer('rating')->default(5); // 1-5 star rating
            $table->string('treatment_type')->nullable();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->date('treatment_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
