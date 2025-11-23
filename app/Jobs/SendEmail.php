<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\EmailNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = [10, 60, 180];

    /**
     * The email log instance.
     *
     * @var \App\Models\EmailLog
     */
    protected $emailLog;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\EmailLog $emailLog
     * @return void
     */
    public function __construct(EmailLog $emailLog)
    {
        $this->emailLog = $emailLog;
        $this->onQueue('emails');
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\EmailNotificationService $emailService
     * @return void
     */
    public function handle(EmailNotificationService $emailService)
    {
        $emailService->sendImmediateEmail($this->emailLog);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        $this->emailLog->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage()
        ]);

        // Dispatch failure event
        event(new \App\Events\EmailFailed($this->emailLog, $exception));
    }
}
