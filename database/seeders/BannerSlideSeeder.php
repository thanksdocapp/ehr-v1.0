<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BannerSlide;

class BannerSlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Your Health, Our Priority',
                'subtitle' => 'Excellence in Healthcare',
                'description' => 'Experience world-class healthcare with our state-of-the-art facilities, expert medical professionals, and compassionate patient care.',
                'button_text' => 'Book Appointment',
                'button_url' => '/appointments/book',
                'text_color' => '#ffffff',
                'background_color' => '#0d6efd',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'title' => 'Advanced Medical Technology',
                'subtitle' => 'Cutting-Edge Healthcare Solutions',
                'description' => 'We use the latest medical technology and innovative treatment methods to provide the best possible care for our patients.',
                'button_text' => 'Learn More',
                'button_url' => '#services',
                'text_color' => '#ffffff',
                'background_color' => '#198754',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'title' => '24/7 Emergency Care',
                'subtitle' => 'Always Here When You Need Us',
                'description' => 'Our emergency department is staffed 24/7 with experienced medical professionals ready to handle any medical emergency.',
                'button_text' => 'Emergency Contact',
                'button_url' => '/contact',
                'text_color' => '#ffffff',
                'background_color' => '#dc3545',
                'sort_order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($slides as $slide) {
            BannerSlide::create($slide);
        }
    }
}
