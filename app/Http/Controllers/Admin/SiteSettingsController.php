<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settingsByGroup = SiteSetting::active()
            ->ordered()
            ->get()
            ->groupBy('group');

        return view('admin.settings.site-settings', compact('settingsByGroup'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
            'files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            
            if ($setting) {
                // Handle file uploads
                if ($setting->type === 'image' && $request->hasFile("files.{$key}")) {
                    $file = $request->file("files.{$key}");
                    $filename = time() . '_' . Str::slug($setting->label) . '.' . $file->getClientOriginalExtension();
                    
                    // Delete old file if it exists
                    if ($setting->value && Storage::disk('public')->exists('uploads/settings/' . $setting->value)) {
                        Storage::disk('public')->delete('uploads/settings/' . $setting->value);
                    }
                    
                    $path = $file->storeAs('uploads/settings', $filename, 'public');
                    $value = $filename;
                }

                $setting->update(['value' => $value]);
            }
        }

        return redirect()
            ->route('admin.settings.site-settings')
            ->with('success', 'Site settings updated successfully!');
    }

    public function create()
    {
        return view('admin.settings.create-setting');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|unique:site_settings,key',
            'label' => 'required|string',
            'type' => 'required|in:text,textarea,image,boolean,number',
            'group' => 'required|string',
            'value' => 'nullable|string',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer'
        ]);

        SiteSetting::create($request->all());

        return redirect()
            ->route('admin.settings.site-settings')
            ->with('success', 'Setting created successfully!');
    }

    public function destroy(SiteSetting $setting)
    {
        // Delete associated file if it's an image type
        if ($setting->type === 'image' && $setting->value) {
            Storage::disk('public')->delete('uploads/settings/' . $setting->value);
        }

        $setting->delete();

        return redirect()
            ->route('admin.settings.site-settings')
            ->with('success', 'Setting deleted successfully!');
    }

    public function toggleStatus(SiteSetting $setting)
    {
        $setting->update(['is_active' => !$setting->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Setting status updated successfully!',
            'is_active' => $setting->is_active
        ]);
    }
}
