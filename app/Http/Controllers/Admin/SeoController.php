<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoPage;
use App\Models\SeoSettings;
use App\Models\SeoAnalytics;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SeoController extends Controller
{
    public function index(Request $request)
    {
        // Load SEO configuration data
        $seoConfig = SeoSettings::getInstance();
        
        $query = SeoPage::query();

        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('url', 'like', '%' . $request->search . '%')
                  ->orWhere('meta_title', 'like', '%' . $request->search . '%')
                  ->orWhere('meta_description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->score_range) {
            $range = explode('-', $request->score_range);
            if (count($range) == 2) {
                $query->whereBetween('seo_score', [$range[0], $range[1]]);
            }
        }

        if ($request->date_from) {
            $query->whereDate('updated_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('updated_at', '<=', $request->date_to);
        }

        // Get real SEO statistics from database
        $totalPages = SeoPage::count();
        $optimizedPages = SeoPage::where('seo_score', '>=', 80)->count();
        $needsWorkPages = SeoPage::where('seo_score', '<', 80)->count();
        $avgSeoScore = SeoPage::avg('seo_score') ?? 0;

        $stats = [
            'total_pages' => $totalPages,
            'optimized_pages' => $optimizedPages,
            'pending_pages' => $needsWorkPages,
            'avg_seo_score' => round($avgSeoScore)
        ];

        // Get paginated SEO pages with real data
        $pages = $query->orderBy('updated_at', 'desc')->paginate(20);

        return view('admin.seo.index', compact('stats', 'pages', 'seoConfig'));
    }

    public function createPage()
    {
        return view('admin.seo.create');
    }

    public function storePage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'url' => 'required|string|unique:seo_pages,url',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Calculate SEO score
        $score = $this->calculateSeoScore($request->all());
        $status = $this->getStatusFromScore($score);

        SeoPage::create([
            'title' => $request->title,
            'url' => $request->url,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'canonical_url' => $request->canonical_url,
            'seo_score' => $score,
            'status' => $status,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.seo.index')->with('success', 'SEO page created successfully!');
    }

    public function editPage($id)
    {
        $page = SeoPage::findOrFail($id);
        return view('admin.seo.edit', compact('page'));
    }

    public function updatePage(Request $request, $id)
    {
        $page = SeoPage::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'url' => 'required|string|unique:seo_pages,url,' . $id,
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Calculate SEO score
        $score = $this->calculateSeoScore($request->all());
        $status = $this->getStatusFromScore($score);

        $page->update([
            'title' => $request->title,
            'url' => $request->url,
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords,
            'canonical_url' => $request->canonical_url,
            'seo_score' => $score,
            'status' => $status,
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', 'Page SEO updated successfully!');
    }

    public function deletePage($id)
    {
        $page = SeoPage::findOrFail($id);
        $page->delete();
        
        return back()->with('success', 'SEO page deleted successfully!');
    }

    public function sitemap()
    {
        $sitemaps = $this->getSitemapInfo();
        $pages = SeoPage::active()->orderBy('updated_at', 'desc')->get();
        
        return view('admin.seo.sitemap', compact('sitemaps', 'pages'));
    }

    public function generateSitemap(Request $request)
    {
        try {
            $pages = SeoPage::active()->get();
            $xml = $this->buildSitemapXml($pages);
            
            $path = public_path('sitemap.xml');
            File::put($path, $xml);
            
            // Update cache
            Cache::forget('sitemap_info');
            
            return back()->with('success', 'Sitemap generated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate sitemap: ' . $e->getMessage());
        }
    }

    public function downloadSitemap()
    {
        $path = public_path('sitemap.xml');
        
        if (!File::exists($path)) {
            return back()->with('error', 'Sitemap file not found. Please generate it first.');
        }
        
        return Response::download($path);
    }

    public function robots()
    {
        $robotsContent = $this->getRobotsContent();
        $settings = SeoSettings::first() ?? new SeoSettings();
        
        return view('admin.seo.robots', compact('robotsContent', 'settings'));
    }

    public function updateRobots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'robots_content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $path = public_path('robots.txt');
            File::put($path, $request->robots_content);
            
            return back()->with('success', 'Robots.txt updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update robots.txt: ' . $e->getMessage());
        }
    }

    public function metaTags()
    {
        $settings = SeoSettings::first() ?? new SeoSettings();
        return view('admin.seo.meta-tags', compact('settings'));
    }

    public function updateMetaTags(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'default_title' => 'nullable|string|max:60',
            'default_description' => 'nullable|string|max:255',
            'default_keywords' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'meta_description' => 'nullable|string|max:255',
            'social_title' => 'nullable|string|max:255',
            'social_description' => 'nullable|string',
            'og_title' => 'nullable|string',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|url',
            'twitter_card' => 'nullable|string',
            'twitter_site' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = SeoSettings::first() ?? new SeoSettings();
        $settings->fill($request->all());
        $settings->save();

        return back()->with('success', 'Meta tags settings updated successfully!');
    }

    public function analytics()
    {
        $analytics = $this->getSeoAnalytics();
        return view('admin.seo.analytics', compact('analytics'));
    }
    
    public function updateConfig(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'meta_keywords' => 'required|string',
            'meta_description' => 'required|string|max:255',
            'social_title' => 'required|string|max:255',
            'social_description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $seoConfig = SeoSettings::firstOrCreate([]);
            $seoConfig->update([
                'meta_keywords' => $request->meta_keywords,
                'meta_description' => $request->meta_description,
                'social_title' => $request->social_title,
                'social_description' => $request->social_description,
            ]);

            return back()->with('success', 'SEO configuration updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update SEO configuration: ' . $e->getMessage());
        }
    }
    
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_image' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $seoConfig = SeoSettings::firstOrCreate([]);
            
            // Delete old image if exists
            if ($seoConfig->social_image && \Storage::disk('public')->exists($seoConfig->social_image)) {
                \Storage::disk('public')->delete($seoConfig->social_image);
            }
            
            // Store new image
            $imagePath = $request->file('social_image')->store('seo/social-images', 'public');
            
            // Update database
            $seoConfig->update([
                'social_image' => $imagePath
            ]);

            return back()->with('success', 'Social image uploaded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to upload image: ' . $e->getMessage());
        }
    }

    private function calculateSeoScore($data)
    {
        $score = 0;
        
        // Title (20 points)
        if (!empty($data['title'])) {
            $score += 10;
            if (strlen($data['title']) >= 10 && strlen($data['title']) <= 60) {
                $score += 10;
            }
        }
        
        // Meta title (20 points)
        if (!empty($data['meta_title'])) {
            $score += 10;
            if (strlen($data['meta_title']) >= 10 && strlen($data['meta_title']) <= 60) {
                $score += 10;
            }
        }
        
        // Meta description (25 points)
        if (!empty($data['meta_description'])) {
            $score += 15;
            if (strlen($data['meta_description']) >= 120 && strlen($data['meta_description']) <= 160) {
                $score += 10;
            }
        }
        
        // Meta keywords (15 points)
        if (!empty($data['meta_keywords'])) {
            $score += 15;
        }
        
        // Canonical URL (10 points)
        if (!empty($data['canonical_url'])) {
            $score += 10;
        }
        
        // URL structure (10 points)
        if (!empty($data['url'])) {
            if (strlen($data['url']) < 100 && !str_contains($data['url'], '?')) {
                $score += 10;
            }
        }
        
        return min($score, 100);
    }
    
    private function getStatusFromScore($score)
    {
        if ($score >= 80) return 'optimized';
        if ($score >= 60) return 'needs_work';
        return 'poor';
    }
    
    private function getSitemapInfo()
    {
        return Cache::remember('sitemap_info', 3600, function() {
            $path = public_path('sitemap.xml');
            $info = [
                'exists' => File::exists($path),
                'size' => File::exists($path) ? File::size($path) : 0,
                'last_modified' => File::exists($path) ? File::lastModified($path) : null,
                'url_count' => 0
            ];
            
            if ($info['exists']) {
                $content = File::get($path);
                $info['url_count'] = substr_count($content, '<url>');
            }
            
            return $info;
        });
    }
    
    private function buildSitemapXml($pages)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($pages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . url($page->url) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $page->updated_at->format('Y-m-d\TH:i:s\Z') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>' . ($page->seo_score >= 80 ? '1.0' : '0.8') . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        return $xml;
    }
    
    private function getRobotsContent()
    {
        $path = public_path('robots.txt');
        
        if (File::exists($path)) {
            return File::get($path);
        }
        
        // Default robots.txt content
        return "User-agent: *\nAllow: /\n\nSitemap: " . url('sitemap.xml');
    }
    
    private function getSeoAnalytics()
    {
        $days = 30;
        $analytics = [];
        
        // SEO performance over time
        $analytics['daily_scores'] = SeoPage::selectRaw('DATE(updated_at) as date, AVG(seo_score) as avg_score')
            ->where('updated_at', '>=', Carbon::now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Score distribution
        $analytics['score_distribution'] = [
            'excellent' => SeoPage::where('seo_score', '>=', 90)->count(),
            'good' => SeoPage::whereBetween('seo_score', [80, 89])->count(),
            'fair' => SeoPage::whereBetween('seo_score', [60, 79])->count(),
            'poor' => SeoPage::where('seo_score', '<', 60)->count(),
        ];
        
        // Top performing pages
        $analytics['top_pages'] = SeoPage::orderBy('seo_score', 'desc')->limit(10)->get();
        
        // Pages needing attention
        $analytics['attention_pages'] = SeoPage::where('seo_score', '<', 60)->orderBy('seo_score')->limit(10)->get();
        
        return $analytics;
    }
}

