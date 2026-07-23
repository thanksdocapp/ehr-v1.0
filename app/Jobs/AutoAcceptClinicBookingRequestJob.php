<?php

namespace App\Jobs;

use App\Services\ClinicBookingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Auto-accept a public clinic booking after the patient has been redirected away from checkout.
 */
class AutoAcceptClinicBookingRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $clinicBookingRequestId,
        public int $doctorId,
        public ?int $acceptedByUserId = null,
    ) {
    }

    public function handle(ClinicBookingService $clinicBookingService): void
    {
        $clinicBookingService->runDeferredAutoAccept(
            $this->clinicBookingRequestId,
            $this->doctorId,
            $this->acceptedByUserId
        );
    }
}
