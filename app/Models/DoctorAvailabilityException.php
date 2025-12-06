<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorAvailabilityException extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'exception_date',
        'type',
        'start_time',
        'end_time',
        'reason',
        'is_all_day'
    ];

    protected $casts = [
        'exception_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_all_day' => 'boolean'
    ];

    // Relationships
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    // Scopes
    public function scopeBlocked($query)
    {
        return $query->where('type', 'blocked');
    }

    public function scopeModified($query)
    {
        return $query->where('type', 'modified');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('exception_date', $date);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('exception_date', '>=', now()->toDateString());
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    // Helper methods
    public function isBlocked(): bool
    {
        return $this->type === 'blocked';
    }

    public function isModified(): bool
    {
        return $this->type === 'modified';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->exception_date->format('l, j F Y');
    }

    public function getFormattedTimeRangeAttribute(): ?string
    {
        if ($this->is_all_day) {
            return 'All Day';
        }

        if ($this->start_time && $this->end_time) {
            return $this->start_time->format('g:i A') . ' - ' . $this->end_time->format('g:i A');
        }

        return null;
    }
}
