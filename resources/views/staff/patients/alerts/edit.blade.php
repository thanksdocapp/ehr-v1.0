@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Alert')

@section('page-title', 'Edit Alert')
@section('page-subtitle', $alert->title)

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

    <form action="{{ route('staff.patients.alerts.update', [$patient, $alert]) }}" method="POST" id="alertEditForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Form Content -->
            <div class="col-lg-8">
                <!-- Alert Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-edit me-2"></i>Edit Alert Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Type and code cannot be changed after creation. Only description, severity, status, and expiry can be modified.
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Alert Type</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-secondary">{{ ucfirst($alert->type) }}</span>
                                    <small class="text-muted d-block">Cannot be changed</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Category/Code</label>
                                <div class="form-control-plaintext">
                                    <code>{{ $alert->code }}</code>
                                    <small class="text-muted d-block">Cannot be changed</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="severity" class="form-label fw-semibold">Severity</label>
                                <select name="severity" id="severity" class="form-select @error('severity') is-invalid @enderror">
                                    @foreach($alertSeverities as $severity)
                                        <option value="{{ $severity }}" {{ old('severity', $alert->severity) === $severity ? 'selected' : '' }}>
                                            {{ ucfirst($severity) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('severity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title', $alert->title) }}">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="5" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      required>{{ old('description', $alert->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expires_at" class="form-label fw-semibold">Expires At (Review Date)</label>
                                <input type="datetime-local" name="expires_at" id="expires_at" 
                                       class="form-control @error('expires_at') is-invalid @enderror" 
                                       value="{{ old('expires_at', $alert->expires_at ? $alert->expires_at->format('Y-m-d\TH:i') : '') }}">
                                <small class="text-muted">Leave empty for no expiry</small>
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="active" name="active" value="1" 
                                           {{ old('active', $alert->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        <strong>Active Alert</strong>
                                    </label>
                                </div>
                                @if(Auth::user()->is_admin || Auth::user()->role === 'admin' || Auth::user()->role === 'doctor')
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" 
                                           id="restricted" name="restricted" value="1" 
                                           {{ old('restricted', $alert->restricted) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="restricted">
                                        <strong>Restricted Alert</strong>
                                        <i class="fas fa-lock ms-1 text-warning" title="Only visible to Admin and Doctors"></i>
                                    </label>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="col-lg-4">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Update Alert
                            </button>
                            <a href="{{ route('staff.patients.alerts.show', [$patient, $alert]) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            <a href="{{ route('staff.patients.alerts.index', $patient) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Alerts
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alert Info Card -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Current Alert Info</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Type</small>
                            <strong>{{ ucfirst($alert->type) }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Severity</small>
                            <span class="badge bg-{{ $alert->severity_color }}">{{ ucfirst($alert->severity) }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Status</small>
                            @if($alert->isActive())
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        @if($alert->restricted)
                        <div class="mb-0">
                            <small class="text-muted d-block">Restricted</small>
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-lock me-1"></i>Restricted
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Patient Info Card -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Patient Information</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Name</small>
                            <strong><a href="{{ route('staff.patients.show', $patient) }}" class="text-decoration-none">{{ $patient->full_name }}</a></strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Patient ID</small>
                            <strong>{{ $patient->patient_id }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

