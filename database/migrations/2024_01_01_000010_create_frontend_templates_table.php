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
        Schema::create('frontend_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g., "Classic Medical", "Modern Healthcare")
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('version')->default('1.0.0');
            $table->string('author')->default('NewWaves Projects');
            
            // Template Files Structure
            $table->string('template_path'); // e.g., 'templates/medical-classic'
            $table->string('preview_image');
            $table->json('screenshots')->nullable(); // Multiple preview images
            
            // Template Configuration
            $table->json('layouts')->nullable(); // Available layouts (homepage, services, contact, etc.)
            $table->json('components')->nullable(); // Available components
            $table->json('features')->nullable(); // Template features list
            $table->json('color_schemes')->nullable(); // Compatible color schemes
            
            // Template Type
            $table->enum('type', ['frontend', 'admin', 'patient_portal'])->default('frontend');
            $table->enum('style', ['modern', 'classic', 'minimal', 'medical', 'corporate'])->default('medical');
            
            // Compatibility
            $table->string('min_php_version')->default('8.1');
            $table->string('laravel_version')->default('10.x');
            $table->json('required_plugins')->nullable();
            
            // Status
            $table->boolean('is_default')->default(false);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 8, 2)->nullable(); // For premium templates
            
            $table->integer('downloads')->default(0);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_templates');
    }
};
