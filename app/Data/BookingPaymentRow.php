<?php

namespace App\Data;

use App\Models\Payment;
use App\Models\ServiceOrder;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Unified row for booking payments report (payment and/or free service order).
 */
class BookingPaymentRow
{
    public function __construct(
        public ?Payment $payment = null,
        public ?ServiceOrder $serviceOrder = null,
    ) {
    }

    public static function fromPayment(Payment $payment): self
    {
        return new self(payment: $payment);
    }

    public static function fromFreeServiceOrder(ServiceOrder $order): self
    {
        return new self(serviceOrder: $order);
    }

    public function isFreeServiceOrder(): bool
    {
        return $this->payment === null && $this->serviceOrder !== null;
    }

    public function sortAt(): ?CarbonInterface
    {
        if ($this->payment?->payment_date) {
            return $this->payment->payment_date;
        }

        return $this->serviceOrder?->paid_at ?? $this->serviceOrder?->created_at;
    }

    public function amount(): float
    {
        if ($this->payment) {
            return (float) $this->payment->amount;
        }

        return (float) ($this->serviceOrder?->fee ?? 0);
    }
}
