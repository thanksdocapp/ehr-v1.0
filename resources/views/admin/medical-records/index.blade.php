@extends('admin.layouts.app')

@section('title', 'Medical Records')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Medical Records</li>
@endsection

@push('styles')
<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.table-actions .btn {
    margin: 0 2px;
    padding: 0.375rem 0.75rem;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
}

.record-type-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
}

.filter-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">Medical Records</h1>
                    <p class="modern-page-subtitle">Manage patient medical records, diagnoses, and treatment history</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ contextRoute('medical-records.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i>New Medical Record
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced fade-in-up stagger-1">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['total'] ?? 0) }}</div>
                        <div class="stat-label">Total Records</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced fade-in-up stagger-2">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['this_month'] ?? 0) }}</div>
                        <div class="stat-label">This Month</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem; background: linear-gradient(135deg, #f6c23e 0%, #f093fb 100%); color: white; border-radius: 15px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: white;">{{ $stats['consultations'] }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Consultations</div>
                    </div>
                    <div class="stat-icon" style="width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-stethoscope text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem; background: linear-gradient(135deg, #e74a3b 0%, #fd79a8 100%); color: white; border-radius: 15px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: white;">{{ $stats['prescriptions'] }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Prescriptions</div>
                    </div>
                    <div class="stat-icon" style="width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-prescription-bottle-alt text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="patient_id" class="form-label">Patient</label>
                <select class="form-control" id="patient_id" name="patient_id">
                    <option value="">All Patients</option>
                    @foreach($patients as $patient)
                        @if($patient && $patient->full_name)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="doctor_id" class="form-label">Doctor</label>
                <select class="form-control" id="doctor_id" name="doctor_id">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        @if($doctor && $doctor->full_name)
                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                {{ formatDoctorName($doctor->full_name) }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="record_type" class="form-label">Type</label>
                <select class="form-control" id="record_type" name="record_type">
                    <option value="">All Types</option>
                    <option value="consultation" {{ request('record_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                    <option value="diagnosis" {{ request('record_type') == 'diagnosis' ? 'selected' : '' }}>Diagnosis</option>
                    <option value="prescription" {{ request('record_type') == 'prescription' ? 'selected' : '' }}>Prescription</option>
                    <option value="lab_result" {{ request('record_type') == 'lab_result' ? 'selected' : '' }}>Lab Result</option>
                    <option value="follow_up" {{ request('record_type') == 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                    <option value="discharge" {{ request('record_type') == 'discharge' ? 'selected' : '' }}>Discharge</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="text" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                       placeholder="dd-mm-yyyy" 
                       pattern="\d{2}-\d{2}-\d{4}" 
                       maxlength="10">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="text" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                       placeholder="dd-mm-yyyy" 
                       pattern="\d{2}-\d{2}-\d{4}" 
                       maxlength="10">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by diagnosis, symptoms, treatment, or patient name">
            </div>
            <div class="col-md-6">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ contextRoute('medical-records.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-1"></i>Reset
                    </a>
                    <a href="{{ contextRoute('medical-records.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Add Record
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Medical Records Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="doctor-card-title mb-0">
                    <i class="fas fa-list me-2"></i>Medical Records List
                </h5>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" 
                            id="exportDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="doctor-card-body p-0">
            @if($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Type</th>
                                <th>Diagnosis</th>
                                <th>Symptoms</th>
                                <th>Date</th>
                                <th>Privacy</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $record)
                                <tr>
                                    <td>
                                        <div>
                                            @if($record->patient)
                                                <strong class="text-primary">{{ $record->patient->full_name }}</strong><br>
                                                <small class="text-muted">{{ $record->patient->patient_id }}</small>
                                            @else
                                                <div class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <strong>Patient Deleted</strong><br>
                                                    <small class="text-muted">Patient record no longer exists</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($record->doctor)
                                            <div>
                                                <strong>{{ formatDoctorName($record->doctor->full_name) }}</strong><br>
                                                <small class="text-muted">{{ $record->doctor->specialization }}</small>
                                            </div>
                                        @else
                                            <div class="text-danger">
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                <strong>Doctor Deleted</strong><br>
                                                <small class="text-muted">ID: {{ $record->doctor_id }}</small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="record-type-badge badge bg-{{ 
                                            $record->record_type === 'consultation' ? 'primary' : 
                                            ($record->record_type === 'diagnosis' ? 'info' : 
                                            ($record->record_type === 'prescription' ? 'warning' : 
                                            ($record->record_type === 'lab_result' ? 'success' : 
                                            ($record->record_type === 'follow_up' ? 'secondary' : 'dark')))) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $record->record_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $record->diagnosis }}">
                                            {{ $record->diagnosis ? Str::limit($record->diagnosis, 30) : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span title="{{ $record->symptoms }}">
                                            {{ $record->symptoms ? Str::limit($record->symptoms, 30) : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ formatDate($record->created_at) }}</strong><br>
                                            <small class="text-muted">{{ $record->created_at->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($record->is_private)
                                            <span class="badge bg-danger"><i class="fas fa-lock me-1"></i>Private</span>
                                        @else
                                            <span class="badge bg-success"><i class="fas fa-unlock me-1"></i>Public</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('admin.medical-records.show', $record) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.medical-records.edit', $record) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit Record">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteRecord({{ $record->id }}); return false;" 
                                                    title="Delete Record">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} results
                    </div>
                    {{ $records->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-medical fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No medical records found</h5>
                    <p class="text-muted">No records match your current filters.</p>
                    <a href="{{ contextRoute('medical-records.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-1"></i>Add First Record
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when select changes
    $('#patient_id, #doctor_id, #record_type').change(function() {
        $(this).closest('form').submit();
    });

    // Clear search on escape
    $('#search').keyup(function(e) {
        if (e.keyCode === 27) { // Escape key
            $(this).val('');
        }
    });
});

// Delete medical record function
function deleteRecord(recordId) {
    console.log('Delete medical record called with ID:', recordId);
    
    // Prevent any default behavior if event exists
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }
    
    // Handle both sync and async confirm dialogs
    function handleConfirmation(confirmResult) {
        console.log('User confirmation result:', confirmResult);
        
        if (confirmResult === true) {
            console.log('User confirmed deletion, proceeding...');
            
            // Add a small delay to ensure the dialog is properly closed
            setTimeout(() => {
                console.log('Creating form for deletion...');
                
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/medical-records/${recordId}`;
                form.style.display = 'none';
                
                // Add CSRF token - try multiple methods
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                
                // Try to get CSRF token from meta tag or Laravel's global
                let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                    csrfTokenValue = Laravel.csrfToken;
                }
                if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                    csrfTokenValue = window.Laravel.csrfToken;
                }
                
                csrfToken.value = csrfTokenValue;
                form.appendChild(csrfToken);
                
                console.log('CSRF token:', csrfTokenValue);
                
                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                console.log('Form children:', form.children);
                
                // Add form to document and submit
                document.body.appendChild(form);
                console.log('Form added to document, submitting...');
                form.submit();
            }, 100);
        } else {
            console.log('User cancelled deletion');
        }
    }
    
    // Use a more explicit confirmation dialog
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this medical record?\n\nThis action cannot be undone and will remove:\n- Patient medical information\n- Diagnosis and treatment data\n- Vital signs and notes\n- All associated record data\n\nClick OK to confirm deletion or Cancel to abort.');
    
    // Handle both Promise and boolean returns
    if (confirmDelete && typeof confirmDelete.then === 'function') {
        // If it's a Promise, wait for it to resolve
        confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
    } else {
        // If it's a boolean, handle it directly
        handleConfirmation(confirmDelete);
    }
    
    return false;
}
</script>
@endpush
