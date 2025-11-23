<!-- Nurse Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Assigned Patients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['assigned_patients'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-injured fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Medications</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['pending_medications'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-pills fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Tasks</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['completed_tasks'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Urgent Alerts</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $data['urgent_alerts'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nurse Specific Content -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Nurse Dashboard</h6>
            </div>
            <div class="card-body text-center">
                <i class="fas fa-user-nurse fa-5x text-gray-300 mb-3"></i>
                <h4 class="text-gray-800">Welcome, Nurse!</h4>
                <p class="text-gray-500">Your nursing dashboard with patient care tools and medication tracking.</p>
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
                    @if($user->hasPermission('patients.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-user-injured fa-2x mb-2"></i>
                            <span class="fw-bold">Patient Care</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('prescriptions.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-pills fa-2x mb-2"></i>
                            <span class="fw-bold">Medications</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('medical_records.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.medical-records.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-heartbeat fa-2x mb-2"></i>
                            <span class="fw-bold">Vital Signs</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('appointments.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-calendar-check fa-2x mb-2"></i>
                            <span class="fw-bold">Appointments</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('lab_reports.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-flask fa-2x mb-2"></i>
                            <span class="fw-bold">Lab Reports</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('patients.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span class="fw-bold">Add Patient</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
