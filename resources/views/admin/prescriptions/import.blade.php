@extends('admin.layouts.app')

@section('title', 'Import Prescriptions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.prescriptions.index') }}">Prescriptions</a></li>
    <li class="breadcrumb-item active">Import CSV</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Import Prescriptions from CSV</h1>
            <p class="text-muted mb-0">Bulk import prescription data from a CSV file</p>
        </div>
        <div>
            <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Prescriptions
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
                    <form action="{{ route('admin.prescriptions.import.csv') }}" method="POST" enctype="multipart/form-data" id="importForm">
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
                            <select class="form-select @error('import_mode') is-invalid @enderror" 
                                    id="import_mode" 
                                    name="import_mode" 
                                    required>
                                <option value="insert">Insert Only (Skip existing records)</option>
                                <option value="update">Update Only (Update existing records)</option>
                                <option value="upsert" selected>Upsert (Create or update)</option>
                                <option value="skip">Skip Duplicates (Skip if exists)</option>
                            </select>
                            <small class="form-text text-muted">
                                Choose how to handle existing records based on prescription number or patient, doctor, and prescription date
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
                                    Skip Errors (Continue importing even if some rows fail)
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                When enabled, rows with errors will be skipped and the import will continue
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Start Import
                            </button>
                            <a href="{{ route('admin.prescriptions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Instructions Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>Import Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Required Fields:</h6>
                    <ul class="list-unstyled mb-4">
                        <li><i class="fas fa-check text-success me-2"></i>Patient ID or Patient Email</li>
                        <li><i class="fas fa-check text-success me-2"></i>Doctor ID or Doctor Email</li>
                        <li><i class="fas fa-check text-success me-2"></i>Prescription Date</li>
                        <li><i class="fas fa-check text-success me-2"></i>Medication Name (at least one)</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Optional Fields:</h6>
                    <ul class="list-unstyled mb-4 small">
                        <li>Appointment ID, Medical Record ID</li>
                        <li>Prescription Number (auto-generated if not provided)</li>
                        <li>Prescribed Date, Expiry Date, Follow Up Date</li>
                        <li>Prescription Type (new, refill, modification, emergency)</li>
                        <li>Status (pending, approved, dispensed, cancelled)</li>
                        <li>Diagnosis, Notes</li>
                        <li>Medication Dosage, Frequency, Duration, Form, Instructions</li>
                        <li>Refills Allowed (integer)</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Prescription Types:</h6>
                    <ul class="list-unstyled mb-4 small">
                        <li>new, refill, modification, emergency</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Status Values:</h6>
                    <ul class="list-unstyled mb-4 small">
                        <li>pending, approved, dispensed, cancelled</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Medication Formats:</h6>
                    <ul class="list-unstyled mb-4 small">
                        <li>Option 1: Individual columns (Medication Name, Dosage, Frequency, etc.)</li>
                        <li>Option 2: JSON array in Medications column</li>
                        <li>Option 3: Pipe-separated: Name|Dosage|Frequency|Duration|Form|Instructions</li>
                    </ul>

                    <h6 class="fw-bold mb-3">Tips:</h6>
                    <ul class="list-unstyled small mb-0">
                        <li><i class="fas fa-lightbulb text-warning me-2"></i><strong>Date format: MM/DD/YYYY (e.g., 01/15/2024) - Required format</strong></li>
                        <li><i class="fas fa-lightbulb text-warning me-2"></i>Patient can be identified by ID or Email</li>
                        <li><i class="fas fa-lightbulb text-warning me-2"></i>Doctor can be identified by ID or Email</li>
                        <li><i class="fas fa-lightbulb text-warning me-2"></i>At least one medication is required per prescription</li>
                    </ul>
                </div>
            </div>

            <!-- CSV Format Example -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2 text-secondary"></i>CSV Format Example
                    </h5>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-2 rounded small">Patient Email,Doctor Email,Prescription Date,Medication Name,Dosage,Frequency,Duration,Diagnosis
john.doe@example.com,doctor@example.com,01/15/2024,Amoxicillin,500mg,Twice daily,7 days,Upper respiratory infection</pre>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Form validation
        $('#importForm').on('submit', function(e) {
            const fileInput = $('#csv_file')[0];
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a CSV file to upload');
                return false;
            }

            const file = fileInput.files[0];
            if (file.size > 10 * 1024 * 1024) { // 10MB
                e.preventDefault();
                alert('File size exceeds 10MB limit');
                return false;
            }

            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Importing...');
        });
    });
</script>
@endpush
@endsection

