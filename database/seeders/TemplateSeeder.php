<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('is_admin', true)->first();
        $createdBy = $admin ? $admin->id : 1;

        $templates = [
            // REFERRAL LETTERS
            [
                'name' => 'Medical Referral Letter',
                'type' => 'letter',
                'content' => $this->getMedicalReferralLetter(),
                'is_system' => true,
            ],
            [
                'name' => 'Specialist Referral Letter',
                'type' => 'letter',
                'content' => $this->getSpecialistReferralLetter(),
                'is_system' => true,
            ],
            [
                'name' => 'Urgent Referral Letter (2WW)',
                'type' => 'letter',
                'content' => $this->getUrgentReferralLetter(),
                'is_system' => true,
            ],

            // MEDICAL CERTIFICATES (stored as 'form' type)
            [
                'name' => 'Fit Note (Statement of Fitness for Work)',
                'type' => 'form',
                'content' => $this->getFitNote(),
                'is_system' => true,
            ],
            [
                'name' => 'Medical Certificate - General',
                'type' => 'form',
                'content' => $this->getMedicalCertificate(),
                'is_system' => true,
            ],
            [
                'name' => 'Fitness to Travel Certificate',
                'type' => 'form',
                'content' => $this->getFitnessToTravel(),
                'is_system' => true,
            ],
            [
                'name' => 'Fitness to Drive Certificate',
                'type' => 'form',
                'content' => $this->getFitnessToDrive(),
                'is_system' => true,
            ],

            // CONSULTATION SUMMARIES
            [
                'name' => 'General Consultation Summary',
                'type' => 'letter',
                'content' => $this->getConsultationSummary(),
                'is_system' => true,
            ],
            [
                'name' => 'Weight Management Consultation',
                'type' => 'letter',
                'content' => $this->getWeightManagementConsultation(),
                'is_system' => true,
            ],
            [
                'name' => 'TRT (Testosterone) Consultation',
                'type' => 'letter',
                'content' => $this->getTRTConsultation(),
                'is_system' => true,
            ],
            [
                'name' => 'Mental Health Assessment',
                'type' => 'letter',
                'content' => $this->getMentalHealthAssessment(),
                'is_system' => true,
            ],
            [
                'name' => 'Diabetes Review Letter',
                'type' => 'letter',
                'content' => $this->getDiabetesReview(),
                'is_system' => true,
            ],
            [
                'name' => 'Cardiovascular Risk Assessment',
                'type' => 'letter',
                'content' => $this->getCardiovascularAssessment(),
                'is_system' => true,
            ],
            [
                'name' => 'COPD Review Letter',
                'type' => 'letter',
                'content' => $this->getCOPDReview(),
                'is_system' => true,
            ],
            [
                'name' => 'Asthma Review Letter',
                'type' => 'letter',
                'content' => $this->getAsthmaReview(),
                'is_system' => true,
            ],

            // PRIVATE MEDICAL REPORTS
            [
                'name' => 'Private Medical Report',
                'type' => 'letter',
                'content' => $this->getPrivateMedicalReport(),
                'is_system' => true,
            ],
            [
                'name' => 'Insurance Medical Report',
                'type' => 'letter',
                'content' => $this->getInsuranceMedicalReport(),
                'is_system' => true,
            ],

            // PRESCRIPTIONS & TREATMENT
            [
                'name' => 'Shared Care Agreement',
                'type' => 'form',
                'content' => $this->getSharedCareAgreement(),
                'is_system' => true,
            ],
            [
                'name' => 'Treatment Plan Letter',
                'type' => 'letter',
                'content' => $this->getTreatmentPlanLetter(),
                'is_system' => true,
            ],

            // CONSENT FORMS
            [
                'name' => 'Consent Form - Treatment',
                'type' => 'form',
                'content' => $this->getConsentFormTreatment(),
                'is_system' => true,
            ],
            [
                'name' => 'Consent Form - Information Sharing',
                'type' => 'form',
                'content' => $this->getConsentFormInfoSharing(),
                'is_system' => true,
            ],

            // DISCHARGE & FOLLOW-UP
            [
                'name' => 'Discharge Summary',
                'type' => 'letter',
                'content' => $this->getDischargeSummary(),
                'is_system' => true,
            ],
            [
                'name' => 'Follow-Up Appointment Letter',
                'type' => 'letter',
                'content' => $this->getFollowUpLetter(),
                'is_system' => true,
            ],

            // SPECIAL SERVICES
            [
                'name' => 'Aesthetics Consultation Summary',
                'type' => 'letter',
                'content' => $this->getAestheticsConsultation(),
                'is_system' => true,
            ],
            [
                'name' => 'Hair Loss Consultation',
                'type' => 'letter',
                'content' => $this->getHairLossConsultation(),
                'is_system' => true,
            ],
            [
                'name' => 'Sexual Health Consultation',
                'type' => 'letter',
                'content' => $this->getSexualHealthConsultation(),
                'is_system' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['name' => $template['name']],
                array_merge($template, [
                    'created_by' => $createdBy,
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Created ' . count($templates) . ' NHS/CQC compliant templates.');
    }

    private function getMedicalReferralLetter(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}<br>Email: {{clinic_email}}</p>
<p>Date: {{current_date}}</p>
<p><strong>MEDICAL REFERRAL</strong></p>
<p>Dear Colleague,</p>
<p><strong>Re: {{patient_name}}</strong><br>DOB: {{patient_dob}} | NHS No: {{patient_nhs_number}}<br>Address: {{patient_address}}</p>
<p>I am writing to refer the above-named patient for your specialist opinion and further management.</p>
<p><strong>Presenting Complaint:</strong><br>[Enter presenting complaint]</p>
<p><strong>Relevant Medical History:</strong><br>{{patient_medical_history}}</p>
<p><strong>Current Medications:</strong><br>{{patient_medications}}</p>
<p><strong>Allergies:</strong><br>{{patient_allergies}}</p>
<p><strong>Relevant Investigations:</strong><br>[Enter investigation results]</p>
<p><strong>Reason for Referral:</strong><br>[Enter reason for referral]</p>
<p>Please do not hesitate to contact me if you require any further information.</p>
<p>Yours faithfully,</p>
<p><br><br><strong>{{doctor_name}}</strong><br>{{doctor_qualifications}}<br>GMC No: {{doctor_gmc}}</p>
</div>';
    }

    private function getSpecialistReferralLetter(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}</p>
<p>Date: {{current_date}}</p>
<p><strong>SPECIALIST REFERRAL</strong></p>
<p>Dear [Specialist Name],</p>
<p><strong>Patient: {{patient_name}}</strong><br>DOB: {{patient_dob}} | NHS No: {{patient_nhs_number}}</p>
<p>Thank you for seeing this patient. I would be grateful for your expert opinion regarding the following:</p>
<p><strong>Clinical Summary:</strong><br>[Enter clinical summary]</p>
<p><strong>Investigations Performed:</strong><br>[Enter investigations]</p>
<p><strong>Current Treatment:</strong><br>{{patient_medications}}</p>
<p><strong>Specific Questions:</strong><br>[Enter specific questions for specialist]</p>
<p>With thanks,</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getUrgentReferralLetter(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt; border: 2px solid red; padding: 15px;">
<p style="color: red; font-size: 16pt;"><strong>** URGENT - TWO WEEK WAIT REFERRAL **</strong></p>
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}</p>
<p>Date: {{current_date}}</p>
<p><strong>Patient: {{patient_name}}</strong><br>DOB: {{patient_dob}} | NHS No: {{patient_nhs_number}}<br>Address: {{patient_address}}<br>Contact: {{patient_phone}}</p>
<p><strong>Suspected Cancer Pathway:</strong> [Select pathway]</p>
<p><strong>Presenting Symptoms:</strong><br>[Enter symptoms meeting 2WW criteria]</p>
<p><strong>Duration of Symptoms:</strong> [Enter duration]</p>
<p><strong>Relevant History:</strong><br>{{patient_medical_history}}</p>
<p><strong>Examination Findings:</strong><br>[Enter findings]</p>
<p><strong>Investigations:</strong><br>[Enter any investigations performed]</p>
<p><strong>Referring Clinician:</strong><br>{{doctor_name}}<br>GMC: {{doctor_gmc}}<br>Contact: {{clinic_phone}}</p>
</div>';
    }

    private function getFitNote(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt; border: 1px solid #000; padding: 20px;">
<p style="text-align: center;"><strong>STATEMENT OF FITNESS FOR WORK</strong><br>(Med 3)</p>
<p><strong>Patient Name:</strong> {{patient_name}}<br><strong>Date of Birth:</strong> {{patient_dob}}<br><strong>NHS Number:</strong> {{patient_nhs_number}}</p>
<p>I assessed your case on: {{current_date}}</p>
<p>Because of the following condition(s):<br>[Enter diagnosis/conditions]</p>
<p><strong>I advise you that:</strong></p>
<p>[ ] You are not fit for work</p>
<p>[ ] You may be fit for work taking account of the following advice:</p>
<ul>
<li>[ ] A phased return to work</li>
<li>[ ] Altered hours</li>
<li>[ ] Amended duties</li>
<li>[ ] Workplace adaptations</li>
</ul>
<p><strong>Comments:</strong><br>[Enter comments]</p>
<p>This will be the case for: [Enter duration] from {{current_date}}</p>
<p>I will/will not need to assess your fitness for work again at the end of this period.</p>
<p><br><strong>Doctor\'s Signature:</strong> _______________________</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}<br>{{clinic_name}}</p>
</div>';
    }

    private function getMedicalCertificate(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p style="text-align: center;"><strong>MEDICAL CERTIFICATE</strong></p>
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}</p>
<p>Date: {{current_date}}</p>
<p><strong>TO WHOM IT MAY CONCERN</strong></p>
<p>This is to certify that <strong>{{patient_name}}</strong>, Date of Birth: {{patient_dob}}, attended this clinic on {{current_date}}.</p>
<p><strong>Clinical Findings:</strong><br>[Enter clinical findings]</p>
<p><strong>Medical Opinion:</strong><br>[Enter medical opinion]</p>
<p>This certificate is issued at the patient\'s request for [Enter purpose].</p>
<p><br><strong>{{doctor_name}}</strong><br>{{doctor_qualifications}}<br>GMC Registration: {{doctor_gmc}}</p>
</div>';
    }

    private function getFitnessToTravel(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p style="text-align: center;"><strong>FITNESS TO TRAVEL CERTIFICATE</strong></p>
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p>This is to certify that <strong>{{patient_name}}</strong> (DOB: {{patient_dob}}) has been examined and is considered:</p>
<p>[ ] FIT TO TRAVEL by air/sea/land<br>[ ] FIT TO TRAVEL with the following conditions:<br>[Enter conditions]</p>
<p><strong>Destination:</strong> [Enter destination]<br><strong>Travel Dates:</strong> [Enter dates]</p>
<p><strong>Medical Conditions:</strong><br>{{patient_medical_history}}</p>
<p><strong>Current Medications:</strong><br>{{patient_medications}}</p>
<p><strong>Special Requirements:</strong><br>[Enter any special requirements]</p>
<p><br><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getFitnessToDrive(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p style="text-align: center;"><strong>FITNESS TO DRIVE ASSESSMENT</strong></p>
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p><strong>Patient:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Licence Type:</strong> [ ] Group 1 (Car/Motorcycle) [ ] Group 2 (HGV/PCV)</p>
<p><strong>Medical Conditions Assessed:</strong><br>[Enter conditions]</p>
<p><strong>Assessment Findings:</strong></p>
<ul>
<li>Vision: [Enter findings]</li>
<li>Cardiovascular: [Enter findings]</li>
<li>Neurological: [Enter findings]</li>
<li>Diabetes: [Enter findings]</li>
</ul>
<p><strong>Opinion:</strong><br>Based on DVLA guidelines, this patient is [ ] FIT / [ ] NOT FIT to drive.</p>
<p><strong>Recommendations:</strong><br>[Enter recommendations]</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getConsultationSummary(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}</p>
<p>Date: {{current_date}}</p>
<p><strong>CONSULTATION SUMMARY</strong></p>
<p><strong>Patient:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}<br><strong>NHS No:</strong> {{patient_nhs_number}}</p>
<p><strong>Reason for Consultation:</strong><br>[Enter reason]</p>
<p><strong>History of Presenting Complaint:</strong><br>[Enter HPC]</p>
<p><strong>Examination Findings:</strong><br>[Enter examination findings]</p>
<p><strong>Diagnosis/Impression:</strong><br>[Enter diagnosis]</p>
<p><strong>Investigations Requested:</strong><br>[Enter investigations]</p>
<p><strong>Management Plan:</strong><br>[Enter plan]</p>
<p><strong>Follow-up:</strong><br>[Enter follow-up arrangements]</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getWeightManagementConsultation(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>WEIGHT MANAGEMENT CONSULTATION</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Anthropometric Measurements:</strong></p>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr><td>Height</td><td>[Enter] cm</td></tr>
<tr><td>Weight</td><td>[Enter] kg</td></tr>
<tr><td>BMI</td><td>[Enter] kg/m²</td></tr>
<tr><td>Waist Circumference</td><td>[Enter] cm</td></tr>
<tr><td>Blood Pressure</td><td>[Enter] mmHg</td></tr>
</table>
<p><strong>Weight History:</strong><br>[Enter weight history and previous attempts]</p>
<p><strong>Lifestyle Assessment:</strong></p>
<ul>
<li>Diet: [Enter dietary habits]</li>
<li>Physical Activity: [Enter activity level]</li>
<li>Alcohol: [Enter units/week]</li>
<li>Smoking: [Enter status]</li>
</ul>
<p><strong>Comorbidities:</strong><br>{{patient_medical_history}}</p>
<p><strong>Investigations:</strong><br>[ ] HbA1c [ ] Lipid Profile [ ] LFTs [ ] TFTs [ ] Other: [Specify]</p>
<p><strong>Treatment Plan:</strong></p>
<ul>
<li>Dietary advice: [Enter]</li>
<li>Exercise prescription: [Enter]</li>
<li>Pharmacotherapy: [Enter if applicable - e.g., Orlistat, Liraglutide, Semaglutide]</li>
<li>Referral: [Enter if applicable]</li>
</ul>
<p><strong>Target Weight:</strong> [Enter] kg over [Enter] months</p>
<p><strong>Follow-up:</strong> [Enter arrangements]</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getTRTConsultation(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>TESTOSTERONE REPLACEMENT THERAPY (TRT) CONSULTATION</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Presenting Symptoms:</strong><br>[Enter symptoms - fatigue, low libido, erectile dysfunction, mood changes, etc.]</p>
<p><strong>Symptom Duration:</strong> [Enter duration]</p>
<p><strong>ADAM Questionnaire Score:</strong> [Enter score]</p>
<p><strong>Relevant History:</strong></p>
<ul>
<li>Previous testosterone levels: [Enter]</li>
<li>Testicular/pituitary conditions: [Enter]</li>
<li>Medications affecting hormones: [Enter]</li>
</ul>
<p><strong>Examination:</strong></p>
<ul>
<li>BMI: [Enter]</li>
<li>Blood Pressure: [Enter]</li>
<li>Testicular examination: [Enter findings]</li>
<li>Gynaecomastia: [Yes/No]</li>
</ul>
<p><strong>Investigations:</strong></p>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr><td>Total Testosterone</td><td>[Enter] nmol/L</td><td>(Reference: 8.6-29 nmol/L)</td></tr>
<tr><td>Free Testosterone</td><td>[Enter] pmol/L</td><td></td></tr>
<tr><td>SHBG</td><td>[Enter] nmol/L</td><td></td></tr>
<tr><td>LH</td><td>[Enter] IU/L</td><td></td></tr>
<tr><td>FSH</td><td>[Enter] IU/L</td><td></td></tr>
<tr><td>Prolactin</td><td>[Enter] mU/L</td><td></td></tr>
<tr><td>PSA</td><td>[Enter] ng/mL</td><td></td></tr>
<tr><td>Haematocrit</td><td>[Enter] %</td><td></td></tr>
</table>
<p><strong>Diagnosis:</strong><br>[ ] Primary hypogonadism [ ] Secondary hypogonadism [ ] Late-onset hypogonadism</p>
<p><strong>Treatment Plan:</strong></p>
<ul>
<li>Preparation: [Gel/Injection - specify]</li>
<li>Dose: [Enter]</li>
<li>Route: [Enter]</li>
<li>Frequency: [Enter]</li>
</ul>
<p><strong>Monitoring Schedule:</strong><br>Bloods at 3, 6, 12 months then annually: Testosterone, Haematocrit, PSA, Lipids, LFTs</p>
<p><strong>Risks Discussed:</strong><br>[ ] Polycythaemia [ ] Prostate effects [ ] Cardiovascular risks [ ] Fertility impact [ ] Skin reactions</p>
<p><strong>Patient Consent:</strong> [ ] Obtained</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getMentalHealthAssessment(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>MENTAL HEALTH ASSESSMENT</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Presenting Complaint:</strong><br>[Enter main concerns]</p>
<p><strong>History of Presenting Complaint:</strong><br>[Enter detailed history]</p>
<p><strong>Risk Assessment:</strong></p>
<ul>
<li>Suicidal ideation: [Enter]</li>
<li>Self-harm: [Enter]</li>
<li>Risk to others: [Enter]</li>
</ul>
<p><strong>Screening Scores:</strong></p>
<ul>
<li>PHQ-9: [Enter score] /27</li>
<li>GAD-7: [Enter score] /21</li>
</ul>
<p><strong>Mental State Examination:</strong><br>[Enter MSE findings]</p>
<p><strong>Diagnosis/Impression:</strong><br>[Enter diagnosis]</p>
<p><strong>Management Plan:</strong></p>
<ul>
<li>Medication: [Enter]</li>
<li>Therapy referral: [Enter]</li>
<li>Safety plan: [Enter]</li>
</ul>
<p><strong>Follow-up:</strong> [Enter]</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getDiabetesReview(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>DIABETES ANNUAL REVIEW</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Diabetes Type:</strong> [ ] Type 1 [ ] Type 2<br><strong>Duration:</strong> [Enter years]</p>
<p><strong>Current Medications:</strong><br>{{patient_medications}}</p>
<p><strong>Measurements:</strong></p>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr><td>HbA1c</td><td>[Enter] mmol/mol</td><td>Target: &lt;48 mmol/mol</td></tr>
<tr><td>BMI</td><td>[Enter] kg/m²</td><td></td></tr>
<tr><td>Blood Pressure</td><td>[Enter] mmHg</td><td>Target: &lt;140/80</td></tr>
<tr><td>eGFR</td><td>[Enter] mL/min</td><td></td></tr>
<tr><td>Cholesterol</td><td>[Enter] mmol/L</td><td></td></tr>
</table>
<p><strong>Complications Screening:</strong></p>
<ul>
<li>Retinopathy screening: [Enter date/result]</li>
<li>Foot examination: [Enter findings]</li>
<li>Urine ACR: [Enter]</li>
<li>Neuropathy symptoms: [Enter]</li>
</ul>
<p><strong>Lifestyle:</strong><br>Diet: [Enter] | Exercise: [Enter] | Smoking: [Enter]</p>
<p><strong>Plan:</strong><br>[Enter management changes]</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getCardiovascularAssessment(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>CARDIOVASCULAR RISK ASSESSMENT</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}} | Age: {{patient_age}}</p>
<p><strong>Risk Factors:</strong></p>
<table border="1" cellpadding="5" style="border-collapse: collapse;">
<tr><td>Blood Pressure</td><td>[Enter] mmHg</td></tr>
<tr><td>Total Cholesterol</td><td>[Enter] mmol/L</td></tr>
<tr><td>HDL Cholesterol</td><td>[Enter] mmol/L</td></tr>
<tr><td>Smoking Status</td><td>[Enter]</td></tr>
<tr><td>Diabetes</td><td>[Yes/No]</td></tr>
<tr><td>Family History</td><td>[Enter]</td></tr>
<tr><td>BMI</td><td>[Enter] kg/m²</td></tr>
</table>
<p><strong>QRISK3 Score:</strong> [Enter]% 10-year CVD risk</p>
<p><strong>ECG:</strong> [Enter findings if performed]</p>
<p><strong>Recommendations:</strong></p>
<ul>
<li>Lifestyle: [Enter]</li>
<li>Statin therapy: [Enter if indicated]</li>
<li>Antihypertensive: [Enter if indicated]</li>
<li>Aspirin: [Enter if indicated]</li>
</ul>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getCOPDReview(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>COPD REVIEW</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Current Inhalers:</strong><br>{{patient_medications}}</p>
<p><strong>Spirometry:</strong></p>
<ul>
<li>FEV1: [Enter] L ([Enter]% predicted)</li>
<li>FVC: [Enter] L</li>
<li>FEV1/FVC: [Enter]%</li>
</ul>
<p><strong>MRC Dyspnoea Scale:</strong> [1-5]</p>
<p><strong>CAT Score:</strong> [Enter] /40</p>
<p><strong>Exacerbations (last 12 months):</strong> [Enter number]</p>
<p><strong>Smoking Status:</strong> [Enter]</p>
<p><strong>Inhaler Technique:</strong> [Checked/Needs review]</p>
<p><strong>Vaccinations:</strong><br>Flu: [Date] | Pneumococcal: [Date]</p>
<p><strong>Plan:</strong><br>[Enter management plan]</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getAsthmaReview(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>ASTHMA REVIEW</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Current Treatment:</strong><br>{{patient_medications}}</p>
<p><strong>Asthma Control (RCP 3 Questions):</strong></p>
<ul>
<li>Sleep disturbance: [Yes/No]</li>
<li>Daytime symptoms: [Yes/No]</li>
<li>Activity limitation: [Yes/No]</li>
</ul>
<p><strong>ACT Score:</strong> [Enter] /25</p>
<p><strong>Peak Flow:</strong> [Enter] L/min (Best: [Enter])</p>
<p><strong>Exacerbations (last 12 months):</strong></p>
<ul>
<li>Oral steroids: [Enter number of courses]</li>
<li>A&E attendances: [Enter]</li>
<li>Hospital admissions: [Enter]</li>
</ul>
<p><strong>Triggers Identified:</strong> [Enter]</p>
<p><strong>Inhaler Technique:</strong> [Satisfactory/Needs correction]</p>
<p><strong>Smoking:</strong> [Enter status]</p>
<p><strong>Management Plan:</strong><br>[Enter plan and step]</p>
<p><strong>Written Asthma Action Plan:</strong> [ ] Provided/Updated</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getPrivateMedicalReport(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>PRIVATE & CONFIDENTIAL</strong></p>
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}</p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>PRIVATE MEDICAL REPORT</strong></p>
<p><strong>Prepared for:</strong> [Enter recipient]</p>
<p><strong>Patient:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}<br><strong>Address:</strong> {{patient_address}}</p>
<p><strong>Purpose of Report:</strong><br>[Enter purpose]</p>
<p><strong>Medical History:</strong><br>{{patient_medical_history}}</p>
<p><strong>Current Medications:</strong><br>{{patient_medications}}</p>
<p><strong>Allergies:</strong><br>{{patient_allergies}}</p>
<p><strong>Examination Findings:</strong><br>[Enter findings]</p>
<p><strong>Opinion:</strong><br>[Enter medical opinion]</p>
<p>This report is based on my clinical assessment and medical records available at the time of writing.</p>
<p><strong>{{doctor_name}}</strong><br>{{doctor_qualifications}}<br>GMC: {{doctor_gmc}}</p>
<p><em>Fee: [Enter fee] - This is a private medical report.</em></p>
</div>';
    }

    private function getInsuranceMedicalReport(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>CONFIDENTIAL - INSURANCE MEDICAL REPORT</strong></p>
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p><strong>Insurance Company:</strong> [Enter name]<br><strong>Policy Number:</strong> [Enter number]<br><strong>Claim Reference:</strong> [Enter reference]</p>
<p><strong>Patient:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Patient Consent:</strong> [ ] Obtained on [Date]</p>
<p><strong>Questions from Insurer:</strong></p>
<p>1. [Enter question]<br>Answer: [Enter answer]</p>
<p>2. [Enter question]<br>Answer: [Enter answer]</p>
<p><strong>Medical History Summary:</strong><br>{{patient_medical_history}}</p>
<p><strong>Current Condition:</strong><br>[Enter details]</p>
<p><strong>Prognosis:</strong><br>[Enter prognosis]</p>
<p>I confirm the above information is accurate based on my clinical knowledge and available records.</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getSharedCareAgreement(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p style="text-align: center;"><strong>SHARED CARE AGREEMENT</strong></p>
<p><strong>Patient:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}<br><strong>NHS No:</strong> {{patient_nhs_number}}</p>
<p><strong>Medication:</strong> [Enter medication]<br><strong>Indication:</strong> [Enter indication]<br><strong>Initiating Specialist:</strong> [Enter name and hospital]</p>
<p><strong>Specialist Responsibilities:</strong></p>
<ul>
<li>Initiate treatment and stabilise dosage</li>
<li>Provide monitoring protocol</li>
<li>Advise GP on dose adjustments</li>
<li>Review patient annually or as needed</li>
</ul>
<p><strong>GP Responsibilities:</strong></p>
<ul>
<li>Prescribe medication as recommended</li>
<li>Perform monitoring as per protocol</li>
<li>Report adverse effects to specialist</li>
<li>Refer back if concerns arise</li>
</ul>
<p><strong>Monitoring Requirements:</strong><br>[Enter monitoring schedule]</p>
<p><strong>Agreement:</strong></p>
<p>GP Signature: _________________ Date: _______</p>
<p>Specialist Signature: _________________ Date: _______</p>
</div>';
    }

    private function getTreatmentPlanLetter(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p><strong>TREATMENT PLAN</strong></p>
<p>Dear {{patient_name}},</p>
<p>Following your consultation on {{current_date}}, I am writing to confirm your treatment plan.</p>
<p><strong>Diagnosis:</strong><br>[Enter diagnosis]</p>
<p><strong>Treatment Prescribed:</strong></p>
<ol>
<li>[Enter treatment 1]</li>
<li>[Enter treatment 2]</li>
</ol>
<p><strong>Expected Outcomes:</strong><br>[Enter expected outcomes]</p>
<p><strong>Potential Side Effects:</strong><br>[Enter side effects to be aware of]</p>
<p><strong>Warning Signs - Contact Us If:</strong><br>[Enter red flags]</p>
<p><strong>Follow-up Appointment:</strong><br>[Enter date/arrangements]</p>
<p>If you have any questions, please contact the clinic.</p>
<p>Kind regards,</p>
<p><strong>{{doctor_name}}</strong></p>
</div>';
    }

    private function getConsentFormTreatment(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p style="text-align: center;"><strong>CONSENT FORM - TREATMENT/PROCEDURE</strong></p>
<p><strong>Patient Name:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Proposed Treatment/Procedure:</strong><br>[Enter details]</p>
<p><strong>I confirm that:</strong></p>
<ul>
<li>[ ] The treatment/procedure has been explained to me in terms I understand</li>
<li>[ ] I have had the opportunity to ask questions</li>
<li>[ ] I understand the expected benefits and risks</li>
<li>[ ] I understand alternatives to this treatment</li>
<li>[ ] I understand I can withdraw consent at any time</li>
</ul>
<p><strong>Risks Discussed:</strong><br>[Enter specific risks]</p>
<p><strong>Patient Signature:</strong> _________________________ Date: _________</p>
<p><strong>Clinician Signature:</strong> _________________________ Date: _________</p>
<p><strong>Clinician Name:</strong> {{doctor_name}}</p>
</div>';
    }

    private function getConsentFormInfoSharing(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p style="text-align: center;"><strong>CONSENT FOR INFORMATION SHARING</strong></p>
<p><strong>Patient Name:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}</p>
<p>I hereby consent to {{clinic_name}} sharing my medical information with:</p>
<p>[ ] My GP: [Enter GP details]<br>[ ] Specialist: [Enter details]<br>[ ] Insurance Company: [Enter details]<br>[ ] Employer: [Enter details]<br>[ ] Other: [Specify]</p>
<p><strong>Information to be shared:</strong><br>[ ] Full medical records<br>[ ] Specific records relating to: [Enter]<br>[ ] Summary letter only</p>
<p><strong>Purpose:</strong><br>[Enter purpose of sharing]</p>
<p>I understand I can withdraw this consent in writing at any time.</p>
<p><strong>Patient Signature:</strong> _________________________ Date: _________</p>
<p><strong>Witness:</strong> _________________________ Date: _________</p>
</div>';
    }

    private function getDischargeSummary(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}</p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>DISCHARGE SUMMARY</strong></p>
<p><strong>Patient:</strong> {{patient_name}}<br><strong>DOB:</strong> {{patient_dob}}<br><strong>NHS No:</strong> {{patient_nhs_number}}</p>
<p><strong>Admission Date:</strong> [Enter]<br><strong>Discharge Date:</strong> [Enter]</p>
<p><strong>Diagnosis:</strong><br>[Enter primary and secondary diagnoses]</p>
<p><strong>Treatment During Admission:</strong><br>[Enter summary]</p>
<p><strong>Procedures Performed:</strong><br>[Enter if applicable]</p>
<p><strong>Discharge Medications:</strong><br>[Enter medications with changes highlighted]</p>
<p><strong>Recommendations for GP:</strong><br>[Enter recommendations]</p>
<p><strong>Follow-up Arrangements:</strong><br>[Enter details]</p>
<p><strong>Information Given to Patient:</strong><br>[Enter]</p>
<p><strong>Discharging Clinician:</strong><br>{{doctor_name}}<br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getFollowUpLetter(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong><br>{{clinic_address}}<br>Tel: {{clinic_phone}}</p>
<p>Date: {{current_date}}</p>
<p>Dear {{patient_name}},</p>
<p><strong>Re: Follow-Up Appointment</strong></p>
<p>Following your recent consultation/treatment, we would like to invite you for a follow-up appointment.</p>
<p><strong>Appointment Details:</strong><br>Date: [Enter date]<br>Time: [Enter time]<br>Location: {{clinic_address}}<br>With: {{doctor_name}}</p>
<p><strong>Purpose of Appointment:</strong><br>[Enter purpose]</p>
<p><strong>Please Bring:</strong></p>
<ul>
<li>This letter</li>
<li>Current medications list</li>
<li>Any recent test results</li>
</ul>
<p>If you are unable to attend, please contact us to rearrange.</p>
<p>Kind regards,</p>
<p><strong>{{clinic_name}}</strong></p>
</div>';
    }

    private function getAestheticsConsultation(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>AESTHETIC CONSULTATION SUMMARY</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Areas of Concern:</strong><br>[Enter patient concerns]</p>
<p><strong>Medical History Review:</strong></p>
<ul>
<li>Previous aesthetic treatments: [Enter]</li>
<li>Allergies: {{patient_allergies}}</li>
<li>Medications: {{patient_medications}}</li>
<li>Pregnancy/Breastfeeding: [N/A or status]</li>
</ul>
<p><strong>Treatment Discussed:</strong><br>[Enter treatment options]</p>
<p><strong>Treatment Plan:</strong><br>[Enter plan]</p>
<p><strong>Products/Devices:</strong><br>[Enter specific products]</p>
<p><strong>Expected Results:</strong><br>[Enter realistic expectations]</p>
<p><strong>Risks & Side Effects Discussed:</strong><br>[Enter risks]</p>
<p><strong>Consent:</strong> [ ] Signed</p>
<p><strong>Photographs:</strong> [ ] Taken</p>
<p><strong>{{doctor_name}}</strong></p>
</div>';
    }

    private function getHairLossConsultation(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>HAIR LOSS CONSULTATION</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Pattern of Hair Loss:</strong><br>[Enter description]</p>
<p><strong>Duration:</strong> [Enter]</p>
<p><strong>Family History:</strong><br>[Enter]</p>
<p><strong>Classification:</strong><br>[ ] Norwood Scale (Male): [Enter stage]<br>[ ] Ludwig Scale (Female): [Enter stage]</p>
<p><strong>Associated Symptoms:</strong><br>[ ] Scalp irritation [ ] Excessive shedding [ ] Other: [Enter]</p>
<p><strong>Investigations:</strong><br>[ ] Thyroid function [ ] Ferritin [ ] Vitamin D [ ] Hormonal panel</p>
<p><strong>Diagnosis:</strong><br>[ ] Androgenetic alopecia [ ] Telogen effluvium [ ] Alopecia areata [ ] Other: [Enter]</p>
<p><strong>Treatment Plan:</strong></p>
<ul>
<li>Topical: [Enter - e.g., Minoxidil]</li>
<li>Oral: [Enter - e.g., Finasteride]</li>
<li>PRP: [If applicable]</li>
<li>Other: [Enter]</li>
</ul>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }

    private function getSexualHealthConsultation(): string
    {
        return '<div style="font-family: Arial, sans-serif; font-size: 12pt;">
<p><strong>CONFIDENTIAL</strong></p>
<p><strong>{{clinic_name}}</strong></p>
<p>Date: {{current_date}}</p>
<p style="text-align: center;"><strong>SEXUAL HEALTH CONSULTATION</strong></p>
<p><strong>Patient:</strong> {{patient_name}} | <strong>DOB:</strong> {{patient_dob}}</p>
<p><strong>Presenting Concern:</strong><br>[Enter concern]</p>
<p><strong>Sexual History:</strong><br>[Enter relevant history - confidentially]</p>
<p><strong>Symptoms:</strong><br>[Enter symptoms if any]</p>
<p><strong>Examination:</strong><br>[Enter findings]</p>
<p><strong>Investigations:</strong></p>
<ul>
<li>[ ] STI screen</li>
<li>[ ] HIV test</li>
<li>[ ] Hepatitis screen</li>
<li>[ ] Other: [Enter]</li>
</ul>
<p><strong>Diagnosis:</strong><br>[Enter diagnosis]</p>
<p><strong>Treatment:</strong><br>[Enter treatment]</p>
<p><strong>Partner Notification:</strong><br>[Enter advice given]</p>
<p><strong>Follow-up:</strong><br>[Enter arrangements]</p>
<p><strong>Safe Sex Advice:</strong> [ ] Given</p>
<p><strong>{{doctor_name}}</strong><br>GMC: {{doctor_gmc}}</p>
</div>';
    }
}
