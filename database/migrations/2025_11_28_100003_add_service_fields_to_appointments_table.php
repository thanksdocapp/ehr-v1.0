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
            if (!Schema::hasColumn('appointments', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('department_id')->constrained('booking_services')->nullOnDelete();
            }
            if (!Schema::hasColumn('appointments', 'created_from')) {
                $table->string('created_from')->default('Internal')->after('status'); // e.g., 'Public Booking Link', 'Admin Panel', 'Patient Portal'
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'created_from']);
        });
    }
};

