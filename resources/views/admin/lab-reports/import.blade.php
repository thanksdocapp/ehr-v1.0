@extends('admin.layouts.app')

@section('title', 'Import Lab Reports')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.lab-reports.index') }}">Lab Reports</a></li>
    <li class="breadcrumb-item active">Import CSV</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Import Lab Reports from CSV</h1>
            <p class="text-muted mb-0">Bulk import lab report data from a CSV file</p>
        </div>
        <div>
            <a href="{{ route('admin.lab-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Lab Reports
            </a>
        </div>
    </div>

    <!-- Alerts -->
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

    @if(session('import_stats'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Import Statistics:</strong><br>
            Total Rows: {{ session('import_stats')['total'] ?? 0 }}<br>
            Created: {{ session('import_stats')['created'] ?? 0 }}<br>
            Updated: {{ session('import_stats')['updated'] ?? 0 }}<br>
            Skipped: {{ session('import_stats')['skipped'] ?? 0 }}<br>
            @if(!empty(session('import_stats')['errors']))
                <strong>Errors:</strong> {{ count(session('import_stats')['errors']) }}
                <details class="mt-2">
                    <summary>View Errors</summary>
                    <ul class="mb-0 mt-2">
                        @foreach(session('import_stats')['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Import Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-upload me-2 text-primary"></i>Upload CSV File
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.lab-reports.import.csv') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label for="csv_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                            <input type="file" 
                                   class="form-control @error('csv_file') is-invalid @enderror" 
                                   id="csv_file" 
                                   name="csv_file" 
                                   accept=".csv,.txt"
                                   required>
                            <small class="form-text text-muted">
                                Maximum file size: 10MB. Supported formats: CSV, TXT
                            </small>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Import Mode -->
                        <div class="mb-4">
                            <label for="import_mode" class="form-label">Import Mode <span class="text-danger">*</span></label>
                            <select class="form-control @error('import_mode') is-invalid @enderror" 
                                    id="import_mode" 
                                    name="import_mode" 
                                    required>
                                <option value="insert">Insert Only (Skip existing)</option>
                                <option value="update">Update Only (Skip new)</option>
                                <option value="upsert">Insert or Update (Create or Update)</option>
                                <option value="skip">Skip Duplicates</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Insert:</strong> Only create new records (skip if exists)<br>
                                <strong>Update:</strong> Only update existing records (skip if new)<br>
                                <strong>Upsert:</strong> Create new or update existing<br>
                                <strong>Skip:</strong> Skip rows with duplicate report numbers
                            </small>
                            @error('import_mode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Skip Errors -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="skip_errors" 
                                       name="skip_errors" 
                                       value="1">
                                <label class="form-check-label" for="skip_errors">
                                    Skip rows with errors and continue import
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                If checked, rows with errors will be skipped and errors will be logged. Otherwise, import will stop on first error.
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Import Lab Reports
                            </button>
                            <a href="{{ route('admin.lab-reports.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- CSV Format Guide -->
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>CSV Format Guide
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Required Fields:</h6>
                    <ul class="list-unstyled mb-3">
                        <li><i class="fas fa-check text-success me-2"></i>Patient ID or Patient Email</li>
                        <li><i class="fas fa-check text-success me-2"></i>Doctor ID or Doctor Email</li>
                        <li><i class="fas fa-check text-success me-2"></i>Test Name</li>
                        <li><i class="fas fa-check text-success me-2"></i>Test Type</li>
                        <li><i class="fas fa-check text-success me-2"></i>Test Date (MM/DD/YYYY)</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Optional Fields:</h6>
                    <ul class="list-unstyled mb-3 small">
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Appointment ID</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Medical Record ID</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Report Number</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Test Category</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Specimen Type</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Collection Date (MM/DD/YYYY)</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Report Date (MM/DD/YYYY)</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Results</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Normal Range</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Reference Range</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Reference Values</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Interpretation</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Status (pending, in_progress, completed, cancelled)</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Priority (normal, urgent, stat)</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Lab Technician</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Technician Name</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Technician Notes</li>
                        <li><i class="fas fa-circle text-muted me-2" style="font-size: 0.5rem;"></i>Notes</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Date Format:</h6>
                    <p class="small text-muted mb-3">
                        <strong>Important:</strong> All dates must be in <strong>MM/DD/YYYY</strong> format.<br>
                        Examples: 01/15/2024, 12/31/2023
                    </p>

                    <h6 class="fw-bold mb-3">Example CSV:</h6>
                    <div class="bg-light p-3 rounded small font-monospace" style="font-size: 0.75rem; max-height: 200px; overflow-y: auto;">
Patient ID,Doctor ID,Test Name,Test Type,Test Date,Status,Priority<br>
123,1,Complete Blood Count,blood,01/15/2024,completed,normal<br>
123,1,Lipid Profile,blood,01/15/2024,completed,normal<br>
124,2,X-Ray Chest,radiology,01/16/2024,pending,urgent<br>
                    </div>
                </div>
            </div>

            <!-- Tips -->
            <div class="card border-warning mt-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Import Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li class="mb-2">Ensure CSV file has headers in the first row</li>
                        <li class="mb-2">Patient and Doctor can be identified by ID or Email</li>
                        <li class="mb-2">Report Number will be auto-generated if not provided</li>
                        <li class="mb-2">Status defaults to "pending" if not specified</li>
                        <li class="mb-2">Priority defaults to "normal" if not specified</li>
                        <li>Date format must be MM/DD/YYYY (e.g., 01/15/2024)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#importForm').on('submit', function(e) {
        const fileInput = $('#csv_file')[0];
        if (fileInput.files.length === 0) {
            e.preventDefault();
            alert('Please select a CSV file to import.');
            return false;
        }

        const file = fileInput.files[0];
        if (file.size > 10240 * 1024) { // 10MB
            e.preventDefault();
            alert('File size exceeds 10MB limit.');
            return false;
        }

        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i>Importing...').prop('disabled', true);
    });
});
</script>
@endpush


