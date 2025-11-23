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
        $settings = [
            [
                'key' => 'about_image',
                'value' => 'about-hospital.svg',
                'type' => 'image',
                'group' => 'about',
                'label' => 'About Section Image',
                'description' => 'Image displayed in the About Us section',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => 'about_title',
                'value' => 'Leading Healthcare Excellence Since 2010',
                'type' => 'text',
                'group' => 'about',
                'label' => 'About Section Title',
                'description' => 'Main title for the About Us section',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => 'about_description',
                'value' => 'ThankDoc EHR has been at the forefront of medical innovation, providing exceptional healthcare services to our community for over a decade.',
                'type' => 'textarea',
                'group' => 'about',
                'label' => 'About Section Description',
                'description' => 'Description text for the About Us section',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'key' => 'default_doctor_image',
                'value' => 'default-doctor.svg',
                'type' => 'image',
                'group' => 'doctors',
                'label' => 'Default Doctor Image',
                'description' => 'Default image for doctors without photos',
                'sort_order' => 1,
                'is_active' => true
            ]
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SiteSetting::whereIn('key', [
            'about_image',
            'about_title', 
            'about_description',
            'default_doctor_image'
        ])->delete();
    }
};
