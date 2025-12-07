<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_request_id',
        'patient_id',
        'doctor_id',
        'prescription_id',
        'order_number',
        'external_order_id',
        'pharmacy_id',
        'pharmacy_name',
        'medications',
        'status',
        'delivery_method',
        'delivery_address',
        'clinical_notes',
        'submitted_at',
        'dispensed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'medications' => 'array',
        'submitted_at' => 'datetime',
        'dispensed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DISPENSING = 'dispensing';
    const STATUS_READY = 'ready';
    const STATUS_COLLECTED = 'collected';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REJECTED = 'rejected';

    /**
     * Delivery methods
     */
    const DELIVERY_COLLECTION = 'collection';
    const DELIVERY_DELIVERY = 'delivery';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->order_number)) {
                $model->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'RX';
        $timestamp = now()->format('ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $timestamp, $count);
    }

    /**
     * Get integration request
     */
    public function integrationRequest()
    {
        return $this->belongsTo(IntegrationRequest::class);
    }

    /**
     * Get patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get original prescription
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Mark as submitted
     */
    public function markSubmitted(string $externalOrderId = null): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'external_order_id' => $externalOrderId,
        ]);
    }

    /**
     * Mark as accepted
     */
    public function markAccepted(): void
    {
        $this->update(['status' => self::STATUS_ACCEPTED]);
    }

    /**
     * Mark as dispensing
     */
    public function markDispensing(): void
    {
        $this->update(['status' => self::STATUS_DISPENSING]);
    }

    /**
     * Mark as ready
     */
    public function markReady(): void
    {
        $this->update(['status' => self::STATUS_READY]);
    }

    /**
     * Mark as collected
     */
    public function markCollected(): void
    {
        $this->update([
            'status' => self::STATUS_COLLECTED,
            'dispensed_at' => now(),
        ]);
    }

    /**
     * Mark as delivered
     */
    public function markDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'dispensed_at' => now(),
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markRejected(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Mark as cancelled
     */
    public function markCancelled(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_SUBMITTED => 'bg-info',
            self::STATUS_ACCEPTED => 'bg-primary',
            self::STATUS_DISPENSING => 'bg-warning',
            self::STATUS_READY => 'bg-success',
            self::STATUS_COLLECTED => 'bg-success',
            self::STATUS_DELIVERED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-secondary',
            self::STATUS_REJECTED => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_DISPENSING => 'Dispensing',
            self::STATUS_READY => 'Ready for Collection',
            self::STATUS_COLLECTED => 'Collected',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REJECTED => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if completed (collected or delivered)
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COLLECTED, self::STATUS_DELIVERED]);
    }

    /**
     * Check if can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_ACCEPTED]);
    }

    /**
     * Scope: Active (not cancelled or rejected)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_REJECTED]);
    }

    /**
     * Scope: For patient
     */
    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: For doctor
     */
    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Get medication count
     */
    public function getMedicationCount(): int
    {
        return count($this->medications ?? []);
    }
}
