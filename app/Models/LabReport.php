<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'medical_record_id',
        'report_number',
        'test_type',
        'test_category',
        'test_name',
        'test_date',
        'specimen_type',
        'collection_date',
        'report_date',
        'results',
        'normal_range',
        'reference_range',
        'reference_values',
        'interpretation',
        'status',
        'priority',
        'lab_technician',
        'technician_name',
        'technician_notes',
        'notes',
        'report_file',
        'file_path',
        'technician_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'collection_date' => 'date',
        'report_date' => 'date',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'medical_record_id' => 'integer',
        'technician_id' => 'integer',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByTestType($query, $type)
    {
        return $query->where('test_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Generate unique report number
    public static function generateReportNumber(): string
    {
        do {
            $number = 'LAB' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('report_number', $number)->exists());

        return $number;
    }

    /**
     * Scope to filter lab reports by department.
     * A lab report belongs to a department if:
     * - The report's doctor belongs to that department, OR
     * - The report's patient belongs to that department (via doctor relationships)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            $q->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where(function($deptQuery) use ($departmentId) {
                    $deptQuery->where('department_id', $departmentId)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                 $pivotQuery->where('departments.id', $departmentId);
                             });
                });
            })
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
     * Scope to filter lab reports by multiple departments.
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
     * Scope to filter lab reports visible to a specific user based on role.
     * 
     * For Doctors:
     * - Lab reports they created (doctor_id matches), OR
     * - Lab reports for patients in their department(s), OR
     * - Lab reports for patients they created
     * 
     * For Admins:
     * - All lab reports (no filtering)
     * 
     * For other roles:
     * - Lab reports in their department(s)
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
     * Check if a lab report is visible to a specific user.
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
