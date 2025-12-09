<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\User;

class WeightLossConsultationFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find admin user to assign as creator
        $admin = User::where('is_admin', true)->orWhere('role', 'admin')->first();
        $createdBy = $admin ? $admin->id : 1;

        // Check if template already exists
        $existing = Template::where('name', 'Weight Loss Consultation Form')->first();
        if ($existing) {
            $this->command->info('Weight Loss Consultation Form template already exists. Skipping...');
            return;
        }

        $content = <<<'HTML'
<div style="font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; border: 2px solid #005EB8;">
    
    <!-- NHS Header -->
    <div style="background-color: #005EB8; color: white; padding: 15px; text-align: center; margin-bottom: 20px;">
        <h1 style="margin: 0; font-size: 24pt; font-weight: bold;">NHS WEIGHT LOSS CONSULTATION FORM</h1>
        <p style="margin: 5px 0 0 0; font-size: 12pt;">Care Quality Commission (CQC) Compliant</p>
    </div>

    <!-- Patient Information -->
    <div style="background-color: #f0f0f0; padding: 15px; margin-bottom: 20px; border-left: 4px solid #005EB8;">
        <h2 style="color: #005EB8; margin-top: 0; font-size: 16pt;">Patient Information</h2>
        <p><strong>Patient Name:</strong> {{patient_name}}</p>
        <p><strong>Date of Birth:</strong> {{patient_dob}} <strong>Age:</strong> {{patient_age}} years</p>
        <p><strong>NHS Number:</strong> {{patient_nhs_number}}</p>
        <p><strong>GP Practice:</strong> {{patient_gp_practice}}</p>
        <p><strong>Consultation Date:</strong> {{current_date}}</p>
    </div>

    <!-- Reason for Consultation -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Reason for Consultation</h3>
        {{textarea:consultation_reason:Please describe the main reason for seeking weight loss advice:}}
    </div>

    <!-- Current Weight and Measurements -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Current Weight and Measurements</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; width: 50%;"><strong>Current Weight (kg):</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{input:current_weight:Current Weight (kg):number}}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Height (cm):</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{input:height:Height (cm):number}}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>BMI (Body Mass Index):</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{input:bmi:BMI:number}}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Waist Circumference (cm):</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{input:waist_circumference:Waist Circumference (cm):number}}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Weight 6 months ago (kg):</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{input:weight_6months_ago:Weight 6 months ago (kg):number}}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>Weight 1 year ago (kg):</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd;">{{input:weight_1year_ago:Weight 1 year ago (kg):number}}</td>
            </tr>
        </table>
    </div>

    <!-- Medical History -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Medical History</h3>
        <p><strong>Do you have any of the following conditions? (Please tick all that apply)</strong></p>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 5px;">{{checkbox:diabetes_type1:Type 1 Diabetes}}</td>
                <td style="padding: 5px;">{{checkbox:diabetes_type2:Type 2 Diabetes}}</td>
                <td style="padding: 5px;">{{checkbox:hypertension:Hypertension (High Blood Pressure)}}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">{{checkbox:heart_disease:Heart Disease}}</td>
                <td style="padding: 5px;">{{checkbox:stroke:Stroke or TIA}}</td>
                <td style="padding: 5px;">{{checkbox:arthritis:Arthritis}}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">{{checkbox:thyroid_disorder:Thyroid Disorder}}</td>
                <td style="padding: 5px;">{{checkbox:sleep_apnoea:Sleep Apnoea}}</td>
                <td style="padding: 5px;">{{checkbox:gallstones:Gallstones}}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">{{checkbox:depression:Depression or Anxiety}}</td>
                <td style="padding: 5px;">{{checkbox:other_medical:Other (please specify):}}</td>
                <td style="padding: 5px;">{{input:other_medical_details::text}}</td>
            </tr>
        </table>
        <p style="margin-top: 15px;"><strong>Current Medications:</strong></p>
        {{textarea:current_medications:Please list all current medications (including dosage):}}
        <p style="margin-top: 15px;"><strong>Allergies:</strong></p>
        {{textarea:allergies:Please list any allergies:}}
    </div>

    <!-- Family History -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Family History</h3>
        <p><strong>Does anyone in your family have:</strong></p>
        <table style="width: 100%;">
            <tr>
                <td style="padding: 5px;">{{checkbox:family_obesity:Obesity}}</td>
                <td style="padding: 5px;">{{checkbox:family_diabetes:Type 2 Diabetes}}</td>
                <td style="padding: 5px;">{{checkbox:family_heart_disease:Heart Disease}}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">{{checkbox:family_hypertension:High Blood Pressure}}</td>
                <td style="padding: 5px;">{{checkbox:family_stroke:Stroke}}</td>
                <td style="padding: 5px;">{{checkbox:family_other:Other (please specify):}}</td>
            </tr>
        </table>
        {{textarea:family_history_details:Additional family history details:}}
    </div>

    <!-- Lifestyle Assessment -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Lifestyle Assessment</h3>
        
        <p><strong>Physical Activity:</strong></p>
        <p>On average, how many days per week do you engage in moderate to vigorous physical activity (at least 30 minutes)?</p>
        {{input:activity_days_per_week:Days per week:number}}
        <p>What type of physical activity do you currently do?</p>
        {{textarea:activity_type:Type of physical activity:}}
        
        <p style="margin-top: 15px;"><strong>Dietary Habits:</strong></p>
        <p>How many portions of fruit and vegetables do you eat per day? (A portion is about 80g)</p>
        {{input:fruit_veg_portions:Portions per day:number}}
        <p>How many meals do you eat per day?</p>
        {{input:meals_per_day:Number of meals:number}}
        <p>How many snacks do you have per day?</p>
        {{input:snacks_per_day:Number of snacks:number}}
        <p>Do you eat regular meals or tend to skip meals?</p>
        {{select:meal_pattern:Regular meals or skip meals:Regular meals,Skip meals,Irregular pattern}}
        <p>How much water/fluid do you drink per day? (litres)</p>
        {{input:water_intake:Water/fluid per day (litres):number}}
        <p>Do you consume alcohol? If yes, how many units per week?</p>
        {{input:alcohol_units:Alcohol units per week:number}}
        
        <p style="margin-top: 15px;"><strong>Sleep:</strong></p>
        <p>On average, how many hours of sleep do you get per night?</p>
        {{input:sleep_hours:Hours per night:number}}
        <p>Do you have any sleep problems?</p>
        {{textarea:sleep_problems:Please describe any sleep problems:}}
        
        <p style="margin-top: 15px;"><strong>Smoking Status:</strong></p>
        {{select:smoking_status:Smoking status:Never smoked,Ex-smoker,Current smoker}}
        <p>If current or ex-smoker, how many cigarettes per day?</p>
        {{input:cigarettes_per_day:Cigarettes per day:number}}
    </div>

    <!-- Previous Weight Loss Attempts -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Previous Weight Loss Attempts</h3>
        <p><strong>Have you tried to lose weight before?</strong></p>
        {{select:previous_attempts:Have you tried to lose weight before?:Yes,No}}
        <p>If yes, please describe your previous attempts:</p>
        {{textarea:previous_attempts_details:Describe previous weight loss attempts (including methods used and results):}}
        <p>What worked well for you?</p>
        {{textarea:what_worked:What worked well:}}
        <p>What challenges did you face?</p>
        {{textarea:challenges_faced:What challenges did you face:}}
    </div>

    <!-- Goals and Expectations -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Goals and Expectations</h3>
        <p><strong>What is your target weight? (kg)</strong></p>
        {{input:target_weight:Target weight (kg):number}}
        <p><strong>How much weight would you like to lose? (kg)</strong></p>
        {{input:desired_weight_loss:Desired weight loss (kg):number}}
        <p><strong>By when would you like to achieve this? (date)</strong></p>
        {{input:target_date:Target date:date}}
        <p><strong>What is your main motivation for losing weight?</strong></p>
        {{textarea:motivation:Main motivation:}}
        <p><strong>How confident are you that you can achieve your weight loss goal? (1-10, where 10 is very confident)</strong></p>
        {{input:confidence_level:Confidence level (1-10):number}}
    </div>

    <!-- Safety Screening (CQC Requirement) -->
    <div style="margin-bottom: 20px; border: 2px solid #d9534f; padding: 15px; background-color: #fff3cd;">
        <h3 style="color: #d9534f; margin-top: 0; font-size: 14pt;">⚠️ Safety Screening (CQC Requirement)</h3>
        <p><strong>Do you currently have or have you ever had an eating disorder (anorexia, bulimia, binge eating disorder)?</strong></p>
        {{select:eating_disorder:Current or past eating disorder?:Yes (current),Yes (past),No}}
        {{textarea:eating_disorder_details:If yes, please provide details:}}
        <p><strong>Are you currently under the care of a mental health professional?</strong></p>
        {{select:mental_health_care:Under mental health care?:Yes,No}}
        {{textarea:mental_health_care_details:If yes, please provide details:}}
        <p><strong>Have you had any recent thoughts of self-harm or suicide?</strong></p>
        {{select:self_harm_thoughts:Recent self-harm or suicide thoughts?:Yes,No}}
        <p><strong>Do you have any concerns about your ability to safely participate in a weight loss programme?</strong></p>
        {{textarea:safety_concerns:Any safety concerns:}}
    </div>

    <!-- Social Support -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Social Support</h3>
        <p><strong>Do you have support from family or friends for your weight loss journey?</strong></p>
        {{select:social_support:Social support available?:Yes, very supportive,Yes, somewhat supportive,No support,Not applicable}}
        <p><strong>Who in your household will be supporting you?</strong></p>
        {{textarea:household_support:Household support details:}}
    </div>

    <!-- Additional Information -->
    <div style="margin-bottom: 20px; border: 1px solid #ddd; padding: 15px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Additional Information</h3>
        <p><strong>Is there anything else you think we should know about your weight, health, or lifestyle?</strong></p>
        {{textarea:additional_information:Additional information:}}
    </div>

    <!-- Consent and Signature -->
    <div style="margin-bottom: 20px; border: 2px solid #005EB8; padding: 15px; background-color: #f0f8ff;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Consent and Declaration</h3>
        <p>I confirm that:</p>
        <ul>
            <li>The information I have provided is accurate to the best of my knowledge</li>
            <li>I understand that this consultation is part of an NHS weight management service</li>
            <li>I consent to my information being shared with my GP and relevant healthcare professionals as necessary</li>
            <li>I understand that this is a consultation only and any weight loss programme will require further assessment</li>
            <li>I have read and understood the privacy notice</li>
        </ul>
        <p><strong>I consent to participate in the weight loss consultation:</strong></p>
        {{checkbox:consent_given:I consent to participate in this weight loss consultation}}
        <p style="margin-top: 20px;"><strong>Patient Signature:</strong></p>
        {{signature:patient_signature:Patient Signature}}
        <p style="margin-top: 15px;"><strong>Date:</strong> {{input:signature_date:Date:date}}</p>
    </div>

    <!-- Healthcare Professional Section -->
    <div style="margin-top: 30px; border-top: 2px solid #005EB8; padding-top: 20px;">
        <h3 style="color: #005EB8; margin-top: 0; font-size: 14pt;">Healthcare Professional Assessment</h3>
        <p><strong>Assessment Notes:</strong></p>
        {{textarea:assessment_notes:Clinical assessment and notes:}}
        <p><strong>Risk Assessment:</strong></p>
        {{select:risk_level:Risk level:Low risk,Medium risk,High risk}}
        {{textarea:risk_assessment_details:Risk assessment details:}}
        <p><strong>Recommended Action Plan:</strong></p>
        {{textarea:action_plan:Recommended action plan:}}
        <p><strong>Follow-up Required:</strong></p>
        {{checkbox:follow_up_required:Follow-up appointment required}}
        <p><strong>Follow-up Date:</strong></p>
        {{input:follow_up_date:Follow-up date:date}}
        <p style="margin-top: 20px;"><strong>Healthcare Professional Name:</strong> {{doctor_name}}</p>
        <p><strong>Job Title:</strong> {{doctor_specialization}}</p>
        <p><strong>GMC Number (if applicable):</strong> {{doctor_gmc}}</p>
        <p><strong>Signature:</strong></p>
        {{signature:healthcare_professional_signature:Healthcare Professional Signature}}
        <p style="margin-top: 15px;"><strong>Date:</strong> {{input:professional_signature_date:Date:date}}</p>
        <p><strong>Clinic/Practice:</strong> {{clinic_name}}</p>
        <p><strong>Address:</strong> {{clinic_address}}</p>
        <p><strong>Contact:</strong> {{clinic_phone}}</p>
    </div>

    <!-- Footer -->
    <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; text-align: center; font-size: 10pt; color: #666;">
        <p>This form complies with NHS England Weight Management Guidelines and CQC Standards</p>
        <p>Document Version: 1.0 | Last Updated: {{current_date}}</p>
    </div>

</div>
HTML;

        Template::create([
            'name' => 'Weight Loss Consultation Form',
            'type' => 'form',
            'content' => $content,
            'created_by' => $createdBy,
            'is_system' => true,
            'is_active' => true,
        ]);

        $this->command->info('Weight Loss Consultation Form template created successfully!');
    }
}
