<?php

namespace App\Events;

use App\Models\EmailLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class EmailFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The email log instance.
     *
     * @var \App\Models\EmailLog
     */
    public $emailLog;

    /**
     * The exception that caused the failure.
     *
     * @var \Throwable
     */
    public $exception;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\EmailLog $emailLog
     * @param \Throwable $exception
     * @return void
     */
    public function __construct(EmailLog $emailLog, Throwable $exception)
    {
        $this->emailLog = $emailLog;
        $this->exception = $exception;
    }
}
