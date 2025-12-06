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
.fade-in-up {
    animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.doctor-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.doctor-card-header {
    background: linear-gradient(135deg, #f8f9fc 0%, #eef0f5 100%);
    color: #2d3748;
    padding: 1.25rem 1.5rem;
    border-bottom: 2px solid #e2e8f0;
}

.doctor-card-header h5 {
    color: #1a202c;
    font-weight: 700;
    margin-bottom: 0;
}

.doctor-card-header i {
    color: var(--primary-color, #4e73df);
}

.doctor-card-body {
    padding: 1.5rem;
}

.doctor-card.border-primary {
    border-color: var(--primary-color, #4e73df) !important;
    border-width: 2px;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.input-group-text {
    min-width: 45px;
    justify-content: center;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.input-group:focus-within .input-group-text {
    border-color: var(--primary-color, #80bdff);
    background-color: #e7f1ff;
}

.form-control, .form-select {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.65rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color, #4e73df);
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
}

.btn-doctor-primary {
    background: linear-gradient(135deg, var(--primary-color, #4e73df) 0%, #224abe 100%);
    border: none;
    color: #fff;
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-doctor-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(78, 115, 223, 0.35);
    color: #fff;
}

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

/* Calculated Age Display */
#calculated_age_display.text-success {
    color: #28a745 !important;
    font-weight: 600;
}

#calculated_age_display.text-danger {
    color: #dc3545 !important;
    font-weight: 600;
}

/* Info Card Styling */
.card.shadow-sm {
    border-radius: 12px;
    border: 1px solid #e3e6f0;
}

.card-header.bg-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;
    border-radius: 12px 12px 0 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid fade-in-up">

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

    <form action="{{ contextRoute('patients.store') }}" method="POST" id="patientCreateForm" enctype="multipart/form-data">
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
                                           value="{{ old('patient_id', 'PAT-' . strtoupper(Str::random(6))) }}"
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
                                    Auto-calculated: Years, months, and days
                                </small>
                            </div>
                        </div>

                        <!-- Inline Age Calculator - No jQuery dependency -->
                        <script>
                        (function() {
                            function calcAge() {
                                var dob = document.getElementById('date_of_birth');
                                var ageDisp = document.getElementById('calculated_age_display');

                                if (!dob || !ageDisp) return;

                                var val = dob.value;
                                if (!val || val.trim() === '') {
                                    ageDisp.value = 'Enter date of birth first';
                                    return;
                                }

                                var birthDate = new Date(val);
                                if (isNaN(birthDate.getTime())) {
                                    ageDisp.value = 'Invalid date';
                                    return;
                                }

                                var today = new Date();
                                var years = today.getFullYear() - birthDate.getFullYear();
                                var months = today.getMonth() - birthDate.getMonth();
                                var days = today.getDate() - birthDate.getDate();

                                if (days < 0) {
                                    months--;
                                    var prevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                                    days += prevMonth.getDate();
                                }
                                if (months < 0) { years--; months += 12; }

                                if (years < 0) {
                                    ageDisp.value = 'Invalid (future date)';
                                    return;
                                }

                                ageDisp.value = years + ' years, ' + months + ' months, ' + days + ' days';
                                ageDisp.style.color = '#28a745';
                                ageDisp.style.fontWeight = '600';
                            }

                            // Attach events
                            var dobField = document.getElementById('date_of_birth');
                            if (dobField) {
                                dobField.addEventListener('change', calcAge);
                                dobField.addEventListener('input', calcAge);
                                dobField.addEventListener('blur', calcAge);
                            }

                            // Run on load
                            calcAge();
                            setTimeout(calcAge, 100);
                            setTimeout(calcAge, 500);
                            setTimeout(calcAge, 1000);
                            window.addEventListener('load', function() { setTimeout(calcAge, 100); });
                        })();
                        </script>

                        <!-- Guardian ID Document (Always visible in this card, required for Under 18) -->
                        <hr class="my-3">
                        <div id="guardian_id_document_section">
                            <div id="guardian_required_alert" class="alert alert-warning d-flex align-items-center mb-3" role="alert" style="display: none !important;">
                                <i class="fas fa-exclamation-triangle me-2 fa-2x"></i>
                                <div>
                                    <strong>Guardian ID Document Required</strong><br>
                                    <small>Patient is under 18 years old. Please upload parent/guardian identification document.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-0">
                                    <label for="guardian_id_document" class="form-label fw-semibold">
                                        <i class="fas fa-user-shield me-1"></i>Guardian ID Document
                                        <span id="guardian_required_star" class="text-danger" style="display: none;">*</span>
                                        <small id="guardian_optional_text" class="text-muted">(Optional - Required for under 18)</small>
                                    </label>
                                    <input type="file" name="guardian_id_document" id="guardian_id_document"
                                           class="form-control @error('guardian_id_document') is-invalid @enderror"
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <small id="guardian_help_text" class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Accepted formats: PDF, JPG, PNG (Max 5MB). Required if patient is under 18.
                                    </small>
                                    @error('guardian_id_document')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Age Check Script - MUST be after guardian elements -->
                        <script>
                        (function() {
                            var lastShownAge = null;

                            function toggleGuardian() {
                                var dob = document.getElementById('date_of_birth');
                                var guardianAlert = document.getElementById('guardian_required_alert');
                                var guardianStar = document.getElementById('guardian_required_star');
                                var guardianOptText = document.getElementById('guardian_optional_text');
                                var guardianInput = document.getElementById('guardian_id_document');

                                if (!dob || !guardianAlert) return;

                                var val = dob.value;
                                if (!val || val.trim() === '') {
                                    // No DOB - hide alert, not required
                                    guardianAlert.style.cssText = 'display: none !important;';
                                    if (guardianStar) guardianStar.style.display = 'none';
                                    if (guardianOptText) guardianOptText.style.display = 'inline';
                                    if (guardianInput) { guardianInput.required = false; guardianInput.style.borderColor = ''; }
                                    return;
                                }

                                var birthDate = new Date(val);
                                if (isNaN(birthDate.getTime())) return;

                                var today = new Date();
                                var years = today.getFullYear() - birthDate.getFullYear();
                                var months = today.getMonth() - birthDate.getMonth();
                                if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
                                    years--;
                                }

                                if (years < 0) return; // Future date

                                if (years < 18) {
                                    // Under 18 - show alert, required
                                    guardianAlert.style.cssText = 'display: flex !important;';
                                    if (guardianStar) guardianStar.style.display = 'inline';
                                    if (guardianOptText) guardianOptText.style.display = 'none';
                                    if (guardianInput) { guardianInput.required = true; guardianInput.style.borderColor = '#ffc107'; }

                                    // Show toast only once per age change
                                    if (lastShownAge !== years && typeof Swal !== 'undefined') {
                                        lastShownAge = years;
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Guardian ID Required',
                                            text: 'Patient is ' + years + ' years old (under 18). Guardian ID document is required.',
                                            timer: 3500,
                                            showConfirmButton: false,
                                            toast: true,
                                            position: 'top-end'
                                        });
                                    }
                                } else {
                                    // 18 or over - hide alert, not required
                                    guardianAlert.style.cssText = 'display: none !important;';
                                    if (guardianStar) guardianStar.style.display = 'none';
                                    if (guardianOptText) guardianOptText.style.display = 'inline';
                                    if (guardianInput) { guardianInput.required = false; guardianInput.style.borderColor = ''; }
                                    lastShownAge = null;
                                }
                            }

                            // Attach to DOB field
                            var dobField = document.getElementById('date_of_birth');
                            if (dobField) {
                                dobField.addEventListener('change', toggleGuardian);
                                dobField.addEventListener('input', toggleGuardian);
                                dobField.addEventListener('blur', toggleGuardian);
                            }

                            // Run on load
                            toggleGuardian();
                            setTimeout(toggleGuardian, 100);
                            setTimeout(toggleGuardian, 500);
                            setTimeout(toggleGuardian, 1000);
                            window.addEventListener('load', function() { setTimeout(toggleGuardian, 200); });
                        })();
                        </script>
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
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label fw-semibold">Address Line 1</label>
                                <input type="text" name="address" id="address"
                                       class="form-control @error('address') is-invalid @enderror"
                                       value="{{ old('address') }}" placeholder="House number and street name">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address_line_2" class="form-label fw-semibold">Address Line 2 <small class="text-muted">(Optional)</small></label>
                                <input type="text" name="address_line_2" id="address_line_2"
                                       class="form-control @error('address_line_2') is-invalid @enderror"
                                       value="{{ old('address_line_2') }}" placeholder="Apartment, suite, unit, etc.">
                                @error('address_line_2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label fw-semibold">Town/City</label>
                                <input type="text" name="city" id="city"
                                       class="form-control @error('city') is-invalid @enderror"
                                       value="{{ old('city') }}" placeholder="Enter town or city">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label fw-semibold">County</label>
                                <input type="text" name="state" id="state"
                                       class="form-control @error('state') is-invalid @enderror"
                                       value="{{ old('state') }}" placeholder="Enter county">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label fw-semibold">Postcode</label>
                                <div class="input-group">
                                    <input type="text" name="postal_code" id="postal_code"
                                           class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code') }}" placeholder="e.g. SW1A 1AA"
                                           style="text-transform: uppercase;">
                                    <button type="button" class="btn btn-outline-primary" id="postcode_lookup_btn">
                                        <i class="fas fa-search"></i> Lookup
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Enter postcode and click Lookup to auto-fill address
                                </small>
                                @error('postal_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Postcode Lookup Script -->
                        <script>
                        (function() {
                            var lookupBtn = document.getElementById('postcode_lookup_btn');
                            var postcodeInput = document.getElementById('postal_code');

                            if (lookupBtn && postcodeInput) {
                                lookupBtn.addEventListener('click', function() {
                                    var postcode = postcodeInput.value.trim().replace(/\s/g, '');
                                    if (!postcode) {
                                        alert('Please enter a postcode');
                                        return;
                                    }

                                    // Show loading state
                                    lookupBtn.disabled = true;
                                    lookupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Looking up...';

                                    // Use Postcodes.io API (free, no API key required)
                                    fetch('https://api.postcodes.io/postcodes/' + encodeURIComponent(postcode))
                                        .then(function(response) { return response.json(); })
                                        .then(function(data) {
                                            if (data.status === 200 && data.result) {
                                                var result = data.result;

                                                // Fill in address fields
                                                var cityField = document.getElementById('city');
                                                var stateField = document.getElementById('state');

                                                if (cityField) {
                                                    cityField.value = result.admin_district || result.post_town || '';
                                                }
                                                if (stateField) {
                                                    stateField.value = result.admin_county || result.region || '';
                                                }

                                                postcodeInput.value = result.postcode || postcode.toUpperCase();

                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        icon: 'success',
                                                        title: 'Address Found',
                                                        text: 'Town/City and County have been filled in.',
                                                        timer: 2500,
                                                        showConfirmButton: false,
                                                        toast: true,
                                                        position: 'top-end'
                                                    });
                                                }
                                            } else {
                                                if (typeof Swal !== 'undefined') {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Postcode Not Found',
                                                        text: 'Please check the postcode and try again.',
                                                        timer: 3000,
                                                        showConfirmButton: false,
                                                        toast: true,
                                                        position: 'top-end'
                                                    });
                                                } else {
                                                    alert('Postcode not found. Please check and try again.');
                                                }
                                            }
                                        })
                                        .catch(function(error) {
                                            console.error('Postcode lookup error:', error);
                                            if (typeof Swal !== 'undefined') {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Lookup Failed',
                                                    text: 'Unable to lookup postcode. Please enter address manually.',
                                                    timer: 3000,
                                                    showConfirmButton: false,
                                                    toast: true,
                                                    position: 'top-end'
                                                });
                                            } else {
                                                alert('Unable to lookup postcode. Please enter address manually.');
                                            }
                                        })
                                        .finally(function() {
                                            lookupBtn.disabled = false;
                                            lookupBtn.innerHTML = '<i class="fas fa-search"></i> Lookup';
                                        });
                                });

                                postcodeInput.addEventListener('keypress', function(e) {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        lookupBtn.click();
                                    }
                                });
                            }
                        })();
                        </script>
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

                <!-- Clinic Assignment -->
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
                                    <strong>No Clinics Available:</strong> No clinics/departments have been created yet.
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
                        function handleGpConsentChange(checkbox) {
                            var gpGroup = document.getElementById('gp_details_group');
                            var gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];

                            if (!gpGroup) return;

                            var isChecked = checkbox && (checkbox.checked || checkbox.getAttribute('checked') !== null);

                            if (isChecked) {
                                gpGroup.style.display = 'block';
                                gpGroup.style.visibility = 'visible';
                                gpGroup.style.opacity = '1';
                                gpGroup.removeAttribute('style');
                                gpGroup.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');

                                gpFields.forEach(function(fieldId) {
                                    var field = document.getElementById(fieldId);
                                    if (field) {
                                        field.required = true;
                                        field.setAttribute('required', 'required');
                                    }
                                });
                            } else {
                                gpGroup.style.display = 'none';
                                gpGroup.style.visibility = 'hidden';
                                gpGroup.removeAttribute('style');
                                gpGroup.setAttribute('style', 'display: none !important;');

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

                        (function() {
                            var checkbox = document.getElementById('consent_share_with_gp');
                            if (checkbox) {
                                setTimeout(function() {
                                    handleGpConsentChange(checkbox);
                                }, 100);

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
                            <a href="{{ contextRoute('patients.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white py-3">
                        <h6 class="card-title mb-0">Admin Registration Info</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Registration Type</small>
                            <strong>Admin-Assisted</strong>
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

                <!-- Quick Tips -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-info text-white py-3">
                        <h6 class="card-title mb-0"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0 ps-3">
                            <li class="mb-2"><small>All fields marked with <span class="text-danger">*</span> are required</small></li>
                            <li class="mb-2"><small>Patient ID is auto-generated</small></li>
                            <li class="mb-2"><small>Guardian ID is required for patients under 18</small></li>
                            <li class="mb-2"><small>Use postcode lookup to auto-fill address</small></li>
                            <li class="mb-0"><small>GP details are required if consent is checked</small></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

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

    // Sync department_ids to department_id hidden field
    $('#department_ids').on('change', function() {
        const selectedIds = $(this).val();
        if (selectedIds && selectedIds.length > 0) {
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

    // Form validation
    $('#patientCreateForm').on('submit', function(e) {
        let isValid = true;
        let errorMessages = [];

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
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
            return false;
        }

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

    // Age calculation with years and months
    function calculateAgeAndToggleGuardian() {
        var dobElement = document.getElementById('date_of_birth');
        var ageDisplayElement = document.getElementById('calculated_age_display');

        if (!dobElement || !ageDisplayElement) {
            console.error('Required elements not found');
            return;
        }

        const birthDateValue = dobElement.value;
        const ageDisplay = $('#calculated_age_display');
        const guardianInput = $('#guardian_id_document');
        const guardianAlert = $('#guardian_required_alert');
        const guardianStar = $('#guardian_required_star');
        const guardianOptionalText = $('#guardian_optional_text');
        const dobField = $('#date_of_birth');

        if (!birthDateValue || birthDateValue.trim() === '') {
            ageDisplay.val('Enter date of birth first').removeClass('text-danger text-success');
            guardianAlert.hide();
            guardianStar.hide();
            guardianOptionalText.show();
            guardianInput.prop('required', false).removeClass('border-warning');
            return;
        }

        const birthDate = new Date(birthDateValue);
        const today = new Date();

        if (isNaN(birthDate.getTime())) {
            ageDisplay.val('Invalid date format').removeClass('text-success').addClass('text-danger');
            return;
        }

        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        let days = today.getDate() - birthDate.getDate();

        // Adjust days first
        if (days < 0) {
            months--;
            const prevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            days += prevMonth.getDate();
        }

        // Then adjust months
        if (months < 0) {
            years--;
            months += 12;
        }

        if (years < 0) {
            ageDisplay.val('Invalid (future date)').removeClass('text-success').addClass('text-danger');
            dobField.addClass('is-invalid');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date',
                    text: 'Birth date cannot be in the future.',
                    confirmButtonColor: '#d33'
                });
            }

            guardianAlert.hide();
            guardianStar.hide();
            guardianOptionalText.show();
            guardianInput.prop('required', false).removeClass('border-warning');
            return;
        } else if (years > 150) {
            ageDisplay.val('Invalid (age > 150)').removeClass('text-success').addClass('text-danger');
            dobField.addClass('is-invalid');
            return;
        } else {
            const ageText = years + ' years, ' + months + ' months, ' + days + ' days';
            ageDisplay.val(ageText).removeClass('text-danger').addClass('text-success');
            dobField.removeClass('is-invalid');
        }

        if (years < 18) {
            guardianAlert.slideDown(300);
            guardianStar.show();
            guardianOptionalText.hide();
            guardianInput.prop('required', true).addClass('border-warning');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'Guardian ID Required',
                    text: `Patient is ${years} years, ${months} months, ${days} days old (under 18). Guardian ID document is required.`,
                    timer: 3500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        } else {
            guardianAlert.slideUp(300);
            guardianStar.hide();
            guardianOptionalText.show();
            guardianInput.prop('required', false).removeClass('border-warning');
        }
    }

    var dobInput = document.getElementById('date_of_birth');

    if (dobInput) {
        $('#date_of_birth').on('change input blur keyup', function() {
            calculateAgeAndToggleGuardian();
        });

        dobInput.addEventListener('change', calculateAgeAndToggleGuardian);
        dobInput.addEventListener('input', calculateAgeAndToggleGuardian);

        calculateAgeAndToggleGuardian();
        setTimeout(calculateAgeAndToggleGuardian, 100);
        setTimeout(calculateAgeAndToggleGuardian, 300);
        setTimeout(calculateAgeAndToggleGuardian, 500);
        setTimeout(calculateAgeAndToggleGuardian, 1000);
    }

    $(window).on('load', function() {
        setTimeout(calculateAgeAndToggleGuardian, 100);
    });

    @if($errors->has('guardian_id_document'))
        $('#guardian_required_alert').show();
        $('#guardian_required_star').show();
        $('#guardian_optional_text').hide();
        $('#guardian_id_document').prop('required', true).addClass('border-warning');
    @endif

    // GP Consent checkbox toggle
    function toggleGpDetails() {
        try {
            const checkbox = $('#consent_share_with_gp');
            const gpDetailsGroup = $('#gp_details_group');
            const gpDetailsGroupElement = document.getElementById('gp_details_group');
            const gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];

            const isChecked = checkbox.length > 0 && (
                checkbox.is(':checked') ||
                checkbox.prop('checked') ||
                checkbox[0].checked
            );

            if (isChecked) {
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
                if (gpDetailsGroup.length > 0) {
                    gpDetailsGroup.slideUp(200);
                    gpDetailsGroup.hide();
                    gpDetailsGroup.css('display', 'none');
                }
                if (gpDetailsGroupElement) {
                    gpDetailsGroupElement.style.display = 'none';
                    gpDetailsGroupElement.style.visibility = 'hidden';
                }

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

    $('#consent_share_with_gp').on('change', function() {
        toggleGpDetails();
    });

    $('#consent_share_with_gp').on('click', function(e) {
        toggleGpDetails();
        setTimeout(function() {
            toggleGpDetails();
        }, 50);
    });

    $('label[for="consent_share_with_gp"]').on('click', function(e) {
        setTimeout(function() {
            toggleGpDetails();
        }, 50);
    });

    const consentCheckbox = document.getElementById('consent_share_with_gp');
    if (consentCheckbox) {
        consentCheckbox.addEventListener('change', toggleGpDetails);
        consentCheckbox.addEventListener('click', function() {
            setTimeout(toggleGpDetails, 50);
        });
    }

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

    $(document).on('click', '.remove-allergy', function() {
        if ($('.allergy-item').length > 1) {
            $(this).closest('.allergy-item').remove();
        }
    });
});
</script>
@endpush
