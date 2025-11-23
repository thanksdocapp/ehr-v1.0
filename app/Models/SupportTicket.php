<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'message',
        'status',
        'attachments',
        'replies',
        'contact_name',
        'contact_email',
        'contact_phone',
        'assigned_to',
        'last_reply_at',
        'resolved_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'replies' => 'array',
        'last_reply_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin assigned to the ticket.
     */
    public function assignedAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }

    /**
     * Scope for open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for closed tickets.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for tickets by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for tickets by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open' => 'primary',
            'in_progress' => 'info',
            'awaiting_admin' => 'warning',
            'awaiting_user' => 'secondary',
            'resolved' => 'success',
            'closed' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'success',
            'medium' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'general' => 'General',
            'account' => 'Account',
            'transaction' => 'Transaction',
            'card' => 'Virtual Card',
            'loan' => 'Loan',
            'deposit' => 'Deposit',
            'technical' => 'Technical',
            'other' => 'Other',
        ];

        return $labels[$this->category] ?? $this->category;
    }

    /**
     * Get the total number of replies.
     */
    public function getRepliesCountAttribute(): int
    {
        return count($this->replies ?? []);
    }

    /**
     * Check if ticket is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if ticket is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if ticket is awaiting admin response.
     */
    public function isAwaitingAdmin(): bool
    {
        return in_array($this->status, ['open', 'awaiting_admin']);
    }

    /**
     * Check if ticket is awaiting user response.
     */
    public function isAwaitingUser(): bool
    {
        return $this->status === 'awaiting_user';
    }

    /**
     * Mark ticket as closed.
     */
    public function close(): bool
    {
        return $this->update([
            'status' => 'closed',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark ticket as resolved.
     */
    public function resolve(): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Assign ticket to an admin.
     */
    public function assignTo(int $adminId): bool
    {
        return $this->update([
            'assigned_to' => $adminId,
            'status' => 'in_progress',
        ]);
    }
}
