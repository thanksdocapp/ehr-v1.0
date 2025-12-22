<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'website')) {
                $table->string('website')->nullable()->after('email');
                $table->index('website');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('departments')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'website')) {
                try {
                    $table->dropIndex(['website']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('website');
            }
        });
    }
};


