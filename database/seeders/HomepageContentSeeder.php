<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HomepageFeature;
use App\Models\HomepageSection;
use App\Models\Testimonial;
use App\Models\AboutStat;
use App\Models\FAQ;

class HomepageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Homepage Features
        $features = [
            [
                'title' => '24/7 Emergency',
                'subtitle' => 'Round-the-clock care',
                'description' => 'Round-the-clock emergency services with immediate medical attention.',
                'icon' => 'fas fa-clock',
                'color' => '#007bff',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Expert Doctors',
                'subtitle' => 'Highly qualified professionals',
                'description' => 'Highly qualified specialists with years of experience in their fields.',
                'icon' => 'fas fa-user-md',
                'color' => '#28a745',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Modern Equipment',
                'subtitle' => 'State-of-the-art technology',
                'description' => 'State-of-the-art medical technology for accurate diagnosis and treatment.',
                'icon' => 'fas fa-microscope',
                'color' => '#ffc107',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Compassionate Care',
                'subtitle' => 'Patient-centered approach',
                'description' => 'Patient-centered approach with personalized care and attention.',
                'icon' => 'fas fa-heart',
                'color' => '#dc3545',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($features as $feature) {
            HomepageFeature::updateOrCreate(
                ['title' => $feature['title']],
                $feature
            );
        }

        // Homepage Sections
        $sections = [
            [
                'section_name' => 'features',
                'title' => 'Why Choose Us',
                'subtitle' => 'Discover what makes us the preferred healthcare provider',
                'description' => 'Our commitment to excellence in healthcare sets us apart',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'section_name' => 'services',
                'title' => 'Comprehensive Healthcare Services',
                'subtitle' => 'We provide a wide range of medical services with the highest standards of care',
                'description' => 'From emergency care to specialized treatments',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'section_name' => 'departments',
                'title' => 'Our Medical Departments',
                'subtitle' => 'Specialized departments with expert medical professionals',
                'description' => 'Comprehensive medical care across multiple specialties',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'section_name' => 'doctors',
                'title' => 'Meet Our Expert Team',
                'subtitle' => 'Highly qualified medical professionals dedicated to your health',
                'description' => 'Our experienced doctors are here to provide the best care',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'section_name' => 'testimonials',
                'title' => 'What Our Patients Say',
                'subtitle' => 'Hear from our satisfied patients about their experience with us',
                'description' => 'Real stories from real patients',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::updateOrCreate(
                ['section_name' => $section['section_name']],
                $section
            );
        }

        // Testimonials
        $testimonials = [
            [
                'customer_name' => 'Sarah Johnson',
                'customer_position' => 'Patient',
                'customer_company' => '',
                'review_text' => 'The medical staff at ThankDoc EHR provided exceptional care during my treatment. Their professionalism and compassion made all the difference in my recovery.',
                'rating' => 5,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Michael Chen',
                'customer_position' => 'Patient',
                'customer_company' => '',
                'review_text' => 'Outstanding facilities and caring staff. The doctors took time to explain everything and made sure I was comfortable throughout my stay.',
                'rating' => 5,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Emily Davis',
                'customer_position' => 'Patient',
                'customer_company' => '',
                'review_text' => 'I\'m grateful for the excellent care I received. The hospital\'s modern equipment and skilled medical team provided me with the best treatment possible.',
                'rating' => 5,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Robert Wilson',
                'customer_position' => 'Patient',
                'customer_company' => '',
                'review_text' => 'From the moment I walked in, I felt welcomed and cared for. The entire staff went above and beyond to ensure my comfort and quick recovery.',
                'rating' => 5,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'customer_name' => 'Lisa Thompson',
                'customer_position' => 'Patient',
                'customer_company' => '',
                'review_text' => 'Exceptional service and medical expertise. The patient portal made it easy to manage my appointments and access my medical records.',
                'rating' => 5,
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'customer_name' => 'David Martinez',
                'customer_position' => 'Patient',
                'customer_company' => '',
                'review_text' => 'The emergency department was efficient and professional. They provided immediate care when I needed it most, and the follow-up care was excellent.',
                'rating' => 5,
                'sort_order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['customer_name' => $testimonial['customer_name']],
                $testimonial
            );
        }

        // About Statistics
        $aboutStats = [
            [
                'title' => 'Expert Doctors',
                'subtitle' => 'Qualified Professionals',
                'value' => '25',
                'suffix' => '+',
                'color' => '#007bff',
                'icon' => 'fas fa-user-md',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Happy Patients',
                'subtitle' => 'Satisfied Customers',
                'value' => '1500',
                'suffix' => '+',
                'color' => '#28a745',
                'icon' => 'fas fa-heart',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Departments',
                'subtitle' => 'Medical Specialties',
                'value' => '8',
                'suffix' => '+',
                'color' => '#ffc107',
                'icon' => 'fas fa-hospital',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Emergency Care',
                'subtitle' => 'Round-the-clock',
                'value' => '24/7',
                'color' => '#dc3545',
                'icon' => 'fas fa-ambulance',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($aboutStats as $stat) {
            AboutStat::updateOrCreate(
                ['title' => $stat['title']],
                $stat
            );
        }

        // FAQ Content
        $faqs = [
            [
                'question' => 'What are your visiting hours?',
                'answer' => 'Our general visiting hours are from 9:00 AM to 8:00 PM daily. However, visiting hours may vary by department. Please check with the nursing station for specific department hours.',
                'category' => 'General',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'question' => 'How do I book an appointment?',
                'answer' => 'You can book an appointment through our online booking system, by calling our main number, or by visiting our reception desk. Our patient portal also allows registered patients to book appointments online.',
                'category' => 'Appointments',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'question' => 'Do you accept insurance?',
                'answer' => 'Yes, we accept most major insurance plans. Please contact our billing department or check with your insurance provider to verify coverage for specific services.',
                'category' => 'Billing',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'question' => 'What should I bring for my appointment?',
                'answer' => 'Please bring a valid ID, your insurance card, any relevant medical records, a list of current medications, and any referral forms from your primary care physician.',
                'category' => 'Appointments',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'question' => 'How do I access my medical records?',
                'answer' => 'Registered patients can access their medical records through our secure patient portal. You can also request physical copies by contacting our medical records department.',
                'category' => 'Patient Portal',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'question' => 'What emergency services do you provide?',
                'answer' => 'Our emergency department operates 24/7 and provides comprehensive emergency care including trauma care, cardiac emergencies, stroke care, and pediatric emergencies.',
                'category' => 'Emergency Services',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'question' => 'How do I register for the patient portal?',
                'answer' => 'You can register for the patient portal by visiting our website and clicking on "Patient Portal" or "Register Now". You\'ll need your patient ID and some basic information to complete the registration.',
                'category' => 'Patient Portal',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'question' => 'What parking options are available?',
                'answer' => 'We offer free parking for patients and visitors in our main parking lot. Valet parking is also available at the main entrance for your convenience.',
                'category' => 'General',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'question' => 'How do I pay my bill?',
                'answer' => 'You can pay your bill online through our patient portal, by phone, by mail, or in person at our billing office. We accept cash, checks, and all major credit cards.',
                'category' => 'Billing',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'question' => 'Do you offer telehealth services?',
                'answer' => 'Yes, we offer telehealth consultations for certain conditions and follow-up appointments. Please contact your doctor\'s office to schedule a telehealth appointment.',
                'category' => 'Services',
                'sort_order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            FAQ::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }

        $this->command->info('Homepage content seeded successfully!');
    }
}
