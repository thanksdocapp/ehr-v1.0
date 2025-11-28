# Patient-Department-Doctor Relationship Logic

## Overview
This document explains the relationship structure between Patients, Departments, and Doctors in the EHR system.

## Key Principle
**Patients are NOT directly assigned to departments** - they are linked to departments **indirectly through doctors**.

## Relationship Structure

### 1. Doctor ↔ Department (Many-to-Many)
- **Relationship**: `doctor_department` pivot table
- **Fields**: `doctor_id`, `department_id`, `is_primary`, `created_at`, `updated_at`
- **Legacy Support**: `doctors.department_id` field (for backward compatibility)
- **Access Methods**:
  - `$doctor->departments()` - Many-to-many relationship
  - `$doctor->department` - Legacy belongsTo relationship
  - `$doctor->primaryDepartment()` - Gets primary department from pivot or falls back to legacy

### 2. Patient ↔ Doctor (Indirect via Relationships)
- **Direct Fields**:
  - `patients.created_by_doctor_id` - Doctor who created the patient
  - `patients.assigned_doctor_id` - Doctor assigned to the patient
- **Indirect Relationships**:
  - Appointments: `appointments.doctor_id`
  - Medical Records: `medical_records.doctor_id`
  - Prescriptions: `prescriptions.doctor_id`

### 3. Patient ↔ Department (Indirect via Doctors)
- **Many-to-Many**: `department_patient` pivot table (with `is_primary` flag)
- **Legacy Support**: `patients.department_id` field (for backward compatibility)
- **Access Methods**:
  - `$patient->departments()` - Many-to-many relationship
  - `$patient->department` - Legacy belongsTo relationship
  - `$patient->primaryDepartment()` - Gets primary department from pivot or falls back to legacy
  - `$patient->getDepartmentIds()` - Gets all department IDs (from both pivot and legacy)

## Patient Belongs to Department When:

A patient belongs to a department if **ANY** of the following conditions are met:

1. **Created by Doctor in Department**
   - Patient's `created_by_doctor_id` → Doctor belongs to department

2. **Assigned to Doctor in Department**
   - Patient's `assigned_doctor_id` → Doctor belongs to department

3. **Has Appointments with Doctors in Department**
   - Patient has appointments where `appointment.doctor_id` → Doctor belongs to department

4. **Has Medical Records from Doctors in Department**
   - Patient has medical records where `medical_record.doctor_id` → Doctor belongs to department

5. **Direct Department Assignment** (Legacy/Backward Compatibility)
   - Patient's `department_id` matches department
   - Patient has entry in `department_patient` pivot table

## Visibility Rules

### For Doctors:
A doctor can see a patient if:

1. **Patient was created by this doctor** (regardless of department)
   ```php
   $patient->created_by_doctor_id === $doctor->id
   ```

2. **Patient's department intersects with doctor's department(s)**
   - Patient must have at least ONE department in common with doctor's departments
   - Checked via:
     - Many-to-many pivot table (`department_patient`)
     - Legacy `department_id` field (only if no pivot records exist)

### For Admins:
- **Can see ALL patients** (no filtering)

### For Other Staff:
- **Can see patients in their department(s)**
- Department intersection checked via:
  - Many-to-many pivot table (`department_patient`)
  - Legacy `department_id` field (only if no pivot records exist)

## Implementation Details

### Model Methods

#### Patient Model
```php
// Get all department IDs (from both pivot and legacy)
$patient->getDepartmentIds(): array

// Get primary department
$patient->primaryDepartment()

// Check visibility to user
$patient->isVisibleTo($user): bool

// Scope: Filter patients visible to user
Patient::visibleTo($user)->get()

// Scope: Filter by department
Patient::byDepartment($departmentId)->get()

// Scope: Filter by multiple departments
Patient::byDepartments([$dept1, $dept2])->get()
```

#### Doctor Model
```php
// Get all departments (many-to-many)
$doctor->departments

// Get primary department
$doctor->primaryDepartment()

// Scope: Filter by department
Doctor::byDepartment($departmentId)->get()

// Scope: Filter by multiple departments
Doctor::byDepartments([$dept1, $dept2])->get()
```

#### Department Model
```php
// Get all doctors in department
$department->doctors

// Get all patients in department (via relationships)
$department->patients
```

## Database Tables

### Core Tables
- `patients` - Patient records
- `doctors` - Doctor records
- `departments` - Department records
- `users` - User accounts (linked to doctors via `doctors.user_id`)

### Pivot Tables
- `doctor_department` - Many-to-many: Doctors ↔ Departments
  - Fields: `doctor_id`, `department_id`, `is_primary`, `created_at`, `updated_at`
  
- `department_patient` - Many-to-many: Departments ↔ Patients
  - Fields: `department_id`, `patient_id`, `is_primary`, `created_at`, `updated_at`

- `user_department` - Many-to-many: Users ↔ Departments
  - Fields: `user_id`, `department_id`, `is_primary`, `created_at`, `updated_at`

### Related Tables
- `appointments` - Links patients to doctors (`patient_id`, `doctor_id`)
- `medical_records` - Links patients to doctors (`patient_id`, `doctor_id`)
- `prescriptions` - Links patients to doctors (`patient_id`, `doctor_id`)

## Backward Compatibility

The system supports both:
1. **New Implementation**: Many-to-many relationships via pivot tables
2. **Legacy Implementation**: Direct foreign keys (`department_id` fields)

**Priority Order**:
1. Check pivot table relationships first
2. Fall back to legacy `department_id` field ONLY if no pivot records exist

This ensures:
- New records use the flexible many-to-many structure
- Old records continue to work without migration
- Gradual migration path without breaking existing functionality

## Example Queries

### Get all patients for a doctor
```php
$doctor = Doctor::find($doctorId);
$patients = Patient::visibleTo($doctor->user)->get();
```

### Get all patients in a department
```php
$patients = Patient::byDepartment($departmentId)->get();
```

### Get all doctors in a department
```php
$doctors = Doctor::byDepartment($departmentId)->get();
```

### Check if patient belongs to department
```php
$patient = Patient::find($patientId);
$departmentIds = $patient->getDepartmentIds();
$belongsToDepartment = in_array($departmentId, $departmentIds);
```

## Important Notes

1. **Patients are NOT directly assigned to departments** - always go through doctors
2. **Doctors can belong to multiple departments** - use pivot table
3. **Patients can belong to multiple departments** - via different doctors/appointments
4. **Legacy `department_id` fields are maintained** for backward compatibility
5. **Pivot tables take priority** over legacy fields when both exist
6. **Visibility is role-based** - doctors see their patients + department patients

