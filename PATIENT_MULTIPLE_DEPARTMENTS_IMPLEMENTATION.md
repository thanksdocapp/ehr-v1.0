# Patient Multiple Departments Implementation

## Overview
Extended patient visibility system to support patients belonging to multiple departments using a many-to-many relationship, while maintaining backward compatibility with the existing `department_id` field.

---

## 1. Database Schema

### Migration: `database/migrations/2025_11_16_010301_create_patient_department_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false); // Mark primary department
            $table->timestamps();
            
            // Ensure unique combination of patient and department
            $table->unique(['patient_id', 'department_id']);
            
            // Index for performance
            $table->index(['patient_id', 'is_primary']);
            $table->index('department_id');
        });
        
        // Backfill existing data from patients.department_id to pivot table
        // This ensures existing patients with department_id are migrated to the new many-to-many relationship
        if (Schema::hasColumn('patients', 'department_id')) {
            DB::statement("
                INSERT INTO department_patient (patient_id, department_id, is_primary, created_at, updated_at)
                SELECT id, department_id, true, created_at, updated_at
                FROM patients
                WHERE department_id IS NOT NULL
                ON DUPLICATE KEY UPDATE updated_at = NOW()
            ");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('department_patient');
    }
};
```

**Key Points:**
- Pivot table: `department_patient`
- Supports `is_primary` flag to mark the primary department
- Automatically backfills existing `patients.department_id` data
- Maintains backward compatibility with `patients.department_id` column

---

## 2. Patient Model - Updated Relationships

**File:** `app/Models/Patient.php`

```php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Many-to-many relationship with departments (current implementation)
 */
public function departments(): BelongsToMany
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
    if ($this->relationLoaded('departments') || $this->departments()->exists()) {
        $departmentIds = $this->departments->pluck('id')->toArray();
    }
    
    // Fallback to legacy department_id if no pivot records exist
    if (empty($departmentIds) && $this->department_id) {
        $departmentIds = [$this->department_id];
    }
    
    return array_unique($departmentIds);
}
```

---

## 3. Department Model - Updated Relationships

**File:** `app/Models/Department.php`

```php
// Many-to-many relationship with patients
public function patients(): BelongsToMany
{
    return $this->belongsToMany(Patient::class, 'department_patient')
        ->withPivot('is_primary')
        ->withTimestamps();
}

// Legacy relationship for backward compatibility
public function directPatients(): HasMany
{
    return $this->hasMany(Patient::class);
}
```

---

## 4. Updated Visibility Scope - Department Intersection

**File:** `app/Models/Patient.php`

```php
/**
 * Scope to filter patients visible to a specific user based on role.
 * 
 * For Doctors:
 * - Patients whose departments intersect with the doctor's departments, OR
 * - Patients that were created by that doctor (created_by_doctor_id)
 * 
 * For Admins:
 * - All patients (no filtering)
 * 
 * For other roles:
 * - Patients whose departments intersect with their departments
 */
public function scopeVisibleTo($query, $user = null)
{
    // ... (admin check code) ...
    
    // For doctors, filter by department intersection and created_by
    if ($user->role === 'doctor') {
        $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
        
        if (!$doctor) {
            return $query->whereRaw('1 = 0');
        }
        
        // Get doctor's department IDs
        $doctorDepartmentIds = [];
        if ($doctor->departments->isNotEmpty()) {
            $doctorDepartmentIds = $doctor->departments->pluck('id')->toArray();
        } elseif ($doctor->department_id) {
            $doctorDepartmentIds = [$doctor->department_id];
        }
        
        return $query->where(function($q) use ($doctor, $doctorDepartmentIds) {
            // Patients whose departments intersect with the doctor's departments
            if (!empty($doctorDepartmentIds)) {
                // Check if patient has any departments in common with doctor's departments
                // Using many-to-many relationship (current implementation)
                $q->whereHas('departments', function($deptQuery) use ($doctorDepartmentIds) {
                    $deptQuery->whereIn('departments.id', $doctorDepartmentIds);
                })
                // OR fallback to legacy department_id field (for backward compatibility)
                ->orWhereIn('department_id', $doctorDepartmentIds)
                // OR patients that were created by this doctor
                ->orWhere('created_by_doctor_id', $doctor->id);
            } else {
                // If no departments, only show patients created by this doctor
                $q->where('created_by_doctor_id', $doctor->id);
            }
        });
    }
    
    // For other staff roles, filter by department intersection
    // ... (similar intersection logic) ...
}
```

**Key Changes:**
- Uses `whereHas('departments')` to check intersection via pivot table
- Falls back to `department_id` for backward compatibility
- Checks intersection of doctor's departments and patient's departments

---

## 5. Updated isVisibleTo Method

**File:** `app/Models/Patient.php`

```php
public function isVisibleTo($user = null)
{
    // ... (admin check code) ...
    
    // For doctors, check department intersection and created_by
    if ($user->role === 'doctor') {
        $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
        
        if (!$doctor) {
            return false;
        }
        
        // Check if patient was created by this doctor
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
            return false;
        }
        
        // Get patient's department IDs (from both pivot table and legacy department_id)
        $patientDepartmentIds = $this->getDepartmentIds();
        
        // Check if there's any intersection between doctor's and patient's departments
        if (!empty($patientDepartmentIds)) {
            $intersection = array_intersect($doctorDepartmentIds, $patientDepartmentIds);
            if (!empty($intersection)) {
                return true;
            }
        }
        
        return false;
    }
    
    // ... (other roles intersection logic) ...
}
```

**Key Points:**
- Uses `getDepartmentIds()` to get patient's departments from both sources
- Checks intersection using `array_intersect()`
- Still allows doctors to see patients they created regardless of department

---

## 6. Controller Example - Admin PatientsController

**File:** `app/Http/Controllers/Admin/PatientsController.php`

### Store Method (Creating Patient):

```php
// Handle department assignment (support both single and multiple departments)
$departmentIds = [];
if ($request->has('department_id') && $request->department_id) {
    // Single department (backward compatibility)
    $patientData['department_id'] = (int)$request->department_id;
    $departmentIds[] = (int)$request->department_id;
}

