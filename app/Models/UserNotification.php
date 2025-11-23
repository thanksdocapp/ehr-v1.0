<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;

    /**
     * Hospital notification types
     */
    const TYPE_APPOINTMENT = 'appointment';
    const TYPE_PRESCRIPTION = 'prescription';
    const TYPE_LAB_RESULT = 'lab_result';
    const TYPE_BILLING = 'billing';
    const TYPE_MEDICAL_RECORD = 'medical_record';
    const TYPE_SYSTEM = 'system';
    const TYPE_REMINDER = 'reminder';
    const TYPE_ALERT = 'alert';
    const TYPE_WELCOME = 'welcome';

    /**
     * Hospital notification categories
     */
    const CATEGORY_MEDICAL = 'medical';
    const CATEGORY_ADMINISTRATIVE = 'administrative';
    const CATEGORY_BILLING = 'billing';
    const CATEGORY_APPOINTMENT = 'appointment';
    const CATEGORY_SYSTEM = 'system';

    /**
     * Notification channels
     */
    const CHANNEL_WEB = 'web';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'priority',
        'is_read',
        'read_at',
        'action_url',
        'category',
        'related_appointment_id',
        'related_patient_id',
        'related_doctor_id',
        'notification_channel',
        'email_sent',
        'sms_sent',
        'scheduled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'json',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'user_id' => 'integer',
        'related_appointment_id' => 'integer',
        'related_patient_id' => 'integer',
        'related_doctor_id' => 'integer',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = now();
            $this->save();
        }
        
        return $this;
    }

    /**
     * Get color class based on notification type
     */
    public function getTypeColorAttribute()
    {
        $colors = [
            self::TYPE_APPOINTMENT => 'info',
            self::TYPE_PRESCRIPTION => 'primary',
            self::TYPE_LAB_RESULT => 'success',
            self::TYPE_BILLING => 'warning',
            self::TYPE_MEDICAL_RECORD => 'secondary',
            self::TYPE_REMINDER => 'info',
            self::TYPE_ALERT => 'danger',
            self::TYPE_WELCOME => 'success',
            self::TYPE_SYSTEM => 'dark',
        ];
        
        return $colors[$this->type] ?? 'secondary';
    }

    /**
     * Get icon based on notification type
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            self::TYPE_APPOINTMENT => 'fas fa-calendar-check',
            self::TYPE_PRESCRIPTION => 'fas fa-file-prescription',
            self::TYPE_LAB_RESULT => 'fas fa-vial',
            self::TYPE_BILLING => 'fas fa-file-invoice-dollar',
            self::TYPE_MEDICAL_RECORD => 'fas fa-file-medical',
            self::TYPE_REMINDER => 'fas fa-bell',
            self::TYPE_ALERT => 'fas fa-exclamation-triangle',
            self::TYPE_WELCOME => 'fas fa-handshake',
            self::TYPE_SYSTEM => 'fas fa-cogs',
        ];
        
        return $icons[$this->type] ?? 'fas fa-bell';
    }

    /**
     * Relationships to other hospital models
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'related_appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'related_patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'related_doctor_id');
    }

    /**
     * Mark notification as sent via a specific channel
     */
    public function markAsSent(string $channel)
    {
        if ($channel === self::CHANNEL_EMAIL) {
            $this->email_sent = true;
        } elseif ($channel === self::CHANNEL_SMS) {
            $this->sms_sent = true;
        }
        $this->save();

        return $this;
    }

    /**
     * Scope notifications by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope notifications by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope notifications scheduled for sending
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')->where('scheduled_at', '<=', now());
    }

    /**
     * Scope notifications for a specific user (their own notifications)
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope notifications for a specific doctor (by related_doctor_id)
     */
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('related_doctor_id', $doctorId);
    }

    /**
     * Scope notifications for a specific patient (by related_patient_id)
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('related_patient_id', $patientId);
    }

    /**
     * Scope notifications visible to a user based on their role
     */
    public function scopeVisibleToUser($query, $user)
    {
        // Admins can see all notifications
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }

        // For doctors, show notifications where:
        // 1. user_id matches (direct notifications)
        // 2. related_doctor_id matches their doctor record
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                return $query->where(function($q) use ($user, $doctor) {
                    $q->where('user_id', $user->id)
                      ->orWhere('related_doctor_id', $doctor->id);
                });
            }
            // If no doctor record, only show direct notifications
            return $query->where('user_id', $user->id);
        }

        // For other staff, show only their direct notifications
        return $query->where('user_id', $user->id);
    }
}
