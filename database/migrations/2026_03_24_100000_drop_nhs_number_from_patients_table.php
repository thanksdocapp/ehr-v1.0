<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('patients', 'nhs_number')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('nhs_number');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('patients', 'nhs_number')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->string('nhs_number', 20)->nullable()->after('patient_id');
            });
        }
    }
};
