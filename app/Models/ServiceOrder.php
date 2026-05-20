<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOrder extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PAID = 'paid';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'order_number',
        'booking_token',
        'patient_id',
        'doctor_id',
        'department_id',
        'service_id',
        'invoice_id',
        'patient_data',
        'notes',
        'list_price',
        'discount_amount',
        'fee',
        'doctor_booking_discount_code_id',
        'clinic_booking_discount_code_id',
        'status',
        'expires_at',
        'paid_at',
        'contacted_at',
    ];

    protected $casts = [
        'patient_data' => 'array',
        'list_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'contacted_at' => 'datetime',
    ];

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'SO-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while (self::where('order_number', $number)->exists());

        return $number;
    }

    public static function generateBookingToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
        } while (self::where('booking_token', $token)->exists());

        return $token;
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
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

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValidForPayment(): bool
    {
        return $this->status === self::STATUS_PENDING_PAYMENT && ! $this->isExpired();
    }

    public function markExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }
}
