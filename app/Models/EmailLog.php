<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLog extends Model
{
    use HasFactory, SoftDeletes;

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
        'metadata'
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
        'deleted_at' => 'datetime'
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
