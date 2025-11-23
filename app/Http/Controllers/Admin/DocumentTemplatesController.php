<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentTemplate;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentTemplatesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', DocumentTemplate::class);

        $query = DocumentTemplate::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $templates = $query->with(['creator', 'updater'])
            ->latest()
            ->paginate(20)->appends($request->query());

        return view('admin.document-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', DocumentTemplate::class);

        return view('admin.document-templates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', DocumentTemplate::class);

        // Decode builder_config if it's a JSON string
        if ($request->has('builder_config') && is_string($request->builder_config)) {
            $decoded = json_decode($request->builder_config, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['builder_config' => $decoded]);
            } else {
                $request->merge(['builder_config' => null]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:letter,form',
            'slug' => 'nullable|string|unique:document_templates,slug',
            'builder_config' => 'nullable|array',
            'render_mode' => 'nullable|string|in:builder,html',
            'content' => 'nullable|string',
            'schema' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $baseSlug = $validated['slug'];
            $counter = 1;
            while (DocumentTemplate::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $baseSlug . '-' . $counter;
                $counter++;
            }
        }

        // Validate builder_config if type is form
        if ($validated['type'] === 'form' && isset($validated['builder_config'])) {
            $this->validateFormBuilderConfig($validated['builder_config']);
        }

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $template = DocumentTemplate::create($validated);

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Document template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, DocumentTemplate $documentTemplate)
    {
        $this->authorize('view', $documentTemplate);

        $documentTemplate->load(['creator', 'updater', 'patientDocuments']);

        // Handle preview request
        if ($request->has('preview') && $request->boolean('preview')) {
            return $this->previewTemplate($request, $documentTemplate);
        }

        return view('admin.document-templates.show', compact('documentTemplate'));
    }
    
    /**
     * Preview template with sample data.
     */
    protected function previewTemplate(Request $request, DocumentTemplate $documentTemplate)
    {
        // Get sample patient for preview
        $samplePatient = Patient::first();
        
        if (!$samplePatient) {
            return response()->json([
                'error' => 'No patient data available for preview. Please create a patient first.'
            ], 404);
        }
        
        // Get builder config from request or use template's config
        $builderConfig = $request->has('builder_config') 
            ? json_decode($request->builder_config, true) 
            : ($documentTemplate->builder_config ?? []);
        
        if ($documentTemplate->type === 'letter') {
            // Render letter with sample data
            $renderer = app(\App\Services\TemplateRenderer::class);
            $branding = $this->getBranding(Auth::user());
            
            try {
                $html = $renderer->renderLetter(
                    $documentTemplate,
                    $samplePatient,
                    Auth::user(),
                    [],
                    $branding
                );
                
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'patient' => [
                        'name' => $samplePatient->full_name,
                        'id' => $samplePatient->patient_id
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to render preview: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Form preview - show schema
            return response()->json([
                'success' => true,
                'type' => 'form',
                'schema' => $documentTemplate->schema ?? []
            ]);
        }
    }
    
    /**
     * Get branding information.
     */
    protected function getBranding($user): array
    {
        $branding = [];
        
        if (function_exists('getLogo')) {
            $branding['clinic_logo'] = getLogo('light');
        }
        
        return $branding;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentTemplate $documentTemplate)
    {
        $this->authorize('update', $documentTemplate);

        return view('admin.document-templates.edit', compact('documentTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentTemplate $documentTemplate)
    {
        $this->authorize('update', $documentTemplate);

        // Decode builder_config if it's a JSON string
        if ($request->has('builder_config') && is_string($request->builder_config)) {
            $decoded = json_decode($request->builder_config, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['builder_config' => $decoded]);
            } else {
                $request->merge(['builder_config' => null]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:letter,form',
            'slug' => 'nullable|string|unique:document_templates,slug,' . $documentTemplate->id,
            'builder_config' => 'nullable|array',
            'render_mode' => 'nullable|string|in:builder,html',
            'content' => 'nullable|string',
            'schema' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Validate builder_config if type is form
        if ($validated['type'] === 'form' && isset($validated['builder_config'])) {
            $this->validateFormBuilderConfig($validated['builder_config']);
        }

        $validated['updated_by'] = Auth::id();

        $documentTemplate->update($validated);

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Document template updated successfully.');
    }

    /**
     * Deactivate the template.
     */
    public function deactivate(DocumentTemplate $documentTemplate)
    {
        $this->authorize('deactivate', $documentTemplate);

        $documentTemplate->update([
            'is_active' => false,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Template deactivated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentTemplate $documentTemplate)
    {
        $this->authorize('delete', $documentTemplate);

        // Check if template is in use
        if ($documentTemplate->patientDocuments()->exists()) {
            return back()->with('error', 'Cannot delete template that is in use by patient documents.');
        }

        $documentTemplate->delete();

        return redirect()
            ->route('admin.document-templates.index')
            ->with('success', 'Document template deleted successfully.');
    }

    /**
     * Validate form builder config.
     */
    protected function validateFormBuilderConfig(array $config): void
    {
        // Check for at least one section
        $hasSection = false;
        $fieldNames = [];

        foreach ($config as $block) {
            if (($block['type'] ?? null) === 'section') {
                $hasSection = true;
                
                // Check children for unique field names
                $children = $block['children'] ?? [];
                foreach ($children as $child) {
                    $fieldType = $child['type'] ?? null;
                    $fieldName = $child['props']['name'] ?? null;
                    
                    // Only validate field types (not info_text)
                    if (in_array($fieldType, ['text', 'textarea', 'select', 'checkbox', 'checkbox_group', 'radio_group', 'date', 'number'])) {
                        if ($fieldName) {
                            if (in_array($fieldName, $fieldNames)) {
                                throw new \Illuminate\Validation\ValidationException(
                                    validator([], []),
                                    ['builder_config' => "Duplicate field name: {$fieldName}"]
                                );
                            }
                            $fieldNames[] = $fieldName;
                        }
                    }
                }
            }
        }

        if (!$hasSection) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                ['builder_config' => 'Form templates must contain at least one section with fields.']
            );
        }
    }
}
