@extends('admin.layouts.app')

@section('title', 'Create User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Create User</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
}

.page-header h4 {
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-header p {
    opacity: 0.9;
    margin-bottom: 0;
}

/* Form Sections */
.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.form-section-header {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.form-section-header h5 {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.form-section-header small {
    color: #718096;
    font-size: 0.85rem;
}

.form-section-header .header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.form-section-header .header-icon.personal {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.form-section-header .header-icon.role {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
}

.form-section-header .header-icon.address {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
}

.form-section-header .header-icon.security {
    background: linear-gradient(135deg, #fa709a, #fee140);
    color: white;
}

.form-section-body {
    padding: 1.5rem;
}

/* Form Controls */
.modern-form-group {
    margin-bottom: 1.25rem;
}

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
    display: block;
}

.modern-form-label i {
    color: #6b7280;
    margin-right: 0.35rem;
}

.modern-form-label .required {
    color: #ef4444;
    margin-left: 0.25rem;
}

.modern-form-control {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 0.925rem;
    transition: all 0.2s ease;
    width: 100%;
    background: #fff;
}

.modern-form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    outline: none;
}

.modern-form-control.is-invalid {
    border-color: #ef4444;
}

.modern-form-control.is-invalid:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15);
}

select.modern-form-control {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25em 1.25em;
    padding-right: 2.5rem;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}

textarea.modern-form-control {
    min-height: 100px;
    resize: vertical;
}

.form-hint {
    font-size: 0.8rem;
    color: #6b7280;
    margin-top: 0.35rem;
}

/* Checkbox Styling */
.modern-checkbox {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: #f9fafb;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s ease;
}

.modern-checkbox:hover {
    border-color: #667eea;
    background: #f3f4f6;
}

.modern-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    cursor: pointer;
    accent-color: #667eea;
}

.modern-checkbox label {
    margin-bottom: 0;
    cursor: pointer;
    font-weight: 500;
    color: #374151;
}

/* Sidebar Cards */
.sidebar-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.sidebar-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.sidebar-card-header h6 {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0;
    font-size: 0.9rem;
}

.sidebar-card-body {
    padding: 1.25rem;
}

.sidebar-card-body ul {
    margin: 0;
    padding-left: 1.25rem;
}

.sidebar-card-body li {
    margin-bottom: 0.5rem;
    color: #4b5563;
    font-size: 0.875rem;
    line-height: 1.5;
}

.sidebar-card-body li:last-child {
    margin-bottom: 0;
}

/* Role Badges in Sidebar */
.role-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.role-item:last-child {
    border-bottom: none;
}

.role-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 0.75rem;
}

