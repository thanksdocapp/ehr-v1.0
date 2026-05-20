<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('booking_services')) {
            return;
        }

        if (! Schema::hasColumn('booking_services', 'is_non_consultation')) {
            Schema::table('booking_services', function (Blueprint $table) {
                $table->boolean('is_non_consultation')->default(false)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking_services') && Schema::hasColumn('booking_services', 'is_non_consultation')) {
            Schema::table('booking_services', function (Blueprint $table) {
                $table->dropColumn('is_non_consultation');
            });
        }
    }
};
