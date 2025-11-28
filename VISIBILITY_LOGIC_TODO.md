# Patient-Department-Doctor Visibility Logic - Implementation Status

## ✅ Completed
- **Patient** - Full visibility logic implemented
- **MedicalRecord** - Full visibility logic implemented

## 🔄 Needs Implementation

### 1. **Prescription** (High Priority)
- **Model**: `app/Models/Prescription.php`
- **Relationships**: `patient_id`, `doctor_id`
- **Controllers to Update**:
  - `app/Http/Controllers/Staff/PrescriptionsController.php`
  - `app/Http/Controllers/Admin/PrescriptionsController.php`
- **Scopes Needed**:
  - `scopeByDepartment($query, $departmentId)`
  - `scopeByDepartments($query, array $departmentIds)`
  - `scopeVisibleTo($query, $user)`
- **Method Needed**:
  - `isVisibleTo($user)`

### 2. **LabReport** (High Priority)
- **Model**: `app/Models/LabReport.php`
- **Relationships**: `patient_id`, `doctor_id`
- **Controllers to Update**:
  - `app/Http/Controllers/Staff/LabReportsController.php`
  - `app/Http/Controllers/Admin/LabReportsController.php`
- **Scopes Needed**:
  - `scopeByDepartment($query, $departmentId)`
  - `scopeByDepartments($query, array $departmentIds)`
  - `scopeVisibleTo($query, $user)`
- **Method Needed**:
  - `isVisibleTo($user)`

### 3. **Appointment** (High Priority)
- **Model**: `app/Models/Appointment.php`
- **Relationships**: `patient_id`, `doctor_id`, `department_id`
- **Note**: Already has `scopeByDepartment()` but needs `scopeVisibleTo()` and `isVisibleTo()`
- **Controllers to Update**:
  - `app/Http/Controllers/Staff/AppointmentsController.php`
  - `app/Http/Controllers/Admin/AppointmentsController.php`
  - `app/Http/Controllers/AppointmentController.php`
- **Scopes Needed**:
  - `scopeByDepartments($query, array $departmentIds)` (enhance existing)
  - `scopeVisibleTo($query, $user)` (NEW)
- **Method Needed**:
  - `isVisibleTo($user)` (NEW)

### 4. **Billing** (High Priority)
- **Model**: `app/Models/Billing.php`
- **Relationships**: `patient_id`, `doctor_id`
- **Controllers to Update**:
  - `app/Http/Controllers/Staff/BillingsController.php`
  - `app/Http/Controllers/Admin/BillingsController.php`
- **Scopes Needed**:
  - `scopeByDepartment($query, $departmentId)`
  - `scopeByDepartments($query, array $departmentIds)`
  - `scopeVisibleTo($query, $user)`
- **Method Needed**:
  - `isVisibleTo($user)`

## 📋 Implementation Pattern

For each model, add the following scopes and methods (similar to MedicalRecord):

```php
// Scope to filter by department
public function scopeByDepartment($query, $departmentId)
{
    return $query->where(function($q) use ($departmentId) {
        // Records from doctors in this department
        $q->whereHas('doctor', function($doctorQuery) use ($departmentId) {
            // Support both pivot table and legacy department_id
        })
        // OR records for patients in this department
        ->orWhereHas('patient', function($patientQuery) use ($departmentId) {
            // Check patient's department relationships
        });
    });
}

// Scope to filter by multiple departments
public function scopeByDepartments($query, array $departmentIds)
{
    // Similar to scopeByDepartment but with whereIn
}

// Scope to filter visible to user
public function scopeVisibleTo($query, $user = null)
{
    // Admins see all
    // Doctors see: their own records + records for patients in their department
    // Staff see: records in their department
}

// Check if record is visible to user
public function isVisibleTo($user = null)
{
    // Check individual record visibility
}
```

## 🎯 Priority Order
1. Prescription
2. LabReport
3. Appointment
4. Billing

## 📝 Notes
- All models should follow the same pattern as MedicalRecord
- Support both new (pivot tables) and legacy (`department_id`) relationships
- Controllers should use `visibleTo($user)` scope instead of manual filtering
- Update statistics queries in DepartmentsController to use new scopes

