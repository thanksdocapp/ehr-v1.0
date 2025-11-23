<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SeoPage;
use Carbon\Carbon;

class HospitalSeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hospitalPages = [
            [
                'title' => 'Home - ThankDoc EHR',
                'url' => '/',
                'meta_title' => 'ThankDoc EHR - Leading Healthcare Provider in Your City',
                'meta_description' => 'ThankDoc EHR offers comprehensive healthcare services with state-of-the-art facilities, experienced doctors, and compassionate care. Book your appointment today for quality medical treatment.',
                'meta_keywords' => 'hospital, healthcare, medical services, doctors, emergency care, surgery, clinic, health',
                'canonical_url' => 'https://newwaveshospital.com/',
                'seo_score' => 92,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'title' => 'Emergency Services - ThankDoc EHR',
                'url' => '/emergency-services',
                'meta_title' => '24/7 Emergency Care Services - ThankDoc EHR',
                'meta_description' => 'ThankDoc EHR provides round-the-clock emergency medical services with skilled emergency physicians, advanced trauma care, and rapid response teams for critical situations.',
                'meta_keywords' => 'emergency room, trauma care, 24/7 medical care, emergency services, critical care, ambulance',
                'canonical_url' => 'https://newwaveshospital.com/emergency-services',
                'seo_score' => 88,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(28),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'title' => 'Cardiology Department - ThankDoc EHR',
                'url' => '/departments/cardiology',
                'meta_title' => 'Cardiology Services - Heart Care Specialists | ThankDoc EHR',
                'meta_description' => 'Expert cardiology care at ThankDoc EHR. Our heart specialists provide comprehensive cardiac services including diagnostics, treatments, and surgical interventions for all heart conditions.',
                'meta_keywords' => 'cardiology, heart specialist, cardiac care, heart surgery, ECG, echocardiogram, heart disease',
                'canonical_url' => 'https://newwaveshospital.com/departments/cardiology',
                'seo_score' => 85,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'title' => 'Orthopedics Department - ThankDoc EHR',
                'url' => '/departments/orthopedics',
                'meta_title' => 'Orthopedic Surgery & Bone Care - ThankDoc EHR',
                'meta_description' => 'Advanced orthopedic care at ThankDoc EHR. Our bone and joint specialists provide treatments for fractures, joint replacements, sports injuries, and spine conditions.',
                'meta_keywords' => 'orthopedics, bone doctor, joint replacement, fracture treatment, sports medicine, spine surgery',
                'canonical_url' => 'https://newwaveshospital.com/departments/orthopedics',
                'seo_score' => 83,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(22),
                'updated_at' => Carbon::now()->subDays(4)
            ],
            [
                'title' => 'Neurology Department - ThankDoc EHR',
                'url' => '/departments/neurology',
                'meta_title' => 'Neurology & Brain Care Services - ThankDoc EHR',
                'meta_description' => 'Comprehensive neurological care at ThankDoc EHR. Our neurologists specialize in treating brain, spinal cord, and nervous system disorders with advanced diagnostic techniques.',
                'meta_keywords' => 'neurology, brain specialist, stroke treatment, epilepsy, migraine, neurological disorders',
                'canonical_url' => 'https://newwaveshospital.com/departments/neurology',
                'seo_score' => 79,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(5)
            ],
            [
                'title' => 'Maternity Ward - ThankDoc EHR',
                'url' => '/departments/maternity',
                'meta_title' => 'Maternity & Childbirth Services - ThankDoc EHR',
                'meta_description' => 'Safe and comfortable maternity care at ThankDoc EHR. Our obstetrics team provides prenatal care, delivery services, and postnatal support for expecting mothers.',
                'meta_keywords' => 'maternity, pregnancy, childbirth, obstetrics, prenatal care, delivery, newborn care',
                'canonical_url' => 'https://newwaveshospital.com/departments/maternity',
                'seo_score' => 76,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(18),
                'updated_at' => Carbon::now()->subDays(6)
            ],
            [
                'title' => 'Pediatrics Department - ThankDoc EHR',
                'url' => '/departments/pediatrics',
                'meta_title' => 'Pediatric Care for Children - ThankDoc EHR',
                'meta_description' => 'Specialized pediatric care at ThankDoc EHR. Our child specialists provide comprehensive healthcare for infants, children, and adolescents with compassionate treatment.',
                'meta_keywords' => 'pediatrics, child doctor, kids healthcare, infant care, pediatric surgery, child specialist',
                'canonical_url' => 'https://newwaveshospital.com/departments/pediatrics',
                'seo_score' => 72,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(7)
            ],
            [
                'title' => 'Laboratory Services - ThankDoc EHR',
                'url' => '/services/laboratory',
                'meta_title' => 'Medical Laboratory & Diagnostic Services',
                'meta_description' => 'Comprehensive laboratory services at ThankDoc EHR. We offer blood tests, urine analysis, pathology services, and diagnostic imaging with accurate results.',
                'meta_keywords' => 'lab tests, blood test, pathology, diagnostic services, medical laboratory',
                'canonical_url' => 'https://newwaveshospital.com/services/laboratory',
                'seo_score' => 68,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(12),
                'updated_at' => Carbon::now()->subDays(8)
            ],
            [
                'title' => 'Radiology Department - ThankDoc EHR',
                'url' => '/departments/radiology',
                'meta_title' => 'Medical Imaging & Radiology Services',
                'meta_description' => 'Advanced medical imaging at ThankDoc EHR including X-rays, CT scans, MRI, and ultrasound services with experienced radiologists.',
                'meta_keywords' => 'radiology, X-ray, CT scan, MRI, ultrasound, medical imaging',
                'canonical_url' => 'https://newwaveshospital.com/departments/radiology',
                'seo_score' => 65,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(9)
            ],
            [
                'title' => 'Pharmacy Services - ThankDoc EHR',
                'url' => '/services/pharmacy',
                'meta_title' => 'Hospital Pharmacy Services',
                'meta_description' => 'In-house pharmacy services at ThankDoc EHR providing prescription medications, over-the-counter drugs, and pharmaceutical consultation.',
                'meta_keywords' => 'pharmacy, prescription drugs, medications, pharmaceutical services',
                'canonical_url' => 'https://newwaveshospital.com/services/pharmacy',
                'seo_score' => 58,
                'status' => 'poor',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(10)
            ],
            [
                'title' => 'ICU Services - ThankDoc EHR',
                'url' => '/departments/icu',
                'meta_title' => 'Intensive Care Unit Services',
                'meta_description' => 'State-of-the-art ICU facilities at ThankDoc EHR with 24/7 monitoring and critical care for patients requiring intensive medical attention.',
                'meta_keywords' => 'ICU, intensive care, critical care, life support, monitoring',
                'canonical_url' => 'https://newwaveshospital.com/departments/icu',
                'seo_score' => 55,
                'status' => 'poor',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(6),
                'updated_at' => Carbon::now()->subDays(11)
            ],
            [
                'title' => 'Dental Department - ThankDoc EHR',
                'url' => '/departments/dental',
                'meta_title' => 'Dental Care Services',
                'meta_description' => 'Comprehensive dental care including general dentistry, oral surgery, and cosmetic procedures.',
                'meta_keywords' => 'dental, dentist, oral care, teeth cleaning, dental surgery',
                'canonical_url' => 'https://newwaveshospital.com/departments/dental',
                'seo_score' => 52,
                'status' => 'poor',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(12)
            ],
            [
                'title' => 'Physiotherapy Department - ThankDoc EHR',
                'url' => '/departments/physiotherapy',
                'meta_title' => 'Physical Therapy & Rehabilitation Services',
                'meta_description' => 'Professional physiotherapy services at ThankDoc EHR helping patients recover from injuries, surgeries, and improve mobility through specialized treatments.',
                'meta_keywords' => 'physiotherapy, physical therapy, rehabilitation, sports therapy, mobility, recovery',
                'canonical_url' => 'https://newwaveshospital.com/departments/physiotherapy',
                'seo_score' => 81,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(14),
                'updated_at' => Carbon::now()->subDays(2)
            ],
            [
                'title' => 'Oncology Department - ThankDoc EHR',
                'url' => '/departments/oncology',
                'meta_title' => 'Cancer Care & Oncology Services - ThankDoc EHR',
                'meta_description' => 'Comprehensive cancer treatment at ThankDoc EHR with advanced oncology services, chemotherapy, radiation therapy, and supportive care for cancer patients.',
                'meta_keywords' => 'oncology, cancer treatment, chemotherapy, radiation therapy, cancer care, tumor treatment',
                'canonical_url' => 'https://newwaveshospital.com/departments/oncology',
                'seo_score' => 87,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(26),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'title' => 'Dermatology Department - ThankDoc EHR',
                'url' => '/departments/dermatology',
                'meta_title' => 'Skin Care & Dermatology Services',
                'meta_description' => 'Expert dermatological care at ThankDoc EHR treating skin conditions, allergies, and cosmetic procedures with experienced dermatologists.',
                'meta_keywords' => 'dermatology, skin care, skin specialist, allergies, cosmetic procedures, dermatologist',
                'canonical_url' => 'https://newwaveshospital.com/departments/dermatology',
                'seo_score' => 74,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(16),
                'updated_at' => Carbon::now()->subDays(5)
            ],
            [
                'title' => 'Appointment Booking - ThankDoc EHR',
                'url' => '/appointments',
                'meta_title' => 'Book Medical Appointment Online - ThankDoc EHR',
                'meta_description' => 'Schedule your medical appointment online at ThankDoc EHR. Easy booking system for consultations, check-ups, and specialist appointments with our healthcare professionals.',
                'meta_keywords' => 'appointment booking, online appointment, medical consultation, doctor appointment, hospital booking',
                'canonical_url' => 'https://newwaveshospital.com/appointments',
                'seo_score' => 90,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(24),
                'updated_at' => Carbon::now()->subDays(1)
            ],
            [
                'title' => 'About ThankDoc EHR',
                'url' => '/about',
                'meta_title' => 'About ThankDoc EHR - Leading Healthcare Provider',
                'meta_description' => 'Learn about ThankDoc EHR, our mission, vision, and commitment to providing exceptional healthcare services. Meet our team of medical professionals and discover our facilities.',
                'meta_keywords' => 'about hospital, healthcare provider, medical team, hospital facilities, mission vision',
                'canonical_url' => 'https://newwaveshospital.com/about',
                'seo_score' => 84,
                'status' => 'optimized',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(27),
                'updated_at' => Carbon::now()->subDays(3)
            ],
            [
                'title' => 'Contact ThankDoc EHR',
                'url' => '/contact',
                'meta_title' => 'Contact ThankDoc EHR - Location & Contact Information',
                'meta_description' => 'Get in touch with ThankDoc EHR. Find our location, contact numbers, email addresses, and visit our hospital for quality healthcare services.',
                'meta_keywords' => 'contact hospital, hospital location, phone number, address, directions, visit hospital',
                'canonical_url' => 'https://newwaveshospital.com/contact',
                'seo_score' => 78,
                'status' => 'needs_work',
                'is_active' => true,
                'created_at' => Carbon::now()->subDays(21),
                'updated_at' => Carbon::now()->subDays(4)
            ]
        ];

        // Clear existing data
        SeoPage::truncate();

        // Insert hospital SEO data
        foreach ($hospitalPages as $page) {
            SeoPage::create($page);
        }

        $this->command->info('Hospital SEO pages seeded successfully!');
    }
}
