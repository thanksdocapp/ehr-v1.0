@extends('admin.layouts.app')

@section('title', 'Alert Details')

@section('content')
<div class="container-fluid">
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Alert Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">
                            <i class="fas fa-{{ $alert->type_icon }} me-2"></i>{{ $alert->title }}
                            @if($alert->restricted)
                                <i class="fas fa-lock text-warning ms-2" title="Restricted Alert"></i>
                            @endif
                        </h5>
                    </div>
                    <div>
                        <span class="badge bg-{{ $alert->severity_color }} me-2">
                            <i class="fas fa-{{ $alert->severity_icon }} me-1"></i>
                            {{ ucfirst($alert->severity) }}
                        </span>
                        @if($alert->isActive())
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <div class="alert-description p-3 bg-light rounded">
                            {!! nl2br(e($alert->description)) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Alert Type</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-secondary">{{ ucfirst($alert->type) }}</span>
                                @if($alert->code)
                                    <br><small class="text-muted">{{ $alert->code }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Severity</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $alert->severity_color }}">
                                    <i class="fas fa-{{ $alert->severity_icon }} me-1"></i>
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <div class="form-control-plaintext">
                                @if($alert->isActive())
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-pause-circle me-1"></i>Inactive
                                    </span>
                                @endif
                                @if($alert->isExpired())
                                    <br><small class="text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Expired
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Restricted</label>
                            <div class="form-control-plaintext">
                                @if($alert->restricted)
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-lock me-1"></i>Restricted
                                    </span>
                                    <small class="text-muted d-block">Only visible to Admin and Doctors</small>
                                @else
                                    <span class="badge bg-secondary">Public</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($alert->expires_at)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Expires At (Review Date)</label>
                            <div class="form-control-plaintext">
                                {{ $alert->expires_at->format('M d, Y H:i') }}
                                @if($alert->isExpired())
                                    <br><small class="text-danger">Expired {{ $alert->expires_at->diffForHumans() }}</small>
                                @else
                                    <br><small class="text-muted">Expires {{ $alert->expires_at->diffForHumans() }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Patient Name</label>
                            <div class="form-control-plaintext">
                                <a href="{{ route('admin.patients.show', $patient) }}" class="text-decoration-none">
                                    <strong>{{ $patient->full_name }}</strong>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Patient ID</label>
                            <div class="form-control-plaintext">{{ $patient->patient_id }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Audit Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Created By</label>
                            <div class="form-control-plaintext">
                                @if($alert->creator)
                                    <strong>{{ $alert->creator->name }}</strong>
                                    <br><small class="text-muted">{{ $alert->creator->role }}</small>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                                <br><small class="text-muted">{{ $alert->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Last Updated By</label>
                            <div class="form-control-plaintext">
                                @if($alert->updater)
                                    <strong>{{ $alert->updater->name }}</strong>
                                    <br><small class="text-muted">{{ $alert->updater->role }}</small>
                                @else
                                    <span class="text-muted">Never updated</span>
                                @endif
                                @if($alert->updated_at && $alert->updated_at->ne($alert->created_at))
                                    <br><small class="text-muted">{{ $alert->updated_at->format('M d, Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0 fw-semibold">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('update', $alert)
                        <a href="{{ route('admin.patients.alerts.edit', [$patient, $alert]) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Alert
                        </a>
                        @endcan
                        @can('toggleActive', $alert)
                        <form action="{{ route('admin.patients.alerts.toggle-active', [$patient, $alert]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-{{ $alert->active ? 'warning' : 'success' }} w-100">
                                <i class="fas fa-{{ $alert->active ? 'pause' : 'play' }} me-2"></i>
                                {{ $alert->active ? 'Deactivate' : 'Activate' }} Alert
                            </button>
                        </form>
                        @endcan
                        @can('delete', $alert)
                        <form action="{{ route('admin.patients.alerts.destroy', [$patient, $alert]) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this alert? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Delete Alert
                            </button>
                        </form>
                        @endcan
                        <a href="{{ route('admin.patients.alerts.index', $patient) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Alerts
                        </a>
                        <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-user me-2"></i>Back to Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