// Support multiple departments (new implementation)
if ($request->has('department_ids') && is_array($request->department_ids)) {
    $departmentIds = array_unique(array_filter(array_map('intval', $request->department_ids)));
    // If multiple departments provided, set first as primary for backward compatibility
    if (!empty($departmentIds) && !isset($patientData['department_id'])) {
        $patientData['department_id'] = $departmentIds[0];
    }
}

// ... (doctor creation logic) ...

$patient = Patient::create($patientData);

// Sync departments to many-to-many relationship
if (!empty($departmentIds)) {
    $syncData = [];
    foreach ($departmentIds as $index => $deptId) {
        $syncData[$deptId] = ['is_primary' => $index === 0]; // First department is primary
    }
    $patient->departments()->sync($syncData);
}
```

### Update Method (Updating Patient):

```php
// Similar logic for updates
$patient->update($patientData);

// Sync departments to many-to-many relationship
if (!empty($departmentIds)) {
    $syncData = [];
    foreach ($departmentIds as $index => $deptId) {
        $syncData[$deptId] = ['is_primary' => $index === 0];
    }
    $patient->departments()->sync($syncData);
} elseif ($request->has('department_id') && !$request->department_id) {
    // If department_id is explicitly set to empty/null, remove all departments
    $patient->departments()->detach();
}
```

---

## 7. Visibility Rules Summary

| Scenario | Doctor Visibility |
|----------|------------------|
| **Patient in Department A only** | Doctor in Department A ✅<br>Doctor in Department B ❌ (unless they created patient) |
| **Patient in Departments A and B** | Doctor in Department A ✅<br>Doctor in Department B ✅<br>Doctor in Department C ❌ (unless they created patient) |
| **Doctor created patient** | Doctor can see patient ✅ (regardless of departments) |
| **Admin role** | Can see ALL patients ✅ |

---

## 8. Backward Compatibility

- ✅ Existing `patients.department_id` column is preserved
- ✅ Migration automatically backfills existing data to pivot table
- ✅ Visibility logic checks both pivot table and `department_id` field
- ✅ Legacy `department()` relationship still works
- ✅ Controllers support both single `department_id` and multiple `department_ids`

---

## 9. Usage Examples

### Get Patient's Departments:
```php
$patient = Patient::find(1);

// Get all departments (many-to-many)
$departments = $patient->departments; // Collection of Department models

// Get department IDs
$departmentIds = $patient->getDepartmentIds(); // Array of IDs

// Get primary department
$primaryDept = $patient->primaryDepartment(); // Department model or null

// Legacy single department (still works)
$singleDept = $patient->department; // Department model or null
```

### Assign Multiple Departments:
```php
$patient = Patient::find(1);

// Sync multiple departments (first is primary)
$patient->departments()->sync([
    1 => ['is_primary' => true],
    2 => ['is_primary' => false],
    3 => ['is_primary' => false],
]);

// Or using attach
$patient->departments()->attach([1, 2, 3], ['is_primary' => false]);
```

### Query Patients by Department Intersection:
```php
// Get patients visible to current user (handles intersection automatically)
$patients = Patient::visibleTo(Auth::user())->get();

// Check if specific patient is visible
if ($patient->isVisibleTo(Auth::user())) {
    // Show patient
}
```

---

## 10. Testing Scenarios

1. ✅ Patient in Department A only - Doctor A sees, Doctor B doesn't (unless creator)
2. ✅ Patient in Departments A and B - Both Doctor A and Doctor B see
3. ✅ Patient created by Doctor A - Doctor A sees regardless of departments
4. ✅ Admin sees all patients
5. ✅ Backward compatibility with `department_id` field
6. ✅ Migration backfills existing data correctly

