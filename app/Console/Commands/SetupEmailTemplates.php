<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;

class SetupEmailTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:email-templates {--force : Force update even if templates exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up all essential email templates for the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up email templates...');
        $this->newLine();
        
        $templates = $this->getTemplates();
        
        $created = 0;
        $updated = 0;
        $skipped = 0;
        
        foreach ($templates as $template) {
            try {
                $existing = EmailTemplate::where('name', $template['name'])->first();
                
                if ($existing) {
                    if ($this->option('force')) {
                        $existing->update($template);
                        $updated++;
                        $this->line("✓ Updated: {$template['name']}");
                    } else {
                        $skipped++;
                        $this->line("⊘ Skipped: {$template['name']} (already exists)");
                    }
                } else {
                    EmailTemplate::create($template);
                    $created++;
                    $this->line("✓ Created: {$template['name']}");
                }
            } catch (\Exception $e) {
                $this->error("✗ Error with {$template['name']}: " . $e->getMessage());
                continue;
            }
        }
        
        $this->newLine();
        $this->info('Email template setup completed!');
        $this->table(
            ['Action', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total', EmailTemplate::count()],
            ]
        );
        
        return 0;
    }
    
    /**
     * Get all email templates to set up.
     *
     * @return array
     */
    protected function getTemplates(): array
    {
        return [
            // Two-Factor Authentication Template (Critical) - All roles
            [
                'name' => 'two_factor_code',
                'subject' => 'Your {{ hospital_name }} Two-Factor Authentication Code',
                'body' => '<p>Hello {{ user_name }},</p>
<p>You are receiving this email because a two-factor authentication code was requested for your account on {{ hospital_name }}.</p>
<p><strong>Your verification code:</strong><br>
<span style="font-size: 24px; font-weight: bold; letter-spacing: 2px; color: #007bff;">{{ verification_code }}</span></p>
<p>This code will expire in {{ expires_minutes }} minutes.</p>
<p>If you did not request this code, you can ignore this email. For your security, never share this code with anyone.</p>
<p>Thanks,<br>{{ hospital_name }}</p>',
                'description' => 'Two-factor authentication code sent to users during login or when enabling 2FA.',
                'category' => 'security',
                'target_roles' => null, // All roles
                'status' => 'active',
                'variables' => [
                    'user_name' => 'User\'s full name',
                    'verification_code' => '6-digit verification code',
                    'expires_minutes' => 'Code expiration time in minutes',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null, // Will use hospital settings
                'sender_email' => null, // Will use hospital settings
            ],
            
            // Welcome Email - All roles
            [
                'name' => 'welcome_email',
                'subject' => 'Welcome to {{ hospital_name }}',
                'body' => '<p>Hello {{ name }},</p>
<p>Welcome to {{ hospital_name }}! Your account has been successfully created.</p>
<p><strong>Account Information:</strong></p>
<ul>
<li>Username: {{ username }}</li>
<li>Account Number: {{ account_number }}</li>
</ul>
<p>You can now log in to your account using your email and password.</p>
<p>If you have any questions, please don\'t hesitate to contact us.</p>
<p>Best regards,<br>{{ hospital_name }} Team</p>',
                'description' => 'Welcome email sent to new users when their account is created.',
                'category' => 'welcome',
                'target_roles' => null, // All roles
                'status' => 'active',
                'variables' => [
                    'name' => 'User\'s full name',
                    'username' => 'Username',
                    'account_number' => 'Account number',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Password Reset Email - All roles
            [
                'name' => 'password_reset',
                'subject' => 'Reset Your Password - {{ hospital_name }}',
                'body' => '<p>Hello {{ name }},</p>
<p>You are receiving this email because we received a password reset request for your account.</p>
<p><strong>To reset your password, click the link below:</strong></p>
<p><a href="{{ reset_link }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a></p>
<p>Or copy and paste this URL into your browser: {{ reset_link }}</p>
<p>This password reset link will expire in 60 minutes.</p>
<p>If you did not request a password reset, no further action is required.</p>
<p>Best regards,<br>{{ hospital_name }} Team</p>',
                'description' => 'Password reset email sent to users when they request a password reset.',
                'category' => 'authentication',
                'target_roles' => null, // All roles
                'status' => 'active',
                'variables' => [
                    'name' => 'User\'s full name',
                    'reset_link' => 'Password reset URL with token',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Patient Welcome Email - Patient role
            [
                'name' => 'patient_password_reset',
                'subject' => 'Reset Your Patient Portal Password - {{ hospital_name }}',
                'body' => '<p>Hello {{ patient_name }},</p>
<p>You are receiving this email because we received a password reset request for your patient portal account.</p>
<p><strong>To reset your password, click the link below:</strong></p>
<p><a href="{{ reset_url }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a></p>
<p>Or copy and paste this URL into your browser: {{ reset_url }}</p>
<p>This password reset link will expire in {{ expiry_minutes }} minutes.</p>
<p>If you did not request a password reset, please contact our support team at {{ support_email }} immediately.</p>
<p>Access your patient portal: <a href="{{ portal_url }}">{{ portal_url }}</a></p>
<p>Best regards,<br>{{ hospital_name }} Patient Services</p>',
                'description' => 'Password reset email for patient portal users.',
                'category' => 'authentication',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'reset_url' => 'Password reset URL with token',
                    'reset_token' => 'Password reset token',
                    'patient_email' => 'Patient\'s email address',
                    'hospital_name' => 'Hospital/clinic name',
                    'expiry_minutes' => 'Token expiry time in minutes',
                    'support_email' => 'Hospital support email',
                    'portal_url' => 'Patient portal login URL',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Admin Password Reset - Staff roles (admin, doctor, nurse, receptionist, pharmacist, technician, staff)
            [
                'name' => 'admin_password_reset',
                'subject' => 'Password Reset Request - {{ hospital_name }}',
                'body' => '<p>Hello {{ user_name }},</p>
<p>Your password has been reset by an administrator at {{ hospital_name }}.</p>
<p><strong>Reason for reset:</strong> {{ reason }}</p>
<p><strong>To set your new password, click the link below:</strong></p>
<p><a href="{{ reset_link }}" style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Set New Password</a></p>
<p>Or copy and paste this URL into your browser: {{ reset_link }}</p>
<p>This password reset link will expire in {{ expiry_hours }} hours.</p>
<p><strong>Important:</strong> You will be required to change your password on your next login for security purposes.</p>
<p>If you did not request this password reset, please contact your system administrator immediately.</p>
<p>Best regards,<br>{{ hospital_name }} IT Support</p>',
                'description' => 'Password reset email sent by admin to staff users.',
                'category' => 'authentication',
                'target_roles' => ['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'staff'],
                'status' => 'active',
                'variables' => [
                    'user_name' => 'User\'s full name',
                    'reason' => 'Reason for password reset',
                    'reset_link' => 'Password reset URL with token',
                    'expiry_hours' => 'Token expiry time in hours',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Admin Welcome Credentials - Staff roles (admin, doctor, nurse, receptionist, pharmacist, technician, staff)
            [
                'name' => 'admin_welcome_credentials',
                'subject' => 'Welcome to {{ hospital_name }} - Your Login Credentials',
                'body' => '<p>Hello {{ user_name }},</p>
<p>Welcome to {{ hospital_name }}! Your account has been successfully created.</p>
<p><strong>Your Account Information:</strong></p>
<ul>
<li><strong>Name:</strong> {{ user_name }}</li>
<li><strong>Email:</strong> {{ user_email }}</li>
<li><strong>Role:</strong> {{ user_role }}</li>
<li><strong>Employee ID:</strong> {{ employee_id }}</li>
</ul>
<p><strong>To access your account:</strong></p>
<ol>
<li>Visit the login portal: <a href="{{ portal_link }}">{{ portal_link }}</a></li>
<li>Use your email address: <strong>{{ user_email }}</strong></li>
<li>Set your password by clicking the link below:</li>
</ol>
<p><a href="{{ reset_link }}" style="background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Set Your Password</a></p>
<p>Or copy and paste this URL into your browser: {{ reset_link }}</p>
<p><strong>Important:</strong> This link will expire in {{ expiry_hours }} hours. Please set your password as soon as possible.</p>
<p><strong>Security Note:</strong> For your security, never share your login credentials with anyone. If you have any questions or concerns, please contact your system administrator.</p>
<p>We look forward to working with you!</p>
<p>Best regards,<br>{{ hospital_name }} IT Support Team</p>',
                'description' => 'Welcome email with credentials sent by admin to new staff users.',
                'category' => 'welcome',
                'target_roles' => ['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'technician', 'staff'],
                'status' => 'active',
                'variables' => [
                    'user_name' => 'User\'s full name',
                    'user_email' => 'User\'s email address',
                    'user_role' => 'User\'s role',
                    'employee_id' => 'Employee ID',
                    'portal_link' => 'Login portal URL',
                    'reset_link' => 'Password setup URL with token',
                    'expiry_hours' => 'Token expiry time in hours',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Patient Welcome Email - Patient role
            [
                'name' => 'patient_welcome',
                'subject' => 'Welcome to {{ hospital_name }} - Your Health Journey Begins Here',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Welcome to {{ hospital_name }}! We are honored that you have chosen us for your healthcare needs.</p>
<p>Your patient account has been successfully created with the following information:</p>
<ul>
<li><strong>Patient ID:</strong> {{ patient_id }}</li>
<li><strong>Registration Date:</strong> {{ registration_date }}</li>
</ul>
<p><strong>What\'s Next:</strong></p>
<ol>
<li>Access your patient portal at <a href="{{ patient_portal_url }}">{{ patient_portal_url }}</a></li>
<li>Complete your medical history and insurance information</li>
<li>Schedule your first appointment online</li>
</ol>
<p><strong>Our Services:</strong></p>
<ul>
<li>24/7 Emergency Care</li>
<li>Specialized Medical Departments</li>
<li>Online Appointment Scheduling</li>
<li>Patient Portal Access</li>
<li>Pharmacy Services</li>
</ul>
<p><strong>Contact Information:</strong></p>
<ul>
<li>Phone: {{ contact_phone }}</li>
<li>Email: {{ contact_email }}</li>
</ul>
<p>Our dedicated staff is here to provide you with exceptional care. If you have any questions, please don\'t hesitate to contact us.</p>
<p>Welcome to the {{ hospital_name }} family!</p>
<p>Best regards,<br>{{ hospital_name }} Patient Services Team</p>',
                'description' => 'Welcome email sent to new patients when they register.',
                'category' => 'welcome',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID number',
                    'registration_date' => 'Registration date',
                    'patient_portal_url' => 'Patient portal login URL',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone number',
                    'contact_email' => 'Hospital email address',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Appointment Confirmation
            [
                'name' => 'appointment_confirmation',
                'subject' => 'Appointment Confirmed - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Your appointment has been confirmed!</p>
<p><strong>Appointment Details:</strong></p>
<ul>
<li><strong>Doctor:</strong> {{ doctor_name }} ({{ doctor_specialization }})</li>
<li><strong>Date:</strong> {{ appointment_date }}</li>
<li><strong>Time:</strong> {{ appointment_time }}</li>
<li><strong>Type:</strong> {{ appointment_type }}</li>
<li><strong>Department:</strong> {{ department }}</li>
<li><strong>Appointment ID:</strong> {{ appointment_id }}</li>
</ul>
<p><strong>Notes:</strong> {{ notes }}</p>
<p><strong>Hospital Information:</strong></p>
<ul>
<li>Address: {{ hospital_address }}</li>
<li>Phone: {{ hospital_phone }}</li>
</ul>
<p>Please arrive 15 minutes early for check-in. If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
<p>We look forward to seeing you!</p>
<p>Best regards,<br>{{ hospital_name }} Appointment Team</p>',
                'description' => 'Appointment confirmation email sent to patients.',
                'category' => 'appointments',
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'patient_email' => 'Patient\'s email',
                    'doctor_name' => 'Doctor\'s name',
                    'doctor_specialization' => 'Doctor\'s specialization',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'appointment_type' => 'Type of appointment',
                    'department' => 'Department name',
                    'appointment_id' => 'Appointment ID',
                    'notes' => 'Additional notes',
                    'hospital_name' => 'Hospital/clinic name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Appointment Reminder
            [
                'name' => 'appointment_reminder',
                'subject' => 'Reminder: Appointment in {{ days_before }} day(s) - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>This is a reminder that you have an appointment scheduled:</p>
<p><strong>Appointment Details:</strong></p>
<ul>
<li><strong>Doctor:</strong> {{ doctor_name }}</li>
<li><strong>Date:</strong> {{ appointment_date }}</li>
<li><strong>Time:</strong> {{ appointment_time }}</li>
<li><strong>Appointment ID:</strong> {{ appointment_id }}</li>
</ul>
<p><strong>Need to reschedule or cancel?</strong></p>
<ul>
<li><a href="{{ reschedule_url }}">Reschedule Appointment</a></li>
<li><a href="{{ cancel_url }}">Cancel Appointment</a></li>
</ul>
<p>Please arrive 15 minutes early for check-in. If you have any questions, please contact us.</p>
<p>We look forward to seeing you!</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Appointment reminder email sent to patients.',
                'category' => 'appointments',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'days_before' => 'Number of days before appointment',
                    'appointment_id' => 'Appointment ID',
                    'reschedule_url' => 'Reschedule URL',
                    'cancel_url' => 'Cancel URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Appointment Cancellation
            [
                'name' => 'appointment_cancellation',
                'subject' => 'Appointment Cancelled - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Your appointment has been cancelled.</p>
<p><strong>Cancelled Appointment Details:</strong></p>
<ul>
<li><strong>Doctor:</strong> {{ doctor_name }}</li>
<li><strong>Date:</strong> {{ appointment_date }}</li>
<li><strong>Time:</strong> {{ appointment_time }}</li>
<li><strong>Department:</strong> {{ department }}</li>
</ul>
<p><strong>Reason:</strong> {{ cancellation_reason }}</p>
<p>If you would like to schedule a new appointment, please visit: <a href="{{ reschedule_url }}">{{ reschedule_url }}</a></p>
<p>For any questions or concerns, please contact us at {{ hospital_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Appointment cancellation notification sent to patients.',
                'category' => 'appointments',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'cancellation_reason' => 'Reason for cancellation',
                    'reschedule_url' => 'Reschedule URL',
                    'hospital_name' => 'Hospital/clinic name',
                    'hospital_phone' => 'Hospital phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Appointment Completion
            [
                'name' => 'appointment_completion',
                'subject' => 'Thank You for Visiting {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Thank you for visiting {{ hospital_name }} today!</p>
<p><strong>Appointment Summary:</strong></p>
<ul>
<li><strong>Doctor:</strong> {{ doctor_name }}</li>
<li><strong>Date:</strong> {{ appointment_date }}</li>
<li><strong>Time:</strong> {{ appointment_time }}</li>
<li><strong>Department:</strong> {{ department }}</li>
</ul>
<p><strong>Diagnosis:</strong> {{ diagnosis }}</p>
<p><strong>Prescription:</strong> {{ prescription }}</p>
<p><strong>Follow-up Instructions:</strong> {{ follow_up_instructions }}</p>
<p><strong>Next Appointment:</strong> {{ next_appointment_date }}</p>
<p>Your medical records have been updated in your patient portal. You can access them anytime at your convenience.</p>
<p>If you have any questions or concerns, please don\'t hesitate to contact us.</p>
<p>We hope you have a speedy recovery!</p>
<p>Best regards,<br>{{ hospital_name }} Team</p>',
                'description' => 'Appointment completion notification sent to patients.',
                'category' => 'appointments',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'diagnosis' => 'Diagnosis',
                    'prescription' => 'Prescription',
                    'follow_up_instructions' => 'Follow-up instructions',
                    'next_appointment_date' => 'Next appointment date',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Appointment Reschedule
            [
                'name' => 'appointment_reschedule',
                'subject' => 'Appointment Rescheduled - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Your appointment has been rescheduled.</p>
<p><strong>Previous Appointment:</strong></p>
<ul>
<li><strong>Date:</strong> {{ old_date }}</li>
<li><strong>Time:</strong> {{ old_time }}</li>
</ul>
<p><strong>New Appointment:</strong></p>
<ul>
<li><strong>Doctor:</strong> {{ doctor_name }}</li>
<li><strong>Date:</strong> {{ new_date }}</li>
<li><strong>Time:</strong> {{ new_time }}</li>
<li><strong>Department:</strong> {{ department }}</li>
</ul>
<p><strong>Reason:</strong> {{ reschedule_reason }}</p>
<p>Please arrive 15 minutes early for check-in. If you need to make any changes, please contact us at {{ hospital_phone }}.</p>
<p>We look forward to seeing you!</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Appointment reschedule notification sent to patients.',
                'category' => 'appointments',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'old_date' => 'Previous appointment date',
                    'old_time' => 'Previous appointment time',
                    'new_date' => 'New appointment date',
                    'new_time' => 'New appointment time',
                    'department' => 'Department name',
                    'reschedule_reason' => 'Reason for rescheduling',
                    'hospital_name' => 'Hospital/clinic name',
                    'hospital_phone' => 'Hospital phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Test Results Ready
            [
                'name' => 'test_results_ready',
                'subject' => 'Your Test Results Are Ready - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Your test results are now available!</p>
<p><strong>Test Information:</strong></p>
<ul>
<li><strong>Test Name:</strong> {{ test_name }}</li>
<li><strong>Test Type:</strong> {{ test_type }}</li>
<li><strong>Test Date:</strong> {{ test_date }}</li>
<li><strong>Results Date:</strong> {{ result_date }}</li>
<li><strong>Ordered By:</strong> {{ doctor_name }}</li>
</ul>
<p>You can view your results in your patient portal: <a href="{{ patient_portal_url }}">{{ patient_portal_url }}</a></p>
<p>If you have any questions about your results, please contact your doctor or call us at {{ contact_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }} Laboratory</p>',
                'description' => 'Test results ready notification sent to patients.',
                'category' => 'lab_reports',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'test_name' => 'Test name',
                    'test_type' => 'Test type',
                    'test_date' => 'Test date',
                    'result_date' => 'Results date',
                    'doctor_name' => 'Doctor\'s name',
                    'patient_portal_url' => 'Patient portal URL',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Prescription Ready
            [
                'name' => 'prescription_ready',
                'subject' => 'Your Prescription is Ready - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Your prescription is ready for pickup!</p>
<p><strong>Prescription Information:</strong></p>
<ul>
<li><strong>Prescription ID:</strong> {{ prescription_id }}</li>
<li><strong>Prescribed By:</strong> {{ doctor_name }}</li>
<li><strong>Ready Date:</strong> {{ ready_date }}</li>
</ul>
<p><strong>Pickup Instructions:</strong></p>
<p>{{ pickup_instructions }}</p>
<p><strong>Pharmacy Hours:</strong> {{ pharmacy_hours }}</p>
<p>Please bring a valid ID when picking up your prescription.</p>
<p>For questions about your prescription, please contact our pharmacy at {{ pharmacy_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }} Pharmacy</p>',
                'description' => 'Prescription ready notification sent to patients.',
                'category' => 'prescriptions',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'prescription_id' => 'Prescription ID',
                    'doctor_name' => 'Doctor\'s name',
                    'ready_date' => 'Ready date',
                    'pickup_instructions' => 'Pickup instructions',
                    'pharmacy_hours' => 'Pharmacy hours',
                    'hospital_name' => 'Hospital/clinic name',
                    'pharmacy_phone' => 'Pharmacy phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Payment Reminder
            [
                'name' => 'payment_reminder',
                'subject' => 'Payment Reminder - Invoice {{ invoice_number }} - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>This is a friendly reminder about your outstanding balance.</p>
<p><strong>Invoice Details:</strong></p>
<ul>
<li><strong>Invoice Number:</strong> {{ invoice_number }}</li>
<li><strong>Amount Due:</strong> ${{ amount_due }}</li>
<li><strong>Due Date:</strong> {{ due_date }}</li>
<li><strong>Service:</strong> {{ service_description }}</li>
</ul>
<p>You can make a payment online at: <a href="{{ payment_url }}">{{ payment_url }}</a></p>
<p>If you have already made a payment, please disregard this notice. For billing questions, please contact us at {{ billing_phone }}.</p>
<p>Thank you for your prompt attention to this matter.</p>
<p>Best regards,<br>{{ hospital_name }} Billing Department</p>',
                'description' => 'Payment reminder sent to patients.',
                'category' => 'billing',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'invoice_number' => 'Invoice number',
                    'amount_due' => 'Amount due',
                    'due_date' => 'Due date',
                    'service_description' => 'Service description',
                    'payment_url' => 'Payment URL',
                    'hospital_name' => 'Hospital/clinic name',
                    'billing_phone' => 'Billing phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Discharge Instructions
            [
                'name' => 'discharge_instructions',
                'subject' => 'Discharge Instructions - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>Please follow these discharge instructions carefully.</p>
<p><strong>Discharge Information:</strong></p>
<ul>
<li><strong>Discharge Date:</strong> {{ discharge_date }}</li>
<li><strong>Attending Doctor:</strong> {{ attending_doctor }}</li>
<li><strong>Follow-up Date:</strong> {{ follow_up_date }}</li>
</ul>
<p><strong>Instructions:</strong></p>
<p>{{ instructions }}</p>
<p><strong>Medications:</strong></p>
<p>{{ medications }}</p>
<p><strong>Emergency Contact:</strong> {{ emergency_contact }}</p>
<p>If you experience any complications or have questions, please contact us immediately.</p>
<p>We wish you a speedy recovery!</p>
<p>Best regards,<br>{{ hospital_name }} Care Team</p>',
                'description' => 'Discharge instructions sent to patients.',
                'category' => 'patient_care',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'discharge_date' => 'Discharge date',
                    'attending_doctor' => 'Attending doctor',
                    'follow_up_date' => 'Follow-up date',
                    'instructions' => 'Discharge instructions',
                    'medications' => 'Medications',
                    'emergency_contact' => 'Emergency contact',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Staff New Patient Registration
            [
                'name' => 'staff_new_patient_registration',
                'subject' => 'New Patient Registration - {{ hospital_name }}',
                'body' => '<p>Hello {{ staff_name }},</p>
<p>A new patient has been registered in the system.</p>
<p><strong>Patient Information:</strong></p>
<ul>
<li><strong>Name:</strong> {{ patient_name }}</li>
<li><strong>Patient ID:</strong> {{ patient_id }}</li>
<li><strong>Registration Date:</strong> {{ registration_date }}</li>
<li><strong>Phone:</strong> {{ patient_phone }}</li>
<li><strong>Email:</strong> {{ patient_email }}</li>
</ul>
<p>View patient details: <a href="{{ patient_url }}">{{ patient_url }}</a></p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to staff about new patient registration.',
                'category' => 'staff_notifications',
                'target_roles' => ['admin', 'receptionist', 'staff'],
                'status' => 'active',
                'variables' => [
                    'staff_name' => 'Staff member\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID',
                    'registration_date' => 'Registration date and time',
                    'patient_phone' => 'Patient phone',
                    'patient_email' => 'Patient email',
                    'patient_url' => 'Patient detail URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor New Appointment
            [
                'name' => 'doctor_new_appointment',
                'subject' => 'New Appointment Scheduled - {{ hospital_name }}',
                'body' => '<p>Hello Dr. {{ doctor_name }},</p>
<p>You have a new appointment scheduled.</p>
<p><strong>Appointment Details:</strong></p>
<ul>
<li><strong>Patient:</strong> {{ patient_name }}</li>
<li><strong>Phone:</strong> {{ patient_phone }}</li>
<li><strong>Date:</strong> {{ appointment_date }}</li>
<li><strong>Time:</strong> {{ appointment_time }}</li>
<li><strong>Type:</strong> {{ appointment_type }}</li>
<li><strong>Notes:</strong> {{ notes }}</li>
</ul>
<p>View appointment: <a href="{{ appointment_url }}">{{ appointment_url }}</a></p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to doctors about new appointments.',
                'category' => 'staff_notifications',
                'target_roles' => ['doctor'],
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'patient_phone' => 'Patient phone',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'appointment_type' => 'Appointment type',
                    'notes' => 'Appointment notes',
                    'appointment_url' => 'Appointment detail URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Appointment Cancelled
            [
                'name' => 'doctor_appointment_cancelled',
                'subject' => 'Appointment Cancelled - {{ hospital_name }}',
                'body' => '<p>Hello Dr. {{ doctor_name }},</p>
<p>An appointment has been cancelled.</p>
<p><strong>Appointment Details:</strong></p>
<ul>
<li><strong>Patient:</strong> {{ patient_name }}</li>
<li><strong>Date:</strong> {{ appointment_date }}</li>
<li><strong>Time:</strong> {{ appointment_time }}</li>
<li><strong>Department:</strong> {{ department }}</li>
<li><strong>Reason:</strong> {{ cancellation_reason }}</li>
</ul>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to doctors when appointments are cancelled.',
                'category' => 'staff_notifications',
                'target_roles' => ['doctor'],
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'appointment_date' => 'Appointment date',
                    'appointment_time' => 'Appointment time',
                    'department' => 'Department name',
                    'cancellation_reason' => 'Cancellation reason',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Appointment Rescheduled
            [
                'name' => 'doctor_appointment_rescheduled',
                'subject' => 'Appointment Rescheduled - {{ hospital_name }}',
                'body' => '<p>Hello Dr. {{ doctor_name }},</p>
<p>An appointment has been rescheduled.</p>
<p><strong>Patient:</strong> {{ patient_name }}</p>
<p><strong>Previous Appointment:</strong></p>
<ul>
<li><strong>Date:</strong> {{ old_date }}</li>
<li><strong>Time:</strong> {{ old_time }}</li>
</ul>
<p><strong>New Appointment:</strong></p>
<ul>
<li><strong>Date:</strong> {{ new_date }}</li>
<li><strong>Time:</strong> {{ new_time }}</li>
<li><strong>Department:</strong> {{ department }}</li>
</ul>
<p><strong>Reason:</strong> {{ reschedule_reason }}</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to doctors when appointments are rescheduled.',
                'category' => 'staff_notifications',
                'target_roles' => ['doctor'],
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'old_date' => 'Previous appointment date',
                    'old_time' => 'Previous appointment time',
                    'new_date' => 'New appointment date',
                    'new_time' => 'New appointment time',
                    'department' => 'Department name',
                    'reschedule_reason' => 'Reschedule reason',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Room Change
            [
                'name' => 'doctor_room_change',
                'subject' => 'Doctor Room Change - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>We would like to inform you about a change in your doctor\'s room.</p>
<p><strong>Doctor:</strong> {{ doctor_name }}</p>
<p><strong>Room Change:</strong></p>
<ul>
<li><strong>Old Room:</strong> {{ old_room }}</li>
<li><strong>New Room:</strong> {{ new_room }}</li>
</ul>
<p>Please update your records. If you have any questions, please contact us at {{ contact_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to patients when doctor room changes.',
                'category' => 'patient_notifications',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'old_room' => 'Old room number',
                    'new_room' => 'New room number',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone',
                    'notification_date' => 'Notification date',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Contact Update
            [
                'name' => 'doctor_contact_update',
                'subject' => 'Doctor Contact Information Updated - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>We would like to inform you that your doctor\'s contact information has been updated.</p>
<p><strong>Doctor:</strong> {{ doctor_name }}</p>
<p><strong>New Phone:</strong> {{ new_phone }}</p>
<p>Please update your records. If you have any questions, please contact us at {{ contact_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to patients when doctor contact updates.',
                'category' => 'patient_notifications',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'new_phone' => 'New phone number',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone',
                    'notification_date' => 'Notification date',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Department Change
            [
                'name' => 'doctor_department_change',
                'subject' => 'Doctor Department Change - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>We would like to inform you that your doctor\'s department has changed.</p>
<p><strong>Doctor:</strong> {{ doctor_name }}</p>
<p><strong>New Department:</strong> {{ new_department }}</p>
<p>Please update your records. If you have any questions, please contact us at {{ contact_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to patients when doctor department changes.',
                'category' => 'patient_notifications',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'new_department' => 'New department name',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone',
                    'notification_date' => 'Notification date',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Unavailable
            [
                'name' => 'doctor_unavailable',
                'subject' => 'Doctor Unavailable - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>We regret to inform you that Dr. {{ doctor_name }} is currently unavailable.</p>
<p>We understand this may cause inconvenience. Please contact us to reschedule your appointment or to be assigned to another doctor.</p>
<p><strong>Options:</strong></p>
<ul>
<li>Reschedule your appointment: <a href="{{ rebooking_url }}">{{ rebooking_url }}</a></li>
<li>Contact support: {{ support_email }}</li>
</ul>
<p>If you have any questions, please contact us at {{ contact_phone }}.</p>
<p>We apologize for any inconvenience and thank you for your understanding.</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to patients when doctor is unavailable.',
                'category' => 'patient_notifications',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'rebooking_url' => 'Rebooking URL',
                    'support_email' => 'Support email',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone',
                    'notification_date' => 'Notification date',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Schedule Update
            [
                'name' => 'doctor_schedule_update',
                'subject' => 'Doctor Schedule Update - {{ hospital_name }}',
                'body' => '<p>Dear {{ patient_name }},</p>
<p>We would like to inform you that Dr. {{ doctor_name }}\'s schedule has been updated.</p>
<p><strong>New Schedule:</strong></p>
<pre>{{ new_schedule }}</pre>
<p>If you need to reschedule your appointment, please visit: <a href="{{ rebooking_url }}">{{ rebooking_url }}</a></p>
<p>For questions, please contact us at {{ contact_phone }}.</p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to patients when doctor schedule updates.',
                'category' => 'patient_notifications',
                'target_roles' => ['patient'],
                'status' => 'active',
                'variables' => [
                    'patient_name' => 'Patient\'s full name',
                    'doctor_name' => 'Doctor\'s name',
                    'new_schedule' => 'New schedule',
                    'rebooking_url' => 'Rebooking URL',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_phone' => 'Hospital phone',
                    'notification_date' => 'Notification date',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Emergency Contact Notification
            [
                'name' => 'emergency_contact_notification',
                'subject' => 'Emergency Contact Notification - {{ hospital_name }}',
                'body' => '<p>Dear {{ contact_name }},</p>
<p>This is an emergency notification regarding {{ patient_name }}.</p>
<p><strong>Emergency Information:</strong></p>
<ul>
<li><strong>Emergency Type:</strong> {{ emergency_type }}</li>
<li><strong>Date/Time:</strong> {{ emergency_date }}</li>
<li><strong>Attending Doctor:</strong> {{ attending_doctor }}</li>
<li><strong>Patient Condition:</strong> {{ patient_condition }}</li>
</ul>
<p><strong>Hospital Information:</strong></p>
<ul>
<li><strong>Hospital:</strong> {{ hospital_name }}</li>
<li><strong>Address:</strong> {{ hospital_address }}</li>
<li><strong>Phone:</strong> {{ hospital_phone }}</li>
<li><strong>Department:</strong> {{ department }}</li>
<li><strong>Room:</strong> {{ room_number }}</li>
</ul>
<p><strong>Visiting Information:</strong></p>
<ul>
<li><strong>Visiting Hours:</strong> {{ visiting_hours }}</li>
<li><strong>Parking:</strong> {{ parking_info }}</li>
<li><strong>Nurse\'s Station:</strong> {{ nurses_station_phone }}</li>
</ul>
<p>Please contact us if you have any questions or concerns.</p>
<p>Best regards,<br>{{ hospital_name }} Emergency Team</p>',
                'description' => 'Emergency contact notification sent to patient\'s emergency contacts.',
                'category' => 'emergency',
                'target_roles' => null, // Public/Anyone (emergency contacts)
                'status' => 'active',
                'variables' => [
                    'contact_name' => 'Emergency contact name',
                    'patient_name' => 'Patient\'s full name',
                    'emergency_type' => 'Emergency type',
                    'emergency_date' => 'Emergency date and time',
                    'attending_doctor' => 'Attending doctor',
                    'patient_condition' => 'Patient condition',
                    'admission_date' => 'Admission date',
                    'admission_time' => 'Admission time',
                    'hospital_name' => 'Hospital/clinic name',
                    'hospital_address' => 'Hospital address',
                    'hospital_phone' => 'Hospital phone',
                    'department' => 'Department name',
                    'room_number' => 'Room number',
                    'visiting_hours' => 'Visiting hours',
                    'parking_info' => 'Parking information',
                    'nurses_station_phone' => 'Nurse\'s station phone',
                    'patient_services_phone' => 'Patient services phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Emergency Admission Alert
            [
                'name' => 'emergency_admission_alert',
                'subject' => 'URGENT: Emergency Admission Alert - {{ hospital_name }}',
                'body' => '<p>Hello {{ staff_name }},</p>
<p><strong>URGENT - Emergency Admission Alert</strong></p>
<p><strong>Patient Information:</strong></p>
<ul>
<li><strong>Name:</strong> {{ patient_name }}</li>
<li><strong>Patient ID:</strong> {{ patient_id }}</li>
<li><strong>Age:</strong> {{ patient_age }}</li>
<li><strong>Emergency Type:</strong> {{ emergency_type }}</li>
<li><strong>Priority Level:</strong> {{ priority_level }}</li>
</ul>
<p><strong>Admission Details:</strong></p>
<ul>
<li><strong>Admission Time:</strong> {{ admission_time }}</li>
<li><strong>Attending Doctor:</strong> {{ attending_doctor }}</li>
<li><strong>Room Assigned:</strong> {{ room_assigned }}</li>
<li><strong>Symptoms:</strong> {{ symptoms }}</li>
</ul>
<p><strong>Vital Signs:</strong></p>
<pre>{{ vital_signs }}</pre>
<p><strong>Medical History:</strong> {{ medical_history }}</p>
<p><strong>Emergency Contact:</strong> {{ emergency_contact }}</p>
<p>View patient: <a href="{{ patient_url }}">{{ patient_url }}</a></p>
<p><strong>ACTION REQUIRED - Please respond immediately!</strong></p>
<p>Best regards,<br>{{ hospital_name }} Emergency Team</p>',
                'description' => 'Emergency admission alert sent to critical staff members.',
                'category' => 'emergency',
                'target_roles' => ['admin', 'doctor', 'nurse'],
                'status' => 'active',
                'variables' => [
                    'staff_name' => 'Staff member\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID',
                    'patient_age' => 'Patient age',
                    'emergency_type' => 'Emergency type',
                    'priority_level' => 'Priority level',
                    'symptoms' => 'Symptoms',
                    'admission_time' => 'Admission time',
                    'attending_doctor' => 'Attending doctor',
                    'room_assigned' => 'Room assigned',
                    'vital_signs' => 'Vital signs',
                    'emergency_contact' => 'Emergency contact',
                    'medical_history' => 'Medical history',
                    'patient_url' => 'Patient detail URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Critical Care Notification
            [
                'name' => 'critical_care_notification',
                'subject' => 'CRITICAL: Critical Care Notification - {{ hospital_name }}',
                'body' => '<p>Hello {{ department_head_name }},</p>
<p><strong>CRITICAL CARE NOTIFICATION</strong></p>
<p><strong>Patient Information:</strong></p>
<ul>
<li><strong>Name:</strong> {{ patient_name }}</li>
<li><strong>Patient ID:</strong> {{ patient_id }}</li>
<li><strong>Emergency Type:</strong> {{ emergency_type }}</li>
<li><strong>Priority Level:</strong> {{ priority_level }}</li>
</ul>
<p><strong>Condition Summary:</strong> {{ condition_summary }}</p>
<p><strong>Admission Details:</strong></p>
<ul>
<li><strong>Admission Time:</strong> {{ admission_time }}</li>
<li><strong>Attending Doctor:</strong> {{ attending_doctor }}</li>
<li><strong>Department:</strong> {{ department_name }}</li>
<li><strong>Specialist Required:</strong> {{ specialist_required }}</li>
<li><strong>Estimated Treatment Time:</strong> {{ estimated_treatment_time }}</li>
</ul>
<p><strong>Emergency Protocol:</strong> {{ emergency_protocol }}</p>
<p>View patient: <a href="{{ patient_url }}">{{ patient_url }}</a></p>
<p><strong>IMMEDIATE ATTENTION REQUIRED</strong></p>
<p>Best regards,<br>{{ hospital_name }} Critical Care Team</p>',
                'description' => 'Critical care notification sent to department heads and specialists.',
                'category' => 'emergency',
                'target_roles' => ['admin', 'doctor'],
                'status' => 'active',
                'variables' => [
                    'department_head_name' => 'Department head name',
                    'patient_name' => 'Patient\'s full name',
                    'patient_id' => 'Patient ID',
                    'emergency_type' => 'Emergency type',
                    'priority_level' => 'Priority level',
                    'condition_summary' => 'Condition summary',
                    'admission_time' => 'Admission time',
                    'attending_doctor' => 'Attending doctor',
                    'department_name' => 'Department name',
                    'specialist_required' => 'Specialist required',
                    'estimated_treatment_time' => 'Estimated treatment time',
                    'emergency_protocol' => 'Emergency protocol',
                    'patient_url' => 'Patient detail URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Doctor Critical Results
            [
                'name' => 'doctor_critical_results',
                'subject' => 'URGENT: Critical Lab Results - {{ hospital_name }}',
                'body' => '<p>Hello Dr. {{ doctor_name }},</p>
<p><strong>URGENT - Critical Lab Results Available</strong></p>
<p><strong>Patient:</strong> {{ patient_name }}</p>
<p><strong>Test Information:</strong></p>
<ul>
<li><strong>Test Name:</strong> {{ test_name }}</li>
<li><strong>Test Type:</strong> {{ test_type }}</li>
<li><strong>Test Date:</strong> {{ test_date }}</li>
<li><strong>Priority:</strong> {{ priority }}</li>
<li><strong>Status:</strong> {{ status }}</li>
</ul>
<p><strong>Lab Technician:</strong> {{ lab_technician }}</p>
<p><strong>Notes:</strong> {{ notes }}</p>
<p><strong>ACTION REQUIRED - Please review immediately!</strong></p>
<p>View lab report: <a href="{{ lab_report_url }}">{{ lab_report_url }}</a></p>
<p>Best regards,<br>{{ hospital_name }} Laboratory</p>',
                'description' => 'Critical lab results notification sent to doctors.',
                'category' => 'lab_reports',
                'target_roles' => ['doctor'],
                'status' => 'active',
                'variables' => [
                    'doctor_name' => 'Doctor\'s name',
                    'patient_name' => 'Patient\'s full name',
                    'test_name' => 'Test name',
                    'test_type' => 'Test type',
                    'test_date' => 'Test date',
                    'priority' => 'Priority level',
                    'status' => 'Test status',
                    'lab_technician' => 'Lab technician name',
                    'notes' => 'Test notes',
                    'lab_report_url' => 'Lab report URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Contact Auto Reply
            [
                'name' => 'contact_auto_reply',
                'subject' => 'Thank You for Contacting {{ hospital_name }}',
                'body' => '<p>Dear {{ full_name }},</p>
<p>Thank you for contacting {{ hospital_name }}!</p>
<p>We have received your message regarding <strong>{{ subject }}</strong> and our team will review it shortly.</p>
<p><strong>Your Message:</strong></p>
<p>{{ message }}</p>
<p>We typically respond within 24-48 hours during business days. For urgent matters, please call us at {{ contact_phone }}.</p>
<p>If you have any additional information or questions, please feel free to contact us.</p>
<p>Best regards,<br>{{ hospital_name }} Contact Team</p>',
                'description' => 'Auto-reply sent to contact form submissions.',
                'category' => 'contact',
                'target_roles' => null, // Public/Anyone
                'status' => 'active',
                'variables' => [
                    'full_name' => 'Sender\'s full name',
                    'subject' => 'Message subject',
                    'message' => 'Message content',
                    'hospital_name' => 'Hospital/clinic name',
                    'contact_email' => 'Hospital email',
                    'contact_phone' => 'Hospital phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Staff New Contact Message
            [
                'name' => 'staff_new_contact_message',
                'subject' => 'New Contact Form Submission - {{ hospital_name }}',
                'body' => '<p>Hello {{ staff_name }},</p>
<p>A new contact form message has been received.</p>
<p><strong>Contact Information:</strong></p>
<ul>
<li><strong>Name:</strong> {{ full_name }}</li>
<li><strong>Email:</strong> {{ email }}</li>
<li><strong>Phone:</strong> {{ phone }}</li>
<li><strong>Subject:</strong> {{ subject }}</li>
</ul>
<p><strong>Message:</strong></p>
<p>{{ message }}</p>
<p>View message: <a href="{{ inbox_url }}">{{ inbox_url }}</a></p>
<p>Best regards,<br>{{ hospital_name }}</p>',
                'description' => 'Notification sent to staff about new contact form messages.',
                'category' => 'staff_notifications',
                'target_roles' => ['admin', 'receptionist', 'staff'],
                'status' => 'active',
                'variables' => [
                    'staff_name' => 'Staff member\'s name',
                    'full_name' => 'Sender\'s full name',
                    'email' => 'Sender\'s email',
                    'phone' => 'Sender\'s phone',
                    'subject' => 'Message subject',
                    'message' => 'Message content',
                    'inbox_url' => 'Contact message URL',
                    'hospital_name' => 'Hospital/clinic name',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
            
            // Contact Reply
            [
                'name' => 'contact_reply',
                'subject' => 'Re: {{ original_subject }} - {{ hospital_name }}',
                'body' => '<p>Dear {{ full_name }},</p>
<p>Thank you for contacting {{ hospital_name }} regarding <strong>{{ original_subject }}</strong>.</p>
<p><strong>Our Response:</strong></p>
<p><strong>{{ reply_subject }}</strong></p>
<p>{{ reply_message }}</p>
<p>If you have any further questions or concerns, please don\'t hesitate to contact us at {{ support_email }} or call {{ support_phone }}.</p>
<p>We appreciate your feedback and look forward to assisting you.</p>
<p>Best regards,<br>{{ hospital_name }} Support Team</p>',
                'description' => 'Reply sent to contact form senders.',
                'category' => 'contact',
                'target_roles' => null, // Public/Anyone
                'status' => 'active',
                'variables' => [
                    'full_name' => 'Sender\'s full name',
                    'original_subject' => 'Original message subject',
                    'reply_subject' => 'Reply subject',
                    'reply_message' => 'Reply message',
                    'hospital_name' => 'Hospital/clinic name',
                    'support_email' => 'Support email',
                    'support_phone' => 'Support phone',
                ],
                'sender_name' => null,
                'sender_email' => null,
            ],
        ];
    }
}

