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
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
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

                        <div class="form-group">
                            <label for="address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Address
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" 
                                      placeholder="Enter complete address">{{ old('address') }}</textarea>
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
                                           id="city" name="city" value="{{ old('city') }}" 
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
                                           id="state" name="state" value="{{ old('state') }}" 
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
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}" 
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

                        <div class="form-group" id="guardian_id_document_group" style="display: none;">
                            <label for="guardian_id_document" class="form-label">
                                <i class="fas fa-file-pdf me-1"></i>Parent/Guardian ID Document <span class="text-danger">*</span>
                                <small class="text-muted">(Required if patient is under 18)</small>
                            </label>
                            <input type="file" class="form-control @error('guardian_id_document') is-invalid @enderror" 
                                   id="guardian_id_document" name="guardian_id_document" 
                                   accept=".pdf,.jpg,.jpeg,.png">
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
                                       {{ old('consent_share_with_gp') ? 'checked' : '' }}>
                                <label class="form-check-label" for="consent_share_with_gp">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <strong>I consent for you to share information with my GP.</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                By checking this box, you authorize the hospital to share your medical information with your GP.
                            </small>
                        </div>

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

    // Age calculator and guardian ID requirement
    function calculateAgeAndToggleGuardian() {
        const dateStr = $('#date_of_birth').val();
        const guardianGroup = $('#guardian_id_document_group');
        const guardianInput = $('#guardian_id_document');
        
        if (dateStr) {
            // Handle both dd-mm-yyyy and yyyy-mm-dd formats
            let dob;
            if (dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                // dd-mm-yyyy format
                const parts = dateStr.split('-');
                dob = new Date(parts[2], parts[1] - 1, parts[0]);
            } else if (dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                // yyyy-mm-dd format (from date input)
                dob = new Date(dateStr);
            }
            
            if (dob && !isNaN(dob.getTime())) {
                const today = new Date();
                const age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
                
                // Show/hide guardian ID document field based on age
                if (age < 18) {
                    guardianGroup.slideDown();
                    guardianInput.prop('required', true);
                } else {
                    guardianGroup.slideUp();
                    guardianInput.prop('required', false);
                    guardianInput.val(''); // Clear value if not required
                }
                
                if ($('#age-display').length) {
                    $('#age-display').html(`<strong>${age} years old</strong>`);
                }
            }
        } else {
            guardianGroup.slideUp();
            guardianInput.prop('required', false);
            if ($('#age-display').length) {
                $('#age-display').html('<span class="text-muted">Select date of birth</span>');
            }
        }
    }
    
    $('#date_of_birth').on('change input', calculateAgeAndToggleGuardian);
    
    // Calculate age on page load if date is already set
    if ($('#date_of_birth').val()) {
        calculateAgeAndToggleGuardian();
    }

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

    // GP Consent checkbox toggle
    $('#consent_share_with_gp').on('change', function() {
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
                $('#' + field).val(''); // Clear values when consent is unchecked
            });
        }
    });
    
    // Initialize GP details visibility based on consent checkbox state
    if ($('#consent_share_with_gp').is(':checked')) {
        $('#gp_details_group').show();
        ['gp_name', 'gp_email', 'gp_phone', 'gp_address'].forEach(function(field) {
            $('#' + field).prop('required', true);
        });
    }

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
        if ($('#guardian_id_document_group').is(':visible') && !$('#guardian_id_document').val()) {
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
