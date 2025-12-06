<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\DocumentTemplate;
use App\Services\TemplateRenderer;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientDocumentsController extends Controller
{
    protected $templateRenderer;
    protected $pdfService;

    public function __construct(TemplateRenderer $templateRenderer, PdfService $pdfService)
    {
        $this->templateRenderer = $templateRenderer;
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of documents for a patient.
     */
    public function index(Patient $patient, Request $request)
    {
        $this->authorize('viewAny', [PatientDocument::class, $patient]);

        $query = $patient->documents();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        $documents = $query->with(['template', 'creator', 'updater'])
            ->latest()
            ->paginate(20)->appends($request->query());

        return view('admin.patients.documents.index', compact('patient', 'documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(Patient $patient, Request $request)
    {
        $this->authorize('create', [PatientDocument::class, $patient]);

        $templateId = $request->get('template_id');
        $template = $templateId ? DocumentTemplate::findOrFail($templateId) : null;
        
        $templates = DocumentTemplate::active()->orderBy('name')->get();

        // Get branding for logos/signatures
        $branding = $this->getBranding(Auth::user());

        return view('admin.patients.documents.create', compact('patient', 'template', 'templates', 'branding'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Patient $patient, Request $request)
    {
        $this->authorize('create', [PatientDocument::class, $patient]);

        $validated = $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:letter,form',
            'content' => 'nullable|string', // For letters (pre-rendered)
            'form_data' => 'nullable|array', // For forms
            'extra_placeholders' => 'nullable|array', // For text_placeholder blocks
        ]);

        $template = DocumentTemplate::findOrFail($validated['template_id']);

        // Ensure type matches template
        if ($template->type !== $validated['type']) {
            return back()->withErrors(['type' => 'Document type must match template type.'])->withInput();
        }

        $title = $validated['title'] ?? $template->name;

        // Render letter if type is letter
        if ($validated['type'] === 'letter') {
            $branding = $this->getBranding(Auth::user());
            $extra = $validated['extra_placeholders'] ?? [];
            
            $content = $this->templateRenderer->renderLetter(
                $template,
                $patient,
                Auth::user(),
                $extra,
                $branding
            );
        } else {
            // For forms, content is null initially
            $content = null;
        }

        $document = PatientDocument::create([
            'patient_id' => $patient->id,
            'template_id' => $template->id,
            'type' => $validated['type'],
            'title' => $title,
            'status' => 'draft',
            'content' => $content,
            'form_data' => $validated['form_data'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.patients.documents.show', [$patient, $document])
            ->with('success', 'Document created successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Patient $patient, PatientDocument $document)
    {
        $this->authorize('view', $document);

        $document->load(['template', 'creator', 'updater', 'patient', 'deliveries.sender']);

        return view('admin.patients.documents.show', compact('patient', 'document'));
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(Patient $patient, PatientDocument $document)
    {
        $this->authorize('update', $document);

        if (!$document->isDraft()) {
            return back()->with('error', 'Only draft documents can be edited.');
        }

        $document->load(['template']);

        $branding = $this->getBranding(Auth::user());

        return view('admin.patients.documents.edit', compact('patient', 'document', 'branding'));
    }

    /**
     * Update the specified document.
     */
    public function update(Patient $patient, PatientDocument $document, Request $request)
    {
        $this->authorize('update', $document);

        if (!$document->isDraft()) {
            return back()->with('error', 'Only draft documents can be edited.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'form_data' => 'nullable|array',
            'extra_placeholders' => 'nullable|array',
        ]);

        // Re-render letter if type is letter and template exists
        if ($document->type === 'letter' && $document->template) {
            $branding = $this->getBranding(Auth::user());
            $extra = $validated['extra_placeholders'] ?? [];
            
            $content = $this->templateRenderer->renderLetter(
                $document->template,
                $patient,
                Auth::user(),
                $extra,
                $branding
            );

            $validated['content'] = $content;
        }

        $validated['updated_by'] = Auth::id();

        $document->update($validated);

        return redirect()
            ->route('admin.patients.documents.show', [$patient, $document])
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Finalise the document (generate PDF and mark as final).
     */
    public function finalise(Patient $patient, PatientDocument $document)
    {
        $this->authorize('finalise', $document);

        if (!$document->isDraft()) {
            return back()->with('error', 'Only draft documents can be finalised.');
        }

        try {
            // Generate PDF
            $html = $document->content;
            
            if ($document->type === 'form' && $document->template) {
                // For forms, render with filled data
                $html = $this->renderFormHtml($document);
            }

            if (empty($html)) {
                return back()->with('error', 'Cannot finalise document without content.');
            }

            $filename = 'document_' . $document->id . '_' . time();
            $pdfPath = $this->pdfService->generateFromHtml($html, $filename);

            // Update document
            $document->update([
                'status' => 'final',
                'pdf_path' => $pdfPath,
                'updated_by' => Auth::id(),
            ]);

            return back()->with('success', 'Document finalised successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to finalise document: ' . $e->getMessage());
        }
    }

    /**
     * Void the document.
     */
    public function void(Patient $patient, PatientDocument $document)
    {
        $this->authorize('void', $document);

        $document->update([
            'status' => 'void',
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document voided successfully.');
    }

    /**
     * Download the PDF.
     */
    public function download(Patient $patient, PatientDocument $document)
    {
        $this->authorize('download', $document);

        if (empty($document->pdf_path) || !$this->pdfService->pdfExists($document->pdf_path)) {
            return back()->with('error', 'PDF not found. Please finalise the document first.');
        }

        $pdfPath = $this->pdfService->getPdfPath($document->pdf_path);
        
        return response()->download($pdfPath, Str::slug($document->title) . '.pdf');
    }

    /**
     * Bulk operations on documents.
     */
    public function bulkAction(Patient $patient, Request $request)
    {
        $this->authorize('viewAny', [PatientDocument::class, $patient]);

        $validated = $request->validate([
            'action' => 'required|in:finalise,void,delete,send',
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:patient_documents,id',
        ]);

        $documentIds = $validated['document_ids'];
        $documents = PatientDocument::whereIn('id', $documentIds)
            ->where('patient_id', $patient->id)
            ->get();

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($documents as $document) {
            try {
                switch ($validated['action']) {
                    case 'finalise':
                        if (Auth::user()->can('finalise', $document) && $document->isDraft()) {
                            try {
                                $html = $document->content;
                                
                                if ($document->type === 'form' && $document->template) {
                                    $html = $this->renderFormHtml($document);
                                }

                                if (!empty($html)) {
                                    $filename = 'document_' . $document->id . '_' . time();
                                    $pdfPath = $this->pdfService->generateFromHtml($html, $filename, ['paper' => 'A4', 'orientation' => 'portrait']);

                                    $document->update([
                                        'status' => 'final',
                                        'pdf_path' => $pdfPath,
                                        'updated_by' => Auth::id(),
                                    ]);
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                    $errors[] = "Document '{$document->title}' has no content to finalise.";
                                }
                            } catch (\Exception $e) {
                                $errorCount++;
                                $errors[] = "Failed to finalise '{$document->title}': " . $e->getMessage();
                            }
                        } else {
                            $errorCount++;
                            $errors[] = "Document '{$document->title}' cannot be finalised (not draft or no permission).";
                        }
                        break;

                    case 'void':
                        if (Auth::user()->can('void', $document) && !$document->isVoid()) {
                            $document->update([
                                'status' => 'void',
                                'updated_by' => Auth::id(),
                            ]);
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Document '{$document->title}' cannot be voided.";
                        }
                        break;

                    case 'delete':
                        if (Auth::user()->can('delete', $document) && $document->isDraft()) {
                            $document->delete();
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Document '{$document->title}' cannot be deleted (not draft or no permission).";
                        }
                        break;

                    case 'send':
                        if (Auth::user()->can('send', $document) && $document->isFinal()) {
                            // This would trigger send action - for now just mark as sent
                            // Full implementation would create delivery records
                            $errorCount++;
                            $errors[] = "Bulk send requires recipient details. Please send documents individually.";
                        } else {
                            $errorCount++;
                            $errors[] = "Document '{$document->title}' cannot be sent (not final or no permission).";
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Error processing '{$document->title}': " . $e->getMessage();
            }
        }

        $message = "{$successCount} document(s) processed successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} document(s) failed.";
        }

        if ($errorCount > 0) {
            return back()->with('error', $message)->with('bulk_errors', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Render form HTML with filled data.
     */
    protected function renderFormHtml(PatientDocument $document): string
    {
        // Simple form rendering - can be enhanced later
        $formData = $document->form_data ?? [];
        $schema = $document->template->schema ?? [];
        
        $html = '<div class="form-document">';
        $html .= '<h1>' . e($document->title) . '</h1>';
        
        foreach ($schema as $section) {
            $html .= '<div class="section">';
            $html .= '<h2>' . e($section['title'] ?? '') . '</h2>';
            
            if (!empty($section['description'])) {
                $html .= '<p>' . e($section['description']) . '</p>';
            }
            
            foreach ($section['fields'] ?? [] as $field) {
                $fieldName = $field['name'] ?? '';
                $fieldLabel = $field['label'] ?? $fieldName;
                $value = $formData[$fieldName] ?? '';
                
                $html .= '<div class="field">';
                $html .= '<strong>' . e($fieldLabel) . ':</strong> ';
                
                if ($field['type'] === 'checkbox') {
                    $html .= $value ? 'Yes' : 'No';
                } else {
                    $html .= e($value);
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get template schema for form filling (AJAX endpoint).
     */
    public function getTemplateSchema(DocumentTemplate $template)
    {
        $schema = $this->templateRenderer->buildFormSchema($template);

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'type' => $template->type,
            ],
            'schema' => $schema,
        ]);
    }

    /**
     * Get branding information for logos/signatures.
     */
    protected function getBranding($user): array
    {
        $branding = [];

        // Get clinic logo
        if (function_exists('getLogo')) {
            $branding['clinic_logo'] = getLogo('light');
        }

        // Get doctor logo/signature if user is a doctor
        $doctor = $user->doctor ?? null;
        if ($doctor) {
            $department = $doctor->primaryDepartment();
            if ($department && $department->image) {
                $branding['department_logo'] = asset($department->image);
            }
        }

        return $branding;
    }

    /**
     * Collect patient signature on the document.
     */
    public function sign(Patient $patient, PatientDocument $document, Request $request)
    {
        $this->authorize('view', $document);

        if (!$document->isFinal()) {
            return back()->with('error', 'Only finalized documents can be signed.');
        }

        if ($document->signed_by_patient) {
            return back()->with('error', 'This document has already been signed.');
        }

        $request->validate([
            'signature' => 'required|string',
        ]);

        // Store signature (base64 image data)
        $signatureData = $request->input('signature');

        // Optionally save the signature image to storage
        if (Str::startsWith($signatureData, 'data:image')) {
            $signaturePath = 'signatures/' . date('Y/m') . '/signature_' . $document->id . '_' . time() . '.png';

            // Extract base64 data
            $imageData = explode(',', $signatureData)[1] ?? '';
            $decodedImage = base64_decode($imageData);

            if ($decodedImage) {
                Storage::disk('private')->put($signaturePath, $decodedImage);
            }
        }

        // Update document with signature info
        $document->update([
            'signed_by_patient' => true,
            'signed_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Document signed successfully by ' . $patient->full_name . '.');
    }

    /**
     * Request patient signature via email.
     */
    public function requestSignature(Patient $patient, PatientDocument $document, Request $request)
    {
        $this->authorize('send', $document);

        if (!$document->isFinal()) {
            return back()->with('error', 'Only finalized documents can be sent for signature.');
        }

        if ($document->signed_by_patient) {
            return back()->with('error', 'This document has already been signed.');
        }

        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:1000',
        ]);

        // Generate a secure signature token
        $signatureToken = Str::random(64);

        // Store token in document meta or a separate table
        // For simplicity, we'll use a cache-based approach
        \Cache::put(
            'document_signature_' . $document->id,
            [
                'token' => $signatureToken,
                'patient_id' => $patient->id,
                'expires_at' => now()->addDays(7),
            ],
            now()->addDays(7)
        );

        // Send email with signature link
        try {
            $signatureUrl = route('public.document.sign', [
                'document' => $document->id,
                'token' => $signatureToken,
            ]);

            // Use the email service to send
            $emailService = app(\App\Services\HospitalEmailNotificationService::class);

            $emailData = [
                'patient_name' => $patient->full_name,
                'document_title' => $document->title,
                'signature_url' => $signatureUrl,
                'custom_message' => $request->input('message'),
                'hospital_name' => \App\Models\Setting::get('hospital_name', config('app.name')),
                'expires_in' => '7 days',
            ];

            // Send using a template or direct mail
            \Mail::to($request->input('email'))->send(new \App\Mail\DocumentSignatureRequest($document, $patient, $signatureUrl, $request->input('message')));

            return back()->with('success', 'Signature request sent successfully to ' . $request->input('email') . '.');
        } catch (\Exception $e) {
            \Log::error('Failed to send signature request email', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send signature request: ' . $e->getMessage());
        }
    }
}
