<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\GeneratedDocument;
use App\Models\Template;
use App\Models\Patient;
use App\Models\SiteSetting;
use App\Services\DocumentPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class GeneratedDocumentsController extends Controller
{
    protected $pdfService;

    public function __construct(DocumentPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $query = GeneratedDocument::with(['template', 'patient', 'generator'])
            ->where('generated_by', auth()->id());

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $documents = $query->latest()->paginate(20);

        return view('staff.generated-documents.index', compact('documents'));
    }

    public function create(Request $request)
    {
        $templates = Template::visibleTo(auth()->user())->active()->orderBy('name')->get();
        $selectedTemplate = null;
        $selectedPatient = null;

        if ($request->filled('template_id')) {
            $selectedTemplate = Template::find($request->template_id);
        }

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        return view('staff.generated-documents.create', compact('templates', 'selectedTemplate', 'selectedPatient'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'patient_id' => 'required|exists:patients,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $template = Template::findOrFail($validated['template_id']);
        $patient = Patient::findOrFail($validated['patient_id']);

        try {
            $document = $this->pdfService->generate($template, $patient, [
                'title' => $validated['title'] ?? $template->name . ' - ' . $patient->full_name,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('staff.generated-documents.show', $document)
                ->with('success', 'Document generated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to generate document: ' . $e->getMessage());
        }
    }

    public function show(GeneratedDocument $generatedDocument)
    {
        $this->authorize('view', $generatedDocument);
        return view('staff.generated-documents.show', compact('generatedDocument'));
    }

    public function download(GeneratedDocument $generatedDocument)
    {
        $this->authorize('download', $generatedDocument);

        if (!$generatedDocument->pdfExists()) {
            return back()->with('error', 'PDF file not found.');
        }

        return Storage::disk('private')->download(
            $generatedDocument->file_path,
            $generatedDocument->file_name
        );
    }

    public function finalize(GeneratedDocument $generatedDocument)
    {
        $this->authorize('finalize', $generatedDocument);

        if ($generatedDocument->status !== 'draft') {
            return back()->with('error', 'Only draft documents can be finalized.');
        }

        $generatedDocument->update(['status' => 'final']);

        return back()->with('success', 'Document has been finalized.');
    }

    public function sendForm(GeneratedDocument $generatedDocument)
    {
        $this->authorize('send', $generatedDocument);
        return view('staff.generated-documents.send', compact('generatedDocument'));
    }

    /**
     * Send document via email.
     * For forms: sends a fillable link
     * For letters: sends PDF attachment
     */
    public function send(Request $request, GeneratedDocument $generatedDocument)
    {
        $this->authorize('send', $generatedDocument);

        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        try {
            $generatedDocument->load('template');

            // Check if this is a form template - send as fillable link
            if ($generatedDocument->template && $generatedDocument->template->type === 'form') {
                return $this->sendFormAsLink($generatedDocument, $validated);
            }

            // For letters - send as PDF attachment
            return $this->sendLetterAsPdf($generatedDocument, $validated);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send document: ' . $e->getMessage());
        }
    }

    /**
     * Send form as a fillable link.
     */
    protected function sendFormAsLink(GeneratedDocument $generatedDocument, array $validated)
    {
        // Create a form request record
        $formRequest = \App\Models\FormRequest::create([
            'generated_document_id' => $generatedDocument->id,
            'template_id' => $generatedDocument->template_id,
            'patient_id' => $generatedDocument->patient_id,
            'requested_by' => auth()->id(),
            'recipient_email' => $validated['email'],
            'rendered_content' => $generatedDocument->rendered_content,
            'sent_at' => now(),
            'notes' => $validated['message'] ?? null,
        ]);

        // Configure SMTP settings from database before sending
        $settings = SiteSetting::getSettings();
        if (isset($settings['smtp_host']) && $settings['smtp_host']) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
            Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
            Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
            Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
            $encryption = $settings['smtp_encryption'] ?? 'tls';
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            if (isset($settings['from_email']) && $settings['from_email']) {
                Config::set('mail.from.address', $settings['from_email']);
                Config::set('mail.from.name', $settings['from_name'] ?? $settings['hospital_name'] ?? config('app.name'));
            }
        }

        // Send email with form link
        Mail::send('emails.forms.form-request', [
            'formRequest' => $formRequest,
            'customMessage' => $validated['message'] ?? null,
        ], function ($mail) use ($validated, $generatedDocument) {
            $mail->to($validated['email'])
                ->subject($validated['subject'] ?? 'Please Complete: ' . $generatedDocument->title);
        });

        // Mark document as sent
        $generatedDocument->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_to' => $validated['email'],
        ]);

        return redirect()->route('staff.generated-documents.show', $generatedDocument)
            ->with('success', 'Form link sent successfully to ' . $validated['email'] . '. The patient can now fill out the form online.');
    }

    /**
     * Send letter as PDF attachment.
     */
    protected function sendLetterAsPdf(GeneratedDocument $generatedDocument, array $validated)
    {
        if (!$generatedDocument->pdfExists()) {
            return back()->with('error', 'PDF file not found.');
        }

        $pdfPath = Storage::disk('private')->path($generatedDocument->file_path);

        // Configure SMTP settings from database before sending
        $settings = SiteSetting::getSettings();
        if (isset($settings['smtp_host']) && $settings['smtp_host']) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
            Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
            Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
            Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
            $encryption = $settings['smtp_encryption'] ?? 'tls';
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            if (isset($settings['from_email']) && $settings['from_email']) {
                Config::set('mail.from.address', $settings['from_email']);
                Config::set('mail.from.name', $settings['from_name'] ?? $settings['hospital_name'] ?? config('app.name'));
            }
        }

        Mail::send('emails.documents.generated-document', [
            'document' => $generatedDocument,
            'customMessage' => $validated['message'] ?? null,
        ], function ($mail) use ($validated, $generatedDocument, $pdfPath) {
            $mail->to($validated['email'])
                ->subject($validated['subject'] ?? 'Document: ' . $generatedDocument->title)
                ->attach($pdfPath, [
                    'as' => $generatedDocument->file_name,
                    'mime' => 'application/pdf',
                ]);
        });

        $generatedDocument->update([
            'status' => 'sent',
            'sent_at' => now(),
            'sent_to' => $validated['email'],
        ]);

        return redirect()->route('staff.generated-documents.show', $generatedDocument)
            ->with('success', 'Document sent successfully to ' . $validated['email']);
    }

    public function destroy(GeneratedDocument $generatedDocument)
    {
        $this->authorize('delete', $generatedDocument);

        $generatedDocument->delete();

        return redirect()->route('staff.generated-documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}
