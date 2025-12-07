<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LabTestOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_request_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'order_number',
        'external_order_id',
        'tests_requested',
        'priority',
        'status',
        'clinical_notes',
        'special_instructions',
        'fasting_required',
        'sample_collected_at',
        'results_received_at',
        'results',
        'results_pdf_path',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'tests_requested' => 'array',
        'results' => 'array',
        'fasting_required' => 'date',
        'sample_collected_at' => 'datetime',
        'results_received_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ORDERED = 'ordered';
    const STATUS_SAMPLE_COLLECTED = 'sample_collected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_RESULTS_READY = 'results_ready';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Priority constants
     */
    const PRIORITY_ROUTINE = 'routine';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_STAT = 'stat';

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
        $prefix = 'LAB';
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
     * Get appointment
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Mark as ordered
     */
    public function markOrdered(string $externalOrderId = null): void
    {
        $this->update([
            'status' => self::STATUS_ORDERED,
            'external_order_id' => $externalOrderId,
        ]);
    }

    /**
     * Mark sample collected
     */
    public function markSampleCollected(): void
    {
        $this->update([
            'status' => self::STATUS_SAMPLE_COLLECTED,
            'sample_collected_at' => now(),
        ]);
    }

    /**
     * Mark results ready
     */
    public function markResultsReady(array $results, string $pdfPath = null): void
    {
        $this->update([
            'status' => self::STATUS_RESULTS_READY,
            'results_received_at' => now(),
            'results' => $results,
            'results_pdf_path' => $pdfPath,
        ]);
    }

    /**
     * Mark as reviewed
     */
    public function markReviewed(int $reviewerId, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REVIEWED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-secondary',
            self::STATUS_ORDERED => 'bg-info',
            self::STATUS_SAMPLE_COLLECTED => 'bg-primary',
            self::STATUS_PROCESSING => 'bg-warning',
            self::STATUS_RESULTS_READY => 'bg-success',
            self::STATUS_REVIEWED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
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
            self::STATUS_ORDERED => 'Ordered',
            self::STATUS_SAMPLE_COLLECTED => 'Sample Collected',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_RESULTS_READY => 'Results Ready',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get priority badge class
     */
    public function getPriorityBadgeClass(): string
    {
        return match($this->priority) {
            self::PRIORITY_ROUTINE => 'bg-secondary',
            self::PRIORITY_URGENT => 'bg-warning',
            self::PRIORITY_STAT => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    /**
     * Check if results are available
     */
    public function hasResults(): bool
    {
        return !empty($this->results) || !empty($this->results_pdf_path);
    }

    /**
     * Check if needs review
     */
    public function needsReview(): bool
    {
        return $this->status === self::STATUS_RESULTS_READY && empty($this->reviewed_at);
    }

    /**
     * Scope: Needs review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('status', self::STATUS_RESULTS_READY)
                     ->whereNull('reviewed_at');
    }

    /**
     * Scope: By status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
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
}
