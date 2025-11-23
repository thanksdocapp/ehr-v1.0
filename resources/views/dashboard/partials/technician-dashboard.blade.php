<!-- Technician Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Tests</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['pending_tests'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-flask fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Today</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['completed_today'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Equipment Alerts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['equipment_alerts'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['in_progress'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-cog fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Technician Specific Content -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Lab Technician Dashboard</h6>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-microscope fa-5x text-gray-300 mb-3"></i>
                <h4 class="text-gray-800">Welcome, Lab Technician!</h4>
                <p class="text-gray-500">Manage lab tests, equipment, and generate reports.</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="enhanced-card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if($user->hasPermission('lab_reports.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-list-ul fa-2x mb-2"></i>
                            <span class="fw-bold">Lab Queue</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('lab_reports.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-file-medical-alt fa-2x mb-2"></i>
                            <span class="fw-bold">Test Results</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('lab_reports.update'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-tools fa-2x mb-2"></i>
                            <span class="fw-bold">Equipment</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('lab_reports.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span class="fw-bold">Test Search</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('reports.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <span class="fw-bold">Analytics</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('lab_reports.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span class="fw-bold">New Test</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
