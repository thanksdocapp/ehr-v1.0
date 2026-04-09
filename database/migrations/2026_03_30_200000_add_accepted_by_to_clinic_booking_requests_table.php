<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track which user accepted a clinic booking (admin assign vs doctor self-accept).
     */
    public function up(): void
    {
        Schema::table('clinic_booking_requests', function (Blueprint $table) {
            $table->foreignId('accepted_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinic_booking_requests', function (Blueprint $table) {
            $table->dropForeign(['accepted_by_user_id']);
            $table->dropColumn(['accepted_by_user_id', 'accepted_at']);
        });
    }
};
