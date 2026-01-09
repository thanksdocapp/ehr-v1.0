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
        if (Schema::hasTable('doctor_service_prices')) {
            Schema::table('doctor_service_prices', function (Blueprint $table) {
                if (!Schema::hasColumn('doctor_service_prices', 'consultation_type')) {
                    $table->enum('consultation_type', ['in_person', 'online'])->default('in_person')->after('custom_duration_minutes');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('doctor_service_prices')) {
            Schema::table('doctor_service_prices', function (Blueprint $table) {
                if (Schema::hasColumn('doctor_service_prices', 'consultation_type')) {
                    $table->dropColumn('consultation_type');
                }
            });
        }
    }
};
