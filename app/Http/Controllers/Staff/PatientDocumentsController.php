<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDocument;
use App\Models\DocumentTemplate;
use App\Models\Template;
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
     * Admin sees all documents; doctors/staff only see documents they created.
     */
    public function index(Patient $patient, Request $request)
    {
        $this->authorize('viewAny', [PatientDocument::class, $patient]);

        $user = Auth::user();
        $isAdmin = ($user->is_admin ?? false) || ($user->role === 'admin');

        // Admin sees all documents; others only see documents they created
        $query = $patient->documents();
        if (!$isAdmin) {
            $query->ownedBy($user);
        }

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

        return view('staff.patients.documents.index', compact('patient', 'documents'));
    }

    /**
     * Show the form for creating a new document.
     * Only show templates the doctor has access to (own + system templates).
     */
    public function create(Patient $patient, Request $request)
    {
        $this->authorize('create', [PatientDocument::class, $patient]);

        $user = Auth::user();
        $templateId = $request->get('template_id');
        $template = $templateId ? Template::findOrFail($templateId) : null;

        // Only show templates visible to this user (own + system templates) from both models
        // First try Template model (Letters & Forms module)
        $templates = Template::visibleTo($user)->active()->orderBy('name')->get();

        // If no templates found in Template, fall back to DocumentTemplate
        if ($templates->isEmpty()) {
            $templates = DocumentTemplate::visibleTo($user)->active()->orderBy('name')->get();
        }

        // Get branding for logos/signatures
        $branding = $this->getBranding($user);

        return view('staff.patients.documents.create', compact('patient', 'template', 'templates', 'branding'));
    }

    /**
     * Store a newly created document.
     */
    public function store(Patient $patient, Request $request)
    {
        $this->authorize('create', [PatientDocument::class, $patient]);

        $documentSource = $request->input('document_source', 'template');

        // Handle PDF upload
        if ($documentSource === 'upload') {
            $validated = $request->validate([
                'pdf_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
                'title' => 'required|string|max:255',
            ]);

            // Store the uploaded PDF
            $file = $request->file('pdf_file');
            $filename = 'patient_document_' . $patient->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $pdfPath = $file->storeAs('patient_documents', $filename, 'private');

            // Create document record
            $document = PatientDocument::create([
                'patient_id' => $patient->id,
                'template_id' => null, // No template for uploaded files
                'type' => 'letter', // Default to letter for uploaded PDFs
                'title' => $validated['title'],
                'status' => 'final', // Uploaded documents are immediately final
                'content' => null, // No content for uploaded files
                'form_data' => null,
                'pdf_path' => $pdfPath,
                'created_by' => Auth::id(),
            ]);

            return redirect()
                ->route('staff.patients.documents.show', [$patient, $document])
                ->with('success', 'Document uploaded successfully.');
        }

        // Handle template-based document creation
        $validated = $request->validate([
            'template_id' => 'required|integer',
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:letter,form',
            'content' => 'nullable|string',
            'form_data' => 'nullable|array',
            'extra_placeholders' => 'nullable|array',
        ]);

        // Try to find template in Template model first, then fall back to DocumentTemplate
        $template = Template::find($validated['template_id']);
        if (!$template) {
            $template = DocumentTemplate::findOrFail($validated['template_id']);
        }

        // Ensure type matches template
        if ($template->type !== $validated['type']) {
            return back()->withErrors(['type' => 'Document type must match template type.'])->withInput();
        }

        $title = $validated['title'] ?? $template->name;

        // Render letter if type is letter
        if ($validated['type'] === 'letter') {
            $branding = $this->getBranding(Auth::user());

            // Transform extra_placeholders from [{name: 'x', value: 'y'}] to ['x' => 'y']
            $extra = [];
            $rawPlaceholders = $validated['extra_placeholders'] ?? [];
            foreach ($rawPlaceholders as $placeholder) {
                if (!empty($placeholder['name'])) {
                    $extra[$placeholder['name']] = $placeholder['value'] ?? '';
                }
            }

            $content = $this->templateRenderer->renderLetter(
                $template,
                $patient,
                Auth::user(),
                $extra,
                $branding
            );
        } else {
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
            ->route('staff.patients.documents.show', [$patient, $document])
            ->with('success', 'Document created successfully.');
    }

    /**
     * Display the specified document.
     */
    public function show(Patient $patient, PatientDocument $document)
    {
        $this->authorize('view', $document);

        $document->load(['template', 'creator', 'updater', 'patient', 'deliveries.sender']);

        return view('staff.patients.documents.show', compact('patient', 'document'));
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

        return view('staff.patients.documents.edit', compact('patient', 'document', 'branding'));
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

        // Only re-render letter if extra_placeholders are provided (user wants to re-generate)
        // Otherwise, keep the user's manually edited content from the form
        $rawPlaceholders = $validated['extra_placeholders'] ?? [];
        $hasPlaceholders = !empty(array_filter($rawPlaceholders, fn($p) => !empty($p['name'])));

        if ($document->type === 'letter' && $document->template && $hasPlaceholders) {
            $branding = $this->getBranding(Auth::user());

            // Transform extra_placeholders from [{name: 'x', value: 'y'}] to ['x' => 'y']
            $extra = [];
            foreach ($rawPlaceholders as $placeholder) {
                if (!empty($placeholder['name'])) {
                    $extra[$placeholder['name']] = $placeholder['value'] ?? '';
                }
            }

            $content = $this->templateRenderer->renderLetter(
                $document->template,
                $patient,
                Auth::user(),
                $extra,
                $branding
            );

            $validated['content'] = $content;
        }
        // If no placeholders provided, the content from the form (validated['content']) is used as-is

        // Remove extra_placeholders from validated data as it's not a model field
        unset($validated['extra_placeholders']);

        $validated['updated_by'] = Auth::id();

        $document->update($validated);

        return redirect()
            ->route('staff.patients.documents.show', [$patient, $document])
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

        if (empty($document->pdf_path)) {
            return back()->with('error', 'PDF not found. Please finalise the document first.');
        }

        // Check if file exists in private storage (uploaded files)
        if (Storage::disk('private')->exists($document->pdf_path)) {
            $filePath = Storage::disk('private')->path($document->pdf_path);
            return response()->download($filePath, Str::slug($document->title) . '.pdf');
        }

        // Check if file exists via PdfService (generated PDFs)
        if ($this->pdfService->pdfExists($document->pdf_path)) {
            $pdfPath = $this->pdfService->getPdfPath($document->pdf_path);
            return response()->download($pdfPath, Str::slug($document->title) . '.pdf');
        }

        return back()->with('error', 'PDF not found. Please finalise the document first.');
    }

    /**
     * Bulk operations on documents.
     */
    public function bulkAction(Patient $patient, Request $request)
    {
        $this->authorize('viewAny', [PatientDocument::class, $patient]);

        $validated = $request->validate([
            'action' => 'required|in:finalise,void,delete',
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
                    // Handle checkbox values - convert 1/'1'/true to Yes, 0/'0'/false/empty to No
                    if ($value === '1' || $value === 1 || $value === true || (is_string($value) && strtolower($value) === 'yes')) {
                        $html .= 'Yes';
                    } else {
                        $html .= 'No';
                    }
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
     * Get branding information for logos/signatures.
     */
    protected function getBranding($user): array
    {
        $branding = [];

        if (function_exists('getLogo')) {
            $branding['clinic_logo'] = getLogo('light');
        }

        $doctor = $user->doctor ?? null;
        if ($doctor) {
            $department = $doctor->primaryDepartment();
            if ($department && $department->image) {
                $branding['department_logo'] = asset($department->image);
            }
        }

        return $branding;
    }
}
