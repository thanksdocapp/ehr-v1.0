<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'type',
        'title',
        'message',
        'data',
        'priority',
        'action_url',
        'is_read',
        'read_at',
        'category',
        'related_appointment_id',
        'related_patient_id',
        'related_doctor_id',
        'notification_channel',
        'email_sent',
        'sms_sent',
        'scheduled_at',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'related_appointment_id' => 'integer',
        'related_patient_id' => 'integer',
        'related_doctor_id' => 'integer',
        'data' => 'array',
        'is_read' => 'boolean',
        'email_sent' => 'boolean',
        'sms_sent' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    // Constants for notification types (same as UserNotification)
    const TYPE_APPOINTMENT = 'appointment';
    const TYPE_PRESCRIPTION = 'prescription';
    const TYPE_LAB_RESULT = 'lab_result';
    const TYPE_BILLING = 'billing';
    const TYPE_MEDICAL_RECORD = 'medical_record';
    const TYPE_SYSTEM = 'system';

    // Constants for categories
    const CATEGORY_MEDICAL = 'medical';
    const CATEGORY_ADMINISTRATIVE = 'administrative';
    const CATEGORY_BILLING = 'billing';
    const CATEGORY_APPOINTMENT = 'appointment';

    // Constants for channels
    const CHANNEL_WEB = 'web';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function relatedAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'related_appointment_id');
    }

    public function relatedDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'related_doctor_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Methods
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    // Accessors for UI styling
    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            self::TYPE_APPOINTMENT => 'primary',
            self::TYPE_PRESCRIPTION => 'success',
            self::TYPE_LAB_RESULT => 'info',
            self::TYPE_BILLING => 'warning',
            self::TYPE_MEDICAL_RECORD => 'secondary',
            self::TYPE_SYSTEM => 'dark',
            default => 'primary'
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_APPOINTMENT => 'fas fa-calendar-alt',
            self::TYPE_PRESCRIPTION => 'fas fa-pills',
            self::TYPE_LAB_RESULT => 'fas fa-flask',
            self::TYPE_BILLING => 'fas fa-file-invoice-dollar',
            self::TYPE_MEDICAL_RECORD => 'fas fa-file-medical',
            self::TYPE_SYSTEM => 'fas fa-cog',
            default => 'fas fa-bell'
        };
    }
}
