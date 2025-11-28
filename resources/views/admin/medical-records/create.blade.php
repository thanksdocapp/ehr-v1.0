@extends('admin.layouts.app')

@section('title', 'Add Medical Record')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.medical-records.index') }}">Medical Records</a></li>
    <li class="breadcrumb-item active">Add Medical Record</li>
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

.vital-signs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-file-medical-alt me-2"></i>Add Medical Record</h5>
        <small class="text-muted">Create a new medical record</small>
        <p class="page-subtitle text-muted">Create a new medical record with diagnosis, treatment, and vital signs</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="createMedicalRecordForm" method="POST" action="{{ route('admin.medical-records.store') }}" enctype="multipart/form-data">
                @csrf
                
                <!-- Patient & Doctor Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                        <small class="opacity-75">Select patient and attending doctor</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>Patient *
                                    </label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" 
                                                    {{ (old('patient_id') ?? $selectedAppointment?->patient_id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->full_name }} ({{ $patient->patient_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="doctor_id" class="form-label">
                                        <i class="fas fa-stethoscope me-1"></i>Doctor *
                                    </label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                            id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" 
                                                    {{ (old('doctor_id') ?? $selectedAppointment?->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                {{ formatDoctorName($doctor->full_name) }} - {{ $doctor->specialization }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_id" class="form-label">
                                        <i class="fas fa-calendar-check me-1"></i>Related Appointment
                                    </label>
                                    <select class="form-control @error('appointment_id') is-invalid @enderror" 
                                            id="appointment_id" name="appointment_id">
                                        <option value="">Select Appointment (Optional)</option>
                                        @if($selectedAppointment)
                                            <option value="{{ $selectedAppointment->id }}" selected>
                                                #{{ $selectedAppointment->appointment_number }} - 
                                                {{ formatDate($selectedAppointment->appointment_date) }}
                                            </option>
                                        @endif
                                    </select>
                                    <div class="form-help">Link this record to a specific appointment</div>
                                    @error('appointment_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="record_type" class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>Record Type *
                                    </label>
                                    <select class="form-control @error('record_type') is-invalid @enderror" 
                                            id="record_type" name="record_type" required>
                                        <option value="">Select Record Type</option>
                                        <option value="consultation" {{ old('record_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                        <option value="diagnosis" {{ old('record_type') == 'diagnosis' ? 'selected' : '' }}>Diagnosis</option>
                                        <option value="prescription" {{ old('record_type') == 'prescription' ? 'selected' : '' }}>Prescription</option>
                                        <option value="lab_result" {{ old('record_type') == 'lab_result' ? 'selected' : '' }}>Lab Result</option>
                                        <option value="follow_up" {{ old('record_type') == 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                                        <option value="discharge" {{ old('record_type') == 'discharge' ? 'selected' : '' }}>Discharge</option>
                                    </select>
                                    @error('record_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Medical Information</h4>
                        <small class="opacity-75">Patient medical history and clinical information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="presenting_complaint" class="form-label fw-semibold" style="color: #0d6efd;">
                                        <i class="fas fa-comment-medical me-1"></i>Presenting Complaint <span class="text-danger">*</span> <small class="text-muted">(PC)</small>
                                    </label>
                                    <textarea class="form-control @error('presenting_complaint') is-invalid @enderror" 
                                              id="presenting_complaint" name="presenting_complaint" rows="4" required>{{ old('presenting_complaint') }}</textarea>
                                    @error('presenting_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="history_of_presenting_complaint" class="form-label fw-semibold" style="color: #0d6efd;">
                                        <i class="fas fa-history me-1"></i>History of Presenting Complaint <span class="text-danger">*</span> <small class="text-muted">(HPC)</small>
                                    </label>
                                    <textarea class="form-control @error('history_of_presenting_complaint') is-invalid @enderror" 
                                              id="history_of_presenting_complaint" name="history_of_presenting_complaint" rows="4" required>{{ old('history_of_presenting_complaint') }}</textarea>
                                    @error('history_of_presenting_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="past_medical_history" class="form-label fw-semibold" style="color: #0d6efd;">
                                        <i class="fas fa-file-medical me-1"></i>Past Medical History <span class="text-danger">*</span> <small class="text-muted">(PMH)</small>
                                    </label>
                                    <textarea class="form-control @error('past_medical_history') is-invalid @enderror" 
                                              id="past_medical_history" name="past_medical_history" rows="4" required>{{ old('past_medical_history') }}</textarea>
                                    @error('past_medical_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="drug_history" class="form-label fw-semibold" style="color: #0d6efd;">
                                        <i class="fas fa-pills me-1"></i>Drug History <span class="text-danger">*</span> <small class="text-muted">(DH)</small>
                                    </label>
                                    <textarea class="form-control @error('drug_history') is-invalid @enderror" 
                                              id="drug_history" name="drug_history" rows="4" required>{{ old('drug_history') }}</textarea>
                                    @error('drug_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="allergies" class="form-label fw-semibold" style="color: #dc3545;">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Allergies <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('allergies') is-invalid @enderror" 
                                              id="allergies" name="allergies" rows="4" required>{{ old('allergies') }}</textarea>
                                    @error('allergies')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="social_history" class="form-label fw-semibold" style="color: #0dcaf0;">
                                        <i class="fas fa-users me-1"></i>Social History <small class="text-muted">(SH)</small>
                                    </label>
                                    <textarea class="form-control @error('social_history') is-invalid @enderror" 
                                              id="social_history" name="social_history" rows="4">{{ old('social_history') }}</textarea>
                                    @error('social_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="family_history" class="form-label fw-semibold" style="color: #0dcaf0;">
                                        <i class="fas fa-sitemap me-1"></i>Family History <small class="text-muted">(FH)</small>
                                    </label>
                                    <textarea class="form-control @error('family_history') is-invalid @enderror" 
                                              id="family_history" name="family_history" rows="4">{{ old('family_history') }}</textarea>
                                    @error('family_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ideas_concerns_expectations" class="form-label fw-semibold" style="color: #ffc107;">
                                        <i class="fas fa-lightbulb me-1"></i>Ideas, Concerns, Expectations <span class="text-danger">*</span> <small class="text-muted">(ICE)</small>
                                    </label>
                                    <textarea class="form-control @error('ideas_concerns_expectations') is-invalid @enderror" 
                                              id="ideas_concerns_expectations" name="ideas_concerns_expectations" rows="4" required>{{ old('ideas_concerns_expectations') }}</textarea>
                                    @error('ideas_concerns_expectations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="plan" class="form-label fw-semibold" style="color: #198754;">
                                <i class="fas fa-clipboard-list me-1"></i>Management Plan <span class="text-danger">*</span> <small class="text-muted">(Plan - investigations, treatment, follow-up)</small>
                            </label>
                            <textarea class="form-control @error('plan') is-invalid @enderror" 
                                      id="plan" name="plan" rows="5" required>{{ old('plan') }}</textarea>
                            @error('plan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="diagnosis" class="form-label">
                                        <i class="fas fa-diagnoses me-1"></i>Diagnosis
                                    </label>
                                    <input type="text" class="form-control @error('diagnosis') is-invalid @enderror" 
                                           id="diagnosis" name="diagnosis" value="{{ old('diagnosis') }}"
                                           placeholder="Enter primary diagnosis">
                                    @error('diagnosis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="follow_up_date" class="form-label">
                                        <i class="fas fa-calendar-plus me-1"></i>Follow-up Date
                                    </label>
                                    <input type="text" class="form-control @error('follow_up_date') is-invalid @enderror" 
                                           id="follow_up_date" name="follow_up_date" 
                                           value="{{ old('follow_up_date') ? formatDate(old('follow_up_date')) : '' }}"
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10">
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025). Must be after today.</small>
                                    @error('follow_up_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Additional Notes
                            </label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4"
                                      placeholder="Any additional notes or observations">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Vital Signs Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Vital Signs</h4>
                        <small class="opacity-75">Patient vital signs and measurements</small>
                    </div>
                    <div class="form-section-body">
                        <div class="vital-signs-grid">
                            <div class="form-group">
                                <label for="vital_signs_blood_pressure" class="form-label">
                                    <i class="fas fa-thermometer me-1"></i>Blood Pressure
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_blood_pressure" name="vital_signs[blood_pressure]" 
                                       value="{{ old('vital_signs.blood_pressure') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_temperature" class="form-label">
                                    <i class="fas fa-temperature-low me-1"></i>Temperature (°C)
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_temperature" name="vital_signs[temperature]" 
                                       value="{{ old('vital_signs.temperature') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_pulse" class="form-label">
                                    <i class="fas fa-heartbeat me-1"></i>Pulse Rate (bpm)
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_pulse" name="vital_signs[pulse]" 
                                       value="{{ old('vital_signs.pulse') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_respiratory_rate" class="form-label">
                                    <i class="fas fa-lungs me-1"></i>Respiratory Rate (breaths/min)
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_respiratory_rate" name="vital_signs[respiratory_rate]" 
                                       value="{{ old('vital_signs.respiratory_rate') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_oxygen_saturation" class="form-label">
                                    <i class="fas fa-percentage me-1"></i>Oxygen Saturation (%)
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_oxygen_saturation" name="vital_signs[oxygen_saturation]" 
                                       value="{{ old('vital_signs.oxygen_saturation') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_weight" class="form-label">
                                    <i class="fas fa-weight me-1"></i>Weight (kg)
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_weight" name="vital_signs[weight]" 
                                       value="{{ old('vital_signs.weight') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_height" class="form-label">
                                    <i class="fas fa-ruler-vertical me-1"></i>Height (cm)
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_height" name="vital_signs[height]" 
                                       value="{{ old('vital_signs.height') }}">
                            </div>

                            <div class="form-group">
                                <label for="vital_signs_bmi" class="form-label">
                                    <i class="fas fa-calculator me-1"></i>BMI
                                </label>
                                <input type="text" class="form-control" 
                                       id="vital_signs_bmi" name="vital_signs[bmi]" 
                                       value="{{ old('vital_signs.bmi') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- File Attachments Section -->
                <div class="form-section">
                    <div class="form-section-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                        <h4 class="mb-0"><i class="fas fa-paperclip me-2"></i>Documents or Attachments</h4>
                        <small class="opacity-75">Attach reference files, documents, or images</small>
                    </div>
                    <div class="form-section-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>File Upload Guidelines:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li>Maximum 10 files per medical record</li>
                                <li>Maximum file size: 10MB per file</li>
                                <li>Allowed file types: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT, ZIP, RAR</li>
                                <li>Files are stored securely and access is logged for audit purposes</li>
                            </ul>
                        </div>

                        <div id="fileUploadContainer">
                            <div class="file-upload-item mb-3 p-3 border rounded">
                                <div class="row">
                                    <div class="col-md-5 mb-2">
                                        <label class="form-label small">File</label>
                                        <input type="file" 
                                               class="form-control form-control-sm file-input" 
                                               name="attachments[]" 
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.zip,.rar">
                                        <small class="text-muted">Max 10MB</small>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small">Type</label>
                                        <select class="form-control form-control-sm" name="attachments_category[]">
                                            <option value="photo">Photo</option>
                                            <option value="results">Results</option>
                                            <option value="documents">Documents</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label small">Description (Optional)</label>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               name="attachments_description[]" 
                                               placeholder="Brief description"
                                               maxlength="20">
                                    </div>
                                    <div class="col-md-1 mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-sm btn-danger remove-file-btn" style="display: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary" id="addFileBtn">
                            <i class="fas fa-plus me-1"></i>Add Another File
                        </button>
                        <small class="text-muted d-block mt-2">
                            <span id="fileCount">0</span> / 10 files
                        </small>
                    </div>
                </div>

                <!-- Privacy Settings Section -->
                <div class="form-section">
                    <div class="form-section-header" style="background: linear-gradient(135deg, #e74a3b 0%, #fd79a8 100%);">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Privacy Settings</h4>
                        <small class="opacity-75">Control record visibility and access</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="is_private" name="is_private" value="1" 
                                       {{ old('is_private') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_private">
                                    <i class="fas fa-lock me-1"></i>Private Record
                                </label>
                            </div>
                            <div class="form-help">Check to make this record private and restrict access</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Save Medical Record
                        </button>
                        <a href="{{ route('admin.medical-records.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <!-- Pre-consultation Verification Checklist -->
            <div class="info-card" style="border-left: 4px solid #ffc107;">
                <h6><i class="fas fa-clipboard-check me-2"></i>Pre-consultation Verification Checklist</h6>
                @php
                    $doctorName = auth()->user()->name ?? 'Dr. [Fullname]';
                    $hospitalName = \App\Models\SiteSetting::get('hospital_name', config('hospital.name', 'ThanksDoc EHR'));
                @endphp
                
                <div class="alert alert-info mb-3 p-2" id="introductionScriptAlert">
                    <strong>Introduction Script:</strong><br>
                    <small>"My name is {{ $doctorName }}, and I'll be conducting your consultation today."</small>
                </div>
                
                <div class="mb-3">
                    <strong>Verification Steps:</strong>
                    <ol class="mb-0 mt-2 small">
                        <li>Verify patient full name and date of birth.</li>
                        <li>Confirm current location for emergencies.</li>
                        <li>If under 18/with carer: confirm carer presence and check photo ID.</li>
                        <li>Obtain consent to proceed online and to document in the medical record.</li>
                    </ol>
                </div>
                
                <div class="form-check mt-3 p-2 bg-light rounded border">
                    <input class="form-check-input @error('pre_consultation_verified') is-invalid @enderror" 
                           type="checkbox" 
                           id="pre_consultation_verified" 
                           name="pre_consultation_verified" 
                           value="1" 
                           required>
                    <label class="form-check-label fw-bold small" for="pre_consultation_verified">
                        I, {{ $doctorName }} confirm the above are complete.
                    </label>
                    @error('pre_consultation_verified')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                    <strong>Note:</strong> You cannot start the consultation until this checkbox is ticked.
                </small>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Record Creation Guidelines</h6>
                <ul>
                    <li>Patient and Doctor selection are required</li>
                    <li>Choose appropriate record type for organization</li>
                    <li>Include comprehensive diagnosis and symptoms</li>
                    <li>Document treatment plans clearly</li>
                    <li>Record accurate vital signs</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                <ul>
                    <li>Link records to appointments when possible</li>
                    <li>Use clear, medical terminology</li>
                    <li>Set follow-up dates for continued care</li>
                    <li>Mark sensitive records as private</li>
                    <li>Include comprehensive notes</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security</h6>
                <ul>
                    <li><strong>GDPR Compliant:</strong> All records protected</li>
                    <li><strong>Access Control:</strong> Role-based permissions</li>
                    <li><strong>Audit Trail:</strong> All changes tracked</li>
                    <li><strong>Private Records:</strong> Restricted access</li>
                    <li><strong>Encryption:</strong> Data encrypted at rest</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('medical-records.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>View All Records
                    </a>
                    <a href="{{ contextRoute('prescriptions.create') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-pills me-1"></i>Add Prescription
                    </a>
                    <a href="{{ contextRoute('lab-reports.create') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-flask me-1"></i>Add Lab Report
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
    // Load appointments when patient changes
    $('#patient_id').change(function() {
        const patientId = $(this).val();
        const appointmentSelect = $('#appointment_id');
        
        // Clear current appointments
        appointmentSelect.html('<option value="">Loading appointments...</option>');
        
        if (patientId) {
            $.get('{{ contextRoute("api.appointments-by-patient") }}', { patient_id: patientId })
                .done(function(appointments) {
                    appointmentSelect.html('<option value="">Select Appointment (Optional)</option>');
                    appointments.forEach(function(appointment) {
                        appointmentSelect.append(
                            `<option value="${appointment.id}">${appointment.display}</option>`
                        );
                    });
                })
                .fail(function() {
                    appointmentSelect.html('<option value="">Select Appointment (Optional)</option>');
                });
        } else {
            appointmentSelect.html('<option value="">Select Appointment (Optional)</option>');
        }
    });

    // Auto-suggest diagnosis based on symptoms
    $('#symptoms').on('input', function() {
        // This could be enhanced with AI suggestions in the future
    });

    // Validate vital signs format
    $('#vital_signs_blood_pressure').on('blur', function() {
        const value = $(this).val();
        if (value && !value.match(/^\d+\/\d+$/)) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Format should be systolic/diastolic (e.g., 120/80)</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // BMI Calculation Function
    function calculateBMI() {
        const weightInput = $('#vital_signs_weight').val();
        const heightInput = $('#vital_signs_height').val();
        const bmiField = $('#vital_signs_bmi');
        
        // Extract numeric values from text inputs (e.g., "70 kg" -> 70, "175 cm" -> 175)
        const weight = parseFloat(weightInput.replace(/[^\d.]/g, ''));
        const height = parseFloat(heightInput.replace(/[^\d.]/g, ''));
        
        if (weight && height && height > 0) {
            // Convert height from cm to meters
            const heightInMeters = height / 100;
            // Calculate BMI: weight (kg) / (height in meters)^2
            const bmi = weight / (heightInMeters * heightInMeters);
            bmiField.val(bmi.toFixed(1));
        } else {
            bmiField.val('');
        }
    }
    
    // Auto-calculate BMI when weight or height changes
    $('#vital_signs_weight, #vital_signs_height').on('input', calculateBMI);
    
    // Calculate BMI on page load if values exist
    if ($('#vital_signs_weight').val() && $('#vital_signs_height').val()) {
        calculateBMI();
    }

    // File upload management
    let fileCount = 1;
    const maxFiles = 10;

    function updateFileCount() {
        const count = $('#fileUploadContainer .file-upload-item').length;
        $('#fileCount').text(count);
        
        // Show/hide remove buttons
        if (count > 1) {
            $('.remove-file-btn').show();
        } else {
            $('.remove-file-btn').hide();
        }
        
        // Enable/disable add button
        if (count >= maxFiles) {
            $('#addFileBtn').prop('disabled', true).addClass('disabled');
        } else {
            $('#addFileBtn').prop('disabled', false).removeClass('disabled');
        }
    }

    // Add file input
    $('#addFileBtn').on('click', function() {
        if ($('#fileUploadContainer .file-upload-item').length >= maxFiles) {
            alert('Maximum ' + maxFiles + ' files allowed.');
            return;
        }

        const newFileItem = `
            <div class="file-upload-item mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-5 mb-2">
                        <label class="form-label small">File</label>
                        <input type="file" 
                               class="form-control form-control-sm file-input" 
                               name="attachments[]" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.zip,.rar">
                        <small class="text-muted">Max 10MB</small>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label small">Type</label>
                        <select class="form-select form-select-sm" name="attachments_category[]">
                            <option value="photo">Photo</option>
                            <option value="results">Results</option>
                            <option value="documents">Documents</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label small">Description (Optional)</label>
                        <input type="text" 
                               class="form-control form-control-sm" 
                               name="attachments_description[]" 
                               placeholder="Brief description"
                               maxlength="20">
                    </div>
                    <div class="col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-danger remove-file-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        $('#fileUploadContainer').append(newFileItem);
        updateFileCount();
    });

    // Remove file input
    $(document).on('click', '.remove-file-btn', function() {
        $(this).closest('.file-upload-item').remove();
        updateFileCount();
    });

    // Validate file size on selection
    $(document).on('change', '.file-input', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File size exceeds 10MB limit. Please choose a smaller file.');
                $(this).val('');
                return;
            }
        }
    });

    // Initialize file count
    updateFileCount();

    // Form validation
    $('#createMedicalRecordForm').on('submit', function(e) {
        let isValid = true;

        // Validate file count
        const fileCount = $('#fileUploadContainer .file-input').filter(function() {
            return $(this).val() !== '';
        }).length;
        
        if (fileCount > maxFiles) {
            alert('Maximum ' + maxFiles + ' files allowed.');
            e.preventDefault();
            return false;
        }
        
        // Check pre-consultation verification checkbox
        if (!$('#pre_consultation_verified').is(':checked')) {
            $('#pre_consultation_verified').addClass('is-invalid');
            alert('⚠️ You must confirm that the pre-consultation verification checks are complete before proceeding.');
            isValid = false;
            e.preventDefault();
            return false;
        } else {
            $('#pre_consultation_verified').removeClass('is-invalid');
        }
        
        // Check required fields
        const requiredFields = ['patient_id', 'doctor_id', 'record_type'];
        requiredFields.forEach(function(field) {
            const element = $(`#${field}`);
            if (!element.val()) {
                element.addClass('is-invalid');
                isValid = false;
            } else {
                element.removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Auto-dismiss alerts after 5 seconds (except introduction script alert)
    setTimeout(function() {
        $('.alert').not('#introductionScriptAlert').fadeOut();
    }, 30000);
    
    // Keep introduction script alert visible for 30 seconds
    setTimeout(function() {
        $('#introductionScriptAlert').fadeOut();
    }, 30000);
});
</script>
@endpush
