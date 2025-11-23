<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Email Events
        \App\Events\EmailSent::class => [
            \App\Listeners\LogSuccessfulEmail::class,
        ],
        \App\Events\EmailFailed::class => [
            \App\Listeners\HandleFailedEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register Model Observers
        \App\Models\Appointment::observe(\App\Observers\AppointmentObserver::class);
        \App\Models\MedicalRecord::observe(\App\Observers\MedicalRecordObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
