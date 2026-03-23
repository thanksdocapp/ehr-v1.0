<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('booking_services')) {
            return;
        }

        Schema::table('booking_services', function (Blueprint $table) {
            if (!Schema::hasColumn('booking_services', 'minimum_age')) {
                $table->unsignedSmallInteger('minimum_age')->nullable()->after('default_price');
            }
            if (!Schema::hasColumn('booking_services', 'maximum_age')) {
                $table->unsignedSmallInteger('maximum_age')->nullable()->after('minimum_age');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_services')) {
            return;
        }

        Schema::table('booking_services', function (Blueprint $table) {
            if (Schema::hasColumn('booking_services', 'maximum_age')) {
                $table->dropColumn('maximum_age');
            }
            if (Schema::hasColumn('booking_services', 'minimum_age')) {
                $table->dropColumn('minimum_age');
            }
        });
    }
};
