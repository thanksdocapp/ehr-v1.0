<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates appointment email templates to use explicit participant_link and host_meeting_link.
     */
    public function up(): void
    {
        $patientConfirmationBody = 'Dear {{patient_name}},\n\nYour appointment has been confirmed with the following details:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_address}}\n\nAdditional Notes:\n{{notes}}\n\nFor online video consultations, use your participant link above to join. Please join 5 minutes before your scheduled time.\n\nPlease arrive 15 minutes early for check-in.\n\nImportant reminders:\n- Bring your ID and insurance card\n- Bring a list of current medications\n- Inform us of any changes to your health status\n\nIf you need to cancel or reschedule, please contact us at {{hospital_phone}} at least 24 hours in advance.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Team';

        $patientConfirmationVariables = [
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

        $patientReminderBody = 'Dear {{patient_name}},\n\nThis is a friendly reminder about your upcoming appointment:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_name}}\n{{hospital_address}}\n\nFor online consultations, use your participant link above. Please join 5 minutes before your scheduled time.\n\nPlease remember to:\n✓ Arrive 15 minutes early\n✓ Bring your ID and insurance card\n✓ Bring your current medications list\n\nIf you need to cancel or reschedule, please call us at {{hospital_phone}} as soon as possible.\n\nThank you,\n{{hospital_name}} Team';

        $patientReminderVariables = [
            'patient_name' => "Patient's full name",
            'doctor_name' => "Doctor's name",
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'department' => 'Department name',
            'hospital_name' => 'Hospital name',
            'hospital_address' => 'Hospital address',
            'hospital_phone' => 'Hospital phone number',
            'online_consultation_section' => 'Online consultation block (participant link, platform)',
            'participant_link' => 'Video join link for patient',
        ];

        $doctorNewAppointmentBody = 'Dear Dr. {{doctor_name}},\n\nA new appointment has been assigned to you:\n\nPatient: {{patient_name}}\nPhone: {{patient_phone}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nType: {{appointment_type}}\n{{online_consultation_section}}\nNotes: {{notes}}\n\nFor online video consultations (e.g. Whereby):\n- Use your host link above to join with meeting controls\n- Share the participant link with the patient if needed\n- Join as host 5 minutes before your scheduled time\n\nView Appointment Details:\n{{appointment_url}}\n\nRegards,\n{{hospital_name}}';

        $doctorNewAppointmentVariables = [
            'doctor_name' => "Doctor's name",
            'patient_name' => "Patient's full name",
            'patient_phone' => "Patient's phone",
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'appointment_type' => 'Appointment type',
            'online_consultation_section' => 'Online consultation block (host link, participant link, platform)',
            'notes' => 'Appointment notes',
            'appointment_url' => 'Link to view appointment',
            'hospital_name' => 'Hospital name',
            'host_meeting_link' => 'Video host link for doctor (Whereby host URL with controls)',
            'participant_link' => 'Video participant link (for sharing with patient)',
            'meeting_link' => 'Same as host_meeting_link for doctor',
        ];

        DB::table('email_templates')->where('name', 'appointment_confirmation')->update([
            'body' => $patientConfirmationBody,
            'variables' => json_encode($patientConfirmationVariables),
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('name', 'appointment_reminder')->update([
            'body' => $patientReminderBody,
            'variables' => json_encode($patientReminderVariables),
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('name', 'doctor_new_appointment')->update([
            'body' => $doctorNewAppointmentBody,
            'variables' => json_encode($doctorNewAppointmentVariables),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous template bodies (without explicit link instructions)
        $patientConfirmationBody = 'Dear {{patient_name}},\n\nYour appointment has been confirmed with the following details:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_address}}\n\nAdditional Notes:\n{{notes}}\n\nPlease arrive 15 minutes early for check-in.\n\nImportant reminders:\n- Bring your ID and insurance card\n- Bring a list of current medications\n- Inform us of any changes to your health status\n\nIf you need to cancel or reschedule, please contact us at {{hospital_phone}} at least 24 hours in advance.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Team';

        $patientReminderBody = 'Dear {{patient_name}},\n\nThis is a friendly reminder about your upcoming appointment:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_name}}\n{{hospital_address}}\n\nPlease remember to:\n✓ Arrive 15 minutes early\n✓ Bring your ID and insurance card\n✓ Bring your current medications list\n\nIf you need to cancel or reschedule, please call us at {{hospital_phone}} as soon as possible.\n\nThank you,\n{{hospital_name}} Team';

        $doctorNewAppointmentBody = 'Dear Dr. {{doctor_name}},\n\nA new appointment has been assigned to you:\n\nPatient: {{patient_name}}\nPhone: {{patient_phone}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nType: {{appointment_type}}\n{{online_consultation_section}}\nNotes: {{notes}}\n\nView Appointment Details:\n{{appointment_url}}\n\nRegards,\n{{hospital_name}}';

        DB::table('email_templates')->where('name', 'appointment_confirmation')->update([
            'body' => $patientConfirmationBody,
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('name', 'appointment_reminder')->update([
            'body' => $patientReminderBody,
            'updated_at' => now(),
        ]);

        DB::table('email_templates')->where('name', 'doctor_new_appointment')->update([
            'body' => $doctorNewAppointmentBody,
            'updated_at' => now(),
        ]);
    }
};
