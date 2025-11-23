<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FAQ;

class FAQSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqs = [
            // General FAQs
            [
                'question' => 'What are your hospital operating hours?',
                'answer' => 'Our hospital is open 24/7 for emergency services. Regular outpatient services are available Monday to Friday from 8:00 AM to 6:00 PM, and Saturday from 9:00 AM to 4:00 PM.',
                'category' => 'general',
                'order' => 1
            ],
            [
                'question' => 'Where is your hospital located?',
                'answer' => 'We are located at 123 Healthcare Avenue, Medical District, City Name. We have convenient parking available and are accessible by public transportation.',
                'category' => 'general',
                'order' => 2
            ],
            [
                'question' => 'Do you accept walk-in patients?',
                'answer' => 'Yes, we accept walk-in patients for emergency services. For non-emergency consultations, we recommend scheduling an appointment to avoid long wait times.',
                'category' => 'general',
                'order' => 3
            ],
            
            // Appointments FAQs
            [
                'question' => 'How do I schedule an appointment?',
                'answer' => 'You can schedule an appointment by calling our reception at (555) 123-4567, using our online booking system, or visiting our hospital in person. Online booking is available 24/7 for your convenience.',
                'category' => 'appointments',
                'order' => 1
            ],
            [
                'question' => 'How far in advance should I book an appointment?',
                'answer' => 'We recommend booking routine appointments 1-2 weeks in advance. For specialist consultations, please book 2-4 weeks ahead. Emergency cases are always seen immediately.',
                'category' => 'appointments',
                'order' => 2
            ],
            [
                'question' => 'Can I reschedule or cancel my appointment?',
                'answer' => 'Yes, you can reschedule or cancel your appointment up to 24 hours before the scheduled time. Please call us or use our online portal to make changes.',
                'category' => 'appointments',
                'order' => 3
            ],
            
            // Services FAQs
            [
                'question' => 'What medical services do you offer?',
                'answer' => 'We offer comprehensive medical services including Emergency Care, Internal Medicine, Cardiology, Orthopedics, Pediatrics, Radiology, Laboratory Services, and Surgical Procedures.',
                'category' => 'services',
                'order' => 1
            ],
            [
                'question' => 'Do you have specialized departments?',
                'answer' => 'Yes, we have specialized departments for Cardiology, Neurology, Oncology, Orthopedics, Pediatrics, Maternity, and Mental Health. Each department is staffed with experienced specialists.',
                'category' => 'services',
                'order' => 2
            ],
            [
                'question' => 'Are diagnostic services available?',
                'answer' => 'Yes, we offer comprehensive diagnostic services including X-rays, CT scans, MRI, ultrasound, blood tests, and specialized cardiac testing. Most results are available within 24-48 hours.',
                'category' => 'services',
                'order' => 3
            ],
            
            // Billing & Insurance FAQs
            [
                'question' => 'What insurance plans do you accept?',
                'answer' => 'We accept most major insurance plans including Blue Cross Blue Shield, Aetna, Cigna, UnitedHealth, and Medicare. Please verify your coverage with our billing department before your visit.',
                'category' => 'billing',
                'order' => 1
            ],
            [
                'question' => 'What if I don\'t have insurance?',
                'answer' => 'We offer payment plans and financial assistance programs for uninsured patients. Our financial counselors can help you explore options and set up affordable payment arrangements.',
                'category' => 'billing',
                'order' => 2
            ],
            [
                'question' => 'How do I pay my medical bills?',
                'answer' => 'You can pay your bills online through our patient portal, by phone, by mail, or in person at our billing office. We accept cash, check, and major credit cards.',
                'category' => 'billing',
                'order' => 3
            ],
            
            // Emergency FAQs
            [
                'question' => 'When should I go to the emergency room?',
                'answer' => 'Visit our emergency room for life-threatening conditions such as chest pain, difficulty breathing, severe injuries, stroke symptoms, or any condition requiring immediate medical attention.',
                'category' => 'emergency',
                'order' => 1
            ],
            [
                'question' => 'What should I bring to the emergency room?',
                'answer' => 'Please bring a valid ID, insurance card, list of current medications, and any relevant medical records. If possible, have a family member or friend accompany you.',
                'category' => 'emergency',
                'order' => 2
            ],
            
            // Facilities FAQs
            [
                'question' => 'Do you have parking available?',
                'answer' => 'Yes, we have ample free parking available for patients and visitors. Valet parking is also available for emergency patients and those with mobility concerns.',
                'category' => 'facilities',
                'order' => 1
            ],
            [
                'question' => 'Are visiting hours restricted?',
                'answer' => 'Visiting hours are 10:00 AM to 8:00 PM daily. ICU and special care units may have different hours. Please check with the nursing staff for specific unit policies.',
                'category' => 'facilities',
                'order' => 2
            ],
            
            // Medical Information FAQs
            [
                'question' => 'How do I get my medical records?',
                'answer' => 'You can request medical records through our Health Information Management department. Complete a release form and provide valid identification. Records can be picked up or securely mailed.',
                'category' => 'medical',
                'order' => 1
            ],
            [
                'question' => 'How do I get my test results?',
                'answer' => 'Test results are available through our patient portal, by phone, or during your follow-up appointment. Critical results are communicated immediately by phone.',
                'category' => 'medical',
                'order' => 2
            ],
            
            // Technology & Online Services FAQs
            [
                'question' => 'How do I access the patient portal?',
                'answer' => 'Visit our website and click on "Patient Portal" to create an account or log in. You\'ll need your date of birth and a verification code that we can provide during registration.',
                'category' => 'technology',
                'order' => 1
            ],
            [
                'question' => 'Can I view my appointments online?',
                'answer' => 'Yes, through our patient portal you can view upcoming appointments, request new appointments, view test results, and communicate with your healthcare team.',
                'category' => 'technology',
                'order' => 2
            ]
        ];
        
        foreach ($faqs as $faq) {
            FAQ::create($faq);
        }
    }
}
