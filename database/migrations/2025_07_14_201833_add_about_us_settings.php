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
        // Create missing About Us settings
        $defaults = [
            'about_hero_title' => 'About ThankDoc EHR',
            'about_hero_subtitle' => 'Excellence in Healthcare Since 2010',
            'about_main_title' => 'Leading Healthcare Excellence',
            'about_main_description' => 'ThankDoc EHR has been at the forefront of medical innovation, providing exceptional healthcare services to our community for over a decade.',
            'about_main_content' => 'We are committed to delivering comprehensive, compassionate, and cutting-edge healthcare services. Our team of highly skilled medical professionals works tirelessly to ensure that every patient receives the best possible care in a comfortable and supportive environment.',
            'about_image_alt' => 'About ThankDoc EHR'
        ];

        foreach ($defaults as $key => $value) {
            if (!SiteSetting::where('key', $key)->exists()) {
                SiteSetting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => 'text',
                    'group' => 'about_us',
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'description' => 'About Us page setting',
                    'sort_order' => 10,
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
        // Remove About Us settings
        SiteSetting::whereIn('key', [
            'about_hero_title',
            'about_hero_subtitle',
            'about_main_title',
            'about_main_description',
            'about_main_content',
            'about_image_alt'
        ])->delete();
    }
};
