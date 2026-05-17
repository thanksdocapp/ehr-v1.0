<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicBookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'department_id',
        'service_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'accepted_by_user_id',
        'accepted_at',
        'appointment_date',
        'appointment_time',
        'consultation_type',
        'fee',
        'notes',
        'patient_data',
        'status',
        'created_from',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'patient_data' => 'array',
        'fee' => 'decimal:2',
        'accepted_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function acceptedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function scopePendingAcceptance($query)
    {
        return $query->where('status', 'pending_acceptance');
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public static function generateRequestNumber(): string
    {
        do {
            $number = 'CB' . date('Ymd') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (static::where('request_number', $number)->exists());

        return $number;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_acceptance';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
