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
use App\Casts\SafeEncrypted;

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
        'guardian_name',
        'guardian_phone',
        'consent_share_with_gp',
        'gp_name',
        'gp_email',
        'gp_phone',
        'gp_address',
        'is_active',
        'is_guest',
        'department_id',
        'contact_group_id',
        'created_by_doctor_id',
        'assigned_doctor_id',
        'email_verified_at',
        'remember_token',
        'notification_preferences',
        'push_tokens',
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
        'notification_preferences' => 'array',
        'push_tokens' => 'array',
        // PHI/PII Encryption (HIPAA Compliance)
        // Use SafeEncrypted to avoid runtime crashes when legacy/plaintext values exist.
        'insurance_number' => SafeEncrypted::class,
        'emergency_contact' => SafeEncrypted::class,
        'emergency_phone' => SafeEncrypted::class,
        'guardian_name' => SafeEncrypted::class,
        'guardian_phone' => SafeEncrypted::class,
        'notes' => SafeEncrypted::class, // May contain sensitive medical information
    ];

    /**
     * Override getAttribute to catch any decryption exceptions that might escape from casts
     */
    public function getAttribute($key)
    {
        try {
            return parent::getAttribute($key);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails, try to return the raw attribute value
            if (array_key_exists($key, $this->attributes)) {
                \Log::warning('Patient: DecryptException caught for attribute', [
                    'key' => $key,
                    'patient_id' => $this->id ?? null,
                    'error' => $e->getMessage()
                ]);
                return $this->attributes[$key];
            }
            throw $e;
        }
    }

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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
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

    public function contactGroup(): BelongsTo
    {
        return $this->belongsTo(PatientContactGroup::class, 'contact_group_id');
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

    /**
     * UK-oriented core vs recommended demographics (see config/uk_ehr.php).
     * Core gaps block treating the record as complete (e.g. clearing is_guest).
     * Recommended gaps are shown in UI but do not block unlock.
     */
    public function hasIncompleteInformation(): array
    {
        $coreLabels = config('uk_ehr.core_labels', []);
        $recommendedLabels = config('uk_ehr.recommended_labels', []);

        $missingCore = [];
        $missingRecommended = [];

        $hasPlaceholderName = strtolower(trim((string) $this->first_name)) === 'guest'
            && strtolower(trim((string) $this->last_name)) === 'patient';
        $hasPlaceholderEmail = $this->email && strpos((string) $this->email, '@payment-link.temp') !== false;

        if ($hasPlaceholderName) {
            $missingCore[] = $coreLabels['placeholder_name'] ?? 'Patient name — replace placeholder';
        }
        if ($hasPlaceholderEmail) {
            $missingCore[] = $coreLabels['placeholder_email'] ?? 'Email — replace temporary booking address';
        } else {
            $email = $this->email ? trim((string) $this->email) : '';
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $missingCore[] = $coreLabels['email_invalid'] ?? 'Valid email address';
            }
        }

        if (! $this->date_of_birth) {
            $missingCore[] = $coreLabels['date_of_birth'] ?? 'Date of birth';
        }
        if (! $this->gender) {
            $missingCore[] = $coreLabels['gender'] ?? 'Gender';
        }
        if (! $this->phone || trim((string) $this->phone) === '' || $this->phone === 'Not specified') {
            $missingCore[] = $coreLabels['phone'] ?? 'Phone number';
        }
        if (! $this->address || trim((string) $this->address) === '') {
            $missingCore[] = $coreLabels['address'] ?? 'Address';
        }

        $emergencyContact = $this->decryptedRawField('emergency_contact');
        $emergencyPhone = $this->decryptedRawField('emergency_phone');
        if ($emergencyContact === null || $emergencyContact === '') {
            $missingRecommended[] = $recommendedLabels['emergency_contact'] ?? 'Emergency contact name';
        }
        if ($emergencyPhone === null || $emergencyPhone === '') {
            $missingRecommended[] = $recommendedLabels['emergency_phone'] ?? 'Emergency contact phone';
        }

        $isIncomplete = count($missingCore) > 0;

        return [
            'is_incomplete' => $isIncomplete,
            'missing_fields' => $missingCore,
            'missing_count' => count($missingCore),
            'recommended_missing_fields' => $missingRecommended,
            'recommended_missing_count' => count($missingRecommended),
            'has_recommended_gaps' => count($missingRecommended) > 0,
            'has_placeholder_info' => $hasPlaceholderName || $hasPlaceholderEmail,
        ];
    }

    /**
     * Read encrypted or plaintext column value safely (used for completeness checks).
     */
    private function decryptedRawField(string $attributeKey): ?string
    {
        $raw = $this->getAttributes()[$attributeKey] ?? null;
        if (! $raw) {
            return null;
        }
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($raw);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return is_string($raw) && trim($raw) !== '' ? $raw : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Check if patient has placeholder information that needs to be fixed
     * (default booking first/last "Guest" / "Patient", or email contains "@payment-link.temp")
     */
    public function hasPlaceholderInformation(): bool
    {
        $hasPlaceholderName = (strtolower(trim($this->first_name)) === 'guest' && 
                              strtolower(trim($this->last_name)) === 'patient');
        $hasPlaceholderEmail = $this->email && strpos($this->email, '@payment-link.temp') !== false;
        
        return $hasPlaceholderName || $hasPlaceholderEmail;
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
            })
            // OR patients directly assigned to this clinic/department
            ->orWhere(function ($subQuery) use ($departmentId) {
                $subQuery->where('department_id', $departmentId)
                    ->whereDoesntHave('departments');
            })
            ->orWhereHas('departments', function ($deptQuery) use ($departmentId) {
                $deptQuery->where('departments.id', $departmentId);
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
            })
            // OR patients directly assigned to any of these clinics/departments
            ->orWhere(function ($subQuery) use ($departmentIds) {
                $subQuery->whereIn('department_id', $departmentIds)
                    ->whereDoesntHave('departments');
            })
            ->orWhereHas('departments', function ($deptQuery) use ($departmentIds) {
                $deptQuery->whereIn('departments.id', $departmentIds);
            });
        });
    }

    /**
     * Scope to filter patients visible to a specific user based on role.
     * 
     * For Doctors:
     * - Patients they created, are assigned to them, or have appointments/records with them
     * - Not clinic-wide: removing a doctor from an appointment removes patient visibility unless they added the patient
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
        
        // For doctors: direct relationship only (not all patients in the same clinic/department).
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();

            if (!$doctor) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function ($q) use ($doctor) {
                $q->where('created_by_doctor_id', $doctor->id)
                    ->orWhere('assigned_doctor_id', $doctor->id)
                    ->orWhereHas('appointments', fn ($aq) => $aq->where('doctor_id', $doctor->id))
                    ->orWhereHas('medicalRecords', fn ($rq) => $rq->where('doctor_id', $doctor->id));
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
        
        // For doctors: direct relationship only (not clinic-wide department overlap).
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();

            if (!$doctor) {
                return false;
            }

            if ($this->created_by_doctor_id === $doctor->id) {
                return true;
            }

            if ($this->assigned_doctor_id === $doctor->id) {
                return true;
            }

            if ($this->appointments()->where('doctor_id', $doctor->id)->exists()) {
                return true;
            }

            if ($this->medicalRecords()->where('doctor_id', $doctor->id)->exists()) {
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

    /**
     * Resolve patient by email for imports / lookups when duplicate emails are allowed.
     *
     * @param  string|null  $patientReference  Human-readable patient_id or numeric database id
     * @throws \RuntimeException when multiple rows share the email and reference is missing
     */
    public static function findByEmailOptionalReference(string $email, ?string $patientReference = null): ?self
    {
        $q = static::query()->where('email', $email);
        if ($patientReference !== null && trim($patientReference) !== '') {
            $ref = trim($patientReference);

            return $q->where(function ($qq) use ($ref) {
                $qq->where('patient_id', $ref);
                if (ctype_digit((string) $ref)) {
                    $qq->orWhere('id', (int) $ref);
                }
            })->first();
        }

        $count = (clone $q)->count();
        if ($count > 1) {
            throw new \RuntimeException(
                'Multiple patient records use this email. Provide patient_reference (Patient ID or numeric id).'
            );
        }

        return $q->first();
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
     * Clear guest flag when demographics are complete (e.g. after staff edit "Complete patient information").
     * Guest status is separate from incomplete-info checks; this aligns them after a successful save.
     */
    public function clearGuestFlagIfInformationComplete(): bool
    {
        if (!$this->is_guest) {
            return false;
        }

        if ($this->hasIncompleteInformation()['is_incomplete']) {
            return false;
        }

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
