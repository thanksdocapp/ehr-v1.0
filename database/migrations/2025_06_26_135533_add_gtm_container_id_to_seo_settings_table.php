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
            if (!Schema::hasColumn('seo_settings', 'gtm_container_id')) {
                $table->string('gtm_container_id', 20)->nullable()->after('google_analytics_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_settings', function (Blueprint $table) {
            if (Schema::hasColumn('seo_settings', 'gtm_container_id')) {
                $table->dropColumn('gtm_container_id');
            }
        });
    }
};
