<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicBookingDiscountCode extends Model
{
    protected $fillable = [
        'department_id',
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
        'department_id' => 'integer',
        'booking_service_id' => 'integer',
        'discount_value' => 'decimal:2',
        'max_uses' => 'integer',
        'uses_count' => 'integer',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function bookingService(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'booking_service_id');
    }

    public static function normalizeCode(string $raw): string
    {
        return strtoupper(trim($raw));
    }

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

        if ($serviceId === null) {
            return false;
        }

        if ((int) $this->booking_service_id === (int) $serviceId) {
            return true;
        }

        // Clinic booking lists one booking_services row per doctor; codes may reference another
        // doctor's row with the same service name. Match by name within this department.
        $canonical = BookingService::find($this->booking_service_id);
        $booked = BookingService::find($serviceId);
        if (!$canonical || !$booked) {
            return false;
        }

        if (strcasecmp(trim((string) $canonical->name), trim((string) $booked->name)) !== 0) {
            return false;
        }

        $departmentId = (int) $this->department_id;
        foreach (Doctor::byDepartment($departmentId)->active()->get() as $doctor) {
            if ($booked->isAvailableForDoctor($doctor->id)) {
                return true;
            }
        }

        return false;
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
