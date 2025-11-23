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
        Schema::create('admin_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name (e.g., "Medical Dashboard", "Modern Admin")
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('version')->default('1.0.0');
            
            // Template Files Structure
            $table->string('template_path'); // e.g., 'admin-templates/medical-dashboard'
            $table->string('preview_image');
            $table->json('screenshots')->nullable();
            
            // Layout Configuration
            $table->enum('sidebar_style', ['fixed', 'collapsible', 'mini', 'overlay'])->default('fixed');
            $table->enum('navbar_style', ['fixed', 'static', 'sticky'])->default('fixed');
            $table->enum('layout_type', ['vertical', 'horizontal', 'detached'])->default('vertical');
            
            // Color Schemes for Admin
            $table->string('primary_color')->default('#0d6efd');
            $table->string('sidebar_bg')->default('#ffffff');
            $table->string('navbar_bg')->default('#ffffff');
            $table->string('card_bg')->default('#ffffff');
            $table->string('text_color')->default('#212529');
            
            // Features
            $table->json('features')->nullable(); // Dark mode, RTL, etc.
            $table->json('components')->nullable(); // Available admin components
            $table->boolean('has_dark_mode')->default(true);
            $table->boolean('has_rtl_support')->default(false);
            $table->boolean('is_responsive')->default(true);
            
            // Status
            $table->boolean('is_default')->default(false);
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
        Schema::dropIfExists('admin_templates');
    }
};
