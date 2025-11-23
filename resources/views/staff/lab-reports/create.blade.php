@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create Lab Report')
@section('page-title', 'Create Lab Report')
@section('page-subtitle', 'Fill out the form below to create a new lab report for selected patient')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-flask me-2 text-primary"></i>Create Lab Report
                    </h1>
                    <p class="text-muted mb-0">Fill out the form below to create a new lab report for selected patient</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.lab-reports.index') }}">Lab Reports</a></li>
                        <li class="breadcrumb-item active">Create Report</li>
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

    <form action="{{ route('staff.lab-reports.store') }}" method="POST" id="labReportForm" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient & Test Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient & Test Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients ?? [] as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->patient_id ?? $patient->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if(auth()->user()->role !== 'doctor')
                                <div class="form-group mb-3">
                                    <label for="doctor_id" class="form-label">Ordering Doctor</label>
                                    <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                            id="doctor_id" name="doctor_id">
                                        <option value="">No Doctor (Optional)</option>
                                        @foreach($doctors ?? [] as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }}
                                                @if($doctor->user && $doctor->user->email)
                                                    - {{ $doctor->user->email }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select the doctor ordering this lab test</small>
                                </div>
                                @endif

                                <div class="form-group mb-3">
                                    <label for="medical_record_id" class="form-label">Related Medical Record</label>
                                    <select class="form-control @error('medical_record_id') is-invalid @enderror" 
                                            id="medical_record_id" name="medical_record_id">
                                        <option value="">No Medical Record (Optional)</option>
                                        @foreach($medicalRecords ?? [] as $record)
                                            <option value="{{ $record->id }}" 
                                                    data-patient-id="{{ $record->patient_id }}"
                                                    {{ old('medical_record_id') == $record->id ? 'selected' : '' }}>
                                                {{ $record->patient->first_name }} {{ $record->patient->last_name }} - 
                                                {{ $record->created_at->format('M d, Y') }} 
                                                ({{ $record->presenting_complaint ?? $record->chief_complaint ?? 'No complaint' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('medical_record_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="collection_date" class="form-label">Collection Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('collection_date') is-invalid @enderror" 
                                           id="collection_date" name="collection_date" 
                                           value="{{ old('collection_date', date('Y-m-d')) }}" required>
                                    @error('collection_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="test_name" class="form-label">Test Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('test_name') is-invalid @enderror" 
                                           id="test_name" name="test_name" value="{{ old('test_name') }}" 
                                           placeholder="Enter test name (e.g., Complete Blood Count, Lipid Panel)" required>
                                    @error('test_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="test_type" class="form-label">Test Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('test_type') is-invalid @enderror" 
                                            id="test_type" name="test_type" required>
                                        <option value="">Select Test Type</option>
                                        <option value="blood" {{ old('test_type') === 'blood' ? 'selected' : '' }}>Blood Test</option>
                                        <option value="urine" {{ old('test_type') === 'urine' ? 'selected' : '' }}>Urine Test</option>
                                        <option value="stool" {{ old('test_type') === 'stool' ? 'selected' : '' }}>Stool Test</option>
                                        <option value="imaging" {{ old('test_type') === 'imaging' ? 'selected' : '' }}>Imaging</option>
                                        <option value="biopsy" {{ old('test_type') === 'biopsy' ? 'selected' : '' }}>Biopsy</option>
                                        <option value="culture" {{ old('test_type') === 'culture' ? 'selected' : '' }}>Culture</option>
                                        <option value="molecular" {{ old('test_type') === 'molecular' ? 'selected' : '' }}>Molecular</option>
                                        <option value="other" {{ old('test_type') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('test_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="test_category" class="form-label">Test Category <span class="text-danger">*</span></label>
                                    <select class="form-control @error('test_category') is-invalid @enderror" 
                                            id="test_category" name="test_category" required>
                                        <option value="">Select Category</option>
                                        <option value="hematology" {{ old('test_category') === 'hematology' ? 'selected' : '' }}>Hematology</option>
                                        <option value="biochemistry" {{ old('test_category') === 'biochemistry' ? 'selected' : '' }}>Biochemistry</option>
                                        <option value="microbiology" {{ old('test_category') === 'microbiology' ? 'selected' : '' }}>Microbiology</option>
                                        <option value="immunology" {{ old('test_category') === 'immunology' ? 'selected' : '' }}>Immunology</option>
                                        <option value="pathology" {{ old('test_category') === 'pathology' ? 'selected' : '' }}>Pathology</option>
                                        <option value="radiology" {{ old('test_category') === 'radiology' ? 'selected' : '' }}>Radiology</option>
                                        <option value="cardiology" {{ old('test_category') === 'cardiology' ? 'selected' : '' }}>Cardiology</option>
                                        <option value="other" {{ old('test_category') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('test_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="specimen_type" class="form-label">Specimen Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('specimen_type') is-invalid @enderror" 
                                            id="specimen_type" name="specimen_type" required>
                                        <option value="">Select Specimen Type</option>
                                        <option value="blood" {{ old('specimen_type') === 'blood' ? 'selected' : '' }}>Blood</option>
                                        <option value="serum" {{ old('specimen_type') === 'serum' ? 'selected' : '' }}>Serum</option>
                                        <option value="plasma" {{ old('specimen_type') === 'plasma' ? 'selected' : '' }}>Plasma</option>
                                        <option value="urine" {{ old('specimen_type') === 'urine' ? 'selected' : '' }}>Urine</option>
                                        <option value="stool" {{ old('specimen_type') === 'stool' ? 'selected' : '' }}>Stool</option>
                                        <option value="saliva" {{ old('specimen_type') === 'saliva' ? 'selected' : '' }}>Saliva</option>
                                        <option value="tissue" {{ old('specimen_type') === 'tissue' ? 'selected' : '' }}>Tissue</option>
                                        <option value="swab" {{ old('specimen_type') === 'swab' ? 'selected' : '' }}>Swab</option>
                                        <option value="csf" {{ old('specimen_type') === 'csf' ? 'selected' : '' }}>CSF</option>
                                        <option value="other" {{ old('specimen_type') === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('specimen_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="report_date" class="form-label">Report Date</label>
                                    <input type="date" class="form-control @error('report_date') is-invalid @enderror" 
                                           id="report_date" name="report_date" 
                                           value="{{ old('report_date') }}" min="{{ date('Y-m-d') }}">
                                    @error('report_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty to set as today</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-microscope me-2"></i>Test Results</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="form-group mb-3">
                            <label for="results" class="form-label">Test Results</label>
                            <textarea class="form-control @error('results') is-invalid @enderror" 
                                      id="results" name="results" rows="4" 
                                      placeholder="Enter detailed test results, measurements, and findings">{{ old('results') }}</textarea>
                            @error('results')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="normal_range" class="form-label">Normal Range</label>
                            <input type="text" class="form-control @error('normal_range') is-invalid @enderror" 
                                   id="normal_range" name="normal_range" value="{{ old('normal_range') }}" 
                                   placeholder="Enter normal range for the test (e.g., 4.5-11.0 x10³/µL)">
                            @error('normal_range')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="interpretation" class="form-label">Test Interpretation</label>
                            <textarea class="form-control @error('interpretation') is-invalid @enderror" 
                                      id="interpretation" name="interpretation" rows="3" 
                                      placeholder="Medical interpretation of the results and clinical significance">{{ old('interpretation') }}</textarea>
                            @error('interpretation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="technician_notes" class="form-label">Technician Notes</label>
                            <textarea class="form-control @error('technician_notes') is-invalid @enderror" 
                                      id="technician_notes" name="technician_notes" rows="3" 
                                      placeholder="Any technical notes, observations, or comments from the laboratory">{{ old('technician_notes') }}</textarea>
                            @error('technician_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="report_file" class="form-label">Report File (Optional)</label>
                            <input type="file" class="form-control @error('report_file') is-invalid @enderror" 
                                   id="report_file" name="report_file" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            @error('report_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 5MB)</small>
                        </div>
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
                                <i class="fas fa-save me-1"></i>Create Lab Report
                            </button>
                            <a href="{{ route('staff.lab-reports.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Guidelines</h5>
                    </div>
                    <div class="doctor-card-body">
                        <ul class="mb-0 text-muted small">
                            <li class="mb-2"><strong>Required fields:</strong> Patient, test date, and test name</li>
                            <li class="mb-2"><strong>Documentation:</strong> Be thorough and objective in your descriptions.</li>
                            <li class="mb-2"><strong>Privacy:</strong> All lab reports are confidential and GDPR compliant</li>
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
    // Set test date to today by default
    if (!$('#test_date').val()) {
        $('#test_date').val(new Date().toISOString().split('T')[0]);
    }

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 30000);
});
</script>
@endpush

