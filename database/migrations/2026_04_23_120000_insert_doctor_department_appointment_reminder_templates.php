<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Scheduled reminder emails for clinicians and department inbox (optional).
     */
    public function up(): void
    {
        $doctorBody = "Dear Dr. {{doctor_name}},\n\n"
            ."This is a reminder of an upcoming appointment.\n\n"
            .'Patient: {{patient_name}}'."\n"
            .'Phone: {{patient_phone}}'."\n"
            .'Email: {{patient_email}}'."\n"
            .'Date: {{appointment_date}}'."\n"
            .'Time: {{appointment_time}}'."\n"
            .'Department: {{department}}'."\n\n"
            .'{{online_consultation_section}}'
            ."Open in staff portal:\n{{appointment_url}}\n\n"
            .'{{hospital_name}} — {{hospital_phone}}';

        $doctorVariables = [
            'doctor_name' => 'Doctor first/last name (no Dr. prefix)',
            'patient_name' => 'Patient full name',
            'patient_phone' => 'Patient phone',
            'patient_email' => 'Patient email',
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'department' => 'Department name',
            'days_before' => 'Lead time context',
            'hospital_name' => 'Organisation name',
            'hospital_phone' => 'Main phone',
            'hospital_address' => 'Address',
            'appointment_id' => 'Internal appointment ID',
            'appointment_url' => 'Staff portal appointment URL',
            'online_consultation_section' => 'Video host link block',
            'host_meeting_link' => 'Host meeting URL',
            'participant_link' => 'Patient video link',
            'meeting_platform' => 'Platform name',
        ];

        DB::table('email_templates')->updateOrInsert(
            ['name' => 'doctor_appointment_reminder'],
            [
                'subject' => 'Appointment reminder (clinician) — {{appointment_date}} — {{patient_name}}',
                'body' => $doctorBody,
                'description' => 'Sent to the assigned doctor when automated appointment reminders run (schedule)',
                'category' => 'reminder',
                'status' => 'active',
                'variables' => json_encode($doctorVariables),
                'sender_name' => null,
                'sender_email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $deptBody = "This is an automated reminder of an upcoming appointment for {{department_name}}.\n\n"
            .'Patient: {{patient_name}}'."\n"
            .'Phone: {{patient_phone}}'."\n"
            .'Clinician: {{doctor_name}}'."\n"
            .'Date: {{appointment_date}}'."\n"
            .'Time: {{appointment_time}}'."\n\n"
            ."Staff portal:\n{{appointment_url}}\n\n"
            .'{{hospital_name}} — {{hospital_phone}}';

        $deptVariables = [
            'department_name' => 'Department / clinic name',
            'doctor_name' => 'Assigned doctor',
            'patient_name' => 'Patient full name',
            'patient_phone' => 'Patient phone',
            'appointment_date' => 'Appointment date',
            'appointment_time' => 'Appointment time',
            'days_before' => 'Lead time context',
            'hospital_name' => 'Organisation name',
            'hospital_phone' => 'Main phone',
            'appointment_id' => 'Internal appointment ID',
            'appointment_url' => 'Staff portal appointment URL',
        ];

        DB::table('email_templates')->updateOrInsert(
            ['name' => 'department_appointment_reminder'],
            [
                'subject' => 'Appointment reminder — {{department_name}} — {{appointment_date}} — {{patient_name}}',
                'body' => $deptBody,
                'description' => 'Sent to the department/clinic email when NOTIFY_APPOINTMENT_REMINDER_STAFF is enabled',
                'category' => 'reminder',
                'status' => 'active',
                'variables' => json_encode($deptVariables),
                'sender_name' => null,
                'sender_email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->whereIn('name', [
            'doctor_appointment_reminder',
            'department_appointment_reminder',
        ])->delete();
    }
};
