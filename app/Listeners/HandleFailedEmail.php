<?php

namespace App\Listeners;

use App\Events\EmailFailed;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EmailFailureNotification;
use App\Models\User;

class HandleFailedEmail
{
    /**
     * Handle the event.
     *
     * @param \App\Events\EmailFailed $event
     * @return void
     */
    public function handle(EmailFailed $event)
    {
        // Log the failure
        Log::error('Email failed to send', [
            'log_id' => $event->emailLog->id,
            'template' => $event->emailLog->emailTemplate?->name,
            'recipient' => $event->emailLog->recipient_email,
            'error' => $event->exception->getMessage(),
            'trace' => $event->exception->getTraceAsString()
        ]);

        // Notify system administrators
        $admins = User::where('is_admin', true)->get();
        
        Notification::send($admins, new EmailFailureNotification(
            $event->emailLog,
            $event->exception
        ));
    }
}
