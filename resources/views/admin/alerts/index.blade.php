@extends('admin.layouts.app')

@section('title', 'Patient Alerts')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Patient Alerts</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">Patient Alerts</h1>
                    <p class="modern-page-subtitle">View and manage all patient alerts across the system</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge-modern badge-modern-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>{{ number_format($alerts->total()) }} total
                    </span>
                    <div class="dropdown">
                        <button class="btn-modern btn-modern-primary dropdown-toggle" type="button" id="createAlertBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus"></i>Create Alert
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="createAlertBtn" style="max-height: 420px; overflow-y: auto; min-width: 340px; padding: 0;">
                            <li class="dropdown-header sticky-top bg-white" style="z-index: 10;">
                                <i class="fas fa-user me-2"></i>Select Patient
                            </li>
                            <li>
                                <div class="px-3 py-2 border-bottom">
                                    <input type="text" class="form-control form-control-sm" id="patientSearchInput" placeholder="Search by name, patient ID, or email..." autocomplete="off">
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
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-bell"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['total'] ?? $alerts->total()) }}</div>
                        <div class="stat-label">Total Alerts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['active'] ?? 0) }}</div>
                        <div class="stat-label">Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['inactive'] ?? 0) }}</div>
                        <div class="stat-label">Inactive / Expired</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['critical_active'] ?? 0) }}</div>
                        <div class="stat-label">Critical (Active)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-filter"></i>Filters</h5>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ contextRoute('alerts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="modern-form-label">Status</label>
                    <select name="status" class="modern-form-select" onchange="this.form.submit()">
                        <option value="">All Alerts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="modern-form-label">Severity</label>
                    <select name="severity" class="modern-form-select" onchange="this.form.submit()">
                        <option value="">All Severities</option>
                        @foreach($severities ?? config('alerts.severities', ['critical', 'high', 'medium', 'low', 'info']) as $severity)
                            <option value="{{ $severity }}" {{ request('severity') === $severity ? 'selected' : '' }}>
                                {{ ucfirst($severity) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="modern-form-label">Type</label>
                    <select name="type" class="modern-form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach(array_keys($alertCategories) as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="modern-form-label">Patient</label>
                    <select name="patient_id" class="modern-form-select" onchange="this.form.submit()">
                        <option value="">All Patients</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }} ({{ $patient->patient_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <a href="{{ contextRoute('alerts.index') }}" class="btn-modern btn-modern-outline">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-list"></i>All Patient Alerts</h5>
        </div>
        <div class="modern-card-body">
            @if($alerts->count() > 0)
                <div class="modern-table-wrapper">
                    <table class="modern-table">
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
                                        <small>{{ formatDateUk($alert->created_at) }}</small>
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
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #f1f5f9;">
                    <div class="text-muted small">
                        Showing {{ $alerts->firstItem() }} to {{ $alerts->lastItem() }} of {{ $alerts->total() }} alerts
                    </div>
                    {{ $alerts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No alerts found matching your filters.</p>
                    @if(request()->anyFilled(['status', 'severity', 'type', 'patient_id']))
                        <a href="{{ contextRoute('alerts.index') }}" class="btn-modern btn-modern-primary">
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

