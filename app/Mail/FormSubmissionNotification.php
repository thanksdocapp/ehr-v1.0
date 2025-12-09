<?php

namespace App\Mail;

use App\Models\FormRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public FormRequest $formRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(FormRequest $formRequest)
    {
        $this->formRequest = $formRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $formName = $this->formRequest->template->name
            ?? $this->formRequest->patientDocument->title
            ?? 'Form';

        return new Envelope(
            subject: 'Form Completed: ' . $formName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.forms.submission-notification',
            with: [
                'formRequest' => $this->formRequest,
                'patient' => $this->formRequest->patient,
                'template' => $this->formRequest->template,
                'formData' => $this->formRequest->form_data,
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
