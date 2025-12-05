@extends('admin.layouts.app')

@section('title', 'Add New Patient')

@php
    use Illuminate\Support\Str;
@endphp

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item active">Add New Patient</li>
@endsection

@push('styles')
<style>
.doctor-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.doctor-card-header {
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    border-bottom: 2px solid #e2e8f0;
}

.doctor-card-header h5,
.doctor-card-header h4 {
    color: #1a202c;
    font-weight: 700;
}

.doctor-card-header i {
    color: #1a202c;
}

.doctor-card-body {
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


.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #fff;
    border: 1px solid #e3e6f0;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
    font-weight: 600;
    font-size: 0.95rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
    font-size: 0.9rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Patient</h5>
        <small class="text-muted">Create a new patient profile</small>
        <p class="page-subtitle text-muted">Register a new patient with complete medical and personal information</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createPatientForm" method="POST" action="{{ contextRoute('patients.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Personal Information Section -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                        <small class="opacity-75">Basic personal details and contact information</small>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name" class="form-label">
                                        <i class="fas fa-user me-1"></i>First Name *
                                    </label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" 
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
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" 
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
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="patient@hospital.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Phone Number *
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="+000 123 456 789" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="patient_id" class="form-label">
                                        <i class="fas fa-id-badge me-1"></i>Patient ID
                                    </label>
                                    <input type="text" class="form-control @error('patient_id') is-invalid @enderror" 
                                           id="patient_id" name="patient_id" value="{{ old('patient_id', 'PAT-' . strtoupper(Str::random(6))) }}" 
                                           placeholder="Auto-generated" readonly>
                                    <div class="form-help">Auto-generated unique patient ID</div>
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
                                           value="{{ old('date_of_birth') ? formatDate(old('date_of_birth')) : '' }}"
                                           placeholder="dd-mm-yyyy"
                                           pattern="\d{2}-\d{2}-\d{4}"
                                           maxlength="10" required>
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-calculator me-1"></i>Calculated Age
                                    </label>
                                    <input type="text" class="form-control bg-light" id="calculated_age_display"
                                           value="Enter date of birth first" readonly>
                                    <small class="form-text text-muted">Auto-calculated: Years and months</small>
                                </div>

                                <!-- Inline Age Calculator - No jQuery dependency -->
                                <script>
                                (function() {
                                    function calcAge() {
                                        var dob = document.getElementById('date_of_birth');
                                        var ageDisp = document.getElementById('calculated_age_display');
                                        var guardianAlert = document.getElementById('guardian_required_alert');
                                        var guardianStar = document.getElementById('guardian_required_star');
                                        var guardianOptText = document.getElementById('guardian_optional_text');
                                        var guardianInput = document.getElementById('guardian_id_document');

                                        if (!dob || !ageDisp) return;

                                        var val = dob.value;
                                        if (!val || val.trim() === '') {
                                            ageDisp.value = 'Enter date of birth first';
                                            return;
                                        }

                                        // Handle dd-mm-yyyy format (admin form uses text input with mask)
                                        var birthDate;
                                        if (val.match(/^\d{2}-\d{2}-\d{4}$/)) {
                                            var parts = val.split('-');
                                            birthDate = new Date(parts[2], parts[1] - 1, parts[0]);
                                        } else if (val.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                            birthDate = new Date(val);
                                        } else {
                                            ageDisp.value = 'Invalid date format';
                                            return;
                                        }

                                        if (isNaN(birthDate.getTime())) {
                                            ageDisp.value = 'Invalid date';
                                            return;
                                        }

                                        var today = new Date();
                                        var years = today.getFullYear() - birthDate.getFullYear();
                                        var months = today.getMonth() - birthDate.getMonth();

                                        if (months < 0) { years--; months += 12; }
                                        if (today.getDate() < birthDate.getDate()) {
                                            months--;
                                            if (months < 0) { years--; months += 12; }
                                        }

                                        if (years < 0) {
                                            ageDisp.value = 'Invalid (future date)';
                                            return;
                                        }

                                        ageDisp.value = years + ' years, ' + months + ' months';
                                        ageDisp.style.color = '#28a745';
                                        ageDisp.style.fontWeight = '600';

                                        // Toggle guardian requirement
                                        if (years < 18) {
                                            if (guardianAlert) guardianAlert.style.display = 'flex';
                                            if (guardianStar) guardianStar.style.display = 'inline';
                                            if (guardianOptText) guardianOptText.style.display = 'none';
                                            if (guardianInput) { guardianInput.required = true; guardianInput.style.borderColor = '#ffc107'; }

                                            // Show toast notification (with SweetAlert2 check)
                                            if (typeof Swal !== 'undefined') {
                                                Swal.fire({
                                                    icon: 'info',
                                                    title: 'Guardian ID Required',
                                                    text: 'Patient is ' + years + ' years, ' + months + ' months old (under 18). Guardian ID document is required.',
                                                    timer: 3500,
                                                    showConfirmButton: false,
                                                    toast: true,
                                                    position: 'top-end'
                                                });
                                            }
                                        } else {
                                            if (guardianAlert) guardianAlert.style.display = 'none';
                                            if (guardianStar) guardianStar.style.display = 'none';
                                            if (guardianOptText) guardianOptText.style.display = 'inline';
                                            if (guardianInput) { guardianInput.required = false; guardianInput.style.borderColor = ''; }
                                        }
                                    }

                                    // Attach events
                                    var dobField = document.getElementById('date_of_birth');
                                    if (dobField) {
                                        dobField.addEventListener('change', calcAge);
                                        dobField.addEventListener('input', calcAge);
                                        dobField.addEventListener('blur', calcAge);
                                        dobField.addEventListener('keyup', calcAge);
                                    }

                                    // Run on load
                                    calcAge();
                                    setTimeout(calcAge, 100);
                                    setTimeout(calcAge, 500);
                                    setTimeout(calcAge, 1000);
                                    window.addEventListener('load', function() { setTimeout(calcAge, 100); });
                                })();
                                </script>

                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars me-1"></i>Gender *
                                    </label>
                                    <select class="form-control @error('gender') is-invalid @enderror" 
                                            id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
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

                                <div class="form-group">
                                    <label for="emergency_contact_name" class="form-label">
                                        <i class="fas fa-user-friends me-1"></i>Emergency Contact Name
                                    </label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                           id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" 
                                           placeholder="Enter emergency contact name">
                                    @error('emergency_contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="emergency_contact_phone" class="form-label">
                                        <i class="fas fa-phone-alt me-1"></i>Emergency Contact Phone
                                    </label>
                                    <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                           id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" 
                                           placeholder="+000 123 456 789">
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
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                            {{ (old('department_ids') && in_array($department->id, old('department_ids'))) || 
                                               (old('department_id') == $department->id) ? 'selected' : '' }}>
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
                        <input type="hidden" id="department_id" name="department_id" value="">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Address Line 1
                                    </label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror"
                                           id="address" name="address" value="{{ old('address') }}"
                                           placeholder="House number and street name">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address_line_2" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Address Line 2 <small class="text-muted">(Optional)</small>
                                    </label>
                                    <input type="text" class="form-control @error('address_line_2') is-invalid @enderror"
                                           id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}"
                                           placeholder="Apartment, suite, unit, etc.">
                                    @error('address_line_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="city" class="form-label">
                                        <i class="fas fa-city me-1"></i>Town/City
                                    </label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                           id="city" name="city" value="{{ old('city') }}"
                                           placeholder="Enter town or city">
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
                                           id="state" name="state" value="{{ old('state') }}"
                                           placeholder="Enter county">
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
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}"
                                           placeholder="e.g. SW1A 1AA" style="text-transform: uppercase;">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Identification Documents Section -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Identification Documents</h5>
                        <small class="opacity-75">Upload patient and guardian identification documents</small>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-group">
                            <label for="patient_id_document" class="form-label">
                                <i class="fas fa-file-pdf me-1"></i>Patient ID Document
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

                        <div class="form-group" id="guardian_id_document_group">
                            <div id="guardian_required_alert" class="alert alert-warning d-flex align-items-center mb-3" role="alert" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                                <div>
                                    <strong>Guardian ID Document Required</strong><br>
                                    <small>Patient is under 18 years old. Please upload parent/guardian identification document.</small>
                                </div>
                            </div>
                            <label for="guardian_id_document" class="form-label">
                                <i class="fas fa-file-pdf me-1"></i>Parent/Guardian ID Document
                                <span id="guardian_required_star" class="text-danger" style="display: none;">*</span>
                                <small id="guardian_optional_text" class="text-muted">(Optional - Required for under 18)</small>
                            </label>
                            <input type="file" class="form-control @error('guardian_id_document') is-invalid @enderror"
                                   id="guardian_id_document" name="guardian_id_document"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                Accepted formats: PDF, JPG, JPEG, PNG (Max 5MB). Required if patient is under 18.
                            </small>
                            @error('guardian_id_document')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> All uploaded documents are stored securely and can only be accessed by authorized staff (Admin and Doctors).
                        </div>
                    </div>
                </div>

                <!-- GP Consent & Details Section -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="mb-0"><i class="fas fa-user-md me-2"></i>GP Consent & Details</h5>
                        <small class="opacity-75">GP sharing consent and contact information</small>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-group">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" 
                                       id="consent_share_with_gp" name="consent_share_with_gp" value="1" 
                                       {{ old('consent_share_with_gp') ? 'checked' : '' }}
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

                        <div id="gp_details_group" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gp_name" class="form-label">
                                            <i class="fas fa-user-md me-1"></i>GP Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('gp_name') is-invalid @enderror" 
                                               id="gp_name" name="gp_name" value="{{ old('gp_name') }}" 
                                               placeholder="Enter GP full name">
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
                                               id="gp_email" name="gp_email" value="{{ old('gp_email') }}" 
                                               placeholder="gp@example.com">
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
                                               id="gp_phone" name="gp_phone" value="{{ old('gp_phone') }}" 
                                               placeholder="+000 123 456 789">
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
                                                  placeholder="Enter GP clinic address">{{ old('gp_address') }}</textarea>
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
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Medical Information</h5>
                        <small class="opacity-75">Medical history, allergies, and insurance details</small>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="insurance_provider" class="form-label">
                                        <i class="fas fa-shield-alt me-1"></i>Insurance Provider
                                    </label>
                                    <input type="text" class="form-control @error('insurance_provider') is-invalid @enderror" 
                                           id="insurance_provider" name="insurance_provider" value="{{ old('insurance_provider') }}" 
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
                                           id="insurance_number" name="insurance_number" value="{{ old('insurance_number') }}" 
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

                        <div class="form-group">
                            <label for="medical_history" class="form-label">
                                <i class="fas fa-file-medical me-1"></i>Medical History
                            </label>
                            <textarea class="form-control @error('medical_history') is-invalid @enderror" 
                                      id="medical_history" name="medical_history" rows="4" 
                                      placeholder="Enter patient's medical history">{{ old('medical_history') }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-toggle-on me-1"></i>Active Patient
                                </label>
                            </div>
                            <div class="form-help">Check to activate patient record</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="doctor-card">
                    <div class="doctor-card-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Create Patient
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
                <h6><i class="fas fa-info-circle me-2"></i>Patient Registration Guidelines</h6>
                <ul>
                    <li>All fields marked with * are required</li>
                    <li>Patient ID will be auto-generated</li>
                    <li>Ensure accurate medical information</li>
                    <li>Emergency contact is highly recommended</li>
                    <li>Blood group helps in emergency situations</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                <ul>
                    <li>Double-check contact information</li>
                    <li>Verify insurance details for billing</li>
                    <li>Document known allergies carefully</li>
                    <li>Keep medical history comprehensive</li>
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
                    <a href="{{ contextRoute('patients.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Patients
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="generate-new-id">
                        <i class="fas fa-refresh me-1"></i>Generate New ID
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" id="validate-form">
                        <i class="fas fa-check me-1"></i>Validate Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
    
    // Initialize department_id from old input if present
    @if(old('department_ids') && count(old('department_ids')) > 0)
        $('#department_id').val({{ old('department_ids')[0] }});
    @elseif(old('department_id'))
        $('#department_id').val({{ old('department_id') }});
    @endif
    
    // Add allergy functionality
    $('#add-allergy').click(function() {
        const allergyHtml = `
            <div class="input-group mb-2 allergy-item">
                <input type="text" 
                       class="form-control" 
                       name="allergies[]" 
                       placeholder="Enter allergy">
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

    // Age calculator with years and months - Auto-calculates on change and page load
    function calculateAgeAndToggleGuardian() {
        var dobElement = document.getElementById('date_of_birth');
        var ageDisplayElement = document.getElementById('calculated_age_display');

        if (!dobElement || !ageDisplayElement) {
            console.error('Required elements not found');
            return;
        }

        const dateStr = dobElement.value;
        const ageDisplay = $('#calculated_age_display');
        const guardianInput = $('#guardian_id_document');
        const guardianAlert = $('#guardian_required_alert');
        const guardianStar = $('#guardian_required_star');
        const guardianOptionalText = $('#guardian_optional_text');
        const dobField = $('#date_of_birth');

        console.log('calculateAgeAndToggleGuardian called, DOB value:', dateStr);

        if (!dateStr || dateStr.trim() === '') {
            ageDisplay.val('Enter date of birth first').removeClass('text-danger text-success');
            // Reset guardian field to optional state
            guardianAlert.hide();
            guardianStar.hide();
            guardianOptionalText.show();
            guardianInput.prop('required', false).removeClass('border-warning');
            return;
        }

        // Handle both dd-mm-yyyy and yyyy-mm-dd formats
        let birthDate;
        if (dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
            // dd-mm-yyyy format
            const parts = dateStr.split('-');
            birthDate = new Date(parts[2], parts[1] - 1, parts[0]);
            console.log('Parsed dd-mm-yyyy:', parts[2], parts[1] - 1, parts[0]);
        } else if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
            // yyyy-mm-dd format
            birthDate = new Date(dateStr);
        } else {
            console.log('Date format not recognized:', dateStr);
            ageDisplay.val('Invalid date format').removeClass('text-success').addClass('text-danger');
            return;
        }

        if (!birthDate || isNaN(birthDate.getTime())) {
            ageDisplay.val('Invalid date').removeClass('text-success').addClass('text-danger');
            return;
        }

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

        console.log('Age Calculated:', years, 'years', months, 'months from DOB:', dateStr);

        // Validate date
        if (years < 0) {
            // Date is in the future
            ageDisplay.val('Invalid (future date)').removeClass('text-success').addClass('text-danger');
            dobField.addClass('is-invalid');
            guardianAlert.hide();
            guardianStar.hide();
            guardianOptionalText.show();
            guardianInput.prop('required', false).removeClass('border-warning');
            return;
        } else if (years > 150) {
            // Age too high
            ageDisplay.val('Invalid (age > 150)').removeClass('text-success').addClass('text-danger');
            dobField.addClass('is-invalid');
            return;
        } else {
            // Valid date - show age with years and months
            const ageText = years + ' years, ' + months + ' months';
            ageDisplay.val(ageText).removeClass('text-danger').addClass('text-success');
            dobField.removeClass('is-invalid');
        }

        // Toggle Guardian ID required status based on age (field always visible)
        if (years < 18) {
            console.log('Patient is under 18 - Guardian ID REQUIRED');
            guardianAlert.slideDown(300).css('display', 'flex');
            guardianStar.show();
            guardianOptionalText.hide();
            guardianInput.prop('required', true).addClass('border-warning');

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
            console.log('Patient is 18 or older - Guardian ID optional');
            guardianAlert.slideUp(300);
            guardianStar.hide();
            guardianOptionalText.show();
            guardianInput.prop('required', false).removeClass('border-warning');
        }
    }

    // Attach event handlers to Date of Birth - multiple events for text input
    var dobInput = document.getElementById('date_of_birth');

    if (dobInput) {
        // jQuery event handlers
        $('#date_of_birth').on('change input blur keyup', function() {
            // Only calculate if we have a complete date (10 chars for dd-mm-yyyy) or empty
            const val = $(this).val();
            console.log('DOB input event, value length:', val.length);
            if (val.length === 10 || val.length === 0) {
                calculateAgeAndToggleGuardian();
            }
        });

        // Native event listeners as backup
        dobInput.addEventListener('change', calculateAgeAndToggleGuardian);
        dobInput.addEventListener('blur', calculateAgeAndToggleGuardian);

        // AUTO-CALCULATE age on page load
        console.log('Page loaded - DOB field found, initializing age calculation');
        console.log('Current DOB value:', dobInput.value);

        // Run calculation immediately
        calculateAgeAndToggleGuardian();

        // Also run after delays to ensure DOM is ready
        setTimeout(calculateAgeAndToggleGuardian, 100);
        setTimeout(calculateAgeAndToggleGuardian, 300);
        setTimeout(calculateAgeAndToggleGuardian, 500);
        setTimeout(calculateAgeAndToggleGuardian, 1000);
    } else {
        console.error('Date of birth field not found!');
    }

    // Additional fallback - run on window load event for production
    $(window).on('load', function() {
        setTimeout(calculateAgeAndToggleGuardian, 100);
    });

    // Show Guardian ID required state if validation error exists
    @if($errors->has('guardian_id_document'))
        console.log('Guardian ID validation error detected');
        $('#guardian_required_alert').show().css('display', 'flex');
        $('#guardian_required_star').show();
        $('#guardian_optional_text').hide();
        $('#guardian_id_document').prop('required', true).addClass('border-warning');
    @endif

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

    // Contact summary update
    function updateContactSummary() {
        $('#email-display').text($('#email').val() || '-');
        $('#phone-display').text($('#phone').val() || '-');
        const emergencyName = $('#emergency_contact_name').val();
        const emergencyPhone = $('#emergency_contact_phone').val();
        if (emergencyName || emergencyPhone) {
            $('#emergency-display').text(`${emergencyName || 'N/A'} (${emergencyPhone || 'N/A'})`);
        } else {
            $('#emergency-display').text('-');
        }
    }

    $('#email, #phone, #emergency_contact_name, #emergency_contact_phone').on('input', updateContactSummary);

    // Generate new patient ID
    $('#generate-new-id').click(function() {
        const newId = 'PAT-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        $('#patient_id').val(newId);
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
                        fieldEl.val(''); // Clear values when consent is unchecked
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

    // Form validation
    $('#validate-form').click(function() {
        let errors = [];
        
        if (!$('#first_name').val()) errors.push('First name is required');
        if (!$('#last_name').val()) errors.push('Last name is required');
        if (!$('#email').val()) errors.push('Email is required');
        if (!$('#phone').val()) errors.push('Phone is required');
        if (!$('#date_of_birth').val()) errors.push('Date of birth is required');
        if (!$('#gender').val()) errors.push('Gender is required');
        
        // Check guardian ID if patient is under 18
        if ($('#guardian_id_document').prop('required') && !$('#guardian_id_document').val()) {
            errors.push('Guardian ID document is required for patients under 18');
        }
        
        // Check GP fields if consent is checked
        if ($('#consent_share_with_gp').is(':checked')) {
            if (!$('#gp_name').val()) errors.push('GP Name is required when consent is checked');
            if (!$('#gp_email').val()) errors.push('GP Email is required when consent is checked');
            if (!$('#gp_phone').val()) errors.push('GP Phone is required when consent is checked');
            if (!$('#gp_address').val()) errors.push('GP Address is required when consent is checked');
        }
        
        if (errors.length > 0) {
            alert('Please fix the following errors:\n' + errors.join('\n'));
        } else {
            alert('Form validation passed!');
        }
    });
});
</script>
@endpush
