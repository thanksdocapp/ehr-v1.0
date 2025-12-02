<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorServicePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'service_id',
        'custom_price',
        'custom_duration_minutes',
        'is_active'
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
        'custom_duration_minutes' => 'integer',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

