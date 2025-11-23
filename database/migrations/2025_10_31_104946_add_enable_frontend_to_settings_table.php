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
        // Insert the enable_frontend setting with default value of true (1)
        \Illuminate\Support\Facades\DB::table('settings')->insert([
            'key' => 'enable_frontend',
            'value' => '1',
            'type' => 'boolean',
            'group' => 'general',
            'description' => 'Enable or disable frontend/homepage display. When disabled, visitors will be redirected to staff login.',
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the enable_frontend setting
        \Illuminate\Support\Facades\DB::table('settings')
            ->where('key', 'enable_frontend')
            ->delete();
    }
};
