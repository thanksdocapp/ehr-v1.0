@extends('admin.layouts.app')

@section('title', 'Lab Reports Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Lab Reports Management</h5>
            <small class="text-muted">Manage laboratory test reports and results</small>
        </div>
        <div>
            <a href="{{ contextRoute('lab-reports.create') }}" class="btn btn-doctor-primary">
                <i class="fas fa-plus me-2"></i>Add Lab Report
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-primary" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['total'] }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Lab Reports</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-flask text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-warning" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['pending'] }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Pending Reports</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-success" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['completed'] }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Completed Reports</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #16a34a); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-info" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['this_month'] }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">This Month</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #0891b2); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filter Lab Reports
            </h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ contextRoute('lab-reports.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="patient_id">Patient</label>
                            <select name="patient_id" id="patient_id" class="form-control">
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
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="doctor_id">Doctor</label>
                            <select name="doctor_id" id="doctor_id" class="form-control">
                                <option value="">All Doctors</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="test_type">Test Type</label>
                            <select name="test_type" id="test_type" class="form-control">
                                <option value="">All Test Types</option>
                                @foreach($testTypes as $testType)
                                    <option value="{{ $testType }}" {{ request('test_type') == $testType ? 'selected' : '' }}>
                                        {{ ucfirst($testType) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">Date From</label>
                            <input type="text" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                                   placeholder="dd-mm-yyyy" 
                                   pattern="\d{2}-\d{2}-\d{4}" 
                                   maxlength="10">
                            <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">Date To</label>
                            <input type="text" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                                   placeholder="dd-mm-yyyy" 
                                   pattern="\d{2}-\d{2}-\d{4}" 
                                   maxlength="10">
                            <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Search by test name, type, patient name..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-doctor-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ contextRoute('lab-reports.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lab Reports Table -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">Lab Reports List</h5>
        </div>
        <div class="doctor-card-body">
            @if($labReports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Test Details</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Report File</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labReports as $labReport)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ formatDate($labReport->test_date) }}</strong>
                                            <small class="text-muted">{{ $labReport->test_date->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($labReport->patient)
                                                <strong>{{ $labReport->patient->full_name }}</strong>
                                                <small class="text-muted">ID: {{ $labReport->patient->patient_id }}</small>
                                            @else
                                                <div class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <strong>Patient Deleted</strong>
                                                    <small class="d-block text-muted">Patient record no longer exists</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $labReport->doctor->full_name }}</strong>
                                            <small class="text-muted">{{ $labReport->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $labReport->test_name }}</strong>
                                            <small class="text-muted">Type: {{ ucfirst($labReport->test_type) }}</small>
                                            @if($labReport->lab_technician)
                                                <small class="text-muted">Tech: {{ $labReport->lab_technician }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClasses = [
                                                'pending' => 'warning',
                                                'in_progress' => 'info',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $statusClass = $statusClasses[$labReport->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $labReport->status)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $priorityClasses = [
                                                'normal' => 'success',
                                                'urgent' => 'warning',
                                                'stat' => 'danger'
                                            ];
                                            $priorityClass = $priorityClasses[$labReport->priority] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $priorityClass }}">{{ ucfirst($labReport->priority) }}</span>
                                    </td>
                                    <td>
                                        @if($labReport->report_file)
                                            <a href="{{ contextRoute('lab-reports.download', $labReport) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        @else
                                            <span class="text-muted">No file</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ contextRoute('lab-reports.show', $labReport) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ contextRoute('lab-reports.edit', $labReport) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $labReport->id }})" title="Delete">
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
                <div class="d-flex justify-content-center">
                    {{ $labReports->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Lab Reports Found</h5>
                    <p class="text-muted">No lab reports match your current filters.</p>
                    <a href="{{ contextRoute('lab-reports.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-1"></i>Create First Lab Report
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when select changes
    $('#patient_id, #doctor_id, #test_type, #status').change(function() {
        $(this).closest('form').submit();
    });

    // Clear search on escape
    $('#search').keyup(function(e) {
        if (e.keyCode === 27) { // Escape key
            $(this).val('');
        }
    });
});

// Delete lab report function
function confirmDelete(labReportId) {
    console.log('Delete lab report called with ID:', labReportId);
    
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
                form.action = `/admin/lab-reports/${labReportId}`;
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
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this lab report?\n\nThis action cannot be undone and will remove:\n- Lab report details and test results\n- Patient medical history record\n- All associated test data and files\n- Laboratory analysis and interpretations\n\nClick OK to confirm deletion or Cancel to abort.');
    
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
@endsection
