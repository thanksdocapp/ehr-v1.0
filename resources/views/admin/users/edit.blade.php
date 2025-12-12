@extends('admin.layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit User</li>
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
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    border-bottom: 2px solid #e2e8f0;
}

.form-section-header h4,
.form-section-header h5 {
    color: #1a202c;
    font-weight: 700;
}

.form-section-header i {
    color: #1a202c;
}

.form-section-header small {
    color: #4a5568;
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
        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
        <small class="text-muted">Update user information</small>
        <p class="page-subtitle text-muted">Update user account information and settings</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editUserForm" method="POST" action="{{ contextRoute('users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
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
                                           id="name" name="name" value="{{ old('name', $user->name) }}" 
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
                                           id="email" name="email" value="{{ old('email', $user->email) }}" 
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
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}" 
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
                                           id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}" 
                                           placeholder="Enter employee ID">
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

                                <div class="form-group">
                                    <label for="department_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>Primary Clinic
                                    </label>
                                    @php
                                        $primaryDeptId = old('department_id') ?? ($user->departments()->wherePivot('is_primary', true)->first()?->id ?? $user->department_id);
                                        $selectedDeptIds = old('department_ids', $user->departments->pluck('id')->toArray());
                                    @endphp
                                    <select class="form-control @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id">
                                        <option value="">Select Primary Clinic</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ $primaryDeptId == $department->id ? 'selected' : '' }}>
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
                                            <option value="{{ $department->id }}" {{ in_array($department->id, $selectedDeptIds) ? 'selected' : '' }}>
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
                                           id="specialization" name="specialization" value="{{ old('specialization', $user->specialization) }}" 
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
                                           value="{{ old('hire_date', $user->hire_date ? formatDate($user->hire_date) : '') }}"
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

                <!-- Address Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address</h4>
                        <small class="opacity-75">Optional address details for this user</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="ideal_postcodes_finder" class="form-label">
                                        <i class="fas fa-search me-1"></i>Find Address
                                    </label>
                                    <input type="text" class="form-control" id="ideal_postcodes_finder"
                                           placeholder="Start typing postcode or address…">
                                    <small class="form-help" id="ideal_postcodes_notice" style="display:none;"></small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-home me-1"></i>Address Line 1
                                    </label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                           id="address" name="address" value="{{ old('address', $user->address) }}">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address_line_2" class="form-label">
                                        <i class="fas fa-building me-1"></i>Address Line 2
                                    </label>
                                    <input type="text" class="form-control @error('address_line_2') is-invalid @enderror"
                                           id="address_line_2" name="address_line_2" value="{{ old('address_line_2', $user->address_line_2) }}">
                                    @error('address_line_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="form-label">
                                        <i class="fas fa-city me-1"></i>Town/City
                                    </label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city', $user->city) }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="form-label">
                                        <i class="fas fa-map me-1"></i>County
                                    </label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror"
                                           id="state" name="state" value="{{ old('state', $user->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code" class="form-label">
                                        <i class="fas fa-mail-bulk me-1"></i>Postcode
                                    </label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}"
                                           style="text-transform: uppercase;">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="country" class="form-label">
                                        <i class="fas fa-flag me-1"></i>Country
                                    </label>
                                    <input type="text" class="form-control @error('country') is-invalid @enderror"
                                           id="country" name="country" value="{{ old('country', $user->country) }}">
                                    @error('country')
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
                                        <i class="fas fa-lock me-1"></i>Password
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" minlength="8">
                                    <div class="form-help">Leave blank to keep current password. Minimum 8 characters if changing.</div>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Confirm Password
                                    </label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" minlength="8">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="avatar" class="form-label">
                                        <i class="fas fa-camera me-1"></i>Profile Picture
                                    </label>
                                    @if($user->avatar)
                                        <div class="mb-2">
                                            <img src="{{ asset('assets/images/avatars/' . $user->avatar) }}" alt="Current Avatar" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                                            <p class="text-muted small mb-2">Current profile picture</p>
                                        </div>
                                    @endif
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
                                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    <i class="fas fa-toggle-on me-1"></i>Active
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="is_admin" name="is_admin" value="1" 
                                                       {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
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
                                      placeholder="Brief description about the user...">{{ old('bio', $user->bio) }}</textarea>
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
                    <i class="fas fa-save me-2"></i>Update User
                </button>
                <a href="{{ contextRoute('users.index') }}" class="btn btn-secondary btn-lg me-3">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
                <a href="{{ contextRoute('users.show', $user) }}" class="btn btn-info btn-lg">
                    <i class="fas fa-eye me-2"></i>View User
                </a>
            </div>
        </div>
    </form>
</div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>User Information</h6>
                <ul>
                    <li><strong>Created:</strong> {{ formatDateTime($user->created_at) }}</li>
                    <li><strong>Last Updated:</strong> {{ formatDateTime($user->updated_at) }}</li>
                    @if($user->last_login_at)
                        <li><strong>Last Login:</strong> {{ formatDateTime($user->last_login_at) }}</li>
                    @endif
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Editing Guidelines</h6>
                <ul>
                    <li>Password field can be left blank to keep current password</li>
                    <li>Role changes may affect user permissions</li>
                    <li>Clinic changes update user assignments</li>
                    <li>Admin privileges should be granted carefully</li>
                    <li>Deactivating users prevents login access</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes</h6>
                <ul>
                    <li>Changing email may require re-verification</li>
                    <li>Employee ID should remain unique</li>
                    <li>Role changes update user capabilities</li>
                    <li>Profile picture updates replace existing image</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('users.show', $user) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>View User Details
                    </a>
                    <a href="{{ contextRoute('users.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>Back to Users List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@ideal-postcodes/address-finder@latest/dist/address-finder.min.js" defer></script>
<script>
$(document).ready(function() {
    // Ideal Postcodes Address Finder (race-safe)
    (function initIdealPostcodes() {
        const apiKey = @json(config('services.ideal_postcodes.api_key'));
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
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
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
            alert('Passwords do not match');
            return false;
        }
    });
});
</script>
@endpush
