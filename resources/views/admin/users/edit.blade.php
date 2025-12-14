@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit User</li>
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

/* Current Avatar Preview */
.current-avatar {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    margin-bottom: 1rem;
}

.current-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 10px;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.current-avatar-info {
    flex: 1;
}

.current-avatar-info .label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.current-avatar-info .text {
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

/* User Info Stats */
.user-info-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.user-info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.user-info-item:first-child {
    padding-top: 0;
}

.user-info-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-size: 0.85rem;
}

.user-info-icon.created {
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.user-info-icon.updated {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.user-info-icon.login {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.user-info-content {
    flex: 1;
}

.user-info-label {
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-info-value {
    font-weight: 600;
    color: #374151;
    font-size: 0.85rem;
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

.quick-action-btn.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    border: none;
}

.quick-action-btn.info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
    color: white;
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

.btn-modern-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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

.btn-modern-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(79, 172, 254, 0.4);
    color: white;
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
                <h4><i class="fas fa-user-edit me-2"></i>Edit User</h4>
                <p>Update account information for {{ $user->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ contextRoute('users.show', $user) }}" class="btn btn-light">
                    <i class="fas fa-eye me-2"></i>View Profile
                </a>
                <a href="{{ contextRoute('users.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <form id="editUserForm" method="POST" action="{{ contextRoute('users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
                                           id="name" name="name" value="{{ old('name', $user->name) }}"
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
                                           id="email" name="email" value="{{ old('email', $user->email) }}"
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
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
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
                                           id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                                           placeholder="Enter employee ID">
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
                                        @if(is_array($roles))
                                            @foreach($roles as $roleKey => $roleLabel)
                                                <option value="{{ $roleKey }}" {{ old('role', $user->role) == $roleKey ? 'selected' : '' }}>
                                                    {{ $roleLabel }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrator</option>
                                            <option value="doctor" {{ old('role', $user->role) == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                            <option value="nurse" {{ old('role', $user->role) == 'nurse' ? 'selected' : '' }}>Nurse</option>
                                            <option value="receptionist" {{ old('role', $user->role) == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                                            <option value="pharmacist" {{ old('role', $user->role) == 'pharmacist' ? 'selected' : '' }}>Pharmacist</option>
                                            <option value="technician" {{ old('role', $user->role) == 'technician' ? 'selected' : '' }}>Technician</option>
                                            <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>Staff</option>
                                        @endif
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
                                           id="specialization" name="specialization" value="{{ old('specialization', $user->specialization) }}"
                                           placeholder="e.g., Cardiology, Pediatrics">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @php
                                $primaryDeptId = old('department_id') ?? ($user->departments()->wherePivot('is_primary', true)->first()?->id ?? $user->department_id);
                                $selectedDeptIds = old('department_ids', $user->departments->pluck('id')->toArray());
                            @endphp
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-hospital"></i>Primary Clinic
                                    </label>
                                    <select class="modern-form-control @error('department_id') is-invalid @enderror"
                                            id="department_id" name="department_id">
                                        <option value="">Select Primary Clinic</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ $primaryDeptId == $department->id ? 'selected' : '' }}>
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
                                            <option value="{{ $department->id }}" {{ in_array($department->id, $selectedDeptIds) ? 'selected' : '' }}>
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
                                    <input type="text" class="modern-form-control @error('hire_date') is-invalid @enderror"
                                           id="hire_date" name="hire_date"
                                           value="{{ old('hire_date', $user->hire_date ? formatDate($user->hire_date) : '') }}"
                                           placeholder="dd-mm-yyyy"
                                           pattern="\d{2}-\d{2}-\d{4}"
                                           maxlength="10">
                                    <div class="form-hint">Format: dd-mm-yyyy (e.g., 15-01-2025)</div>
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
                                           id="address" name="address" value="{{ old('address', $user->address) }}">
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
                                           id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $user->address_line_2) }}">
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
                                           id="city" name="city" value="{{ old('city', $user->city) }}">
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
                                           id="state" name="state" value="{{ old('state', $user->state) }}">
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
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}"
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
                                           id="country" name="country" value="{{ old('country', $user->country) }}">
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
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-lock"></i>New Password
                                    </label>
                                    <input type="password" class="modern-form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" minlength="8"
                                           placeholder="Leave blank to keep current">
                                    <div class="form-hint">Leave blank to keep current password. Min 8 characters if changing.</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-lock"></i>Confirm New Password
                                    </label>
                                    <input type="password" class="modern-form-control"
                                           id="password_confirmation" name="password_confirmation" minlength="8"
                                           placeholder="Re-enter new password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-camera"></i>Profile Picture
                                    </label>
                                    @if($user->avatar)
                                        <div class="current-avatar">
                                            <img src="{{ asset('assets/images/avatars/' . $user->avatar) }}" alt="Current Avatar">
                                            <div class="current-avatar-info">
                                                <div class="label">Current Picture</div>
                                                <div class="text">{{ $user->avatar }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="file-input-wrapper">
                                        <input type="file" class="modern-form-control @error('avatar') is-invalid @enderror"
                                               id="avatar" name="avatar" accept="image/*">
                                    </div>
                                    <div class="form-hint">JPG, PNG, GIF. Max 2MB. Leave blank to keep current.</div>
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
                                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                                <label for="is_active">
                                                    <i class="fas fa-check-circle text-success me-1"></i>Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="modern-checkbox">
                                                <input type="checkbox" id="is_admin" name="is_admin" value="1"
                                                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
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
                                              placeholder="Brief description about the user...">{{ old('bio', $user->bio) }}</textarea>
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
                        <i class="fas fa-save"></i>Update User
                    </button>
                    <a href="{{ contextRoute('users.index') }}" class="btn-modern-secondary">
                        <i class="fas fa-times"></i>Cancel
                    </a>
                    <a href="{{ contextRoute('users.show', $user) }}" class="btn-modern-info">
                        <i class="fas fa-eye"></i>View Profile
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- User Info Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-info-circle text-primary me-2"></i>User Information</h6>
                </div>
                <div class="sidebar-card-body">
                    <div class="user-info-item">
                        <div class="user-info-icon created">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="user-info-content">
                            <div class="user-info-label">Created</div>
                            <div class="user-info-value">{{ formatDateTime($user->created_at) }}</div>
                        </div>
                    </div>
                    <div class="user-info-item">
                        <div class="user-info-icon updated">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="user-info-content">
                            <div class="user-info-label">Last Updated</div>
                            <div class="user-info-value">{{ formatDateTime($user->updated_at) }}</div>
                        </div>
                    </div>
                    @if($user->last_login_at)
                    <div class="user-info-item">
                        <div class="user-info-icon login">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="user-info-content">
                            <div class="user-info-label">Last Login</div>
                            <div class="user-info-value">{{ formatDateTime($user->last_login_at) }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Guidelines Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-lightbulb text-warning me-2"></i>Editing Guidelines</h6>
                </div>
                <div class="sidebar-card-body">
                    <ul>
                        <li>Password field can be left blank to keep current password</li>
                        <li>Role changes may affect user permissions</li>
                        <li>Clinic changes update user assignments</li>
                        <li>Admin privileges should be granted carefully</li>
                        <li>Deactivating users prevents login access</li>
                    </ul>
                </div>
            </div>

            <!-- Important Notes Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-exclamation-triangle text-danger me-2"></i>Important Notes</h6>
                </div>
                <div class="sidebar-card-body">
                    <ul>
                        <li>Changing email may require re-verification</li>
                        <li>Employee ID should remain unique</li>
                        <li>Role changes update user capabilities</li>
                        <li>Profile picture updates replace existing image</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6><i class="fas fa-bolt text-info me-2"></i>Quick Actions</h6>
                </div>
                <div class="sidebar-card-body">
                    <a href="{{ contextRoute('users.show', $user) }}" class="quick-action-btn info">
                        <i class="fas fa-eye me-2"></i>View User Details
                    </a>
                    <a href="{{ contextRoute('users.index') }}" class="quick-action-btn secondary">
                        <i class="fas fa-list me-2"></i>Back to Users List
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

    // Date input mask for dd-mm-yyyy format
    $('#hire_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        $(this).val(value);
    });

    // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission
    $('form').on('submit', function() {
        const hireDateInput = $('#hire_date');
        const dateStr = hireDateInput.val();
        if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
            const parts = dateStr.split('-');
            const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
            hireDateInput.val(yyyyMmDd);
        }
    });

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

    // Form validation
    $('#editUserForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();

        if (password && password !== confirmPassword) {
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
