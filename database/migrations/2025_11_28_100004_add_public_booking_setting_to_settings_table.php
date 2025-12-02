<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add public_booking_enabled setting
        Setting::set(
            'public_booking_enabled',
            '1',
            'boolean',
            'general',
            'Allow public online booking through unique doctor/clinic links'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::where('key', 'public_booking_enabled')->delete();
    }
};

