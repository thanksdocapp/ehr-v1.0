<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\DocumentSetting;
use App\Models\DocumentTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentSettingsController extends Controller
{
    /**
     * Display document settings page.
     */
    public function index()
    {
        $settings = DocumentSetting::getAllGrouped();
        $groups = DocumentSetting::getGroups();
        $categories = DocumentCategory::with('children')->roots()->orderBy('sort_order')->get();

        // Get statistics
        $stats = [
            'total_templates' => DocumentTemplate::count(),
            'active_templates' => DocumentTemplate::where('is_active', true)->count(),
            'letter_templates' => DocumentTemplate::where('type', 'letter')->count(),
            'form_templates' => DocumentTemplate::where('type', 'form')->count(),
            'categories' => DocumentCategory::count(),
        ];

        return view('admin.document-settings.index', compact('settings', 'groups', 'categories', 'stats'));
    }

    /**
     * Update document settings.
     */
    public function update(Request $request)
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            $existing = \DB::table('document_settings')->where('key', $key)->first();
            if ($existing) {
                $type = $existing->type;

                // Handle boolean values
                if ($type === 'boolean') {
                    $value = $value === 'on' || $value === '1' || $value === true ? 'true' : 'false';
                }

                DocumentSetting::set($key, $value, $type);
            }
        }

        // Clear settings cache
        DocumentSetting::clearCache();

        return redirect()->route('admin.document-settings.index')
            ->with('success', 'Document settings updated successfully.');
    }

    /**
     * List categories.
     */
    public function categories()
    {
        $categories = DocumentCategory::with(['children', 'templates'])
            ->roots()
            ->orderBy('sort_order')
            ->get();

        return view('admin.document-settings.categories', compact('categories'));
    }

    /**
     * Store a new category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'type' => 'nullable|in:letter,form',
            'parent_id' => 'nullable|exists:document_categories,id',
            'is_active' => 'boolean',
        ]);

        // Handle empty type as null (both letter and form)
        if (empty($validated['type'])) {
            $validated['type'] = null;
        }

        $validated['slug'] = DocumentCategory::generateSlug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['color'] = $validated['color'] ?? '#667eea';
        $validated['icon'] = $validated['icon'] ?? 'fa-folder';

        // Get next sort order
        $maxSort = DocumentCategory::where('parent_id', $validated['parent_id'] ?? null)->max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSort + 1;

        $category = DocumentCategory::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.document-settings.categories')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Get category for editing (AJAX).
     */
    public function editCategory(DocumentCategory $category)
    {
        return response()->json($category);
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, DocumentCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'type' => 'nullable|in:letter,form',
            'parent_id' => 'nullable|exists:document_categories,id',
            'is_active' => 'boolean',
        ]);

        // Handle empty type as null (both letter and form)
        if (empty($validated['type'])) {
            $validated['type'] = null;
        }

        // Prevent setting parent to self or descendant
        if (isset($validated['parent_id'])) {
            if ($validated['parent_id'] == $category->id) {
                return back()->with('error', 'Category cannot be its own parent.');
            }
            // Check descendants
            $descendantIds = $this->getDescendantIds($category);
            if (in_array($validated['parent_id'], $descendantIds)) {
                return back()->with('error', 'Category cannot be a child of its descendant.');
            }
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category->fresh(),
            ]);
        }

        return redirect()->route('admin.document-settings.categories')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function destroyCategory(DocumentCategory $category)
    {
        // Check if category has templates
        if ($category->templates()->exists()) {
            return back()->with('error', 'Cannot delete category with templates. Please move or delete templates first.');
        }

        // Check if category has children
        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category with subcategories. Please delete subcategories first.');
        }

        $category->delete();

        return redirect()->route('admin.document-settings.categories')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Reorder categories.
     */
    public function reorderCategories(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:document_categories,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['order'] as $item) {
            DocumentCategory::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully.',
        ]);
    }

    /**
     * Get descendant IDs for a category.
     */
    protected function getDescendantIds(DocumentCategory $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    /**
     * Get icon options for categories.
     */
    public function getIconOptions()
    {
        $icons = [
            'General' => [
                'fa-folder' => 'Folder',
                'fa-folder-open' => 'Folder Open',
                'fa-file-alt' => 'Document',
                'fa-file-medical' => 'Medical File',
                'fa-file-contract' => 'Contract',
            ],
            'Medical' => [
                'fa-stethoscope' => 'Stethoscope',
                'fa-heartbeat' => 'Heartbeat',
                'fa-pills' => 'Pills',
                'fa-syringe' => 'Syringe',
                'fa-prescription' => 'Prescription',
                'fa-notes-medical' => 'Medical Notes',
                'fa-hospital' => 'Hospital',
                'fa-ambulance' => 'Ambulance',
            ],
            'Forms & Letters' => [
                'fa-envelope-open-text' => 'Letter',
                'fa-clipboard-list' => 'Checklist',
                'fa-clipboard-check' => 'Clipboard Check',
                'fa-edit' => 'Edit',
                'fa-pen' => 'Pen',
                'fa-signature' => 'Signature',
                'fa-file-signature' => 'Signed Document',
            ],
            'Administrative' => [
                'fa-user-check' => 'User Check',
                'fa-id-card' => 'ID Card',
                'fa-certificate' => 'Certificate',
                'fa-award' => 'Award',
                'fa-balance-scale' => 'Legal',
                'fa-gavel' => 'Gavel',
            ],
        ];

        return response()->json($icons);
    }

    /**
     * Get color options for categories.
     */
    public function getColorOptions()
    {
        $colors = [
            '#667eea' => 'Purple',
            '#764ba2' => 'Deep Purple',
            '#11998e' => 'Teal',
            '#38ef7d' => 'Green',
            '#4e73df' => 'Blue',
            '#1cc88a' => 'Success',
            '#36b9cc' => 'Cyan',
            '#f6c23e' => 'Yellow',
            '#e74a3b' => 'Red',
            '#858796' => 'Gray',
            '#5a5c69' => 'Dark Gray',
            '#2e59d9' => 'Royal Blue',
        ];

        return response()->json($colors);
    }
}
