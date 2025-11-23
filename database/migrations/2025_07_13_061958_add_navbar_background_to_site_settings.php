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
        // Check if table exists
        if (!Schema::hasTable('site_settings')) {
            return; // Table doesn't exist, skip this migration
        }

        // Check if column already exists
        if (Schema::hasColumn('site_settings', 'navbar_background_image')) {
            return; // Column already exists, skip this migration
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('navbar_background_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('navbar_background_image');
        });
    }
};
