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
            // Contact Settings
            [
                'key' => 'contact_address',
                'value' => '123 Medical Center Drive, Healthcare City, HC 12345',
                'type' => 'textarea',
                'group' => 'contact',
                'label' => 'Hospital Address',
                'description' => 'Complete address of the hospital',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => 'contact_phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Phone Number',
                'description' => 'Main contact phone number',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@newwaveshospital.com',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Contact Email',
                'description' => 'Main contact email address',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'key' => 'contact_emergency_phone',
                'value' => '+1 (555) 911-0000',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Emergency Phone',
                'description' => 'Emergency contact number',
                'sort_order' => 4,
                'is_active' => true
            ],
            
            // Social Media Settings
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/newwaveshospital',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Facebook URL',
                'description' => 'Facebook page URL',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/newwaveshospital',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Twitter URL',
                'description' => 'Twitter profile URL',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/newwaveshospital',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Instagram URL',
                'description' => 'Instagram profile URL',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/newwaveshospital',
                'type' => 'text',
                'group' => 'social',
                'label' => 'LinkedIn URL',
                'description' => 'LinkedIn company page URL',
                'sort_order' => 4,
                'is_active' => true
            ],
            
            // Footer Settings
            [
                'key' => 'footer_description',
                'value' => 'Leading healthcare excellence with compassionate care, advanced technology, and expert medical professionals.',
                'type' => 'textarea',
                'group' => 'footer',
                'label' => 'Footer Description',
                'description' => 'Description text in footer',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'key' => 'footer_copyright',
                'value' => 'All rights reserved.',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Copyright Text',
                'description' => 'Copyright text in footer',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'key' => 'footer_working_hours',
                'value' => '24/7 Emergency Services',
                'type' => 'text',
                'group' => 'footer',
                'label' => 'Working Hours',
                'description' => 'Working hours display in footer',
                'sort_order' => 3,
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
            'contact_address',
            'contact_phone',
            'contact_email',
            'contact_emergency_phone',
            'social_facebook',
            'social_twitter',
            'social_instagram',
            'social_linkedin',
            'footer_description',
            'footer_copyright',
            'footer_working_hours'
        ])->delete();
    }
};
