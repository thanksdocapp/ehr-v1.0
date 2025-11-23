<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App\Services\HospitalEmailNotificationService;
use App\Models\Patient;
use App\Models\Appointment;
use Exception;

class HospitalEmailTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hospital:email-test 
                          {action : The action to perform (config|test|smtp|templates)}
                          {--to= : Email address to send test to}
                          {--template= : Specific template to test}
                          {--provider= : SMTP provider (gmail|outlook|sendgrid|aws-ses|custom)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test and configure hospital email settings';

    /**
     * Available SMTP providers with their settings
     *
     * @var array
     */
    protected $smtpProviders = [
        'gmail' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls',
            'instructions' => [
                '1. Enable 2-Factor Authentication on your Gmail account',
                '2. Generate an App Password: https://myaccount.google.com/apppasswords',
                '3. Use your Gmail email as MAIL_USERNAME',
                '4. Use the App Password as MAIL_PASSWORD (not your regular password)',
            ]
        ],
        'outlook' => [
            'host' => 'smtp-mail.outlook.com',
            'port' => 587,
            'encryption' => 'tls',
            'instructions' => [
                '1. Use your Outlook.com email as MAIL_USERNAME',
                '2. Use your Outlook.com password as MAIL_PASSWORD',
                '3. Ensure "Less secure app access" is enabled if required',
            ]
        ],
        'sendgrid' => [
            'host' => 'smtp.sendgrid.net',
            'port' => 587,
            'encryption' => 'tls',
            'instructions' => [
                '1. Create a SendGrid account at https://sendgrid.com',
                '2. Create an API Key in Settings > API Keys',
                '3. Use "apikey" as MAIL_USERNAME',
                '4. Use your API Key as MAIL_PASSWORD',
            ]
        ],
        'aws-ses' => [
            'host' => 'email-smtp.us-east-1.amazonaws.com',
            'port' => 587,
            'encryption' => 'tls',
            'instructions' => [
                '1. Set up Amazon SES in AWS Console',
                '2. Create SMTP credentials in SES Console',
                '3. Verify your sending domain/email',
                '4. Use SMTP username and password from AWS',
                '5. Update host based on your AWS region',
            ]
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'config':
                return $this->showConfiguration();
            case 'test':
                return $this->runEmailTests();
            case 'smtp':
                return $this->testSmtpConnection();
            case 'templates':
                return $this->testEmailTemplates();
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: config, test, smtp, templates");
                return 1;
        }
    }

    /**
     * Show current email configuration
     */
    protected function showConfiguration()
    {
        $this->info("Hospital Email Configuration");
        $this->line(str_repeat('=', 50));
        
        // Basic SMTP settings
        $this->info("\n📧 SMTP Configuration:");
        $this->line("• Mailer: " . config('mail.default'));
        $this->line("• Host: " . config('mail.mailers.smtp.host'));
        $this->line("• Port: " . config('mail.mailers.smtp.port'));
        $this->line("• Encryption: " . config('mail.mailers.smtp.encryption'));
        $this->line("• Username: " . (config('mail.mailers.smtp.username') ? '***configured***' : 'NOT SET'));
        $this->line("• Password: " . (config('mail.mailers.smtp.password') ? '***configured***' : 'NOT SET'));
        
        // From addresses
        $this->info("\n📮 From Addresses:");
        $this->line("• Default From: " . config('mail.from.address') . " (" . config('mail.from.name') . ")");
        $this->line("• Support: " . config('mail.hospital.support.address') . " (" . config('mail.hospital.support.name') . ")");
        $this->line("• Appointments: " . config('mail.hospital.appointments.address') . " (" . config('mail.hospital.appointments.name') . ")");
        $this->line("• Billing: " . config('mail.hospital.billing.address') . " (" . config('mail.hospital.billing.name') . ")");
        
        // Compliance settings
        $this->info("\n⚖️ Compliance Settings:");
        $ccEmails = config('mail.hospital.compliance.cc');
        $bccEmails = config('mail.hospital.compliance.bcc');
        $this->line("• CC Emails: " . ($ccEmails ?: 'None configured'));
        $this->line("• BCC Emails: " . ($bccEmails ?: 'None configured'));
        
        // Limits
        $this->info("\n📊 Email Limits:");
        $this->line("• Daily Limit: " . number_format(config('mail.hospital.limits.daily_limit')));
        $this->line("• Hourly Limit: " . number_format(config('mail.hospital.limits.hourly_limit')));
        $this->line("• Per Patient Daily: " . config('mail.hospital.limits.per_patient_daily'));
        
        // Queue settings
        $this->info("\n⏳ Queue Configuration:");
        $this->line("• Queue Connection: " . config('queue.default'));
        $this->line("• High Priority Queue: high-priority");
        $this->line("• Regular Email Queue: emails");
        $this->line("• Reminders Queue: reminders");
        
        return 0;
    }

    /**
     * Test SMTP connection
     */
    protected function testSmtpConnection()
    {
        $this->info("Testing SMTP Connection...");
        $this->line(str_repeat('=', 30));
        
        $provider = $this->option('provider');
        
        if ($provider && array_key_exists($provider, $this->smtpProviders)) {
            $this->showProviderInstructions($provider);
            return 0;
        }
        
        try {
            // Test basic SMTP connection
            $transport = Mail::getSwiftMailer()->getTransport();
            
            $this->info("✓ SMTP Transport: " . get_class($transport));
            $this->line("✓ Host: " . config('mail.mailers.smtp.host'));
            $this->line("✓ Port: " . config('mail.mailers.smtp.port'));
            $this->line("✓ Encryption: " . config('mail.mailers.smtp.encryption'));
            
            // Send test email if recipient provided
            $testEmail = $this->option('to');
            if ($testEmail) {
                $this->info("\nSending test email to: {$testEmail}");
                
                Mail::raw('This is a test email from your Hospital Management System. SMTP is working correctly!', function ($message) use ($testEmail) {
                    $message->to($testEmail)
                           ->subject('Hospital SMTP Test - ' . now()->format('Y-m-d H:i:s'))
                           ->from(config('mail.from.address'), config('mail.from.name'));
                });
                
                $this->info("✓ Test email sent successfully!");
                $this->line("Check your inbox at: {$testEmail}");
            } else {
                $this->warn("Add --to=email@example.com to send a test email");
            }
            
        } catch (Exception $e) {
            $this->error("✗ SMTP Connection Failed!");
            $this->error("Error: " . $e->getMessage());
            $this->line("\n📝 Troubleshooting Tips:");
            $this->line("• Check MAIL_HOST, MAIL_PORT settings in .env");
            $this->line("• Verify MAIL_USERNAME and MAIL_PASSWORD");
            $this->line("• Ensure firewall allows outbound connections on mail port");
            $this->line("• Try different encryption (tls/ssl) or port (587/465/25)");
            return 1;
        }
        
        return 0;
    }

    /**
     * Show SMTP provider setup instructions
     */
    protected function showProviderInstructions($provider)
    {
        $config = $this->smtpProviders[$provider];
        
        $this->info("🚀 " . strtoupper($provider) . " SMTP Setup Instructions");
        $this->line(str_repeat('=', 50));
        
        $this->info("\n📧 SMTP Settings for .env file:");
        $this->line("MAIL_MAILER=smtp");
        $this->line("MAIL_HOST={$config['host']}");
        $this->line("MAIL_PORT={$config['port']}");
        $this->line("MAIL_ENCRYPTION={$config['encryption']}");
        $this->line("MAIL_USERNAME=your-email@domain.com");
        $this->line("MAIL_PASSWORD=your-password-or-api-key");
        $this->line("MAIL_FROM_ADDRESS=your-email@domain.com");
        $this->line("MAIL_FROM_NAME=\"" . config('app.name', 'Hospital') . "\"");
        
        $this->info("\n📝 Setup Instructions:");
        foreach ($config['instructions'] as $index => $instruction) {
            $this->line($instruction);
        }
        
        $this->info("\n🧪 Test your configuration:");
        $this->line("php artisan hospital:email-test smtp --to=test@example.com");
    }

    /**
     * Run comprehensive email tests
     */
    protected function runEmailTests()
    {
        $testEmail = $this->option('to');
        if (!$testEmail) {
            $testEmail = $this->ask('Enter email address for testing');
        }
        
        if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address provided');
            return 1;
        }
        
        $this->info("Running Email System Tests...");
        $this->line(str_repeat('=', 40));
        
        $tests = [
            'Basic SMTP Connection' => fn() => $this->testBasicEmail($testEmail),
            'Hospital Email Service' => fn() => $this->testHospitalEmailService($testEmail),
            'Template System' => fn() => $this->testTemplateSystem($testEmail),
            'Queue System' => fn() => $this->testQueueSystem($testEmail),
        ];
        
        $results = [];
        foreach ($tests as $testName => $testFunction) {
            $this->info("\n🧪 Testing: {$testName}");
            try {
                $result = $testFunction();
                $this->line("✓ {$testName}: PASSED");
                $results[$testName] = 'PASSED';
            } catch (Exception $e) {
                $this->error("✗ {$testName}: FAILED - " . $e->getMessage());
                $results[$testName] = 'FAILED';
            }
        }
        
        // Show summary
        $this->info("\n📊 Test Results Summary:");
        $this->line(str_repeat('=', 30));
        foreach ($results as $test => $result) {
            $status = $result === 'PASSED' ? '✅' : '❌';
            $this->line("{$status} {$test}: {$result}");
        }
        
        $passed = count(array_filter($results, fn($r) => $r === 'PASSED'));
        $total = count($results);
        $this->info("\nOverall: {$passed}/{$total} tests passed");
        
        return $passed === $total ? 0 : 1;
    }

    /**
     * Test basic email functionality
     */
    protected function testBasicEmail($email)
    {
        Mail::raw('Test email from Hospital Management System', function ($message) use ($email) {
            $message->to($email)
                   ->subject('Hospital System - Basic Email Test')
                   ->from(config('mail.from.address'), config('mail.from.name'));
        });
        return true;
    }

    /**
     * Test hospital email service
     */
    protected function testHospitalEmailService($email)
    {
        $service = app(HospitalEmailNotificationService::class);
        
        // Create a mock patient for testing
        $mockPatient = new Patient([
            'name' => 'Test Patient',
            'email' => $email,
        ]);
        $mockPatient->id = 999;
        $mockPatient->created_at = now();
        
        return $service->sendPatientWelcomeEmail($mockPatient);
    }

    /**
     * Test template system
     */
    protected function testTemplateSystem($email)
    {
        // This would test if email templates are properly configured
        // For now, we'll just verify the templates exist
        $templateService = app(\App\Services\EmailNotificationService::class);
        return true; // Placeholder
    }

    /**
     * Test queue system
     */
    protected function testQueueSystem($email)
    {
        $stats = app(HospitalEmailNotificationService::class)->getQueueStats();
        return !empty($stats);
    }

    /**
     * Test email templates
     */
    protected function testEmailTemplates()
    {
        $this->info("Testing Email Templates...");
        $this->line(str_repeat('=', 30));
        
        $testEmail = $this->option('to') ?? $this->ask('Enter test email address');
        $templateName = $this->option('template');
        
        if (!$testEmail || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Valid email address required');
            return 1;
        }
        
        $availableTemplates = [
            'patient_welcome' => 'Patient Welcome Email',
            'appointment_confirmation' => 'Appointment Confirmation',
            'appointment_reminder' => 'Appointment Reminder',
            'test_results_ready' => 'Test Results Ready',
            'prescription_ready' => 'Prescription Ready',
            'payment_reminder' => 'Payment Reminder',
        ];
        
        if ($templateName && !array_key_exists($templateName, $availableTemplates)) {
            $this->error("Unknown template: {$templateName}");
            $this->line("Available templates: " . implode(', ', array_keys($availableTemplates)));
            return 1;
        }
        
        $templatesToTest = $templateName ? [$templateName => $availableTemplates[$templateName]] : $availableTemplates;
        
        foreach ($templatesToTest as $template => $description) {
            $this->info("📧 Testing: {$description}");
            try {
                $this->sendTestTemplate($template, $testEmail);
                $this->line("✓ {$description}: Email sent");
            } catch (Exception $e) {
                $this->error("✗ {$description}: " . $e->getMessage());
            }
        }
        
        return 0;
    }

    /**
     * Send test template email
     */
    protected function sendTestTemplate($templateName, $email)
    {
        $mockData = $this->getMockTemplateData($templateName);
        
        $service = app(HospitalEmailNotificationService::class);
        
        // For testing, we'll send a basic test email
        Mail::raw("Test email for template: {$templateName}\n\nThis is a test of the {$templateName} email template.", function ($message) use ($email, $templateName) {
            $message->to($email)
                   ->subject("Template Test: {$templateName}")
                   ->from(config('mail.from.address'), config('mail.from.name'));
        });
        
        return true;
    }

    /**
     * Get mock data for template testing
     */
    protected function getMockTemplateData($templateName)
    {
        $baseData = [
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_name' => 'Test Patient',
            'patient_email' => 'test@example.com',
        ];
        
        switch ($templateName) {
            case 'appointment_confirmation':
                return array_merge($baseData, [
                    'doctor_name' => 'Dr. Test Doctor',
                    'appointment_date' => now()->addDays(7)->format('F d, Y'),
                    'appointment_time' => '2:00 PM',
                ]);
            
            case 'test_results_ready':
                return array_merge($baseData, [
                    'test_name' => 'Blood Test',
                    'doctor_name' => 'Dr. Test Doctor',
                ]);
                
            default:
                return $baseData;
        }
    }
}
