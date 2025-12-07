<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IntegrationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_module_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'request_type',
        'external_reference',
        'internal_reference',
        'status',
        'request_data',
        'response_data',
        'notes',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->internal_reference)) {
                $model->internal_reference = static::generateReference();
            }
        });
    }

    /**
     * Generate unique internal reference
     */
    public static function generateReference(): string
    {
        $prefix = 'INT';
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(6));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Get integration module
     */
    public function integrationModule()
    {
        return $this->belongsTo(IntegrationModule::class);
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
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get lab test order
     */
    public function labTestOrder()
    {
        return $this->hasOne(LabTestOrder::class);
    }

    /**
     * Get prescription order
     */
    public function prescriptionOrder()
    {
        return $this->hasOne(PrescriptionOrder::class);
    }

    /**
     * Get imaging order
     */
    public function imagingOrder()
    {
        return $this->hasOne(ImagingOrder::class);
    }

    /**
     * Mark as submitted
     */
    public function markSubmitted(string $externalReference = null): void
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'external_reference' => $externalReference,
        ]);
    }

    /**
     * Mark as processing
     */
    public function markProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(array $responseData = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'response_data' => $responseData ?? $this->response_data,
        ]);
    }

    /**
     * Mark as failed
     */
    public function markFailed(string $errorMessage, array $responseData = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $errorMessage,
            'response_data' => $responseData ?? $this->response_data,
        ]);

        // Also log error on the module
        $this->integrationModule->logError($errorMessage);
    }

    /**
     * Mark as cancelled
     */
    public function markCancelled(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason,
        ]);
    }

    /**
     * Scope: By status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: In progress (submitted or processing)
     */
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [self::STATUS_SUBMITTED, self::STATUS_PROCESSING]);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-warning',
            self::STATUS_SUBMITTED => 'bg-info',
            self::STATUS_PROCESSING => 'bg-primary',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_FAILED => 'bg-danger',
            self::STATUS_CANCELLED => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
