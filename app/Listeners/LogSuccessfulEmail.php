<?php

namespace App\Listeners;

use App\Events\EmailSent;
use Illuminate\Support\Facades\Log;

class LogSuccessfulEmail
{
    /**
     * Handle the event.
     *
     * @param \App\Events\EmailSent $event
     * @return void
     */
    public function handle(EmailSent $event)
    {
        Log::info('Email sent successfully', [
            'log_id' => $event->emailLog->id,
            'template' => $event->emailLog->emailTemplate?->name,
            'recipient' => $event->emailLog->recipient_email,
            'subject' => $event->emailLog->subject
        ]);

        // You can add additional success handling here
        // For example, notify admins, update stats, etc.
    }
}
