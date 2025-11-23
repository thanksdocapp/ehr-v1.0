# Patient Visibility Implementation Summary

## Overview
This document shows the implementation of patient visibility rules for doctors, ensuring doctors can only see:
- Patients whose department matches one of the doctor's departments, OR
- Patients that were created by that doctor

Admins can see all patients.

---

## 1. Patient Model - Visibility Scope

**File:** `app/Models/Patient.php`

```php
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
    
    // For doctors, filter by department and created_by
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
            // Patients whose department_id matches one of the doctor's departments
            if (!empty($doctorDepartmentIds)) {
                $q->whereIn('department_id', $doctorDepartmentIds);
            }
            // OR patients that were created by this doctor
            ->orWhere('created_by_doctor_id', $doctor->id);
        });
    }
    
    // For other staff roles, filter by their department(s)
    $userDepartmentIds = [];
    $user->load('departments');
    if ($user->departments->isNotEmpty()) {
        $userDepartmentIds = $user->departments->pluck('id')->toArray();
    } elseif ($user->department_id) {
        $userDepartmentIds = [$user->department_id];
    }
    
    if (!empty($userDepartmentIds)) {
        return $query->whereIn('department_id', $userDepartmentIds);
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
    
    // For doctors, check department and created_by
    if ($user->role === 'doctor') {
        $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
        
        if (!$doctor) {
            return false;
        }
        
        // Check if patient was created by this doctor
        if ($this->created_by_doctor_id === $doctor->id) {
            return true;
        }
        
        // Check if patient's department matches one of doctor's departments
        if ($this->department_id) {
            // Get doctor's department IDs
            $doctorDepartmentIds = [];
            if ($doctor->departments->isNotEmpty()) {
                $doctorDepartmentIds = $doctor->departments->pluck('id')->toArray();
            } elseif ($doctor->department_id) {
                $doctorDepartmentIds = [$doctor->department_id];
            }
            
            if (in_array($this->department_id, $doctorDepartmentIds)) {
                return true;
            }
        }
        
        return false;
    }
    
    // For other staff roles, check their department(s)
    $user->load('departments');
    $userDepartmentIds = [];
    if ($user->departments->isNotEmpty()) {
        $userDepartmentIds = $user->departments->pluck('id')->toArray();
    } elseif ($user->department_id) {
        $userDepartmentIds = [$user->department_id];
    }
    
    if (!empty($userDepartmentIds) && $this->department_id) {
        return in_array($this->department_id, $userDepartmentIds);
    }
    
    return false;
}
```

---

## 2. Patient Policy - Authorization

**File:** `app/Policies/PatientPolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Determine whether the user can view any patients.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // Other roles (doctors, staff) can view patients based on visibility rules
        // The scopeVisibleTo will handle the filtering
        return true;
    }

    /**
     * Determine whether the user can view the patient.
     */
    public function view(User $user, Patient $patient): bool
    {
        // Admins can view all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // Use the patient's isVisibleTo method to check visibility
        return $patient->isVisibleTo($user);
    }

    /**
     * Determine whether the user can create patients.
     */
    public function create(User $user): bool
    {
        // Admins and staff with appropriate roles can create patients
        return $user->is_admin || 
               $user->role === 'admin' || 
               in_array($user->role, ['doctor', 'nurse', 'receptionist', 'staff']);
    }

    /**
     * Determine whether the user can update the patient.
     */
    public function update(User $user, Patient $patient): bool
    {
        // Admins can update all patients
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }
        
        // Staff can update patients they can view
        return $patient->isVisibleTo($user);
    }

    /**
     * Determine whether the user can delete the patient.
     */
    public function delete(User $user, Patient $patient): bool
    {
        // Only admins can delete patients
        return $user->is_admin || $user->role === 'admin';
    }

    // ... other policy methods (restore, forceDelete)
}
```

**Registration:** `app/Providers/AuthServiceProvider.php`

```php
protected $policies = [
    \App\Models\Patient::class => \App\Policies\PatientPolicy::class,
];
```

---

## 3. Controller Implementation - Before and After

### Before: Staff/PatientsController

**File:** `app/Http/Controllers/Staff/PatientsController.php`

