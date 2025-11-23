<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HomepageSection;
use App\Models\HomepageFeature;
use App\Models\AboutStat;
use App\Models\Service;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Testimonial;
use App\Models\SiteSetting;
use App\Models\ThemeSetting;
use App\Models\FrontendTemplate;
use App\Models\BannerSlide;
use App\Models\FAQ;

class HomepageController extends Controller
{
    /**
     * Homepage now redirects to booking page
     * This method is kept for backward compatibility but is no longer used
     */
    public function index()
    {
        // Homepage removed - redirect to booking page (which is now the homepage)
        return redirect()->route('homepage');
    }

    public function about()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'about_stats' => AboutStat::active()->ordered()->get(),
            'departments' => Department::active()->ordered()->get(),
            'doctors' => Doctor::active()->ordered()->take(10)->get(),
        ];
        
        return view('about', $data);
    }

    public function contact()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'departments' => Department::active()->ordered()->get(),
        ];
        
        return view('contact', $data);
    }

    public function departments()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'departments' => Department::active()->ordered()->get(),
        ];
        
        return view('departments', $data);
    }

    public function departmentDetail($id)
    {
        $department = Department::active()->where('id', $id)->firstOrFail();
        $related_doctors = Doctor::active()->where('department_id', $id)->ordered()->get();
        $related_services = Service::active()->where('department_id', $id)->ordered()->get();
        
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'department' => $department,
            'related_doctors' => $related_doctors,
            'related_services' => $related_services,
        ];
        
        return view('department-detail', $data);
    }

    public function services()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'services' => Service::active()->ordered()->get(),
            'departments' => Department::active()->ordered()->get(),
        ];
        
        return view('services', $data);
    }

    public function serviceDetail($id)
    {
        $service = Service::active()->where('id', $id)->firstOrFail();
        $related_doctors = Doctor::active()->where('department_id', $service->department_id)->ordered()->get();
        
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'service' => $service,
            'related_doctors' => $related_doctors,
        ];
        
        return view('service-detail', $data);
    }

    public function doctors()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'doctors' => Doctor::active()->ordered()->get(),
            'departments' => Department::active()->ordered()->get(),
        ];
        
        return view('doctors', $data);
    }

    public function doctorDetail($id)
    {
        $doctor = Doctor::active()->where('id', $id)->firstOrFail();
        $related_services = Service::active()->where('department_id', $doctor->department_id)->ordered()->get();
        
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'doctor' => $doctor,
            'related_services' => $related_services,
        ];
        
        return view('doctor-detail', $data);
    }

    public function faq()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'faqs' => FAQ::active()->ordered()->get(),
        ];
        
        return view('faq', $data);
    }
}
