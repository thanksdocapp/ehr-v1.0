<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\ClinicBookingService;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->tryFinalizeClinicBooking($payment);
    }

    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        $this->tryFinalizeClinicBooking($payment);
    }

    private function tryFinalizeClinicBooking(Payment $payment): void
    {
        if ($payment->status !== 'completed') {
            return;
        }

        $invoice = $payment->invoice ?? $payment->invoice()->first();
        if (! $invoice) {
            return;
        }

        app(ClinicBookingService::class)->finalizeClinicBookingForPaidInvoice($invoice);
    }
}
