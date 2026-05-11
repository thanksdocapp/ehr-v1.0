<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'exclude_from_consultation_report')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->boolean('exclude_from_consultation_report')->default(false)->after('created_from');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'exclude_from_consultation_report')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('exclude_from_consultation_report');
            });
        }
    }
};
