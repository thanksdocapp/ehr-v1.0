<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add consultation_type: in_person, online (video), telephone.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('appointments', 'consultation_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('consultation_type', 20)->nullable()->after('is_online');
            });
            // Backfill: existing rows get online or in_person from is_online
            DB::table('appointments')->whereNull('consultation_type')->update([
                'consultation_type' => DB::raw("CASE WHEN is_online = 1 THEN 'online' ELSE 'in_person' END"),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'consultation_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('consultation_type');
            });
        }
    }
};
