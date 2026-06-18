<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $body = 'Dear {{patient_name}},\n\nThank you for your consultation with {{doctor_name}} at {{hospital_name}}.\n\nThe details recorded from your visit are below:\n\n- Doctor: {{doctor_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n- Diagnosis: {{diagnosis}}\n- Prescription: {{prescription}}\n- Follow-up Instructions: {{follow_up_instructions}}\n- Next Appointment: {{next_appointment_date}}\n\n{{contact_doctor_note}}\n\nBest regards,\n{{hospital_name}} Care Team';

        $variables = [
            'patient_name' => "Patient's full name",
            'doctor_name' => "Doctor's name",
            'doctor_email' => "Doctor's email address",
            'doctor_phone' => "Doctor's phone number",
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'department' => 'Department name',
            'diagnosis' => 'Diagnosis information',
            'prescription' => 'Prescription details',
            'follow_up_instructions' => 'Follow-up instructions',
            'next_appointment_date' => 'Next appointment date',
            'hospital_name' => 'Hospital name',
            'hospital_phone' => 'Hospital phone number',
            'contact_doctor_note' => 'Note asking the patient to contact their clinician if anything is unclear or incorrect',
        ];

        DB::table('email_templates')->where('name', 'appointment_completion')->update([
            'subject' => 'Thank you for your consultation - {{hospital_name}}',
            'body' => $body,
            'variables' => json_encode($variables),
            'description' => 'Sent to patients after a consultation is completed with visit details and how to query their clinician',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $body = 'Dear {{patient_name}},\n\nThank you for visiting {{hospital_name}}. Here is a summary of your appointment:\n\n- Doctor: {{doctor_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n- Diagnosis: {{diagnosis}}\n- Prescription: {{prescription}}\n- Follow-up Instructions: {{follow_up_instructions}}\n- Next Appointment: {{next_appointment_date}}\n\nIf you have any questions, please contact us.\n\nBest regards,\n{{hospital_name}} Care Team';

        $variables = [
            'patient_name' => "Patient's full name",
            'doctor_name' => "Doctor's name",
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'department' => 'Department name',
            'diagnosis' => 'Diagnosis information',
            'prescription' => 'Prescription details',
            'follow_up_instructions' => 'Follow-up instructions',
            'next_appointment_date' => 'Next appointment date',
            'hospital_name' => 'Hospital name',
        ];

        DB::table('email_templates')->where('name', 'appointment_completion')->update([
            'subject' => 'Appointment Summary - {{hospital_name}}',
            'body' => $body,
            'variables' => json_encode($variables),
            'description' => 'Sent to patients after appointment completion with summary',
            'updated_at' => now(),
        ]);
    }
};
