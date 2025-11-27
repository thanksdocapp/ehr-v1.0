@extends('admin.layouts.app')

@section('title', 'Edit Patient')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item active">Edit Patient</li>
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
        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Patient</h5>
        <small class="text-muted">Update patient profile information</small>
        <p class="page-subtitle text-muted">Update patient information and medical details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="editPatientForm" method="POST" action="{{ contextRoute('patients.update', $patient->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h4>
                        <small class="opacity-75">Update personal details and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>First Name *
                                    </label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" 
                                           placeholder="Enter first name" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="last_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>Last Name *
                                    </label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" 
                                           placeholder="Enter last name" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address *
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $patient->email) }}" 
                                           placeholder="patient@hospital.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Phone Number
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $patient->phone) }}" 
                                           placeholder="+000 123 456 789">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="patient_id" class="form-label">
                                        <i class="fas fa-id-badge me-1"></i>Patient ID
                                    </label>
                                    <input type="text" class="form-control @error('patient_id') is-invalid @enderror" 
                                           id="patient_id" name="patient_id" value="{{ old('patient_id', $patient->patient_id) }}" 
                                           readonly>
                                    <div class="form-help">Patient ID is read-only</div>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Date of Birth *
                                    </label>
                                    <input type="text" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" 
                                           value="{{ old('date_of_birth', $patient->date_of_birth ? formatDate($patient->date_of_birth) : '') }}" 
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10" required>
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars me-1"></i>Gender *
                                    </label>
                                    <select class="form-control @error('gender') is-invalid @enderror" 
                                            id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="blood_group" class="form-label">
                                        <i class="fas fa-tint me-1"></i>Blood Group
                                    </label>
                                    <select class="form-control @error('blood_group') is-invalid @enderror" 
                                            id="blood_group" name="blood_group">
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

                                <div class="form-group">
                                    <label for="emergency_contact_name" class="form-label">
                                        <i class="fas fa-user-friends me-1"></i>Emergency Contact Name
                                    </label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                           id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}" 
                                           placeholder="Enter emergency contact name">
                                    @error('emergency_contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="emergency_contact_phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Emergency Contact Phone
                                    </label>
                                    <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                           id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}" 
                                           placeholder="Enter emergency contact phone">
                                    @error('emergency_contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="department_ids" class="form-label">
                                <i class="fas fa-building me-1"></i>Assign to Clinic(s)
                            </label>
                            <select class="form-control @error('department_ids') is-invalid @enderror" 
                                    id="department_ids" name="department_ids[]" multiple size="5">
                                @php
                                    $selectedDeptIds = old('department_ids', $patient->getDepartmentIds());
                                    if (!is_array($selectedDeptIds) && $selectedDeptIds) {
                                        $selectedDeptIds = [$selectedDeptIds];
                                    }
                                    if (empty($selectedDeptIds) && old('department_id', $patient->department_id)) {
                                        $selectedDeptIds = [old('department_id', $patient->department_id)];
                                    }
                                @endphp
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                            {{ ($selectedDeptIds && in_array($department->id, $selectedDeptIds)) ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Hold <kbd>Ctrl</kbd> (Windows/Linux) or <kbd>Cmd</kbd> (Mac) to select multiple clinics. First selected clinic will be set as primary.
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

                        <div class="form-group">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Address
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" 
                                      placeholder="Enter complete address">{{ old('address', $patient->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="form-label">
                                        <i class="fas fa-city me-1"></i>City
                                    </label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city', $patient->city) }}" 
                                           placeholder="Enter city">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state" class="form-label">
                                        <i class="fas fa-map me-1"></i>State
                                    </label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $patient->state) }}" 
                                           placeholder="Enter state">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code" class="form-label">
                                        <i class="fas fa-mail-bulk me-1"></i>Postal Code
                                    </label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $patient->postal_code) }}" 
                                           placeholder="Enter postal code">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identification Documents Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Identification Documents</h4>
                        <small class="opacity-75">Upload patient and guardian identification documents</small>
                    </div>
                    <div class="form-section-body">
                        @php
                            $dateOfBirth = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth) : null;
                            $age = $dateOfBirth ? $dateOfBirth->age : null;
                            $isUnder18 = $age !== null && $age < 18;
                        @endphp
                        
                        @if($patient->patient_id_document_path)
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Patient ID Document:</strong> Already uploaded
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label for="patient_id_document" class="form-label">
                                <i class="fas fa-file-pdf me-1"></i>Patient ID Document
                                @if($patient->patient_id_document_path)
                                    <small class="text-muted">(Leave empty to keep current document)</small>
                                @endif
                            </label>
                            <input type="file" class="form-control @error('patient_id_document') is-invalid @enderror" 
                                   id="patient_id_document" name="patient_id_document" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                Accepted formats: PDF, JPG, JPEG, PNG (Max 5MB)
                            </small>
                            @error('patient_id_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" id="guardian_id_document_group" style="display: {{ $isUnder18 ? 'block' : 'none' }};">
                            @if($patient->guardian_id_document_path)
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Guardian ID Document:</strong> Already uploaded
                                </div>
                            @endif
                            
                            <label for="guardian_id_document" class="form-label">
                                <i class="fas fa-file-pdf me-1"></i>Parent/Guardian ID Document 
                                @if($isUnder18)
                                    <span class="text-danger">*</span>
                                @endif
                                <small class="text-muted">(Required if patient is under 18)</small>
                            </label>
                            <input type="file" class="form-control @error('guardian_id_document') is-invalid @enderror" 
                                   id="guardian_id_document" name="guardian_id_document" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   @if($isUnder18 && !$patient->guardian_id_document_path) required @endif>
                            <small class="form-text text-muted">
                                Accepted formats: PDF, JPG, JPEG, PNG (Max 5MB). Required for patients under 18 years of age.
                            </small>
                            @error('guardian_id_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> All uploaded documents are stored securely and can only be accessed by authorized staff (Admin and Doctors).
                        </div>
                    </div>
                </div>

                <!-- GP Consent & Details Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>GP Consent & Details</h4>
                        <small class="opacity-75">GP sharing consent and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       id="consent_share_with_gp" name="consent_share_with_gp" value="1" 
                                       {{ old('consent_share_with_gp', $patient->consent_share_with_gp) ? 'checked' : '' }}
                                       onchange="handleGpConsentChange(this)">
                                <label class="form-check-label" for="consent_share_with_gp" onclick="setTimeout(function(){handleGpConsentChange(document.getElementById('consent_share_with_gp'));}, 10);">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <strong>I consent for you to share information with my GP.</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                By checking this box, you authorize the hospital to share your medical information with your GP.
                            </small>
                        </div>

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

                        <div id="gp_details_group" style="display: {{ old('consent_share_with_gp', $patient->consent_share_with_gp) ? 'block' : 'none' }};">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gp_name" class="form-label">
                                            <i class="fas fa-user-md me-1"></i>GP Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('gp_name') is-invalid @enderror" 
                                               id="gp_name" name="gp_name" 
                                               value="{{ old('gp_name', $patient->gp_name) }}" 
                                               placeholder="Enter GP full name"
                                               @if(old('consent_share_with_gp', $patient->consent_share_with_gp)) required @endif>
                                        @error('gp_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gp_email" class="form-label">
                                            <i class="fas fa-envelope me-1"></i>GP Email <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control @error('gp_email') is-invalid @enderror" 
                                               id="gp_email" name="gp_email" 
                                               value="{{ old('gp_email', $patient->gp_email) }}" 
                                               placeholder="gp@example.com"
                                               @if(old('consent_share_with_gp', $patient->consent_share_with_gp)) required @endif>
                                        @error('gp_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gp_phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>GP Phone Number <span class="text-danger">*</span>
                                        </label>
                                        <input type="tel" class="form-control @error('gp_phone') is-invalid @enderror" 
                                               id="gp_phone" name="gp_phone" 
                                               value="{{ old('gp_phone', $patient->gp_phone) }}" 
                                               placeholder="+000 123 456 789"
                                               @if(old('consent_share_with_gp', $patient->consent_share_with_gp)) required @endif>
                                        @error('gp_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gp_address" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>GP Address <span class="text-danger">*</span>
                                        </label>
                                        <textarea class="form-control @error('gp_address') is-invalid @enderror" 
                                                  id="gp_address" name="gp_address" rows="2" 
                                                  placeholder="Enter GP clinic address"
                                                  @if(old('consent_share_with_gp', $patient->consent_share_with_gp)) required @endif>{{ old('gp_address', $patient->gp_address) }}</textarea>
                                        @error('gp_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Medical Information</h4>
                        <small class="opacity-75">Medical history, allergies, and insurance details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="insurance_provider" class="form-label">
                                        <i class="fas fa-shield-alt me-1"></i>Insurance Provider
                                    </label>
                                    <input type="text" class="form-control @error('insurance_provider') is-invalid @enderror" 
                                           id="insurance_provider" name="insurance_provider" value="{{ old('insurance_provider', $patient->insurance_provider) }}" 
                                           placeholder="Enter insurance provider">
                                    @error('insurance_provider')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="insurance_number" class="form-label">
                                        <i class="fas fa-id-card me-1"></i>Insurance Number
                                    </label>
                                    <input type="text" class="form-control @error('insurance_number') is-invalid @enderror" 
                                           id="insurance_number" name="insurance_number" value="{{ old('insurance_number', $patient->insurance_number) }}" 
                                           placeholder="Enter insurance number">
                                    @error('insurance_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="allergies" class="form-label">
                                <i class="fas fa-exclamation-triangle me-1"></i>Allergies
                            </label>
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

                        <div class="form-group">
                            <label for="medical_history" class="form-label">
                                <i class="fas fa-file-medical me-1"></i>Medical History
                            </label>
                            <textarea class="form-control @error('medical_history') is-invalid @enderror" 
                                      id="medical_history" name="medical_history" rows="4" 
                                      placeholder="Enter patient's medical history">{{ old('medical_history', $patient->medical_history) }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', $patient->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on me-1"></i>Active Patient
                                </label>
                            </div>
                            <div class="form-help">Check to activate patient record</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Patient
                        </button>
                        <a href="{{ contextRoute('patients.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Current Patient Info</h6>
                <ul>
                    <li><strong>Patient ID:</strong> {{ $patient->id }}</li>
                    <li><strong>Registration:</strong> {{ formatDate($patient->created_at) }}</li>
                    <li><strong>Last Update:</strong> {{ formatDateTime($patient->updated_at) }}</li>
                    <li><strong>Current Status:</strong> {{ $patient->is_active ? 'Active' : 'Inactive' }}</li>
                    <li><strong>Blood Group:</strong> {{ $patient->blood_group ?? 'Not set' }}</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Edit Guidelines</h6>
                <ul>
                    <li>Ensure accurate medical information</li>
                    <li>Double-check contact information</li>
                    <li>Verify insurance details for billing</li>
                    <li>Document known allergies carefully</li>
                    <li>Activate patient record when ready</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security</h6>
                <ul>
                    <li><strong>GDPR Compliant:</strong> All data protected</li>
                    <li><strong>Access Control:</strong> Role-based permissions</li>
                    <li><strong>Audit Trail:</strong> All changes tracked</li>
                    <li><strong>Data Backup:</strong> Regular automated backups</li>
                    <li><strong>Encryption:</strong> Data encrypted at rest</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('patients.show', $patient->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>View Patient Profile
                    </a>
                    <a href="{{ contextRoute('patients.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Patients List
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
    // Age calculator and guardian ID requirement
    function calculateAgeAndToggleGuardian() {
        const dateStr = $('#date_of_birth').val();
        const guardianGroup = $('#guardian_id_document_group');
        const guardianInput = $('#guardian_id_document');
        
        if (dateStr) {
            // Handle yyyy-mm-dd format (from date input)
            const dob = new Date(dateStr);
            
            if (dob && !isNaN(dob.getTime())) {
                const today = new Date();
                const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
                
                // Show/hide guardian ID document field based on age
                if (age < 18) {
                    guardianGroup.slideDown();
                    if (!guardianGroup.find('.alert').length) {
                        // Only require if no existing document
                        guardianInput.prop('required', !guardianInput.closest('.form-group').find('.alert').length);
                    }
                } else {
                    guardianGroup.slideUp();
                    guardianInput.prop('required', false);
                }
            }
        } else {
            guardianGroup.slideUp();
            guardianInput.prop('required', false);
        }
    }
    
    $('#date_of_birth').on('change input', calculateAgeAndToggleGuardian);
    
    // Calculate age on page load if date is already set
    if ($('#date_of_birth').val()) {
        calculateAgeAndToggleGuardian();
    }
    
    // GP Consent checkbox toggle - backup handler (primary is inline)
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
                    $('#' + field).val('');
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
    
    // Date input mask for dd-mm-yyyy format
    $('#date_of_birth').on('input', function() {
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
        const dobInput = $('#date_of_birth');
        const dateStr = dobInput.val();
        if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
            const parts = dateStr.split('-');
            const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
            dobInput.val(yyyyMmDd);
        }
    });

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

    // Form validation
    $('#editPatientForm').submit(function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['first_name', 'last_name', 'email', 'date_of_birth', 'gender'];
        
        requiredFields.forEach(function(field) {
            const input = $('#' + field);
            if (!input.val()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush
