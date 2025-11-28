<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'medical_record_id',
        'prescription_number',
        'medications',
        'status',
        'prescription_type',
        'prescription_date',
        'diagnosis',
        'notes',
        'prescribed_date',
        'expiry_date',
        'follow_up_date',
        'refills_allowed',
        'pharmacist_notes',
        'pharmacist_id',
        'created_by',
        'updated_by',
        'dispensed_at',
        'approved_at',
    ];

    protected $casts = [
        'medications' => 'array',
        'prescription_date' => 'date',
        'prescribed_date' => 'date',
        'expiry_date' => 'date',
        'follow_up_date' => 'date',
        'dispensed_at' => 'datetime',
        'approved_at' => 'datetime',
        'refills_allowed' => 'integer',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'medical_record_id' => 'integer',
        'pharmacist_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
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

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    // Generate unique prescription number
    public static function generatePrescriptionNumber(): string
    {
        do {
            $number = 'RX' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('prescription_number', $number)->exists());

        return $number;
    }

    /**
     * Scope to filter prescriptions by department.
     * A prescription belongs to a department if:
     * - The prescription's doctor belongs to that department, OR
     * - The prescription's patient belongs to that department (via doctor relationships)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            // Prescriptions from doctors in this department
            $q->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where(function($deptQuery) use ($departmentId) {
                    $deptQuery->where('department_id', $departmentId)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                 $pivotQuery->where('departments.id', $departmentId);
                             });
                });
            })
            // OR prescriptions for patients in this department
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
                    ->orWhereHas('appointments', function($appointmentQuery) use ($departmentId) {
                        $appointmentQuery->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                            $doctorQuery->where(function($deptQuery) use ($departmentId) {
                                $deptQuery->where('department_id', $departmentId)
                                         ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                             $pivotQuery->where('departments.id', $departmentId);
                                         });
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
     * Scope to filter prescriptions by multiple departments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $departmentIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartments($query, array $departmentIds)
    {
        return $query->where(function($q) use ($departmentIds) {
            $q->whereHas('doctor', function($doctorQuery) use ($departmentIds) {
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
                    ->orWhereHas('appointments', function($appointmentQuery) use ($departmentIds) {
                        $appointmentQuery->whereHas('doctor', function($doctorQuery) use ($departmentIds) {
                            $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                                $deptQuery->whereIn('department_id', $departmentIds)
                                         ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                             $pivotQuery->whereIn('departments.id', $departmentIds);
                                         });
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
     * Scope to filter prescriptions visible to a specific user based on role.
     * 
     * For Doctors:
     * - Prescriptions they created (doctor_id matches), OR
     * - Prescriptions for patients in their department(s), OR
     * - Prescriptions for patients they created
     * 
     * For Admins:
     * - All prescriptions (no filtering)
     * 
     * For other roles:
     * - Prescriptions in their department(s)
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
                    $q->orWhereHas('patient', function($patientQuery) use ($doctorDepartmentIds) {
                        $patientQuery->whereHas('departments', function($deptQuery) use ($doctorDepartmentIds) {
                            $deptQuery->whereIn('departments.id', $doctorDepartmentIds);
                        })
                        ->orWhere(function($subQuery) use ($doctorDepartmentIds) {
                            $subQuery->whereIn('department_id', $doctorDepartmentIds)
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
                $q->whereHas('doctor', function($doctorQuery) use ($userDepartmentIds) {
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
     * Check if a prescription is visible to a specific user.
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
}
