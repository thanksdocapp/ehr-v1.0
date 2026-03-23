<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'guardian_name')) {
                $table->text('guardian_name')->nullable()->after('guardian_id_document_path');
            }
            if (!Schema::hasColumn('patients', 'guardian_phone')) {
                $table->text('guardian_phone')->nullable()->after('guardian_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'guardian_phone')) {
                $table->dropColumn('guardian_phone');
            }
            if (Schema::hasColumn('patients', 'guardian_name')) {
                $table->dropColumn('guardian_name');
            }
        });
    }
};
