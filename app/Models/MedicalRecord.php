<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'record_type',
        'record_date',
        'presenting_complaint', // PC
        'history_of_presenting_complaint', // HPC
        'past_medical_history', // PMH
        'drug_history', // DH
        'allergies', // Allergies
        'social_history', // SH
        'family_history', // FH
        'ideas_concerns_expectations', // ICE
        'plan', // Plan
        'diagnosis',
        'symptoms',
        'treatment',
        'notes',
        'vital_signs',
        'follow_up_date',
        'is_private',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'vital_signs' => 'array',
        'record_date' => 'date',
        'follow_up_date' => 'date',
        'is_private' => 'boolean',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labReports(): HasMany
    {
        return $this->hasMany(LabReport::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MedicalRecordAttachment::class);
    }

    // Scopes
    public function scopeByRecordType($query, $type)
    {
        return $query->where('record_type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Scope to filter medical records by department.
     * A medical record belongs to a department if:
     * - The record's doctor belongs to that department, OR
     * - The record's patient belongs to that department (via doctor relationships)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            // Medical records from doctors in this department
            $q->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where(function($deptQuery) use ($departmentId) {
                    // Support both old department_id and new pivot table
                    $deptQuery->where('department_id', $departmentId)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                 $pivotQuery->where('departments.id', $departmentId);
                             });
                });
            })
            // OR medical records for patients in this department
            ->orWhereHas('patient', function($patientQuery) use ($departmentId) {
                $patientQuery->where(function($p) use ($departmentId) {
                    // Patients created by a doctor in this department
                    $p->whereHas('createdByDoctor', function($doctorQuery) use ($departmentId) {
                        $doctorQuery->where(function($deptQuery) use ($departmentId) {
                            $deptQuery->where('department_id', $departmentId)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                         $pivotQuery->where('departments.id', $departmentId);
                                     });
                        });
                    })
                    // OR patients assigned to a doctor in this department
                    ->orWhereHas('assignedDoctor', function($doctorQuery) use ($departmentId) {
                        $doctorQuery->where(function($deptQuery) use ($departmentId) {
                            $deptQuery->where('department_id', $departmentId)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                         $pivotQuery->where('departments.id', $departmentId);
                                     });
                        });
                    })
                    // OR patients with appointments to doctors in this department
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
                    // OR patients with departments in pivot table
                    ->orWhereHas('departments', function($deptQuery) use ($departmentId) {
                        $deptQuery->where('departments.id', $departmentId);
                    })
                    // OR legacy department_id field (fallback)
                    ->orWhere('department_id', $departmentId);
                });
            });
        });
    }

    /**
     * Scope to filter medical records by multiple departments.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $departmentIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartments($query, array $departmentIds)
    {
        return $query->where(function($q) use ($departmentIds) {
            // Medical records from doctors in any of these departments
            $q->whereHas('doctor', function($doctorQuery) use ($departmentIds) {
                $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                    $deptQuery->whereIn('department_id', $departmentIds)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                 $pivotQuery->whereIn('departments.id', $departmentIds);
                             });
                });
            })
            // OR medical records for patients in any of these departments
            ->orWhereHas('patient', function($patientQuery) use ($departmentIds) {
                $patientQuery->where(function($p) use ($departmentIds) {
                    // Patients created by a doctor in any of these departments
                    $p->whereHas('createdByDoctor', function($doctorQuery) use ($departmentIds) {
                        $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                            $deptQuery->whereIn('department_id', $departmentIds)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                         $pivotQuery->whereIn('departments.id', $departmentIds);
                                     });
                        });
                    })
                    // OR patients assigned to a doctor in any of these departments
                    ->orWhereHas('assignedDoctor', function($doctorQuery) use ($departmentIds) {
                        $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                            $deptQuery->whereIn('department_id', $departmentIds)
                                     ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                         $pivotQuery->whereIn('departments.id', $departmentIds);
                                     });
                        });
                    })
                    // OR patients with appointments to doctors in any of these departments
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
                    // OR patients with departments in pivot table
                    ->orWhereHas('departments', function($deptQuery) use ($departmentIds) {
                        $deptQuery->whereIn('departments.id', $departmentIds);
                    })
                    // OR legacy department_id field (fallback)
                    ->orWhereIn('department_id', $departmentIds);
                });
            });
        });
    }

    /**
     * Scope to filter medical records visible to a specific user based on role.
     * 
     * For Doctors:
     * - Medical records they created (doctor_id matches), OR
     * - Medical records for patients in their department(s), OR
     * - Medical records for patients they created
     * 
     * For Admins:
     * - All medical records (no filtering)
     * 
     * For other roles:
     * - Medical records in their department(s)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User|int|null $user User model, user ID, or null (uses Auth::user())
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisibleTo($query, $user = null)
    {
        // If no user provided, try to get from auth
        if ($user === null) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        
        // If still no user, return empty result
        if (!$user) {
            return $query->whereRaw('1 = 0'); // No results
        }
        
        // Convert user ID to User model if needed
        if (is_int($user) || is_string($user)) {
            $user = \App\Models\User::find($user);
            if (!$user) {
                return $query->whereRaw('1 = 0'); // No results
            }
        }
        
        // Admins can see all medical records
        if ($user->is_admin || $user->role === 'admin') {
            return $query; // No filtering
        }
        
        // For doctors, filter by doctor relationship and department intersection
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
            
            if (!$doctor) {
                return $query->whereRaw('1 = 0'); // No results if doctor not found
            }
            
            // Get doctor's department IDs (support both pivot table and legacy department_id)
            $doctorDepartmentIds = [];
            if ($doctor->departments->isNotEmpty()) {
                $doctorDepartmentIds = $doctor->departments->pluck('id')->toArray();
            } elseif ($doctor->department_id) {
                $doctorDepartmentIds = [$doctor->department_id];
            }
            
            return $query->where(function($q) use ($doctor, $doctorDepartmentIds) {
                // Medical records created by this doctor
                $q->where('doctor_id', $doctor->id);
                
                // OR medical records for patients created by this doctor (regardless of department)
                $q->orWhereHas('patient', function($patientQuery) use ($doctor) {
                    $patientQuery->where('created_by_doctor_id', $doctor->id);
                });
                
                // OR medical records for patients in doctor's department(s)
                if (!empty($doctorDepartmentIds)) {
                    $q->orWhereHas('patient', function($patientQuery) use ($doctorDepartmentIds) {
                        // Priority: Check many-to-many relationship first
                        $patientQuery->whereHas('departments', function($deptQuery) use ($doctorDepartmentIds) {
                            $deptQuery->whereIn('departments.id', $doctorDepartmentIds);
                        })
                        // Fallback to legacy department_id field ONLY if no pivot records exist
                        ->orWhere(function($subQuery) use ($doctorDepartmentIds) {
                            $subQuery->whereIn('department_id', $doctorDepartmentIds)
                                    ->whereDoesntHave('departments');
                        });
                    });
                }
            });
        }
        
        // For other staff roles, filter by department intersection
        $userDepartmentIds = [];
        $user->load('departments');
        if ($user->departments->isNotEmpty()) {
            $userDepartmentIds = $user->departments->pluck('id')->toArray();
        } elseif ($user->department_id) {
            $userDepartmentIds = [$user->department_id];
        }
        
        if (!empty($userDepartmentIds)) {
            return $query->where(function($q) use ($userDepartmentIds) {
                // Medical records from doctors in user's department(s)
                $q->whereHas('doctor', function($doctorQuery) use ($userDepartmentIds) {
                    $doctorQuery->where(function($deptQuery) use ($userDepartmentIds) {
                        $deptQuery->whereIn('department_id', $userDepartmentIds)
                                 ->orWhereHas('departments', function($pivotQuery) use ($userDepartmentIds) {
                                     $pivotQuery->whereIn('departments.id', $userDepartmentIds);
                                 });
                    });
                })
                // OR medical records for patients in user's department(s)
                ->orWhereHas('patient', function($patientQuery) use ($userDepartmentIds) {
                    // Priority: Check many-to-many relationship first
                    $patientQuery->whereHas('departments', function($deptQuery) use ($userDepartmentIds) {
                        $deptQuery->whereIn('departments.id', $userDepartmentIds);
                    })
                    // Fallback to legacy department_id field ONLY if no pivot records exist
                    ->orWhere(function($subQuery) use ($userDepartmentIds) {
                        $subQuery->whereIn('department_id', $userDepartmentIds)
                                ->whereDoesntHave('departments');
                    });
                });
            });
        }
        
        // No departments assigned, return empty result
        return $query->whereRaw('1 = 0');
    }
    
    /**
     * Check if a medical record is visible to a specific user.
     * 
     * @param \App\Models\User|int|null $user User model, user ID, or null (uses Auth::user())
     * @return bool
     */
    public function isVisibleTo($user = null)
    {
        // If no user provided, try to get from auth
        if ($user === null) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        
        // If still no user, medical record is not visible
        if (!$user) {
            return false;
        }
        
        // Convert user ID to User model if needed
        if (is_int($user) || is_string($user)) {
            $user = \App\Models\User::find($user);
            if (!$user) {
                return false;
            }
        }
        
        // Admins can see all medical records
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // For doctors, check doctor relationship and department intersection
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
            
            if (!$doctor) {
                return false;
            }
            
            // Check if medical record was created by this doctor
            if ($this->doctor_id === $doctor->id) {
                return true;
            }
            
            // Check if medical record is for a patient created by this doctor
            if ($this->patient && $this->patient->created_by_doctor_id === $doctor->id) {
                return true;
            }
            
            // Get doctor's department IDs
            $doctorDepartmentIds = [];
            if ($doctor->departments->isNotEmpty()) {
                $doctorDepartmentIds = $doctor->departments->pluck('id')->toArray();
            } elseif ($doctor->department_id) {
                $doctorDepartmentIds = [$doctor->department_id];
            }
            
            if (empty($doctorDepartmentIds)) {
                return false;
            }
            
            // Check if patient's departments intersect with doctor's departments
            if ($this->patient) {
                $patientDepartmentIds = $this->patient->getDepartmentIds();
                $intersection = array_intersect($doctorDepartmentIds, $patientDepartmentIds);
                return !empty($intersection);
            }
            
            return false;
        }
        
        // For other staff roles, check department intersection
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
        
        // Check if doctor's departments intersect with user's departments
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
        
        // Check if patient's departments intersect with user's departments
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
