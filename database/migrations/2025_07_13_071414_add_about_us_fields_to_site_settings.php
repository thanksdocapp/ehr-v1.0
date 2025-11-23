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
        Schema::table('site_settings', function (Blueprint $table) {
            // About Us Hero Section
            $table->string('about_hero_title')->nullable();
            $table->text('about_hero_subtitle')->nullable();
            
            // About Us Main Content
            $table->text('about_main_title')->nullable();
            $table->text('about_main_description')->nullable();
            $table->text('about_main_content')->nullable();
            
            // About Us Image
            $table->string('about_image')->nullable();
            $table->string('about_image_alt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'about_hero_title',
                'about_hero_subtitle',
                'about_main_title',
                'about_main_description',
                'about_main_content',
                'about_image',
                'about_image_alt'
            ]);
        });
    }
};
