<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sms_template_id',
        'recipient_phone',
        'recipient_name',
        'message',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'retries',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'json',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_phone', 'phone');
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'sent' => 'bg-success',
            'failed' => 'bg-danger',
            'pending' => 'bg-warning',
            'cancelled' => 'bg-secondary'
        ][$this->status] ?? 'bg-secondary';
    }

    public function wasSuccessful()
    {
        return $this->status === 'sent';
    }

    public function markAsSent()
    {
        return $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }

    public function markAsFailed($error = null)
    {
        return $this->update([
            'status' => 'failed',
            'error_message' => $error
        ]);
    }
}
