<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\DocumentDelivery;
use App\Services\HospitalEmailNotificationService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentDeliveriesController extends Controller
{
    protected $emailService;
    protected $pdfService;

    public function __construct(HospitalEmailNotificationService $emailService, PdfService $pdfService)
    {
        $this->emailService = $emailService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of deliveries for a document.
     */
    public function index(Patient $patient, PatientDocument $document)
    {
        $this->authorize('viewAny', [DocumentDelivery::class, $document]);

        $deliveries = $document->deliveries()
            ->with(['sender', 'patient'])
            ->latest()
            ->paginate(20);

        return view('staff.patients.documents.deliveries.index', compact('patient', 'document', 'deliveries'));
    }

    /**
     * Store a newly created delivery (send document).
     */
    public function store(Patient $patient, PatientDocument $document, Request $request)
    {
        $this->authorize('create', [DocumentDelivery::class, $document]);

        // Check document is final
        if (!$document->isFinal()) {
            return back()->with('error', 'Only final documents can be sent.');
        }

        $validated = $request->validate([
            'recipient_type' => 'required|in:patient,third_party',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_email' => 'required|email',
            'recipient_phone' => 'nullable|string|max:20',
            'channel' => 'nullable|string|in:email,portal,print',
        ]);

        // Set defaults
        $validated['channel'] = $validated['channel'] ?? 'email';

        // Handle patient recipient
        if ($validated['recipient_type'] === 'patient') {
            $validated['recipient_name'] = $validated['recipient_name'] ?? $patient->full_name;
            $validated['recipient_email'] = $validated['recipient_email'] ?? $patient->email;
        }

        // Create delivery record
        $delivery = DocumentDelivery::create([
            'patient_document_id' => $document->id,
            'patient_id' => $patient->id,
            'sent_by' => Auth::id(),
            'recipient_type' => $validated['recipient_type'],
            'recipient_name' => $validated['recipient_name'],
            'recipient_email' => $validated['recipient_email'],
            'recipient_phone' => $validated['recipient_phone'],
            'channel' => $validated['channel'],
            'status' => 'pending',
        ]);

        // Send via email
        if ($validated['channel'] === 'email') {
            try {
                $this->sendDocumentEmail($document, $delivery, $patient);
                
                $delivery->markAsSent([
                    'sent_at' => now()->toDateTimeString(),
                    'channel' => 'email',
                ]);

                return back()->with('success', 'Document sent successfully via email.');
            } catch (\Exception $e) {
                $delivery->markAsFailed([
                    'error' => $e->getMessage(),
                ]);

                return back()->with('error', 'Failed to send document: ' . $e->getMessage());
            }
        }

        // For portal/print, mark as pending (manual handling)
        return back()->with('success', 'Delivery created. Document will be available via portal/print.');
    }

    /**
     * Send document via email.
     */
    protected function sendDocumentEmail(PatientDocument $document, DocumentDelivery $delivery, Patient $patient)
    {
        $recipientEmail = $delivery->recipient_email;
        $recipientName = $delivery->recipient_name ?? $patient->full_name;

        // Get PDF path
        if (!$document->pdf_path || !$this->pdfService->pdfExists($document->pdf_path)) {
            throw new \Exception('PDF not found. Please finalise the document first.');
        }

        $pdfPath = $this->pdfService->getPdfPath($document->pdf_path);

        // Prepare email data
        $clinicName = config('app.name', 'Clinic');
        $subject = "New document from {$clinicName}: {$document->title}";

        $emailBody = view('emails.documents.send', [
            'document' => $document,
            'patient' => $patient,
            'recipientName' => $recipientName,
            'clinicName' => $clinicName,
        ])->render();

        // Send email with attachment
        \Mail::send([], [], function ($message) use ($recipientEmail, $recipientName, $subject, $emailBody, $pdfPath, $document) {
            $message->to($recipientEmail, $recipientName)
                ->subject($subject)
                ->html($emailBody)
                ->attach($pdfPath, [
                    'as' => Str::slug($document->title) . '.pdf',
                    'mime' => 'application/pdf',
                ]);
        });
    }
}
