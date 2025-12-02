<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\PatientResetPasswordNotification;

class Patient extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'patient_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'date_of_birth',
        'gender',
        'blood_group',
        'emergency_contact',
        'emergency_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'insurance_provider',
        'insurance_number',
        'allergies',
        'medical_conditions',
        'notes',
        'photo',
        'patient_id_document_path',
        'guardian_id_document_path',
        'consent_share_with_gp',
        'gp_name',
        'gp_email',
        'gp_phone',
        'gp_address',
        'is_active',
        'is_guest',
        'department_id',
        'created_by_doctor_id',
        'assigned_doctor_id',
        'email_verified_at',
        'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'allergies' => 'array',
        'medical_conditions' => 'array',
        'is_active' => 'boolean',
        'is_guest' => 'boolean',
        'consent_share_with_gp' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labReports(): HasMany
    {
        return $this->hasMany(LabReport::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Many-to-many relationship with departments (current implementation)
     */
    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_patient')
            ->withPivot('is_primary')
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc');
    }
    
    /**
     * Legacy relationship for backward compatibility (single department)
     */
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    /**
     * Get primary department from pivot table or fallback to department_id
     */
    public function primaryDepartment()
    {
        return $this->departments()->wherePivot('is_primary', true)->first() 
            ?? $this->departments()->first() 
            ?? $this->department; // Fallback to old department_id
    }
    
    /**
     * Get all department IDs for this patient (from both pivot table and legacy department_id)
     */
    public function getDepartmentIds(): array
    {
        $departmentIds = [];
        
        // Get from many-to-many relationship
        if ($this->relationLoaded('departments')) {
            $departmentIds = $this->departments->pluck('id')->toArray();
        } elseif ($this->departments()->exists()) {
            // Load the relationship if it exists but isn't loaded
            $this->load('departments');
            $departmentIds = $this->departments->pluck('id')->toArray();
        }
        
        // Fallback to legacy department_id if no pivot records exist
        if (empty($departmentIds) && $this->department_id) {
            $departmentIds = [$this->department_id];
        }
        
        return array_unique($departmentIds);
    }

    public function createdByDoctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'created_by_doctor_id');
    }

    public function assignedDoctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PatientNotification::class);
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(PatientNotification::class)->where('is_read', false);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(PatientAlert::class);
    }

    public function activeAlerts(): HasMany
    {
        return $this->hasMany(PatientAlert::class)->active();
    }

    /**
     * Get the patient documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeGuest($query)
    {
        return $query->where('is_guest', true);
    }

    public function scopeNotGuest($query)
    {
        return $query->where('is_guest', false);
    }

    public function scopeByBloodGroup($query, $bloodGroup)
    {
        return $query->where('blood_group', $bloodGroup);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to filter patients by department.
     * Shows patients who belong to the specified department.
     * A patient belongs to a department if:
     * - They were created by a doctor in this department, OR
     * - They are assigned to a doctor in this department, OR
     * - They have appointments/records with doctors in this department
     * 
     * Note: Patients are NOT directly assigned to departments - they're linked through doctors.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            // Patients created by a doctor in this department (support both old and new relationships)
            $q->whereHas('createdByDoctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where(function($deptQuery) use ($departmentId) {
                    $deptQuery->where('department_id', $departmentId)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                 $pivotQuery->where('departments.id', $departmentId);
                             });
                });
            })
            // OR patients assigned to a doctor in this department (support both old and new relationships)
            ->orWhereHas('assignedDoctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where(function($deptQuery) use ($departmentId) {
                    $deptQuery->where('department_id', $departmentId)
                             ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                 $pivotQuery->where('departments.id', $departmentId);
                             });
                });
            })
            // OR patients with appointments to doctors in this department (support both old and new relationships)
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
            // OR patients with medical records from doctors in this department (support both old and new relationships)
            ->orWhereHas('medicalRecords', function($recordQuery) use ($departmentId) {
                $recordQuery->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                    $doctorQuery->where(function($deptQuery) use ($departmentId) {
                        $deptQuery->where('department_id', $departmentId)
                                 ->orWhereHas('departments', function($pivotQuery) use ($departmentId) {
                                     $pivotQuery->where('departments.id', $departmentId);
                                 });
                    });
                });
            });
        });
    }

    /**
     * Scope to filter patients by multiple departments.
     * Shows patients who belong to any of the specified departments.
     * A patient belongs to a department if:
     * - They were created by a doctor in that department, OR
     * - They are assigned to a doctor in that department, OR
     * - They have appointments/records with doctors in that department
     * 
     * Note: Patients are NOT directly assigned to departments - they're linked through doctors.
     * Focus: Only patients with doctor relationships are visible - no fallback to patient.department_id.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $departmentIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDepartments($query, array $departmentIds)
    {
        return $query->where(function($q) use ($departmentIds) {
            // Patients created by a doctor in any of these departments
            $q->whereHas('createdByDoctor', function($doctorQuery) use ($departmentIds) {
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
            // OR patients with medical records from doctors in any of these departments
            ->orWhereHas('medicalRecords', function($recordQuery) use ($departmentIds) {
                $recordQuery->whereHas('doctor', function($doctorQuery) use ($departmentIds) {
                    $doctorQuery->where(function($deptQuery) use ($departmentIds) {
                        $deptQuery->whereIn('department_id', $departmentIds)
                                 ->orWhereHas('departments', function($pivotQuery) use ($departmentIds) {
                                     $pivotQuery->whereIn('departments.id', $departmentIds);
                                 });
                    });
                });
            });
        });
    }

    /**
     * Scope to filter patients visible to a specific user based on role.
     * 
     * For Doctors:
     * - Patients whose department_id matches one of the doctor's departments, OR
     * - Patients that were created by that doctor (created_by_doctor_id)
     * 
     * For Admins:
     * - All patients (no filtering)
     * 
     * For other roles:
     * - Patients in their department(s)
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
        
        // Admins can see all patients
        if ($user->is_admin || $user->role === 'admin') {
            return $query; // No filtering
        }
        
        // For doctors, filter by department intersection and created_by
        // Doctors should see:
        // 1. Patients they added themselves (regardless of department)
        // 2. Patients in their department (including those added by other doctors in the same department)
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
                // Patients that were created by this doctor (regardless of department)
                $q->where('created_by_doctor_id', $doctor->id);
                
                // OR patients whose departments intersect with the doctor's departments
                // This includes patients added by other doctors IF they share at least one department
                if (!empty($doctorDepartmentIds)) {
                    // Priority: Check many-to-many relationship first (current implementation)
                    // This ensures we use the most up-to-date department assignments
                    $q->orWhere(function($subQuery) use ($doctorDepartmentIds) {
                        // Check via pivot table - patient must have at least one department matching doctor's departments
                        $subQuery->whereHas('departments', function($deptQuery) use ($doctorDepartmentIds) {
                            $deptQuery->whereIn('departments.id', $doctorDepartmentIds);
                        });
                    })
                    // Fallback to legacy department_id field ONLY if no pivot records exist
                    // This ensures backward compatibility for old records without pivot entries
                    ->orWhere(function($subQuery) use ($doctorDepartmentIds) {
                        $subQuery->whereNotNull('department_id')
                                ->whereIn('department_id', $doctorDepartmentIds)
                                // Only use department_id if patient has no departments in pivot table
                                ->whereDoesntHave('departments');
                    });
                }
                
                // OR patients with appointments to doctors in the doctor's departments
                // This ensures guest patients from public bookings appear in the list
                if (!empty($doctorDepartmentIds)) {
                    $q->orWhereHas('appointments', function($appointmentQuery) use ($doctorDepartmentIds) {
                        $appointmentQuery->whereHas('doctor', function($doctorQuery) use ($doctorDepartmentIds) {
                            $doctorQuery->where(function($deptQuery) use ($doctorDepartmentIds) {
                                $deptQuery->whereIn('department_id', $doctorDepartmentIds)
                                         ->orWhereHas('departments', function($pivotQuery) use ($doctorDepartmentIds) {
                                             $pivotQuery->whereIn('departments.id', $doctorDepartmentIds);
                                         });
                            });
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
                // Priority: Check many-to-many relationship first (current implementation)
                // This ensures we use the most up-to-date department assignments
                $q->whereHas('departments', function($deptQuery) use ($userDepartmentIds) {
                    $deptQuery->whereIn('departments.id', $userDepartmentIds);
                })
                // Fallback to legacy department_id field ONLY if no pivot records exist
                // This ensures backward compatibility for old records without pivot entries
                ->orWhere(function($subQuery) use ($userDepartmentIds) {
                    $subQuery->whereIn('department_id', $userDepartmentIds)
                            // Only use department_id if patient has no departments in pivot table
                            ->whereDoesntHave('departments');
                })
                // OR patients with appointments to doctors in the user's departments
                // This ensures guest patients from public bookings appear in the list
                ->orWhereHas('appointments', function($appointmentQuery) use ($userDepartmentIds) {
                    $appointmentQuery->whereHas('doctor', function($doctorQuery) use ($userDepartmentIds) {
                        $doctorQuery->where(function($deptQuery) use ($userDepartmentIds) {
                            $deptQuery->whereIn('department_id', $userDepartmentIds)
                                     ->orWhereHas('departments', function($pivotQuery) use ($userDepartmentIds) {
                                         $pivotQuery->whereIn('departments.id', $userDepartmentIds);
                                     });
                        });
                    });
                });
            });
        }
        
        // No departments assigned, return empty result
        return $query->whereRaw('1 = 0');
    }
    
    /**
     * Check if a patient is visible to a specific user.
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
        
        // If still no user, patient is not visible
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
        
        // Admins can see all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // For doctors, check department intersection and created_by
        // Doctors should see:
        // 1. Patients they added themselves (regardless of department)
        // 2. Patients in their department (including those added by other doctors in the same department)
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
            
            if (!$doctor) {
                return false;
            }
            
            // Check if patient was created by this doctor (regardless of department)
            if ($this->created_by_doctor_id === $doctor->id) {
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
                // If doctor has no departments, only show patients they created
                return false;
            }
            
            // Check if patient's departments intersect with doctor's departments
            // Patient must have AT LEAST ONE department in common with doctor's departments
            // Using many-to-many relationship (current implementation)
            $hasMatchingDepartment = $this->departments()
                ->whereIn('departments.id', $doctorDepartmentIds)
                ->exists();
            
            if ($hasMatchingDepartment) {
                return true;
            }
            
            // OR fallback to legacy department_id field (for backward compatibility)
            // Only check if patient's department_id matches one of doctor's departments
            if ($this->department_id && in_array($this->department_id, $doctorDepartmentIds)) {
                return true;
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
        
        // Get patient's department IDs (from both pivot table and legacy department_id)
        $patientDepartmentIds = $this->getDepartmentIds();
        
        // Check if there's any intersection between user's and patient's departments
        if (!empty($patientDepartmentIds)) {
            $intersection = array_intersect($userDepartmentIds, $patientDepartmentIds);
            return !empty($intersection);
        }
        
        return false;
    }

    /**
     * Scope to filter patients visible to a specific doctor.
     * Shows patients where:
     * - Patient is in the same department as the doctor, AND
     * - Either created by this doctor, assigned to this doctor, or has appointments/records with this doctor
     * 
     * This ensures doctors can see:
     * 1. Patients they created (in their department)
     * 2. Patients assigned to them (in their department)
     * 3. Patients with appointments/records with them (in their department)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $doctorId
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Builder
     * @deprecated Use scopeVisibleTo instead
     */
    public function scopeVisibleToDoctor($query, $doctorId, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            // Patient's department matches
            $q->where('department_id', $departmentId)
            // OR patient was created by a doctor in this department
            ->orWhereHas('createdByDoctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where('department_id', $departmentId);
            })
            // OR patient is assigned to a doctor in this department
            ->orWhereHas('assignedDoctor', function($doctorQuery) use ($departmentId) {
                $doctorQuery->where('department_id', $departmentId);
            })
            // OR patient has appointments with doctors in this department
            ->orWhereHas('appointments', function($appointmentQuery) use ($departmentId) {
                $appointmentQuery->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                    $doctorQuery->where('department_id', $departmentId);
                });
            })
            // OR patient has medical records with doctors in this department
            ->orWhereHas('medicalRecords', function($recordQuery) use ($departmentId) {
                $recordQuery->whereHas('doctor', function($doctorQuery) use ($departmentId) {
                    $doctorQuery->where('department_id', $departmentId);
                });
            });
        })
        ->where(function($q) use ($doctorId) {
            // Patients created by this doctor
            $q->where('created_by_doctor_id', $doctorId)
            // OR patients assigned to this doctor
            ->orWhere('assigned_doctor_id', $doctorId)
            // OR patients with appointments with this doctor
            ->orWhereHas('appointments', function($appointmentQuery) use ($doctorId) {
                $appointmentQuery->where('doctor_id', $doctorId);
            })
            // OR patients with medical records from this doctor
            ->orWhereHas('medicalRecords', function($recordQuery) use ($doctorId) {
                $recordQuery->where('doctor_id', $doctorId);
            });
        });
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : null;
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Clean the photo path
            $photoPath = ltrim($this->photo, '/');
            
            // Check if it's an uploaded file stored in storage/app/public/patients
            if (Storage::disk('public')->exists('patients/' . $photoPath)) {
                return Storage::disk('public')->url('patients/' . $photoPath);
            }
            
            // Alternative path for uploads/patients
            if (Storage::disk('public')->exists('uploads/patients/' . $photoPath)) {
                return Storage::disk('public')->url('uploads/patients/' . $photoPath);
            }
            
            // Fallback to asset for static images
            return asset('assets/images/patients/' . $photoPath);
        }
        
        return asset('assets/images/default-patient.jpg');
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    // Helper methods
    public function getTotalAppointmentsAttribute()
    {
        return $this->appointments()->count();
    }

    public function getLastAppointmentAttribute()
    {
        return $this->appointments()
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->first();
    }

    public function getUpcomingAppointmentsAttribute()
    {
        return $this->appointments()
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
    }

    public function hasAllergy($allergy)
    {
        return in_array($allergy, $this->allergies ?? []);
    }

    public function hasMedicalCondition($condition)
    {
        return in_array($condition, $this->medical_conditions ?? []);
    }

    // Generate unique patient ID
    public static function generatePatientId()
    {
        do {
            $id = 'P' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::where('patient_id', $id)->exists());

        return $id;
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PatientResetPasswordNotification($token));
    }

    /**
     * Get the email address for password reset.
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Convert guest patient to full patient.
     * 
     * @param array $additionalData Additional required fields (DOB, address, gender, etc.)
     * @return bool
     */
    public function convertToFullPatient(array $additionalData = [])
    {
        if (!$this->is_guest) {
            return false; // Already a full patient
        }

        $this->fill($additionalData);
        $this->is_guest = false;
        return $this->save();
    }

    /**
     * Check if patient can perform certain actions (not a guest).
     */
    public function canPerformActions()
    {
        return !$this->is_guest;
    }
}
