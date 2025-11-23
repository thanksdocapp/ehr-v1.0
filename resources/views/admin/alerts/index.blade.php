@extends('admin.layouts.app')

@section('title', 'Patient Alerts')

@section('content')
<div class="container-fluid">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Patient Alerts</h5>
            <small class="text-muted">View and manage all patient alerts across the system</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger me-2">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $alerts->total() }} Total Alerts
            </span>
            <div class="dropdown">
                <button class="btn btn-doctor-primary dropdown-toggle" type="button" id="createAlertBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-plus me-2"></i>Create Alert
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createAlertBtn" style="max-height: 400px; overflow-y: auto; min-width: 300px; padding: 0;">
                    <li class="dropdown-header sticky-top bg-white" style="z-index: 10;">
                        <i class="fas fa-user me-2"></i>Select Patient
                    </li>
                    <li>
                        <div class="px-3 py-2 border-bottom">
                            <input type="text" class="form-control form-control-sm" id="patientSearchInput" placeholder="Search by name or patient ID..." autocomplete="off" style="border-radius: 0.25rem;">
                        </div>
                    </li>
                    <li><hr class="dropdown-divider my-0"></li>
                    <div id="patientDropdownList">
                        @if($patients->count() > 0)
                            @foreach($patients as $patient)
                                <li class="patient-dropdown-item" data-search-text="{{ strtolower($patient->full_name . ' ' . $patient->patient_id . ' ' . ($patient->email ?? '')) }}">
                                    <a class="dropdown-item" href="{{ route('admin.patients.alerts.create', $patient) }}">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder bg-info text-white rounded-circle me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; font-size: 0.875rem;">
                                                {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $patient->full_name }}</div>
                                                <small class="text-muted">{{ $patient->patient_id }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li>
                                <span class="dropdown-item-text text-muted">
                                    <i class="fas fa-info-circle me-2"></i>No patients available
                                </span>
                            </li>
                        @endif
                    </div>
                    <li id="noPatientResults" class="dropdown-item-text text-muted" style="display: none;">
                        <i class="fas fa-search me-2"></i>No patients found
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.alerts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Alerts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Severity</label>
                    <select name="severity" class="form-select" onchange="this.form.submit()">
                        <option value="">All Severities</option>
                        @foreach($severities ?? config('alerts.severities', ['critical', 'high', 'medium', 'low', 'info']) as $severity)
                            <option value="{{ $severity }}" {{ request('severity') === $severity ? 'selected' : '' }}>
                                {{ ucfirst($severity) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Type</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach(array_keys($alertCategories) as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Patient</label>
                    <select name="patient_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Patients</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }} ({{ $patient->patient_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <a href="{{ route('admin.alerts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">All Patient Alerts</h5>
        </div>
        <div class="card-body">
            @if($alerts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Type</th>
                                <th>Title</th>
                                <th>Severity</th>
                                <th>Restricted</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts as $alert)
                                @can('view', $alert)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.patients.show', $alert->patient) }}" class="text-decoration-none">
                                            <strong>{{ $alert->patient->full_name }}</strong>
                                        </a>
                                        <br><small class="text-muted">{{ $alert->patient->patient_id }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($alert->type) }}</span>
                                        @if($alert->code)
                                            <br><small class="text-muted">{{ $alert->code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.patients.alerts.show', [$alert->patient, $alert]) }}" class="text-decoration-none">
                                            <strong>{{ $alert->title }}</strong>
                                        </a>
                                        @if($alert->restricted)
                                            <i class="fas fa-lock text-warning ms-1" title="Restricted Alert"></i>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $alert->severity_color }}">
                                            <i class="fas fa-{{ $alert->severity_icon }} me-1"></i>
                                            {{ ucfirst($alert->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($alert->restricted)
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-lock me-1"></i>Restricted
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Public</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($alert->isActive())
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                        @if($alert->isExpired())
                                            <br><small class="text-danger">Expired</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $alert->created_at->format('M d, Y') }}</small>
                                        @if($alert->creator)
                                            <br><small class="text-muted">by {{ $alert->creator->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.patients.alerts.show', [$alert->patient, $alert]) }}" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $alert)
                                            <a href="{{ route('admin.patients.alerts.edit', [$alert->patient, $alert]) }}" 
                                               class="btn btn-outline-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            <a href="{{ route('admin.patients.show', $alert->patient) }}" 
                                               class="btn btn-outline-success" title="View Patient">
                                                <i class="fas fa-user"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endcan
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $alerts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No alerts found matching your filters.</p>
                    @if(request()->anyFilled(['status', 'severity', 'type', 'patient_id']))
                        <a href="{{ route('admin.alerts.index') }}" class="btn btn-primary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const searchInput = $('#patientSearchInput');
    const patientItems = $('.patient-dropdown-item');
    const noResults = $('#noPatientResults');
    
    // Prevent dropdown from closing when clicking on search input
    searchInput.on('click', function(e) {
        e.stopPropagation();
    });
    
    // Filter patients on search input
    searchInput.on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        let hasVisibleItems = false;
        
        if (searchTerm === '') {
            // Show all items
            patientItems.show();
            noResults.hide();
        } else {
            // Filter items
            patientItems.each(function() {
                const searchText = $(this).data('search-text') || '';
                if (searchText.includes(searchTerm)) {
                    $(this).show();
                    hasVisibleItems = true;
                } else {
                    $(this).hide();
                }
            });
            
            // Show/hide no results message
            if (hasVisibleItems) {
                noResults.hide();
            } else {
                noResults.show();
                patientItems.hide();
            }
        }
    });
    
    // Clear search when dropdown is closed
    $('.dropdown').on('hidden.bs.dropdown', function() {
        searchInput.val('');
        patientItems.show();
        noResults.hide();
    });
    
    // Focus search input when dropdown is shown
    $('#createAlertBtn').on('click', function() {
        setTimeout(function() {
            searchInput.focus();
        }, 100);
    });
});
</script>
@endpush
@endsection

