<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (! Schema::hasColumn('doctors', 'clinic_post_booking_redirect_url')) {
                $table->string('clinic_post_booking_redirect_url', 2048)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            if (Schema::hasColumn('doctors', 'clinic_post_booking_redirect_url')) {
                $table->dropColumn('clinic_post_booking_redirect_url');
            }
        });
    }
};
