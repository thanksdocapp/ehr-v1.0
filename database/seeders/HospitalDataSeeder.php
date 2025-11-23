<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HospitalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding basic hospital data...');
        
        // Seed basic departments
        $this->seedDepartments();
        
        // Seed basic doctors
        $this->seedDoctors();
        
        // Seed sample patients
        $this->seedPatients();
        
        $this->command->info('Basic hospital data seeded successfully!');
    }
    
    private function seedDepartments()
    {
        $departments = [
            ['name' => 'Cardiology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Neurology', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pediatrics', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Orthopedics', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Emergency Medicine', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        DB::table('departments')->insert($departments);
    }
    
    private function seedDoctors()
    {
        $doctors = [
            [
                'title' => 'Dr.',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'specialization' => 'Cardiologist',
                'department_id' => 1,
                'email' => 'sarah.johnson@hospital.com',
                'phone' => '+233 123 456 701',
                'consultation_fee' => 150.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Dr.',
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'specialization' => 'Neurologist',
                'department_id' => 2,
                'email' => 'michael.chen@hospital.com',
                'phone' => '+233 123 456 702',
                'consultation_fee' => 180.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Dr.',
                'first_name' => 'Emily',
                'last_name' => 'Rodriguez',
                'specialization' => 'Pediatrician',
                'department_id' => 3,
                'email' => 'emily.rodriguez@hospital.com',
                'phone' => '+233 123 456 703',
                'consultation_fee' => 120.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('doctors')->insert($doctors);
    }
    
    private function seedPatients()
    {
        $patients = [
            [
                'patient_id' => 'P2025001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@email.com',
                'phone' => '+233 123 456 789',
                'password' => Hash::make('password'),
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'blood_group' => 'O+',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => 'P2025002',
                'first_name' => 'Mary',
                'last_name' => 'Johnson',
                'email' => 'mary.johnson@email.com',
                'phone' => '+233 123 456 790',
                'password' => Hash::make('password'),
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'blood_group' => 'A+',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('patients')->insert($patients);
    }

    // Comprehensive hospital data seeding methods can follow here

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Service;
use App\Models\HomepageSection;
use App\Models\SiteSettings;
use App\Models\BannerSlide;
use App\Models\HomepageFeature;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\AboutStat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HospitalDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding hospital management system data...');
        
        // Seed basic site settings
        $this->seedSiteSettings();
        
        // Seed homepage sections
        $this->seedHomepageSections();
        
        // Seed banner slides
        $this->seedBannerSlides();
        
        // Seed homepage features
        $this->seedHomepageFeatures();
        
        // Seed departments
        $this->seedDepartments();
        
        // Seed services
        $this->seedServices();
        
        // Seed doctors
        $this->seedDoctors();
        
        // Seed testimonials
        $this->seedTestimonials();
        
        // Seed FAQs
        $this->seedFaqs();
        
        // Seed about stats
        $this->seedAboutStats();
        
        // Seed some patients for demo
        $this->seedPatients();
        
        $this->command->info('Hospital data seeded successfully!');
    }
    
    private function seedSiteSettings()
    {
        DB::table('site_settings')->insert([
            'site_name' => 'ThankDoc EHR',
            'site_tagline' => 'Quality Healthcare for Everyone',
            'site_description' => 'Leading healthcare provider committed to delivering exceptional medical services with compassion and expertise.',
            'contact_email' => 'info@newwaveshospital.com',
            'contact_phone' => '+233 123 456 789',
            'contact_address' => '123 Medical Center Drive, Accra, Ghana',
            'facebook_url' => 'https://facebook.com/newwaveshospital',
            'twitter_url' => 'https://twitter.com/newwaveshospital',
            'instagram_url' => 'https://instagram.com/newwaveshospital',
            'linkedin_url' => 'https://linkedin.com/company/newwaveshospital',
            'working_hours' => 'Mon-Fri: 8:00 AM - 10:00 PM<br>Sat-Sun: 9:00 AM - 8:00 PM',
            'emergency_number' => '+233 911 000 911',
            'footer_text' => '© 2025 ThankDoc EHR. All rights reserved.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    private function seedHomepageSections()
    {
        $sections = [
            [
                'section_name' => 'hero',
                'title' => 'Excellence in Healthcare',
                'subtitle' => 'Your Health, Our Priority',
                'content' => 'We provide world-class medical care with state-of-the-art facilities and experienced healthcare professionals.',
                'button_text' => 'Book Appointment',
                'button_url' => '/appointments/book',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'section_name' => 'about',
                'title' => 'About ThankDoc EHR',
                'subtitle' => '25+ Years of Medical Excellence',
                'content' => 'ThankDoc EHR has been serving the community for over 25 years, providing comprehensive healthcare services with cutting-edge technology and compassionate care.',
                'button_text' => 'Learn More',
                'button_url' => '/about',
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];
        
        foreach ($sections as $section) {
            $section['created_at'] = now();
            $section['updated_at'] = now();
            DB::table('homepage_sections')->insert($section);
        }
    }
    
    private function seedBannerSlides()
    {
        $slides = [
            [
                'title' => 'Advanced Medical Technology',
                'subtitle' => 'State-of-the-art equipment for better diagnosis',
                'description' => 'Experience the latest in medical technology with our advanced diagnostic and treatment equipment.',
                'button_text' => 'Our Services',
                'button_url' => '/services',
                'image' => 'banner1.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Expert Medical Team',
                'subtitle' => 'Experienced doctors and healthcare professionals',
                'description' => 'Our team of board-certified physicians and specialists are here to provide you with the best care.',
                'button_text' => 'Meet Our Doctors',
                'button_url' => '/doctors',
                'image' => 'banner2.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => '24/7 Emergency Care',
                'subtitle' => 'Round-the-clock emergency services',
                'description' => 'Our emergency department is always ready to provide immediate medical attention when you need it most.',
                'button_text' => 'Emergency Contact',
                'button_url' => '/contact',
                'image' => 'banner3.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];
        
        foreach ($slides as $slide) {
            $slide['created_at'] = now();
            $slide['updated_at'] = now();
            DB::table('banner_slides')->insert($slide);
        }
    }
    
    private function seedHomepageFeatures()
    {
        $features = [
            [
                'title' => 'Emergency Care',
                'subtitle' => '24/7 Emergency Services',
                'description' => 'Round-the-clock emergency medical services with experienced emergency physicians.',
                'icon' => 'fas fa-ambulance',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Expert Doctors',
                'subtitle' => 'Qualified Medical Professionals',
                'description' => 'Board-certified physicians and specialists with years of experience in their fields.',
                'icon' => 'fas fa-user-md',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Modern Equipment',
                'subtitle' => 'Advanced Medical Technology',
                'description' => 'State-of-the-art medical equipment for accurate diagnosis and effective treatment.',
                'icon' => 'fas fa-microscope',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Patient Care',
                'subtitle' => 'Compassionate Healthcare',
                'description' => 'Patient-centered care with a focus on comfort, dignity, and personalized treatment.',
                'icon' => 'fas fa-heart',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
        
        foreach ($features as $feature) {
            $feature['created_at'] = now();
            $feature['updated_at'] = now();
            DB::table('homepage_features')->insert($feature);
        }
    }
    
    private function seedDepartments()
    {
        $departments = [
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'description' => 'Comprehensive heart and cardiovascular care with advanced diagnostic and treatment options.',
                'image' => 'cardiology.jpg',
                'head_doctor' => 'Dr. Sarah Johnson',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'description' => 'Expert care for neurological conditions including brain, spine, and nervous system disorders.',
                'image' => 'neurology.jpg',
                'head_doctor' => 'Dr. Michael Chen',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'description' => 'Specialized medical care for infants, children, and adolescents.',
                'image' => 'pediatrics.jpg',
                'head_doctor' => 'Dr. Emily Rodriguez',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Orthopedics',
                'slug' => 'orthopedics',
                'description' => 'Treatment of musculoskeletal conditions including bones, joints, and muscles.',
                'image' => 'orthopedics.jpg',
                'head_doctor' => 'Dr. David Wilson',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Emergency Medicine',
                'slug' => 'emergency-medicine',
                'description' => '24/7 emergency medical services for urgent and critical care needs.',
                'image' => 'emergency.jpg',
                'head_doctor' => 'Dr. Lisa Thompson',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];
        
        foreach ($departments as $department) {
            $department['created_at'] = now();
            $department['updated_at'] = now();
            DB::table('departments')->insert($department);
        }
    }
    
    private function seedServices()
    {
        $services = [
            [
                'title' => 'General Consultation',
                'slug' => 'general-consultation',
                'short_description' => 'Comprehensive health check-ups and consultations with experienced physicians.',
                'description' => 'Our general consultation services provide comprehensive health assessments, preventive care, and treatment of common medical conditions. Our experienced physicians are committed to maintaining your overall health and well-being.',
                'icon' => 'fas fa-stethoscope',
                'image' => 'consultation.jpg',
                'price' => 100.00,
                'duration' => '30 minutes',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Laboratory Services',
                'slug' => 'laboratory-services',
                'short_description' => 'Complete range of diagnostic laboratory tests and screenings.',
                'description' => 'Our state-of-the-art laboratory provides accurate and timely diagnostic testing services including blood tests, urine analysis, microbiology, and specialized testing.',
                'icon' => 'fas fa-flask',
                'image' => 'laboratory.jpg',
                'price' => 50.00,
                'duration' => '15 minutes',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Radiology & Imaging',
                'slug' => 'radiology-imaging',
                'short_description' => 'Advanced imaging services including X-ray, CT, MRI, and ultrasound.',
                'description' => 'Our radiology department offers comprehensive imaging services using the latest technology for accurate diagnosis. Services include X-ray, CT scans, MRI, ultrasound, and mammography.',
                'icon' => 'fas fa-x-ray',
                'image' => 'radiology.jpg',
                'price' => 200.00,
                'duration' => '45 minutes',
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Surgical Services',
                'slug' => 'surgical-services',
                'short_description' => 'Modern surgical procedures with minimally invasive techniques.',
                'description' => 'Our surgical team provides a full range of surgical services including general surgery, laparoscopic procedures, and specialized surgical interventions with focus on patient safety and quick recovery.',
                'icon' => 'fas fa-cut',
                'image' => 'surgery.jpg',
                'price' => 1500.00,
                'duration' => '2-4 hours',
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
        
        foreach ($services as $service) {
            $service['created_at'] = now();
            $service['updated_at'] = now();
            DB::table('services')->insert($service);
        }
    }
    
    private function seedDoctors()
    {
        $doctors = [
            [
                'title' => 'Dr.',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'slug' => 'dr-sarah-johnson',
                'specialization' => 'Cardiologist',
                'department_id' => 1,
                'bio' => 'Dr. Sarah Johnson is a board-certified cardiologist with over 15 years of experience in cardiovascular medicine. She specializes in preventive cardiology and advanced heart disease management.',
                'qualification' => 'MD, FACC, FSCAI',
                'experience_years' => 15,
                'languages' => json_encode(['English', 'Spanish']),
                'specialties' => json_encode(['Interventional Cardiology', 'Heart Disease Prevention', 'Cardiac Catheterization']),
                'email' => 'sarah.johnson@hospital.com',
                'phone' => '+233 123 456 701',
                'room_number' => 'C-101',
                'consultation_fee' => 150.00,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Dr.',
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'slug' => 'dr-michael-chen',
                'specialization' => 'Neurologist',
                'department_id' => 2,
                'bio' => 'Dr. Michael Chen is a renowned neurologist specializing in brain and spinal cord disorders. He has extensive experience in treating stroke, epilepsy, and neurodegenerative diseases.',
                'qualification' => 'MD, PhD, FAAN',
                'experience_years' => 18,
                'languages' => json_encode(['English', 'Mandarin']),
                'specialties' => json_encode(['Stroke Care', 'Epilepsy', 'Movement Disorders']),
                'email' => 'michael.chen@hospital.com',
                'phone' => '+233 123 456 702',
                'room_number' => 'N-201',
                'consultation_fee' => 180.00,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Dr.',
                'first_name' => 'Emily',
                'last_name' => 'Rodriguez',
                'slug' => 'dr-emily-rodriguez',
                'specialization' => 'Pediatrician',
                'department_id' => 3,
                'bio' => 'Dr. Emily Rodriguez is a dedicated pediatrician with a passion for child healthcare. She provides comprehensive care for children from birth through adolescence.',
                'qualification' => 'MD, FAAP',
                'experience_years' => 12,
                'languages' => json_encode(['English', 'Spanish', 'French']),
                'specialties' => json_encode(['Child Development', 'Pediatric Nutrition', 'Adolescent Medicine']),
                'email' => 'emily.rodriguez@hospital.com',
                'phone' => '+233 123 456 703',
                'room_number' => 'P-301',
                'consultation_fee' => 120.00,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];
        
        foreach ($doctors as $doctor) {
            $doctor['created_at'] = now();
            $doctor['updated_at'] = now();
            DB::table('doctors')->insert($doctor);
        }
    }
    
    private function seedTestimonials()
    {
        $testimonials = [
            [
                'name' => 'John Smith',
                'designation' => 'Patient',
                'content' => 'The care I received at ThankDoc EHR was exceptional. The staff was professional and compassionate, and the facilities are top-notch.',
                'rating' => 5,
                'image' => 'testimonial1.jpg',
                'is_approved' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Maria Garcia',
                'designation' => 'Patient',
                'content' => 'Dr. Rodriguez provided excellent care for my daughter. The pediatric department is wonderful and child-friendly.',
                'rating' => 5,
                'image' => 'testimonial2.jpg',
                'is_approved' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Robert Wilson',
                'designation' => 'Patient',
                'content' => 'After my heart surgery, the recovery process was smooth thanks to the dedicated medical team. Highly recommended!',
                'rating' => 5,
                'image' => 'testimonial3.jpg',
                'is_approved' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
        ];
        
        foreach ($testimonials as $testimonial) {
            $testimonial['created_at'] = now();
            $testimonial['updated_at'] = now();
            DB::table('testimonials')->insert($testimonial);
        }
    }
    
    private function seedFaqs()
    {
        $faqs = [
            [
                'question' => 'How do I book an appointment?',
                'answer' => 'You can book an appointment online through our website, call our appointment hotline, or visit our reception desk during business hours.',
                'category' => 'Appointments',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'question' => 'What are your visiting hours?',
                'answer' => 'Visiting hours are from 10:00 AM to 8:00 PM daily. However, ICU visiting hours are restricted to 2:00 PM to 4:00 PM and 6:00 PM to 8:00 PM.',
                'category' => 'General',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'question' => 'Do you accept insurance?',
                'answer' => 'Yes, we accept most major insurance plans. Please contact our billing department to verify your coverage before your appointment.',
                'category' => 'Billing',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'question' => 'What should I bring to my appointment?',
                'answer' => 'Please bring a valid ID, insurance cards, list of current medications, and any relevant medical records or test results.',
                'category' => 'Appointments',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
        
        foreach ($faqs as $faq) {
            $faq['created_at'] = now();
            $faq['updated_at'] = now();
            DB::table('faqs')->insert($faq);
        }
    }
    
    private function seedAboutStats()
    {
        $stats = [
            [
                'title' => 'Years of Service',
                'value' => '25+',
                'description' => 'Years serving the community',
                'icon' => 'fas fa-calendar-alt',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Expert Doctors',
                'value' => '50+',
                'description' => 'Qualified medical professionals',
                'icon' => 'fas fa-user-md',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Happy Patients',
                'value' => '10,000+',
                'description' => 'Satisfied patients served',
                'icon' => 'fas fa-smile',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Success Rate',
                'value' => '98%',
                'description' => 'Treatment success rate',
                'icon' => 'fas fa-chart-line',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];
        
        foreach ($stats as $stat) {
            $stat['created_at'] = now();
            $stat['updated_at'] = now();
            DB::table('about_stats')->insert($stat);
        }
    }
    
    private function seedPatients()
    {
        $patients = [
            [
                'patient_id' => 'P2025001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@email.com',
                'phone' => '+233 123 456 789',
                'password' => bcrypt('password'),
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'blood_group' => 'O+',
                'address' => '123 Main Street, Accra',
                'city' => 'Accra',
                'state' => 'Greater Accra',
                'country' => 'Ghana',
                'postal_code' => '00233',
                'emergency_contact' => 'Jane Doe',
                'emergency_phone' => '+233 987 654 321',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'patient_id' => 'P2025002',
                'first_name' => 'Mary',
                'last_name' => 'Johnson',
                'email' => 'mary.johnson@email.com',
                'phone' => '+233 123 456 790',
                'password' => bcrypt('password'),
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'blood_group' => 'A+',
                'address' => '456 Oak Avenue, Kumasi',
                'city' => 'Kumasi',
                'state' => 'Ashanti',
                'country' => 'Ghana',
                'postal_code' => '00233',
                'emergency_contact' => 'Robert Johnson',
                'emergency_phone' => '+233 987 654 322',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];
        
        foreach ($patients as $patient) {
            $patient['created_at'] = now();
            $patient['updated_at'] = now();
            DB::table('patients')->insert($patient);
        }
    }
}
