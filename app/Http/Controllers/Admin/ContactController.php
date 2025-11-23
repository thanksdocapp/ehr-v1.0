<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Display the Contact management page.
     */
    public function index()
    {
        // Get all current settings as key-value pairs
        $settings = SiteSetting::getSettings();
        
        return view('admin.contact.index', compact('settings'));
    }

    /**
     * Show the form for editing Contact settings.
     */
    public function edit()
    {
        // Get all current settings as key-value pairs
        $settings = SiteSetting::getSettings();
        
        return view('admin.contact.edit', compact('settings'));
    }

    /**
     * Update the Contact settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_hero_title' => 'required|string|max:255',
            'contact_hero_subtitle' => 'required|string|max:500',
            'contact_form_title' => 'required|string|max:255',
            'contact_form_subtitle' => 'required|string|max:500',
            'contact_form_success_message' => 'required|string|max:500',
            'contact_emergency_phone' => 'required|string|max:20',
            'contact_general_phone' => 'required|string|max:20',
            'contact_appointments_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'required|email|max:255',
            'contact_address' => 'required|string|max:500',
            // Global Offices
            'global_office_1_name' => 'nullable|string|max:255',
            'global_office_1_address' => 'nullable|string|max:500', 
            'global_office_1_phone' => 'nullable|string|max:20',
            'global_office_2_name' => 'nullable|string|max:255',
            'global_office_2_address' => 'nullable|string|max:500',
            'global_office_2_phone' => 'nullable|string|max:20',
            'global_office_3_name' => 'nullable|string|max:255',
            'global_office_3_address' => 'nullable|string|max:500',
            'global_office_3_phone' => 'nullable|string|max:20',
            'global_office_4_name' => 'nullable|string|max:255',
            'global_office_4_address' => 'nullable|string|max:500',
            'global_office_4_phone' => 'nullable|string|max:20',
            'contact_emergency_hours' => 'required|string|max:50',
            'contact_outpatient_hours' => 'required|string|max:50',
            'contact_visitor_hours' => 'required|string|max:50',
            'contact_pharmacy_hours' => 'required|string|max:50',
            'contact_map_embed_url' => 'nullable|url|max:1000',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_linkedin' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_whatsapp' => 'nullable|string|max:20',
            'social_facebook_enabled' => 'boolean',
            'social_twitter_enabled' => 'boolean',
            'social_instagram_enabled' => 'boolean',
            'social_linkedin_enabled' => 'boolean',
            'social_youtube_enabled' => 'boolean',
            'social_whatsapp_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update contact settings using key-value pairs
            SiteSetting::set('contact_hero_title', $request->contact_hero_title);
            SiteSetting::set('contact_hero_subtitle', $request->contact_hero_subtitle);
            SiteSetting::set('contact_form_title', $request->contact_form_title);
            SiteSetting::set('contact_form_subtitle', $request->contact_form_subtitle);
            SiteSetting::set('contact_form_success_message', $request->contact_form_success_message);
            SiteSetting::set('contact_emergency_phone', $request->contact_emergency_phone);
            SiteSetting::set('contact_general_phone', $request->contact_general_phone);
            SiteSetting::set('contact_appointments_email', $request->contact_appointments_email);
            
            // Update the basic contact settings that appear in header/footer
            SiteSetting::set('contact_phone', $request->contact_phone);
            SiteSetting::set('contact_email', $request->contact_email);
            SiteSetting::set('contact_address', $request->contact_address);
            
            // Update Global Offices settings
            SiteSetting::set('global_office_1_name', $request->global_office_1_name ?? '');
            SiteSetting::set('global_office_1_address', $request->global_office_1_address ?? '');
            SiteSetting::set('global_office_1_phone', $request->global_office_1_phone ?? '');
            SiteSetting::set('global_office_2_name', $request->global_office_2_name ?? '');
            SiteSetting::set('global_office_2_address', $request->global_office_2_address ?? '');
            SiteSetting::set('global_office_2_phone', $request->global_office_2_phone ?? '');
            SiteSetting::set('global_office_3_name', $request->global_office_3_name ?? '');
            SiteSetting::set('global_office_3_address', $request->global_office_3_address ?? '');
            SiteSetting::set('global_office_3_phone', $request->global_office_3_phone ?? '');
            SiteSetting::set('global_office_4_name', $request->global_office_4_name ?? '');
            SiteSetting::set('global_office_4_address', $request->global_office_4_address ?? '');
            SiteSetting::set('global_office_4_phone', $request->global_office_4_phone ?? '');
            SiteSetting::set('contact_emergency_hours', $request->contact_emergency_hours);
            SiteSetting::set('contact_outpatient_hours', $request->contact_outpatient_hours);
            SiteSetting::set('contact_visitor_hours', $request->contact_visitor_hours);
            SiteSetting::set('contact_pharmacy_hours', $request->contact_pharmacy_hours);
            
            if ($request->filled('contact_map_embed_url')) {
                SiteSetting::set('contact_map_embed_url', $request->contact_map_embed_url);
            }
            
            // Update social media settings and enable/disable flags
            if ($request->filled('social_facebook')) {
                SiteSetting::set('social_facebook', $request->social_facebook);
            }
            SiteSetting::set('social_facebook_enabled', $request->has('social_facebook_enabled') ? '1' : '0');
            
            if ($request->filled('social_twitter')) {
                SiteSetting::set('social_twitter', $request->social_twitter);
            }
            SiteSetting::set('social_twitter_enabled', $request->has('social_twitter_enabled') ? '1' : '0');
            
            if ($request->filled('social_instagram')) {
                SiteSetting::set('social_instagram', $request->social_instagram);
            }
            SiteSetting::set('social_instagram_enabled', $request->has('social_instagram_enabled') ? '1' : '0');
            
            if ($request->filled('social_linkedin')) {
                SiteSetting::set('social_linkedin', $request->social_linkedin);
            }
            SiteSetting::set('social_linkedin_enabled', $request->has('social_linkedin_enabled') ? '1' : '0');
            
            if ($request->filled('social_youtube')) {
                SiteSetting::set('social_youtube', $request->social_youtube);
            }
            SiteSetting::set('social_youtube_enabled', $request->has('social_youtube_enabled') ? '1' : '0');
            
            if ($request->filled('social_whatsapp')) {
                SiteSetting::set('social_whatsapp', $request->social_whatsapp);
            }
            SiteSetting::set('social_whatsapp_enabled', $request->has('social_whatsapp_enabled') ? '1' : '0');

            return redirect()->route('admin.contact.index')
                ->with('success', 'Contact page settings updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Contact settings update failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Failed to update contact settings. Error: ' . $e->getMessage())
                ->withInput();
        }
    }
}