.role-dot.admin { background: linear-gradient(135deg, #dc3545, #c82333); }
.role-dot.doctor { background: linear-gradient(135deg, #667eea, #764ba2); }
.role-dot.nurse { background: linear-gradient(135deg, #20c997, #17a2b8); }
.role-dot.receptionist { background: linear-gradient(135deg, #fd7e14, #e55a00); }
.role-dot.pharmacist { background: linear-gradient(135deg, #28a745, #218838); }
.role-dot.technician { background: linear-gradient(135deg, #6c757d, #5a6268); }
.role-dot.staff { background: linear-gradient(135deg, #17a2b8, #138496); }

.role-name {
    font-weight: 600;
    color: #374151;
    font-size: 0.85rem;
}

.role-desc {
    color: #6b7280;
    font-size: 0.8rem;
    margin-left: auto;
}

/* Quick Actions */
.quick-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.65rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    text-decoration: none;
    margin-bottom: 0.5rem;
}

.quick-action-btn:last-child {
    margin-bottom: 0;
}

.quick-action-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.quick-action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
}

.quick-action-btn.secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.quick-action-btn.secondary:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* Action Buttons */
.btn-modern-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.85rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.btn-modern-secondary {
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    color: #374151;
    padding: 0.85rem 2rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-modern-secondary:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* File Input */
.file-input-wrapper {
    position: relative;
}

.file-input-wrapper input[type="file"] {
    padding: 0.5rem;
}

.file-input-wrapper input[type="file"]::file-selector-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    margin-right: 1rem;
    transition: all 0.2s ease;
}

.file-input-wrapper input[type="file"]::file-selector-button:hover {
    opacity: 0.9;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-user-plus me-2"></i>Create New User</h4>
                <p>Add a new staff member to the system with their complete profile details</p>
            </div>
            <a href="{{ contextRoute('users.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <form id="createUserForm" method="POST" action="{{ contextRoute('users.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="d-flex align-items-center">
                            <div class="header-icon personal me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Personal Information</h5>
                                <small>Basic details and contact information</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-user"></i>Full Name<span class="required">*</span>
                                    </label>
                                    <input type="text" class="modern-form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}"
                                           placeholder="Enter full name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-envelope"></i>Email Address<span class="required">*</span>
                                    </label>
                                    <input type="email" class="modern-form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}"
                                           placeholder="user@example.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-phone"></i>Phone Number
                                    </label>
                                    <input type="tel" class="modern-form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}"
                                           placeholder="+44 123 456 7890">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-id-badge"></i>Employee ID
                                    </label>
                                    <input type="text" class="modern-form-control @error('employee_id') is-invalid @enderror"
                                           id="employee_id" name="employee_id" value="{{ old('employee_id') }}"
                                           placeholder="Leave blank for auto-generation">
                                    <div class="form-hint">Will be auto-generated if left empty</div>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role & Assignment Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="d-flex align-items-center">
                            <div class="header-icon role me-3">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Role & Assignment</h5>
                                <small>Define role, specialization and clinic assignments</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-user-shield"></i>Role<span class="required">*</span>
                                    </label>
                                    <select class="modern-form-control @error('role') is-invalid @enderror"
                                            id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                        <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                        <option value="nurse" {{ old('role') == 'nurse' ? 'selected' : '' }}>Nurse</option>
                                        <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                        <option value="pharmacist" {{ old('role') == 'pharmacist' ? 'selected' : '' }}>Pharmacist</option>
                                        <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>Technician</option>
                                        <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-stethoscope"></i>Specialisation
                                    </label>
                                    <input type="text" class="modern-form-control @error('specialization') is-invalid @enderror"
                                           id="specialization" name="specialization" value="{{ old('specialization') }}"
                                           placeholder="e.g., Cardiology, Pediatrics">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-hospital"></i>Primary Clinic
                                    </label>
                                    <select class="modern-form-control @error('department_id') is-invalid @enderror"
                                            id="department_id" name="department_id">
                                        <option value="">Select Primary Clinic</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-hint">The main clinic this user will work at</div>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-building"></i>Additional Clinics
                                    </label>
                                    <select class="modern-form-control @error('department_ids') is-invalid @enderror"
                                            id="department_ids" name="department_ids[]" multiple size="4">
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ in_array($department->id, old('department_ids', [])) ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-hint">Hold Ctrl/Cmd to select multiple clinics</div>
                                    @error('department_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-calendar-alt"></i>Hire Date
                                    </label>
                                    <input type="text" class="modern-form-control uk-date @error('hire_date') is-invalid @enderror"
                                           id="hire_date" name="hire_date"
                                           value="{{ old('hire_date') ? (old('hire_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('hire_date')) ? \Carbon\Carbon::parse(old('hire_date'))->format('d/m/Y') : old('hire_date')) : '' }}"
                                           placeholder="dd/mm/yyyy"
                                           pattern="\d{2}/\d{2}/\d{4}"
                                           maxlength="10">
                                    <div class="form-hint">Format: dd/mm/yyyy (e.g., 15/01/2025)</div>
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="d-flex align-items-center">
                            <div class="header-icon address me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Address Information</h5>
                                <small>Optional address details for this user</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-search"></i>Find Address
                                    </label>
                                    <input type="text" class="modern-form-control" id="ideal_postcodes_finder"
                                           placeholder="Start typing postcode or address...">
                                    <small class="form-hint" id="ideal_postcodes_notice" style="display:none;"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-home"></i>Address Line 1
                                    </label>
                                    <input type="text" class="modern-form-control @error('address') is-invalid @enderror"
                                           id="address" name="address" value="{{ old('address') }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-building"></i>Address Line 2
                                    </label>
                                    <input type="text" class="modern-form-control @error('address_line_2') is-invalid @enderror"
                                           id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}">
                                    @error('address_line_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-city"></i>Town/City
                                    </label>
                                    <input type="text" class="modern-form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-map"></i>County
                                    </label>
                                    <input type="text" class="modern-form-control @error('state') is-invalid @enderror"
                                           id="state" name="state" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-mail-bulk"></i>Postcode
                                    </label>
                                    <input type="text" class="modern-form-control @error('postal_code') is-invalid @enderror"
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                           style="text-transform: uppercase;">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="modern-form-group mb-0">
                                    <label class="modern-form-label">
                                        <i class="fas fa-flag"></i>Country
                                    </label>
                                    <input type="text" class="modern-form-control @error('country') is-invalid @enderror"
                                           id="country" name="country" value="{{ old('country', 'United Kingdom') }}">
                                    @error('country')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Security Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="d-flex align-items-center">
                            <div class="header-icon security me-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Account Security</h5>
                                <small>Password and account settings</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="generate_password" name="generate_password" value="1" {{ old('generate_password') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="generate_password">
                                        <i class="fas fa-magic me-1"></i>Generate password automatically
                                    </label>
                                    <small class="d-block text-muted mt-1">A secure 12-character password will be created. For doctors, it is sent in the welcome email.</small>
                                </div>
                            </div>
                            <div class="col-md-6 password-fields">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-lock"></i>Password<span class="required">*</span>
                                    </label>
                                    <input type="password" class="modern-form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" minlength="8"
                                           placeholder="Minimum 8 characters (or use generate above)">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 password-fields">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-lock"></i>Confirm Password<span class="required">*</span>
                                    </label>
                                    <input type="password" class="modern-form-control"
                                           id="password_confirmation" name="password_confirmation" minlength="8"
                                           placeholder="Re-enter password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-camera"></i>Profile Picture
                                    </label>
                                    <div class="file-input-wrapper">
                                        <input type="file" class="modern-form-control @error('avatar') is-invalid @enderror"
                                               id="avatar" name="avatar" accept="image/*">
                                    </div>
                                    <div class="form-hint">JPG, PNG, GIF. Max 2MB</div>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-toggle-on"></i>Account Status
                                    </label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="modern-checkbox">
                                                <input type="checkbox" id="is_active" name="is_active" value="1"
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label for="is_active">
                                                    <i class="fas fa-check-circle text-success me-1"></i>Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="modern-checkbox">
                                                <input type="checkbox" id="is_admin" name="is_admin" value="1"
                                                       {{ old('is_admin') ? 'checked' : '' }}>
                                                <label for="is_admin">
                                                    <i class="fas fa-crown text-warning me-1"></i>Admin
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="modern-form-group mb-0">
                                    <label class="modern-form-label">
                                        <i class="fas fa-align-left"></i>Bio/Description
                                    </label>
                                    <textarea class="modern-form-control @error('bio') is-invalid @enderror"
                                              id="bio" name="bio" rows="3"
                                              placeholder="Brief description about the user...">{{ old('bio') }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <button type="submit" class="btn-modern-primary">
                        <i class="fas fa-user-plus"></i>Create User
                    </button>
                    <a href="{{ contextRoute('users.index') }}" class="btn-modern-secondary">
                        <i class="fas fa-times"></i>Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Guidelines Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-info-circle text-primary me-2"></i>Creation Guidelines</h6>
                </div>
                <div class="sidebar-card-body">
                    <ul>
                        <li>All fields marked with <span class="text-danger">*</span> are required</li>
                        <li>Employee ID will be auto-generated if left empty</li>
                        <li>Password must be at least 8 characters long</li>
                        <li>Profile picture should be professional</li>
                        <li>Admin privileges should be granted carefully</li>
                    </ul>
                </div>
            </div>

            <!-- Best Practices Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Best Practices</h6>
                </div>
                <div class="sidebar-card-body">
                    <ul>
                        <li>Keep employee ID unique across the system</li>
                        <li>Ensure role matches clinic assignment</li>
                        <li>Update specialisation for medical staff</li>
                        <li>Set appropriate clinic for each role</li>
                        <li>Activate accounts only when ready for use</li>
                    </ul>
                </div>
            </div>

            <!-- Role Descriptions Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-users text-info me-2"></i>Role Descriptions</h6>
                </div>
                <div class="sidebar-card-body">
                    <div class="role-item">
                        <div class="role-dot admin"></div>
                        <span class="role-name">Administrator</span>
                        <span class="role-desc">Full access</span>
                    </div>
                    <div class="role-item">
                        <div class="role-dot doctor"></div>
                        <span class="role-name">Doctor</span>
                        <span class="role-desc">Medical staff</span>
                    </div>
                    <div class="role-item">
                        <div class="role-dot nurse"></div>
                        <span class="role-name">Nurse</span>
                        <span class="role-desc">Patient care</span>
                    </div>
                    <div class="role-item">
                        <div class="role-dot receptionist"></div>
                        <span class="role-name">Receptionist</span>
                        <span class="role-desc">Front desk</span>
                    </div>
                    <div class="role-item">
                        <div class="role-dot pharmacist"></div>
                        <span class="role-name">Pharmacist</span>
                        <span class="role-desc">Medication</span>
                    </div>
                    <div class="role-item">
                        <div class="role-dot technician"></div>
                        <span class="role-name">Technician</span>
                        <span class="role-desc">Lab & equipment</span>
                    </div>
                    <div class="role-item">
                        <div class="role-dot staff"></div>
                        <span class="role-name">Staff</span>
                        <span class="role-desc">General support</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-bolt text-danger me-2"></i>Quick Actions</h6>
                </div>
                <div class="sidebar-card-body">
                    <a href="{{ contextRoute('users.index') }}" class="quick-action-btn primary">
                        <i class="fas fa-list me-2"></i>View All Users
                    </a>
                    <a href="{{ contextRoute('departments.index') }}" class="quick-action-btn secondary">
                        <i class="fas fa-hospital me-2"></i>Manage Clinics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@ideal-postcodes/address-finder-bundled@5/dist/address-finder.js" defer></script>
<script>
$(document).ready(function() {
    // Ideal Postcodes Address Finder (race-safe)
    (function initIdealPostcodes() {
        const apiKey = @json(\App\Models\Setting::get('ideal_postcodes_api_key') ?: config('services.ideal_postcodes.api_key'));
        const input = document.getElementById('ideal_postcodes_finder');
        const notice = document.getElementById('ideal_postcodes_notice');
        if (!input || !notice) return;

        function hideNotice() {
            notice.style.display = 'none';
            notice.textContent = '';
        }
        function showNotice(msg) {
            notice.style.display = 'block';
            notice.innerHTML = `<i class="fas fa-info-circle me-1"></i>${msg}`;
        }
        function getAF() {
            if (typeof AddressFinder !== 'undefined' && AddressFinder) return AddressFinder;
            if (window.IdealPostcodes && window.IdealPostcodes.AddressFinder) return window.IdealPostcodes.AddressFinder;
            return null;
        }
        function waitForAF(timeoutMs) {
            return new Promise((resolve, reject) => {
                const start = Date.now();
                (function tick() {
                    const AF = getAF();
                    if (AF && typeof AF.setup === 'function') return resolve(AF);
                    if (Date.now() - start > timeoutMs) return reject(new Error('Ideal Postcodes AddressFinder load timeout'));
                    setTimeout(tick, 50);
                })();
            });
        }

        if (!apiKey) {
            showNotice('Address lookup is unavailable (missing API key). Please enter address manually.');
            return;
        }

        hideNotice();
        waitForAF(8000)
            .then((AF) => {
                AF.setup({
                    apiKey: apiKey,
                    inputField: input,
                    outputFields: {
                        line_1: '#address',
                        line_2: '#address_line_2',
                        post_town: '#city',
                        county: '#state',
                        postcode: '#postal_code',
                    },
                    onCheckFailed: function() {
                        showNotice('Address lookup is unavailable right now. Please enter address manually.');
                    },
                });
            })
            .catch((e) => {
                console.error('Ideal Postcodes load/init failed:', e);
                showNotice('Address lookup failed to load. Please enter address manually.');
            });
    })();

    // Generate password: toggle password fields
    function togglePasswordFields() {
        const useGenerate = $('#generate_password').is(':checked');
        $('#password, #password_confirmation').prop('required', !useGenerate).prop('disabled', useGenerate);
        if (useGenerate) {
            $('#password, #password_confirmation').val('').removeClass('is-invalid');
        }
    }
    $('#generate_password').on('change', togglePasswordFields);
    togglePasswordFields();

    // Password confirmation validation
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();

        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Employee ID auto-suggestion
    $('#name').on('blur', function() {
        const employeeIdField = $('#employee_id');
        if (!employeeIdField.val()) {
            const name = $(this).val().trim();
            if (name) {
                const nameParts = name.split(' ');
                const initials = nameParts.map(part => part.charAt(0).toUpperCase()).join('');
                const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                employeeIdField.attr('placeholder', `Suggested: ${initials}${randomNum}`);
            }
        }
    });

    // Form validation
    $('#createUserForm').on('submit', function(e) {
        if ($('#generate_password').is(':checked')) return true;
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();

        if (password !== confirmPassword) {
            e.preventDefault();
            $('#password_confirmation').addClass('is-invalid');
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'The passwords you entered do not match. Please try again.',
                confirmButtonColor: '#667eea'
            });
            return false;
        }
    });
});
</script>
@endpush
