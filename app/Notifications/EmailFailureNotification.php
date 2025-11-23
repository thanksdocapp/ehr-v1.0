<?php

namespace App\Notifications;

use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class EmailFailureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The failed email log.
     *
     * @var \App\Models\EmailLog
     */
    protected $emailLog;

    /**
     * The exception that caused the failure.
     *
     * @var \Throwable
     */
    protected $exception;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\EmailLog $emailLog
     * @param \Throwable $exception
     * @return void
     */
    public function __construct(EmailLog $emailLog, Throwable $exception)
    {
        $this->emailLog = $emailLog;
        $this->exception = $exception;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->error()
            ->subject('Email Sending Failure Alert')
            ->line('An email failed to send in the system.')
            ->line('Details:')
            ->line('Template: ' . ($this->emailLog->emailTemplate?->name ?? 'N/A'))
            ->line('Recipient: ' . $this->emailLog->recipient_email)
            ->line('Error: ' . $this->exception->getMessage())
            ->action('View Email Log', url("/admin/email-logs/{$this->emailLog->id}"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'email_log_id' => $this->emailLog->id,
            'template' => $this->emailLog->emailTemplate?->name,
            'recipient' => $this->emailLog->recipient_email,
            'error' => $this->exception->getMessage(),
            'url' => "/admin/email-logs/{$this->emailLog->id}"
        ];
    }
}
