<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicesSection;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::ordered()->paginate(10);
        $section = ServicesSection::first();
        return view('admin.services.index', compact('services', 'section'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.services.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('services', $filename, 'public');
            $validated['image'] = $imagePath;
            $validated['image_url'] = $imagePath; // For compatibility
        }

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Service::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->has('is_active');

        Service::create($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            $oldImagePath = $service->image ?? $service->image_url;
            if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                Storage::disk('public')->delete($oldImagePath);
            }

            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('services', $filename, 'public');
            $validated['image'] = $imagePath;
            $validated['image_url'] = $imagePath; // For compatibility
        }

        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        // Delete image if it exists
        if ($service->image_url && Storage::disk('public')->exists($service->image_url)) {
            Storage::disk('public')->delete($service->image_url);
        }

        $service->delete();

        return redirect()->route('admin.services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function toggleStatus(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service status updated successfully.');
    }

    /**
     * Update the services section settings
     */
    public function updateSection(Request $request)
    {
        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
            'section_subtitle' => 'nullable|string|max:255',
            'section_description' => 'nullable|string',
            'background_color' => 'nullable|string|max:7'
        ]);

        $section = ServicesSection::first();
        
        $validated['is_active'] = $request->has('is_active');
        $validated['background_color'] = $validated['background_color'] ?: '#f8f9fa';

        if ($section) {
            $section->update($validated);
        } else {
            ServicesSection::create($validated);
        }

        return redirect()->route('admin.services.index')
            ->with('success', 'Services section updated successfully.');
    }
}
