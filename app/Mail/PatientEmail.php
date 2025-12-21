<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Email data
     */
    public $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Get sender email from settings
        $senderEmail = \App\Models\SiteSetting::where('key', 'hospital_email')
            ->value('value') ?? config('mail.from.address', 'noreply@example.com');
        
        $senderName = $this->emailData['clinic_name'] ?? config('app.name', 'Clinic');

        // Omit replyTo to prevent replies (no reply-to header will be set)
        return new Envelope(
            subject: $this->emailData['subject'],
            from: $senderEmail,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.patient-email',
            with: [
                'emailData' => $this->emailData,
                'trackingToken' => $this->emailData['tracking_token'] ?? null,
                'emailLogId' => $this->emailData['email_log_id'] ?? null,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
