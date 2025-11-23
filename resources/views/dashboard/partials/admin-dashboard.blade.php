<!-- Enhanced Admin Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card primary animate__animated animate__fadeInUp">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Total Users</div>
                    <div class="stat-number">{{ number_format($data['total_users'] ?? 0) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +12% from last month
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card success animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-number">{{ number_format($data['total_patients'] ?? 0) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +8% from last month
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card info animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Total Appointments</div>
                    <div class="stat-number">{{ number_format($data['total_appointments'] ?? 0) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +15% from last month
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #0891b2);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card warning animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Today's Appointments</div>
                    <div class="stat-number">{{ number_format($data['today_appointments'] ?? 0) }}</div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i>
                        -3% from yesterday
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Admin Charts and Data -->
<div class="row">
    <!-- Interactive Dashboard Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="enhanced-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">System Analytics</h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-line me-1"></i> View
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="switchChart('daily')">Daily</a></li>
                        <li><a class="dropdown-item" href="#" onclick="switchChart('weekly')">Weekly</a></li>
                        <li><a class="dropdown-item" href="#" onclick="switchChart('monthly')">Monthly</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="adminChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- System Health Cards -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="enhanced-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle me-3" style="background: linear-gradient(135deg, var(--success), #059669);">
                                <i class="fas fa-heartbeat"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold">System Health</h6>
                                <small class="text-muted">All systems operational</small>
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar" style="background: linear-gradient(90deg, var(--success), #059669); width: 95%;" role="progressbar"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Excellent</small>
                            <small class="text-success font-weight-bold">95%</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="enhanced-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle me-3" style="background: linear-gradient(135deg, var(--info), #0891b2);">
                                <i class="fas fa-database"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold">Storage Usage</h6>
                                <small class="text-muted">12.4 GB / 20 GB used</small>
                            </div>
                        </div>
                        <div class="progress mb-2" style="height: 8px; border-radius: 10px;">
                            <div class="progress-bar" style="background: linear-gradient(90deg, var(--info), #0891b2); width: 62%;" role="progressbar"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">Good</small>
                            <small class="text-info font-weight-bold">62%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Activity Timeline -->
    <div class="col-xl-4 col-lg-5">
        <div class="enhanced-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>Recent Activities
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshActivities()" data-bs-toggle="tooltip" title="Refresh">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div class="activity-timeline">
                    @if(!empty($data['recent_activities']) && count($data['recent_activities']) > 0)
                        @foreach($data['recent_activities'] as $index => $activity)
                            <div class="timeline-item">
                                <div class="timeline-marker" style="background: {{ ['var(--primary)', 'var(--success)', 'var(--info)', 'var(--warning)'][$index % 4] }};">
                                    <i class="fas {{ ['fa-user', 'fa-calendar', 'fa-file', 'fa-cog'][$index % 4] }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="fw-bold mb-1">{{ $activity['title'] ?? 'System Activity' }}</div>
                                    <div class="text-muted small mb-2">{{ $activity['message'] ?? $activity }}</div>
                                    <div class="text-xs text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $activity['time'] ?? 'Just now' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <div class="icon-circle mx-auto mb-3" style="background: var(--gray-200); color: var(--gray-500);">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <p class="text-muted mb-0">No recent activities</p>
                            <small class="text-muted">Activities will appear here as they occur</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Quick Stats Mini Cards -->
        <div class="row">
            <div class="col-6 mb-3">
                <div class="enhanced-card text-center">
                    <div class="card-body py-3">
                        <div class="stat-number" style="font-size: 1.5rem;">{{ $data['active_staff'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.7rem;">Active Staff</div>
                    </div>
                </div>
            </div>
            <div class="col-6 mb-3">
                <div class="enhanced-card text-center">
                    <div class="card-body py-3">
                        <div class="stat-number" style="font-size: 1.5rem; color: var(--warning);">{{ $data['pending_approvals'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.7rem;">Pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Management Shortcuts -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Management</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <div class="mb-2">
                            <div class="text-gray-800 text-xs font-weight-bold text-uppercase mb-1">Active Staff</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $data['active_staff'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="mb-2">
                            <div class="text-gray-800 text-xs font-weight-bold text-uppercase mb-1">Pending Approvals</div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">{{ $data['pending_approvals'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Appointment Analytics</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <div class="mb-2">
                            <div class="text-gray-800 text-xs font-weight-bold text-uppercase mb-1">Pending</div>
                            <div class="h6 mb-0 font-weight-bold text-warning">{{ $data['pending_appointments'] ?? 0 }}</div>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="mb-2">
                            <div class="text-gray-800 text-xs font-weight-bold text-uppercase mb-1">Completed</div>
                            <div class="h6 mb-0 font-weight-bold text-success">{{ $data['completed_appointments'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
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
                    @if($user->hasPermission('users.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.users.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span class="fw-bold">Add User</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('appointments.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <span class="fw-bold">Schedule</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('medical_records.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.medical-records.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-file-medical fa-2x mb-2"></i>
                            <span class="fw-bold">New Record</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('reports.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <span class="fw-bold">Reports</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('settings.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.settings.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-cogs fa-2x mb-2"></i>
                            <span class="fw-bold">Settings</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('patients.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-user-injured fa-2x mb-2"></i>
                            <span class="fw-bold">Add Patient</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize admin dashboard chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('adminChart');
    if (ctx) {
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Patients',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Appointments',
                    data: [2, 3, 20, 5, 1, 4],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});

// Chart switching function
function switchChart(period) {
    console.log('Switching to ' + period + ' view');
    // Implement chart data switching logic here
}

// Refresh activities function
function refreshActivities() {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    icon.classList.add('fa-spin');
    
    // Simulate API call
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        showNotification('Activities refreshed successfully!', 'success');
    }, 1000);
}
</script>
