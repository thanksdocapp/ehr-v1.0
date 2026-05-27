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
        if (! Schema::hasTable('templates') || Schema::hasColumn('templates', 'formeo_schema')) {
            return;
        }

        Schema::table('templates', function (Blueprint $table) {
            $table->longText('formeo_schema')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('formeo_schema');
        });
    }
};