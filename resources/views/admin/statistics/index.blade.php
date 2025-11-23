@extends('admin.layouts.app')

@section('title', 'Advanced Statistics Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">Advanced Statistics Dashboard</h1>
                <div class="d-flex gap-2">
                    <select class="form-select" id="dateRange">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="365">Last Year</option>
                    </select>
                    <button class="btn btn-primary" onclick="refreshStats()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-success" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Patients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalPatients">
                                {{ $statistics['total_patients'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-success mr-2">
                            <i class="fas fa-arrow-up"></i> {{ $statistics['patients_growth'] ?? 0 }}%
                        </span>
                        <span class="text-xs">Since last month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="totalAppointments">
                                {{ $statistics['total_appointments'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-success mr-2">
                            <i class="fas fa-arrow-up"></i> {{ $statistics['appointments_growth'] ?? 0 }}%
                        </span>
                        <span class="text-xs">Since last month</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Active Doctors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="activeDoctors">
                                {{ $statistics['active_doctors'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-info mr-2">
                            <i class="fas fa-arrow-right"></i> {{ $statistics['doctors_availability'] ?? 0 }}%
                        </span>
                        <span class="text-xs">Average availability</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Revenue (Monthly)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="monthlyRevenue">
                                ${{ number_format($statistics['monthly_revenue'] ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-warning mr-2">
                            <i class="fas fa-arrow-up"></i> {{ $statistics['revenue_growth'] ?? 0 }}%
                        </span>
                        <span class="text-xs">Since last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Appointments Overview</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Actions:</div>
                            <a class="dropdown-item" href="#" onclick="changeChartType('line')">Line Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('bar')">Bar Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('area')">Area Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="appointmentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Appointment Status Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Completed
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> Scheduled
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-danger"></i> Cancelled
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Department Performance</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="departmentTable">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Total Appointments</th>
                                    <th>Completed</th>
                                    <th>Completion Rate</th>
                                    <th>Revenue</th>
                                    <th>Average Rating</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['departments'] ?? [] as $department)
                                <tr>
                                    <td>{{ $department['name'] }}</td>
                                    <td>{{ $department['total_appointments'] }}</td>
                                    <td>{{ $department['completed_appointments'] }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $department['completion_rate'] }}%">
                                                {{ $department['completion_rate'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($department['revenue'], 2) }}</td>
                                    <td>
                                        <div class="rating">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $department['rating'] ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                            <span class="ml-1">{{ $department['rating'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-arrow-{{ $department['trend'] == 'up' ? 'up text-success' : ($department['trend'] == 'down' ? 'down text-danger' : 'right text-warning') }}"></i>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor Performance -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Doctors</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Appointments</th>
                                    <th>Rating</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['top_doctors'] ?? [] as $doctor)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2">
                                                {{ substr($doctor['name'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $doctor['name'] }}</div>
                                                <div class="text-muted small">{{ $doctor['specialty'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $doctor['appointments'] }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $doctor['rating'] }}</span>
                                    </td>
                                    <td>${{ number_format($doctor['revenue'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Patient Demographics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="chart-pie">
                                <canvas id="genderChart"></canvas>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="chart-pie">
                                <canvas id="ageChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center small">
                        <div class="mb-2">
                            <span class="mr-2">
                                <i class="fas fa-circle text-primary"></i> Male
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-danger"></i> Female
                            </span>
                        </div>
                        <div>
                            <span class="mr-2">
                                <i class="fas fa-circle text-success"></i> 18-30
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-warning"></i> 31-50
                            </span>
                            <span class="mr-2">
                                <i class="fas fa-circle text-info"></i> 51+
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Updates -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Real-time Activity Feed</h6>
                </div>
                <div class="card-body">
                    <div class="activity-feed" id="activityFeed" style="height: 400px; overflow-y: auto;">
                        <!-- Activity items will be dynamically loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <h4 class="text-primary">{{ $statistics['today_appointments'] ?? 0 }}</h4>
                                <small class="text-muted">Today's Appointments</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <h4 class="text-success">{{ $statistics['new_patients_today'] ?? 0 }}</h4>
                                <small class="text-muted">New Patients Today</small>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border-bottom pb-2">
                                <h4 class="text-warning">{{ $statistics['pending_appointments'] ?? 0 }}</h4>
                                <small class="text-muted">Pending Appointments</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <h4 class="text-info">{{ $statistics['occupied_rooms'] ?? 0 }}/{{ $statistics['total_rooms'] ?? 0 }}</h4>
                            <small class="text-muted">Occupied Rooms</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.chart-area {
    position: relative;
    height: 10rem;
    width: 100%;
}

.chart-pie {
    position: relative;
    height: 15rem;
    width: 100%;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
}

.activity-feed {
    position: relative;
}

.activity-item {
    display: flex;
    align-items-start;
    padding: 1rem 0;
    border-bottom: 1px solid #e3e6f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 0.875rem;
}

.activity-content {
    flex: 1;
}

.activity-time {
    font-size: 0.75rem;
    color: #6c757d;
}

.rating {
    display: flex;
    align-items: center;
}

.progress {
    height: 0.5rem;
}

@media (max-width: 768px) {
    .chart-area {
        height: 8rem;
    }
    
    .chart-pie {
        height: 10rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    initializeCharts();
    loadActivityFeed();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadActivityFeed();
        refreshStats();
    }, 30000);
});

function initializeCharts() {
    // Appointments Chart
    const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
    const appointmentsChart = new Chart(appointmentsCtx, {
        type: 'line',
        data: {
            labels: @json($statistics['appointments_chart']['labels'] ?? []),
            datasets: [{
                label: 'Appointments',
                data: @json($statistics['appointments_chart']['data'] ?? []),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'Scheduled', 'Cancelled'],
            datasets: [{
                data: @json($statistics['status_chart'] ?? []),
                backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    const genderChart = new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                data: @json($statistics['gender_chart'] ?? []),
                backgroundColor: ['#4e73df', '#e74a3b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Age Chart
    const ageCtx = document.getElementById('ageChart').getContext('2d');
    const ageChart = new Chart(ageCtx, {
        type: 'pie',
        data: {
            labels: ['18-30', '31-50', '51+'],
            datasets: [{
                data: @json($statistics['age_chart'] ?? []),
                backgroundColor: ['#1cc88a', '#f6c23e', '#36b9cc']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function loadActivityFeed() {
    // Simulate real-time activity feed
    const activities = [
        { icon: 'fas fa-user-plus', color: 'bg-success', text: 'New patient registered: John Doe', time: '2 minutes ago' },
        { icon: 'fas fa-calendar-check', color: 'bg-primary', text: 'Appointment completed with Dr. Smith', time: '5 minutes ago' },
        { icon: 'fas fa-user-md', color: 'bg-info', text: 'Dr. Johnson started consultation', time: '8 minutes ago' },
        { icon: 'fas fa-calendar-times', color: 'bg-warning', text: 'Appointment cancelled by patient', time: '12 minutes ago' },
        { icon: 'fas fa-pills', color: 'bg-secondary', text: 'Prescription issued to Mary Jane', time: '15 minutes ago' }
    ];

    const feedHtml = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-icon ${activity.color}">
                <i class="${activity.icon} text-white"></i>
            </div>
            <div class="activity-content">
                <div class="activity-text">${activity.text}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        </div>
    `).join('');

    document.getElementById('activityFeed').innerHTML = feedHtml;
}

function refreshStats() {
    // This would make an AJAX call to refresh statistics
    console.log('Refreshing statistics...');
}

function exportReport() {
    // This would generate and download a report
    alert('Report export functionality would be implemented here');
}

function changeChartType(type) {
    // This would change the chart type
    console.log('Changing chart to:', type);
}
</script>
@endpush
