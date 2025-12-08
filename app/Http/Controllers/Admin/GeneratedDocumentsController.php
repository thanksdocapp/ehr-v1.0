<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeneratedDocument;
use App\Models\Patient;
use App\Models\Template;
use App\Services\DocumentPdfService;
use App\Services\TemplateMergeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class GeneratedDocumentsController extends Controller
{
    protected DocumentPdfService $pdfService;
    protected TemplateMergeService $mergeService;

    public function __construct(DocumentPdfService $pdfService, TemplateMergeService $mergeService)
    {
        $this->pdfService = $pdfService;
        $this->mergeService = $mergeService;
    }

    /**
     * Display a listing of generated documents.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', GeneratedDocument::class);

        $user = Auth::user();

        $query = GeneratedDocument::generatedBy($user);

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->with(['template', 'patient', 'generator'])
            ->latest()
            ->paginate(20)
            ->appends($request->query());

        return view('admin.generated-documents.index', compact('documents'));
    }

    /**
     * Show form to generate a new document.
     */
    public function create(Request $request)
    {
        $this->authorize('create', GeneratedDocument::class);

        $user = Auth::user();

        // Get available templates
        $templates = Template::visibleTo($user)
            ->active()
            ->orderBy('name')
            ->get();

        // Get selected patient if provided
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->patient_id);
        }

        // Get selected template if provided
        $template = null;
        if ($request->filled('template_id')) {
            $template = Template::find($request->template_id);
        }

        return view('admin.generated-documents.create', compact('templates', 'patient', 'template'));
    }

    /**
     * Generate a new document.
     */
    public function store(Request $request)
    {
        $this->authorize('create', GeneratedDocument::class);

        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'patient_id' => 'required|exists:patients,id',
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'custom_data' => 'nullable|array',
        ]);

        $template = Template::findOrFail($validated['template_id']);
        $patient = Patient::findOrFail($validated['patient_id']);

        // Check if user can use this template
        $this->authorize('use', $template);

        try {
            $document = $this->pdfService->generate($template, $patient, [
                'title' => $validated['title'] ?? $template->name,
                'notes' => $validated['notes'] ?? null,
                'custom_data' => $validated['custom_data'] ?? [],
            ]);

            return redirect()
                ->route('admin.generated-documents.show', $document)
                ->with('success', 'Document generated successfully.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to generate document: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified document.
     */
    public function show(GeneratedDocument $generatedDocument)
    {
        $this->authorize('view', $generatedDocument);

        $generatedDocument->load(['template', 'patient', 'generator']);

        return view('admin.generated-documents.show', compact('generatedDocument'));
    }

    /**
     * Download the PDF document.
     */
    public function download(GeneratedDocument $generatedDocument)
    {
        $this->authorize('download', $generatedDocument);

        try {
            return $this->pdfService->streamPdf($generatedDocument);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * Preview the merged content.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:templates,id',
            'patient_id' => 'required|exists:patients,id',
        ]);

        $template = Template::findOrFail($validated['template_id']);
        $patient = Patient::findOrFail($validated['patient_id']);

        $this->authorize('use', $template);

        $content = $this->mergeService->preview($template, $patient);

        return response()->json([
            'content' => $content,
            'patient_name' => $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name),
            'template_name' => $template->name,
        ]);
    }

    /**
     * Finalize the document.
     */
    public function finalize(GeneratedDocument $generatedDocument)
    {
        $this->authorize('finalize', $generatedDocument);

        $generatedDocument->markAsFinal();

        return back()->with('success', 'Document finalized successfully.');
    }

    /**
     * Void the document.
     */
    public function void(GeneratedDocument $generatedDocument)
    {
        $this->authorize('void', $generatedDocument);

        $generatedDocument->void();

        return back()->with('success', 'Document voided successfully.');
    }

    /**
     * Show form to send document via email.
     */
    public function sendForm(GeneratedDocument $generatedDocument)
    {
        $this->authorize('send', $generatedDocument);

        $generatedDocument->load('patient');

        return view('admin.generated-documents.send', compact('generatedDocument'));
    }

    /**
     * Send document via email.
     */
    public function send(Request $request, GeneratedDocument $generatedDocument)
    {
        $this->authorize('send', $generatedDocument);

        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        try {
            // Get PDF content
            $pdfContent = $this->pdfService->getPdfContent($generatedDocument);

            if (!$pdfContent) {
                return back()->with('error', 'PDF file not found.');
            }

            // Send email with attachment
            Mail::send('emails.documents.generated-document', [
                'document' => $generatedDocument,
                'customMessage' => $validated['message'] ?? null,
            ], function ($mail) use ($validated, $generatedDocument, $pdfContent) {
                $mail->to($validated['email'])
                    ->subject($validated['subject'] ?? 'Document: ' . $generatedDocument->title)
                    ->attachData($pdfContent, $generatedDocument->file_name, [
                        'mime' => 'application/pdf',
                    ]);
            });

            // Mark as sent
            $generatedDocument->markAsSent($validated['email']);

            return redirect()
                ->route('admin.generated-documents.show', $generatedDocument)
                ->with('success', 'Document sent successfully to ' . $validated['email']);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send document: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate the PDF.
     */
    public function regenerate(GeneratedDocument $generatedDocument)
    {
        $this->authorize('update', $generatedDocument);

        try {
            $this->pdfService->regenerate($generatedDocument);

            return back()->with('success', 'Document regenerated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to regenerate document: ' . $e->getMessage());
        }
    }

    /**
     * Delete the document.
     */
    public function destroy(GeneratedDocument $generatedDocument)
    {
        $this->authorize('delete', $generatedDocument);

        $generatedDocument->delete();

        return redirect()
            ->route('admin.generated-documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * List documents for a specific patient (for embedding in patient view).
     */
    public function patientDocuments(Patient $patient, Request $request)
    {
        $this->authorize('viewAny', GeneratedDocument::class);

        $user = Auth::user();

        $documents = GeneratedDocument::generatedBy($user)
            ->forPatient($patient->id)
            ->with(['template', 'generator'])
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return view('admin.generated-documents.patient-documents', compact('patient', 'documents'));
    }
}
