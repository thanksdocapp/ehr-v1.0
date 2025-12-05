@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create New Patient')

@section('page-title', 'Create New Patient')
@section('page-subtitle', 'Register a new patient in the system')

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
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Validation Errors</h5>
            <hr>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.patients.store') }}" method="POST" id="patientCreateForm" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Form Content -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="first_name" id="first_name" 
                                           class="form-control @error('first_name') is-invalid @enderror" 
                                           value="{{ old('first_name') }}" 
                                           placeholder="Enter first name" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="last_name" id="last_name" 
                                           class="form-control @error('last_name') is-invalid @enderror" 
                                           value="{{ old('last_name') }}" 
                                           placeholder="Enter last name" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                                    <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="blood_group" class="form-label fw-semibold">Blood Group</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tint"></i></span>
                                    <select name="blood_group" id="blood_group" class="form-select @error('blood_group') is-invalid @enderror">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-0">
                                <label for="patient_id" class="form-label fw-semibold">Patient ID</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" name="patient_id" id="patient_id" 
                                           class="form-control @error('patient_id') is-invalid @enderror" 
                                           value="{{ old('patient_id', 'PAT-' . strtoupper(\Illuminate\Support\Str::random(6))) }}" 
                                           placeholder="Auto-generated" readonly>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Auto-generated unique patient ID
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Age & Date of Birth with Guardian Documents -->
                <div class="doctor-card mb-4 border-primary">
                    <div class="doctor-card-header bg-white border-bottom">
                        <h5 class="doctor-card-title mb-0 text-primary">
                            <i class="fas fa-birthday-cake me-2"></i>Age & Date of Birth
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label fw-semibold">
                                    Date of Birth <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="date_of_birth" id="date_of_birth" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           value="{{ old('date_of_birth') }}" 
                                           max="{{ date('Y-m-d') }}"
                                           min="{{ date('Y-m-d', strtotime('-150 years')) }}"
                                           required>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Must be today or earlier
                                </small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Calculated Age</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                    <input type="text" id="calculated_age_display" 
                                           class="form-control bg-light" 
                                           value="Enter date of birth first" 
                                           readonly>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Auto-calculated: Years and months
                                </small>
                            </div>
                        </div>

                        <!-- Guardian ID Document (Shows for Under 18) -->
                        <div id="guardian_id_document_section" style="display: {{ $errors->has('guardian_id_document') ? 'block' : 'none' }};">
                            <hr class="my-3">
                            
                            <div class="alert alert-warning d-flex align-items-center mb-3" role="alert">
                                <i class="fas fa-exclamation-triangle me-2 fa-2x"></i>
                                <div>
                                    <strong>Guardian ID Document Required</strong><br>
                                    <small>Patient is under 18 years old. Please upload parent/guardian identification document.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12 mb-0">
                                    <label for="guardian_id_document" class="form-label fw-semibold">
                                        <i class="fas fa-user-shield me-1"></i>Guardian ID Document <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" name="guardian_id_document" id="guardian_id_document" 
                                           class="form-control border-warning @error('guardian_id_document') is-invalid @enderror" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-danger d-block mt-1 fw-bold">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Required for patients under 18: PDF, JPG, PNG (Max 5MB)
                                    </small>
                                    @error('guardian_id_document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient ID Document -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header bg-white border-bottom">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-id-card me-2"></i>Patient ID Document
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="patient_id_document" class="form-label fw-semibold">
                                Upload Patient ID Document <small class="text-muted">(Optional)</small>
                            </label>
                            <input type="file" name="patient_id_document" id="patient_id_document" 
                                   class="form-control @error('patient_id_document') is-invalid @enderror" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Accepted formats: PDF, JPG, PNG (Max 5MB)
                            </small>
                            @error('patient_id_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            <strong>Security:</strong> Uploaded documents are stored securely and accessible only to authorized staff.
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
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}" 
                                           placeholder="patient@example.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}" 
                                           placeholder="+44 123 456 7890" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="address" class="form-label fw-semibold">Address</label>
                                <textarea name="address" id="address" rows="3" 
                                          class="form-control @error('address') is-invalid @enderror" 
                                          placeholder="Enter complete address">{{ old('address') }}</textarea>
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
                                       value="{{ old('city') }}" placeholder="Enter city">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label fw-semibold">State</label>
                                <input type="text" name="state" id="state" 
                                       class="form-control @error('state') is-invalid @enderror" 
                                       value="{{ old('state') }}" placeholder="Enter state">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label fw-semibold">Postal Code</label>
                                <input type="text" name="postal_code" id="postal_code" 
                                       class="form-control @error('postal_code') is-invalid @enderror" 
                                       value="{{ old('postal_code') }}" placeholder="Enter postal code">
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
                                <label for="emergency_contact_name" class="form-label fw-semibold">Emergency Contact Name <span class="text-danger">*</span></label>
                                <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                       class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_name') }}" required>
                                @error('emergency_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label fw-semibold">Emergency Contact Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" 
                                       class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_phone') }}" required>
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
                                                {{ (old('department_ids') && in_array($department->id, old('department_ids'))) || 
                                                   (old('department_id') == $department->id) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Hold <kbd>Ctrl</kbd> (Windows/Linux) or <kbd>Cmd</kbd> (Mac) to select multiple clinics. First selected clinic will be set as primary.
                                @if(!$user->is_admin && $user->role !== 'admin' && !empty($userDepartments))
                                    <br><strong>Your assigned clinic(s):</strong> You belong to {{ count($userDepartments) }} clinic(s). Patient will be assigned to your clinic(s) if not specified.
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
                        <input type="hidden" id="department_id" name="department_id" value="">
                    </div>
                </div>
                @endif

                <!-- GP Consent & Details -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>GP Consent & Details</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" 
                                   id="consent_share_with_gp" name="consent_share_with_gp" value="1" 
                                   {{ old('consent_share_with_gp') ? 'checked' : '' }}
                                   onchange="handleGpConsentChange(this)">
                            <label class="form-check-label" for="consent_share_with_gp" onclick="setTimeout(function(){handleGpConsentChange(document.getElementById('consent_share_with_gp'));}, 10);">
                                <strong>I consent for you to share information with my GP.</strong>
                            </label>
                        </div>
                        <small class="text-muted d-block mb-3">By checking this box, you authorize the hospital to share your medical information with your GP.</small>

                        <script>
                        // Inline function to handle GP consent - runs immediately
                        function handleGpConsentChange(checkbox) {
                            var gpGroup = document.getElementById('gp_details_group');
                            var gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];
                            
                            if (!gpGroup) return;
                            
                            var isChecked = checkbox && (checkbox.checked || checkbox.getAttribute('checked') !== null);
                            
                            if (isChecked) {
                                // Force show - use multiple methods
                                gpGroup.style.display = 'block';
                                gpGroup.style.visibility = 'visible';
                                gpGroup.style.opacity = '1';
                                gpGroup.removeAttribute('style');
                                gpGroup.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                                
                                // Set required fields
                                gpFields.forEach(function(fieldId) {
                                    var field = document.getElementById(fieldId);
                                    if (field) {
                                        field.required = true;
                                        field.setAttribute('required', 'required');
                                    }
                                });
                            } else {
                                // Force hide
                                gpGroup.style.display = 'none';
                                gpGroup.style.visibility = 'hidden';
                                gpGroup.removeAttribute('style');
                                gpGroup.setAttribute('style', 'display: none !important;');
                                
                                // Remove required and clear fields
                                gpFields.forEach(function(fieldId) {
                                    var field = document.getElementById(fieldId);
                                    if (field) {
                                        field.required = false;
                                        field.removeAttribute('required');
                                        field.value = '';
                                    }
                                });
                            }
                        }
                        
                        // Initialize on page load
                        (function() {
                            var checkbox = document.getElementById('consent_share_with_gp');
                            if (checkbox) {
                                // Check initial state
                                setTimeout(function() {
                                    handleGpConsentChange(checkbox);
                                }, 100);
                                
                                // Also add event listeners
                                checkbox.addEventListener('change', function() {
                                    handleGpConsentChange(this);
                                });
                                checkbox.addEventListener('click', function() {
                                    setTimeout(function() {
                                        handleGpConsentChange(checkbox);
                                    }, 10);
                                });
                            }
                        })();
                        </script>

                        <div id="gp_details_group" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gp_name" class="form-label fw-semibold">GP Name <span class="text-danger">*</span></label>
                                    <input type="text" name="gp_name" id="gp_name" 
                                           class="form-control @error('gp_name') is-invalid @enderror" 
                                           value="{{ old('gp_name') }}" placeholder="Enter GP full name">
                                    @error('gp_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gp_email" class="form-label fw-semibold">GP Email <span class="text-danger">*</span></label>
                                    <input type="email" name="gp_email" id="gp_email" 
                                           class="form-control @error('gp_email') is-invalid @enderror" 
                                           value="{{ old('gp_email') }}" placeholder="gp@example.com">
                                    @error('gp_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gp_phone" class="form-label fw-semibold">GP Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="gp_phone" id="gp_phone" 
                                           class="form-control @error('gp_phone') is-invalid @enderror" 
                                           value="{{ old('gp_phone') }}" placeholder="+000 123 456 789">
                                    @error('gp_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gp_address" class="form-label fw-semibold">GP Address <span class="text-danger">*</span></label>
                                    <textarea name="gp_address" id="gp_address" rows="2" 
                                              class="form-control @error('gp_address') is-invalid @enderror" 
                                              placeholder="Enter GP clinic address">{{ old('gp_address') }}</textarea>
                                    @error('gp_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                                       value="{{ old('insurance_provider') }}" placeholder="Enter insurance provider">
                                @error('insurance_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="insurance_number" class="form-label fw-semibold">Insurance Number</label>
                                <input type="text" name="insurance_number" id="insurance_number" 
                                       class="form-control @error('insurance_number') is-invalid @enderror" 
                                       value="{{ old('insurance_number') }}" placeholder="Enter insurance number">
                                @error('insurance_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="allergies" class="form-label fw-semibold">Allergies</label>
                            <div id="allergies-container">
                                @if(old('allergies'))
                                    @foreach(old('allergies') as $allergy)
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
                                      placeholder="Enter patient's medical history">{{ old('medical_history') }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" 
                                   id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Patient</strong>
                            </label>
                            <small class="text-muted d-block">Check to activate patient record</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="col-lg-4">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Create Patient
                            </button>
                            <a href="{{ route('staff.patients.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white py-3">
                        <h6 class="card-title mb-0">Staff Registration Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Registration Type</small>
                            <strong>Staff-Assisted</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Login Access</small>
                            <strong>Email & Password</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Account Status</small>
                            <strong class="text-success">Active Upon Creation</strong>
                        </div>
                        <hr class="my-3">
                        <p class="small text-muted mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Patient account will be created. Login credentials can be shared manually if needed.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    /* Guardian ID Document Section */
    #guardian_id_document_section {
        transition: all 0.3s ease-in-out;
    }
    
    #guardian_id_document_section.highlight {
        animation: highlightPulse 1s ease-in-out;
    }
    
    @keyframes highlightPulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
        50% {
            box-shadow: 0 0 20px 5px rgba(255, 193, 7, 0.5);
        }
    }
    
    /* Clean Input Group Styling */
    .input-group-text {
        min-width: 45px;
        justify-content: center;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #80bdff;
        background-color: #e7f1ff;
    }
    
    /* Calculated Age Display */
    #calculated_age_display.text-success {
        color: #28a745 !important;
        font-weight: 600;
    }
    
    #calculated_age_display.text-danger {
        color: #dc3545 !important;
        font-weight: 600;
    }
    
    /* Clean card headers */
    .doctor-card-header.bg-white {
        background-color: #ffffff;
    }
    
    .doctor-card.border-primary {
        border-color: #0d6efd !important;
        border-width: 2px;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Log any server-side errors for debugging
    @if($errors->any())
        console.error('Validation errors:', @json($errors->all()));
    @endif
    
    @if(session('error'))
        console.error('Server error:', '{{ session('error') }}');
    @endif
    
    // Scroll to error summary if present
    if ($('.alert-danger').length > 0) {
        $('html, body').animate({
            scrollTop: $('.alert-danger').first().offset().top - 100
        }, 500);
    }
    
    // Form validation
    $('#patientCreateForm').on('submit', function(e) {
        let isValid = true;
        let errorMessages = [];
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                const fieldName = $(this).closest('.mb-3').find('label').text().replace('*', '').trim();
                errorMessages.push(fieldName + ' is required');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields:\n\n' + errorMessages.join('\n'));
            // Scroll to first invalid field
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
            return false;
        }
        
        // Show loading state
        console.log('Submitting patient creation form...');
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin"></i> Creating Patient...').prop('disabled', true);
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
    
    // Age calculation with years and months - Auto-calculates on change and page load
    function calculateAgeAndToggleGuardian() {
        const birthDateValue = $('#date_of_birth').val();
        const ageDisplay = $('#calculated_age_display');
        
        if (!birthDateValue) {
            ageDisplay.val('Enter date of birth first').removeClass('text-danger text-success');
            $('#guardian_id_document_section').hide();
            $('#guardian_id_document').prop('required', false);
            return;
        }
        
        const birthDate = new Date(birthDateValue);
        const today = new Date();
        
        // Calculate years and months accurately
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        
        if (months < 0) {
            years--;
            months += 12;
        }
        
        // If birth day hasn't occurred this month yet, subtract a month
        if (today.getDate() < birthDate.getDate()) {
            months--;
            if (months < 0) {
                years--;
                months += 12;
            }
        }
        
        const guardianSection = $('#guardian_id_document_section');
        const guardianInput = $('#guardian_id_document');
        const dobField = $('#date_of_birth');
        
        console.log('Age Calculated:', years, 'years', months, 'months from DOB:', birthDateValue);
        
        // Validate date
        if (years < 0) {
            // Date is in the future
            ageDisplay.val('❌ Invalid (future date)').removeClass('text-success').addClass('text-danger');
            dobField.addClass('is-invalid');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Birth date cannot be in the future.',
                    confirmButtonColor: '#d33'
                });
            } else {
                alert('Birth date cannot be in the future.');
            }
            
            guardianSection.slideUp();
            guardianInput.prop('required', false);
            return;
        } else if (years > 150) {
            // Age too high
            ageDisplay.val('❌ Invalid (age > 150)').removeClass('text-success').addClass('text-danger');
            dobField.addClass('is-invalid');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Unusual Date',
                    text: 'Please verify the birth date. Age seems too high.',
                    confirmButtonColor: '#f0ad4e'
                });
            } else {
                alert('Please verify the birth date.');
            }
            return;
        } else {
            // Valid date - show age with years and months
            const ageText = years + ' years, ' + months + ' months';
            ageDisplay.val(ageText).removeClass('text-danger').addClass('text-success');
            dobField.removeClass('is-invalid');
        }

        // Show/hide Guardian ID section within the same card based on age
        if (years < 18) {
            console.log('✓ Patient is under 18 - showing Guardian ID section');
            guardianSection.slideDown(300);
            guardianInput.prop('required', true);
            
            // Show notification
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Guardian ID Required',
                    text: `Patient is ${years} years, ${months} months old (under 18). Guardian ID document is required.`,
                    timer: 3500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        } else {
            console.log('✓ Patient is 18 or older - hiding Guardian ID section');
            guardianSection.slideUp(300);
            guardianInput.prop('required', false);
            guardianInput.val(''); // Clear file input
        }
    }
    
    // Attach change event handler to Date of Birth
    $('#date_of_birth').on('change', function() {
        console.log('📅 Date of birth changed:', $(this).val());
        calculateAgeAndToggleGuardian();
    });
    
    // AUTO-CALCULATE age on page load if DOB already has a value
    $(document).ready(function() {
        console.log('🔄 Page loaded - initializing age calculation');
        const initialDOB = $('#date_of_birth').val();
        console.log('📅 Initial DOB value:', initialDOB);
        
        if (initialDOB && initialDOB.trim() !== '') {
            console.log('✓ DOB found on page load - calculating age automatically...');
            setTimeout(function() {
                calculateAgeAndToggleGuardian();
            }, 300);
        } else {
            console.log('ℹ️ No DOB found on page load');
        }
        
        // Show Guardian ID section if validation error exists
        @if($errors->has('guardian_id_document'))
            console.log('⚠️ Guardian ID validation error detected - showing section');
            $('#guardian_id_document_section').show();
            $('#guardian_id_document').prop('required', true);
        @endif
    });
    
    // GP Consent checkbox toggle - robust implementation with multiple fallbacks
    function toggleGpDetails() {
        try {
            const checkbox = $('#consent_share_with_gp');
            const gpDetailsGroup = $('#gp_details_group');
            const gpDetailsGroupElement = document.getElementById('gp_details_group');
            const gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];
            
            // Check state using multiple methods
            const isChecked = checkbox.length > 0 && (
                checkbox.is(':checked') || 
                checkbox.prop('checked') || 
                checkbox[0].checked ||
                (gpDetailsGroupElement && gpDetailsGroupElement.previousElementSibling && 
                 gpDetailsGroupElement.previousElementSibling.querySelector('input[type="checkbox"]')?.checked)
            );
            
            if (isChecked) {
                // Show using multiple methods for maximum compatibility
                if (gpDetailsGroup.length > 0) {
                    gpDetailsGroup.show();
                    gpDetailsGroup.css('display', 'block');
                    gpDetailsGroup.slideDown(200);
                }
                if (gpDetailsGroupElement) {
                    gpDetailsGroupElement.style.display = 'block';
                    gpDetailsGroupElement.style.visibility = 'visible';
                    gpDetailsGroupElement.style.opacity = '1';
                }
                
                // Set required fields
                gpFields.forEach(function(field) {
                    const fieldEl = $('#' + field);
                    if (fieldEl.length > 0) {
                        fieldEl.prop('required', true);
                    }
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.required = true;
                    }
                });
            } else {
                // Hide using multiple methods
                if (gpDetailsGroup.length > 0) {
                    gpDetailsGroup.slideUp(200);
                    gpDetailsGroup.hide();
                    gpDetailsGroup.css('display', 'none');
                }
                if (gpDetailsGroupElement) {
                    gpDetailsGroupElement.style.display = 'none';
                    gpDetailsGroupElement.style.visibility = 'hidden';
                }
                
                // Remove required and clear fields
                gpFields.forEach(function(field) {
                    const fieldEl = $('#' + field);
                    if (fieldEl.length > 0) {
                        fieldEl.prop('required', false);
                        fieldEl.val('');
                    }
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.required = false;
                        fieldElement.value = '';
                    }
                });
            }
        } catch (error) {
            console.error('Error toggling GP details:', error);
        }
    }
    
    // Handle checkbox change - primary handler
    $('#consent_share_with_gp').on('change', function() {
        toggleGpDetails();
    });
    
    // Handle checkbox click directly - immediate response
    $('#consent_share_with_gp').on('click', function(e) {
        // Immediate check
        toggleGpDetails();
        // Also check after a short delay to catch any state changes
        setTimeout(function() {
            toggleGpDetails();
        }, 50);
    });
    
    // Handle label click - ensure it toggles the checkbox
    $('label[for="consent_share_with_gp"]').on('click', function(e) {
        // Don't prevent default - let label naturally toggle checkbox
        setTimeout(function() {
            toggleGpDetails();
        }, 50);
    });
    
    // Also use vanilla JS as fallback
    const consentCheckbox = document.getElementById('consent_share_with_gp');
    if (consentCheckbox) {
        consentCheckbox.addEventListener('change', toggleGpDetails);
        consentCheckbox.addEventListener('click', function() {
            setTimeout(toggleGpDetails, 50);
        });
    }
    
    // Initialize GP details visibility based on consent checkbox state
    setTimeout(function() {
        if ($('#consent_share_with_gp').is(':checked') || 
            $('#consent_share_with_gp').prop('checked') ||
            (consentCheckbox && consentCheckbox.checked)) {
            const gpDetailsGroup = $('#gp_details_group');
            const gpDetailsGroupElement = document.getElementById('gp_details_group');
            
            if (gpDetailsGroup.length > 0) {
                gpDetailsGroup.show();
                gpDetailsGroup.css('display', 'block');
            }
            if (gpDetailsGroupElement) {
                gpDetailsGroupElement.style.display = 'block';
            }
            
            ['gp_name', 'gp_email', 'gp_phone', 'gp_address'].forEach(function(field) {
                $('#' + field).prop('required', true);
                const fieldElement = document.getElementById(field);
                if (fieldElement) {
                    fieldElement.required = true;
                }
            });
        }
    }, 100);
    
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
