<?php

namespace App\Providers;

use App\Routing\StaleRouteCacheFallbacks;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use App\Models\Payment;
use App\Models\User;
use App\Observers\PaymentObserver;
use App\Observers\UserObserver;
use App\View\Composers\PendingAppointmentsComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // After RouteServiceProvider loads cached routes (AppServiceProvider boots first).
        $this->app->booted(function () {
            StaleRouteCacheFallbacks::register();
        });

        // Force HTTPS URL generation only when request is truly HTTPS.
        // This avoids redirect loops in proxy/CDN setups where scheme detection can differ.
        if ($this->app->environment('production') && ! $this->app->runningInConsole()) {
            $request = request();
            $forwardedProto = strtolower((string) $request->header('X-Forwarded-Proto', ''));
            $isHttps = $request->isSecure() || str_contains($forwardedProto, 'https');

            if ($isHttps) {
                URL::forceScheme('https');
            }
        }
        
        // Register model observers
        User::observe(UserObserver::class);
        Payment::observe(PaymentObserver::class);
        
        // Share site settings with all views
        view()->composer('*', function ($view) {
            // Check if we're in installation mode or if database is not ready
            if (request()->is('install') || request()->is('install/*')) {
                return; // Skip loading settings during installation
            }
            
            // Check if database and tables exist before loading settings
            try {
                // Load site settings for all routes (frontend and admin)
                // Check if site_settings table exists
                if (\Schema::hasTable('site_settings')) {
                    $view->with('site_settings', \App\Models\SiteSetting::getSettings());
                } else {
                    $view->with('site_settings', []);
                }
                
                // Check if theme_settings table exists
                if (\Schema::hasTable('theme_settings')) {
                    $view->with('theme_settings', \App\Models\ThemeSetting::getActive() ?? []);
                } else {
                    $view->with('theme_settings', []);
                }
                // SEO/tracking settings (GTM, GA, etc.)
                if (\Schema::hasTable('seo_settings')) {
                    $seo = \App\Models\SeoSettings::getInstance();
                    $view->with('seo_settings', $seo);
                } else {
                    $view->with('seo_settings', null);
                }
            } catch (\Exception $e) {
                // If database connection fails or tables don't exist, provide empty arrays
                $view->with('site_settings', []);
                $view->with('theme_settings', []);
                $view->with('seo_settings', null);
            }
        });

        // Share embed flag for public booking (iframe / WordPress embed)
        view()->composer(['layouts.public-booking', 'public-booking.*'], function ($view) {
            if (request()->is('book') || request()->is('book/*')) {
                $view->with('embed', request()->boolean('embed'));
            }
        });

        // Pending appointments for staff/doctor layouts (fixed bar)
        View::composer(['layouts.doctor', 'layouts.staff'], PendingAppointmentsComposer::class);
    }
}
