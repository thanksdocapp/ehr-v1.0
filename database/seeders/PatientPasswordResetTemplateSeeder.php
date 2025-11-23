<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class PatientPasswordResetTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the template already exists
        if (EmailTemplate::where('name', 'patient_password_reset')->exists()) {
            return;
        }

        EmailTemplate::create([
            'name' => 'patient_password_reset',
            'subject' => 'Reset Your Patient Portal Password - {{hospital_name}}',
            'body' => $this->getEmailBody(),
            'description' => 'Password reset email for patient portal users',
            'category' => 'authentication',
            'status' => 'active',
            'variables' => [
                'patient_name' => "Patient's full name",
                'reset_url' => 'Password reset URL with token',
                'reset_token' => 'Password reset token',
                'patient_email' => "Patient's email address",
                'hospital_name' => 'Hospital name',
                'expiry_minutes' => 'Token expiry time in minutes',
                'support_email' => 'Hospital support email',
                'portal_url' => 'Patient portal login URL'
            ],
            'sender_name' => null, // Will use hospital settings
            'sender_email' => null, // Will use hospital settings
        ]);
    }

    /**
     * Get the email body template.
     */
    private function getEmailBody(): string
    {
        return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { background: #ffffff; padding: 30px; border-radius: 0 0 8px 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .button { display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .button:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
        .security-info { background: #f8f9fa; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; border-radius: 0 5px 5px 0; }
        .footer { text-align: center; margin-top: 30px; padding: 20px; color: #666; font-size: 14px; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 20px 0; }
        @media (max-width: 600px) {
            .container { padding: 10px; }
            .content, .header { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Password Reset Request</h1>
            <p>{{hospital_name}} Patient Portal</p>
        </div>
        
        <div class="content">
            <p><strong>Hello {{patient_name}},</strong></p>
            
            <p>We received a request to reset the password for your patient portal account associated with <strong>{{patient_email}}</strong>.</p>
            
            <p>To reset your password, please click the button below:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{reset_url}}" class="button" style="color: white;">Reset My Password</a>
            </div>
            
            <p>If the button above doesn\'t work, copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #667eea; font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 5px;">{{reset_url}}</p>
            
            <div class="security-info">
                <h3 style="margin-top: 0; color: #667eea;">🛡️ Security Information</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>This password reset link will expire in <strong>{{expiry_minutes}} minutes</strong></li>
                    <li>If you didn\'t request this reset, please ignore this email</li>
                    <li>Your current password remains unchanged until you set a new one</li>
                    <li>For security, this link can only be used once</li>
                </ul>
            </div>
            
            <div class="warning">
                <strong>⚠️ Important:</strong> If you didn\'t request a password reset, please contact our support team immediately at {{support_email}} or through our hospital\'s main phone line.
            </div>
            
            <p>After resetting your password, you can access your patient portal at:</p>
            <p><a href="{{portal_url}}" style="color: #667eea;">{{portal_url}}</a></p>
            
            <p>If you need assistance, please don\'t hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            <strong>{{hospital_name}} IT Support Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This is an automated email from {{hospital_name}}\'s Patient Portal system.</p>
            <p>Please do not reply to this email. For support, contact: {{support_email}}</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="font-size: 12px; color: #999;">
                This email contains sensitive information. Please ensure you are viewing it in a secure environment.
            </p>
        </div>
    </div>
</body>
</html>';
    }
}
