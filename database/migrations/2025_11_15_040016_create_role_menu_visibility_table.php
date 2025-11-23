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
        Schema::create('role_menu_visibility', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // User role: admin, doctor, nurse, receptionist, pharmacist, technician, staff
            $table->string('menu_type')->default('admin'); // 'admin' or 'staff'
            $table->string('menu_key'); // Menu item key: 'dashboard', 'patients', 'appointments', etc.
            $table->boolean('is_visible')->default(true); // Whether this menu item is visible for this role
            $table->integer('order')->default(0); // Order within this role's menu
            $table->timestamps();
            
            // Unique constraint: one visibility setting per role+menu_type+menu_key
            $table->unique(['role', 'menu_type', 'menu_key']);
            $table->index(['role', 'menu_type', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menu_visibility');
    }
};
