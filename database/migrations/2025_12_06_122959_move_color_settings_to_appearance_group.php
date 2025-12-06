<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Move all color/theme related settings from 'general' group to 'appearance' group
     */
    public function up(): void
    {
        // List of color/theme related setting keys
        $colorSettings = [
            'primary_color',
            'secondary_color',
            'accent_color',
            'text_color',
            'background_color',
            'sidebar_color',
            'success_color',
            'danger_color',
            'warning_color',
            'info_color',
            'theme_mode',
            'border_radius',
            'font_family',
        ];

        // Move each color setting from general to appearance group
        foreach ($colorSettings as $key) {
            // Check if setting exists in general group
            $generalSetting = DB::table('settings')
                ->where('key', $key)
                ->where('group', 'general')
                ->first();

            if ($generalSetting) {
                // Check if setting already exists in appearance group
                $appearanceSetting = DB::table('settings')
                    ->where('key', $key)
                    ->where('group', 'appearance')
                    ->first();

                if ($appearanceSetting) {
                    // If exists in both, keep the appearance one and delete the general one
                    DB::table('settings')
                        ->where('key', $key)
                        ->where('group', 'general')
                        ->delete();
                } else {
                    // Move from general to appearance
                    DB::table('settings')
                        ->where('key', $key)
                        ->where('group', 'general')
                        ->update(['group' => 'appearance']);
                }
            }
        }

        // Clear cache to ensure changes are reflected
        \Illuminate\Support\Facades\Cache::flush();
    }

    /**
     * Reverse the migrations.
     * 
     * Move color settings back to general group (if needed for rollback)
     */
    public function down(): void
    {
        // List of color/theme related setting keys
        $colorSettings = [
            'primary_color',
            'secondary_color',
            'accent_color',
            'text_color',
            'background_color',
            'sidebar_color',
            'success_color',
            'danger_color',
            'warning_color',
            'info_color',
            'theme_mode',
            'border_radius',
            'font_family',
        ];

        // Move each color setting back to general group
        foreach ($colorSettings as $key) {
            // Check if setting exists in appearance group
            $appearanceSetting = DB::table('settings')
                ->where('key', $key)
                ->where('group', 'appearance')
                ->first();

            if ($appearanceSetting) {
                // Check if setting already exists in general group
                $generalSetting = DB::table('settings')
                    ->where('key', $key)
                    ->where('group', 'general')
                    ->first();

                if (!$generalSetting) {
                    // Move from appearance to general
                    DB::table('settings')
                        ->where('key', $key)
                        ->where('group', 'appearance')
                        ->update(['group' => 'general']);
                }
            }
        }

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();
    }
};
