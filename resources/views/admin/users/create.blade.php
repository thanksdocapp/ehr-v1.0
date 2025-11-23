@extends('admin.layouts.app')

@section('title', 'Create User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Create User</li>
@endsection

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.form-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
}

.form-section-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create New User</h5>
        <small class="text-muted">Create a new system user</small>
        <p class="page-subtitle text-muted">Add a new user to the system with comprehensive account details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createUserForm" method="POST" action="{{ contextRoute('users.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h4>
                        <small class="opacity-75">Basic personal details and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Full Name *
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Enter full name" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address *
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="user@hospital.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="+000 123 456 789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="employee_id" class="form-label">
                                        <i class="fas fa-id-badge me-1"></i>Employee ID
                                    </label>
                                    <input type="text" class="form-control @error('employee_id') is-invalid @enderror" 
                                           id="employee_id" name="employee_id" value="{{ old('employee_id') }}" 
                                           placeholder="Leave blank for auto-generation">
                                    <div class="form-help">Will be auto-generated if left empty</div>
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role" class="form-label">
                                        <i class="fas fa-user-tag me-1"></i>Role *
                                    </label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
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

                                <div class="form-group">
                                    <label for="department_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>Primary Clinic
                                    </label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id">
                                        <option value="">Select Primary Clinic</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">The primary clinic for this user</small>
                                </div>

                                <div class="form-group">
                                    <label for="department_ids" class="form-label">
                                        <i class="fas fa-building me-1"></i>Additional Clinics
                                    </label>
                                    <select class="form-control @error('department_ids') is-invalid @enderror" 
                                            id="department_ids" name="department_ids[]" multiple size="4">
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ in_array($department->id, old('department_ids', [])) ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple clinics</small>
                                </div>

                                <div class="form-group">
                                    <label for="specialization" class="form-label">
                                        <i class="fas fa-stethoscope me-1"></i>Specialisation
                                    </label>
                                    <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                           id="specialization" name="specialization" value="{{ old('specialization') }}" 
                                           placeholder="e.g., Cardiology, Pediatrics">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="hire_date" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Hire Date
                                    </label>
                                    <input type="text" class="form-control @error('hire_date') is-invalid @enderror" 
                                           id="hire_date" name="hire_date" 
                                           value="{{ old('hire_date') ? formatDate(old('hire_date')) : '' }}"
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10">
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Details Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Account Details</h4>
                        <small class="opacity-75">Password and account settings</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Password *
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required minlength="8">
                                    <div class="form-help">Minimum 8 characters</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Confirm Password *
                                    </label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required minlength="8">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="avatar" class="form-label">
                                        <i class="fas fa-camera me-1"></i>Profile Picture
                                    </label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                           id="avatar" name="avatar" accept="image/*">
                                    <div class="form-help">JPG, PNG, GIF. Max 2MB</div>
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_active" name="is_active" value="1" 
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    <i class="fas fa-toggle-on me-1"></i>Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_admin" name="is_admin" value="1" 
                                                       {{ old('is_admin') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_admin">
                                                    <i class="fas fa-shield-alt me-1"></i>Admin
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="bio" class="form-label">
                                <i class="fas fa-user-edit me-1"></i>Bio/Description
                            </label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" name="bio" rows="3" 
                                      placeholder="Brief description about the user...">{{ old('bio') }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

        <!-- Action Buttons -->
        <div class="form-section">
            <div class="form-section-body text-center">
                <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                    <i class="fas fa-save me-2"></i>Create User
                </button>
                <a href="{{ contextRoute('users.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </div>
    </form>
</div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>User Creation Guidelines</h6>
                <ul>
                    <li>All fields marked with * are required</li>
                    <li>Employee ID will be auto-generated if left empty</li>
                    <li>Password must be at least 8 characters long</li>
                    <li>Profile picture should be professional</li>
                    <li>Admin privileges should be granted carefully</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                <ul>
                    <li>Keep employee ID unique across the system</li>
                    <li>Ensure role matches clinic assignment</li>
                    <li>Update specialisation for medical staff</li>
                    <li>Set appropriate clinic for each role</li>
                    <li>Activate accounts only when ready for use</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-users me-2"></i>Role Descriptions</h6>
                <ul>
                    <li><strong>Administrator:</strong> Full system access</li>
                    <li><strong>Doctor:</strong> Medical staff with patient access</li>
                    <li><strong>Nurse:</strong> Patient care and basic medical tasks</li>
                    <li><strong>Receptionist:</strong> Front desk and scheduling</li>
                    <li><strong>Pharmacist:</strong> Medication management</li>
                    <li><strong>Technician:</strong> Equipment and lab work</li>
                    <li><strong>Staff:</strong> General hospital support</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('users.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Users
                    </a>
                    <a href="{{ contextRoute('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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
        const password = $('#password').val();
        const confirmPassword = $('#password_confirmation').val();
        
        if (password !== confirmPassword) {
            e.preventDefault();
            $('#password_confirmation').addClass('is-invalid');
            alert('Passwords do not match');
            return false;
        }
    });
});
</script>
@endpush
