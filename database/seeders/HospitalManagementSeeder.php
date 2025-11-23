<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;
use App\Models\HomepageSection;
use App\Models\Service;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Testimonial;
use App\Models\ThemeSetting;
use App\Models\FrontendTemplate;

class HospitalSystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSiteSettings();
        $this->seedThemeSettings();
        $this->seedFrontendTemplates();
        $this->seedHomepageSections();
        $this->seedServices();
        $this->seedDepartments();
        $this->seedDoctors();
        $this->seedPatients();
        $this->seedAppointments();
        $this->seedTestimonials();
    }

    private function seedSiteSettings()
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'ThankDoc EHR', 'type' => 'text', 'group' => 'general', 'label' => 'Site Name', 'sort_order' => 1],
            ['key' => 'site_tagline', 'value' => 'Leading Healthcare Excellence', 'type' => 'text', 'group' => 'general', 'label' => 'Site Tagline', 'sort_order' => 2],
            ['key' => 'site_description', 'value' => 'ThankDoc EHR - Advanced healthcare solutions with modern technology and compassionate care.', 'type' => 'textarea', 'group' => 'general', 'label' => 'Site Description', 'sort_order' => 3],
            ['key' => 'site_logo', 'value' => 'logo.png', 'type' => 'image', 'group' => 'general', 'label' => 'Site Logo', 'sort_order' => 4],
            
            // Contact Settings
            ['key' => 'contact_address', 'value' => '123 Medical Center Drive, Healthcare City, HC 12345', 'type' => 'textarea', 'group' => 'contact', 'label' => 'Hospital Address', 'sort_order' => 1],
            ['key' => 'contact_phone', 'value' => '+1 (555) 123-4567', 'type' => 'text', 'group' => 'contact', 'label' => 'Phone Number', 'sort_order' => 2],
            ['key' => 'contact_emergency', 'value' => '+1 (555) 911-0000', 'type' => 'text', 'group' => 'contact', 'label' => 'Emergency Hotline', 'sort_order' => 3],
            ['key' => 'contact_email', 'value' => 'info@newwaveshospital.com', 'type' => 'text', 'group' => 'contact', 'label' => 'Contact Email', 'sort_order' => 4],
            ['key' => 'contact_appointments_email', 'value' => 'appointments@newwaveshospital.com', 'type' => 'text', 'group' => 'contact', 'label' => 'Appointments Email', 'sort_order' => 5],
            
            // Social Media
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/newwaveshospital', 'type' => 'text', 'group' => 'social', 'label' => 'Facebook URL', 'sort_order' => 1],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/newwaveshospital', 'type' => 'text', 'group' => 'social', 'label' => 'Twitter URL', 'sort_order' => 2],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/newwaveshospital', 'type' => 'text', 'group' => 'social', 'label' => 'Instagram URL', 'sort_order' => 3],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/newwaveshospital', 'type' => 'text', 'group' => 'social', 'label' => 'LinkedIn URL', 'sort_order' => 4],

            // Hospital Stats
            ['key' => 'stat_doctors', 'value' => '500+', 'type' => 'text', 'group' => 'stats', 'label' => 'Number of Doctors', 'sort_order' => 1],
            ['key' => 'stat_patients', 'value' => '50K+', 'type' => 'text', 'group' => 'stats', 'label' => 'Happy Patients', 'sort_order' => 2],
            ['key' => 'stat_departments', 'value' => '25+', 'type' => 'text', 'group' => 'stats', 'label' => 'Departments', 'sort_order' => 3],
            ['key' => 'stat_experience', 'value' => '24/7', 'type' => 'text', 'group' => 'stats', 'label' => 'Emergency Care', 'sort_order' => 4],
        ];

        foreach ($settings as $setting) {
            SiteSetting::create($setting);
        }
    }

    private function seedThemeSettings()
    {
        $themes = [
            [
                'name' => 'Medical Blue',
                'slug' => 'medical-blue',
                'description' => 'Professional medical theme with calming blue colors',
                'preview_image' => 'themes/medical-blue-preview.jpg',
                'primary_color' => '#0d6efd',
                'secondary_color' => '#6c757d',
                'success_color' => '#198754',
                'danger_color' => '#dc3545',
                'warning_color' => '#ffc107',
                'info_color' => '#0dcaf0',
                'light_color' => '#f8f9fa',
                'dark_color' => '#212529',
                'accent_color' => '#667eea',
                'is_default' => true,
                'is_active' => true
            ],
            [
                'name' => 'Healthcare Green',
                'slug' => 'healthcare-green',
                'description' => 'Fresh and natural theme with green accent colors',
                'preview_image' => 'themes/healthcare-green-preview.jpg',
                'primary_color' => '#198754',
                'secondary_color' => '#6c757d',
                'success_color' => '#28a745',
                'danger_color' => '#dc3545',
                'warning_color' => '#ffc107',
                'info_color' => '#17a2b8',
                'light_color' => '#f8f9fa',
                'dark_color' => '#343a40',
                'accent_color' => '#20c997',
                'is_default' => false,
                'is_active' => true
            ],
            [
                'name' => 'Modern Purple',
                'slug' => 'modern-purple',
                'description' => 'Contemporary design with elegant purple tones',
                'preview_image' => 'themes/modern-purple-preview.jpg',
                'primary_color' => '#6f42c1',
                'secondary_color' => '#6c757d',
                'success_color' => '#198754',
                'danger_color' => '#dc3545',
                'warning_color' => '#ffc107',
                'info_color' => '#0dcaf0',
                'light_color' => '#f8f9fa',
                'dark_color' => '#212529',
                'accent_color' => '#e83e8c',
                'is_default' => false,
                'is_active' => true
            ]
        ];

        foreach ($themes as $theme) {
            ThemeSetting::create($theme);
        }
    }

    private function seedFrontendTemplates()
    {
        $templates = [
            [
                'name' => 'Classic Medical',
                'slug' => 'classic-medical',
                'description' => 'Traditional hospital website design with professional layout',
                'version' => '1.0.0',
                'author' => 'NewWaves Projects',
                'template_path' => 'templates/classic-medical',
                'preview_image' => 'templates/classic-medical-preview.jpg',
                'screenshots' => ['classic-medical-1.jpg', 'classic-medical-2.jpg', 'classic-medical-3.jpg'],
                'layouts' => ['homepage', 'services', 'doctors', 'contact', 'appointment'],
                'components' => ['hero', 'services', 'testimonials', 'stats', 'contact-form'],
                'features' => ['responsive', 'seo-optimized', 'fast-loading', 'accessibility'],
                'color_schemes' => ['medical-blue', 'healthcare-green'],
                'type' => 'frontend',
                'style' => 'classic',
                'is_default' => true,
                'is_active' => true,
                'downloads' => 1250,
                'rating' => 4.8
            ],
            [
                'name' => 'Modern Healthcare',
                'slug' => 'modern-healthcare',
                'description' => 'Contemporary design with smooth animations and modern UI',
                'version' => '1.2.0',
                'author' => 'NewWaves Projects',
                'template_path' => 'templates/modern-healthcare',
                'preview_image' => 'templates/modern-healthcare-preview.jpg',
                'screenshots' => ['modern-healthcare-1.jpg', 'modern-healthcare-2.jpg'],
                'layouts' => ['homepage', 'services', 'doctors', 'contact', 'appointment', 'blog'],
                'components' => ['hero-slider', 'services-grid', 'doctor-cards', 'testimonials-carousel'],
                'features' => ['animations', 'parallax', 'video-background', 'dark-mode'],
                'color_schemes' => ['medical-blue', 'modern-purple'],
                'type' => 'frontend',
                'style' => 'modern',
                'is_premium' => true,
                'price' => 29.00,
                'is_active' => true,
                'downloads' => 890,
                'rating' => 4.9
            ]
        ];

        foreach ($templates as $template) {
            FrontendTemplate::create($template);
        }
    }

    private function seedHomepageSections()
    {
        $sections = [
            [
                'section_name' => 'hero',
                'title' => 'Your Health, Our Priority',
                'subtitle' => 'Leading Healthcare Excellence',
                'description' => 'Experience world-class healthcare with our state-of-the-art facilities, expert medical professionals, and compassionate patient care.',
                'image' => 'hero-doctor.jpg',
                'data' => [
                    'button_text' => 'Book Appointment',
                    'button_link' => '/appointment',
                    'secondary_button_text' => 'Learn More',
                    'secondary_button_link' => '#services'
                ],
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'section_name' => 'about',
                'title' => 'Leading Healthcare Excellence Since 2010',
                'subtitle' => 'About Us',
                'description' => 'ThankDoc EHR has been at the forefront of medical innovation, providing exceptional healthcare services to our community for over a decade.',
                'image' => 'about-hospital.jpg',
                'sort_order' => 2,
                'is_active' => true
            ]
        ];

        foreach ($sections as $section) {
            HomepageSection::create($section);
        }
    }

    private function seedServices()
    {
        $services = [
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'short_description' => 'Advanced cardiac care with state-of-the-art equipment and experienced cardiologists.',
                'description' => 'Our Cardiology department offers comprehensive heart care services including diagnosis, treatment, and prevention of cardiovascular diseases. We use the latest technology and evidence-based treatments to ensure the best outcomes for our patients.',
                'icon' => 'fas fa-heartbeat',
                'price' => 150.00,
                'duration' => '45 minutes',
                'features' => ['ECG', 'Echocardiography', 'Stress Testing', 'Cardiac Catheterization'],
                'sort_order' => 1,
                'is_featured' => true,
                'is_active' => true
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'short_description' => 'Expert neurological care for complex brain and nervous system disorders.',
                'description' => 'Our Neurology department specializes in diagnosing and treating disorders of the nervous system, including the brain, spinal cord, and peripheral nerves.',
                'icon' => 'fas fa-brain',
                'price' => 200.00,
                'duration' => '60 minutes',
                'features' => ['MRI Imaging', 'EEG Testing', 'Neuropsychological Testing', 'Stroke Care'],
                'sort_order' => 2,
                'is_featured' => true,
                'is_active' => true
            ],
            [
                'name' => 'Orthopedics',
                'slug' => 'orthopedics',
                'short_description' => 'Comprehensive bone and joint care with minimally invasive procedures.',
                'description' => 'Our Orthopedic department provides comprehensive care for musculoskeletal conditions, from sports injuries to joint replacements.',
                'icon' => 'fas fa-bone',
                'price' => 175.00,
                'duration' => '30 minutes',
                'features' => ['Joint Replacement', 'Arthroscopy', 'Sports Medicine', 'Fracture Care'],
                'sort_order' => 3,
                'is_featured' => true,
                'is_active' => true
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'short_description' => 'Specialized healthcare for infants, children, and adolescents.',
                'description' => 'Our Pediatrics department provides comprehensive healthcare for children from birth through adolescence, focusing on physical, emotional, and social health.',
                'icon' => 'fas fa-baby',
                'price' => 120.00,
                'duration' => '30 minutes',
                'features' => ['Well-child Visits', 'Immunizations', 'Developmental Screening', 'Adolescent Medicine'],
                'sort_order' => 4,
                'is_featured' => true,
                'is_active' => true
            ]
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }

    private function seedDepartments()
    {
        $departments = [
            [
                'name' => 'Emergency',
                'slug' => 'emergency',
                'description' => '24/7 emergency medical services with immediate care for critical conditions.',
                'icon' => 'fas fa-ambulance',
                'color' => '#dc3545',
                'head_of_department' => 'Dr. Michael Smith',
                'location' => 'Ground Floor, Wing A',
                'phone' => '+1 (555) 911-0000',
                'email' => 'emergency@newwaveshospital.com',
                'services' => ['Trauma Care', 'Cardiac Emergency', 'Stroke Care', 'Pediatric Emergency'],
                'working_hours' => '24/7 Available',
                'sort_order' => 1,
                'is_emergency' => true,
                'is_active' => true
            ],
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'description' => 'Comprehensive heart and cardiovascular care with advanced diagnostic and treatment options.',
                'icon' => 'fas fa-heartbeat',
                'color' => '#0d6efd',
                'head_of_department' => 'Dr. Sarah Johnson',
                'location' => '3rd Floor, Wing B',
                'phone' => '+1 (555) 123-4571',
                'email' => 'cardiology@newwaveshospital.com',
                'services' => ['Cardiac Surgery', 'Interventional Cardiology', 'Heart Failure Management', 'Preventive Cardiology'],
                'working_hours' => 'Mon-Fri: 8:00 AM - 6:00 PM',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'description' => 'Expert care for brain, spinal cord, and nervous system disorders.',
                'icon' => 'fas fa-brain',
                'color' => '#6f42c1',
                'head_of_department' => 'Dr. Michael Chen',
                'location' => '4th Floor, Wing B',
                'phone' => '+1 (555) 123-4572',
                'email' => 'neurology@newwaveshospital.com',
                'services' => ['Stroke Treatment', 'Epilepsy Care', 'Movement Disorders', 'Headache Management'],
                'working_hours' => 'Mon-Fri: 9:00 AM - 5:00 PM',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'description' => 'Dedicated healthcare for children from birth through adolescence.',
                'icon' => 'fas fa-baby',
                'color' => '#20c997',
                'head_of_department' => 'Dr. Emily Rodriguez',
                'location' => '2nd Floor, Wing C',
                'phone' => '+1 (555) 123-4573',
                'email' => 'pediatrics@newwaveshospital.com',
                'services' => ['Newborn Care', 'Child Development', 'Immunizations', 'Adolescent Health'],
                'working_hours' => 'Mon-Fri: 8:00 AM - 7:00 PM',
                'sort_order' => 4,
                'is_active' => true
            ]
        ];

        foreach ($departments as $department) {
            Department::create($department);
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
                'specialization' => 'Cardiology',
                'department_id' => 2, // Cardiology department
                'bio' => 'Dr. Sarah Johnson is a leading cardiologist with over 15 years of experience in cardiac surgery and interventional cardiology. She specializes in complex heart procedures and has helped thousands of patients restore their heart health.',
                'photo' => 'doctor-1.jpg',
                'qualification' => 'MD, FACC, FSCAI',
                'experience_years' => 15,
                'languages' => ['English', 'Spanish'],
                'specialties' => ['Cardiac Surgery', 'Interventional Cardiology', 'Heart Failure'],
                'email' => 'sarah.johnson@newwaveshospital.com',
                'phone' => '+1 (555) 123-4580',
                'room_number' => 'B301',
                'consultation_fee' => 150.00,
                'availability' => [
                    'monday' => ['available' => true, 'times' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'tuesday' => ['available' => true, 'times' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'wednesday' => ['available' => true, 'times' => ['09:00', '10:00', '11:00']],
                    'thursday' => ['available' => true, 'times' => ['09:00', '10:00', '11:00', '14:00', '15:00']],
                    'friday' => ['available' => true, 'times' => ['09:00', '10:00', '11:00']],
                    'saturday' => ['available' => false],
                    'sunday' => ['available' => false]
                ],
                'is_available_online' => true,
                'sort_order' => 1,
                'is_featured' => true,
                'is_active' => true
            ],
            [
                'title' => 'Dr.',
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'slug' => 'dr-michael-chen',
                'specialization' => 'Neurology',
                'department_id' => 3, // Neurology department
                'bio' => 'Dr. Michael Chen is a renowned neurologist specializing in brain disorders and neurological conditions. With 12+ years of experience, he has expertise in stroke treatment, epilepsy care, and movement disorders.',
                'photo' => 'doctor-2.jpg',
                'qualification' => 'MD, PhD, FAAN',
                'experience_years' => 12,
                'languages' => ['English', 'Mandarin'],
                'specialties' => ['Stroke Treatment', 'Epilepsy', 'Movement Disorders', 'Neuroimaging'],
                'email' => 'michael.chen@newwaveshospital.com',
                'phone' => '+1 (555) 123-4581',
                'room_number' => 'B401',
                'consultation_fee' => 200.00,
                'availability' => [
                    'monday' => ['available' => true, 'times' => ['10:00', '11:00', '14:00', '15:00', '16:00']],
                    'tuesday' => ['available' => true, 'times' => ['10:00', '11:00', '14:00', '15:00']],
                    'wednesday' => ['available' => true, 'times' => ['10:00', '11:00', '14:00', '15:00', '16:00']],
                    'thursday' => ['available' => true, 'times' => ['10:00', '11:00', '14:00']],
                    'friday' => ['available' => true, 'times' => ['10:00', '11:00', '14:00', '15:00']],
                    'saturday' => ['available' => false],
                    'sunday' => ['available' => false]
                ],
                'is_available_online' => true,
                'sort_order' => 2,
                'is_featured' => true,
                'is_active' => true
            ],
            [
                'title' => 'Dr.',
                'first_name' => 'Emily',
                'last_name' => 'Rodriguez',
                'slug' => 'dr-emily-rodriguez',
                'specialization' => 'Pediatrics',
                'department_id' => 4, // Pediatrics department
                'bio' => 'Dr. Emily Rodriguez is dedicated to providing comprehensive healthcare for children and adolescents. She has extensive experience in child development, preventive care, and adolescent medicine.',
                'photo' => 'doctor-3.jpg',
                'qualification' => 'MD, FAAP',
                'experience_years' => 10,
                'languages' => ['English', 'Spanish'],
                'specialties' => ['Child Development', 'Preventive Care', 'Adolescent Medicine', 'Immunizations'],
                'email' => 'emily.rodriguez@newwaveshospital.com',
                'phone' => '+1 (555) 123-4582',
                'room_number' => 'C201',
                'consultation_fee' => 120.00,
                'availability' => [
                    'monday' => ['available' => true, 'times' => ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00']],
                    'tuesday' => ['available' => true, 'times' => ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00']],
                    'wednesday' => ['available' => true, 'times' => ['08:00', '09:00', '10:00', '11:00']],
                    'thursday' => ['available' => true, 'times' => ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00']],
                    'friday' => ['available' => true, 'times' => ['08:00', '09:00', '10:00', '11:00', '14:00']],
                    'saturday' => ['available' => true, 'times' => ['09:00', '10:00', '11:00']],
                    'sunday' => ['available' => false]
                ],
                'is_available_online' => false,
                'sort_order' => 3,
                'is_featured' => true,
                'is_active' => true
            ]
        ];

        foreach ($doctors as $doctor) {
            Doctor::create($doctor);
        }
    }

    private function seedPatients()
    {
        $patients = [
            [
                'patient_id' => Patient::generatePatientId(),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'blood_group' => 'A+',
                'email' => 'john.doe@email.com',
                'phone' => '+1 (555) 234-5678',
                'emergency_contact' => 'Jane Doe (Wife)',
                'emergency_phone' => '+1 (555) 234-5679',
                'address' => '123 Main Street',
                'city' => 'Healthcare City',
                'state' => 'HC',
                'country' => 'USA',
                'postal_code' => '12345',
                'insurance_provider' => 'HealthCare Plus',
                'insurance_number' => 'HCP123456789',
                'allergies' => ['Penicillin', 'Shellfish'],
                'medical_conditions' => ['Hypertension'],
                'notes' => 'Regular patient, very cooperative.',
                'is_active' => true
            ],
            [
                'patient_id' => Patient::generatePatientId(),
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'date_of_birth' => '1992-03-22',
                'gender' => 'female',
                'blood_group' => 'O-',
                'email' => 'maria.garcia@email.com',
                'phone' => '+1 (555) 345-6789',
                'emergency_contact' => 'Carlos Garcia (Husband)',
                'emergency_phone' => '+1 (555) 345-6780',
                'address' => '456 Oak Avenue',
                'city' => 'Healthcare City',
                'state' => 'HC',
                'country' => 'USA',
                'postal_code' => '12346',
                'insurance_provider' => 'MediCare Insurance',
                'insurance_number' => 'MCI987654321',
                'allergies' => ['Latex'],
                'medical_conditions' => ['Diabetes Type 2'],
                'notes' => 'Requires Spanish translation services.',
                'is_active' => true
            ]
        ];

        foreach ($patients as $patient) {
            Patient::create($patient);
        }
    }

    private function seedAppointments()
    {
        $appointments = [
            [
                'appointment_number' => Appointment::generateAppointmentNumber(),
                'patient_id' => 1,
                'doctor_id' => 1,
                'department_id' => 2,
                'appointment_date' => now()->addDays(1),
                'appointment_time' => '10:00',
                'type' => 'consultation',
                'status' => 'confirmed',
                'reason' => 'Chest pain and shortness of breath',
                'symptoms' => 'Patient reports chest discomfort during physical activity',
                'fee' => 150.00,
                'is_online' => false
            ],
            [
                'appointment_number' => Appointment::generateAppointmentNumber(),
                'patient_id' => 2,
                'doctor_id' => 3,
                'department_id' => 4,
                'appointment_date' => now()->addDays(2),
                'appointment_time' => '14:00',
                'type' => 'followup',
                'status' => 'pending',
                'reason' => 'Diabetes management follow-up',
                'symptoms' => 'Blood sugar monitoring and medication adjustment',
                'fee' => 120.00,
                'is_online' => true,
                'meeting_link' => 'https://meet.newwaveshospital.com/room/abc123'
            ]
        ];

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }
    }

    private function seedTestimonials()
    {
        $testimonials = [
            [
                'patient_name' => 'John Smith',
                'patient_photo' => 'testimonial-1.jpg',
                'testimonial' => 'The care I received at ThankDoc EHR was exceptional. Dr. Johnson and her team saved my life with their expertise in cardiac surgery. I am forever grateful.',
                'rating' => 5,
                'treatment_type' => 'Cardiac Surgery',
                'doctor_id' => 1,
                'treatment_date' => '2023-11-15',
                'sort_order' => 1,
                'is_featured' => true,
                'is_approved' => true,
                'is_active' => true
            ],
            [
                'patient_name' => 'Lisa Chen',
                'patient_photo' => 'testimonial-2.jpg',
                'testimonial' => 'Dr. Chen provided excellent neurological care for my father. The staff was compassionate and professional throughout the entire treatment process.',
                'rating' => 5,
                'treatment_type' => 'Stroke Treatment',
                'doctor_id' => 2,
                'treatment_date' => '2023-12-02',
                'sort_order' => 2,
                'is_featured' => true,
                'is_approved' => true,
                'is_active' => true
            ],
            [
                'patient_name' => 'Amanda Rodriguez',
                'patient_photo' => 'testimonial-3.jpg',
                'testimonial' => 'Dr. Rodriguez has been wonderful with my children. She makes them feel comfortable and provides thorough, caring pediatric services.',
                'rating' => 5,
                'treatment_type' => 'Pediatric Care',
                'doctor_id' => 3,
                'treatment_date' => '2023-12-10',
                'sort_order' => 3,
                'is_featured' => true,
                'is_approved' => true,
                'is_active' => true
            ]
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
