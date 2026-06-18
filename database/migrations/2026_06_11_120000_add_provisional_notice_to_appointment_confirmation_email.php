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
        $body = 'Dear {{patient_name}},\n\n{{provisional_notice}}{{confirmation_intro}}\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_address}}\n\nAdditional Notes:\n{{notes}}\n\nFor online video consultations, use your participant link above to join. Please join 5 minutes before your scheduled time.\n\nPlease arrive 15 minutes early for check-in.\n\nImportant reminders:\n- Bring your ID and insurance card\n- Bring a list of current medications\n- Inform us of any changes to your health status\n\nIf you need to cancel or reschedule, please contact us at {{hospital_phone}} at least 24 hours in advance.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Team';

        $variables = [
            'patient_name' => "Patient's full name",
            'doctor_name' => "Doctor's name",
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'department' => 'Department name',
            'notes' => 'Additional appointment notes',
            'hospital_name' => 'Hospital name',
            'hospital_address' => 'Hospital address',
            'hospital_phone' => 'Hospital phone number',
            'online_consultation_section' => 'Online consultation block (participant link, platform, instructions)',
            'participant_link' => 'Video join link for patient (Whereby participant URL)',
            'meeting_link' => 'Same as participant_link',
            'join_meeting_url' => 'Same as participant_link',
            'provisional_notice' => 'Provisional booking notice (empty once clinician confirms)',
            'confirmation_intro' => 'Opening line for provisional vs confirmed booking',
            'confirmation_email_subject' => 'Email subject prefix (Provisional Appointment vs Appointment Confirmation)',
        ];

        DB::table('email_templates')->where('name', 'appointment_confirmation')->update([
            'subject' => '{{confirmation_email_subject}} - {{hospital_name}}',
            'body' => $body,
            'variables' => json_encode($variables),
            'description' => 'Sent to patients when an appointment is booked (provisional) and again when a clinician confirms it',
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $body = 'Dear {{patient_name}},\n\nYour appointment has been confirmed with the following details:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_address}}\n\nAdditional Notes:\n{{notes}}\n\nFor online video consultations, use your participant link above to join. Please join 5 minutes before your scheduled time.\n\nPlease arrive 15 minutes early for check-in.\n\nImportant reminders:\n- Bring your ID and insurance card\n- Bring a list of current medications\n- Inform us of any changes to your health status\n\nIf you need to cancel or reschedule, please contact us at {{hospital_phone}} at least 24 hours in advance.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Team';

        $variables = [
            'patient_name' => "Patient's full name",
            'doctor_name' => "Doctor's name",
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'department' => 'Department name',
            'notes' => 'Additional appointment notes',
            'hospital_name' => 'Hospital name',
            'hospital_address' => 'Hospital address',
            'hospital_phone' => 'Hospital phone number',
            'online_consultation_section' => 'Online consultation block (participant link, platform, instructions)',
            'participant_link' => 'Video join link for patient (Whereby participant URL)',
            'meeting_link' => 'Same as participant_link',
            'join_meeting_url' => 'Same as participant_link',
        ];

        DB::table('email_templates')->where('name', 'appointment_confirmation')->update([
            'subject' => 'Appointment Confirmation - {{hospital_name}}',
            'body' => $body,
            'variables' => json_encode($variables),
            'description' => 'Sent to patients when their appointment is confirmed',
            'updated_at' => now(),
        ]);
    }
};
