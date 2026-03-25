<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    /**
     * When set, the code applies only to these booking services (plus legacy
     * {@see $booking_service_id} until migrated). Empty pivot and null
     * {@see $booking_service_id} means all services the doctor offers.
     */
    public function bookingServices(): BelongsToMany
    {
        return $this->belongsToMany(
            BookingService::class,
            'doctor_booking_discount_code_services',
            'doctor_booking_discount_code_id',
            'booking_service_id'
        )->withTimestamps();
    }

    /**
     * @param list<int|string>|null $raw
     * @return list<int>
     */
    public static function normalizeServiceIdList(?array $raw): array
    {
        if ($raw === null || $raw === []) {
            return [];
        }

        return collect($raw)
            ->filter(fn ($id) => $id !== '' && $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Replace restricted services (empty = valid on all doctor services).
     *
     * @param list<int> $normalizedIds
     */
    public function replaceRestrictedBookingServices(array $normalizedIds): void
    {
        if ($normalizedIds === []) {
            $this->bookingServices()->detach();
        } else {
            $this->bookingServices()->sync($normalizedIds);
        }

        if ($this->booking_service_id !== null) {
            $this->forceFill(['booking_service_id' => null])->saveQuietly();
        }
    }

    /**
     * IDs to pre-select in forms (pivot first, then legacy column).
     *
     * @return list<int>
     */
    public function selectedBookingServiceIdsForForm(): array
    {
        $this->loadMissing('bookingServices');
        $fromPivot = $this->bookingServices->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        if ($fromPivot !== []) {
            return array_values($fromPivot);
        }
        if ($this->booking_service_id !== null) {
            return [(int) $this->booking_service_id];
        }

        return [];
    }

    /** @return Collection<int, string> */
    public function restrictedServiceNamesForDisplay(): Collection
    {
        $this->loadMissing(['bookingServices', 'bookingService']);
        $names = $this->bookingServices->pluck('name')->filter()->map(fn ($n) => trim((string) $n))->unique()->values();
        if ($names->isNotEmpty()) {
            return $names;
        }
        if ($this->booking_service_id) {
            $n = $this->bookingService?->name;

            return $n ? collect([trim((string) $n)]) : collect();
        }

        return collect();
    }

    /**
     * Canonical booking_service rows that restrict this code, or null if unrestricted.
     *
     * @return list<int>|null
     */
    public function restrictedCanonicalServiceIds(): ?array
    {
        if ($this->relationLoaded('bookingServices')) {
            $pivotIds = $this->bookingServices->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();
        } else {
            $pivotIds = $this->bookingServices()->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        if ($pivotIds !== []) {
            return array_values($pivotIds);
        }

        if ($this->booking_service_id !== null) {
            return [(int) $this->booking_service_id];
        }

        return null;
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
        $restricted = $this->restrictedCanonicalServiceIds();
        if ($restricted === null) {
            return true;
        }

        if ($serviceId === null) {
            return false;
        }

        foreach ($restricted as $canonicalId) {
            if ($this->bookedServiceMatchesCanonical((int) $canonicalId, (int) $serviceId)) {
                return true;
            }
        }

        return false;
    }

    private function bookedServiceMatchesCanonical(int $canonicalServiceId, int $bookedServiceId): bool
    {
        if ($canonicalServiceId === $bookedServiceId) {
            return true;
        }

        $canonical = BookingService::find($canonicalServiceId);
        $booked = BookingService::find($bookedServiceId);
        if (!$canonical || !$booked) {
            return false;
        }

        if (strcasecmp(trim((string) $canonical->name), trim((string) $booked->name)) !== 0) {
            return false;
        }

        return $booked->isAvailableForDoctor((int) $this->doctor_id);
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
