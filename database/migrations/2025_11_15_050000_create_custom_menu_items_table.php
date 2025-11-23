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
        Schema::create('custom_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key')->unique(); // Unique key for role visibility (e.g., 'custom-link-1')
            $table->string('label'); // Menu item label
            $table->string('icon')->nullable(); // Font Awesome icon class (e.g., 'fa-link', 'fa-external-link-alt')
            $table->string('url'); // URL to open
            $table->enum('target', ['_blank', '_self'])->default('_blank'); // Open in new tab or same tab
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true); // Enable/disable menu item
            $table->string('menu_type')->default('staff'); // 'staff' or 'admin' (for future expansion)
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();
            
            // Indexes
            $table->index(['menu_type', 'is_active', 'order']);
            $table->index('menu_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_menu_items');
    }
};

