<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeyFeature;
use App\Models\KeyFeaturesSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KeyFeatureController extends Controller
{
    public function index()
    {
        $keyFeatures = KeyFeature::ordered()->paginate(10);
        $section = KeyFeaturesSection::first();
        return view('admin.key-features.index', compact('keyFeatures', 'section'));
    }

    public function create()
    {
        return view('admin.key-features.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('key-features', $filename, 'public');
            $validated['image_url'] = $imagePath;
        }

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = KeyFeature::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->has('is_active');

        KeyFeature::create($validated);

        return redirect()->route('admin.key-features.index')
            ->with('success', 'Key feature created successfully.');
    }

    public function show(KeyFeature $keyFeature)
    {
        return view('admin.key-features.show', compact('keyFeature'));
    }

    public function edit(KeyFeature $keyFeature)
    {
        return view('admin.key-features.edit', compact('keyFeature'));
    }

    public function update(Request $request, KeyFeature $keyFeature)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($keyFeature->image_url && Storage::disk('public')->exists($keyFeature->image_url)) {
                Storage::disk('public')->delete($keyFeature->image_url);
            }

            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('key-features', $filename, 'public');
            $validated['image_url'] = $imagePath;
        }

        $validated['is_active'] = $request->has('is_active');

        $keyFeature->update($validated);

        return redirect()->route('admin.key-features.index')
            ->with('success', 'Key feature updated successfully.');
    }

    public function destroy(KeyFeature $keyFeature)
    {
        // Delete image if it exists
        if ($keyFeature->image_url && Storage::disk('public')->exists($keyFeature->image_url)) {
            Storage::disk('public')->delete($keyFeature->image_url);
        }

        $keyFeature->delete();

        return redirect()->route('admin.key-features.index')
            ->with('success', 'Key feature deleted successfully.');
    }

    public function toggleStatus(KeyFeature $keyFeature)
    {
        $keyFeature->update(['is_active' => !$keyFeature->is_active]);

        return redirect()->route('admin.key-features.index')
            ->with('success', 'Key feature status updated successfully.');
    }

    /**
     * Update the key features section settings
     */
    public function updateSection(Request $request)
    {
        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
            'section_subtitle' => 'nullable|string|max:255',
            'section_description' => 'nullable|string',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_color' => 'nullable|string|max:7'
        ]);

        $section = KeyFeaturesSection::first();
        
        // Handle image upload
        if ($request->hasFile('main_image')) {
            // Delete old image if it exists
            if ($section && $section->main_image && Storage::disk('public')->exists($section->main_image)) {
                Storage::disk('public')->delete($section->main_image);
            }

            $image = $request->file('main_image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('key-features-section', $filename, 'public');
            $validated['main_image'] = $imagePath;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['background_color'] = $validated['background_color'] ?: '#f8f9fa';

        if ($section) {
            $section->update($validated);
        } else {
            KeyFeaturesSection::create($validated);
        }

        return redirect()->route('admin.key-features.index')
            ->with('success', 'Key features section updated successfully.');
    }
}
