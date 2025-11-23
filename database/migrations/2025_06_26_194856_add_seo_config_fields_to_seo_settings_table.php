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
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->text('meta_keywords')->nullable()->after('default_keywords');
            $table->text('meta_description')->nullable()->after('meta_keywords');
            $table->string('social_title')->nullable()->after('meta_description');
            $table->text('social_description')->nullable()->after('social_title');
            $table->string('social_image')->nullable()->after('social_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_keywords',
                'meta_description', 
                'social_title',
                'social_description',
                'social_image'
            ]);
        });
    }
};
