<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SeoSettings;

class SeoSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        SeoSettings::truncate();

        // Create default SEO settings
        SeoSettings::create([
            'default_title' => 'ThankDoc EHR - Leading Healthcare Provider',
            'default_description' => 'ThankDoc EHR offers comprehensive healthcare services with state-of-the-art facilities, experienced doctors, and compassionate care. Book your appointment today for quality medical treatment.',
            'default_keywords' => 'hospital, healthcare, medical services, doctors, emergency care, surgery, clinic, health, newwaves hospital',
            'meta_keywords' => 'hospital, healthcare, medical services, doctors, emergency care, surgery, clinic, health, newwaves hospital, medical center, patient care',
            'meta_description' => 'ThankDoc EHR - Your trusted healthcare partner providing comprehensive medical services, emergency care, specialized treatments, and compassionate patient care with advanced medical technology.',
            'social_title' => 'ThankDoc EHR - Leading Healthcare Provider',
            'social_description' => 'ThankDoc EHR offers comprehensive healthcare services with state-of-the-art facilities, experienced doctors, and compassionate care.',
            'social_image' => 'assets/images/logos/newwaves-hospital-logo.png',
            'og_title' => 'ThankDoc EHR - Leading Healthcare Provider',
            'og_description' => 'ThankDoc EHR offers comprehensive healthcare services with state-of-the-art facilities, experienced doctors, and compassionate care.',
            'og_image' => 'assets/images/logos/newwaves-hospital-logo.png',
            'twitter_card' => 'summary_large_image',
            'twitter_site' => '@NewWavesHospital',
            'google_analytics_id' => '',
            'google_search_console_id' => '',
            'facebook_pixel_id' => '',
            'robots_content' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        ]);

        $this->command->info('SEO settings seeded successfully!');
    }
}
