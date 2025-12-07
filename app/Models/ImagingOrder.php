<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_request_id',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'order_number',
        'external_order_id',
        'scan_type',
        'body_part',
        'priority',
        'status',
        'clinical_indication',
        'clinical_history',
        'special_instructions',
        'contrast_required',
        'scheduled_at',
        'location',
        'completed_at',
        'report_received_at',
        'report',
        'report_pdf_path',
        'images',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'contrast_required' => 'boolean',
        'images' => 'array',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'report_received_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_REFERRED = 'referred';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REPORTED = 'reported';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Priority constants
     */
    const PRIORITY_ROUTINE = 'routine';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_EMERGENCY = 'emergency';

    /**
     * Scan types
     */
    const SCAN_MRI = 'MRI';
    const SCAN_CT = 'CT';
    const SCAN_XRAY = 'X-Ray';
    const SCAN_ULTRASOUND = 'Ultrasound';
    const SCAN_PET = 'PET';
    const SCAN_MAMMOGRAM = 'Mammogram';
    const SCAN_DEXA = 'DEXA';
    const SCAN_FLUOROSCOPY = 'Fluoroscopy';

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
        $prefix = 'IMG';
        $timestamp = now()->format('ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $timestamp, $count);
    }

    /**
     * Get all scan types
     */
    public static function getScanTypes(): array
    {
        return [
            self::SCAN_MRI => 'MRI (Magnetic Resonance Imaging)',
            self::SCAN_CT => 'CT Scan (Computed Tomography)',
            self::SCAN_XRAY => 'X-Ray',
            self::SCAN_ULTRASOUND => 'Ultrasound',
            self::SCAN_PET => 'PET Scan',
            self::SCAN_MAMMOGRAM => 'Mammogram',
            self::SCAN_DEXA => 'DEXA Bone Density Scan',
            self::SCAN_FLUOROSCOPY => 'Fluoroscopy',
        ];
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
     * Mark as referred
     */
    public function markReferred(string $externalOrderId = null): void
    {
        $this->update([
            'status' => self::STATUS_REFERRED,
            'external_order_id' => $externalOrderId,
        ]);
    }

    /**
     * Mark as scheduled
     */
    public function markScheduled(\DateTime $scheduledAt, string $location = null): void
    {
        $this->update([
            'status' => self::STATUS_SCHEDULED,
            'scheduled_at' => $scheduledAt,
            'location' => $location,
        ]);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark report received
     */
    public function markReported(string $report, string $pdfPath = null, array $images = null): void
    {
        $this->update([
            'status' => self::STATUS_REPORTED,
            'report_received_at' => now(),
            'report' => $report,
            'report_pdf_path' => $pdfPath,
            'images' => $images,
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
            self::STATUS_REFERRED => 'bg-info',
            self::STATUS_SCHEDULED => 'bg-primary',
            self::STATUS_COMPLETED => 'bg-warning',
            self::STATUS_REPORTED => 'bg-success',
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
            self::STATUS_REFERRED => 'Referred',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_COMPLETED => 'Scan Complete',
            self::STATUS_REPORTED => 'Report Ready',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Check if report is available
     */
    public function hasReport(): bool
    {
        return !empty($this->report) || !empty($this->report_pdf_path);
    }

    /**
     * Check if needs review
     */
    public function needsReview(): bool
    {
        return $this->status === self::STATUS_REPORTED && empty($this->reviewed_at);
    }

    /**
     * Scope: Needs review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('status', self::STATUS_REPORTED)
                     ->whereNull('reviewed_at');
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
