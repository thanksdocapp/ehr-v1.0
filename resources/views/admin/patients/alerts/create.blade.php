@extends('admin.layouts.app')

@section('title', 'Create Alert')

@section('content')
<div class="container-fluid">
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

    <form action="{{ route('admin.patients.alerts.store', $patient) }}" method="POST" id="alertCreateForm">
        @csrf
        
        <div class="row">
            <!-- Form Content -->
            <div class="col-lg-8">
                <!-- Alert Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Alert Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label fw-semibold">Alert Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    @foreach($alertTypes as $type)
                                        <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label fw-semibold">Category/Code <span class="text-danger">*</span></label>
                                <select name="code" id="code" class="form-select @error('code') is-invalid @enderror" required>
                                    <option value="">Select Type First</option>
                                </select>
                                <small class="text-muted">Select alert type first to see available categories</small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="severity" class="form-label fw-semibold">Severity</label>
                                <select name="severity" id="severity" class="form-select @error('severity') is-invalid @enderror">
                                    <option value="">Use Default from Category</option>
                                    @foreach($alertSeverities as $severity)
                                        <option value="{{ $severity }}" {{ old('severity') === $severity ? 'selected' : '' }}>
                                            {{ ucfirst($severity) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">If not specified, default from category will be used</small>
                                @error('severity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="title" class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" id="title" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       value="{{ old('title') }}" 
                                       placeholder="Auto-generated from category">
                                <small class="text-muted">If not specified, will be auto-generated from category</small>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="5" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Enter full description and context of the alert..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="expires_at" class="form-label fw-semibold">Expires At (Review Date)</label>
                                <input type="datetime-local" name="expires_at" id="expires_at" 
                                       class="form-control @error('expires_at') is-invalid @enderror" 
                                       value="{{ old('expires_at') }}">
                                <small class="text-muted">Optional: Set a review/expiry date for this alert</small>
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="active" name="active" value="1" 
                                           {{ old('active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        <strong>Active Alert</strong>
                                    </label>
                                    <small class="text-muted d-block">Uncheck to create inactive alert</small>
                                </div>
                                @if(Auth::user()->is_admin || Auth::user()->role === 'admin' || Auth::user()->role === 'doctor')
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" 
                                           id="restricted" name="restricted" value="1" 
                                           {{ old('restricted') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="restricted">
                                        <strong>Restricted Alert</strong>
                                        <i class="fas fa-lock ms-1 text-warning" title="Only visible to Admin and Doctors"></i>
                                    </label>
                                    <small class="text-muted d-block">Restricted alerts are only visible to Admin and Doctors</small>
                                </div>
                                @endif
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Alert
                            </button>
                            <a href="{{ route('admin.patients.alerts.index', $patient) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Alerts
                            </a>
                            <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-user me-2"></i>Back to Patient
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Patient Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0 fw-semibold">Patient Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Name</small>
                            <strong>{{ $patient->full_name }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Patient ID</small>
                            <strong>{{ $patient->patient_id }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Date of Birth</small>
                            <strong>{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'Not provided' }}</strong>
                            @if($patient->date_of_birth)
                                <small class="text-muted">({{ $patient->age }} years old)</small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Alert Categories</h6>
                    <p class="small mb-0">
                        <strong>Clinical:</strong> Allergies, DNAR, medication risks<br>
                        <strong>Safeguarding:</strong> Child/adult safeguarding, domestic abuse<br>
                        <strong>Behaviour:</strong> Violence risk, staff safety concerns<br>
                        <strong>Communication:</strong> Interpreter needs, disabilities<br>
                        <strong>Admin:</strong> Missing documents, consent issues<br>
                        <strong>Medication:</strong> Drug interactions, contraindications
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const alertCategories = @json($alertCategories);
    
    // Update code dropdown when type changes
    $('#type').on('change', function() {
        const type = $(this).val();
        const codeSelect = $('#code');
        
        codeSelect.empty();
        
        if (type && alertCategories[type]) {
            codeSelect.append('<option value="">Select Category</option>');
            
            $.each(alertCategories[type], function(code, config) {
                const option = $('<option>')
                    .val(code)
                    .text(code.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()))
                    .data('config', config);
                
                if ('{{ old("code") }}' === code) {
                    option.prop('selected', true);
                }
                
                codeSelect.append(option);
            });
        } else {
            codeSelect.append('<option value="">Select Type First</option>');
        }
    });
    
    // Auto-fill severity and title when code is selected
    $('#code').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const config = selectedOption.data('config');
        
        if (config) {
            // Set default severity if not already set
            if (!$('#severity').val() && config.default_severity) {
                $('#severity').val(config.default_severity);
            }
            
            // Set default title if not already set
            if (!$('#title').val() && config.default_title_prefix) {
                const codeLabel = $(this).find('option:selected').text();
                $('#title').val(config.default_title_prefix + codeLabel);
            }
            
            // Set restricted if default
            if (config.restricted && !$('#restricted').is(':checked')) {
                $('#restricted').prop('checked', config.restricted);
            }
        }
    });
    
    // Trigger type change on page load if type is already selected
    if ($('#type').val()) {
        $('#type').trigger('change');
    }
});
</script>
@endpush

