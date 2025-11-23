@extends('admin.layouts.app')

@section('title', 'Add Lab Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.lab-reports.index') }}">Lab Reports</a></li>
    <li class="breadcrumb-item active">Add Lab Report</li>
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
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
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

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Add Lab Report</h5>
        <small class="text-muted">Create a new lab test report</small>
        <p class="page-subtitle text-muted">Create a new lab report with test results and analysis</p>
    </div>

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
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.lab-reports.store') }}" enctype="multipart/form-data" id="labReportForm">
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient & Doctor Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                        <small class="opacity-75">Select patient and ordering doctor</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" 
                                                    {{ (old('patient_id') ?? $selectedAppointment?->patient_id ?? $selectedMedicalRecord?->patient_id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->full_name }} ({{ $patient->patient_id ?? $patient->id }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doctor_id" class="form-label">Ordering Doctor <span class="text-danger">*</span></label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                            id="doctor_id" name="doctor_id" required>
                                        <option value="">Select Doctor</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" 
                                                    {{ (old('doctor_id') ?? $selectedAppointment?->doctor_id ?? $selectedMedicalRecord?->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }} - {{ $doctor->specialization ?? 'General' }}
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
                                    <label for="appointment_id" class="form-label">Related Appointment (Optional)</label>
                                    <select class="form-control @error('appointment_id') is-invalid @enderror" 
                                            id="appointment_id" name="appointment_id">
                                        <option value="">No Appointment</option>
                                        @if($selectedAppointment)
                                            <option value="{{ $selectedAppointment->id }}" selected>
                                                #{{ $selectedAppointment->appointment_number }} - {{ $selectedAppointment->patient->full_name }}
                                                ({{ $selectedAppointment->appointment_date->format('M d, Y') }})
                                            </option>
                                        @endif
                                    </select>
                                    @error('appointment_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="medical_record_id" class="form-label">Related Medical Record (Optional)</label>
                                    <select class="form-control @error('medical_record_id') is-invalid @enderror" 
                                            id="medical_record_id" name="medical_record_id">
                                        <option value="">No Medical Record</option>
                                        @if($selectedMedicalRecord)
                                            <option value="{{ $selectedMedicalRecord->id }}" selected>
                                                {{ $selectedMedicalRecord->patient->full_name }} - 
                                                {{ $selectedMedicalRecord->created_at->format('M d, Y') }}
                                            </option>
                                        @endif
                                    </select>
                                    @error('medical_record_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Information -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-vial me-2"></i>Test Information</h4>
                        <small class="opacity-75">Enter test details and specifications</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="test_name" class="form-label">Test Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('test_name') is-invalid @enderror" 
                                           id="test_name" name="test_name" 
                                           value="{{ old('test_name') }}" 
                                           placeholder="e.g., Complete Blood Count, Lipid Panel" required>
                                    @error('test_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="test_type" class="form-label">Test Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('test_type') is-invalid @enderror" 
                                            id="test_type" name="test_type" required>
                                        <option value="">Select Test Type</option>
                                        <option value="blood" {{ old('test_type') == 'blood' ? 'selected' : '' }}>Blood Test</option>
                                        <option value="urine" {{ old('test_type') == 'urine' ? 'selected' : '' }}>Urine Test</option>
                                        <option value="stool" {{ old('test_type') == 'stool' ? 'selected' : '' }}>Stool Test</option>
                                        <option value="imaging" {{ old('test_type') == 'imaging' ? 'selected' : '' }}>Imaging</option>
                                        <option value="biopsy" {{ old('test_type') == 'biopsy' ? 'selected' : '' }}>Biopsy</option>
                                        <option value="culture" {{ old('test_type') == 'culture' ? 'selected' : '' }}>Culture</option>
                                        <option value="molecular" {{ old('test_type') == 'molecular' ? 'selected' : '' }}>Molecular</option>
                                        <option value="other" {{ old('test_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('test_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="test_date" class="form-label">Test Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('test_date') is-invalid @enderror" 
                                           id="test_date" name="test_date" 
                                           value="{{ old('test_date', date('Y-m-d')) }}" required>
                                    @error('test_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-control @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority" required>
                                        <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                        <option value="stat" {{ old('priority') == 'stat' ? 'selected' : '' }}>Stat</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lab_technician" class="form-label">Lab Technician</label>
                                    <input type="text" class="form-control @error('lab_technician') is-invalid @enderror" 
                                           id="lab_technician" name="lab_technician" 
                                           value="{{ old('lab_technician') }}" 
                                           placeholder="Enter technician name">
                                    @error('lab_technician')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-microscope me-2"></i>Test Results</h4>
                        <small class="opacity-75">Enter test results, interpretation, and notes</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="results" class="form-label">Test Results</label>
                            <textarea class="form-control @error('results') is-invalid @enderror" 
                                      id="results" name="results" rows="6" 
                                      placeholder="Enter detailed test results, measurements, and findings">{{ old('results') }}</textarea>
                            @error('results')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reference_range" class="form-label">Reference Range</label>
                            <input type="text" class="form-control @error('reference_range') is-invalid @enderror" 
                                   id="reference_range" name="reference_range" 
                                   value="{{ old('reference_range') }}" 
                                   placeholder="e.g., 4.5-11.0 x10³/µL">
                            @error('reference_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="interpretation" class="form-label">Test Interpretation</label>
                            <textarea class="form-control @error('interpretation') is-invalid @enderror" 
                                      id="interpretation" name="interpretation" rows="4" 
                                      placeholder="Medical interpretation of the results and clinical significance">{{ old('interpretation') }}</textarea>
                            @error('interpretation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Any additional notes or observations">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="file_path" class="form-label">Report File (Optional)</label>
                            <input type="file" class="form-control @error('file_path') is-invalid @enderror" 
                                   id="file_path" name="file_path" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            @error('file_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                    </div>
                    <div class="form-section-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Create Lab Report
                            </button>
                            <a href="{{ route('admin.lab-reports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="info-card">
                    <h6><i class="fas fa-info-circle me-2"></i>Guidelines</h6>
                    <ul class="mb-0">
                        <li><strong>Required fields:</strong> Patient, Doctor, Test Name, Test Type, Test Date, Status, and Priority</li>
                        <li><strong>Test Results:</strong> Be thorough and objective in documenting results</li>
                        <li><strong>Interpretation:</strong> Provide clear clinical interpretation of findings</li>
                        <li><strong>Privacy:</strong> All lab reports are confidential and GDPR compliant</li>
                        <li><strong>File Upload:</strong> Attach scanned or digital lab report documents when available</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);

    // Patient selection change - filter related records
    $('#patient_id').on('change', function() {
        const patientId = $(this).val();
        if (patientId) {
            // You can add logic here to filter appointments/medical records by patient
        }
    });
});
</script>
@endpush
