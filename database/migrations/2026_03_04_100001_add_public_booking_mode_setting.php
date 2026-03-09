<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('settings')->where('key', 'public_booking_mode')->exists();
        if (!$exists) {
            DB::table('settings')->insert([
                'key' => 'public_booking_mode',
                'value' => 'clinic',
                'group' => 'general',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'public_booking_mode')->delete();
    }
};
