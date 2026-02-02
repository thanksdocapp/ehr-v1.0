<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'appointment_confirmation',
                'subject' => 'Appointment Confirmation - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients when their appointment is confirmed',
                'body' => 'Dear {{patient_name}},\n\nYour appointment has been confirmed with the following details:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_address}}\n\nAdditional Notes:\n{{notes}}\n\nPlease arrive 15 minutes early for check-in.\n\nImportant reminders:\n- Bring your ID and insurance card\n- Bring a list of current medications\n- Inform us of any changes to your health status\n\nIf you need to cancel or reschedule, please contact us at {{hospital_phone}} at least 24 hours in advance.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'notes' => 'Additional appointment notes',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number',
                    'online_consultation_section' => 'Online consultation details (if applicable)'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'appointment_reminder',
                'subject' => 'Appointment Reminder - {{appointment_date}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'Sent to patients 24 hours before their appointment',
                'body' => 'Dear {{patient_name}},\n\nThis is a friendly reminder about your upcoming appointment:\n\nDoctor: {{doctor_name}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nDepartment: {{department}}\n{{online_consultation_section}}\nLocation: {{hospital_name}}\n{{hospital_address}}\n\nPlease remember to:\n✓ Arrive 15 minutes early\n✓ Bring your ID and insurance card\n✓ Bring your current medications list\n\nIf you need to cancel or reschedule, please call us at {{hospital_phone}} as soon as possible.\n\nThank you,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number',
                    'online_consultation_section' => 'Online consultation details (if applicable)'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'test_results_ready',
                'subject' => 'Your Test Results Are Ready - {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to patients when their test results are available',
                'body' => 'Dear {{patient_name}},\n\nYour test results from {{test_date}} are now ready for review.\n\nTest Type: {{test_type}}\nOrdered by: {{doctor_name}}\n\nTo access your results, please:\n1. Log into your patient portal at {{portal_url}}\n2. Navigate to "Lab Results"\n3. Review the results and any doctor notes\n\nIf you have any questions about your results, please contact your doctor\'s office at {{doctor_phone}} or schedule a follow-up appointment.\n\nImportant: These results are confidential and should only be accessed by you or your authorized healthcare proxy.\n\nThank you for choosing {{hospital_name}} for your healthcare needs.\n\nBest regards,\n{{hospital_name}} Laboratory Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'test_date' => 'Test date',
                    'test_type' => 'Type of test',
                    'doctor_name' => 'Ordering doctor\'s name',
                    'doctor_phone' => 'Doctor\'s phone number',
                    'portal_url' => 'Patient portal URL',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Laboratory',
                'sender_email' => 'lab@hospital.com'
            ],
            [
                'name' => 'patient_welcome',
                'subject' => 'Welcome to {{hospital_name}} - Your Health Journey Begins Here',
                'category' => 'welcome',
                'status' => 'active',
                'description' => 'Sent to new patients when they register',
                'body' => 'Dear {{patient_name}},\n\nWelcome to {{hospital_name}}! We are honored that you have chosen us for your healthcare needs.\n\nYour patient account has been successfully created with the following information:\n- Patient ID: {{patient_id}}\n- Registration Date: {{registration_date}}\n\nWhat\'s Next:\n1. Access your patient portal at {{portal_url}} using your email and the password you created\n2. Complete your medical history and insurance information\n3. Schedule your first appointment online or call {{hospital_phone}}\n\nOur Services:\n- 24/7 Emergency Care\n- Specialized Medical Departments\n- Online Appointment Scheduling\n- Patient Portal Access\n- Pharmacy Services\n\nImportant Information:\n- Hospital Address: {{hospital_address}}\n- Main Phone: {{hospital_phone}}\n- Emergency: {{emergency_phone}}\n- Patient Portal: {{portal_url}}\n\nOur dedicated staff is here to provide you with exceptional care. If you have any questions, please don\'t hesitate to contact us.\n\nWelcome to the {{hospital_name}} family!\n\nBest regards,\n{{hospital_name}} Patient Services Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID number',
                    'registration_date' => 'Registration date',
                    'portal_url' => 'Patient portal URL',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number',
                    'emergency_phone' => 'Emergency phone number'
                ],
                'sender_name' => 'Hospital Patient Services',
                'sender_email' => 'welcome@hospital.com'
            ],
            [
                'name' => 'discharge_instructions',
                'subject' => 'Discharge Instructions - {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to patients upon discharge with care instructions',
                'body' => 'Dear {{patient_name}},\n\nThank you for choosing {{hospital_name}} for your recent healthcare needs. You were discharged on {{discharge_date}} with the following instructions:\n\nDISCHARGE SUMMARY:\n- Admission Date: {{admission_date}}\n- Discharge Date: {{discharge_date}}\n- Attending Physician: {{doctor_name}}\n- Diagnosis: {{diagnosis}}\n\nMEDICATIONS:\n{{medications}}\n\nFOLLOW-UP CARE:\n{{follow_up_instructions}}\n\nACTIVITY RESTRICTIONS:\n{{activity_restrictions}}\n\nDIET INSTRUCTIONS:\n{{diet_instructions}}\n\nWARNING SIGNS - Contact us immediately if you experience:\n{{warning_signs}}\n\nFOLLOW-UP APPOINTMENTS:\n{{follow_up_appointments}}\n\nCONTACT INFORMATION:\n- Doctor\'s Office: {{doctor_phone}}\n- Hospital Main Line: {{hospital_phone}}\n- Emergency: {{emergency_phone}}\n\nIf you have any questions or concerns about your discharge instructions, please contact your doctor\'s office or our patient services team.\n\nWe wish you a speedy recovery!\n\nBest regards,\n{{hospital_name}} Care Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'discharge_date' => 'Discharge date',
                    'admission_date' => 'Admission date',
                    'doctor_name' => 'Attending physician\'s name',
                    'diagnosis' => 'Primary diagnosis',
                    'medications' => 'Prescribed medications',
                    'follow_up_instructions' => 'Follow-up care instructions',
                    'activity_restrictions' => 'Activity restrictions',
                    'diet_instructions' => 'Diet instructions',
                    'warning_signs' => 'Warning signs to watch for',
                    'follow_up_appointments' => 'Scheduled follow-up appointments',
                    'doctor_phone' => 'Doctor\'s office phone',
                    'hospital_name' => 'Hospital name',
                    'hospital_phone' => 'Hospital phone number',
                    'emergency_phone' => 'Emergency phone number'
                ],
                'sender_name' => 'Hospital Discharge Team',
                'sender_email' => 'discharge@hospital.com'
            ],
            [
                'name' => 'prescription_ready',
                'subject' => 'Your Prescription is Ready for Pickup - {{hospital_name}} Pharmacy',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to patients when their prescription is ready',
                'body' => 'Dear {{patient_name}},\n\nYour prescription is ready for pickup at {{hospital_name}} Pharmacy.\n\nPRESCRIPTION DETAILS:\n- Prescription Number: {{prescription_number}}\n- Medication: {{medication_name}}\n- Prescribed by: {{doctor_name}}\n- Date Filled: {{fill_date}}\n\nPICKUP INFORMATION:\n- Pharmacy Location: {{pharmacy_address}}\n- Pharmacy Hours: {{pharmacy_hours}}\n- Pharmacy Phone: {{pharmacy_phone}}\n\nIMPORTANT REMINDERS:\n- Bring a valid ID when picking up\n- Prescription will be held for {{hold_days}} days\n- If someone else is picking up, they need written authorization\n\nPAYMENT:\n- Insurance: {{insurance_info}}\n- Estimated Cost: {{estimated_cost}}\n\nIf you have any questions about your prescription or need to arrange delivery, please contact our pharmacy at {{pharmacy_phone}}.\n\nThank you for choosing {{hospital_name}} Pharmacy for your medication needs.\n\nBest regards,\n{{hospital_name}} Pharmacy Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'prescription_number' => 'Prescription number',
                    'medication_name' => 'Medication name',
                    'doctor_name' => 'Prescribing doctor\'s name',
                    'fill_date' => 'Date prescription was filled',
                    'pharmacy_address' => 'Pharmacy address',
                    'pharmacy_hours' => 'Pharmacy operating hours',
                    'pharmacy_phone' => 'Pharmacy phone number',
                    'hold_days' => 'Days prescription will be held',
                    'insurance_info' => 'Insurance information',
                    'estimated_cost' => 'Estimated cost',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Pharmacy',
                'sender_email' => 'pharmacy@hospital.com'
            ],
            [
                'name' => 'payment_reminder',
                'subject' => 'Payment Reminder - {{hospital_name}}',
                'category' => 'reminder',
                'status' => 'active',
                'description' => 'Sent to patients with outstanding balances',
                'body' => 'Dear {{patient_name}},\n\nThis is a friendly reminder that you have an outstanding balance with {{hospital_name}}.\n\nACCOUNT INFORMATION:\n- Account Number: {{account_number}}\n- Patient ID: {{patient_id}}\n- Service Date: {{service_date}}\n- Amount Due: {{amount_due}}\n- Due Date: {{due_date}}\n\nPAYMENT OPTIONS:\n1. Online: Visit {{payment_url}} to pay securely online\n2. Phone: Call {{billing_phone}} to pay by phone\n3. Mail: Send payment to:\n   {{hospital_name}} Billing Department\n   {{billing_address}}\n4. In Person: Visit our billing office during business hours\n\nPAYMENT METHODS ACCEPTED:\n- Credit/Debit Cards\n- Electronic Bank Transfer\n- Check or Money Order\n\nIf you have insurance that should cover this service, please contact our billing department at {{billing_phone}} immediately.\n\nIf you are experiencing financial hardship, we offer payment plans and financial assistance programs. Please contact our financial counselors at {{financial_counselor_phone}} to discuss your options.\n\nIf you have already made this payment, please disregard this notice.\n\nThank you for your prompt attention to this matter.\n\nBest regards,\n{{hospital_name}} Billing Department',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'account_number' => 'Account number',
                    'patient_id' => 'Patient ID',
                    'service_date' => 'Date of service',
                    'amount_due' => 'Amount due',
                    'due_date' => 'Payment due date',
                    'payment_url' => 'Online payment URL',
                    'billing_phone' => 'Billing department phone',
                    'billing_address' => 'Billing mailing address',
                    'financial_counselor_phone' => 'Financial counselor phone',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Billing',
                'sender_email' => 'billing@hospital.com'
            ],
            [
                'name' => 'emergency_contact_notification',
                'subject' => 'Emergency Notification - {{patient_name}} at {{hospital_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to emergency contacts when patient is admitted',
                'body' => 'Dear {{contact_name}},\n\nThis is to inform you that {{patient_name}} has been admitted to {{hospital_name}} and you are listed as their emergency contact.\n\nPATIENT INFORMATION:\n- Patient Name: {{patient_name}}\n- Admission Date: {{admission_date}}\n- Admission Time: {{admission_time}}\n- Department: {{department}}\n- Room Number: {{room_number}}\n- Attending Physician: {{doctor_name}}\n\nHOSPITAL INFORMATION:\n- Hospital: {{hospital_name}}\n- Address: {{hospital_address}}\n- Main Phone: {{hospital_phone}}\n- Visiting Hours: {{visiting_hours}}\n\nIMPORTANT INFORMATION:\n- Please bring valid ID when visiting\n- Check with the nurses\' station upon arrival\n- Follow all hospital safety protocols\n- Parking information: {{parking_info}}\n\nFor updates on the patient\'s condition, please contact the nurses\' station at {{nurses_station_phone}} or speak with the attending physician.\n\nIf you have any questions or concerns, please don\'t hesitate to contact our patient services team at {{patient_services_phone}}.\n\nThank you,\n{{hospital_name}} Patient Services Team',
                'variables' => [
                    'contact_name' => 'Emergency contact\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'admission_date' => 'Admission date',
                    'admission_time' => 'Admission time',
                    'department' => 'Department name',
                    'room_number' => 'Room number',
                    'doctor_name' => 'Attending physician\'s name',
                    'hospital_name' => 'Hospital name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone number',
                    'visiting_hours' => 'Visiting hours',
                    'parking_info' => 'Parking information',
                    'nurses_station_phone' => 'Nurses station phone',
                    'patient_services_phone' => 'Patient services phone'
                ],
                'sender_name' => 'Hospital Emergency Services',
                'sender_email' => 'emergency@hospital.com'
            ]
            ,
            [
                'name' => 'appointment_cancellation',
                'subject' => 'Appointment Cancelled - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients when their appointment is cancelled',
                'body' => 'Dear {{patient_name}},\n\nYour appointment has been cancelled.\n\nDETAILS:\n- Doctor: {{doctor_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n\nReason: {{cancellation_reason}}\n\nYou can reschedule your appointment at any time: {{reschedule_url}}\n\nIf this was unexpected, please contact us at {{hospital_phone}}.\n\nBest regards,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'cancellation_reason' => 'Reason for cancellation',
                    'reschedule_url' => 'Reschedule URL',
                    'hospital_phone' => 'Hospital phone number',
                    'hospital_name' => 'Hospital name'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'appointment_completion',
                'subject' => 'Appointment Summary - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients after appointment completion with summary',
                'body' => 'Dear {{patient_name}},\n\nThank you for visiting {{hospital_name}}. Here is a summary of your appointment:\n\n- Doctor: {{doctor_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n- Diagnosis: {{diagnosis}}\n- Prescription: {{prescription}}\n- Follow-up Instructions: {{follow_up_instructions}}\n- Next Appointment: {{next_appointment_date}}\n\nIf you have any questions, please contact us.\n\nBest regards,\n{{hospital_name}} Care Team',
                'variables' => [
                    'patient_name', 'doctor_name', 'appointment_date', 'appointment_time', 'department',
                    'diagnosis', 'prescription', 'follow_up_instructions', 'next_appointment_date', 'hospital_name'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'appointment_reschedule',
                'subject' => 'Appointment Rescheduled - {{hospital_name}}',
                'category' => 'appointment',
                'status' => 'active',
                'description' => 'Sent to patients when their appointment is rescheduled',
                'body' => 'Dear {{patient_name}},\n\nYour appointment has been rescheduled.\n\nPREVIOUS:\n- Date: {{old_date}}\n- Time: {{old_time}}\n\nNEW:\n- Date: {{new_date}}\n- Time: {{new_time}}\n- Doctor: {{doctor_name}}\n- Department: {{department}}\n\nReason: {{reschedule_reason}}\n\nIf the new time does not work for you, you can reschedule from your portal or contact us at {{hospital_phone}}.\n\nBest regards,\n{{hospital_name}} Team',
                'variables' => [
                    'patient_name','old_date','old_time','new_date','new_time','doctor_name','department','reschedule_reason','hospital_phone','hospital_name'
                ],
                'sender_name' => 'Hospital Appointments',
                'sender_email' => 'appointments@hospital.com'
            ],
            [
                'name' => 'patient_feedback_request',
                'subject' => 'How was your consultation? (1 minute) - {{hospital_name}}',
                'category' => 'feedback',
                'status' => 'active',
                'description' => 'Sent to patients 2 days after a completed consultation to request feedback',
                // Use double quotes so \n becomes real newlines in DB (prevents broken auto-linking in emails)
                'body' => "Dear {{patient_name}},\n\nThank you for your recent consultation with {{doctor_name}}.\n\nWe would really appreciate your feedback. It takes about 1 minute and helps us improve our service.\n\nPlease complete the feedback form here:\n{{feedback_url}}\n\nYou can choose to submit your feedback anonymously or with your details.\n\nThank you,\n{{hospital_name}} Team",
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'feedback_url' => 'Secure feedback form link',
                    'hospital_name' => 'Hospital name',
                ],
                'sender_name' => 'Patient Experience',
                'sender_email' => 'feedback@hospital.com'
            ],
            [
                'name' => 'doctor_appointment_cancelled',
                'subject' => 'Appointment Cancelled - {{patient_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to doctor when an appointment is cancelled',
                'body' => 'Dear Dr. {{doctor_name}},\n\nThe following appointment has been cancelled:\n\n- Patient: {{patient_name}}\n- Date: {{appointment_date}}\n- Time: {{appointment_time}}\n- Department: {{department}}\n\nReason: {{cancellation_reason}}\n\nRegards,\n{{hospital_name}}',
                'variables' => ['doctor_name','patient_name','appointment_date','appointment_time','department','cancellation_reason','hospital_name'],
                'sender_name' => 'Hospital Notifications',
                'sender_email' => 'no-reply@hospital.com'
            ],
            [
                'name' => 'doctor_appointment_rescheduled',
                'subject' => 'Appointment Rescheduled - {{patient_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to doctor when an appointment is rescheduled',
                'body' => 'Dear Dr. {{doctor_name}},\n\nAn appointment has been rescheduled:\n\n- Patient: {{patient_name}}\n- Old: {{old_date}} at {{old_time}}\n- New: {{new_date}} at {{new_time}}\n- Department: {{department}}\n\nReason: {{reschedule_reason}}\n\nRegards,\n{{hospital_name}}',
                'variables' => ['doctor_name','patient_name','old_date','old_time','new_date','new_time','department','reschedule_reason','hospital_name'],
                'sender_name' => 'Hospital Notifications',
                'sender_email' => 'no-reply@hospital.com'
            ],
            [
                'name' => 'doctor_new_appointment',
                'subject' => 'New Appointment Assigned - {{patient_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to doctor when a new appointment is assigned',
                'body' => 'Dear Dr. {{doctor_name}},\n\nA new appointment has been assigned to you:\n\nPatient: {{patient_name}}\nPhone: {{patient_phone}}\nDate: {{appointment_date}}\nTime: {{appointment_time}}\nType: {{appointment_type}}\n{{online_consultation_section}}\nNotes: {{notes}}\n\nView Appointment Details:\n{{appointment_url}}\n\nRegards,\n{{hospital_name}}',
                'variables' => ['doctor_name','patient_name','patient_phone','appointment_date','appointment_time','appointment_type','online_consultation_section','notes','appointment_url','hospital_name'],
                'sender_name' => 'Hospital Notifications',
                'sender_email' => 'no-reply@hospital.com'
            ],
            [
                'name' => 'staff_new_patient_registration',
                'subject' => 'New Patient Registered - {{patient_name}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to staff when a new patient is registered',
                'body' => 'Hello {{staff_name}},\n\nA new patient has been registered:\n\n- Name: {{patient_name}}\n- Patient ID: {{patient_id}}\n- Phone: {{patient_phone}}\n- Email: {{patient_email}}\n- Registered: {{registration_date}}\n\nView patient: {{patient_url}}\n\nRegards,\n{{hospital_name}}',
                'variables' => ['staff_name','patient_name','patient_id','patient_phone','patient_email','registration_date','patient_url','hospital_name'],
                'sender_name' => 'Hospital Notifications',
                'sender_email' => 'no-reply@hospital.com'
            ],
            [
                'name' => 'critical_care_notification',
                'subject' => 'CRITICAL CARE: {{patient_name}} - {{emergency_type}}',
                'category' => 'emergency',
                'status' => 'active',
                'description' => 'Sent to department heads/specialists for critical care cases',
                'body' => 'Dear {{department_head_name}},\n\nCritical care notification:\n\n- Patient: {{patient_name}} (ID: {{patient_id}})\n- Emergency: {{emergency_type}}\n- Priority: {{priority_level}}\n- Summary: {{condition_summary}}\n- Time: {{admission_time}}\n- Attending: {{attending_doctor}}\n- Department: {{department_name}}\n- Specialist Required: {{specialist_required}}\n\nProtocol: {{emergency_protocol}}\n\nView patient: {{patient_url}}\n\nRegards,\n{{hospital_name}}',
                'variables' => ['department_head_name','patient_name','patient_id','emergency_type','priority_level','condition_summary','admission_time','attending_doctor','department_name','specialist_required','emergency_protocol','patient_url','hospital_name'],
                'sender_name' => 'Hospital Emergency',
                'sender_email' => 'emergency@hospital.com'
            ],
            [
                'name' => 'doctor_critical_results',
                'subject' => 'CRITICAL RESULTS: {{patient_name}} - {{test_name}}',
                'category' => 'emergency',
                'status' => 'active',
                'description' => 'Sent to ordering doctor when critical lab results are available',
                'body' => 'Dear Dr. {{doctor_name}},\n\nCritical lab results require your immediate attention:\n\n- Patient: {{patient_name}}\n- Test: {{test_name}} ({{test_type}})\n- Test Date: {{test_date}}\n- Priority: {{priority}}\n- Status: {{status}}\n- Lab Technician: {{lab_technician}}\n\nNotes: {{notes}}\n\nView report: {{lab_report_url}}\n\nRegards,\n{{hospital_name}} Laboratory',
                'variables' => ['doctor_name','patient_name','test_name','test_type','test_date','priority','status','lab_technician','notes','lab_report_url','hospital_name'],
                'sender_name' => 'Hospital Laboratory',
                'sender_email' => 'lab@hospital.com'
            ],
            [
                'name' => 'contact_auto_reply',
                'subject' => 'We received your message - {{hospital_name}}',
                'category' => 'general',
                'status' => 'active',
                'description' => 'Auto reply to website contact form submission',
                'body' => 'Hello {{full_name}},\n\nThank you for contacting {{hospital_name}}. We have received your message with the subject "{{subject}}" and our team will get back to you shortly.\n\nYour message:\n{{message}}\n\nIf this is urgent, please call us at {{contact_phone}}.\n\nBest regards,\n{{hospital_name}} Support ({{contact_email}})',
                'variables' => ['full_name','subject','message','hospital_name','contact_email','contact_phone'],
                'sender_name' => 'Hospital Support',
                'sender_email' => 'support@hospital.com'
            ],
            [
                'name' => 'staff_new_contact_message',
                'subject' => 'New Contact Message: {{subject}}',
                'category' => 'notification',
                'status' => 'active',
                'description' => 'Sent to staff when a new website contact message is submitted',
                'body' => 'Hello {{staff_name}},\n\nA new contact message has been received:\n\n- Name: {{full_name}}\n- Email: {{email}}\n- Phone: {{phone}}\n- Subject: {{subject}}\n\nMessage:\n{{message}}\n\nView in inbox: {{inbox_url}}\n\nRegards,\n{{hospital_name}}',
                'variables' => ['staff_name','full_name','email','phone','subject','message','inbox_url','hospital_name'],
                'sender_name' => 'Hospital Notifications',
                'sender_email' => 'no-reply@hospital.com'
            ],
            [
                'name' => 'contact_reply',
                'subject' => '{{reply_subject}}',
                'category' => 'general',
                'status' => 'active',
                'description' => 'Reply email to a contact message sender',
                'body' => 'Hello {{full_name}},\n\n{{reply_message}}\n\nRegards,\n{{hospital_name}}\n\n(Regarding your original message: {{original_subject}})',
                'variables' => ['full_name','reply_subject','reply_message','original_subject','hospital_name'],
                'sender_name' => 'Hospital Support',
                'sender_email' => 'support@hospital.com'
            ],
            [
                'name' => 'doctor_welcome_epr',
                'subject' => 'Welcome to {{hospital_name}} – Your EPR Login Details',
                'category' => 'welcome',
                'status' => 'active',
                'description' => 'Sent to new doctors when their account is created (EPR login details)',
                'target_roles' => ['doctor'],
                'body' => '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Welcome – Your EPR Login Details</title></head><body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f5f7fa;"><table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;padding:20px;"><tr><td align="center"><table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.1);"><tr><td style="padding:30px;"><p style="color:#2d3748;font-size:16px;margin:0 0 20px 0;">Dear {{doctor_name}},</p><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">Welcome to {{hospital_name}}.</p><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">You have been successfully registered as a doctor on the EPR platform.</p><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">Please find your login details below to access the Electronic Patient Records platform:</p><div style="background-color:#f8f9fc;border-left:4px solid #1a202c;padding:15px;margin:20px 0;"><p style="margin:5px 0;color:#2d3748;font-size:14px;"><strong>Email:</strong> {{doctor_email}}</p><p style="margin:5px 0;color:#2d3748;font-size:14px;"><strong>Password:</strong> {{password}}</p></div><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">You can log in here:</p><p style="margin:0 0 20px 0;"><a href="{{login_url}}" style="color:#2563eb;text-decoration:none;">{{login_url}}</a></p><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">After logging in for the first time, please complete the following steps:</p><ol style="color:#4a5568;font-size:14px;line-height:1.8;margin:0 0 20px 0;padding-left:20px;"><li>Enable two-factor authentication (2FA). This is required before you can fully use the platform.</li><li>Set up and configure your services. These services must be created before they can be linked to your booking link and made available for patients.</li></ol><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">Please note that you can only reset your password while logged into the platform. There is no "Forgot Password" function for security reasons. If you forget your password and are unable to log in, please contact the support team or, in an emergency, call {{support_phone}}.</p><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">If you have any questions or need assistance, please contact us at <a href="mailto:{{support_email}}" style="color:#2563eb;">{{support_email}}</a>.</p><p style="color:#4a5568;font-size:14px;line-height:1.6;margin:0 0 20px 0;">Thank you for joining {{hospital_name}}.</p><p style="color:#2d3748;font-size:14px;margin:20px 0 0 0;">Kind regards,</p><p style="color:#2d3748;font-size:14px;margin:5px 0 0 0;">The {{hospital_name}} Team</p><p style="color:#718096;font-size:13px;margin:10px 0 0 0;"><a href="{{website_url}}" style="color:#2563eb;">{{website_url}}</a></p></td></tr></table></td></tr></table></body></html>',
                'variables' => [
                    'doctor_name' => 'Doctor\'s full name',
                    'doctor_email' => 'Doctor\'s email (login)',
                    'password' => 'Temporary password',
                    'login_url' => 'EPR login URL',
                    'support_email' => 'Support email',
                    'support_phone' => 'Support phone',
                    'website_url' => 'Hospital website URL',
                    'hospital_name' => 'Hospital/organisation name'
                ],
                'sender_name' => 'Hospital EPR',
                'sender_email' => 'noreply@hospital.com'
            ]
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}

