-- Doctor clinic booking request email template (manual run, or use migration 2026_03_30_180000_insert_doctor_clinic_booking_request_email_template)
-- MySQL: single quote inside a string is escaped as ''

INSERT INTO `email_templates` (
    `name`,
    `subject`,
    `body`,
    `description`,
    `category`,
    `status`,
    `target_roles`,
    `variables`,
    `sender_name`,
    `sender_email`,
    `created_at`,
    `updated_at`
) VALUES (
    'doctor_clinic_booking_request',
    'New clinic booking request – {{clinic_name}} – {{appointment_date}}',
    'Dear Dr. {{doctor_name}},

A new online booking request has been submitted at {{clinic_name}} and is waiting for a doctor to accept it.

Request reference: {{request_number}}
Patient: {{patient_name}}
Phone: {{patient_phone}}
Email: {{patient_email}}
Service: {{service_name}}
Preferred date: {{appointment_date}}
Preferred time: {{appointment_time}}
Consultation: {{consultation_type}}

Reason for booking:
{{booking_notes}}

Open Clinic Requests in the staff portal to review and accept:
{{accept_requests_url}}

Regards,
{{hospital_name}}',
    'Sent to each active doctor in the clinic when a patient books via the public clinic link (pending acceptance)',
    'notification',
    'active',
    NULL,
    '{"doctor_name":"Doctor first/last name (no Dr. prefix)","patient_name":"Patient full name","patient_phone":"Patient phone","patient_email":"Patient email","clinic_name":"Clinic / department name","service_name":"Booked service name","appointment_date":"Requested appointment date","appointment_time":"Requested appointment time","consultation_type":"in person / online / telephone","request_number":"Public reference e.g. CB…","booking_notes":"Patient notes / reason (truncated)","accept_requests_url":"Staff link to clinic booking requests inbox","hospital_name":"Organisation name"}',
    NULL,
    NULL,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `subject` = VALUES(`subject`),
    `body` = VALUES(`body`),
    `description` = VALUES(`description`),
    `category` = VALUES(`category`),
    `status` = VALUES(`status`),
    `target_roles` = VALUES(`target_roles`),
    `variables` = VALUES(`variables`),
    `sender_name` = VALUES(`sender_name`),
    `sender_email` = VALUES(`sender_email`),
    `updated_at` = NOW();
