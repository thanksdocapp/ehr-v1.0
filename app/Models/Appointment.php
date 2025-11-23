<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'doctor_id',
        'department_id',
        'appointment_date',
        'appointment_time',
        'type',
        'status',
        'reason',
        'symptoms',
        'notes',
        'fee',
        'is_online',
        'meeting_link',
        'meeting_platform',
        'check_in_time',
        'check_out_time',
        'prescription',
        'diagnosis',
        'vital_signs',
        'follow_up_instructions',
        'next_appointment_date'
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'department_id' => 'integer',
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
        'next_appointment_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'vital_signs' => 'array',
        'fee' => 'decimal:2',
        'is_online' => 'boolean'
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('appointment_date', 'asc')
                    ->orderBy('appointment_time', 'asc');
    }

    // Accessors
    public function getAppointmentDateTimeAttribute()
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time->format('H:i:s'));
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'rescheduled' => 'secondary'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'consultation' => 'primary',
            'followup' => 'info',
            'emergency' => 'danger',
            'checkup' => 'success',
            'surgery' => 'warning'
        ];

        return $badges[$this->type] ?? 'secondary';
    }

    public function getDurationAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }

        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }

    public function getPatientNameAttribute()
    {
        return $this->patient ? $this->patient->name : 'N/A';
    }

    public function getPatientEmailAttribute()
    {
        return $this->patient ? $this->patient->email : 'N/A';
    }

    public function getFormattedMeetingLinkAttribute()
    {
        if (!$this->meeting_link) {
            return null;
        }
        return $this->meeting_link;
    }

    public function getMeetingPlatformNameAttribute()
    {
        if (!$this->meeting_platform) {
            return 'Custom';
        }
        return match($this->meeting_platform) {
            'zoom' => 'Zoom',
            'google_meet' => 'Google Meet',
            'teams' => 'Microsoft Teams',
            'whereby' => 'Whereby',
            'custom' => 'Custom Platform',
            default => ucfirst($this->meeting_platform)
        };
    }

    public function getMeetingPlatformIconAttribute()
    {
        if (!$this->meeting_platform) {
            return 'fa-video';
        }
        return match($this->meeting_platform) {
            'zoom' => 'fab fa-zoom',
            'google_meet' => 'fab fa-google',
            'teams' => 'fab fa-microsoft',
            'whereby' => 'fa-video',
            'custom' => 'fa-video',
            default => 'fa-video'
        };
    }

    // Check if meeting link can be accessed (15 mins before to 30 mins after appointment time)
    public function canJoinMeeting()
    {
        if (!$this->is_online || !$this->meeting_link) {
            return false;
        }

        if (!in_array($this->status, ['confirmed'])) {
            return false;
        }

        $appointmentDateTime = $this->appointment_date_time;
        $now = now();
        
        $startTime = $appointmentDateTime->copy()->subMinutes(15);
        $endTime = $appointmentDateTime->copy()->addMinutes(30);

        return $now->between($startTime, $endTime);
    }

    // Get countdown until meeting starts
    public function getMeetingCountdownAttribute()
    {
        if (!$this->is_online || !$this->meeting_link) {
            return null;
        }

        $appointmentDateTime = $this->appointment_date_time;
        $now = now();

        if ($now->isBefore($appointmentDateTime)) {
            $diff = $now->diff($appointmentDateTime);
            return $diff->format('%h hours, %i minutes');
        } elseif ($now->isAfter($appointmentDateTime->copy()->addMinutes(30))) {
            return 'Meeting has ended';
        } else {
            return 'Meeting in progress';
        }
    }

    // Helper methods
    public function canBeCheckedIn()
    {
        return $this->status === 'confirmed' && 
               $this->appointment_date_time->isToday() && 
               !$this->check_in_time;
    }

    public function canBeCheckedOut()
    {
        return $this->check_in_time && !$this->check_out_time;
    }

    public function isOverdue()
    {
        return $this->appointment_date_time->isPast() && 
               in_array($this->status, ['pending', 'confirmed']);
    }

    public function checkIn()
    {
        $this->update([
            'check_in_time' => now(),
            'status' => 'confirmed'
        ]);
    }

    public function checkOut()
    {
        $this->update([
            'check_out_time' => now(),
            'status' => 'completed'
        ]);
    }

    // Generate unique appointment number
    public static function generateAppointmentNumber()
    {
        do {
            $number = 'A' . date('Y') . date('m') . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        } while (static::where('appointment_number', $number)->exists());

        return $number;
    }

    // Validation rules for meeting link
    public static function getMeetingLinkValidationRules()
    {
        return [
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom',
            'is_online' => 'boolean',
        ];
    }

    // Validation: If is_online is true, meeting_link is required
    public static function validateOnlineAppointment(array $data)
    {
        if (!empty($data['is_online']) && empty($data['meeting_link'])) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], [])->errors()->add('meeting_link', 'Meeting link is required for online consultations.')
            );
        }
        return true;
    }
}
