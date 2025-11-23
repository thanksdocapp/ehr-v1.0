<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class ContactSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Hero Section
            [
                'key' => 'contact_hero_title',
                'label' => 'Contact Hero Title',
                'value' => 'Contact Us',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Title displayed in the contact page hero section',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'key' => 'contact_hero_subtitle',
                'label' => 'Contact Hero Subtitle',
                'value' => 'Get in touch with our medical team. We\'re here to help you with all your healthcare needs, 24/7.',
                'type' => 'textarea',
                'group' => 'contact',
                'description' => 'Subtitle displayed in the contact page hero section',
                'is_active' => true,
                'sort_order' => 2
            ],
            
            // Contact Information
            [
                'key' => 'contact_emergency_phone',
                'label' => 'Emergency Phone',
                'value' => '+1 (555) 911-0000',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Emergency hotline phone number',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'key' => 'contact_general_phone',
                'label' => 'General Phone',
                'value' => '+1 (555) 123-4567',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'General contact phone number',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'key' => 'contact_email',
                'label' => 'Contact Email',
                'value' => 'info@newwaveshospital.com',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'General contact email address',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'key' => 'contact_appointments_email',
                'label' => 'Appointments Email',
                'value' => 'appointments@newwaveshospital.com',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Email for appointment requests',
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'key' => 'contact_address',
                'label' => 'Hospital Address',
                'value' => '123 Medical Center Drive<br>Healthcare City, HC 12345',
                'type' => 'textarea',
                'group' => 'contact',
                'description' => 'Physical address of the hospital',
                'is_active' => true,
                'sort_order' => 7
            ],
            
            // Operating Hours
            [
                'key' => 'contact_emergency_hours',
                'label' => 'Emergency Hours',
                'value' => '24/7 Available',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Emergency services operating hours',
                'is_active' => true,
                'sort_order' => 8
            ],
            [
                'key' => 'contact_outpatient_hours',
                'label' => 'Outpatient Hours',
                'value' => '6:00 AM - 10:00 PM',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Outpatient clinic operating hours',
                'is_active' => true,
                'sort_order' => 9
            ],
            [
                'key' => 'contact_visitor_hours',
                'label' => 'Visitor Hours',
                'value' => '8:00 AM - 8:00 PM',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Visitor hours',
                'is_active' => true,
                'sort_order' => 10
            ],
            [
                'key' => 'contact_pharmacy_hours',
                'label' => 'Pharmacy Hours',
                'value' => '24/7 Available',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Pharmacy operating hours',
                'is_active' => true,
                'sort_order' => 11
            ],
            
            // Map Settings
            [
                'key' => 'contact_map_embed_url',
                'label' => 'Map Embed URL',
                'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.355731049394!2d-74.01084558461596!3d40.70813797923035!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a316fd4bb07%3A0x6e9cc2a13dac3bda!2sHealthcare%20District%2C%20Medical%20Center%2C%20USA!5e0!3m2!1sen!2sus!4v1642162729845!5m2!1sen!2sus',
                'type' => 'textarea',
                'group' => 'contact',
                'description' => 'Google Maps embed URL for the hospital location',
                'is_active' => true,
                'sort_order' => 12
            ],
            
            // Contact Form Settings
            [
                'key' => 'contact_form_title',
                'label' => 'Contact Form Title',
                'value' => 'Send Us a Message',
                'type' => 'text',
                'group' => 'contact',
                'description' => 'Title for the contact form section',
                'is_active' => true,
                'sort_order' => 13
            ],
            [
                'key' => 'contact_form_subtitle',
                'label' => 'Contact Form Subtitle',
                'value' => 'Have a question or need assistance? Fill out the form below and our team will get back to you as soon as possible.',
                'type' => 'textarea',
                'group' => 'contact',
                'description' => 'Subtitle for the contact form section',
                'is_active' => true,
                'sort_order' => 14
            ],
            [
                'key' => 'contact_form_success_message',
                'label' => 'Form Success Message',
                'value' => 'Thank you for your message! We will get back to you within 24 hours.',
                'type' => 'textarea',
                'group' => 'contact',
                'description' => 'Success message shown after form submission',
                'is_active' => true,
                'sort_order' => 15
            ],
        ];
        
        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
