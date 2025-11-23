<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::ordered()->paginate(10);
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        $doctors = Doctor::active()->ordered()->get();
        return view('admin.testimonials.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_position' => 'nullable|string|max:255',
            'customer_company' => 'nullable|string|max:255',
            'review_text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'customer_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        // Handle image upload
        if ($request->hasFile('customer_photo')) {
            $validated['customer_photo'] = $request->file('customer_photo')->store('testimonials', 'public');
        }

        // Set default sort order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Testimonial::max('sort_order') + 1;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        Testimonial::create($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function show(Testimonial $testimonial)
    {
        return view('admin.testimonials.show', compact('testimonial'));
    }

    public function edit(Testimonial $testimonial)
    {
        $doctors = Doctor::active()->ordered()->get();
        return view('admin.testimonials.edit', compact('testimonial', 'doctors'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_position' => 'nullable|string|max:255',
            'customer_company' => 'nullable|string|max:255',
            'review_text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'customer_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'remove_photo' => 'boolean'
        ]);

        // Handle image removal
        if ($request->input('remove_photo') == '1') {
            if ($testimonial->customer_photo) {
                Storage::disk('public')->delete($testimonial->customer_photo);
            }
            $validated['customer_photo'] = null;
        }

        // Handle image upload
        if ($request->hasFile('customer_photo')) {
            // Delete old image if it exists
            if ($testimonial->customer_photo) {
                Storage::disk('public')->delete($testimonial->customer_photo);
            }
            $validated['customer_photo'] = $request->file('customer_photo')->store('testimonials', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $testimonial->update($validated);

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function destroy(Testimonial $testimonial)
    {
        // Delete image if it exists
        if ($testimonial->customer_photo && Storage::disk('public')->exists($testimonial->customer_photo)) {
            Storage::disk('public')->delete($testimonial->customer_photo);
        }

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function toggleStatus(Testimonial $testimonial)
    {
        $testimonial->update(['is_active' => !$testimonial->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial status updated successfully.',
            'is_active' => $testimonial->is_active
        ]);
    }
}
