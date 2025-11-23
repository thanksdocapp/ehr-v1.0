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
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Theme name (e.g., "Medical Blue", "Healthcare Green")
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            
            // Color Settings
            $table->string('primary_color')->default('#0d6efd'); // Bootstrap primary
            $table->string('secondary_color')->default('#6c757d');
            $table->string('success_color')->default('#198754');
            $table->string('danger_color')->default('#dc3545');
            $table->string('warning_color')->default('#ffc107');
            $table->string('info_color')->default('#0dcaf0');
            $table->string('light_color')->default('#f8f9fa');
            $table->string('dark_color')->default('#212529');
            
            // Custom Colors
            $table->string('accent_color')->default('#667eea');
            $table->string('text_color')->default('#212529');
            $table->string('background_color')->default('#ffffff');
            $table->string('card_background')->default('#ffffff');
            $table->string('sidebar_color')->default('#ffffff');
            $table->string('header_color')->default('#ffffff');
            $table->string('footer_color')->default('#212529');
            
            // Typography
            $table->string('primary_font')->default('Poppins');
            $table->string('secondary_font')->default('Inter');
            $table->string('font_size_base')->default('16px');
            
            // Layout Settings
            $table->string('border_radius')->default('0.5rem');
            $table->string('box_shadow')->default('0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)');
            $table->string('container_width')->default('1200px');
            
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
