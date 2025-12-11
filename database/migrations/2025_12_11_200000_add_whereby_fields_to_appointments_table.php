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
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'whereby_meeting_id')) {
                $table->string('whereby_meeting_id')->nullable()->after('meeting_platform');
            }
            if (!Schema::hasColumn('appointments', 'whereby_host_url')) {
                $table->string('whereby_host_url', 500)->nullable()->after('whereby_meeting_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'whereby_meeting_id')) {
                $table->dropColumn('whereby_meeting_id');
            }
            if (Schema::hasColumn('appointments', 'whereby_host_url')) {
                $table->dropColumn('whereby_host_url');
            }
        });
    }
};
