<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerSlideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $slides = BannerSlide::ordered()->get();
        return view('admin.banner-slides.index', compact('slides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.banner-slides.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:7',
            'background_color' => 'required|string|max:7',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'remove_image' => 'boolean'
        ]);
        
        // Custom validation for button_url (allow both relative and absolute URLs)
        if ($request->filled('button_url')) {
            $url = $request->input('button_url');
            // Check if it's a relative URL (starts with /) or a valid absolute URL
            if (!str_starts_with($url, '/') && !filter_var($url, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['button_url' => 'The button URL must be a valid URL or relative path (e.g., /about, /contact).'])->withInput();
            }
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['text_color'] = $data['text_color'] ?? '#ffffff';
        $data['sort_order'] = $data['sort_order'] ?? 1;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banner-slides', 'public');
        }

        BannerSlide::create($data);

        return redirect()->route('admin.banner-slides.index')
            ->with('success', 'Banner slide created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BannerSlide $bannerSlide)
    {
        return view('admin.banner-slides.show', ['slide' => $bannerSlide]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BannerSlide $bannerSlide)
    {
        return view('admin.banner-slides.edit', ['slide' => $bannerSlide]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BannerSlide $bannerSlide)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:7',
            'background_color' => 'required|string|max:7',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'remove_image' => 'boolean'
        ]);
        
        // Custom validation for button_url (allow both relative and absolute URLs)
        if ($request->filled('button_url')) {
            $url = $request->input('button_url');
            // Check if it's a relative URL (starts with /) or a valid absolute URL
            if (!str_starts_with($url, '/') && !filter_var($url, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['button_url' => 'The button URL must be a valid URL or relative path (e.g., /about, /contact).'])->withInput();
            }
        }

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['text_color'] = $data['text_color'] ?? '#ffffff';

        // Handle image removal
        if ($request->input('remove_image') == '1') {
            if ($bannerSlide->image) {
                Storage::disk('public')->delete($bannerSlide->image);
            }
            $data['image'] = null;
        }
        
        // Handle new image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($bannerSlide->image) {
                Storage::disk('public')->delete($bannerSlide->image);
            }
            $data['image'] = $request->file('image')->store('banner-slides', 'public');
        }

        $bannerSlide->update($data);

        return redirect()->route('admin.banner-slides.index')
            ->with('success', 'Banner slide updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BannerSlide $bannerSlide)
    {
        if ($bannerSlide->image) {
            Storage::disk('public')->delete($bannerSlide->image);
        }
        
        $bannerSlide->delete();

        return redirect()->route('admin.banner-slides.index')
            ->with('success', 'Banner slide deleted successfully.');
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus(BannerSlide $bannerSlide)
    {
        $bannerSlide->update([
            'is_active' => !$bannerSlide->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner slide status updated successfully.',
            'is_active' => $bannerSlide->is_active
        ]);
    }
}
