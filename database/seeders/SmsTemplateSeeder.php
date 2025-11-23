<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SmsTemplate;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'appointment_confirmation_sms',
                'message' => 'Hi {{patient_name}}, your appointment with {{doctor_name}} on {{appointment_date}} at {{appointment_time}} has been confirmed. Please arrive 15 minutes early. {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'SMS sent to patients when their appointment is confirmed',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'appointment_reminder_sms',
                'message' => 'REMINDER: {{patient_name}}, you have an appointment with {{doctor_name}} tomorrow at {{appointment_time}} in {{department}}. Please bring your ID and insurance card. {{hospital_name}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'SMS sent to patients 24 hours before their appointment',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'test_results_ready_sms',
                'message' => 'Hi {{patient_name}}, your {{test_type}} results are ready. Please log into your patient portal at {{portal_url}} to view them or call {{doctor_phone}}. {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'SMS sent to patients when their test results are available',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'test_type' => 'Type of test',
                    'portal_url' => 'Patient portal URL',
                    'doctor_phone' => 'Doctor\'s phone number',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'patient_welcome_sms',
                'message' => 'Welcome to {{hospital_name}}, {{patient_name}}! Your account has been created. Patient ID: {{patient_id}}. Call {{hospital_phone}} for appointments. Portal: {{portal_url}}',
                'category' => 'welcome',
                'status' => 'active',
                'description' => 'SMS sent to new patients when they register',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID number',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number',
                    'portal_url' => 'Patient portal URL'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'prescription_ready_sms',
                'message' => 'Hi {{patient_name}}, your prescription {{medication_name}} is ready for pickup at {{hospital_name}} Pharmacy. Hours: {{pharmacy_hours}}. Call {{pharmacy_phone}} for queries.',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'SMS sent to patients when their prescription is ready',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'medication_name' => 'Medication name',
                    'hospital_name' => 'Hospital name',
                    'pharmacy_hours' => 'Pharmacy operating hours',
                    'pharmacy_phone' => 'Pharmacy phone number'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'payment_reminder_sms',
                'message' => 'Hi {{patient_name}}, you have an outstanding balance of {{amount_due}} due on {{due_date}}. Pay online at {{payment_url}} or call {{billing_phone}}. {{hospital_name}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'SMS sent to patients with outstanding balances',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'amount_due' => 'Amount due',
                    'due_date' => 'Payment due date',
                    'payment_url' => 'Online payment URL',
                    'billing_phone' => 'Billing department phone',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'emergency_contact_sms',
                'message' => 'URGENT: {{patient_name}} has been admitted to {{hospital_name}} on {{admission_date}} at {{admission_time}}. Room: {{room_number}}. Contact: {{hospital_phone}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'SMS sent to emergency contacts when patient is admitted',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'hospital_name' => 'Hospital name',
                    'admission_date' => 'Admission date',
                    'admission_time' => 'Admission time',
                    'room_number' => 'Room number',
                    'hospital_phone' => 'Hospital phone number'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'appointment_cancellation_sms',
                'message' => 'Hi {{patient_name}}, your appointment with {{doctor_name}} on {{appointment_date}} at {{appointment_time}} has been cancelled. Please call {{hospital_phone}} to reschedule. {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'SMS sent to patients when their appointment is cancelled',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'hospital_phone' => 'Hospital phone number',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'discharge_sms',
                'message' => 'Hi {{patient_name}}, you have been discharged from {{hospital_name}}. Please follow your discharge instructions. Emergency contact: {{emergency_phone}}. Get well soon!',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'SMS sent to patients upon discharge',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'hospital_name' => 'Hospital name',
                    'emergency_phone' => 'Emergency phone number'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'appointment_rescheduled_sms',
                'message' => 'Hi {{patient_name}}, your appointment has been rescheduled to {{new_date}} at {{new_time}} with {{doctor_name}}. {{department}} dept. {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'SMS sent to patients when their appointment is rescheduled',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'new_date' => 'New appointment date',
                    'new_time' => 'New appointment time',
                    'doctor_name' => 'Doctor\'s name',
                    'department' => 'Department name',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'vaccination_reminder_sms',
                'message' => 'Hi {{patient_name}}, this is a reminder that {{vaccine_name}} vaccination is due for {{child_name}}. Please schedule an appointment at {{hospital_phone}}. {{hospital_name}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'SMS sent to parents for child vaccination reminders',
                'variables' => [
                    'patient_name' => 'Parent\'s full name',
                    'vaccine_name' => 'Vaccine name',
                    'child_name' => 'Child\'s name',
                    'hospital_phone' => 'Hospital phone number',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ],
            [
                'name' => 'surgery_reminder_sms',
                'message' => 'Hi {{patient_name}}, reminder: Your {{surgery_type}} surgery is scheduled for {{surgery_date}} at {{surgery_time}}. Please follow pre-surgery instructions. {{hospital_name}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'SMS sent to patients before scheduled surgery',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'surgery_type' => 'Type of surgery',
                    'surgery_date' => 'Surgery date',
                    'surgery_time' => 'Surgery time',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_id' => 'HOSPITAL'
            ]
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
