<!-- Pharmacist Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending Prescriptions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['pending_prescriptions'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-prescription-bottle fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Dispensed Today</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['dispensed_today'] ?? 0 }}</div>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['low_stock_items'] ?? 0 }}</div>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Inventory</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['total_inventory'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pharmacist Specific Content -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pharmacist Dashboard</h6>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-pills fa-5x text-gray-300 mb-3"></i>
                <h4 class="text-gray-800">Welcome, Pharmacist!</h4>
                <p class="text-gray-500">Manage prescriptions, inventory, and medication dispensing.</p>
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
                    @if($user->hasPermission('prescriptions.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-prescription fa-2x mb-2"></i>
                            <span class="fw-bold">Prescriptions</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('prescriptions.update'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-boxes fa-2x mb-2"></i>
                            <span class="fw-bold">Inventory</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('prescriptions.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-plus-circle fa-2x mb-2"></i>
                            <span class="fw-bold">New Prescription</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('reports.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <span class="fw-bold">Reports</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('prescriptions.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span class="fw-bold">Drug Search</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('prescriptions.update'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                            <span class="fw-bold">Stock Check</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
