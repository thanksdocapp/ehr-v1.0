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
        'default_price',
        'tags',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'default_price' => 'decimal:2',
        'default_duration_minutes' => 'integer'
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

    // Helper methods
    public function getPriceForDoctor($doctorId)
    {
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->first();

        return $doctorPrice ? $doctorPrice->custom_price : $this->default_price;
    }

    public function getDurationForDoctor($doctorId)
    {
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->first();

        return $doctorPrice ? $doctorPrice->custom_duration_minutes : $this->default_duration_minutes;
    }

    public function isAvailableForDoctor($doctorId)
    {
        $doctorPrice = $this->doctorPrices()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->first();

        // If doctor has a custom price entry, use its is_active status
        if ($doctorPrice) {
            return $doctorPrice->is_active;
        }

        // Otherwise, use global service is_active status
        return $this->is_active;
    }
}

