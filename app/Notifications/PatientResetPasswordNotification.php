<?php

namespace App\Notifications;

use App\Services\EmailNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class PatientResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Use the hospital's email service instead of Laravel's default mail
        $this->sendViaHospitalEmailService($notifiable);
        
        // Return a dummy MailMessage to satisfy the interface
        // The actual email is sent via the hospital service above
        return (new MailMessage)
            ->subject('Password Reset Sent')
            ->line('Password reset email has been sent via hospital email service.');
    }
    
    /**
     * Send password reset email using the hospital's email service.
     */
    protected function sendViaHospitalEmailService($notifiable)
    {
        try {
            $resetUrl = $this->resetUrl($notifiable);
            
            $emailService = app(EmailNotificationService::class);
            
            // Prepare variables for the email template
            $variables = [
                'patient_name' => $notifiable->full_name ?? $notifiable->name,
                'reset_url' => $resetUrl,
                'reset_token' => $this->token,
                'patient_email' => $notifiable->getEmailForPasswordReset(),
                'hospital_name' => config('app.name', 'Hospital'),
                'expiry_minutes' => config('auth.passwords.patients.expire', 60),
                'support_email' => config('mail.from.address'),
                'portal_url' => url('/patient/login'),
            ];
            
            // Send using hospital email service
            $emailService->sendTemplateEmail(
                'patient_password_reset',
                [$notifiable->getEmailForPasswordReset() => $notifiable->full_name ?? $notifiable->name],
                $variables
            );
            
            Log::info('Password reset email sent via hospital service', [
                'patient_email' => $notifiable->getEmailForPasswordReset(),
                'token' => $this->token
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email via hospital service', [
                'patient_email' => $notifiable->getEmailForPasswordReset(),
                'error' => $e->getMessage()
            ]);
            
            // If hospital service fails, we could fall back to default Laravel mail
            // but for now, we'll just log the error
            throw $e;
        }
    }

    /**
     * Get the password reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable): string
    {
        return url(route('patient.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
