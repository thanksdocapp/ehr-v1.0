<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use App\Models\FrontendTemplate;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeController extends Controller
{
    public function index()
    {
        $currentTheme = ThemeSetting::getCurrent();
        $themes = ThemeSetting::active()->get();
        $currentTemplate = FrontendTemplate::getCurrent();
        $templates = FrontendTemplate::active()->ordered()->get();

        return view('admin.theme.index', compact('currentTheme', 'themes', 'currentTemplate', 'templates'));
    }

    // Theme Settings Management
    public function showThemes()
    {
        $currentTheme = ThemeSetting::getCurrent();
        $themes = ThemeSetting::active()->get();

        return view('admin.theme.themes', compact('currentTheme', 'themes'));
    }

    public function createTheme()
    {
        return view('admin.theme.create-theme');
    }

    public function storeTheme(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'success_color' => 'required|string',
            'danger_color' => 'required|string',
            'warning_color' => 'required|string',
            'info_color' => 'required|string',
            'light_color' => 'required|string',
            'dark_color' => 'required|string',
            'accent_color' => 'required|string',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'card_background' => 'required|string',
            'sidebar_color' => 'required|string',
            'header_color' => 'required|string',
            'footer_color' => 'required|string',
            'primary_font' => 'required|string',
            'secondary_font' => 'required|string',
            'font_size_base' => 'required|string',
            'border_radius' => 'required|string',
            'box_shadow' => 'required|string',
            'container_width' => 'required|string',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('preview_image');
        $data['slug'] = Str::slug($request->name);

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            $file = $request->file('preview_image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/themes', $filename, 'public');
            $data['preview_image'] = $filename;
        }

        $theme = ThemeSetting::create($data);

        // Generate CSS file for the theme
        $this->generateThemeCss($theme);

        return redirect()
            ->route('admin.theme.themes')
            ->with('success', 'Theme created successfully!');
    }

    public function editTheme(ThemeSetting $theme)
    {
        return view('admin.theme.edit-theme', compact('theme'));
    }

    public function updateTheme(Request $request, ThemeSetting $theme)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'success_color' => 'required|string',
            'danger_color' => 'required|string',
            'warning_color' => 'required|string',
            'info_color' => 'required|string',
            'light_color' => 'required|string',
            'dark_color' => 'required|string',
            'accent_color' => 'required|string',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'card_background' => 'required|string',
            'sidebar_color' => 'required|string',
            'header_color' => 'required|string',
            'footer_color' => 'required|string',
            'primary_font' => 'required|string',
            'secondary_font' => 'required|string',
            'font_size_base' => 'required|string',
            'border_radius' => 'required|string',
            'box_shadow' => 'required|string',
            'container_width' => 'required|string',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('preview_image');
        $data['slug'] = Str::slug($request->name);

        // Handle preview image upload
        if ($request->hasFile('preview_image')) {
            // Delete old image
            if ($theme->preview_image) {
                Storage::disk('public')->delete('uploads/themes/' . $theme->preview_image);
            }

            $file = $request->file('preview_image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/themes', $filename, 'public');
            $data['preview_image'] = $filename;
        }

        $theme->update($data);

        // Regenerate CSS file for the theme
        $this->generateThemeCss($theme);

        return redirect()
            ->route('admin.theme.themes')
            ->with('success', 'Theme updated successfully!');
    }

    public function setDefaultTheme(ThemeSetting $theme)
    {
        $theme->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Theme set as default successfully!'
        ]);
    }

    public function duplicateTheme(ThemeSetting $theme)
    {
        $newTheme = $theme->replicate();
        $newTheme->name = $theme->name . ' (Copy)';
        $newTheme->slug = Str::slug($newTheme->name);
        $newTheme->is_default = false;
        $newTheme->save();

        // Generate CSS file for the new theme
        $this->generateThemeCss($newTheme);

        return redirect()
            ->route('admin.theme.themes')
            ->with('success', 'Theme duplicated successfully!');
    }

    // Template Management
    public function showTemplates()
    {
        $currentTemplate = FrontendTemplate::getCurrent();
        $templates = FrontendTemplate::active()->ordered()->get();

        return view('admin.theme.templates', compact('currentTemplate', 'templates'));
    }

    public function setDefaultTemplate(FrontendTemplate $template)
    {
        $template->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Template set as default successfully!'
        ]);
    }

    public function previewTemplate(FrontendTemplate $template)
    {
        // This would show a preview of the template
        return view('admin.theme.preview-template', compact('template'));
    }

    // CSS Generation and Live Preview
    public function generateThemeCss(ThemeSetting $theme)
    {
        $css = $theme->toCssString();
        
        // Save CSS to public directory
        $cssPath = public_path('assets/css/themes');
        if (!File::exists($cssPath)) {
            File::makeDirectory($cssPath, 0755, true);
        }

        File::put($cssPath . '/' . $theme->slug . '.css', $css);

        return $css;
    }

    public function previewTheme(Request $request)
    {
        $request->validate([
            'theme_data' => 'required|array',
            'preview_page' => 'required|in:homepage,services,contact,appointment'
        ]);

        $themeData = $request->theme_data;
        
        // Generate temporary CSS for preview
        $css = ":root {\n";
        foreach ($themeData as $property => $value) {
            $cssProperty = '--' . str_replace('_', '-', $property);
            $css .= "    {$cssProperty}: {$value};\n";
        }
        $css .= "}\n";

        return response()->json([
            'success' => true,
            'css' => $css,
            'preview_url' => route('theme.preview', ['page' => $request->preview_page])
        ]);
    }

    public function livePreview(Request $request)
    {
        $page = $request->get('page', 'homepage');
        
        // This would render the requested page with theme preview
        switch ($page) {
            case 'homepage':
                // Homepage is now the booking page
                return app(\App\Http\Controllers\AppointmentController::class)->create();
            case 'services':
                return view('services');
            case 'contact':
                return view('contact');
            case 'appointment':
                return app(\App\Http\Controllers\AppointmentController::class)->create();
            default:
                // Homepage is now the booking page
                return app(\App\Http\Controllers\AppointmentController::class)->create();
        }
    }

    public function exportTheme(ThemeSetting $theme)
    {
        $themeData = $theme->toArray();
        unset($themeData['id'], $themeData['created_at'], $themeData['updated_at']);

        $filename = 'theme-' . $theme->slug . '-' . date('Y-m-d') . '.json';
        
        return response()->json($themeData)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function importTheme(Request $request)
    {
        $request->validate([
            'theme_file' => 'required|file|mimes:json'
        ]);

        $file = $request->file('theme_file');
        $content = file_get_contents($file->getRealPath());
        $themeData = json_decode($content, true);

        if (!$themeData) {
            return redirect()
                ->back()
                ->with('error', 'Invalid theme file format!');
        }

        // Create new theme from imported data
        $themeData['name'] = $themeData['name'] . ' (Imported)';
        $themeData['slug'] = Str::slug($themeData['name']);
        $themeData['is_default'] = false;

        $theme = ThemeSetting::create($themeData);

        // Generate CSS file for the imported theme
        $this->generateThemeCss($theme);

        return redirect()
            ->route('admin.theme.themes')
            ->with('success', 'Theme imported successfully!');
    }

    public function resetTheme(ThemeSetting $theme)
    {
        // Reset to default values (you could have a default theme configuration)
        $defaultValues = [
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'success_color' => '#198754',
            'danger_color' => '#dc3545',
            'warning_color' => '#ffc107',
            'info_color' => '#0dcaf0',
            'light_color' => '#f8f9fa',
            'dark_color' => '#212529',
            'accent_color' => '#667eea',
            'text_color' => '#212529',
            'background_color' => '#ffffff',
            'card_background' => '#ffffff',
            'sidebar_color' => '#ffffff',
            'header_color' => '#ffffff',
            'footer_color' => '#212529',
            'primary_font' => 'Poppins',
            'secondary_font' => 'Inter',
            'font_size_base' => '16px',
            'border_radius' => '0.5rem',
            'box_shadow' => '0 0.125rem 0.25rem rgba(0, 0, 0, 0.075)',
            'container_width' => '1200px'
        ];

        $theme->update($defaultValues);

        // Regenerate CSS file
        $this->generateThemeCss($theme);

        return response()->json([
            'success' => true,
            'message' => 'Theme reset to default values successfully!'
        ]);
    }
}
