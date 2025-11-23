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
        Schema::create('sidebar_menu_orders', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key'); // e.g., 'dashboard', 'patients', 'appointments'
            $table->string('menu_type')->default('admin'); // 'admin' or 'staff'
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->string('label')->nullable(); // Custom label override
            $table->json('menu_data')->nullable(); // Store menu item data (icon, route, etc.)
            $table->timestamps();
            
            // Unique constraint on combination of menu_key and menu_type
            $table->unique(['menu_key', 'menu_type']);
            $table->index(['menu_type', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidebar_menu_orders');
    }
};
