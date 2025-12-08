<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplatesController extends Controller
{
    /**
     * Display a listing of templates.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Template::class);

        $user = Auth::user();

        $query = Template::visibleTo($user);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter own vs system templates
        if ($request->filled('ownership')) {
            if ($request->ownership === 'own') {
                $query->where('created_by', $user->id);
            } elseif ($request->ownership === 'system') {
                $query->where('is_system', true);
            }
        }

        $templates = $query->with('creator')
            ->latest()
            ->paginate(15)
            ->appends($request->query());

        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Template::class);

        $type = $request->get('type', 'letter');
        $placeholders = Template::DEFAULT_PLACEHOLDERS;

        return view('admin.templates.create', compact('type', 'placeholders'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Template::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:letter,form',
            'content' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'placeholders' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Only admin can set is_system
        if (!$user->is_admin && $user->role !== 'admin') {
            $validated['is_system'] = false;
        }

        $template = Template::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_system' => $validated['is_system'] ?? false,
            'placeholders' => $validated['placeholders'] ?? null,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('admin.templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    /**
     * Display the specified template.
     */
    public function show(Template $template)
    {
        $this->authorize('view', $template);

        $template->load('creator', 'generatedDocuments');
        $usageCount = $template->generatedDocuments()->count();

        return view('admin.templates.show', compact('template', 'usageCount'));
    }

    /**
     * Show the form for editing the specified template.
     */
    public function edit(Template $template)
    {
        $this->authorize('update', $template);

        $placeholders = Template::DEFAULT_PLACEHOLDERS;

        return view('admin.templates.edit', compact('template', 'placeholders'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:letter,form',
            'content' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'placeholders' => 'nullable|array',
        ]);

        $user = Auth::user();

        // Only admin can set is_system
        if (!$user->is_admin && $user->role !== 'admin') {
            unset($validated['is_system']);
        }

        $template->update(array_merge($validated, [
            'updated_by' => $user->id,
        ]));

        return redirect()
            ->route('admin.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    /**
     * Remove the specified template.
     */
    public function destroy(Template $template)
    {
        $this->authorize('delete', $template);

        // Check if template has been used
        if ($template->generatedDocuments()->exists()) {
            return back()->with('error', 'Cannot delete template that has been used to generate documents.');
        }

        $template->delete();

        return redirect()
            ->route('admin.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(Template $template)
    {
        $this->authorize('duplicate', $template);

        $newTemplate = $template->replicate([
            'created_at',
            'updated_at',
        ]);
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->is_system = false;
        $newTemplate->is_active = false;
        $newTemplate->created_by = Auth::id();
        $newTemplate->updated_by = null;
        $newTemplate->save();

        return redirect()
            ->route('admin.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully. You can now edit it.');
    }

    /**
     * Toggle template active status.
     */
    public function toggleActive(Template $template)
    {
        $this->authorize('toggleActive', $template);

        $template->update([
            'is_active' => !$template->is_active,
            'updated_by' => Auth::id(),
        ]);

        $status = $template->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Template {$status} successfully.");
    }

    /**
     * Preview template with sample data.
     */
    public function preview(Request $request, Template $template)
    {
        $this->authorize('view', $template);

        // Return the template content for preview
        return response()->json([
            'content' => $template->content,
            'placeholders' => $template->extractUsedPlaceholders(),
        ]);
    }
}
