@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Lab Report')
@section('page-title', 'Edit Lab Report')
@section('page-subtitle', 'Update lab report details and test information')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Edit Lab Report</h1>
                    <p class="text-muted mb-0">Update lab report details and test information</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.lab-reports.index') }}">Lab Reports</a></li>
                        <li class="breadcrumb-item active">Edit Report</li>
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

    <form action="{{ route('staff.lab-reports.update', $labReport->id) }}" method="POST" id="labReportForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-doctor-card-header">
                        <h5 class="doctor-doctor-card-title mb-0">
                            <i class="fas fa-user-md me-2"></i>Basic Information
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <!-- Patient Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                                @if(auth()->user()->role === 'doctor' || auth()->user()->role === 'admin')
                                    <select name="patient_id" id="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id', $labReport->patient_id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} - ID: {{ $patient->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="patient_id" value="{{ $labReport->patient_id }}">
                                    <input type="text" class="form-control-plaintext" readonly 
                                           value="{{ $labReport->patient->first_name }} {{ $labReport->patient->last_name }} - ID: {{ $labReport->patient_id }}">
                                @endif
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Doctor Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label fw-semibold">Ordering Doctor</label>
                                @if(auth()->user()->role !== 'doctor')
                                    <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                                        <option value="">No Doctor (Optional)</option>
                                        @foreach($doctors ?? [] as $doctor)
                                            <option value="{{ $doctor->id }}" {{ old('doctor_id', $labReport->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }}
                                                @if($doctor->user && $doctor->user->email)
                                                    - {{ $doctor->user->email }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select the doctor ordering this lab test</small>
                                @else
                                    <input type="hidden" name="doctor_id" value="{{ $labReport->doctor_id }}">
                                    <input type="text" class="form-control-plaintext" readonly 
                                           value="{{ $labReport->doctor ? $labReport->doctor->title . ' ' . $labReport->doctor->first_name . ' ' . $labReport->doctor->last_name : 'No Doctor Assigned' }}">
                                @endif
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Collection Date -->
                            <div class="col-md-6 mb-3">
                                <label for="collection_date" class="form-label fw-semibold">Collection Date <span class="text-danger">*</span></label>
                                <input type="date" name="collection_date" id="collection_date" 
                                       class="form-control @error('collection_date') is-invalid @enderror" 
                                       value="{{ old('collection_date', $labReport->collection_date->format('Y-m-d')) }}" required>
                                @error('collection_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Report Number -->
                            <div class="col-md-6 mb-3">
                                <label for="report_number" class="form-label fw-semibold">Report Number</label>
                                <input type="text" name="report_number" id="report_number" 
                                       class="form-control @error('report_number') is-invalid @enderror" 
                                       value="{{ old('report_number', $labReport->report_number) }}"
                                       placeholder="Auto-generated if empty">
                                @error('report_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    <option value="pending" {{ old('status', $labReport->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ old('status', $labReport->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ old('status', $labReport->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $labReport->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority', $labReport->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', $labReport->priority) === 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority', $labReport->priority) === 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $labReport->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="stat" {{ old('priority', $labReport->priority) === 'stat' ? 'selected' : '' }}>STAT (Immediate)</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Details -->
                <div class="doctor-card mb-4">
                    <div class="doctor-doctor-card-header">
                        <h5 class="doctor-doctor-card-title mb-0">
                            <i class="fas fa-flask me-2"></i>Test Details
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="test_category" class="form-label fw-semibold">Test Category <span class="text-danger">*</span></label>
                                <select name="test_category" id="test_category" class="form-select @error('test_category') is-invalid @enderror" required>
                                    <option value="">Select Category</option>
                                    <option value="hematology" {{ old('test_category', $labReport->test_category) === 'hematology' ? 'selected' : '' }}>Hematology</option>
                                    <option value="biochemistry" {{ old('test_category', $labReport->test_category) === 'biochemistry' ? 'selected' : '' }}>Biochemistry</option>
                                    <option value="microbiology" {{ old('test_category', $labReport->test_category) === 'microbiology' ? 'selected' : '' }}>Microbiology</option>
                                    <option value="immunology" {{ old('test_category', $labReport->test_category) === 'immunology' ? 'selected' : '' }}>Immunology</option>
                                    <option value="pathology" {{ old('test_category', $labReport->test_category) === 'pathology' ? 'selected' : '' }}>Pathology</option>
                                    <option value="radiology" {{ old('test_category', $labReport->test_category) === 'radiology' ? 'selected' : '' }}>Radiology</option>
                                    <option value="other" {{ old('test_category', $labReport->test_category) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('test_category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="test_name" class="form-label fw-semibold">Test Name <span class="text-danger">*</span></label>
                                <input type="text" name="test_name" id="test_name" 
                                       class="form-control @error('test_name') is-invalid @enderror" 
                                       value="{{ old('test_name', $labReport->test_name) }}" 
                                       required placeholder="Enter test name">
                                @error('test_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="test_type" class="form-label fw-semibold">Test Type <span class="text-danger">*</span></label>
                                <select name="test_type" id="test_type" class="form-select @error('test_type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="blood" {{ old('test_type', $labReport->test_type) === 'blood' ? 'selected' : '' }}>Blood Test</option>
                                    <option value="urine" {{ old('test_type', $labReport->test_type) === 'urine' ? 'selected' : '' }}>Urine Test</option>
                                    <option value="stool" {{ old('test_type', $labReport->test_type) === 'stool' ? 'selected' : '' }}>Stool Test</option>
                                    <option value="imaging" {{ old('test_type', $labReport->test_type) === 'imaging' ? 'selected' : '' }}>Imaging</option>
                                    <option value="biopsy" {{ old('test_type', $labReport->test_type) === 'biopsy' ? 'selected' : '' }}>Biopsy</option>
                                    <option value="culture" {{ old('test_type', $labReport->test_type) === 'culture' ? 'selected' : '' }}>Culture</option>
                                    <option value="other" {{ old('test_type', $labReport->test_type) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('test_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="specimen_type" class="form-label fw-semibold">Specimen Type <span class="text-danger">*</span></label>
                                <select name="specimen_type" id="specimen_type" class="form-select @error('specimen_type') is-invalid @enderror" required>
                                    <option value="">Select Specimen</option>
                                    <option value="blood" {{ old('specimen_type', $labReport->specimen_type) === 'blood' ? 'selected' : '' }}>Blood</option>
                                    <option value="serum" {{ old('specimen_type', $labReport->specimen_type) === 'serum' ? 'selected' : '' }}>Serum</option>
                                    <option value="plasma" {{ old('specimen_type', $labReport->specimen_type) === 'plasma' ? 'selected' : '' }}>Plasma</option>
                                    <option value="urine" {{ old('specimen_type', $labReport->specimen_type) === 'urine' ? 'selected' : '' }}>Urine</option>
                                    <option value="stool" {{ old('specimen_type', $labReport->specimen_type) === 'stool' ? 'selected' : '' }}>Stool</option>
                                    <option value="saliva" {{ old('specimen_type', $labReport->specimen_type) === 'saliva' ? 'selected' : '' }}>Saliva</option>
                                    <option value="tissue" {{ old('specimen_type', $labReport->specimen_type) === 'tissue' ? 'selected' : '' }}>Tissue</option>
                                    <option value="swab" {{ old('specimen_type', $labReport->specimen_type) === 'swab' ? 'selected' : '' }}>Swab</option>
                                    <option value="other" {{ old('specimen_type', $labReport->specimen_type) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('specimen_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Clinical Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="clinical_history" class="form-label fw-semibold">Clinical History</label>
                                <textarea name="clinical_history" id="clinical_history" rows="3" 
                                          class="form-control @error('clinical_history') is-invalid @enderror" 
                                          placeholder="Patient's clinical history relevant to the test...">{{ old('clinical_history', $labReport->clinical_history) }}</textarea>
                                @error('clinical_history')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="test_indication" class="form-label fw-semibold">Test Indication</label>
                                <textarea name="test_indication" id="test_indication" rows="3" 
                                          class="form-control @error('test_indication') is-invalid @enderror" 
                                          placeholder="Reason for ordering this test...">{{ old('test_indication', $labReport->test_indication) }}</textarea>
                                @error('test_indication')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results & Notes -->
                <div class="doctor-card mb-4">
                    <div class="doctor-doctor-card-header">
                        <h5 class="doctor-doctor-card-title mb-0">
                            <i class="fas fa-comment-medical me-2"></i>Results & Notes
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="results" class="form-label fw-semibold">Test Results</label>
                                <textarea name="results" id="results" rows="5" 
                                          class="form-control @error('results') is-invalid @enderror" 
                                          placeholder="Enter detailed test results...">{{ old('results', $labReport->results) }}</textarea>
                                @error('results')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="technician_notes" class="form-label fw-semibold">Technician Notes</label>
                                <textarea name="technician_notes" id="technician_notes" rows="3" 
                                          class="form-control @error('technician_notes') is-invalid @enderror" 
                                          placeholder="Technical notes, observations...">{{ old('technician_notes', $labReport->technician_notes) }}</textarea>
                                @error('technician_notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expected_completion_date" class="form-label fw-semibold">Expected Completion Date</label>
                                <input type="date" name="expected_completion_date" id="expected_completion_date" 
                                       class="form-control @error('expected_completion_date') is-invalid @enderror" 
                                       value="{{ old('expected_completion_date', $labReport->expected_completion_date ? $labReport->expected_completion_date->format('Y-m-d') : '') }}" 
                                       min="{{ date('Y-m-d') }}">
                                @error('expected_completion_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="report_file" class="form-label fw-semibold">Update Report File</label>
                                <input type="file" name="report_file" id="report_file" 
                                       class="form-control @error('report_file') is-invalid @enderror" 
                                       accept=".pdf,.doc,.docx,.jpg,.png">
                                @error('report_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max: 10MB)</small>
                                @if($labReport->report_file)
                                    <div class="mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-file me-1"></i>Current file: {{ basename($labReport->report_file) }}
                                            <a href="{{ route('staff.lab-reports.download', $labReport) }}" class="ms-2 text-decoration-none">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </small>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="report_date" class="form-label fw-semibold">Report Date</label>
                                <input type="date" name="report_date" id="report_date" 
                                       class="form-control @error('report_date') is-invalid @enderror" 
                                       value="{{ old('report_date', $labReport->report_date ? $labReport->report_date->format('Y-m-d') : '') }}">
                                @error('report_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Date when results were finalized</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header bg-light py-3">
                        <h6 class="doctor-doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Update Lab Report
                            </button>
                            <a href="{{ route('staff.lab-reports.show', $labReport->id) }}" class="btn btn-info">
                                <i class="fas fa-eye me-2"></i>View Report
                            </a>
                            @if($labReport->report_file)
                                <a href="{{ route('staff.lab-reports.download', $labReport) }}" class="btn btn-success">
                                    <i class="fas fa-download me-2"></i>Download File
                                </a>
                            @endif
                            <a href="{{ route('staff.lab-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="doctor-card">
                    <div class="doctor-doctor-card-header">
                        <h6 class="doctor-doctor-card-title mb-0">Current Information</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Report ID</small>
                            <strong>#{{ $labReport->id }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Report Number</small>
                            <strong>{{ $labReport->report_number }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Patient</small>
                            <strong>{{ $labReport->patient->first_name }} {{ $labReport->patient->last_name }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Doctor</small>
                            <strong>Dr. {{ $labReport->doctor->name }}</strong>
                        </div>
                        @if($labReport->technician)
                            <div class="mb-3">
                                <small class="text-muted d-block">Technician</small>
                                <strong>{{ $labReport->technician->name }}</strong>
                            </div>
                        @endif
                        <div class="mb-3">
                            <small class="text-muted d-block">Current Status</small>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'in_progress' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$labReport->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $labReport->status)) }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created Date</small>
                            <strong>{{ $labReport->created_at->format('M d, Y') }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Last Updated</small>
                            <strong>{{ $labReport->updated_at->format('M d, Y g:i A') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test category change - suggest common tests
    document.getElementById('test_category').addEventListener('change', function() {
        const category = this.value;
        const testNameField = document.getElementById('test_name');
        
        if (category && !testNameField.value) {
            let suggestions = {
                'hematology': 'Complete Blood Count (CBC)',
                'biochemistry': 'Basic Metabolic Panel',
                'microbiology': 'Culture and Sensitivity',
                'immunology': 'Immunoglobulin Panel',
                'pathology': 'Histopathology Examination',
                'radiology': 'X-Ray Examination'
            };
            
            if (suggestions[category]) {
                testNameField.setAttribute('placeholder', 'e.g., ' + suggestions[category]);
            }
        }
    });

    // Test type change - auto-select specimen type
    document.getElementById('test_type').addEventListener('change', function() {
        const testType = this.value;
        const specimenField = document.getElementById('specimen_type');
        
        if (testType && !specimenField.value) {
            let specimenMapping = {
                'blood': 'blood',
                'urine': 'urine',
                'stool': 'stool',
                'culture': 'swab'
            };
            
            if (specimenMapping[testType]) {
                specimenField.value = specimenMapping[testType];
            }
        }
    });

    // Priority change - set expected completion date
    document.getElementById('priority').addEventListener('change', function() {
        const priority = this.value;
        const completionField = document.getElementById('expected_completion_date');
        
        if (priority && !completionField.value) {
            let days = {
                'stat': 0,
                'urgent': 1,
                'high': 2,
                'normal': 3,
                'low': 7
            };
            
            if (days[priority] !== undefined) {
                const completionDate = new Date();
                completionDate.setDate(completionDate.getDate() + days[priority]);
                completionField.value = completionDate.toISOString().split('T')[0];
            }
        }
    });

    // Form validation
    document.getElementById('labReportForm').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = this.querySelectorAll('[required]');
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
    });

    // File upload validation
    document.getElementById('report_file').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File size must be less than 10MB');
                this.value = '';
                return;
            }
            
            const allowedTypes = ['application/pdf', 'application/msword', 
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                alert('Please select a valid file type (PDF, DOC, DOCX, JPG, PNG)');
                this.value = '';
                return;
            }
        }
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 30000);

    // Real-time validation
    document.addEventListener('blur', function(e) {
        if (e.target.hasAttribute('required') && !e.target.value.trim()) {
            e.target.classList.add('is-invalid');
        } else {
            e.target.classList.remove('is-invalid');
        }
    }, true);
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    border-radius: 10px;
}

.doctor-card-header {
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

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.alert {
    border-radius: 8px;
    border: none;
}

.form-control-plaintext {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
}
</style>
@endpush
@endsection
