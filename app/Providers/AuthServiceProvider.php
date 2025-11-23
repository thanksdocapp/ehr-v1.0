<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Patient::class => \App\Policies\PatientPolicy::class,
        \App\Models\PatientAlert::class => \App\Policies\PatientAlertPolicy::class,
        \App\Models\DocumentTemplate::class => \App\Policies\DocumentTemplatePolicy::class,
        \App\Models\PatientDocument::class => \App\Policies\PatientDocumentPolicy::class,
        \App\Models\DocumentDelivery::class => \App\Policies\DocumentDeliveryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
