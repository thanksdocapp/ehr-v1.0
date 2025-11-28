@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create Medical Record')
@section('page-title', 'Create Medical Record')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Create comprehensive medical records for your patients' : 'Create medical records as authorized by your role')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-file-medical-alt me-2 text-primary"></i>Create Medical Record
                    </h1>
                    <p class="text-muted mb-0">
                        @if(auth()->user()->role === 'doctor')
                            Create comprehensive medical records for your patients
                        @else
                            Create medical records as authorized by your role
                        @endif
                    </p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.medical-records.index') }}">Medical Records</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

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

    <form action="{{ route('staff.medical-records.store') }}" method="POST" id="medicalRecordForm" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient & Appointment Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient & Appointment Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatientId ?? null) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone ?? 'No phone' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <a href="{{ route('staff.patients.create') }}" class="text-decoration-none">
                                            <i class="fas fa-plus"></i> Add new patient
                                        </a>
                                    </small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="appointment_id" class="form-label">Related Appointment</label>
                                    <select class="form-control @error('appointment_id') is-invalid @enderror" 
                                            id="appointment_id" name="appointment_id">
                                        <option value="">No Appointment (Optional)</option>
                                        @foreach($appointments as $appointment)
                                            <option value="{{ $appointment->id }}" 
                                                    {{ old('appointment_id', $selectedAppointmentId ?? null) == $appointment->id ? 'selected' : '' }}
                                                    data-appointment-date="{{ $appointment->appointment_date }}"
                                                    data-reason="{{ $appointment->reason ?? '' }}"
                                                    data-symptoms="{{ $appointment->symptoms ?? '' }}">
                                                #{{ $appointment->appointment_number }} - {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} 
                                                ({{ formatDate($appointment->appointment_date) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('appointment_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        @if($selectedAppointmentId)
                                            <span class="text-success">Appointment pre-selected</span>
                                        @else
                                            Link this record to a completed appointment
                                        @endif
                                    </small>
                                </div>
                                
                                @if(isset($previousRecords) && count($previousRecords) > 0 && !$selectedAppointmentId)
                                <div class="form-group mb-3">
                                    <label class="form-label">Copy From Previous Record</label>
                                    <select class="form-control" id="copy_from_record" onchange="copyFromPreviousRecord(this.value)">
                                        <option value="">-- Don't copy --</option>
                                        @foreach($previousRecords as $prevRecord)
                                            <option value="{{ $prevRecord->id }}" 
                                                    data-pmh="{{ $prevRecord->past_medical_history ?? '' }}"
                                                    data-drug-history="{{ $prevRecord->drug_history ?? '' }}"
                                                    data-allergies="{{ $prevRecord->allergies ?? '' }}"
                                                    data-social-history="{{ $prevRecord->social_history ?? '' }}"
                                                    data-family-history="{{ $prevRecord->family_history ?? '' }}">
                                                {{ formatDate($prevRecord->record_date) }} - {{ ucfirst(str_replace('_', ' ', $prevRecord->record_type)) }}
                                                @if($prevRecord->presenting_complaint)
                                                    ({{ Str::limit($prevRecord->presenting_complaint, 30) }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        <i class="fas fa-copy me-1"></i>
                                        Copy PMH, Drug History, Allergies, Social & Family History from previous record
                                    </small>
                                </div>
                                @endif

                                <div class="form-group mb-3">
                                    <label for="record_date" class="form-label">Record Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('record_date') is-invalid @enderror" 
                                           id="record_date" name="record_date" 
                                           value="{{ old('record_date', date('Y-m-d')) }}" required>
                                    @error('record_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                @if(auth()->user()->role === 'doctor')
                                    <div class="form-group mb-3">
                                        <label class="form-label">Attending Doctor</label>
                                        <div class="form-control-plaintext border rounded p-2 bg-light">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ formatDoctorName(auth()->user()->name) }}</div>
                                                    <small class="text-muted">{{ auth()->user()->specialization ?? (auth()->user()->doctor->specialization ?? 'GP') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="doctor_id" value="{{ auth()->user()->id }}">
                                    </div>
                                @else
                                    <div class="form-group mb-3">
                                        <label for="doctor_id" class="form-label">Supervising Doctor</label>
                                        <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                                id="doctor_id" name="doctor_id">
                                            <option value="">No Doctor Assigned</option>
                                            @foreach($doctors ?? \App\Models\Doctor::orderBy('first_name')->get() as $doctor)
                                                @php
                                                    $doctorUserId = $doctor->user_id ?? $doctor->id;
                                                    $doctorName = $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name);
                                                @endphp
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    {{ formatDoctorName($doctorName) }} - {{ $doctor->specialization ?? 'General' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Record will be created under your name with doctor supervision</small>
                                    </div>
                                @endif

                                <div class="form-group mb-3">
                                    <label for="record_type" class="form-label">Record Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('record_type') is-invalid @enderror" 
                                            id="record_type" name="record_type" required>
                                        <option value="">Select Record Type</option>
                                        <option value="consultation" {{ old('record_type') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                        <option value="follow_up" {{ old('record_type') === 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                                        <option value="emergency" {{ old('record_type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                        <option value="routine_checkup" {{ old('record_type') === 'routine_checkup' ? 'selected' : '' }}>Routine Checkup</option>
                                        <option value="procedure" {{ old('record_type') === 'procedure' ? 'selected' : '' }}>Procedure</option>
                                    </select>
                                    @error('record_type')
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
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-notes-medical me-2"></i>Medical Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="presenting_complaint" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-comment-medical me-1"></i>Presenting Complaint <span class="text-danger">*</span> <small class="text-muted">(PC)</small></label>
                                    <textarea class="form-control @error('presenting_complaint') is-invalid @enderror" 
                                              id="presenting_complaint" name="presenting_complaint" rows="4" required>{{ old('presenting_complaint') }}</textarea>
                                    @error('presenting_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="history_of_presenting_complaint" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-history me-1"></i>History of Presenting Complaint <span class="text-danger">*</span> <small class="text-muted">(HPC)</small></label>
                                    <textarea class="form-control @error('history_of_presenting_complaint') is-invalid @enderror" 
                                              id="history_of_presenting_complaint" name="history_of_presenting_complaint" rows="4" required>{{ old('history_of_presenting_complaint') }}</textarea>
                                    @error('history_of_presenting_complaint')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="past_medical_history" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-file-medical me-1"></i>Past Medical History <span class="text-danger">*</span> <small class="text-muted">(PMH)</small></label>
                                    <textarea class="form-control @error('past_medical_history') is-invalid @enderror" 
                                              id="past_medical_history" name="past_medical_history" rows="4" required>{{ old('past_medical_history') }}</textarea>
                                    @error('past_medical_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="drug_history" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-pills me-1"></i>Drug History <span class="text-danger">*</span> <small class="text-muted">(DH)</small></label>
                                    <textarea class="form-control @error('drug_history') is-invalid @enderror" 
                                              id="drug_history" name="drug_history" rows="4" required>{{ old('drug_history') }}</textarea>
                                    @error('drug_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="allergies" class="form-label fw-semibold" style="color: #dc3545;"><i class="fas fa-exclamation-triangle me-1"></i>Allergies <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('allergies') is-invalid @enderror" 
                                              id="allergies" name="allergies" rows="4" required>{{ old('allergies') }}</textarea>
                                    @error('allergies')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="social_history" class="form-label fw-semibold" style="color: #0dcaf0;"><i class="fas fa-users me-1"></i>Social History <small class="text-muted">(SH)</small></label>
                                    <textarea class="form-control @error('social_history') is-invalid @enderror" 
                                              id="social_history" name="social_history" rows="4">{{ old('social_history') }}</textarea>
                                    @error('social_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="family_history" class="form-label fw-semibold" style="color: #0dcaf0;"><i class="fas fa-sitemap me-1"></i>Family History <small class="text-muted">(FH)</small></label>
                                    <textarea class="form-control @error('family_history') is-invalid @enderror" 
                                              id="family_history" name="family_history" rows="4">{{ old('family_history') }}</textarea>
                                    @error('family_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="ideas_concerns_expectations" class="form-label fw-semibold" style="color: #ffc107;"><i class="fas fa-lightbulb me-1"></i>Ideas, Concerns, Expectations <span class="text-danger">*</span> <small class="text-muted">(ICE)</small></label>
                                    <textarea class="form-control @error('ideas_concerns_expectations') is-invalid @enderror" 
                                              id="ideas_concerns_expectations" name="ideas_concerns_expectations" rows="4" required>{{ old('ideas_concerns_expectations') }}</textarea>
                                    @error('ideas_concerns_expectations')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="plan" class="form-label fw-semibold" style="color: #198754;"><i class="fas fa-clipboard-list me-1"></i>Management Plan <span class="text-danger">*</span> <small class="text-muted">(Plan - investigations, treatment, follow-up)</small></label>
                                    <textarea class="form-control @error('plan') is-invalid @enderror" 
                                              id="plan" name="plan" rows="5" required>{{ old('plan') }}</textarea>
                                    @error('plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vital Signs -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-heartbeat me-2"></i>Vital Signs</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="temperature" class="form-label">Temperature (°C)</label>
                                    <input type="number" step="0.1" class="form-control" 
                                           id="temperature" name="vital_signs[temperature]" 
                                           value="{{ old('vital_signs.temperature') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="blood_pressure" class="form-label">Blood Pressure</label>
                                    <input type="text" class="form-control" 
                                           id="blood_pressure" name="vital_signs[blood_pressure]" 
                                           value="{{ old('vital_signs.blood_pressure') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="pulse" class="form-label">Pulse Rate (bpm)</label>
                                    <input type="number" class="form-control" 
                                           id="pulse" name="vital_signs[pulse]" 
                                           value="{{ old('vital_signs.pulse') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="respiratory_rate" class="form-label">Respiratory Rate (breaths/min)</label>
                                    <input type="number" class="form-control" 
                                           id="respiratory_rate" name="vital_signs[respiratory_rate]" 
                                           value="{{ old('vital_signs.respiratory_rate') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="oxygen_saturation" class="form-label">Oxygen Saturation (%)</label>
                                    <input type="number" step="0.1" class="form-control" 
                                           id="oxygen_saturation" name="vital_signs[oxygen_saturation]" 
                                           value="{{ old('vital_signs.oxygen_saturation') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" 
                                           id="weight" name="vital_signs[weight]" 
                                           value="{{ old('vital_signs.weight') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="height" class="form-label">Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control" 
                                           id="height" name="vital_signs[height]" 
                                           value="{{ old('vital_signs.height') }}">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="bmi" class="form-label">BMI</label>
                                    <input type="number" step="0.1" class="form-control" 
                                           id="bmi" name="vital_signs[bmi]" 
                                           value="{{ old('vital_signs.bmi') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Additional Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Any additional notes, observations, or instructions...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- File Attachments -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-paperclip me-2"></i>Documents or Attachments</h5>
                    </div>
                    <div class="doctor-card-body">
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
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-1"></i>Save Medical Record
                            </button>
                            <a href="{{ route('staff.medical-records.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Doctor Information -->
                @if(auth()->user()->role === 'doctor')
                    <div class="doctor-card mb-4">
                        <div class="doctor-card-header">
                            <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>Doctor Information</h5>
                        </div>
                        <div class="doctor-card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-lg me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ formatDoctorName(auth()->user()->name) }}</div>
                                    <div class="text-muted">{{ auth()->user()->specialization ?? (auth()->user()->doctor->specialization ?? 'GP') }}</div>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Pre-consultation Verification Checklist -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-clipboard-check me-2"></i>Pre-consultation Verification Checklist</h5>
                    </div>
                    <div class="doctor-card-body">
                        @php
                            $doctorName = auth()->user()->name ?? 'Dr. [Fullname]';
                            $hospitalName = \App\Models\SiteSetting::get('hospital_name', config('hospital.name', 'ThanksDoc EHR'));
                        @endphp
                        
                        <div class="alert alert-info mb-3" id="introductionScriptAlert">
                            <strong>Introduction Script:</strong><br>
                            "My name is {{ $doctorName }}, and I'll be conducting your consultation today."
                        </div>
                        
                        <div class="mb-3">
                            <strong>Verification Steps:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Verify patient full name and date of birth.</li>
                                <li>Confirm current location for emergencies.</li>
                                <li>If under 18/with carer: confirm carer presence and check photo ID.</li>
                                <li>Obtain consent to proceed online and to document in the medical record.</li>
                            </ol>
                        </div>
                        
                        <div class="form-check mt-4 p-3 bg-light rounded border">
                            <input class="form-check-input @error('pre_consultation_verified') is-invalid @enderror" 
                                   type="checkbox" 
                                   id="pre_consultation_verified" 
                                   name="pre_consultation_verified" 
                                   value="1" 
                                   required>
                            <label class="form-check-label fw-bold" for="pre_consultation_verified">
                                I ({{ $doctorName }}) confirm the above are complete.
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
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@php
    $appointmentsJson = json_encode($appointments ?? []);
    $appointmentsByPatientRoute = route('staff.api.appointments-by-patient');
@endphp

@push('scripts')
<script>
$(document).ready(function() {
    // Appointments data loaded from PHP
    var allAppointments = {!! $appointmentsJson !!};
    var appointmentsByPatientRoute = '{!! $appointmentsByPatientRoute !!}';
    
    // Calculate BMI automatically
    function calculateBMI() {
        const weight = parseFloat($('#weight').val());
        const height = parseFloat($('#height').val());
        const bmiField = $('#bmi');
        
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

    // BMI calculation on weight/height change
    $('#weight, #height').on('input', calculateBMI);
    
    // Calculate BMI on page load if values exist
    if ($('#weight').val() && $('#height').val()) {
        calculateBMI();
    }

    // Add CSRF token to AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Debug: Check if jQuery and CSRF token are available
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('CSRF token:', $('meta[name="csrf-token"]').attr('content'));

    // Patient selection change - fetch appointments via AJAX
    $('#patient_id').on('change', function() {
        const patientId = $(this).val();
        const appointmentSelect = $('#appointment_id');
        
        console.log('Patient changed to:', patientId);
        
        // Clear current appointments
        appointmentSelect.html('<option value="">Loading appointments...</option>');
        
        if (patientId) {
            // Fixed fallback: filter existing appointments by patient using valid JavaScript.
            var patientAppointments = allAppointments.filter(function(apt) {
                return String(apt.patient_id) === String(patientId);
            });

            appointmentSelect.html('<option value="">No Appointment (Optional)</option>');
            if (patientAppointments.length > 0) {
                patientAppointments.forEach(function(appointment) {
                    const optionText = '#' + appointment.appointment_number + ' - ' + 
                                     appointment.patient.first_name + ' ' + appointment.patient.last_name + 
                                     ' (' + new Date(appointment.appointment_date).toLocaleDateString() + ')';
                    appointmentSelect.append(
                        '<option value="' + appointment.id + '">' + optionText + '</option>'
                    );
                });
            } else {
                appointmentSelect.append('<option value="" disabled>No active appointments found for this patient</option>');
            }
            
            // Also try AJAX as backup
            $.ajax({
                url: appointmentsByPatientRoute,
                method: 'GET',
                data: { patient_id: patientId },
                dataType: 'json',
                timeout: 5000,
                success: function(appointments) {
                    console.log('AJAX Success:', appointments);
                    // Only update if we got different/more data
                    if (appointments && appointments.length > patientAppointments.length) {
                        appointmentSelect.html('<option value="">No Appointment (Optional)</option>');
                        appointments.forEach(function(appointment) {
                            appointmentSelect.append(
                                '<option value="' + appointment.id + '">' + appointment.text + '</option>'
                            );
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('Response:', xhr.responseText);
                    // Don't show error since we have fallback working
                }
            });
        } else {
            appointmentSelect.html('<option value="">No Appointment (Optional)</option>');
        }
    });

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
    $('#medicalRecordForm').on('submit', function(e) {
        let isValid = true;
        
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

        // Validate file count
        const fileCount = $('#fileUploadContainer .file-input').filter(function() {
            return $(this).val() !== '';
        }).length;
        
        if (fileCount > maxFiles) {
            alert('Maximum ' + maxFiles + ' files allowed.');
            e.preventDefault();
            return false;
        }
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if ($(this).attr('type') === 'checkbox') {
                // Skip checkbox validation here, already handled above
                return;
            }
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Validate vital signs ranges
        const temperature = parseFloat($('#temperature').val());
        const pulse = parseInt($('#pulse').val());
        const respRate = parseInt($('#respiratory_rate').val());
        const oxygenSat = parseFloat($('#oxygen_saturation').val());
        
        // Temperature validation (35-42°C)
        if (temperature && (temperature < 35 || temperature > 42)) {
            $('#temperature').addClass('is-invalid');
            alert('Temperature seems outside normal range (35-42°C). Please verify.');
            isValid = false;
        }
        
        // Pulse validation (40-200 bpm)
        if (pulse && (pulse < 40 || pulse > 200)) {
            $('#pulse').addClass('is-invalid');
            alert('Pulse seems outside normal range (40-200 bpm). Please verify.');
            isValid = false;
        }
        
        // Respiratory rate validation (8-40)
        if (respRate && (respRate < 8 || respRate > 40)) {
            $('#respiratory_rate').addClass('is-invalid');
            alert('Respiratory rate seems outside normal range (8-40). Please verify.');
            isValid = false;
        }
        
        // Oxygen saturation validation (70-100%)
        if (oxygenSat && (oxygenSat < 70 || oxygenSat > 100)) {
            $('#oxygen_saturation').addClass('is-invalid');
            alert('Oxygen saturation seems outside normal range (70-100%). Please verify.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields and verify vital signs.');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Creating...').prop('disabled', true);
    });
    
    // Real-time validation
    $('input, select, textarea').on('blur', function() {
        if ($(this).prop('required') && !$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Auto-resize textareas
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Populate fields based on record type
    $('#record_type').on('change', function() {
        const type = $(this).val();
        
        // Set default templates based on record type
        switch(type) {
            case 'emergency':
                if (!$('#presenting_complaint').val()) {
                    $('#presenting_complaint').val('Emergency presentation: ');
                }
                break;
            case 'routine_checkup':
                if (!$('#presenting_complaint').val()) {
                    $('#presenting_complaint').val('Routine health maintenance visit');
                }
                break;
            case 'follow_up':
                if (!$('#presenting_complaint').val()) {
                    $('#presenting_complaint').val('Follow-up visit for: ');
                }
                break;
        }
    });

    // Set record date to today by default
    if (!$('#record_date').val()) {
        $('#record_date').val(new Date().toISOString().split('T')[0]);
    }

    // Copy from previous record function
    function copyFromPreviousRecord(recordId) {
        if (!recordId) return;
        
        const select = document.getElementById('copy_from_record');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.dataset) {
            // Copy Past Medical History
            if (selectedOption.dataset.pmh && $('#past_medical_history').val() === '') {
                $('#past_medical_history').val(selectedOption.dataset.pmh);
            }
            
            // Copy Drug History
            if (selectedOption.dataset.drugHistory && $('#drug_history').val() === '') {
                $('#drug_history').val(selectedOption.dataset.drugHistory);
            }
            
            // Copy Allergies
            if (selectedOption.dataset.allergies && $('#allergies').val() === '') {
                $('#allergies').val(selectedOption.dataset.allergies);
            }
            
            // Copy Social History
            if (selectedOption.dataset.socialHistory && $('#social_history').val() === '') {
                $('#social_history').val(selectedOption.dataset.socialHistory);
            }
            
            // Copy Family History
            if (selectedOption.dataset.familyHistory && $('#family_history').val() === '') {
                $('#family_history').val(selectedOption.dataset.familyHistory);
            }
            
            // Show success message
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Previous record data has been copied. Please review and update as needed.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
    }
    
    // Auto-fill from appointment
    $('#appointment_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val() && selectedOption.data('appointment-date')) {
            // Auto-fill record date with appointment date
            const appointmentDate = selectedOption.data('appointment-date');
            if (appointmentDate && !$('#record_date').val()) {
                $('#record_date').val(appointmentDate);
            }
            
            // Auto-fill presenting complaint with appointment reason/symptoms
            const reason = selectedOption.data('reason');
            const symptoms = selectedOption.data('symptoms');
            if ((reason || symptoms) && !$('#presenting_complaint').val()) {
                let complaint = '';
                if (reason) complaint += reason;
                if (symptoms) {
                    if (complaint) complaint += '\n\nSymptoms: ';
                    complaint += symptoms;
                }
                $('#presenting_complaint').val(complaint);
            }
        }
    });
    
    // Pre-fill from selected appointment if available
    @if(isset($selectedAppointment) && $selectedAppointment)
        $(document).ready(function() {
            // Pre-fill record date
            $('#record_date').val('{{ $selectedAppointment->appointment_date->format('Y-m-d') }}');
            
            // Pre-fill presenting complaint if appointment has reason/symptoms
            @if($selectedAppointment->reason || $selectedAppointment->symptoms)
                let complaint = '';
                @if($selectedAppointment->reason)
                    complaint += '{{ addslashes($selectedAppointment->reason) }}';
                @endif
                @if($selectedAppointment->symptoms)
                    if (complaint) complaint += '\n\nSymptoms: ';
                    complaint += '{{ addslashes($selectedAppointment->symptoms) }}';
                @endif
                $('#presenting_complaint').val(complaint);
            @endif
        });
    @endif

    // Auto-dismiss alerts after 5 seconds (except introduction script alert)
    setTimeout(function() {
        $('.alert').not('#introductionScriptAlert').fadeOut();
    }, 30000);
    
    // Keep introduction script alert visible for 30 seconds
    setTimeout(function() {
        $('#introductionScriptAlert').fadeOut();
    }, 30000);
    
    // Auto-complete for Diagnosis
    let diagnosisSuggestions = [];
    let diagnosisTimeout;
    const diagnosisInput = $('#diagnosis');
    const diagnosisSuggestionsContainer = $('<div class="autocomplete-suggestions" id="diagnosisSuggestions" style="display: none; position: absolute; z-index: 1000; background: white; border: 1px solid #ddd; border-radius: 4px; max-height: 300px; overflow-y: auto; width: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>');
    
    // Append suggestions container to the diagnosis input's parent
    if (diagnosisInput.length) {
        diagnosisInput.parent().css('position', 'relative').append(diagnosisSuggestionsContainer);
        
        diagnosisInput.on('input', function() {
            const query = $(this).val().trim();
            
            clearTimeout(diagnosisTimeout);
            
            if (query.length < 2) {
                diagnosisSuggestionsContainer.hide();
                return;
            }
            
            diagnosisTimeout = setTimeout(function() {
                fetch('{{ route("staff.api.suggestions.diagnosis") }}?q=' + encodeURIComponent(query), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                })
                .then(response => response.json())
                .then(data => {
                    diagnosisSuggestions = Array.isArray(data) ? data : [];
                    
                    if (diagnosisSuggestions.length > 0) {
                        let html = '<ul class="list-unstyled mb-0">';
                        diagnosisSuggestions.forEach(function(suggestion) {
                            html += '<li class="autocomplete-suggestion-item p-2 border-bottom" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background=\'#f8f9fa\'" onmouseout="this.style.background=\'white\'" data-value="' + suggestion.replace(/"/g, '&quot;') + '">' + suggestion + '</li>';
                        });
                        html += '</ul>';
                        diagnosisSuggestionsContainer.html(html).show();
                        
                        // Handle suggestion click
                        diagnosisSuggestionsContainer.find('.autocomplete-suggestion-item').on('click', function() {
                            const value = $(this).data('value');
                            diagnosisInput.val(value);
                            diagnosisSuggestionsContainer.hide();
                        });
                    } else {
                        diagnosisSuggestionsContainer.hide();
                    }
                })
                .catch(error => {
                    console.error('Diagnosis suggestions error:', error);
                    diagnosisSuggestionsContainer.hide();
                });
            }, 300);
        });
        
        // Hide suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#diagnosis, #diagnosisSuggestions').length) {
                diagnosisSuggestionsContainer.hide();
            }
        });
        
        // Handle keyboard navigation
        diagnosisInput.on('keydown', function(e) {
            const visibleItems = diagnosisSuggestionsContainer.find('.autocomplete-suggestion-item:visible');
            if (visibleItems.length === 0) return;
            
            const currentIndex = visibleItems.index(visibleItems.filter('.highlighted'));
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                visibleItems.removeClass('highlighted');
                const nextIndex = currentIndex < visibleItems.length - 1 ? currentIndex + 1 : 0;
                visibleItems.eq(nextIndex).addClass('highlighted').css('background', '#f8f9fa');
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                visibleItems.removeClass('highlighted');
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : visibleItems.length - 1;
                visibleItems.eq(prevIndex).addClass('highlighted').css('background', '#f8f9fa');
            } else if (e.key === 'Enter' && currentIndex >= 0) {
                e.preventDefault();
                visibleItems.eq(currentIndex).click();
            } else if (e.key === 'Escape') {
                diagnosisSuggestionsContainer.hide();
            }
        });
    }
});
</script>
<style>
.autocomplete-suggestion-item.highlighted {
    background: #f8f9fa !important;
}
</style>
@endpush
