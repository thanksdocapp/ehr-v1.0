<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('sidebar_menu_orders')
            ->where('menu_key', 'clinic-booking-requests')
            ->where('menu_type', 'staff')
            ->exists();

        if (!$exists) {
            $maxOrder = (int) DB::table('sidebar_menu_orders')
                ->where('menu_type', 'staff')
                ->max('order');
            DB::table('sidebar_menu_orders')->insert([
                'menu_key' => 'clinic-booking-requests',
                'menu_type' => 'staff',
                'order' => $maxOrder + 1,
                'is_visible' => true,
                'label' => 'Clinic Requests',
                'menu_data' => json_encode(['icon' => 'fa-inbox']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('sidebar_menu_orders')
            ->where('menu_key', 'clinic-booking-requests')
            ->where('menu_type', 'staff')
            ->delete();
    }
};
