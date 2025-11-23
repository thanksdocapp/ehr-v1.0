<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AboutUsController extends Controller
{
    /**
     * Display the About Us management page.
     */
    public function index()
    {
        // Get all current settings as key-value pairs
        $settings = SiteSetting::getSettings();
        
        return view('admin.about-us.index', compact('settings'));
    }

    /**
     * Show the form for editing About Us settings.
     */
    public function edit()
    {
        // Get all current settings as key-value pairs
        $settings = SiteSetting::getSettings();
        
        return view('admin.about-us.edit', compact('settings'));
    }

    /**
     * Update the About Us settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'about_hero_title' => 'required|string|max:255',
            'about_hero_subtitle' => 'required|string|max:500',
            'about_main_title' => 'required|string|max:255',
            'about_main_description' => 'required|string',
            'about_main_content' => 'required|string',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'about_image_alt' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update text settings using key-value pairs
            SiteSetting::set('about_hero_title', $request->about_hero_title);
            SiteSetting::set('about_hero_subtitle', $request->about_hero_subtitle);
            SiteSetting::set('about_main_title', $request->about_main_title);
            SiteSetting::set('about_main_description', $request->about_main_description);
            SiteSetting::set('about_main_content', $request->about_main_content);
            
            // Update image alt text
            if ($request->filled('about_image_alt')) {
                SiteSetting::set('about_image_alt', $request->about_image_alt);
            }

            // Handle image upload
            if ($request->hasFile('about_image')) {
                $image = $request->file('about_image');
                $imageName = 'about-' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('about-us', $imageName, 'public');
                
                // Delete old image if it exists and is not the default
                $oldImage = SiteSetting::get('about_image');
                if ($oldImage && !str_starts_with($oldImage, 'assets/') && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
                
                SiteSetting::set('about_image', $imagePath);
            }

            return redirect()->route('admin.about.index')
                ->with('success', 'About Us content updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update About Us content. Please try again.')
                ->withInput();
        }
    }

    /**
     * Reset About Us image to default.
     */
    public function resetImage()
    {
        try {
            // Get current image
            $currentImage = SiteSetting::get('about_image');
            
            // Delete current image if it's not the default
            if ($currentImage && !str_starts_with($currentImage, 'assets/') && Storage::disk('public')->exists($currentImage)) {
                Storage::disk('public')->delete($currentImage);
            }
            
            // Reset to default image
            SiteSetting::set('about_image', 'assets/images/about-hospital.jpg');
            SiteSetting::set('about_image_alt', 'About ' . getAppName());
            
            return redirect()->route('admin.about.index')
                ->with('success', 'About Us image reset to default successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reset image. Please try again.');
        }
    }
}
