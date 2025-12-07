<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PatientDocument extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_FINAL = 'final';
    const STATUS_VOID = 'void';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Approval status constants
    const APPROVAL_NOT_REQUIRED = 'not_required';
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'patient_id',
        'template_id',
        'type',
        'title',
        'status',
        'priority',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'content',
        'form_data',
        'pdf_path',
        'created_by',
        'updated_by',
        'signed_by_patient',
        'signed_at',
        'additional_signatures',
        'witness_id',
        'witnessed_at',
        'appointment_id',
        'encounter_id',
        'internal_notes',
        'revision_history',
        'valid_from',
        'valid_until',
        'external_reference',
        'is_confidential',
        'favorited_by',
    ];

    protected $casts = [
        'form_data' => 'array',
        'additional_signatures' => 'array',
        'revision_history' => 'array',
        'favorited_by' => 'array',
        'signed_by_patient' => 'boolean',
        'is_confidential' => 'boolean',
        'signed_at' => 'datetime',
        'approved_at' => 'datetime',
        'witnessed_at' => 'datetime',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Get the patient that owns the document.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the template used for this document.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    /**
     * Get the user who created the document.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the document.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who approved the document.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the witness user.
     */
    public function witness(): BelongsTo
    {
        return $this->belongsTo(User::class, 'witness_id');
    }

    /**
     * Get the related appointment.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Get the deliveries for this document.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(DocumentDelivery::class, 'patient_document_id');
    }

    /**
     * Check if document is final.
     */
    public function isFinal(): bool
    {
        return $this->status === self::STATUS_FINAL;
    }

    /**
     * Check if document is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if document is void.
     */
    public function isVoid(): bool
    {
        return $this->status === self::STATUS_VOID;
    }

    /**
     * Check if document requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    /**
     * Check if document is approved.
     */
    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    /**
     * Check if document is valid (within validity period).
     */
    public function isValid(): bool
    {
        if (!$this->isFinal()) {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if document is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->valid_until) {
            return false;
        }

        return now()->startOfDay()->gt($this->valid_until);
    }

    /**
     * Check if document is signed.
     */
    public function isSigned(): bool
    {
        return $this->signed_by_patient === true;
    }

    /**
     * Check if document is witnessed.
     */
    public function isWitnessed(): bool
    {
        return $this->witness_id !== null && $this->witnessed_at !== null;
    }

    /**
     * Check if document has been sent.
     */
    public function hasBeenSent(): bool
    {
        return $this->deliveries()->where('status', 'sent')->exists();
    }

    /**
     * Approve the document.
     */
    public function approve(?User $user = null, ?string $notes = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$this->isDraft() || $this->approval_status !== self::APPROVAL_PENDING) {
            return false;
        }

        $this->update([
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'updated_by' => $user->id,
        ]);

        $this->addRevision('approved', $user);

        return true;
    }

    /**
     * Reject the document.
     */
    public function reject(?User $user = null, ?string $reason = null): bool
    {
        $user = $user ?? Auth::user();

        if ($this->approval_status !== self::APPROVAL_PENDING) {
            return false;
        }

        $this->update([
            'approval_status' => self::APPROVAL_REJECTED,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $reason,
            'updated_by' => $user->id,
        ]);

        $this->addRevision('rejected', $user, ['reason' => $reason]);

        return true;
    }

    /**
     * Add witness signature.
     */
    public function addWitness(User $witness): bool
    {
        if (!$this->isFinal()) {
            return false;
        }

        $this->update([
            'witness_id' => $witness->id,
            'witnessed_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        $this->addRevision('witnessed', $witness);

        return true;
    }

    /**
     * Add additional signature.
     */
    public function addSignature(User $signer, ?string $role = null, ?string $signaturePath = null): bool
    {
        if (!$this->isFinal()) {
            return false;
        }

        $signatures = $this->additional_signatures ?? [];

        $signatures[] = [
            'user_id' => $signer->id,
            'name' => $signer->name,
            'role' => $role ?? $signer->role,
            'signed_at' => now()->toIso8601String(),
            'signature_path' => $signaturePath,
        ];

        $this->update([
            'additional_signatures' => $signatures,
            'updated_by' => Auth::id(),
        ]);

        $this->addRevision('signature_added', $signer);

        return true;
    }

    /**
     * Add revision to history.
     */
    public function addRevision(string $action, ?User $user = null, array $data = []): void
    {
        $user = $user ?? Auth::user();
        $history = $this->revision_history ?? [];

        $history[] = [
            'action' => $action,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ];

        $this->update(['revision_history' => $history]);
    }

    /**
     * Generate document number.
     */
    public static function generateDocumentNumber(): string
    {
        $format = DocumentSetting::get('document_numbering_format', 'DOC-{YEAR}-{SEQ}');
        $sequence = DocumentSetting::get('document_numbering_sequence', 1);

        $number = str_replace(
            ['{YEAR}', '{MONTH}', '{DAY}', '{SEQ}'],
            [date('Y'), date('m'), date('d'), str_pad($sequence, 5, '0', STR_PAD_LEFT)],
            $format
        );

        // Increment sequence
        DocumentSetting::set('document_numbering_sequence', $sequence + 1, 'integer');

        return $number;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeOfPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for urgent/high priority documents.
     */
    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Scope for pending approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    /**
     * Scope for documents awaiting signature.
     */
    public function scopeAwaitingSignature($query)
    {
        return $query->where('status', self::STATUS_FINAL)
            ->where('signed_by_patient', false);
    }

    /**
     * Scope for valid documents (within validity period).
     */
    public function scopeValid($query)
    {
        $now = now()->startOfDay();

        return $query->where('status', self::STATUS_FINAL)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            });
    }

    /**
     * Scope for expired documents.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_FINAL)
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->startOfDay());
    }

    /**
     * Scope for confidential documents.
     */
    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    /**
     * Scope to filter documents owned by a specific user (doctor).
     * Doctors can only see documents they created.
     * Admin can see all documents.
     */
    public function scopeOwnedBy($query, User $user)
    {
        // Admin can see all
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }

        // For doctors and other staff, only show documents they created
        return $query->where('created_by', $user->id);
    }

    /**
     * Check if document is owned by user.
     */
    public function isOwnedBy(User $user): bool
    {
        // Admin owns all
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $this->created_by === $user->id;
    }

    /**
     * Get priority badge class.
     */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            self::PRIORITY_URGENT => 'bg-danger',
            self::PRIORITY_HIGH => 'bg-warning',
            self::PRIORITY_LOW => 'bg-secondary',
            default => 'bg-info',
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_FINAL => 'bg-success',
            self::STATUS_VOID => 'bg-danger',
            default => 'bg-warning',
        };
    }

    /**
     * Get approval status badge class.
     */
    public function getApprovalBadgeClassAttribute(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_APPROVED => 'bg-success',
            self::APPROVAL_REJECTED => 'bg-danger',
            self::APPROVAL_PENDING => 'bg-warning',
            default => 'bg-secondary',
        };
    }

    /**
     * Get formatted validity period.
     */
    public function getValidityPeriodAttribute(): ?string
    {
        if (!$this->valid_from && !$this->valid_until) {
            return null;
        }

        $from = $this->valid_from ? $this->valid_from->format('d M Y') : 'N/A';
        $until = $this->valid_until ? $this->valid_until->format('d M Y') : 'Indefinite';

        return "{$from} - {$until}";
    }

    /**
     * Get available actions for current user.
     */
    public function getAvailableActions(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        $actions = [];

        if ($this->isDraft()) {
            $actions[] = 'edit';
            $actions[] = 'delete';

            if ($this->approval_status === self::APPROVAL_PENDING) {
                if ($user->can('approve', $this)) {
                    $actions[] = 'approve';
                    $actions[] = 'reject';
                }
            } else {
                $actions[] = 'finalize';
            }
        }

        if ($this->isFinal()) {
            $actions[] = 'view';
            $actions[] = 'download';
            $actions[] = 'send';

            if (!$this->signed_by_patient && $this->template?->requires_signature) {
                $actions[] = 'request_signature';
            }

            if ($this->template?->requires_witness && !$this->isWitnessed()) {
                $actions[] = 'add_witness';
            }

            if (!$this->hasBeenSent()) {
                $actions[] = 'void';
            }
        }

        return $actions;
    }

    /**
     * Get priority options.
     */
    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_FINAL => 'Final',
            self::STATUS_VOID => 'Void',
        ];
    }
}
