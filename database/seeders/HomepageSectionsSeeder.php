<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HomepageSection;

class HomepageSectionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create default homepage section titles and subtitles for the ones currently hardcoded
        $sections = [
            [
                'section_name' => 'services',
                'title' => 'Comprehensive Healthcare Services',
                'subtitle' => 'We provide a wide range of medical services with the highest standards of care',
                'description' => 'Our comprehensive healthcare services are designed to meet all your medical needs under one roof. From preventive care to specialized treatments, we ensure excellence in every aspect of healthcare delivery.',
                'image' => null,
                'data' => [],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'section_name' => 'departments',
                'title' => 'Our Medical Departments',
                'subtitle' => 'Specialized departments with expert medical professionals',
                'description' => 'Our medical departments are staffed with highly qualified specialists who provide exceptional care across various medical disciplines. Each department is equipped with state-of-the-art technology.',
                'image' => null,
                'data' => [],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'section_name' => 'doctors',
                'title' => 'Meet Our Expert Team',
                'subtitle' => 'Highly qualified medical professionals dedicated to your health',
                'description' => 'Our team of experienced doctors and medical professionals are committed to providing the highest quality healthcare services with compassion and expertise.',
                'image' => null,
                'data' => [],
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'section_name' => 'features',
                'title' => 'Why Choose Us',
                'subtitle' => 'Discover what makes us the preferred healthcare provider',
                'description' => 'We combine cutting-edge medical technology with compassionate care to deliver exceptional healthcare services that exceed your expectations.',
                'image' => null,
                'data' => [],
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['section_name' => $section['section_name']],
                $section
            );
        }
    }
}