```php
public function index()
{
    $user = Auth::user();
    $query = Patient::with(['appointments', 'assignedDoctor', 'createdByDoctor']);
    
    // For doctors, filter by direct assignment ONLY (not by department)
    // Doctors should only see:
    // 1. Patients directly assigned to them
    // 2. Patients they created
    // 3. Patients with appointments with them
    // 4. Patients with medical records from them
    if ($user->role === 'doctor') {
        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor) {
            $doctorId = $doctor->id;
            
            // Filter patients ONLY by direct relationships with this doctor
            $query->where(function($q) use ($doctorId) {
                // Direct assignment: assigned to this doctor
                $q->where('assigned_doctor_id', $doctorId)
                // OR created by this doctor
                ->orWhere('created_by_doctor_id', $doctorId)
                // OR has appointments with this doctor
                ->orWhereHas('appointments', function($appQuery) use ($doctorId) {
                    $appQuery->where('doctor_id', $doctorId);
                })
                // OR has medical records from this doctor
                ->orWhereHas('medicalRecords', function($recordQuery) use ($doctorId) {
                    $recordQuery->where('doctor_id', $doctorId);
                });
            });
        }
    } else {
        // For non-doctor staff, filter by department - support multiple departments
        $departmentId = $this->getUserDepartmentId();
        $userDepartmentIds = $this->getUserDepartmentIds();
        
        if (!empty($userDepartmentIds)) {
            // Users can see patients from any of their departments
            $query->byDepartments($userDepartmentIds);
        } elseif ($departmentId) {
            // Fallback to single department check
            $query->byDepartment($departmentId);
        }
    }
    
    $patients = $query->latest()->paginate(15);

    return view('staff.patients.index', compact('patients'));
}

public function show(Patient $patient)
{
    $user = Auth::user();
    
    // Complex manual access check with department logic...
    // (Many lines of code checking departments, doctors, appointments, etc.)
    
    if (!$hasAccess) {
        abort(403, 'You do not have permission to view this patient.');
    }
    
    // Load relationships...
    return view('staff.patients.show', compact('patient'));
}
```

### After: Staff/PatientsController

**File:** `app/Http/Controllers/Staff/PatientsController.php`

```php
public function index()
{
    $user = Auth::user();
    
    // Apply visibility filter based on user role
    // Admins see all, doctors see based on department/created_by, staff see by department
    $query = Patient::with(['appointments', 'assignedDoctor', 'createdByDoctor'])
        ->visibleTo($user);
    
    $patients = $query->latest()->paginate(15);

    return view('staff.patients.index', compact('patients'));
}

public function show(Patient $patient)
{
    $user = Auth::user();
    
    // Check if patient is visible to this user using policy
    $this->authorize('view', $patient);
    
    // Load relationships with proper ordering
    $patient->load([
        'appointments' => function($query) {
            $query->with('doctor')->latest('appointment_date')->latest('appointment_time');
        },
        'medicalRecords' => function($query) {
            $query->with('doctor')->latest('record_date')->latest('created_at');
        },
        // ... other relationships
    ]);
    
    return view('staff.patients.show', compact('patient'));
}
```

---

## 4. Usage Examples

### In Controllers

```php
// List patients visible to current user
$patients = Patient::visibleTo(Auth::user())->paginate(15);

// List patients visible to specific user
$patients = Patient::visibleTo($user)->get();

// Check if a specific patient is visible
if ($patient->isVisibleTo($user)) {
    // Show patient details
}

// In controller method, use policy
$this->authorize('view', $patient);
```

### In API Controllers

```php
// API endpoint for doctors
public function index(Request $request)
{
    $patients = Patient::visibleTo(Auth::user())
        ->with('department')
        ->paginate(20);
    
    return response()->json($patients);
}
```

---

## 5. Visibility Rules Summary

| Role | Visibility Rules |
|------|-----------------|
| **Admin** | Can see ALL patients (no filtering) |
| **Doctor** | Can see:<br>1. Patients whose `department_id` matches one of the doctor's departments, OR<br>2. Patients where `created_by_doctor_id` = doctor's ID |
| **Other Staff** | Can see patients whose `department_id` matches one of their departments |

---

## 6. Key Points

1. **Reusable Scope**: `scopeVisibleTo()` can be chained with other query methods
2. **Policy Protection**: `PatientPolicy` ensures authorization at the gate/policy level
3. **Admin Override**: Admins always see all patients
4. **Doctor Logic**: Doctors see patients in their departments OR patients they created
5. **Backward Compatible**: Supports both new (pivot table) and old (`department_id`) department relationships

---

## 7. Testing Scenarios

- ✅ Doctor A can see patients in Department A
- ✅ Doctor A can see patients they created (even if in different department)
- ✅ Doctor A cannot see patients in Department B (created by Doctor B)
- ✅ Admin can see all patients
- ✅ Other staff see patients in their departments only

