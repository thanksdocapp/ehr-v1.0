<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'default_duration_minutes',
        'default_consultation_type',
        'default_price',
        'minimum_age',
        'maximum_age',
        'tags',
        'created_by',
        'is_active',
        'is_non_consultation',
        'sort_order',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'is_non_consultation' => 'boolean',
        'default_price' => 'decimal:2',
        'default_duration_minutes' => 'integer',
        'minimum_age' => 'integer',
        'maximum_age' => 'integer',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function doctorPrices(): HasMany
    {
        return $this->hasMany(DoctorServicePrice::class, 'service_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'service_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    // Helper methods
    public function getPriceForDoctor($doctorId)
    {
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->first();

        // Use default_price when doctor has no override or when custom_price is null
        if (!$doctorPrice) {
            return $this->default_price;
        }
        return $doctorPrice->custom_price ?? $this->default_price;
    }

    public function getDurationForDoctor($doctorId)
    {
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->first();

        // Use default_duration_minutes when doctor has no override or when custom_duration_minutes is null
        if (!$doctorPrice) {
            return $this->default_duration_minutes ?? 30;
        }
        return $doctorPrice->custom_duration_minutes ?? $this->default_duration_minutes ?? 30;
    }

    public function getConsultationTypeForDoctor($doctorId)
    {
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->first();

        $fallback = $this->default_consultation_type ?: 'in_person';

        return $doctorPrice?->consultation_type ?? $fallback;
    }

    public function isNonConsultation(): bool
    {
        return (bool) $this->is_non_consultation;
    }

    public function isUnder18OnlyService(): bool
    {
        return $this->maximum_age === 17 && $this->minimum_age === null;
    }

    public function isAdultsOnlyService(): bool
    {
        return $this->minimum_age === 18 && $this->maximum_age === null;
    }

    /** Human-readable age rule for admin/doctor UI. */
    public function ageRestrictionLabel(): string
    {
        if ($this->isUnder18OnlyService()) {
            return 'Under 18 only';
        }
        if ($this->isAdultsOnlyService()) {
            return 'Adults only (18+)';
        }
        if ($this->minimum_age === null && $this->maximum_age === null) {
            return 'Any age';
        }
        $parts = [];
        if ($this->minimum_age !== null) {
            $parts[] = 'min ' . $this->minimum_age;
        }
        if ($this->maximum_age !== null) {
            $parts[] = 'max ' . $this->maximum_age;
        }

        return 'Custom: ' . implode(', ', $parts);
    }

    /**
     * Map UI checkboxes to stored columns (under-18: max 17; adults: min 18).
     *
     * @return array{0: ?int, 1: ?int} [minimum_age, maximum_age]
     */
    public static function ageBoundsFromRestrictionCheckboxes(bool $under18Only, bool $adultsOnly): array
    {
        if ($under18Only) {
            return [null, 17];
        }
        if ($adultsOnly) {
            return [18, null];
        }

        return [null, null];
    }

    /**
     * Whether a patient age (in full years, as of today) is allowed for this service.
     * Null minimum_age / maximum_age means no bound on that side.
     */
    public function isEligibleForAgeYears(int $ageYears): bool
    {
        if ($this->minimum_age !== null && $ageYears < $this->minimum_age) {
            return false;
        }
        if ($this->maximum_age !== null && $ageYears > $this->maximum_age) {
            return false;
        }

        return true;
    }

    public function isAvailableForDoctor($doctorId)
    {
        // Check if doctor has a specific price entry for this service (regardless of is_active)
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->first();

        // If doctor has a custom price entry, use its is_active status
        // This allows explicitly disabling a service for specific doctors
        if ($doctorPrice) {
            return (bool) $doctorPrice->is_active;
        }

        // If no specific entry exists, the service is available for all doctors
        // when the global service is active
        return $this->is_active;
    }
}

