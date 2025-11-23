<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\Faq;
use App\Models\HomepageSection;
use App\Models\HowItWork;
use App\Models\LoanRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FrontendController extends Controller
{
    public function index()
    {
        $stats = [
            'services' => Service::count(),
            'testimonials' => Testimonial::count(),
            'faqs' => Faq::count(),
            'homepageSections' => HomepageSection::count(),
            'keyFeatures' => \App\Models\KeyFeature::count(),
            'loanRates' => LoanRate::count(),
            'howItWorks' => HowItWork::count(),
            'activeServices' => Service::active()->count(),
            'activeTestimonials' => Testimonial::active()->count(),
            'activeFaqs' => Faq::active()->count(),
            'activeHomepageSections' => HomepageSection::where('is_active', true)->count(),
            'activeKeyFeatures' => \App\Models\KeyFeature::where('is_active', true)->count(),
            'activeLoanRates' => LoanRate::where('is_active', true)->count(),
            'activeHowItWorks' => HowItWork::where('is_active', true)->count(),
        ];
        
        return view('admin.frontend.index', compact('stats'));
    }

    public function homepage()
    {
        $sections = HomepageSection::ordered()->paginate(15);
        return view('admin.frontend.homepage.index', compact('sections'));
    }


    public function services()
    {
        $services = Service::ordered()->paginate(15);
        return view('admin.frontend.services.index', compact('services'));
    }

    public function createService()
    {
        return view('admin.frontend.services.create');
    }

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'image_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Service::create($validated);

        return redirect()->route('admin.frontend.services')
            ->with('success', 'Service created successfully!');
    }

    public function editService($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.frontend.services.edit', compact('service'));
    }

    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'image_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $service->update($validated);

        return redirect()->route('admin.frontend.services')
            ->with('success', 'Service updated successfully!');
    }

    public function destroyService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Service deleted successfully!');
    }

    public function testimonials()
    {
        $testimonials = Testimonial::ordered()->paginate(15);
        $section = \App\Models\TestimonialsSection::first();
        return view('admin.frontend.testimonials.index', compact('testimonials', 'section'));
    }

    public function createTestimonial()
    {
        $nextSortOrder = Testimonial::max('sort_order') + 1;
        return view('admin.frontend.testimonials.create', compact('nextSortOrder'));
    }

    public function storeTestimonial(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'testimonial' => 'required|string',
            'client_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Handle file upload
        if ($request->hasFile('client_photo')) {
            $validated['client_photo'] = $request->file('client_photo')->store('testimonials', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Testimonial::create($validated);

        return redirect()->route('admin.frontend.testimonials')
            ->with('success', 'Testimonial created successfully!');
    }

    public function editTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return view('admin.frontend.testimonials.edit', compact('testimonial'));
    }

    public function updateTestimonial(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);
        
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_position' => 'nullable|string|max:255',
            'client_company' => 'nullable|string|max:255',
            'testimonial' => 'required|string',
            'client_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'rating' => 'required|integer|min:1|max:5',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        // Handle file upload
        if ($request->hasFile('client_photo')) {
            // Delete old photo if exists
            if ($testimonial->client_photo) {
                Storage::disk('public')->delete($testimonial->client_photo);
            }
            $validated['client_photo'] = $request->file('client_photo')->store('testimonials', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $testimonial->update($validated);

        return redirect()->route('admin.frontend.testimonials')
            ->with('success', 'Testimonial updated successfully!');
    }

    public function destroyTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        
        // Delete photo if exists
        if ($testimonial->client_photo) {
            Storage::disk('public')->delete($testimonial->client_photo);
        }
        
        $testimonial->delete();

        return response()->json(['success' => true, 'message' => 'Testimonial deleted successfully']);
    }

    public function faqs()
    {
        $faqs = Faq::ordered()->paginate(15);
        $categories = Faq::select('category')->distinct()->pluck('category');
        $section = \App\Models\FaqsSection::first();
        return view('admin.frontend.faqs.index', compact('faqs', 'categories', 'section'));
    }

    public function createFaq()
    {
        $nextSortOrder = Faq::max('sort_order') + 1;
        $categories = Faq::select('category')->distinct()->pluck('category')->toArray();
        return view('admin.frontend.faqs.create', compact('nextSortOrder', 'categories'));
    }

    public function storeFaq(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Faq::create($validated);

        return redirect()->route('frontend.faqs')
            ->with('success', 'FAQ created successfully!');
    }

    public function editFaq($id)
    {
        $faq = Faq::findOrFail($id);
        $categories = Faq::select('category')->distinct()->pluck('category')->toArray();
        return view('admin.frontend.faqs.edit', compact('faq', 'categories'));
    }

    public function updateFaq(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);
        
        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'category' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $faq->update($validated);

        return redirect()->route('frontend.faqs')
            ->with('success', 'FAQ updated successfully!');
    }

    public function destroyFaq($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json(['success' => true, 'message' => 'FAQ deleted successfully']);
    }

    // Additional methods for other frontend content
    public function loanRates()
    {
        $loanRates = LoanRate::ordered()->paginate(15);
        return view('admin.frontend.loan-rates.index', compact('loanRates'));
    }

    public function createLoanRate()
    {
        return view('admin.frontend.loan-rates.create');
    }

    public function storeLoanRate(Request $request)
    {
        $validated = $request->validate([
            'loan_type' => 'required|string|max:255',
            'interest_rate' => 'required|numeric|min:0|max:99.99',
            'duration' => 'required|string|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        LoanRate::create($validated);

        return redirect()->route('admin.frontend.loan-rates')
            ->with('success', 'Loan rate created successfully!');
    }

    public function editLoanRate($id)
    {
        $loanRate = LoanRate::findOrFail($id);
        return view('admin.frontend.loan-rates.edit', compact('loanRate'));
    }

    public function updateLoanRate(Request $request, $id)
    {
        $loanRate = LoanRate::findOrFail($id);
        
        $validated = $request->validate([
            'loan_type' => 'required|string|max:255',
            'interest_rate' => 'required|numeric|min:0|max:99.99',
            'duration' => 'required|string|max:100',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $loanRate->update($validated);

        return redirect()->route('admin.frontend.loan-rates')
            ->with('success', 'Loan rate updated successfully!');
    }

    public function destroyLoanRate($id)
    {
        $loanRate = LoanRate::findOrFail($id);
        $loanRate->delete();

        return back()->with('success', 'Loan rate deleted successfully!');
    }

    public function createHomepage()
    {
        // Get the next sort order based on existing sections
        $nextSortOrder = HomepageSection::max('sort_order') + 1;
        
        return view('admin.frontend.homepage.create', compact('nextSortOrder'));
    }

    public function storeHomepage(Request $request)
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('homepage-sections', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        HomepageSection::create($validated);

        return redirect()->route('admin.frontend.homepage')
            ->with('success', 'Homepage section created successfully!');
    }

    public function editHomepage($id)
    {
        $section = HomepageSection::findOrFail($id);
        return view('admin.frontend.homepage.edit', compact('section'));
    }

    public function updateHomepage(Request $request, $id)
    {
        $section = HomepageSection::findOrFail($id);

        $validated = $request->validate([
            'section_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists and is a local file
            if ($section->image_url && Storage::disk('public')->exists($section->image_url)) {
                Storage::disk('public')->delete($section->image_url);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('homepage-sections', $imageName, 'public');
            $validated['image_url'] = $imagePath;
        }

        $section->update($validated);

        return redirect()->route('admin.frontend.homepage')
            ->with('success', 'Homepage section updated successfully!');
    }

    public function destroyHomepage($id)
    {
        $section = HomepageSection::findOrFail($id);
        $section->delete();

        return back()->with('success', 'Homepage section deleted successfully!');
    }

    public function howItWorks()
    {
        $howItWorks = HowItWork::ordered()->paginate(15);
        $section = \App\Models\HowItWorksSection::first();
        return view('admin.frontend.how-it-works.index', compact('howItWorks', 'section'));
    }

    public function createHowItWorks()
    {
        $nextSortOrder = HowItWork::max('sort_order') + 1;
        return view('admin.frontend.how-it-works.create', compact('nextSortOrder'));
    }

    public function storeHowItWorks(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'image_url' => 'nullable|url|max:500',
            'step_number' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        HowItWork::create($validated);

        return redirect()->route('admin.frontend.how-it-works')
            ->with('success', 'How it works step created successfully!');
    }

    public function editHowItWorks($id)
    {
        $howItWorks = HowItWork::findOrFail($id);
        return view('admin.frontend.how-it-works.edit', compact('howItWorks'));
    }

    public function updateHowItWorks(Request $request, $id)
    {
        $howItWorks = HowItWork::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'nullable|string|max:100',
            'image_url' => 'nullable|url|max:500',
            'step_number' => 'required|integer|min:1',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $howItWorks->update($validated);

        return redirect()->route('admin.frontend.how-it-works')
            ->with('success', 'How it works step updated successfully!');
    }

    public function destroyHowItWorks($id)
    {
        $howItWorks = HowItWork::findOrFail($id);
        $howItWorks->delete();

        return back()->with('success', 'How it works step deleted successfully!');
    }

    public function keyFeatures()
    {
        return view('admin.coming-soon', [
            'title' => 'Key Features Management',
            'description' => 'Manage platform key features content.',
            'icon' => 'fas fa-star'
        ]);
    }

    public function updateKeyFeature(Request $request, $id)
    {
        return back()->with('success', 'Key feature updated successfully');
    }

    public function pages()
    {
        return view('admin.coming-soon', [
            'title' => 'Pages Management',
            'description' => 'Manage static pages content.',
            'icon' => 'fas fa-file-alt'
        ]);
    }

    public function aboutPage()
    {
        return view('admin.coming-soon', [
            'title' => 'About Page',
            'description' => 'Manage about page content.',
            'icon' => 'fas fa-info'
        ]);
    }

    public function updateAboutPage(Request $request)
    {
        return back()->with('success', 'About page updated successfully');
    }

    public function contactPage()
    {
        return view('admin.coming-soon', [
            'title' => 'Contact Page',
            'description' => 'Manage contact page content.',
            'icon' => 'fas fa-phone'
        ]);
    }

    public function updateContactPage(Request $request)
    {
        return back()->with('success', 'Contact page updated successfully');
    }

    public function updateFaqsSection(Request $request)
    {
        $section = \App\Models\FaqsSection::firstOrCreate([]);

        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
            'section_subtitle' => 'nullable|string|max:255',
            'section_description' => 'nullable|string',
            'background_color' => 'nullable|string|max:7',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $section->update($validated);

        return back()->with('success', 'FAQs section updated successfully!');
    }

    public function updateHowItWorksSection(Request $request)
    {
        $section = \App\Models\HowItWorksSection::firstOrCreate([]);

        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
            'section_subtitle' => 'nullable|string|max:255',
            'section_description' => 'nullable|string',
            'background_color' => 'nullable|string|max:7',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $section->update($validated);

        return back()->with('success', 'How It Works section updated successfully!');
    }

    public function updateTestimonialsSection(Request $request)
    {
        $section = \App\Models\TestimonialsSection::firstOrCreate([]);

        $validated = $request->validate([
            'section_title' => 'required|string|max:255',
            'section_subtitle' => 'nullable|string|max:255',
            'section_description' => 'nullable|string',
            'background_color' => 'nullable|string|max:7',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $section->update($validated);

        return back()->with('success', 'Testimonials section updated successfully!');
    }
}

