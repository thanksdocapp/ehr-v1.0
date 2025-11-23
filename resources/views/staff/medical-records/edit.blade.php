@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Medical Record')
@section('page-title', 'Edit Medical Record')
@section('page-subtitle', 'Update medical record information')

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
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Edit Permissions Alert -->
    @if(auth()->user()->role !== 'doctor')
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Note:</strong> You can only edit medical records as authorized by your role. 
            Some fields may be restricted or require doctor approval.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.medical-records.update', $medicalRecord) }}" method="POST" id="medicalRecordEditForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Patient</label>
                                <div class="form-control-plaintext border rounded p-2 bg-light">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($medicalRecord->patient->first_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}</div>
                                            <small class="text-muted">{{ $medicalRecord->patient->phone ?? 'No phone' }} | {{ $medicalRecord->patient->email ?? 'No email' }}</small>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">Patient cannot be changed in edit mode</small>
                                <!-- Hidden field for patient_id since it's required by controller -->
                                <input type="hidden" name="patient_id" value="{{ $medicalRecord->patient_id }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_id" class="form-label fw-semibold">Related Appointment</label>
                                <select class="form-select @error('appointment_id') is-invalid @enderror" 
                                        id="appointment_id" name="appointment_id"
                                        {{ auth()->user()->role !== 'doctor' ? 'disabled' : '' }}>
                                    <option value="">No Appointment (Optional)</option>
                                    @foreach($appointments as $appointment)
                                        <option value="{{ $appointment->id }}" 
                                                {{ old('appointment_id', $medicalRecord->appointment_id) == $appointment->id ? 'selected' : '' }}>
                                            #{{ $appointment->appointment_number }} - {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }} 
                                            ({{ formatDate($appointment->appointment_date) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('appointment_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(auth()->user()->role !== 'doctor')
                                    <small class="text-muted">Only doctors can modify appointment associations</small>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="record_date" class="form-label fw-semibold">Record Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('record_date') is-invalid @enderror" 
                                       id="record_date" name="record_date" 
                                       value="{{ old('record_date', now()->format('Y-m-d')) }}" 
                                       required {{ auth()->user()->role !== 'doctor' ? 'readonly' : '' }}>
                                @error('record_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if(auth()->user()->role !== 'doctor')
                                    <small class="text-muted">Only doctors can modify record dates</small>
                                @endif
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="record_type" class="form-label fw-semibold">Record Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('record_type') is-invalid @enderror" 
                                        id="record_type" name="record_type" required>
                                    <option value="">Select Record Type</option>
                                    <option value="consultation" {{ old('record_type', $medicalRecord->record_type ?? 'consultation') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                    <option value="follow_up" {{ old('record_type', $medicalRecord->record_type) === 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                                    <option value="emergency" {{ old('record_type', $medicalRecord->record_type) === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="routine_checkup" {{ old('record_type', $medicalRecord->record_type) === 'routine_checkup' ? 'selected' : '' }}>Routine Checkup</option>
                                    <option value="procedure" {{ old('record_type', $medicalRecord->record_type) === 'procedure' ? 'selected' : '' }}>Procedure</option>
                                </select>
                                @error('record_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                            <div class="col-md-6 mb-3">
                                <label for="presenting_complaint" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-comment-medical me-1"></i>Presenting Complaint <span class="text-danger">*</span> <small class="text-muted">(PC)</small></label>
                                <textarea class="form-control @error('presenting_complaint') is-invalid @enderror" 
                                          id="presenting_complaint" name="presenting_complaint" rows="4" required>{{ old('presenting_complaint', $medicalRecord->presenting_complaint ?? $medicalRecord->chief_complaint ?? '') }}</textarea>
                                @error('presenting_complaint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="history_of_presenting_complaint" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-history me-1"></i>History of Presenting Complaint <span class="text-danger">*</span> <small class="text-muted">(HPC)</small></label>
                                <textarea class="form-control @error('history_of_presenting_complaint') is-invalid @enderror" 
                                          id="history_of_presenting_complaint" name="history_of_presenting_complaint" rows="4" required>{{ old('history_of_presenting_complaint', $medicalRecord->history_of_presenting_complaint ?? $medicalRecord->present_illness ?? '') }}</textarea>
                                @error('history_of_presenting_complaint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="past_medical_history" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-file-medical me-1"></i>Past Medical History <span class="text-danger">*</span> <small class="text-muted">(PMH)</small></label>
                                <textarea class="form-control @error('past_medical_history') is-invalid @enderror" 
                                          id="past_medical_history" name="past_medical_history" rows="4" required>{{ old('past_medical_history', $medicalRecord->past_medical_history) }}</textarea>
                                @error('past_medical_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="drug_history" class="form-label fw-semibold" style="color: #0d6efd;"><i class="fas fa-pills me-1"></i>Drug History <span class="text-danger">*</span> <small class="text-muted">(DH)</small></label>
                                <textarea class="form-control @error('drug_history') is-invalid @enderror" 
                                          id="drug_history" name="drug_history" rows="4" required>{{ old('drug_history', $medicalRecord->drug_history) }}</textarea>
                                @error('drug_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="allergies" class="form-label fw-semibold" style="color: #dc3545;"><i class="fas fa-exclamation-triangle me-1"></i>Allergies <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('allergies') is-invalid @enderror" 
                                          id="allergies" name="allergies" rows="4" required>{{ old('allergies', $medicalRecord->allergies) }}</textarea>
                                @error('allergies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="social_history" class="form-label fw-semibold" style="color: #0dcaf0;"><i class="fas fa-users me-1"></i>Social History <small class="text-muted">(SH)</small></label>
                                <textarea class="form-control @error('social_history') is-invalid @enderror" 
                                          id="social_history" name="social_history" rows="4">{{ old('social_history', $medicalRecord->social_history) }}</textarea>
                                @error('social_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="family_history" class="form-label fw-semibold" style="color: #0dcaf0;"><i class="fas fa-sitemap me-1"></i>Family History <small class="text-muted">(FH)</small></label>
                                <textarea class="form-control @error('family_history') is-invalid @enderror" 
                                          id="family_history" name="family_history" rows="4">{{ old('family_history', $medicalRecord->family_history) }}</textarea>
                                @error('family_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ideas_concerns_expectations" class="form-label fw-semibold" style="color: #ffc107;"><i class="fas fa-lightbulb me-1"></i>Ideas, Concerns, Expectations <span class="text-danger">*</span> <small class="text-muted">(ICE)</small></label>
                                <textarea class="form-control @error('ideas_concerns_expectations') is-invalid @enderror" 
                                          id="ideas_concerns_expectations" name="ideas_concerns_expectations" rows="4" required>{{ old('ideas_concerns_expectations', $medicalRecord->ideas_concerns_expectations) }}</textarea>
                                @error('ideas_concerns_expectations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="plan" class="form-label fw-semibold" style="color: #198754;"><i class="fas fa-clipboard-list me-1"></i>Management Plan <span class="text-danger">*</span> <small class="text-muted">(Plan - investigations, treatment, follow-up)</small></label>
                                <textarea class="form-control @error('plan') is-invalid @enderror" 
                                          id="plan" name="plan" rows="5" required>{{ old('plan', $medicalRecord->plan) }}</textarea>
                                @error('plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                        @php
                            $vitalSigns = $medicalRecord->vital_signs ?? [];
                        @endphp
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="temperature" class="form-label fw-semibold">Temperature (°C)</label>
                                <input type="number" step="0.1" class="form-control" 
                                       id="temperature" name="vital_signs[temperature]" 
                                       value="{{ old('vital_signs.temperature', $vitalSigns['temperature'] ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="blood_pressure" class="form-label fw-semibold">Blood Pressure</label>
                                <input type="text" class="form-control" 
                                       id="blood_pressure" name="vital_signs[blood_pressure]" 
                                       value="{{ old('vital_signs.blood_pressure', $vitalSigns['blood_pressure'] ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="pulse" class="form-label fw-semibold">Pulse Rate (bpm)</label>
                                <input type="number" class="form-control" 
                                       id="pulse" name="vital_signs[pulse]" 
                                       value="{{ old('vital_signs.pulse', $vitalSigns['pulse'] ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="respiratory_rate" class="form-label fw-semibold">Respiratory Rate (breaths/min)</label>
                                <input type="number" class="form-control" 
                                       id="respiratory_rate" name="vital_signs[respiratory_rate]" 
                                       value="{{ old('vital_signs.respiratory_rate', $vitalSigns['respiratory_rate'] ?? '') }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="oxygen_saturation" class="form-label fw-semibold">Oxygen Saturation (%)</label>
                                <input type="number" step="0.1" class="form-control" 
                                       id="oxygen_saturation" name="vital_signs[oxygen_saturation]" 
                                       value="{{ old('vital_signs.oxygen_saturation', $vitalSigns['oxygen_saturation'] ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="weight" class="form-label fw-semibold">Weight (kg)</label>
                                <input type="number" step="0.1" class="form-control" 
                                       id="weight" name="vital_signs[weight]" 
                                       value="{{ old('vital_signs.weight', $vitalSigns['weight'] ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="height" class="form-label fw-semibold">Height (cm)</label>
                                <input type="number" step="0.1" class="form-control" 
                                       id="height" name="vital_signs[height]" 
                                       value="{{ old('vital_signs.height', $vitalSigns['height'] ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="bmi" class="form-label fw-semibold">BMI</label>
                                <input type="number" step="0.1" class="form-control" 
                                       id="bmi" name="vital_signs[bmi]" 
                                       value="{{ old('vital_signs.bmi', $vitalSigns['bmi'] ?? '') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="doctor-card mb-4">
                    <div class="card-header bg-warning text-white py-3">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Additional Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Any additional notes, observations, or instructions...">{{ old('notes', $medicalRecord->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_reason" class="form-label fw-semibold">Reason for Edit <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('edit_reason') is-invalid @enderror" 
                                      id="edit_reason" name="edit_reason" rows="2" 
                                      placeholder="Please provide a reason for this medical record modification..." required>{{ old('edit_reason') }}</textarea>
                            @error('edit_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This will be logged for audit purposes (minimum 10 characters required)</small>
                        </div>
                    </div>
                </div>

                <!-- Existing Files -->
                @if($medicalRecord->attachments && $medicalRecord->attachments->count() > 0)
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-file me-2"></i>Existing Attachments ({{ $medicalRecord->attachments->count() }})</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($medicalRecord->attachments as $attachment)
                                    <tr>
                                        <td>
                                            <i class="fas fa-{{ $attachment->file_icon }} me-2"></i>
                                            {{ $attachment->file_name }}
                                            @if($attachment->description)
                                                <br><small class="text-muted">{{ $attachment->description }}</small>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info">{{ ucfirst($attachment->file_category) }}</span></td>
                                        <td>{{ $attachment->file_size_human }}</td>
                                        <td>{{ $attachment->uploader->name ?? 'Unknown' }}</td>
                                        <td>{{ formatDate($attachment->created_at) }}</td>
                                        <td>
                                            @if($attachment->canAccess(auth()->user()) && $attachment->virus_scan_status !== 'infected')
                                                @if($attachment->isViewable())
                                                    <a href="{{ route('staff.medical-record-attachments.view', $attachment) }}" 
                                                       target="_blank"
                                                       class="btn btn-sm btn-outline-info me-1" 
                                                       title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('staff.medical-record-attachments.download', $attachment) }}" 
                                                   class="btn btn-sm btn-outline-primary me-1" 
                                                   title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user()->is_admin || $attachment->uploaded_by === auth()->id())
                                            <form action="{{ route('staff.medical-record-attachments.destroy', $attachment) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- File Attachments -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-paperclip me-2"></i>Add New Documents or Attachments</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>File Upload Guidelines:</strong>
                            <ul class="mb-0 mt-2 small">
                                <li>Maximum 10 files total per medical record (including existing files)</li>
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
                            <span id="fileCount">0</span> / <span id="maxFiles">{{ 10 - ($medicalRecord->attachments->count() ?? 0) }}</span> files remaining
                        </small>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="card-header bg-light py-3">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Update Medical Record
                            </button>
                            <a href="{{ route('staff.medical-records.show', $medicalRecord) }}" class="btn btn-info">
                                <i class="fas fa-eye me-2"></i>View Record
                            </a>
                            <a href="{{ route('staff.medical-records.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                            @if(auth()->user()->role === 'doctor')
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash me-2"></i>Delete Record
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white py-3">
                        <h6 class="doctor-card-title mb-0">Current Information</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Record ID</small>
                            <strong>#{{ $medicalRecord->id }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Doctor</small>
                            <strong>
                                @if($medicalRecord->doctor)
                                    {{ formatDoctorName($medicalRecord->doctor->name) }}
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Record Type</small>
                            <strong>{{ ucfirst(str_replace('_', ' ', $medicalRecord->record_type ?? 'consultation')) }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created Date</small>
                            <strong>{{ formatDate($medicalRecord->created_at) }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Last Updated</small>
                            <strong>{{ $medicalRecord->updated_at ? formatDateTime($medicalRecord->updated_at) : 'Never' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Delete Record Modal -->
@if(auth()->user()->role === 'doctor')
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Medical Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('staff.medical-records.destroy', $medicalRecord) }}" method="POST" id="deleteRecordForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will permanently delete the medical record and cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Keep Record
                    </button>
                    <button type="submit" class="btn btn-danger" id="deleteSubmitBtn">
                        <i class="fas fa-trash me-2"></i>Delete Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@php
    $existingFilesCount = ($medicalRecord->attachments ?? collect())->count();
    $userIsDoctor = auth()->user()->role === 'doctor';
@endphp

@push('scripts')
<script>
$(document).ready(function() {
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

    // Calculate BMI on page load
    calculateBMI();

    // Form validation
    $('#medicalRecordEditForm').on('submit', function(e) {
        let isValid = true;
        let errorMessages = [];
        
        // Check required fields
        $(this).find('[required]').each(function() {
            const $field = $(this);
            const value = $field.val().trim();
            
            if (!value) {
                $field.addClass('is-invalid');
                isValid = false;
                const label = $field.closest('.mb-3, .col-md-6, .col-md-12').find('label').text().trim();
                errorMessages.push(label.replace('*', '').trim() + ' is required');
            } else {
                $field.removeClass('is-invalid');
                
                // Special validation for edit_reason (min 10 characters)
                if ($field.attr('id') === 'edit_reason' && value.length < 10) {
                    $field.addClass('is-invalid');
                    isValid = false;
                    errorMessages.push('Reason for Edit must be at least 10 characters long');
                }
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            const message = errorMessages.length > 0 
                ? 'Please fix the following errors:\n\n' + errorMessages.join('\n')
                : 'Please fill in all required fields.';
            alert(message);
            // Scroll to first invalid field
            const firstInvalid = $(this).find('.is-invalid').first();
            if (firstInvalid.length) {
                $('html, body').animate({
                    scrollTop: firstInvalid.offset().top - 100
                }, 500);
                firstInvalid.focus();
            }
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

    // File upload management
    const existingFiles = {{ $existingFilesCount }};
    const maxFiles = 10;
    let remainingSlots = maxFiles - existingFiles;

    function updateFileCount() {

        const count = $('#fileUploadContainer .file-upload-item').length;
        const filesWithValues = $('#fileUploadContainer .file-input').filter(function() {
            return $(this).val() !== '';
        }).length;
        
        $('#fileCount').text(filesWithValues);
        $('#maxFiles').text(remainingSlots);
        
        // Show/hide remove buttons
        if (count > 1) {
            $('.remove-file-btn').show();
        } else {
            $('.remove-file-btn').hide();
        }
        
        // Enable/disable add button
        if (existingFiles + filesWithValues >= maxFiles) {
            $('#addFileBtn').prop('disabled', true).addClass('disabled');
        } else {
            $('#addFileBtn').prop('disabled', false).removeClass('disabled');
        }
    }

    // Add file input
    $('#addFileBtn').on('click', function() {
        const currentFiles = $('#fileUploadContainer .file-input').filter(function() {
            return $(this).val() !== '';
        }).length;
        
        if (existingFiles + currentFiles >= maxFiles) {
            alert('Maximum ' + maxFiles + ' files allowed. You have ' + existingFiles + ' existing files.');
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
        updateFileCount();
    });

    // Initialize file count
    updateFileCount();
});

@if($userIsDoctor)
// Delete record function
function confirmDelete() {
    // Reset form when modal opens
    $('#deleteRecordForm')[0].reset();
    $('#deleteRecordForm .is-invalid').removeClass('is-invalid');
    
    // Show modal using Bootstrap 5 method
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'), {
        backdrop: true,
        keyboard: true,
        focus: true
    });
    deleteModal.show();
    
    // Ensure modal is clickable after showing
    setTimeout(function() {
        $('#deleteModal').css({
            'pointer-events': 'auto',
            'z-index': '1055'
        });
        $('#deleteModal .modal-dialog, #deleteModal .modal-content, #deleteModal .btn, #deleteModal .form-control').css({
            'pointer-events': 'auto',
            'position': 'relative'
        });
    }, 100);
}

// Delete form submission - show loading state
$('#deleteRecordForm').on('submit', function(e) {
    // Show loading state
    $('#deleteSubmitBtn').html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...').prop('disabled', true);
});
@endif
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

/* Fix modal z-index and pointer-events issues */
#deleteModal {
    z-index: 1055 !important;
}

#deleteModal .modal-dialog {
    z-index: 1056 !important;
    pointer-events: auto !important;
    position: relative !important;
}

#deleteModal .modal-content {
    z-index: 1057 !important;
    pointer-events: auto !important;
    position: relative !important;
}

#deleteModal .modal-header,
#deleteModal .modal-body,
#deleteModal .modal-footer {
    pointer-events: auto !important;
    position: relative !important;
}

#deleteModal .btn,
#deleteModal .form-control,
#deleteModal .form-check-input,
#deleteModal textarea {
    pointer-events: auto !important;
    position: relative !important;
    z-index: 1058 !important;
}

.modal-backdrop {
    z-index: 1054 !important;
    pointer-events: none !important; /* Don't block clicks - allow clicks through backdrop */
}

/* Ensure modal is clickable when shown */
#deleteModal.show {
    display: block !important;
    pointer-events: auto !important;
}

#deleteModal.show .modal-dialog {
    pointer-events: auto !important;
}

/* Fix any overlay issues */
body.modal-open {
    overflow: hidden;
}

body.modal-open #deleteModal {
    overflow: visible !important;
}
</style>
@endpush

@endsection
