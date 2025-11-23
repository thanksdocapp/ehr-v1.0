<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailBounce extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_log_id',
        'patient_id',
        'email_address',
        'bounce_type',
        'bounce_reason',
        'bounce_message',
        'smtp_response_code',
        'bounced_at',
        'is_resolved',
        'resolved_at',
        'resolved_by',
        'resolution_notes'
    ];

    protected $casts = [
        'bounced_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean'
    ];

    /**
     * Get the email log.
     */
    public function emailLog()
    {
        return $this->belongsTo(EmailLog::class);
    }

    /**
     * Get the patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who resolved the bounce.
     */
    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark bounce as resolved.
     */
    public function markAsResolved($resolvedBy, $notes = null)
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $notes
        ]);
    }

    /**
     * Scope for unresolved bounces.
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for hard bounces.
     */
    public function scopeHardBounces($query)
    {
        return $query->where('bounce_type', 'hard');
    }

    /**
     * Scope for complaints.
     */
    public function scopeComplaints($query)
    {
        return $query->where('bounce_type', 'complaint');
    }
}

