<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConsultationFormsSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user ID for created_by field
        $adminId = User::where('role', 'admin')->first()?->id ?? 1;

        $forms = [
            // 1. Patient Registration Form (CQC/NHS Compliant)
            [
                'name' => 'New Patient Registration Form',
                'type' => 'form',
                'content' => $this->getPatientRegistrationForm(),
            ],
            // 2. Medical History Questionnaire
            [
                'name' => 'Medical History Questionnaire',
                'type' => 'form',
                'content' => $this->getMedicalHistoryForm(),
            ],
            // 3. GDPR/Data Protection Consent
            [
                'name' => 'GDPR Data Protection Consent',
                'type' => 'form',
                'content' => $this->getGDPRConsentForm(),
            ],
            // 4. New Patient Health Screening
            [
                'name' => 'New Patient Health Screening',
                'type' => 'form',
                'content' => $this->getHealthScreeningForm(),
            ],
            // 5. Mental Health Assessment (PHQ-9)
            [
                'name' => 'Mental Health Assessment (PHQ-9)',
                'type' => 'form',
                'content' => $this->getPHQ9Form(),
            ],
            // 6. Anxiety Assessment (GAD-7)
            [
                'name' => 'Anxiety Assessment (GAD-7)',
                'type' => 'form',
                'content' => $this->getGAD7Form(),
            ],
            // 7. Smoking Cessation Assessment
            [
                'name' => 'Smoking Cessation Assessment',
                'type' => 'form',
                'content' => $this->getSmokingCessationForm(),
            ],
            // 8. Alcohol Use Assessment (AUDIT-C)
            [
                'name' => 'Alcohol Use Assessment (AUDIT-C)',
                'type' => 'form',
                'content' => $this->getAuditCForm(),
            ],
            // 9. Medication Review Form
            [
                'name' => 'Medication Review Form',
                'type' => 'form',
                'content' => $this->getMedicationReviewForm(),
            ],
            // 10. Pre-Procedure Consent Form
            [
                'name' => 'Pre-Procedure Consent Form',
                'type' => 'form',
                'content' => $this->getPreProcedureConsentForm(),
            ],
        ];

        foreach ($forms as $form) {
            Template::updateOrCreate(
                ['name' => $form['name']],
                [
                    'type' => $form['type'],
                    'content' => $form['content'],
                    'created_by' => $adminId,
                    'is_system' => true,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Created ' . count($forms) . ' consultation forms.');
    }

    private function getPatientRegistrationForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">NEW PATIENT REGISTRATION FORM</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Please complete all sections. This information is required for your medical records.</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Personal Details</h3>
        <p><strong>Full Name:</strong> {{patient_name}}</p>
        <p><strong>Date of Birth:</strong> {{patient_dob}}</p>
        <p><strong>NHS Number (if known):</strong> {{input:nhs_number:NHS Number:text}}</p>
        <p><strong>Gender:</strong> {{patient_gender}}</p>
        <p><strong>Preferred Pronouns:</strong> {{select:pronouns:Preferred Pronouns:He/Him,She/Her,They/Them,Other,Prefer not to say}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Contact Information</h3>
        <p><strong>Address:</strong> {{patient_address}}</p>
        <p><strong>Telephone:</strong> {{patient_phone}}</p>
        <p><strong>Email:</strong> {{patient_email}}</p>
        <p><strong>Preferred Contact Method:</strong> {{select:contact_method:Preferred Contact Method:Phone,Email,SMS,Letter}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Emergency Contact</h3>
        <p>{{input:emergency_name:Emergency Contact Name:text}}</p>
        <p>{{input:emergency_relationship:Relationship to Patient:text}}</p>
        <p>{{input:emergency_phone:Emergency Contact Phone:tel}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Previous GP Details</h3>
        <p>{{input:previous_gp_name:Previous GP Practice Name:text}}</p>
        <p>{{input:previous_gp_address:Previous GP Address:text}}</p>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="color: #856404;">Ethnic Origin (NHS Requirement)</h3>
        <p style="font-size: 9pt; color: #856404;">This information helps the NHS monitor health services. Completion is voluntary.</p>
        <p>{{select:ethnicity:Ethnic Origin:White British,White Irish,White Other,Mixed White/Black Caribbean,Mixed White/Black African,Mixed White/Asian,Mixed Other,Asian/British Indian,Asian/British Pakistani,Asian/British Bangladeshi,Asian/British Chinese,Asian Other,Black/British Caribbean,Black/British African,Black Other,Arab,Other Ethnic Group,Prefer not to say}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Accessibility & Communication Needs</h3>
        <p>{{checkbox:needs_interpreter:I require an interpreter}}</p>
        <p>{{input:interpreter_language:If yes, which language?:text}}</p>
        <p>{{checkbox:needs_large_print:I require large print communications}}</p>
        <p>{{checkbox:needs_hearing_loop:I require hearing loop/assistance}}</p>
        <p>{{checkbox:needs_wheelchair:I require wheelchair access}}</p>
        <p>{{textarea:other_accessibility:Other accessibility requirements}}</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Declaration</h3>
        <p style="font-size: 10pt;">I confirm that the information provided is accurate to the best of my knowledge. I understand that my medical records from my previous GP will be requested.</p>
        <p>{{checkbox:declaration_confirm:I confirm the above information is correct}}</p>
        <p>{{checkbox:records_consent:I consent to my medical records being requested from my previous GP}}</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9pt; color: #7f8c8d;">
        <p><strong>For Office Use Only:</strong></p>
        <p>Registration Date: {{current_date}} | Processed by: {{doctor_name}}</p>
    </div>
</div>
HTML;
    }

    private function getMedicalHistoryForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">MEDICAL HISTORY QUESTIONNAIRE</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Confidential - Please complete all sections honestly and thoroughly</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Date:</strong> {{current_date}}</p>
    </div>

    <div style="background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f44336;">
        <h3 style="color: #c62828;">Allergies & Adverse Reactions</h3>
        <p>{{checkbox:has_allergies:I have known allergies}}</p>
        <p>{{textarea:allergy_details:Please list all allergies and reactions (medications, foods, environmental)}}</p>
        <p>{{checkbox:no_known_allergies:I have NO known allergies (NKDA)}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Current Medications</h3>
        <p style="font-size: 10pt; color: #7f8c8d;">Include all prescription medications, over-the-counter medicines, vitamins, and supplements</p>
        <p>{{textarea:current_medications:List all current medications with doses and frequency}}</p>
        <p>{{checkbox:no_medications:I am not currently taking any medications}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Medical Conditions</h3>
        <p style="font-size: 10pt; color: #7f8c8d;">Please tick all conditions you have been diagnosed with:</p>

        <div style="columns: 2; column-gap: 20px;">
            <p>{{checkbox:condition_diabetes:Diabetes}}</p>
            <p>{{checkbox:condition_hypertension:High Blood Pressure}}</p>
            <p>{{checkbox:condition_heart_disease:Heart Disease}}</p>
            <p>{{checkbox:condition_stroke:Stroke/TIA}}</p>
            <p>{{checkbox:condition_asthma:Asthma}}</p>
            <p>{{checkbox:condition_copd:COPD}}</p>
            <p>{{checkbox:condition_epilepsy:Epilepsy}}</p>
            <p>{{checkbox:condition_thyroid:Thyroid Problems}}</p>
            <p>{{checkbox:condition_kidney:Kidney Disease}}</p>
            <p>{{checkbox:condition_liver:Liver Disease}}</p>
            <p>{{checkbox:condition_cancer:Cancer (current or previous)}}</p>
            <p>{{checkbox:condition_arthritis:Arthritis}}</p>
            <p>{{checkbox:condition_mental_health:Mental Health Condition}}</p>
            <p>{{checkbox:condition_autoimmune:Autoimmune Disease}}</p>
        </div>

        <p>{{textarea:other_conditions:Other medical conditions not listed above}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Surgical History</h3>
        <p>{{textarea:surgical_history:Please list any previous surgeries or procedures with approximate dates}}</p>
        <p>{{checkbox:no_surgical_history:I have not had any previous surgeries}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Family Medical History</h3>
        <p style="font-size: 10pt; color: #7f8c8d;">Please indicate if any immediate family members (parents, siblings, grandparents) have/had:</p>
        <p>{{checkbox:family_heart_disease:Heart Disease}}</p>
        <p>{{checkbox:family_stroke:Stroke}}</p>
        <p>{{checkbox:family_diabetes:Diabetes}}</p>
        <p>{{checkbox:family_cancer:Cancer}} {{input:family_cancer_type:If yes, what type?:text}}</p>
        <p>{{checkbox:family_mental_health:Mental Health Conditions}}</p>
        <p>{{textarea:family_other:Other significant family medical history}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Lifestyle</h3>

        <p><strong>Smoking Status:</strong></p>
        <p>{{select:smoking_status:Smoking Status:Never smoked,Ex-smoker,Current smoker - occasional,Current smoker - daily}}</p>
        <p>{{input:smoking_amount:If smoker, how many per day?:number}}</p>

        <p><strong>Alcohol Consumption:</strong></p>
        <p>{{select:alcohol_status:Alcohol Consumption:None,Occasional (1-2 units/week),Moderate (3-14 units/week),Heavy (14+ units/week)}}</p>

        <p><strong>Exercise:</strong></p>
        <p>{{select:exercise_frequency:Exercise Frequency:None,Light (1-2 times/week),Moderate (3-4 times/week),Regular (5+ times/week)}}</p>

        <p><strong>Occupation:</strong> {{input:occupation:Current Occupation:text}}</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Declaration</h3>
        <p style="font-size: 10pt;">I confirm that the information provided is accurate and complete to the best of my knowledge. I understand the importance of providing accurate medical history for my healthcare.</p>
        <p>{{checkbox:history_declaration:I confirm this information is accurate and complete}}</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>
</div>
HTML;
    }

    private function getGDPRConsentForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">DATA PROTECTION & PRIVACY CONSENT</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">In accordance with the UK General Data Protection Regulation (UK GDPR) and Data Protection Act 2018</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
    </div>

    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <h3 style="color: #1565c0;">Why We Need Your Information</h3>
        <p style="font-size: 10pt;">As a healthcare provider registered with the Care Quality Commission (CQC), we are required to maintain accurate medical records. Your personal and health information is processed under Article 6(1)(e) and Article 9(2)(h) of the UK GDPR for healthcare purposes.</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">How We Use Your Information</h3>
        <p style="font-size: 10pt;">Your information may be used for:</p>
        <ul style="font-size: 10pt;">
            <li>Direct patient care and treatment</li>
            <li>Referrals to NHS and private healthcare providers</li>
            <li>Prescription services</li>
            <li>Laboratory and diagnostic services</li>
            <li>Statutory reporting requirements</li>
            <li>Clinical audit and quality improvement</li>
        </ul>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Your Consent Choices</h3>

        <div style="margin-bottom: 15px; padding: 10px; background: #fff; border-radius: 5px;">
            <p><strong>Electronic Prescriptions (EPS)</strong></p>
            <p style="font-size: 10pt;">Allows prescriptions to be sent electronically to your nominated pharmacy.</p>
            <p>{{checkbox:consent_eps:I consent to my prescriptions being sent electronically}}</p>
        </div>

        <div style="margin-bottom: 15px; padding: 10px; background: #fff; border-radius: 5px;">
            <p><strong>Summary Care Record (SCR)</strong></p>
            <p style="font-size: 10pt;">An NHS electronic record containing key health information accessible to authorised NHS staff.</p>
            <p>{{checkbox:consent_scr:I consent to an enhanced Summary Care Record being created}}</p>
        </div>

        <div style="margin-bottom: 15px; padding: 10px; background: #fff; border-radius: 5px;">
            <p><strong>SMS/Email Communications</strong></p>
            <p style="font-size: 10pt;">Appointment reminders, test results notifications, and health information.</p>
            <p>{{checkbox:consent_sms:I consent to receiving SMS messages}}</p>
            <p>{{checkbox:consent_email:I consent to receiving emails about my healthcare}}</p>
        </div>

        <div style="margin-bottom: 15px; padding: 10px; background: #fff; border-radius: 5px;">
            <p><strong>Research & Statistics</strong></p>
            <p style="font-size: 10pt;">Anonymised data may be used for medical research and NHS statistics.</p>
            <p>{{checkbox:consent_research:I consent to my anonymised data being used for research}}</p>
        </div>

        <div style="margin-bottom: 15px; padding: 10px; background: #fff; border-radius: 5px;">
            <p><strong>Third Party Sharing</strong></p>
            <p style="font-size: 10pt;">Sharing information with family members or carers about your care.</p>
            <p>{{checkbox:consent_family:I consent to information being shared with my named contacts}}</p>
            <p>{{input:named_contacts:Named contact(s) who can receive information:text}}</p>
        </div>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="color: #856404;">Your Rights</h3>
        <p style="font-size: 10pt;">Under the UK GDPR, you have the right to:</p>
        <ul style="font-size: 10pt;">
            <li>Access your personal data (Subject Access Request)</li>
            <li>Rectify inaccurate personal data</li>
            <li>Request erasure of your data (subject to legal requirements)</li>
            <li>Restrict processing of your data</li>
            <li>Object to processing</li>
            <li>Withdraw consent at any time</li>
        </ul>
        <p style="font-size: 10pt;">To exercise these rights or for any data protection queries, please contact our Data Protection Officer.</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Consent Declaration</h3>
        <p style="font-size: 10pt;">I have read and understood this privacy notice. I understand how my personal and health information will be used and my rights under data protection law.</p>
        <p>{{checkbox:gdpr_declaration:I acknowledge I have read and understood this privacy notice}}</p>
        <p>{{checkbox:gdpr_consent:I consent to my data being processed as described above}}</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9pt; color: #7f8c8d;">
        <p>Data Controller: {{clinic_name}} | ICO Registration: [Registration Number]</p>
        <p>For more information, please see our full Privacy Policy available at reception or on our website.</p>
    </div>
</div>
HTML;
    }

    private function getHealthScreeningForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">NEW PATIENT HEALTH SCREENING</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">NHS Health Check Questionnaire</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Age:</strong> {{patient_age}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Physical Measurements</h3>
        <p><em style="font-size: 10pt; color: #7f8c8d;">To be completed by healthcare professional</em></p>
        <p>Height: {{input:height_cm:Height (cm):number}} cm</p>
        <p>Weight: {{input:weight_kg:Weight (kg):number}} kg</p>
        <p>Blood Pressure: {{input:bp_systolic:Systolic:number}}/{{input:bp_diastolic:Diastolic:number}} mmHg</p>
        <p>Pulse: {{input:pulse:Pulse (bpm):number}} bpm</p>
        <p>Waist Circumference: {{input:waist_cm:Waist (cm):number}} cm</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Cardiovascular Risk Assessment</h3>

        <p><strong>Do you have a family history of heart disease or stroke before age 60?</strong></p>
        <p>{{select:family_cvd:Family history of CVD:No,Yes - parent,Yes - sibling,Yes - both}}</p>

        <p><strong>Have you ever been told you have high cholesterol?</strong></p>
        <p>{{select:high_cholesterol:High Cholesterol:No,Yes - diet controlled,Yes - on medication,Unknown}}</p>

        <p><strong>Do you have diabetes or pre-diabetes?</strong></p>
        <p>{{select:diabetes_status:Diabetes Status:No,Pre-diabetes,Type 1 Diabetes,Type 2 Diabetes}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Lifestyle Assessment</h3>

        <p><strong>Smoking:</strong></p>
        <p>{{select:smoking:Smoking Status:Never smoked,Ex-smoker (quit 12+ months ago),Ex-smoker (quit less than 12 months),Current smoker}}</p>
        <p>{{input:cigarettes_day:If current smoker, cigarettes per day:number}}</p>

        <p><strong>Alcohol:</strong></p>
        <p>{{select:alcohol_frequency:How often do you drink alcohol?:Never,Monthly or less,2-4 times per month,2-3 times per week,4+ times per week}}</p>
        <p>{{input:alcohol_units:Typical units per drinking day:number}}</p>

        <p><strong>Physical Activity:</strong></p>
        <p>{{select:activity_level:How many days per week do you do 30+ mins of moderate activity?:0,1-2,3-4,5+}}</p>

        <p><strong>Diet:</strong></p>
        <p>{{select:fruit_veg:How many portions of fruit/vegetables do you eat daily?:0-1,2-3,4-5,5+}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Current Symptoms</h3>
        <p style="font-size: 10pt; color: #7f8c8d;">Please indicate if you currently experience any of the following:</p>

        <p>{{checkbox:symptom_chest_pain:Chest pain or discomfort}}</p>
        <p>{{checkbox:symptom_breathless:Shortness of breath}}</p>
        <p>{{checkbox:symptom_palpitations:Palpitations or irregular heartbeat}}</p>
        <p>{{checkbox:symptom_dizziness:Dizziness or fainting}}</p>
        <p>{{checkbox:symptom_fatigue:Unusual fatigue}}</p>
        <p>{{checkbox:symptom_weight_change:Unexplained weight change}}</p>
        <p>{{checkbox:symptom_urinary:Urinary problems}}</p>
        <p>{{checkbox:symptom_bowel:Change in bowel habits}}</p>
        <p>{{textarea:other_symptoms:Please describe any other symptoms or concerns}}</p>
    </div>

    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <h3 style="color: #1565c0;">Screening Tests Due</h3>
        <p style="font-size: 10pt;">Based on your age and gender, you may be due for:</p>
        <p>{{checkbox:screening_bp:Blood Pressure Check}}</p>
        <p>{{checkbox:screening_cholesterol:Cholesterol Test}}</p>
        <p>{{checkbox:screening_diabetes:Diabetes Screening}}</p>
        <p>{{checkbox:screening_cervical:Cervical Screening (if applicable)}}</p>
        <p>{{checkbox:screening_breast:Breast Screening (if applicable)}}</p>
        <p>{{checkbox:screening_bowel:Bowel Cancer Screening (if 60+)}}</p>
        <p>{{checkbox:screening_aaa:AAA Screening (if male 65+)}}</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p><strong>Healthcare Professional:</strong> {{doctor_name}}</p>
        <p><strong>Assessment Date:</strong> {{current_date}}</p>
        <p><strong>Actions/Recommendations:</strong></p>
        <p>{{textarea:hcp_notes:Healthcare Professional Notes}}</p>
    </div>
</div>
HTML;
    }

    private function getPHQ9Form(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">PATIENT HEALTH QUESTIONNAIRE (PHQ-9)</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Depression Screening Tool</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Date:</strong> {{current_date}}</p>
    </div>

    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <p style="font-size: 10pt;"><strong>Instructions:</strong> Over the <u>last 2 weeks</u>, how often have you been bothered by any of the following problems?</p>
        <p style="font-size: 10pt;">0 = Not at all | 1 = Several days | 2 = More than half the days | 3 = Nearly every day</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #e0e0e0;">
                <th style="padding: 10px; text-align: left;">Question</th>
                <th style="padding: 10px; text-align: center;">Score</th>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">1. Little interest or pleasure in doing things</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q1:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">2. Feeling down, depressed, or hopeless</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q2:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">3. Trouble falling or staying asleep, or sleeping too much</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q3:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">4. Feeling tired or having little energy</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q4:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">5. Poor appetite or overeating</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q5:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">6. Feeling bad about yourself — or that you are a failure or have let yourself or your family down</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q6:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">7. Trouble concentrating on things, such as reading the newspaper or watching television</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q7:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">8. Moving or speaking so slowly that other people could have noticed? Or the opposite — being so fidgety or restless that you have been moving around a lot more than usual</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q8:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr style="background: #ffebee;">
                <td style="padding: 10px; border-bottom: 1px solid #ddd;"><strong>9. Thoughts that you would be better off dead or of hurting yourself in some way</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:phq9_q9:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
        </table>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong>If you checked off any problems, how difficult have these problems made it for you to do your work, take care of things at home, or get along with other people?</strong></p>
        <p>{{select:phq9_difficulty:Functional Difficulty:Not difficult at all,Somewhat difficult,Very difficult,Extremely difficult}}</p>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="color: #856404;">PHQ-9 Scoring Guide</h3>
        <p style="font-size: 10pt;">
            <strong>0-4:</strong> Minimal depression<br>
            <strong>5-9:</strong> Mild depression<br>
            <strong>10-14:</strong> Moderate depression<br>
            <strong>15-19:</strong> Moderately severe depression<br>
            <strong>20-27:</strong> Severe depression
        </p>
    </div>

    <div style="background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f44336;">
        <h3 style="color: #c62828;">Important Safety Note</h3>
        <p style="font-size: 10pt;">If you have had thoughts of hurting yourself or suicide, please inform your healthcare provider immediately. If you are in crisis, please contact:</p>
        <ul style="font-size: 10pt;">
            <li>NHS 111 - Option 2 for mental health crisis</li>
            <li>Samaritans: 116 123 (24 hours)</li>
            <li>In emergency: 999</li>
        </ul>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p><strong>Healthcare Professional:</strong> {{doctor_name}}</p>
        <p><strong>Total PHQ-9 Score:</strong> {{input:phq9_total:Total Score:number}}/27</p>
        <p><strong>Clinical Assessment:</strong></p>
        <p>{{textarea:clinician_notes:Clinical Notes and Plan}}</p>
    </div>
</div>
HTML;
    }

    private function getGAD7Form(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">GENERALISED ANXIETY DISORDER ASSESSMENT (GAD-7)</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Anxiety Screening Tool</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Date:</strong> {{current_date}}</p>
    </div>

    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <p style="font-size: 10pt;"><strong>Instructions:</strong> Over the <u>last 2 weeks</u>, how often have you been bothered by the following problems?</p>
        <p style="font-size: 10pt;">0 = Not at all | 1 = Several days | 2 = More than half the days | 3 = Nearly every day</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #e0e0e0;">
                <th style="padding: 10px; text-align: left;">Question</th>
                <th style="padding: 10px; text-align: center;">Score</th>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">1. Feeling nervous, anxious, or on edge</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q1:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">2. Not being able to stop or control worrying</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q2:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">3. Worrying too much about different things</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q3:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">4. Trouble relaxing</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q4:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">5. Being so restless that it's hard to sit still</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q5:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">6. Becoming easily annoyed or irritable</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q6:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">7. Feeling afraid as if something awful might happen</td>
                <td style="padding: 10px; border-bottom: 1px solid #ddd;">{{select:gad7_q7:Score:0 - Not at all,1 - Several days,2 - More than half the days,3 - Nearly every day}}</td>
            </tr>
        </table>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong>If you checked off any problems, how difficult have these made it for you to do your work, take care of things at home, or get along with other people?</strong></p>
        <p>{{select:gad7_difficulty:Functional Difficulty:Not difficult at all,Somewhat difficult,Very difficult,Extremely difficult}}</p>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="color: #856404;">GAD-7 Scoring Guide</h3>
        <p style="font-size: 10pt;">
            <strong>0-4:</strong> Minimal anxiety<br>
            <strong>5-9:</strong> Mild anxiety<br>
            <strong>10-14:</strong> Moderate anxiety<br>
            <strong>15-21:</strong> Severe anxiety
        </p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p><strong>Healthcare Professional:</strong> {{doctor_name}}</p>
        <p><strong>Total GAD-7 Score:</strong> {{input:gad7_total:Total Score:number}}/21</p>
        <p><strong>Clinical Assessment:</strong></p>
        <p>{{textarea:clinician_notes:Clinical Notes and Plan}}</p>
    </div>
</div>
HTML;
    }

    private function getSmokingCessationForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">SMOKING CESSATION ASSESSMENT</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">NHS Stop Smoking Service Assessment</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Date:</strong> {{current_date}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Smoking History</h3>

        <p><strong>What do you smoke?</strong></p>
        <p>{{checkbox:smoke_cigarettes:Cigarettes}}</p>
        <p>{{checkbox:smoke_rollups:Roll-ups}}</p>
        <p>{{checkbox:smoke_cigars:Cigars}}</p>
        <p>{{checkbox:smoke_pipe:Pipe}}</p>
        <p>{{checkbox:smoke_vape:E-cigarettes/Vape}}</p>

        <p><strong>How many do you smoke per day?</strong> {{input:cigarettes_per_day:Cigarettes per day:number}}</p>
        <p><strong>At what age did you start smoking?</strong> {{input:age_started:Age started:number}}</p>
        <p><strong>How many years have you been smoking?</strong> {{input:years_smoking:Years smoking:number}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Fagerström Test for Nicotine Dependence</h3>

        <p><strong>1. How soon after you wake up do you smoke your first cigarette?</strong></p>
        <p>{{select:fagerstrom_q1:Time to first cigarette:Within 5 minutes (3),6-30 minutes (2),31-60 minutes (1),After 60 minutes (0)}}</p>

        <p><strong>2. Do you find it difficult to refrain from smoking in places where it is forbidden?</strong></p>
        <p>{{select:fagerstrom_q2:Difficulty refraining:Yes (1),No (0)}}</p>

        <p><strong>3. Which cigarette would you hate most to give up?</strong></p>
        <p>{{select:fagerstrom_q3:Most hated to give up:The first one in the morning (1),Any other (0)}}</p>

        <p><strong>4. How many cigarettes per day do you smoke?</strong></p>
        <p>{{select:fagerstrom_q4:Cigarettes per day:10 or less (0),11-20 (1),21-30 (2),31 or more (3)}}</p>

        <p><strong>5. Do you smoke more frequently during the first hours after waking than during the rest of the day?</strong></p>
        <p>{{select:fagerstrom_q5:Smoke more in morning:Yes (1),No (0)}}</p>

        <p><strong>6. Do you smoke if you are so ill that you are in bed most of the day?</strong></p>
        <p>{{select:fagerstrom_q6:Smoke when ill:Yes (1),No (0)}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Motivation & Readiness</h3>

        <p><strong>Why do you want to stop smoking?</strong></p>
        <p>{{checkbox:reason_health:Health reasons}}</p>
        <p>{{checkbox:reason_cost:Cost/Financial}}</p>
        <p>{{checkbox:reason_family:Family/Children}}</p>
        <p>{{checkbox:reason_fitness:Fitness}}</p>
        <p>{{checkbox:reason_appearance:Appearance/Smell}}</p>
        <p>{{textarea:other_reasons:Other reasons}}</p>

        <p><strong>On a scale of 1-10, how motivated are you to quit?</strong></p>
        <p>{{select:motivation_scale:Motivation (1-10):1,2,3,4,5,6,7,8,9,10}}</p>

        <p><strong>On a scale of 1-10, how confident are you that you can quit?</strong></p>
        <p>{{select:confidence_scale:Confidence (1-10):1,2,3,4,5,6,7,8,9,10}}</p>

        <p><strong>Have you tried to quit before?</strong></p>
        <p>{{select:previous_attempts:Previous Attempts:No,Yes - once,Yes - 2-3 times,Yes - more than 3 times}}</p>
        <p>{{textarea:previous_methods:What methods did you try and what happened?}}</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Treatment Options Discussed</h3>
        <p>{{checkbox:nrt_patch:Nicotine patches}}</p>
        <p>{{checkbox:nrt_gum:Nicotine gum}}</p>
        <p>{{checkbox:nrt_inhalator:Nicotine inhalator}}</p>
        <p>{{checkbox:nrt_spray:Nicotine spray}}</p>
        <p>{{checkbox:varenicline:Varenicline (Champix)}}</p>
        <p>{{checkbox:bupropion:Bupropion (Zyban)}}</p>
        <p>{{checkbox:ecig:E-cigarette switch}}</p>
        <p>{{checkbox:behavioural:Behavioural support only}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p><strong>Agreed Quit Date:</strong> {{input:quit_date:Quit Date:date}}</p>
        <p><strong>CO Reading (if available):</strong> {{input:co_reading:CO Reading (ppm):number}} ppm</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p><strong>Healthcare Professional:</strong> {{doctor_name}}</p>
        <p><strong>Fagerström Score:</strong> {{input:fagerstrom_total:Total Score:number}}/10</p>
        <p><strong>Plan:</strong></p>
        <p>{{textarea:clinician_notes:Treatment Plan and Notes}}</p>
    </div>
</div>
HTML;
    }

    private function getAuditCForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">ALCOHOL USE ASSESSMENT (AUDIT-C)</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Alcohol Use Disorders Identification Test</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Date:</strong> {{current_date}}</p>
    </div>

    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <p style="font-size: 10pt;"><strong>One unit of alcohol = half pint of beer/lager, single measure of spirits, small glass of wine</strong></p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">AUDIT-C Questions</h3>

        <p><strong>1. How often do you have a drink containing alcohol?</strong></p>
        <p>{{select:audit_q1:Frequency:Never (0),Monthly or less (1),2-4 times per month (2),2-3 times per week (3),4+ times per week (4)}}</p>

        <p><strong>2. How many units of alcohol do you drink on a typical day when you are drinking?</strong></p>
        <p>{{select:audit_q2:Units per day:1-2 (0),3-4 (1),5-6 (2),7-9 (3),10+ (4)}}</p>

        <p><strong>3. How often have you had 6 or more units if female, or 8 or more if male, on a single occasion in the last year?</strong></p>
        <p>{{select:audit_q3:Binge frequency:Never (0),Less than monthly (1),Monthly (2),Weekly (3),Daily or almost daily (4)}}</p>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="color: #856404;">AUDIT-C Scoring</h3>
        <p style="font-size: 10pt;">
            <strong>Score 0-4:</strong> Lower risk<br>
            <strong>Score 5+ (men) / 4+ (women):</strong> Increasing risk - consider full AUDIT<br>
            <strong>Score 8+:</strong> Higher risk - refer for specialist assessment
        </p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Additional Assessment</h3>

        <p><strong>Have you ever felt you should cut down on your drinking?</strong></p>
        <p>{{select:cage_cutdown:Cut down:Yes,No}}</p>

        <p><strong>Have people annoyed you by criticising your drinking?</strong></p>
        <p>{{select:cage_annoyed:Annoyed:Yes,No}}</p>

        <p><strong>Have you ever felt guilty about your drinking?</strong></p>
        <p>{{select:cage_guilty:Guilty:Yes,No}}</p>

        <p><strong>Have you ever had a drink first thing in the morning (eye-opener)?</strong></p>
        <p>{{select:cage_eyeopener:Eye-opener:Yes,No}}</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Brief Advice Given</h3>
        <p>{{checkbox:advice_guidelines:Discussed UK Chief Medical Officers' drinking guidelines (14 units/week)}}</p>
        <p>{{checkbox:advice_health_risks:Discussed health risks of alcohol}}</p>
        <p>{{checkbox:advice_reduction:Discussed strategies for reducing intake}}</p>
        <p>{{checkbox:advice_support:Provided information on support services}}</p>
        <p>{{checkbox:advice_leaflet:Provided patient information leaflet}}</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p><strong>Healthcare Professional:</strong> {{doctor_name}}</p>
        <p><strong>AUDIT-C Score:</strong> {{input:auditc_total:Total Score:number}}/12</p>
        <p><strong>Plan:</strong></p>
        <p>{{textarea:clinician_notes:Clinical Notes and Plan}}</p>
    </div>
</div>
HTML;
    }

    private function getMedicationReviewForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">MEDICATION REVIEW FORM</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Structured Medication Review (SMR) - NHS Requirement</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>Date:</strong> {{current_date}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Current Medications</h3>
        <p style="font-size: 10pt; color: #7f8c8d;">Please list all medications you are currently taking, including those prescribed by other doctors, over-the-counter medicines, and supplements.</p>
        <p>{{textarea:current_medications:List all current medications with doses and frequency}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Medication Adherence</h3>

        <p><strong>Do you take your medications as prescribed?</strong></p>
        <p>{{select:adherence:Adherence:Always,Usually,Sometimes,Rarely}}</p>

        <p><strong>Do you ever forget to take your medications?</strong></p>
        <p>{{select:forget_meds:Forget Medications:Never,Rarely,Sometimes,Often}}</p>

        <p><strong>Do you ever stop taking your medications when you feel better?</strong></p>
        <p>{{select:stop_when_better:Stop when better:No,Sometimes,Yes}}</p>

        <p><strong>Do you have any difficulties taking your medications?</strong></p>
        <p>{{checkbox:difficulty_swallowing:Difficulty swallowing tablets}}</p>
        <p>{{checkbox:difficulty_opening:Difficulty opening containers}}</p>
        <p>{{checkbox:difficulty_remembering:Difficulty remembering to take}}</p>
        <p>{{checkbox:difficulty_cost:Cost of medications}}</p>
        <p>{{textarea:other_difficulties:Other difficulties}}</p>
    </div>

    <div style="background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f44336;">
        <h3 style="color: #c62828;">Side Effects</h3>
        <p><strong>Are you experiencing any side effects from your medications?</strong></p>
        <p>{{select:has_side_effects:Side Effects:No,Yes - mild,Yes - moderate,Yes - severe}}</p>
        <p>{{textarea:side_effects_details:Please describe any side effects}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Effectiveness</h3>

        <p><strong>Do you feel your medications are working well?</strong></p>
        <p>{{select:effectiveness:Effectiveness:Very effective,Somewhat effective,Not sure,Not effective}}</p>

        <p><strong>Are there any concerns you would like to discuss about your medications?</strong></p>
        <p>{{textarea:patient_concerns:Patient Concerns}}</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Review Outcomes (Clinician)</h3>
        <p>{{checkbox:outcome_continue:Continue current medications unchanged}}</p>
        <p>{{checkbox:outcome_dose_change:Dose adjustment required}}</p>
        <p>{{checkbox:outcome_new_med:New medication added}}</p>
        <p>{{checkbox:outcome_stop_med:Medication stopped/deprescribed}}</p>
        <p>{{checkbox:outcome_monitoring:Additional monitoring required}}</p>
        <p>{{checkbox:outcome_referral:Referral to specialist required}}</p>
        <p>{{textarea:review_notes:Review Notes and Changes Made}}</p>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <p><strong>Reviewed by:</strong> {{doctor_name}}</p>
        <p><strong>Next Review Due:</strong> {{input:next_review:Next Review Date:date}}</p>
    </div>
</div>
HTML;
    }

    private function getPreProcedureConsentForm(): string
    {
        return <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #2c3e50; margin-bottom: 5px;">CONSENT FOR PROCEDURE/TREATMENT</h2>
        <p style="color: #7f8c8d; font-size: 10pt;">{{clinic_name}}</p>
        <p style="color: #7f8c8d; font-size: 9pt;">Patient Agreement to Investigation or Treatment - CQC Compliant</p>
    </div>

    <div style="background: #f8f9fa; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
        <p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | <strong>NHS Number:</strong> {{input:nhs_number:NHS Number:text}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Procedure Details</h3>
        <p><strong>Name of Proposed Procedure/Treatment:</strong></p>
        <p>{{textarea:procedure_name:Name and description of procedure}}</p>

        <p><strong>Intended Benefits:</strong></p>
        <p>{{textarea:intended_benefits:Expected benefits of the procedure}}</p>
    </div>

    <div style="background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f44336;">
        <h3 style="color: #c62828;">Risks and Complications</h3>
        <p style="font-size: 10pt;">The following risks have been explained to me:</p>
        <p>{{textarea:risks_explained:Specific risks discussed}}</p>

        <p><strong>I understand that common risks may include:</strong></p>
        <p>{{checkbox:risk_pain:Pain or discomfort}}</p>
        <p>{{checkbox:risk_bleeding:Bleeding}}</p>
        <p>{{checkbox:risk_infection:Infection}}</p>
        <p>{{checkbox:risk_scarring:Scarring}}</p>
        <p>{{checkbox:risk_bruising:Bruising}}</p>
        <p>{{checkbox:risk_nerve:Nerve damage}}</p>
        <p>{{checkbox:risk_allergic:Allergic reaction}}</p>
        <p>{{textarea:additional_risks:Additional procedure-specific risks}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Alternative Treatments</h3>
        <p style="font-size: 10pt;">The following alternatives have been explained to me:</p>
        <p>{{textarea:alternatives:Alternative treatment options discussed}}</p>
        <p>{{checkbox:no_treatment_option:The option of no treatment has been explained}}</p>
    </div>

    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <h3 style="color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 8px;">Anaesthesia</h3>
        <p><strong>Type of anaesthesia:</strong></p>
        <p>{{select:anaesthesia_type:Anaesthesia Type:None required,Local anaesthetic,Sedation,General anaesthetic}}</p>
        <p>{{checkbox:anaesthesia_risks:I understand the risks associated with anaesthesia have been explained}}</p>
    </div>

    <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
        <h3 style="color: #2e7d32;">Patient Declaration</h3>
        <p style="font-size: 10pt;"><strong>I confirm that:</strong></p>
        <p>{{checkbox:confirm_explained:The procedure has been explained to me in terms I understand}}</p>
        <p>{{checkbox:confirm_questions:I have had the opportunity to ask questions and all my questions have been answered}}</p>
        <p>{{checkbox:confirm_benefits_risks:I understand the expected benefits and potential risks}}</p>
        <p>{{checkbox:confirm_alternatives:I understand the alternatives to this procedure}}</p>
        <p>{{checkbox:confirm_withdraw:I understand I can withdraw my consent at any time before the procedure}}</p>
        <p>{{checkbox:confirm_photos:I consent to clinical photographs being taken for my medical records (optional)}}</p>
        <p>{{checkbox:confirm_training:I consent to supervised trainees being present during my procedure (optional)}}</p>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
        <h3 style="color: #856404;">Important Information</h3>
        <ul style="font-size: 10pt;">
            <li>You have the right to change your mind at any time, including after signing this form</li>
            <li>If you have any concerns before the procedure, please speak to your healthcare team</li>
            <li>Please inform us of any changes to your health before the procedure</li>
        </ul>
    </div>

    <div style="margin-top: 30px;">
        <p><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
        <p><strong>Time:</strong> {{input:signature_time:Time:text}}</p>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px;">
        <h3>Healthcare Professional Statement</h3>
        <p style="font-size: 10pt;">I confirm that I have explained the procedure, its benefits, risks, and alternatives to the patient. I have answered all questions and believe the patient has capacity to give informed consent.</p>
        <p><strong>Name:</strong> {{doctor_name}}</p>
        <p><strong>Professional Registration Number:</strong> {{input:hcp_reg_number:Registration Number:text}}</p>
        <p><strong>Signature:</strong></p>
        {{signature:hcp_signature:Healthcare Professional Signature}}
        <p><strong>Date:</strong> {{current_date}}</p>
    </div>
</div>
HTML;
    }
}
