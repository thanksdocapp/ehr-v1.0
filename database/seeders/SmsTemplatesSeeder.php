<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsTemplate;
use Carbon\Carbon;

class SmsTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'name' => 'appointment_confirmation',
                'message' => 'Dear {{patient_name}}, your appointment with {{doctor_name}} has been confirmed for {{appointment_date}} at {{appointment_time}}. {{hospital_name}} - {{hospital_phone}}',
                'description' => 'Sent when a patient\'s appointment is confirmed',
                'category' => 'appointment',
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'appointment_reminder',
                'message' => 'Reminder: You have an appointment with {{doctor_name}} at {{hospital_name}} tomorrow ({{appointment_date}}) at {{appointment_time}}. Please arrive 15 minutes early.',
                'description' => 'Sent 24 hours before appointment',
                'category' => 'appointment',
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'hospital_name' => 'Hospital name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => false
                ]
            ],
            [
                'name' => 'test_results_ready',
                'message' => 'Hello {{patient_name}}, your {{test_type}} results are ready. Please collect them from {{hospital_name}} or call {{hospital_phone}} for more information.',
                'description' => 'Sent when test results are available',
                'category' => 'medical',
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'test_type' => 'Type of test',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'patient_welcome',
                'message' => 'Welcome to {{hospital_name}}! Your patient ID is {{patient_id}}. For assistance call {{hospital_phone}}. Thank you for choosing us for your healthcare needs.',
                'description' => 'Sent to new patients upon registration',
                'category' => 'welcome',
                'status' => 'active',
                'variables' => [
                    'hospital_name' => 'Hospital name',
                    'patient_id' => 'Patient ID',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => false,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'prescription_ready',
                'message' => 'Your prescription for {{medication_name}} is ready for pickup at {{hospital_name}} pharmacy. Pharmacy hours: {{pharmacy_hours}}. Questions? Call {{pharmacy_phone}}.',
                'description' => 'Sent when prescription is ready for pickup',
                'category' => 'pharmacy',
                'status' => 'active',
                'variables' => [
                    'medication_name' => 'Medication name',
                    'hospital_name' => 'Hospital name',
                    'pharmacy_hours' => 'Pharmacy operating hours',
                    'pharmacy_phone' => 'Pharmacy phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'payment_reminder',
                'message' => 'Dear {{patient_name}}, you have an outstanding balance of {{amount_due}} due on {{due_date}}. Pay online: {{payment_url}} or call {{billing_phone}}.',
                'description' => 'Sent for payment reminders',
                'category' => 'billing',
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'amount_due' => 'Amount due',
                    'due_date' => 'Payment due date',
                    'payment_url' => 'Online payment URL',
                    'billing_phone' => 'Billing department phone'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => false,
                    'send_immediately' => false
                ]
            ],
            [
                'name' => 'emergency_contact',
                'message' => 'URGENT: {{patient_name}} has been admitted to {{hospital_name}} emergency department. Please contact {{emergency_phone}} immediately.',
                'description' => 'Sent to emergency contacts',
                'category' => 'emergency',
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'hospital_name' => 'Hospital name',
                    'emergency_phone' => 'Emergency department phone'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'appointment_cancelled',
                'message' => 'Your appointment with {{doctor_name}} on {{appointment_date}} at {{appointment_time}} has been cancelled. Please call {{hospital_phone}} to reschedule.',
                'description' => 'Sent when appointment is cancelled',
                'category' => 'appointment',
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'discharge_notification',
                'message' => 'Dear {{patient_name}}, you have been discharged from {{hospital_name}}. Please follow the instructions given by {{doctor_name}}. For queries call {{hospital_phone}}.',
                'description' => 'Sent when patient is discharged',
                'category' => 'medical',
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'hospital_name' => 'Hospital name',
                    'doctor_name' => 'Doctor\'s name',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'appointment_rescheduled',
                'message' => 'Your appointment with {{doctor_name}} has been rescheduled to {{new_date}} at {{new_time}}. {{hospital_name}} - {{hospital_phone}}',
                'description' => 'Sent when appointment is rescheduled',
                'category' => 'appointment',
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'new_date' => 'New appointment date',
                    'new_time' => 'New appointment time',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => true
                ]
            ],
            [
                'name' => 'vaccination_reminder',
                'message' => 'Reminder: {{child_name}} is due for {{vaccine_name}} vaccination. Please schedule an appointment at {{hospital_name}} or call {{hospital_phone}}.',
                'description' => 'Sent for vaccination reminders',
                'category' => 'medical',
                'status' => 'active',
                'variables' => [
                    'child_name' => 'Child\'s name',
                    'vaccine_name' => 'Vaccine name',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => false,
                    'send_immediately' => false
                ]
            ],
            [
                'name' => 'surgery_reminder',
                'message' => 'Reminder: You have {{surgery_type}} scheduled for {{surgery_date}} at {{surgery_time}}. Please arrive 2 hours early. {{hospital_name}} - {{hospital_phone}}',
                'description' => 'Sent before scheduled surgery',
                'category' => 'medical',
                'status' => 'active',
                'variables' => [
                    'surgery_type' => 'Type of surgery',
                    'surgery_date' => 'Surgery date',
                    'surgery_time' => 'Surgery time',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL',
                'metadata' => [
                    'character_limit' => 160,
                    'is_critical' => true,
                    'send_immediately' => false
                ]
            ]
        ];

        foreach ($templates as $template) {
            SmsTemplate::create($template);
        }

        $this->command->info('SMS templates seeded successfully!');
    }
}
