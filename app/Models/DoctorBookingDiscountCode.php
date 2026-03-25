<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorBookingDiscountCode extends Model
{
    protected $fillable = [
        'doctor_id',
        'code',
        'discount_type',
        'discount_value',
        'booking_service_id',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'doctor_id' => 'integer',
        'booking_service_id' => 'integer',
        'discount_value' => 'decimal:2',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function bookingService(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'booking_service_id');
    }

    public static function normalizeCode(string $raw): string
    {
        return strtoupper(trim($raw));
    }

    /**
     * @param CarbonInterface|string|null $on
     */
    public function isCurrentlyValid($on = null): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $moment = $on ? \Illuminate\Support\Carbon::parse($on) : now();
        $momentDate = $moment->toDateString();
        if ($this->valid_from && $this->valid_from->toDateString() > $momentDate) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->toDateString() < $momentDate) {
            return false;
        }

        return true;
    }

    public function appliesToService(?int $serviceId): bool
    {
        if ($this->booking_service_id === null) {
            return true;
        }

        return $serviceId !== null && (int) $this->booking_service_id === (int) $serviceId;
    }

    public function isUsableForBooking(?int $serviceId, $on = null): bool
    {
        return $this->isCurrentlyValid($on)
            && $this->appliesToService($serviceId)
            && ($this->max_uses === null || $this->uses_count < $this->max_uses);
    }

    public function computeDiscountAmount(float $subtotal): float
    {
        $subtotal = max(0, $subtotal);
        if ($this->discount_type === 'percent') {
            $pct = min(100, max(0, (float) $this->discount_value));

            return round($subtotal * ($pct / 100), 2);
        }

        return round(min($subtotal, max(0, (float) $this->discount_value)), 2);
    }
}
