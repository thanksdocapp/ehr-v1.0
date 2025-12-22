<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EmailLog extends Model
{
    use HasFactory;

    /**
     * Cache of existing columns for the current connection.
     * This prevents repeated schema queries when logging emails.
     *
     * @var array<string, bool>|null
     */
    protected static $existingColumns = null;

    protected static function booted()
    {
        $sanitize = function (EmailLog $model): void {
            // Lazily cache column map; if schema introspection fails, do nothing (avoid breaking logging).
            if (static::$existingColumns === null) {
                try {
                    $cols = Schema::getColumnListing($model->getTable());
                    static::$existingColumns = array_fill_keys($cols, true);
                } catch (\Throwable $e) {
                    static::$existingColumns = [];
                }
            }

            // If we couldn't load columns (e.g., DB error), don't mutate attributes.
            if (empty(static::$existingColumns)) {
                return;
            }

            // Remove any attributes that don't exist as columns to avoid SQL "unknown column" errors.
            foreach (array_keys($model->getAttributes()) as $key) {
                if (!isset(static::$existingColumns[$key])) {
                    unset($model->{$key});
                }
            }
        };

        static::creating($sanitize);
        static::updating($sanitize);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_template_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'body',
        'variables',
        'cc_emails',
        'bcc_emails',
        'attachments',
        'status',
        'error_message',
        'sent_at',
        'opened_at',
        'metadata',
        'event',
        'patient_id',
        'billing_id',
        'invoice_id',
        'payment_id',
        'email_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'variables' => 'array',
        'cc_emails' => 'array',
        'bcc_emails' => 'array',
        'attachments' => 'array',
        'metadata' => 'json',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // PHI Encryption - Email content may contain sensitive medical information
        'body' => 'encrypted',
    ];

    /**
     * Get the email template.
     */
    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * Get the recipient user if exists.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_email', 'email');
    }

    /**
     * Get the patient associated with this email log.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the attachments for this email log.
     */
    public function attachments()
    {
        return $this->hasMany(EmailAttachment::class);
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        return [
            'sent' => 'bg-success',
            'failed' => 'bg-danger',
            'pending' => 'bg-warning',
            'cancelled' => 'bg-secondary'
        ][$this->status] ?? 'bg-secondary';
    }

    /**
     * Check if email was sent successfully.
     *
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->status === 'sent';
    }

    /**
     * Check if email has been opened.
     *
     * @return bool
     */
    public function wasOpened()
    {
        return !is_null($this->opened_at);
    }

    /**
     * Mark email as opened.
     *
     * @return bool
     */
    public function markAsOpened()
    {
        if (!$this->wasOpened()) {
            return $this->update(['opened_at' => now()]);
        }
        return false;
    }

    /**
     * Mark email as sent.
     *
     * @return bool
     */
    public function markAsSent()
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    /**
     * Mark email as failed.
     *
     * @param string $error
     * @return bool
     */
    public function markAsFailed($error = null)
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $error
        ]);
    }

    /**
     * Scope sent emails.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope failed emails.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope pending emails.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope opened emails.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpened($query)
    {
        return $query->whereNotNull('opened_at');
    }
}
