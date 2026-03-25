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
            if (!Schema::hasColumn('booking_services', 'default_consultation_type')) {
                $table->string('default_consultation_type', 20)->default('in_person');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('booking_services')) {
            return;
        }
        Schema::table('booking_services', function (Blueprint $table) {
            if (Schema::hasColumn('booking_services', 'default_consultation_type')) {
                $table->dropColumn('default_consultation_type');
            }
        });
    }
};
