<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Observers\UserObserver;

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
        // Force HTTPS in production for security and compliance
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        
        // Register model observers
        User::observe(UserObserver::class);
        
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
            } catch (\Exception $e) {
                // If database connection fails or tables don't exist, provide empty arrays
                $view->with('site_settings', []);
                $view->with('theme_settings', []);
            }
        });

        // Share embed flag for public booking (iframe / WordPress embed)
        view()->composer(['layouts.public-booking', 'public-booking.*'], function ($view) {
            if (request()->is('book') || request()->is('book/*')) {
                $view->with('embed', request()->boolean('embed'));
            }
        });
    }
}
