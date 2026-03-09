<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendingClinicBooking extends Model
{
    protected $fillable = [
        'booking_token',
        'department_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'notes',
        'patient_data',
        'fee',
        'invoice_id',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'patient_data' => 'array',
        'appointment_date' => 'date',
        'fee' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    public static function generateBookingToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('booking_token', $token)->exists());

        return $token;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValidForPayment(): bool
    {
        return $this->status === 'pending_payment' && !$this->isExpired();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }
}
