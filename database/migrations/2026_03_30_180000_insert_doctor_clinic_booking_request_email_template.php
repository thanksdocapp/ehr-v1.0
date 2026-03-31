<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Email to all clinic doctors when a patient submits a clinic (pool) booking request.
     */
    public function up(): void
    {
        $body = "Dear Dr. {{doctor_name}},\n\n"
            ."A new online booking request has been submitted at {{clinic_name}} and is waiting for a doctor to accept it.\n\n"
            ."Request reference: {{request_number}}\n"
            ."Patient: {{patient_name}}\n"
            ."Phone: {{patient_phone}}\n"
            ."Email: {{patient_email}}\n"
            ."Service: {{service_name}}\n"
            ."Preferred date: {{appointment_date}}\n"
            ."Preferred time: {{appointment_time}}\n"
            ."Consultation: {{consultation_type}}\n\n"
            ."Reason for booking:\n{{booking_notes}}\n\n"
            ."Open Clinic Requests in the staff portal to review and accept:\n{{accept_requests_url}}\n\n"
            ."Regards,\n{{hospital_name}}";

        $variables = [
            'doctor_name' => 'Doctor first/last name (no Dr. prefix)',
            'patient_name' => 'Patient full name',
            'patient_phone' => 'Patient phone',
            'patient_email' => 'Patient email',
            'clinic_name' => 'Clinic / department name',
            'service_name' => 'Booked service name',
            'appointment_date' => 'Requested appointment date',
            'appointment_time' => 'Requested appointment time',
            'consultation_type' => 'in person / online / telephone',
            'request_number' => 'Public reference e.g. CB…',
            'booking_notes' => 'Patient notes / reason (truncated)',
            'accept_requests_url' => 'Staff link to clinic booking requests inbox',
            'hospital_name' => 'Organisation name',
        ];

        DB::table('email_templates')->updateOrInsert(
            ['name' => 'doctor_clinic_booking_request'],
            [
                'subject' => 'New clinic booking request – {{clinic_name}} – {{appointment_date}}',
                'body' => $body,
                'description' => 'Sent to each active doctor in the clinic when a patient books via the public clinic link (pending acceptance)',
                'category' => 'notification',
                'status' => 'active',
                'variables' => json_encode($variables),
                'sender_name' => null,
                'sender_email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('email_templates')->where('name', 'doctor_clinic_booking_request')->delete();
    }
};
