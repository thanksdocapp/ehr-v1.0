<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailNotificationService;
use Exception;

class PreviewEmailTemplate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:preview
                            {template : Template name (e.g., appointment_confirmation, doctor_new_appointment)}
                            {--context=patient : patient or doctor context}
                            {--online : Include online consultation section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preview a rendered email template without sending it';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $templateName = $this->argument('template');
        $context = strtolower($this->option('context') ?? 'patient');

        if (!in_array($context, ['patient', 'doctor'], true)) {
            $this->error('Invalid context. Use patient or doctor.');
            return 1;
        }

        $baseVariables = [
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_name' => 'Test Patient',
            'patient_email' => 'test.patient@example.com',
            'doctor_name' => 'Dr Test Doctor',
            'department' => 'General',
            'appointment_date' => now()->addDay()->format('F d, Y'),
            'appointment_time' => '2:00 PM',
            'appointment_type' => 'consultation',
            'meeting_platform' => 'Whereby',
        ];

        if ($this->option('online')) {
            $participantLink = 'https://whereby.test/participant';
            $hostLink = 'https://whereby.test/host';

            if ($context === 'doctor') {
                $onlineSection = "\n*** ONLINE CONSULTATION ***\nPlatform: Whereby\nHost link: {$hostLink}\n";
                $baseVariables['meeting_link'] = $hostLink;
                $baseVariables['host_meeting_link'] = $hostLink;
            } else {
                $onlineSection = "\n*** ONLINE CONSULTATION ***\nThis is an online video consultation.\nPlatform: Whereby\nParticipant link: {$participantLink}\n\nPlease join the meeting 5 minutes before your scheduled time.\n";
                $baseVariables['meeting_link'] = $participantLink;
                $baseVariables['join_meeting_url'] = $participantLink;
            }

            $baseVariables['online_consultation_section'] = $onlineSection;
        }

        try {
            $renderer = app(EmailNotificationService::class);
            $rendered = $renderer->renderTemplate($templateName, $baseVariables);

            $this->line('Subject:');
            $this->line($rendered['subject']);
            $this->newLine();
            $this->line('Body:');
            $this->line($rendered['body']);

            return 0;
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
