<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A recurring weekly availability window for a doctor, tagged with the modality it supports.
 *
 * modality is one of: in_person, online, telephone, or "all" (serves every modality —
 * the compatibility value produced by the backfill). The booking modality itself comes from
 * the service (consultation_type); these rules answer "when is that modality possible?".
 */
class DoctorAvailabilityRule extends Model
{
    use HasFactory;

    public const MODALITY_IN_PERSON = 'in_person';
    public const MODALITY_ONLINE = 'online';
    public const MODALITY_TELEPHONE = 'telephone';
    public const MODALITY_ALL = 'all';

    public const SOURCE_BACKFILL = 'backfill';
    public const SOURCE_DEFAULT = 'default';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_WEEKLY_SCHEDULE = 'weekly_schedule';

    /** @var array<int, string> */
    public const AUTO_MANAGED_SOURCES = [
        self::SOURCE_BACKFILL,
        self::SOURCE_DEFAULT,
        self::SOURCE_WEEKLY_SCHEDULE,
    ];

    /** Concrete modalities a service can request (telephone is treated like online: remote). */
    public const CONCRETE_MODALITIES = ['in_person', 'online', 'telephone'];

    protected $fillable = [
        'doctor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'modality',
        'is_active',
        'needs_review',
        'source',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'needs_review' => 'boolean',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, string $dayName)
    {
        return $query->where('day_of_week', strtolower($dayName));
    }

    public function scopeNeedsReview($query)
    {
        return $query->where('needs_review', true);
    }

    /**
     * Expand this rule's modality into the concrete set it serves.
     * An "all" rule serves every concrete modality.
     *
     * @return list<string>
     */
    public function modalitySet(): array
    {
        return self::expandModality($this->modality);
    }

    /**
     * @return list<string>
     */
    public static function expandModality(?string $modality): array
    {
        if ($modality === self::MODALITY_ALL || $modality === null) {
            return self::CONCRETE_MODALITIES;
        }

        return in_array($modality, self::CONCRETE_MODALITIES, true) ? [$modality] : [];
    }

    /**
     * Whether this rule's window supports the given concrete modality.
     */
    public function supportsModality(string $modality): bool
    {
        return in_array($modality, $this->modalitySet(), true);
    }

    /**
     * Normalize a consultation_type/modality string to a concrete modality, defaulting to in_person.
     */
    public static function normalizeModality(?string $modality): string
    {
        $modality = is_string($modality) ? strtolower(trim($modality)) : '';

        return in_array($modality, self::CONCRETE_MODALITIES, true) ? $modality : self::MODALITY_IN_PERSON;
    }
}
