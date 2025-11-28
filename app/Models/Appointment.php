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
        return $query->where(function($q) use ($departmentId) {
            // Appointments with direct department_id match
            $q->where('department_id', $departmentId)
            // OR appointments from doctors in this department
            ->orWhereHas('doctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where(function($deptQuery) use ($departmentId) {
                    $deptQuery->where('department_id', $departmentId)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                 $pivotQuery->where('departments.id', $departmentId);
                             });
                });
            })
            // OR appointments for patients in this department
            ->orWhereHas('patient', function($patientQuery) use ($departmentId) {
                $patientQuery->where(function($p) use ($departmentId) {
                    $p->whereHas('createdByDoctor', function($doctorQuery) use ($departmentId) {
                        $doctorQuery->where(function($deptQuery) use ($departmentId) {
                            $deptQuery->where('department_id', $departmentId)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                         $pivotQuery->where('departments.id', $departmentId);
                                     });
                        });
                    })
                    ->orWhereHas('assignedDoctor', function($doctorQuery) use ($departmentId) {
                        $doctorQuery->where(function($deptQuery) use ($departmentId) {
                            $deptQuery->where('department_id', $departmentId)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                         $pivotQuery->where('departments.id', $departmentId);
                                     });
                        });
                    })
                    ->orWhereHas('departments', function($deptQuery) use ($departmentId) {
                        $deptQuery->where('departments.id', $departmentId);
                    })
                    ->orWhere('department_id', $departmentId);
                });
            });
        });
    }

    /**
     * Scope to filter appointments by multiple departments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $departmentIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartments($query, array $departmentIds)
    {
        return $query->where(function($q) use ($departmentIds) {
            $q->whereIn('department_id', $departmentIds)
            ->orWhereHas('doctor', function($doctorQuery) use ($departmentIds) {
                $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                    $deptQuery->whereIn('department_id', $departmentIds)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                 $pivotQuery->whereIn('departments.id', $departmentIds);
                             });
                });
            })
            ->orWhereHas('patient', function($patientQuery) use ($departmentIds) {
                $patientQuery->where(function($p) use ($departmentIds) {
                    $p->whereHas('createdByDoctor', function($doctorQuery) use ($departmentIds) {
                        $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                            $deptQuery->whereIn('department_id', $departmentIds)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                         $pivotQuery->whereIn('departments.id', $departmentIds);
                                     });
                        });
                    })
                    ->orWhereHas('assignedDoctor', function($doctorQuery) use ($departmentIds) {
                        $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                            $deptQuery->whereIn('department_id', $departmentIds)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                         $pivotQuery->whereIn('departments.id', $departmentIds);
                                     });
                        });
                    })
                    ->orWhereHas('departments', function($deptQuery) use ($departmentIds) {
                        $deptQuery->whereIn('departments.id', $departmentIds);
                    })
                    ->orWhereIn('department_id', $departmentIds);
                });
            });
        });
    }

    /**
     * Scope to filter appointments visible to a specific user based on role.
     * 
     * For Doctors:
     * - Appointments they have (doctor_id matches), OR
     * - Appointments for patients in their department(s), OR
     * - Appointments for patients they created
     * 
     * For Admins:
     * - All appointments (no filtering)
     * 
     * For other roles:
     * - Appointments in their department(s)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|int|null $user User model, user ID, or null (uses Auth::user())
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleTo($query, $user = null)
    {
        if ($user === null) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        if (is_int($user) || is_string($user)) {
            $user = \App\Models\User::find($user);
            if (!$user) {
                return $query->whereRaw('1 = 0');
            }
        }
        
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }
        
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
            
            if (!$doctor) {
                return $query->whereRaw('1 = 0');
            }
            
            $doctorDepartmentIds = [];
            if ($doctor->departments->isNotEmpty()) {
                $doctorDepartmentIds = $doctor->departments->pluck('id')->toArray();
            } elseif ($doctor->department_id) {
                $doctorDepartmentIds = [$doctor->department_id];
            }
            
            return $query->where(function($q) use ($doctor, $doctorDepartmentIds) {
                $q->where('doctor_id', $doctor->id);
                
                $q->orWhereHas('patient', function($patientQuery) use ($doctor) {
                    $patientQuery->where('created_by_doctor_id', $doctor->id);
                });
                
                if (!empty($doctorDepartmentIds)) {
                    $q->orWhere(function($subQuery) use ($doctorDepartmentIds) {
                        $subQuery->whereIn('department_id', $doctorDepartmentIds);
                    })
                    ->orWhereHas('patient', function($patientQuery) use ($doctorDepartmentIds) {
                        $patientQuery->whereHas('departments', function($deptQuery) use ($doctorDepartmentIds) {
                            $deptQuery->whereIn('departments.id', $doctorDepartmentIds);
                        })
                        ->orWhere(function($subQuery2) use ($doctorDepartmentIds) {
                            $subQuery2->whereIn('department_id', $doctorDepartmentIds)
                                    ->whereDoesntHave('departments');
                        });
                    });
                }
            });
        }
        
        $userDepartmentIds = [];
        $user->load('departments');
        if ($user->departments->isNotEmpty()) {
            $userDepartmentIds = $user->departments->pluck('id')->toArray();
        } elseif ($user->department_id) {
            $userDepartmentIds = [$user->department_id];
        }
        
        if (!empty($userDepartmentIds)) {
            return $query->where(function($q) use ($userDepartmentIds) {
                $q->whereIn('department_id', $userDepartmentIds)
                ->orWhereHas('doctor', function($doctorQuery) use ($userDepartmentIds) {
                    $doctorQuery->where(function($deptQuery) use ($userDepartmentIds) {
                        $deptQuery->whereIn('department_id', $userDepartmentIds)
                                 ->orWhereHas('departments', function($pivotQuery) use ($userDepartmentIds) {
                                     $pivotQuery->whereIn('departments.id', $userDepartmentIds);
                                 });
                    });
                })
                ->orWhereHas('patient', function($patientQuery) use ($userDepartmentIds) {
                    $patientQuery->whereHas('departments', function($deptQuery) use ($userDepartmentIds) {
                        $deptQuery->whereIn('departments.id', $userDepartmentIds);
                    })
                    ->orWhere(function($subQuery) use ($userDepartmentIds) {
                        $subQuery->whereIn('department_id', $userDepartmentIds)
                                ->whereDoesntHave('departments');
                    });
                });
            });
        }
        
        return $query->whereRaw('1 = 0');
    }
    
    /**
     * Check if an appointment is visible to a specific user.
     * 
     * @param \App\Models\User|int|null $user User model, user ID, or null (uses Auth::user())
     * @return bool
     */
    public function isVisibleTo($user = null)
    {
        if ($user === null) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        
        if (!$user) {
            return false;
        }
        
        if (is_int($user) || is_string($user)) {
            $user = \App\Models\User::find($user);
            if (!$user) {
                return false;
            }
        }
        
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
            
            if (!$doctor) {
                return false;
            }
            
            if ($this->doctor_id === $doctor->id) {
                return true;
            }
            
            if ($this->patient && $this->patient->created_by_doctor_id === $doctor->id) {
                return true;
            }
            
            $doctorDepartmentIds = [];
            if ($doctor->departments->isNotEmpty()) {
                $doctorDepartmentIds = $doctor->departments->pluck('id')->toArray();
            } elseif ($doctor->department_id) {
                $doctorDepartmentIds = [$doctor->department_id];
            }
            
            if (empty($doctorDepartmentIds)) {
                return false;
            }
            
            if (in_array($this->department_id, $doctorDepartmentIds)) {
                return true;
            }
            
            if ($this->patient) {
                $patientDepartmentIds = $this->patient->getDepartmentIds();
                $intersection = array_intersect($doctorDepartmentIds, $patientDepartmentIds);
                return !empty($intersection);
            }
            
            return false;
        }
        
        $user->load('departments');
        $userDepartmentIds = [];
        if ($user->departments->isNotEmpty()) {
            $userDepartmentIds = $user->departments->pluck('id')->toArray();
        } elseif ($user->department_id) {
            $userDepartmentIds = [$user->department_id];
        }
        
        if (empty($userDepartmentIds)) {
            return false;
        }
        
        if (in_array($this->department_id, $userDepartmentIds)) {
            return true;
        }
        
        if ($this->doctor) {
            $doctorDepartmentIds = [];
            if ($this->doctor->departments->isNotEmpty()) {
                $doctorDepartmentIds = $this->doctor->departments->pluck('id')->toArray();
            } elseif ($this->doctor->department_id) {
                $doctorDepartmentIds = [$this->doctor->department_id];
            }
            
            if (!empty($doctorDepartmentIds)) {
                $intersection = array_intersect($userDepartmentIds, $doctorDepartmentIds);
                if (!empty($intersection)) {
                    return true;
                }
            }
        }
        
        if ($this->patient) {
            $patientDepartmentIds = $this->patient->getDepartmentIds();
            if (!empty($patientDepartmentIds)) {
                $intersection = array_intersect($userDepartmentIds, $patientDepartmentIds);
                if (!empty($intersection)) {
                    return true;
                }
            }
        }
        
        return false;
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
