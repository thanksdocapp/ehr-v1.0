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
        // Create missing Contact settings that are not yet in the database
        $contact_settings = [
            'contact_hero_title' => 'Contact Us',
            'contact_hero_subtitle' => 'Get in touch with our medical team. We\'re here to help you with all your healthcare needs, 24/7.',
            'contact_form_title' => 'Send Us a Message',
            'contact_form_subtitle' => 'Have a question or need assistance? Fill out the form below and our team will get back to you as soon as possible.',
            'contact_form_success_message' => 'Thank you for your message! We will get back to you within 24 hours.',
            'contact_emergency_hours' => '24/7 Available',
            'contact_outpatient_hours' => '6:00 AM - 10:00 PM',
            'contact_visitor_hours' => '8:00 AM - 8:00 PM',
            'contact_pharmacy_hours' => '24/7 Available',
            'contact_map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.355731049394!2d-74.01084558461596!3d40.70813797923035!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a316fd4bb07%3A0x6e9cc2a13dac3bda!2sHealthcare%20District%2C%20Medical%20Center%2C%20USA!5e0!3m2!1sen!2sus!4v1642162729845!5m2!1sen!2sus'
        ];

        foreach ($contact_settings as $key => $value) {
            if (!SiteSetting::where('key', $key)->exists()) {
                SiteSetting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => 'text',
                    'group' => 'contact',
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'description' => 'Contact page setting',
                    'sort_order' => 20,
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
        // Remove Contact settings
        SiteSetting::whereIn('key', [
            'contact_hero_title',
            'contact_hero_subtitle',
            'contact_form_title',
            'contact_form_subtitle',
            'contact_form_success_message',
            'contact_emergency_hours',
            'contact_outpatient_hours',
            'contact_visitor_hours',
            'contact_pharmacy_hours',
            'contact_map_embed_url'
        ])->delete();
    }
};
