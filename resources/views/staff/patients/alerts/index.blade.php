@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Patient Alerts')

@section('page-title', 'Patient Alerts')
@section('page-subtitle', 'Manage alerts for ' . $patient->full_name)

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

    <!-- Header Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Alerts for {{ $patient->full_name }}</h5>
            <small class="text-muted">Patient ID: {{ $patient->patient_id }}</small>
        </div>
        <div>
            <a href="{{ route('staff.patients.show', $patient) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Patient
            </a>
            @can('create', [\App\Models\PatientAlert::class, $patient])
            <a href="{{ route('staff.patients.alerts.create', $patient) }}" class="btn btn-doctor-primary">
                <i class="fas fa-plus me-2"></i>Add Alert
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.patients.alerts.index', $patient) }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">All Alerts</option>
                        <option value="active" {{ request('filter') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('filter') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Severity</label>
                    <select name="severity" class="form-select" onchange="this.form.submit()">
                        <option value="">All Severities</option>
                        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Type</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        @foreach(config('alerts.types', []) as $type)
                            <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="{{ route('staff.patients.alerts.index', $patient) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-2"></i>Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">Alerts List</h5>
        </div>
        <div class="doctor-card-body">
            @if($alerts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
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
                                        <span class="badge bg-secondary">{{ ucfirst($alert->type) }}</span>
                                        @if($alert->code)
                                            <br><small class="text-muted">{{ $alert->code }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('staff.patients.alerts.show', [$patient, $alert]) }}" class="text-decoration-none">
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
                                            <a href="{{ route('staff.patients.alerts.show', [$patient, $alert]) }}" 
                                               class="btn btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('update', $alert)
                                            <a href="{{ route('staff.patients.alerts.edit', [$patient, $alert]) }}" 
                                               class="btn btn-outline-info" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('toggleActive', $alert)
                                            <form action="{{ route('staff.patients.alerts.toggle-active', [$patient, $alert]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-outline-{{ $alert->active ? 'warning' : 'success' }}" 
                                                        title="{{ $alert->active ? 'Deactivate' : 'Activate' }}"
                                                        onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-{{ $alert->active ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            @endcan
                                            @can('delete', $alert)
                                            <form action="{{ route('staff.patients.alerts.destroy', [$patient, $alert]) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-outline-danger" 
                                                        title="Delete"
                                                        onclick="return confirm('Are you sure you want to delete this alert?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @endcan
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
                    <p class="text-muted">No alerts found for this patient.</p>
                    @can('create', [\App\Models\PatientAlert::class, $patient])
                    <a href="{{ route('staff.patients.alerts.create', $patient) }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Add First Alert
                    </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

