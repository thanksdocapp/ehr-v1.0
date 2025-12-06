<?php

namespace App\Mail;

use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentSignatureRequest extends Mailable
{
    use Queueable, SerializesModels;

    public PatientDocument $document;
    public Patient $patient;
    public string $signatureUrl;
    public ?string $customMessage;
    public string $hospitalName;

    /**
     * Create a new message instance.
     */
    public function __construct(PatientDocument $document, Patient $patient, string $signatureUrl, ?string $customMessage = null)
    {
        $this->document = $document;
        $this->patient = $patient;
        $this->signatureUrl = $signatureUrl;
        $this->customMessage = $customMessage;
        $this->hospitalName = Setting::get('hospital_name', config('app.name'));
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Signature Required: ' . $this->document->title . ' - ' . $this->hospitalName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.documents.signature-request',
            with: [
                'document' => $this->document,
                'patient' => $this->patient,
                'signatureUrl' => $this->signatureUrl,
                'customMessage' => $this->customMessage,
                'hospitalName' => $this->hospitalName,
                'expiresIn' => '7 days',
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
