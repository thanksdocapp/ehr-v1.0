<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DocumentDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_document_id',
        'patient_id',
        'sent_by',
        'recipient_type',
        'recipient_name',
        'recipient_email',
        'recipient_phone',
        'channel',
        'status',
        'sent_at',
        'opened_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    /**
     * Get the document being delivered.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(PatientDocument::class, 'patient_document_id');
    }

    /**
     * Get the patient for this delivery.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who sent the delivery.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Check if delivery is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if delivery was sent successfully.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Check if delivery failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark delivery as sent.
     */
    public function markAsSent(array $meta = []): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'meta' => array_merge($this->meta ?? [], $meta),
        ]);
    }

    /**
     * Mark delivery as failed.
     */
    public function markAsFailed(array $meta = []): void
    {
        $this->update([
            'status' => 'failed',
            'meta' => array_merge($this->meta ?? [], $meta),
        ]);
    }

    /**
     * Mark delivery as opened.
     */
    public function markAsOpened(): void
    {
        if (!$this->opened_at) {
            $this->update([
                'opened_at' => now(),
            ]);
        }
    }

    /**
     * Check if delivery was opened.
     */
    public function isOpened(): bool
    {
        return $this->opened_at !== null;
    }

    /**
     * Generate tracking token for this delivery.
     */
    public function getTrackingToken(): string
    {
        return base64_encode($this->id . ':' . md5($this->id . $this->created_at . config('app.key')));
    }

    /**
     * Get delivery by tracking token.
     */
    public static function findByTrackingToken(string $token): ?self
    {
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);

        if (count($parts) !== 2) {
            return null;
        }

        $id = $parts[0];
        $delivery = self::find($id);

        if (!$delivery) {
            return null;
        }

        // Verify token
        $expectedHash = md5($delivery->id . $delivery->created_at . config('app.key'));
        if ($parts[1] !== $expectedHash) {
            return null;
        }

        return $delivery;
    }

    /**
     * Scope to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by recipient type.
     */
    public function scopeOfRecipientType($query, string $type)
    {
        return $query->where('recipient_type', $type);
    }
}
