<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestimonialsSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialsSectionController extends Controller
{
    public function index()
    {
        $section = TestimonialsSection::firstOrCreate([]);
        return view('admin.testimonials-section.index', compact('section'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'section_title' => 'nullable|string|max:255',
            'section_subtitle' => 'nullable|string|max:500',
            'section_description' => 'nullable|string|max:1000',
            'background_color' => 'nullable|string|max:20',
            'text_color' => 'nullable|string|max:20',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $section = TestimonialsSection::firstOrCreate([]);
        
        $data = $request->only([
            'section_title',
            'section_subtitle', 
            'section_description',
            'background_color',
            'text_color',
            'is_active'
        ]);

        // Handle image upload
        if ($request->hasFile('main_image')) {
            // Delete old image if exists
            if ($section->main_image) {
                Storage::disk('public')->delete($section->main_image);
            }
            
            $imagePath = $request->file('main_image')->store('testimonials-section', 'public');
            $data['main_image'] = $imagePath;
        }

        $section->update($data);

        return redirect()->route('admin.testimonials-section.index')
                        ->with('success', 'Testimonials section updated successfully.');
    }
}
