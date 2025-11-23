<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;

class HospitalSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating sample hospital data...');
        
        // Create Departments
        $departments = [
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'description' => 'Heart and cardiovascular system care',
                'icon' => 'fas fa-heartbeat',
                'color' => '#dc3545',
                'phone' => '+1-555-0101',
                'email' => 'cardiology@hospital.com',
                'is_active' => true,
            ],
            [
                'name' => 'Neurology',
                'slug' => 'neurology',
                'description' => 'Brain and nervous system care',
                'icon' => 'fas fa-brain',
                'color' => '#6f42c1',
                'phone' => '+1-555-0102',
                'email' => 'neurology@hospital.com',
                'is_active' => true,
            ],
            [
                'name' => 'Orthopedics',
                'slug' => 'orthopedics',
                'description' => 'Bone and joint care',
                'icon' => 'fas fa-bone',
                'color' => '#fd7e14',
                'phone' => '+1-555-0103',
                'email' => 'orthopedics@hospital.com',
                'is_active' => true,
            ],
            [
                'name' => 'Pediatrics',
                'slug' => 'pediatrics',
                'description' => 'Children\'s healthcare',
                'icon' => 'fas fa-child',
                'color' => '#20c997',
                'phone' => '+1-555-0104',
                'email' => 'pediatrics@hospital.com',
                'is_active' => true,
            ],
            [
                'name' => 'Emergency',
                'slug' => 'emergency',
                'description' => 'Emergency medical care',
                'icon' => 'fas fa-ambulance',
                'color' => '#dc3545',
                'phone' => '+1-555-0105',
                'email' => 'emergency@hospital.com',
                'is_emergency' => true,
                'is_active' => true,
            ],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['slug' => $dept['slug']], 
                $dept
            );
        }

        // Create Doctors
        $doctors = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'slug' => 'sarah-johnson',
                'email' => 'sarah.johnson@hospital.com',
                'phone' => '+1-555-1001',
                'specialization' => 'Cardiology',
                'department_id' => 1,
                'qualification' => 'MD, PhD in Cardiology',
                'experience_years' => 15,
                'bio' => 'Dr. Sarah Johnson is a renowned cardiologist with over 15 years of experience in treating heart conditions.',
                'is_active' => true,
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'slug' => 'michael-chen',
                'email' => 'michael.chen@hospital.com',
                'phone' => '+1-555-1002',
                'specialization' => 'Neurology',
                'department_id' => 2,
                'qualification' => 'MD, Neurology Specialist',
                'experience_years' => 12,
                'bio' => 'Dr. Michael Chen specializes in neurological disorders and has extensive experience in brain surgery.',
                'is_active' => true,
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Rodriguez',
                'slug' => 'emily-rodriguez',
                'email' => 'emily.rodriguez@hospital.com',
                'phone' => '+1-555-1003',
                'specialization' => 'Orthopedics',
                'department_id' => 3,
                'qualification' => 'MD, Orthopedic Surgeon',
                'experience_years' => 10,
                'bio' => 'Dr. Emily Rodriguez is an expert orthopedic surgeon specializing in joint replacements and sports injuries.',
                'is_active' => true,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'slug' => 'david-wilson',
                'email' => 'david.wilson@hospital.com',
                'phone' => '+1-555-1004',
                'specialization' => 'Pediatrics',
                'department_id' => 4,
                'qualification' => 'MD, Pediatric Specialist',
                'experience_years' => 8,
                'bio' => 'Dr. David Wilson is a caring pediatrician dedicated to providing excellent healthcare for children.',
                'is_active' => true,
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Thompson',
                'slug' => 'lisa-thompson',
                'email' => 'lisa.thompson@hospital.com',
                'phone' => '+1-555-1005',
                'specialization' => 'Emergency Medicine',
                'department_id' => 5,
                'qualification' => 'MD, Emergency Medicine',
                'experience_years' => 7,
                'bio' => 'Dr. Lisa Thompson is an emergency medicine specialist with expertise in critical care and trauma.',
                'is_active' => true,
            ],
        ];

        foreach ($doctors as $doctor) {
            Doctor::firstOrCreate(
                ['email' => $doctor['email']], 
                $doctor
            );
        }

        // Create Patients
        $patients = [
            [
                'patient_id' => 'P001',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@email.com',
                'phone' => '+1-555-2001',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
                'address' => '123 Main St, City, State 12345',
                'emergency_contact' => 'Jane Smith - +1-555-2002',
                'blood_group' => 'A+',
                'created_at' => Carbon::now()->subDays(30),
            ],
            [
                'patient_id' => 'P002',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@email.com',
                'phone' => '+1-555-2003',
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
                'address' => '456 Oak Ave, City, State 12346',
                'emergency_contact' => 'Carlos Garcia - +1-555-2004',
                'blood_group' => 'B+',
                'created_at' => Carbon::now()->subDays(25),
            ],
            [
                'patient_id' => 'P003',
                'first_name' => 'Robert',
                'last_name' => 'Johnson',
                'email' => 'robert.johnson@email.com',
                'phone' => '+1-555-2005',
                'date_of_birth' => '1978-11-08',
                'gender' => 'male',
                'address' => '789 Pine Rd, City, State 12347',
                'emergency_contact' => 'Mary Johnson - +1-555-2006',
                'blood_group' => 'O-',
                'created_at' => Carbon::now()->subDays(20),
            ],
            [
                'patient_id' => 'P004',
                'first_name' => 'Jennifer',
                'last_name' => 'Lee',
                'email' => 'jennifer.lee@email.com',
                'phone' => '+1-555-2007',
                'date_of_birth' => '1992-07-12',
                'gender' => 'female',
                'address' => '321 Elm St, City, State 12348',
                'emergency_contact' => 'David Lee - +1-555-2008',
                'blood_group' => 'AB+',
                'created_at' => Carbon::now()->subDays(15),
            ],
            [
                'patient_id' => 'P005',
                'first_name' => 'William',
                'last_name' => 'Brown',
                'email' => 'william.brown@email.com',
                'phone' => '+1-555-2009',
                'date_of_birth' => '1965-12-03',
                'gender' => 'male',
                'address' => '654 Maple Ave, City, State 12349',
                'emergency_contact' => 'Susan Brown - +1-555-2010',
                'blood_group' => 'A-',
                'created_at' => Carbon::now()->subDays(10),
            ],
        ];

        foreach ($patients as $patient) {
            Patient::firstOrCreate(
                ['email' => $patient['email']], 
                $patient
            );
        }

        // Create Appointments
        $appointments = [
            [
                'appointment_number' => 'APT-' . str_pad(1, 6, '0', STR_PAD_LEFT),
                'patient_id' => 1,
                'patient_name' => 'John Smith',
                'patient_email' => 'john.smith@email.com',
                'patient_phone' => '+1-555-2001',
                'doctor_id' => 1,
                'department_id' => 1,
                'appointment_date' => Carbon::today()->addDays(1),
                'appointment_time' => '09:00',
                'status' => 'confirmed',
                'reason' => 'Routine cardiac checkup',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'appointment_number' => 'APT-' . str_pad(2, 6, '0', STR_PAD_LEFT),
                'patient_id' => 2,
                'patient_name' => 'Maria Garcia',
                'patient_email' => 'maria.garcia@email.com',
                'patient_phone' => '+1-555-2003',
                'doctor_id' => 2,
                'department_id' => 2,
                'appointment_date' => Carbon::today()->addDays(2),
                'appointment_time' => '10:30',
                'status' => 'pending',
                'reason' => 'Headache consultation',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'appointment_number' => 'APT-' . str_pad(3, 6, '0', STR_PAD_LEFT),
                'patient_id' => 3,
                'patient_name' => 'Robert Johnson',
                'patient_email' => 'robert.johnson@email.com',
                'patient_phone' => '+1-555-2005',
                'doctor_id' => 3,
                'department_id' => 3,
                'appointment_date' => Carbon::today()->addDays(3),
                'appointment_time' => '14:00',
                'status' => 'confirmed',
                'reason' => 'Knee pain examination',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'appointment_number' => 'APT-' . str_pad(4, 6, '0', STR_PAD_LEFT),
                'patient_id' => 4,
                'patient_name' => 'Jennifer Lee',
                'patient_email' => 'jennifer.lee@email.com',
                'patient_phone' => '+1-555-2007',
                'doctor_id' => 4,
                'department_id' => 4,
                'appointment_date' => Carbon::today()->addDays(4),
                'appointment_time' => '11:00',
                'status' => 'pending',
                'reason' => 'Child wellness check',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'appointment_number' => 'APT-' . str_pad(5, 6, '0', STR_PAD_LEFT),
                'patient_id' => 5,
                'patient_name' => 'William Brown',
                'patient_email' => 'william.brown@email.com',
                'patient_phone' => '+1-555-2009',
                'doctor_id' => 1,
                'department_id' => 1,
                'appointment_date' => Carbon::today(),
                'appointment_time' => '16:00',
                'status' => 'confirmed',
                'reason' => 'Follow-up consultation',
                'created_at' => Carbon::now()->subHours(6),
            ],
        ];

        foreach ($appointments as $appointment) {
            Appointment::create($appointment);
        }

        // Create Services
        $services = [
            [
                'name' => 'Cardiac Surgery',
                'description' => 'Advanced cardiac surgical procedures',
                'price' => 5000.00,
                'duration' => 180,
                'is_active' => true,
                'department_id' => 1,
            ],
            [
                'name' => 'Brain MRI',
                'description' => 'Magnetic resonance imaging of the brain',
                'price' => 800.00,
                'duration' => 45,
                'is_active' => true,
                'department_id' => 2,
            ],
            [
                'name' => 'Joint Replacement',
                'description' => 'Hip and knee joint replacement surgery',
                'price' => 3500.00,
                'duration' => 120,
                'is_active' => true,
                'department_id' => 3,
            ],
            [
                'name' => 'Pediatric Vaccination',
                'description' => 'Childhood immunization program',
                'price' => 50.00,
                'duration' => 15,
                'is_active' => true,
                'department_id' => 4,
            ],
            [
                'name' => 'Emergency Care',
                'description' => '24/7 emergency medical services',
                'price' => 200.00,
                'duration' => 60,
                'is_active' => true,
                'department_id' => 5,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }

        $this->command->info('Sample hospital data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- 5 Departments');
        $this->command->info('- 5 Doctors');
        $this->command->info('- 5 Patients');
        $this->command->info('- 5 Appointments');
        $this->command->info('- 5 Services');
    }
}
