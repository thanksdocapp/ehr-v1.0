<?php

namespace App\Events;

use App\Models\EmailLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The email log instance.
     *
     * @var \App\Models\EmailLog
     */
    public $emailLog;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\EmailLog $emailLog
     * @return void
     */
    public function __construct(EmailLog $emailLog)
    {
        $this->emailLog = $emailLog;
    }
}
