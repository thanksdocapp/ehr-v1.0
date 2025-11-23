<!-- Receptionist Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Today's Appointments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['today_appointments'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Registered Patients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['total_patients'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Appointments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['pending_appointments'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Walk-ins Today</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['walkins_today'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-walking fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receptionist Specific Content -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Receptionist Dashboard</h6>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-user-tie fa-5x text-gray-300 mb-3"></i>
                <h4 class="text-gray-800">Welcome, Receptionist!</h4>
                <p class="text-gray-500">Manage appointments, patient registration, and front desk operations.</p>
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
                    @if($user->hasPermission('appointments.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <span class="fw-bold">Book Appointment</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('patients.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span class="fw-bold">Register Patient</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('patients.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-search fa-2x mb-2"></i>
                            <span class="fw-bold">Patient Search</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('appointments.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <span class="fw-bold">View Schedule</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('appointments.update'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-edit fa-2x mb-2"></i>
                            <span class="fw-bold">Modify Booking</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('patients.update'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-phone fa-2x mb-2"></i>
                            <span class="fw-bold">Contact Patient</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
