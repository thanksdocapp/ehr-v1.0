@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Patient')
@section('page-title', 'Edit Patient')
@section('page-subtitle', 'Update patient information')

@section('content')
<div class="fade-in-up">

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.patients.update', $patient->id) }}" method="POST" id="patientEditForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>Personal Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" id="first_name" 
                                       class="form-control @error('first_name') is-invalid @enderror" 
                                       value="{{ old('first_name', $patient->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" id="last_name" 
                                       class="form-control @error('last_name') is-invalid @enderror" 
                                       value="{{ old('last_name', $patient->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" id="date_of_birth" 
                                       class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}" required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="blood_group" class="form-label fw-semibold">Blood Group</label>
                                <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" {{ old('blood_group', $patient->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_group', $patient->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_group', $patient->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_group', $patient->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_group', $patient->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_group', $patient->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_group', $patient->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_group', $patient->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label fw-semibold">Patient ID</label>
                                <input type="text" name="patient_id" id="patient_id" 
                                       class="form-control @error('patient_id') is-invalid @enderror" 
                                       value="{{ old('patient_id', $patient->patient_id) }}" readonly>
                                <small class="text-muted">Patient ID cannot be changed</small>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Contact Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $patient->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $patient->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label fw-semibold">Address</label>
                                <textarea name="address" id="address" rows="3" 
                                          class="form-control @error('address') is-invalid @enderror" 
                                          placeholder="Enter complete address">{{ old('address', $patient->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label fw-semibold">City</label>
                                <input type="text" name="city" id="city" 
                                       class="form-control @error('city') is-invalid @enderror" 
                                       value="{{ old('city', $patient->city) }}" placeholder="Enter city">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label fw-semibold">State</label>
                                <input type="text" name="state" id="state" 
                                       class="form-control @error('state') is-invalid @enderror" 
                                       value="{{ old('state', $patient->state) }}" placeholder="Enter state">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label fw-semibold">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code" 
                                       class="form-control @error('postal_code') is-invalid @enderror" 
                                       value="{{ old('postal_code', $patient->postal_code) }}" placeholder="Enter postal code">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Emergency Contact Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-shield me-2"></i>Emergency Contact</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_name" class="form-label fw-semibold">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                       class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_name', $patient->emergency_contact) }}" 
                                       placeholder="Enter emergency contact name">
                                @error('emergency_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label fw-semibold">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" 
                                       class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_phone', $patient->emergency_phone) }}" 
                                       placeholder="+000 123 456 789">
                                @error('emergency_contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clinic Assignment (for doctors and staff) -->
                @php
                    $user = auth()->user();
                    $selectedDeptIds = old('department_ids', $patient->getDepartmentIds());
                    if (!is_array($selectedDeptIds) && $selectedDeptIds) {
                        $selectedDeptIds = [$selectedDeptIds];
                    }
                    if (empty($selectedDeptIds) && old('department_id', $patient->department_id)) {
                        $selectedDeptIds = [old('department_id', $patient->department_id)];
                    }
                    $userDepartments = [];
                    if ($user->role === 'doctor') {
                        $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
                        if ($doctor) {
                            if ($doctor->departments->isNotEmpty()) {
                                $userDepartments = $doctor->departments->pluck('id')->toArray();
                            } elseif ($doctor->department_id) {
                                $userDepartments = [$doctor->department_id];
                            }
                        }
                    } else {
                        // For staff users, get their departments
                        $user->load('departments');
                        if ($user->departments->isNotEmpty()) {
                            $userDepartments = $user->departments->pluck('id')->toArray();
                        } elseif ($user->department_id) {
                            $userDepartments = [$user->department_id];
                        }
                    }
                @endphp
                @if($user->role === 'doctor' || $user->is_admin || $user->role === 'admin')
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-building me-2"></i>Clinic Assignment</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="department_ids" class="form-label fw-semibold">Assign to Clinic(s)</label>
                            @if($departments->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>No Clinics Available:</strong> You are not assigned to any clinic/department. Please contact an administrator to assign you to a clinic.
                                </div>
                            @else
                                <select class="form-select @error('department_ids') is-invalid @enderror" 
                                        id="department_ids" name="department_ids[]" multiple size="5">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ ($selectedDeptIds && in_array($department->id, $selectedDeptIds)) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Hold <kbd>Ctrl</kbd> (Windows/Linux) or <kbd>Cmd</kbd> (Mac) to select multiple clinics. First selected clinic will be set as primary.
                                @if(!$user->is_admin && $user->role !== 'admin' && !empty($userDepartments))
                                    <br><strong>Your assigned clinic(s):</strong> You belong to {{ count($userDepartments) }} clinic(s).
                                @elseif($user->is_admin || $user->role === 'admin')
                                    <br><strong>Admin access:</strong> You can assign patients to any clinic.
                                @endif
                            </small>
                            @error('department_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('department_ids.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Hidden field for backward compatibility with single department_id -->
                        <input type="hidden" id="department_id" name="department_id" value="{{ old('department_id', $patient->department_id) }}">
                    </div>
                </div>
                @endif
                
                <!-- Medical Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-stethoscope me-2"></i>Medical Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="insurance_provider" class="form-label fw-semibold">Insurance Provider</label>
                                <input type="text" name="insurance_provider" id="insurance_provider" 
                                       class="form-control @error('insurance_provider') is-invalid @enderror" 
                                       value="{{ old('insurance_provider', $patient->insurance_provider) }}" 
                                       placeholder="Enter insurance provider">
                                @error('insurance_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="insurance_number" class="form-label fw-semibold">Insurance Number</label>
                                <input type="text" name="insurance_number" id="insurance_number" 
                                       class="form-control @error('insurance_number') is-invalid @enderror" 
                                       value="{{ old('insurance_number', $patient->insurance_number) }}" 
                                       placeholder="Enter insurance number">
                                @error('insurance_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="allergies" class="form-label fw-semibold">Allergies</label>
                            <div id="allergies-container">
                                @php
                                    $allergies = old('allergies', $patient->allergies ?? []);
                                @endphp
                                @if($allergies && count($allergies) > 0)
                                    @foreach($allergies as $allergy)
                                        <div class="input-group mb-2 allergy-item">
                                            <input type="text" class="form-control" name="allergies[]" 
                                                   value="{{ $allergy }}" placeholder="Enter allergy">
                                            <button type="button" class="btn btn-outline-danger remove-allergy">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 allergy-item">
                                        <input type="text" class="form-control" name="allergies[]" 
                                               placeholder="Enter allergy">
                                        <button type="button" class="btn btn-outline-danger remove-allergy">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-allergy">
                                <i class="fas fa-plus"></i> Add Allergy
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="medical_history" class="form-label fw-semibold">Medical History</label>
                            <textarea name="medical_history" id="medical_history" rows="4" 
                                      class="form-control @error('medical_history') is-invalid @enderror" 
                                      placeholder="Enter patient's medical history">{{ old('medical_history', $patient->notes) }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" 
                                   id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $patient->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Patient</strong>
                            </label>
                            <small class="text-muted d-block">Check to activate patient record</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="col-lg-4">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Update Patient
                            </button>
                            <a href="{{ route('staff.patients.show', $patient->id) }}" class="btn btn-info">
                                <i class="fas fa-eye me-2"></i>View Patient
                            </a>
                            <a href="{{ route('staff.patients.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white py-3">
                        <h6 class="card-title mb-0">Current Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Patient ID</small>
                            <strong>#{{ $patient->id }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <strong>{{ $patient->email }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Phone</small>
                            <strong>{{ $patient->phone }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Registered Date</small>
                            <strong>{{ $patient->created_at->format('M d, Y') }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Last Updated</small>
                            <strong>{{ $patient->updated_at->format('M d, Y g:i A') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Sync department_ids to department_id hidden field (for backward compatibility)
    // First selected department becomes the primary department_id
    $('#department_ids').on('change', function() {
        const selectedIds = $(this).val();
        if (selectedIds && selectedIds.length > 0) {
            // Set first selected department as primary (for backward compatibility)
            $('#department_id').val(selectedIds[0]);
        } else {
            $('#department_id').val('');
        }
    });
    
    // Initialize department_id from patient's departments if present
    const selectedIds = $('#department_ids').val();
    if (selectedIds && selectedIds.length > 0) {
        $('#department_id').val(selectedIds[0]);
    }
    
    // Form validation
    $('#patientEditForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...').prop('disabled', true);
    });

    // Real-time validation
    $('input, select, textarea').on('blur', function() {
        if ($(this).prop('required') && !$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Email validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Please enter a valid email address.');
        }
    });

    // Phone number formatting (simple)
    $('#phone, #emergency_contact_phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // Age calculation and guardian ID requirement
    function calculateAgeAndToggleGuardian() {
        const birthDate = new Date($('#date_of_birth').val());
        const today = new Date();
        const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
        const guardianGroup = $('#guardian_id_document_group');
        const guardianInput = $('#guardian_id_document');
        
        if (age < 0) {
            alert('Birth date cannot be in the future.');
            $('#date_of_birth').val('');
            guardianGroup.slideUp();
            guardianInput.prop('required', false);
        } else if (age > 150) {
            alert('Please check the birth date. Age seems too high.');
        } else {
            // Show/hide guardian ID document field based on age
            if (age < 18) {
                guardianGroup.slideDown();
                if (!guardianGroup.find('.alert').length) {
                    // Only require if no existing document
                    guardianInput.prop('required', true);
                }
            } else {
                guardianGroup.slideUp();
                guardianInput.prop('required', false);
            }
        }
    }
    
    $('#date_of_birth').on('change', calculateAgeAndToggleGuardian);
    
    // Calculate age on page load if date is already set
    if ($('#date_of_birth').val()) {
        calculateAgeAndToggleGuardian();
    }
    
    // GP Consent checkbox toggle - backup handler (primary should be inline)
    $('#consent_share_with_gp').on('change', function() {
        if (typeof handleGpConsentChange === 'function') {
            handleGpConsentChange(this);
        } else {
            const gpDetailsGroup = $('#gp_details_group');
            const gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];
            
            if ($(this).is(':checked')) {
                gpDetailsGroup.slideDown();
                gpFields.forEach(function(field) {
                    $('#' + field).prop('required', true);
                });
            } else {
                gpDetailsGroup.slideUp();
                gpFields.forEach(function(field) {
                    $('#' + field).prop('required', false);
                });
            }
        }
    });
    
    // Initialize GP details visibility based on consent checkbox state
    setTimeout(function() {
        if ($('#consent_share_with_gp').is(':checked')) {
            if (typeof handleGpConsentChange === 'function') {
                handleGpConsentChange(document.getElementById('consent_share_with_gp'));
            } else {
                $('#gp_details_group').show();
                ['gp_name', 'gp_email', 'gp_phone', 'gp_address'].forEach(function(field) {
                    $('#' + field).prop('required', true);
                });
            }
        }
    }, 200);
    
    // Add allergy functionality
    $('#add-allergy').click(function() {
        const allergyHtml = `
            <div class="input-group mb-2 allergy-item">
                <input type="text" class="form-control" name="allergies[]" placeholder="Enter allergy">
                <button type="button" class="btn btn-outline-danger remove-allergy">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        $('#allergies-container').append(allergyHtml);
    });

    // Remove allergy functionality
    $(document).on('click', '.remove-allergy', function() {
        if ($('.allergy-item').length > 1) {
            $(this).closest('.allergy-item').remove();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

.form-control:focus,
.form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #495057;
}

.alert {
    border-radius: 8px;
    border: none;
}
</style>
@endpush
@endsection
