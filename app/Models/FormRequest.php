<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FormRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_document_id',
        'template_id',
        'patient_id',
        'requested_by',
        'token',
        'recipient_email',
        'status',
        'form_data',
        'rendered_content',
        'sent_at',
        'opened_at',
        'completed_at',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'form_data' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_OPENED = 'opened';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_EXPIRED = 'expired';

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (FormRequest $formRequest) {
            if (empty($formRequest->token)) {
                $formRequest->token = Str::random(64);
            }
            if (empty($formRequest->expires_at)) {
                $formRequest->expires_at = now()->addDays(30);
            }
        });
    }

    /**
     * Get the generated document.
     */
    public function generatedDocument(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class);
    }

    /**
     * Get the template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the patient.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who requested this form.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending forms.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter completed forms.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to filter forms requested by a user.
     */
    public function scopeRequestedBy($query, User $user)
    {
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }
        return $query->where('requested_by', $user->id);
    }

    /**
     * Check if form is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if form has been opened.
     */
    public function isOpened(): bool
    {
        return $this->status === self::STATUS_OPENED;
    }

    /**
     * Check if form is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if form is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED ||
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if form can be filled.
     */
    public function canBeFilled(): bool
    {
        return !$this->isCompleted() && !$this->isExpired();
    }

    /**
     * Mark form as opened.
     */
    public function markAsOpened(): bool
    {
        if ($this->status === self::STATUS_PENDING) {
            return $this->update([
                'status' => self::STATUS_OPENED,
                'opened_at' => now(),
            ]);
        }
        return false;
    }

    /**
     * Mark form as completed with submitted data.
     */
    public function markAsCompleted(array $formData): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'form_data' => $formData,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark form as expired.
     */
    public function markAsExpired(): bool
    {
        return $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Get the public URL for filling the form.
     */
    public function getPublicUrl(): string
    {
        return route('forms.fill', $this->token);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_OPENED => 'bg-info',
            self::STATUS_COMPLETED => 'bg-success',
            self::STATUS_EXPIRED => 'bg-secondary',
            default => 'bg-secondary',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
