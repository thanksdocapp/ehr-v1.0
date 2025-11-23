<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add enable/disable flags for social media platforms
        $social_media_settings = [
            'social_facebook_enabled' => true,
            'social_twitter_enabled' => true,
            'social_instagram_enabled' => true,
            'social_linkedin_enabled' => true,
            'social_youtube_enabled' => true,
            'social_whatsapp_enabled' => true,
        ];

        foreach ($social_media_settings as $key => $default_value) {
            if (!SiteSetting::where('key', $key)->exists()) {
                SiteSetting::create([
                    'key' => $key,
                    'value' => $default_value ? '1' : '0',
                    'type' => 'boolean',
                    'group' => 'social_media',
                    'label' => ucwords(str_replace(['_', 'social'], [' ', 'Social'], $key)),
                    'description' => 'Enable/disable ' . str_replace(['_', 'social'], [' ', 'Social'], $key) . ' display',
                    'sort_order' => 30,
                    'is_active' => true
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove social media enable flags
        SiteSetting::whereIn('key', [
            'social_facebook_enabled',
            'social_twitter_enabled',
            'social_instagram_enabled',
            'social_linkedin_enabled',
            'social_youtube_enabled',
            'social_whatsapp_enabled'
        ])->delete();
    }
};
