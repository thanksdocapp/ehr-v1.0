<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert medical record email templates
        DB::table('email_templates')->insert([
            [
                'name' => 'medical_record_update',
                'subject' => 'Medical Record Update - {{hospital_name}}',
                'body' => 'Dear {{patient_name}},

Your medical record has been updated by {{doctor_name}} on {{update_date}}.

UPDATE DETAILS:
- Record Type: {{record_type}}
- Update Type: {{update_type}}
- Changes: {{changes_summary}}

CURRENT INFORMATION:
- Diagnosis: {{diagnosis}}
- Treatment Plan: {{treatment_plan}}
- Follow-up Required: {{follow_up_required}}

NEXT STEPS:
Please log into your patient portal to review your updated medical record: {{patient_portal_url}}

PRIVACY NOTICE:
{{privacy_note}}

If you have any questions about this update, please contact your healthcare provider.

CONTACT INFORMATION:
- Hospital Phone: {{contact_phone}}
- Patient Portal: {{patient_portal_url}}

Best regards,
{{hospital_name}} Medical Records Team',
                'description' => 'Sent to patients when their medical record is updated',
                'category' => 'medical',
                'status' => 'active',
                'variables' => '["patient_name","doctor_name","update_date","record_type","update_type","changes_summary","diagnosis","treatment_plan","follow_up_required","patient_portal_url","privacy_note","contact_phone","hospital_name"]',
                'sender_name' => 'Hospital Medical Records',
                'sender_email' => 'medical-records@hospital.com',
                'cc_emails' => null,
                'bcc_emails' => null,
                'attachments' => null,
                'metadata' => null,
                'last_used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'significant_diagnosis_notification',
                'subject' => 'Important Medical Update - {{diagnosis_title}} - {{hospital_name}}',
                'body' => 'Dear {{patient_name}},

Your healthcare provider, {{doctor_name}}, has documented an important medical update in your record on {{diagnosis_date}}.

DIAGNOSIS INFORMATION:
- Condition: {{diagnosis_title}}
- Urgency Level: {{urgency_level}}

EXPLANATION:
{{condition_explanation}}

TREATMENT OPTIONS:
{{treatment_options}}

FOLLOW-UP INSTRUCTIONS:
{{follow_up_instructions}}

NEXT STEPS:
{{next_steps}}

SUPPORT RESOURCES:
We understand this information may be concerning. Our care team is here to support you through this process.

CONTACT YOUR CARE TEAM:
- Your Doctor\'s Office: {{support_phone}}
- Patient Portal: {{patient_portal_url}}
- Schedule Appointments: {{appointment_scheduling_url}}

IMPORTANT REMINDERS:
- Please contact your healthcare provider if you have any questions
- Follow all treatment recommendations
- Attend all scheduled appointments
- Report any concerning symptoms immediately

This information is confidential and intended only for you. Please keep this communication secure.

With care and support,
{{hospital_name}} Medical Team

P.S. If you need emotional support or have concerns, our patient services team is available to help connect you with appropriate resources.',
                'description' => 'Sent to patients when they receive a significant diagnosis',
                'category' => 'medical',
                'status' => 'active',
                'variables' => '["patient_name","doctor_name","diagnosis_date","diagnosis_title","urgency_level","condition_explanation","treatment_options","follow_up_instructions","next_steps","support_phone","patient_portal_url","appointment_scheduling_url","hospital_name"]',
                'sender_name' => 'Hospital Medical Team',
                'sender_email' => 'medical-care@hospital.com',
                'cc_emails' => null,
                'bcc_emails' => null,
                'attachments' => null,
                'metadata' => null,
                'last_used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'name' => 'treatment_plan_update',
                'subject' => 'Treatment Plan Update - {{hospital_name}}',
                'body' => 'Dear {{patient_name}},

{{doctor_name}} has updated your treatment plan on {{update_date}}.

TREATMENT CHANGES:
{{treatment_changes}}

NEW MEDICATIONS:
{{new_medications}}

DISCONTINUED MEDICATIONS:
{{discontinued_medications}}

DOSAGE CHANGES:
{{dosage_changes}}

SPECIAL INSTRUCTIONS:
{{special_instructions}}

NEXT APPOINTMENT:
{{next_appointment}}

MONITORING REQUIREMENTS:
{{monitoring_requirements}}

IMPORTANT SAFETY INFORMATION:
{{emergency_instructions}}

PHARMACY INFORMATION:
If you have questions about your medications, please contact our pharmacy at {{pharmacy_phone}}.

FOLLOW-UP CARE:
Please ensure you follow all instructions carefully and attend your scheduled appointments.

ACCESS YOUR RECORDS:
View your complete treatment plan: {{patient_portal_url}}

If you have any questions or concerns about your treatment plan, please contact your healthcare provider immediately.

Best regards,
{{hospital_name}} Care Team

EMERGENCY NOTE: If you experience any severe side effects or emergency symptoms, contact emergency services immediately.',
                'description' => 'Sent to patients when their treatment plan is updated',
                'category' => 'medical',
                'status' => 'active',
                'variables' => '["patient_name","doctor_name","update_date","treatment_changes","new_medications","discontinued_medications","dosage_changes","special_instructions","next_appointment","monitoring_requirements","emergency_instructions","pharmacy_phone","patient_portal_url","hospital_name"]',
                'sender_name' => 'Hospital Care Team',
                'sender_email' => 'care-team@hospital.com',
                'cc_emails' => null,
                'bcc_emails' => null,
                'attachments' => null,
                'metadata' => null,
                'last_used_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->whereIn('name', [
            'medical_record_update',
            'significant_diagnosis_notification', 
            'treatment_plan_update'
        ])->delete();
    }
};
