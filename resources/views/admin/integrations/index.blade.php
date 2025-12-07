@extends('admin.layouts.app')

@section('title', 'Integrations')
@section('page-title', 'External Integrations')

@section('content')
<div class="container-fluid">
    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="fas fa-plug fa-lg text-primary"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $modules->count() }}</h3>
                            <small class="text-muted">Total Modules</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="fas fa-check-circle fa-lg text-success"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $modules->where('is_active', true)->count() }}</h3>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                            <i class="fas fa-cog fa-lg text-warning"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $modules->where('is_configured', false)->count() }}</h3>
                            <small class="text-muted">Needs Config</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                            <i class="fas fa-exclamation-triangle fa-lg text-danger"></i>
                        </div>
                        <div>
                            <h3 class="mb-0">{{ $modules->filter(fn($m) => $m->last_error_at && $m->last_error_at->isAfter(now()->subHours(24)))->count() }}</h3>
                            <small class="text-muted">Has Errors</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Integration Modules -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-plug me-2 text-primary"></i>Available Integrations
                    </h5>
                </div>
                <div class="card-body">
                    @if($modules->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-puzzle-piece fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No integration modules available.</p>
                        </div>
                    @else
                        @foreach(['lab_tests' => 'Lab Tests', 'prescriptions' => 'Prescriptions', 'imaging' => 'Imaging/Scans'] as $type => $typeLabel)
                            @php $typeModules = $groupedModules->get($type, collect()); @endphp
                            @if($typeModules->isNotEmpty())
                                <h6 class="text-uppercase text-muted mb-3 mt-4 first:mt-0">
                                    <i class="fas {{ $type === 'lab_tests' ? 'fa-vial' : ($type === 'prescriptions' ? 'fa-prescription' : 'fa-x-ray') }} me-2"></i>
                                    {{ $typeLabel }}
                                </h6>

                                <div class="row g-3 mb-4">
                                    @foreach($typeModules as $module)
                                        <div class="col-md-6">
                                            <div class="card h-100 {{ $module->is_active ? 'border-success' : 'border-light' }}">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="mb-1">{{ $module->name }}</h6>
                                                            <span class="badge {{ $module->getStatusBadgeClass() }}">
                                                                {{ $module->getStatusText() }}
                                                            </span>
                                                            @if($module->environment === 'sandbox')
                                                                <span class="badge bg-info">Sandbox</span>
                                                            @endif
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <form action="{{ route('admin.integrations.toggle-status', $module) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="checkbox"
                                                                       class="form-check-input"
                                                                       {{ $module->is_active ? 'checked' : '' }}
                                                                       {{ !$module->is_configured ? 'disabled' : '' }}
                                                                       onchange="this.form.submit()">
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <p class="small text-muted mb-3">{{ Str::limit($module->description, 100) }}</p>

                                                    @if($module->last_error_at && $module->last_error_at->isAfter(now()->subHours(24)))
                                                        <div class="alert alert-danger py-1 px-2 mb-2 small">
                                                            <i class="fas fa-exclamation-circle me-1"></i>
                                                            {{ Str::limit($module->last_error_message, 50) }}
                                                        </div>
                                                    @endif

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            @if($module->last_sync_at)
                                                                Last sync: {{ $module->last_sync_at->diffForHumans() }}
                                                            @else
                                                                Never synced
                                                            @endif
                                                        </small>
                                                        <a href="{{ route('admin.integrations.show', $module) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-cog me-1"></i>Configure
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-history me-2 text-primary"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted small mb-0">No recent activity</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentRequests as $request)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-semibold small">{{ $request->integrationModule->name }}</div>
                                            <small class="text-muted">
                                                {{ $request->request_type }} -
                                                {{ $request->patient ? $request->patient->full_name : 'N/A' }}
                                            </small>
                                        </div>
                                        <span class="badge {{ $request->getStatusBadgeClass() }}">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">
                        <i class="fas fa-info-circle me-2 text-info"></i>About Integrations
                    </h6>
                    <p class="small text-muted mb-2">
                        External integrations allow you to connect with third-party services for:
                    </p>
                    <ul class="small text-muted mb-3">
                        <li><strong>Lab Tests:</strong> Order blood tests and receive results</li>
                        <li><strong>Prescriptions:</strong> Send e-prescriptions to pharmacies</li>
                        <li><strong>Imaging:</strong> Refer for scans and receive reports</li>
                    </ul>
                    <p class="small text-muted mb-0">
                        Contact each provider to obtain API credentials before configuring.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
