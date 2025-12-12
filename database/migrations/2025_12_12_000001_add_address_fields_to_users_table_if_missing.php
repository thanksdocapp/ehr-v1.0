<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'address_line_2')) {
                $table->string('address_line_2')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address_line_2');
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable()->after('postal_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop individually to stay compatible with partially-applied schemas
            if (Schema::hasColumn('users', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('users', 'postal_code')) {
                $table->dropColumn('postal_code');
            }
            if (Schema::hasColumn('users', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('users', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('users', 'address_line_2')) {
                $table->dropColumn('address_line_2');
            }
            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }
        });
    }
};


