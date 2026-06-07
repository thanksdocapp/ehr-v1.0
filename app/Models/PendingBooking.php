<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PendingBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_token',
        'doctor_id',
        'service_id',
        'department_id',
        'availability_rule_id',
        'appointment_date',
        'appointment_time',
        'is_online',
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
        'is_online' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a unique booking token.
     */
    public static function generateBookingToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('booking_token', $token)->exists());

        return $token;
    }

    /**
     * Check if the booking has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the booking is still valid for payment.
     */
    public function isValidForPayment(): bool
    {
        return $this->status === 'pending_payment' && !$this->isExpired();
    }

    /**
     * Get patient first name from patient_data.
     */
    public function getPatientFirstNameAttribute(): ?string
    {
        return $this->patient_data['first_name'] ?? null;
    }

    /**
     * Get patient last name from patient_data.
     */
    public function getPatientLastNameAttribute(): ?string
    {
        return $this->patient_data['last_name'] ?? null;
    }

    /**
     * Get patient email from patient_data.
     */
    public function getPatientEmailAttribute(): ?string
    {
        return $this->patient_data['email'] ?? null;
    }

    /**
     * Get patient full name.
     */
    public function getPatientFullNameAttribute(): string
    {
        return trim(($this->patient_data['first_name'] ?? '') . ' ' . ($this->patient_data['last_name'] ?? ''));
    }

    /**
     * Relationships
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service()
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Scope to get non-expired pending bookings.
     */
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment')
                     ->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired bookings.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
                     ->where('status', 'pending_payment');
    }

    /**
     * Mark booking as completed.
     */
    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark booking as expired.
     */
    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark booking as payment failed.
     */
    public function markPaymentFailed(): void
    {
        $this->update(['status' => 'payment_failed']);
    }

    /**
     * Mark booking as cancelled.
     */
    public function markCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
