@php
    // Get active alerts for the patient
    $activeAlerts = $patient->activeAlerts()
        ->with(['creator'])
        ->orderByRaw("FIELD(severity, 'critical', 'high', 'medium', 'low', 'info')")
        ->orderBy('created_at', 'desc')
        ->get()
        ->filter(function($alert) {
            // Filter by user permissions using policy
            return auth()->check() && auth()->user()->can('view', $alert);
        });
@endphp

@if($activeAlerts->count() > 0)
    <div class="patient-alert-bar mb-4" style="border-left: 4px solid #dc3545; background: #fff3cd; border-radius: 8px; padding: 1rem;">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <h6 class="mb-1 fw-bold">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>
                    Patient Alerts ({{ $activeAlerts->count() }})
                </h6>
                <small class="text-muted">Please review all alerts before proceeding</small>
            </div>
            @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'doctor'))
                @php
                    // Admin routes are already prefixed with 'admin.', staff routes with 'staff.'
                    $routePrefix = auth()->user()->role === 'admin' ? 'admin.patients.alerts' : 'staff.patients.alerts';
                @endphp
                <a href="{{ route($routePrefix . '.index', $patient) }}" 
                   class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-cog me-1"></i>Manage Alerts
                </a>
            @endif
        </div>
        
        <div class="alert-badges d-flex flex-wrap gap-2">
            @foreach($activeAlerts as $alert)
                <button type="button" 
                        class="btn btn-sm alert-badge alert-badge-{{ $alert->severity }}"
                        data-bs-toggle="modal" 
                        data-bs-target="#alertModal{{ $alert->id }}"
                        style="border-radius: 20px; padding: 0.25rem 0.75rem; font-size: 0.875rem; border: none;">
                    <i class="fas fa-{{ $alert->type_icon }} me-1"></i>
                    {{ $alert->title }}
                    @if($alert->restricted)
                        <i class="fas fa-lock ms-1" style="font-size: 0.75rem;"></i>
                    @endif
                </button>

                <!-- Alert Detail Modal -->
                <div class="modal fade" id="alertModal{{ $alert->id }}" tabindex="-1" aria-labelledby="alertModalLabel{{ $alert->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-{{ $alert->severity_color }} text-white">
                                <h5 class="modal-title" id="alertModalLabel{{ $alert->id }}">
                                    <i class="fas fa-{{ $alert->type_icon }} me-2"></i>{{ $alert->title }}
                                    @if($alert->restricted)
                                        <i class="fas fa-lock ms-2" title="Restricted Alert"></i>
                                    @endif
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Severity</label>
                                    <div>
                                        <span class="badge bg-{{ $alert->severity_color }}">
                                            <i class="fas fa-{{ $alert->severity_icon }} me-1"></i>
                                            {{ ucfirst($alert->severity) }}
                                        </span>
                                        <span class="badge bg-secondary ms-2">{{ ucfirst($alert->type) }}</span>
                                        @if($alert->restricted)
                                            <span class="badge bg-warning text-dark ms-2">
                                                <i class="fas fa-lock me-1"></i>Restricted
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Description</label>
                                    <div class="p-3 bg-light rounded">
                                        {!! nl2br(e($alert->description)) !!}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Patient</label>
                                        <div>{{ $patient->full_name }} ({{ $patient->patient_id }})</div>
                                    </div>
                                    @if($alert->creator)
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Created By</label>
                                        <div>
                                            {{ $alert->creator->name }}
                                            <br><small class="text-muted">{{ $alert->created_at->format('M d, Y H:i') }}</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                @if($alert->expires_at)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Review Date</label>
                                    <div>
                                        {{ $alert->expires_at->format('M d, Y H:i') }}
                                        @if($alert->isExpired())
                                            <span class="badge bg-danger ms-2">Expired</span>
                                        @else
                                            <span class="badge bg-info ms-2">Expires {{ $alert->expires_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                @if(auth()->check() && (auth()->user()->role === 'admin' || auth()->user()->role === 'doctor'))
                                    @php
                                        // Admin routes are already prefixed with 'admin.', staff routes with 'staff.'
                                        $routePrefix = auth()->user()->role === 'admin' ? 'admin.patients.alerts' : 'staff.patients.alerts';
                                    @endphp
                                    <a href="{{ route($routePrefix . '.show', [$patient, $alert]) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View Full Details
                                    </a>
                                @endif
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('styles')
    <style>
        .alert-badge {
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .alert-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .alert-badge-critical {
            background: #dc3545;
            color: white;
        }
        
        .alert-badge-critical:hover {
            background: #bb2d3b;
            color: white;
        }
        
        .alert-badge-high {
            background: #fd7e14;
            color: white;
        }
        
        .alert-badge-high:hover {
            background: #e87010;
            color: white;
        }
        
        .alert-badge-medium {
            background: #0dcaf0;
            color: #000;
        }
        
        .alert-badge-medium:hover {
            background: #0aa2c0;
            color: #000;
        }
        
        .alert-badge-low {
            background: #6c757d;
            color: white;
        }
        
        .alert-badge-low:hover {
            background: #5c636a;
            color: white;
        }
        
        .alert-badge-info {
            background: #0d6efd;
            color: white;
        }
        
        .alert-badge-info:hover {
            background: #0b5ed7;
            color: white;
        }
    </style>
    @endpush
@endif

