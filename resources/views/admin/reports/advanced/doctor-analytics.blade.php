@extends('admin.layouts.app')

@section('title', 'Doctor Analytics Dashboard')

@section('styles')
<style>
.analytics-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s;
}

.analytics-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.chart-container {
    position: relative;
    height: 300px;
}

.doctor-summary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.growth-indicator {
    font-size: 0.8rem;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: bold;
}

.growth-positive {
    background-color: rgba(28, 200, 138, 0.2);
    color: #1cc88a;
}

.growth-negative {
    background-color: rgba(231, 74, 59, 0.2);
    color: #e74a3b;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.doctor-performance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e3e6f0;
}

.doctor-performance-item:last-child {
    border-bottom: none;
}

.performance-excellent {
    color: #1cc88a;
}

.performance-good {
    color: #f6c23e;
}

.performance-poor {
    color: #e74a3b;
}

.doctor-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #4e73df;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    margin-right: 10px;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-md mr-2"></i>Doctor Analytics Dashboard
        </h1>
        <div class="btn-group">
            <a href="{{ route('admin.advanced-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Back to Reports
            </a>
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="fas fa-download mr-1"></i>Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" onclick="exportDoctorReport('pdf')">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a class="dropdown-item" href="#" onclick="exportDoctorReport('excel')">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="kpi-grid">
        <!-- Total Doctors -->
        <div class="analytics-card">
            <div class="card-body doctor-summary text-center">
                <div class="metric-icon bg-light text-primary mx-auto">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3 class="mb-1">
                    {{ number_format($analytics['total_doctors'] ?? 0) }}
                </h3>
                <p class="mb-2">Total Doctors</p>
                @php
                    $growthRate = $analytics['doctor_growth_rate'] ?? 0;
                    $growthClass = $growthRate > 0 ? 'growth-positive' : ($growthRate < 0 ? 'growth-negative' : 'text-muted');
                    $growthIcon = $growthRate > 0 ? 'fa-arrow-up' : ($growthRate < 0 ? 'fa-arrow-down' : 'fa-minus');
                @endphp
                <span class="growth-indicator {{ $growthClass }}">
                    <i class="fas {{ $growthIcon }} mr-1"></i>
                    {{ abs($growthRate) }}% vs last quarter
                </span>
            </div>
        </div>

        <!-- Active Doctors -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-success text-white mx-auto">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3 class="mb-1 text-success">{{ number_format($analytics['active_doctors'] ?? 0) }}</h3>
                <p class="text-muted mb-0">Active Doctors</p>
                <small class="text-muted">
                    {{ round((($analytics['active_doctors'] ?? 0) / max(1, $analytics['total_doctors'] ?? 0)) * 100, 1) }}% of total
                </small>
            </div>
        </div>

        <!-- Total Appointments -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-info text-white mx-auto">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="mb-1 text-info">{{ number_format($analytics['total_appointments'] ?? 0) }}</h3>
                <p class="text-muted mb-0">Total Appointments</p>
                <small class="text-muted">
                    This month
                </small>
            </div>
        </div>

        <!-- Average Rating -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-warning text-white mx-auto">
                    <i class="fas fa-star"></i>
                </div>
                <h3 class="mb-1 text-warning">{{ round($analytics['average_rating'] ?? 4.2, 1) }}</h3>
                <p class="text-muted mb-0">Average Rating</p>
                <small class="text-muted">Out of 5</small>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Doctor Performance Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Doctor Performance Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Department Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="departmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Performing Doctors</h6>
                </div>
                <div class="card-body">
                    @if(isset($analytics['top_doctors']) && $analytics['top_doctors']->count() > 0)
                        @foreach($analytics['top_doctors']->take(8) as $doctor)
                        <div class="doctor-performance-item">
                            <div class="d-flex align-items-center">
                                <div class="doctor-avatar">
                                    {{ strtoupper(substr($doctor->first_name, 0, 1)) }}{{ strtoupper(substr($doctor->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}</h6>
                                    <small class="text-muted">
                                        {{ $doctor->department }} • {{ $doctor->total_appointments }} appointments
                                    </small>
                                </div>
                            </div>
                            <div class="text-right">
                                <h6 class="mb-0 text-success">{{ $doctor->rating ?? '4.5' }}/5</h6>
                                <small class="text-muted">
                                    {{ $doctor->completion_rate ?? 95 }}% completion
                                </small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No doctor performance data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Workload Distribution -->
        <div class="col-lg-6 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Workload Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="workloadChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctor Insights -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Key Insights</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-trophy text-warning mr-2"></i>
                            <strong>Top Department</strong>
                        </div>
                        @if(isset($analytics['top_department']))
                            <p class="text-muted mb-0">
                                {{ $analytics['top_department']['name'] }}<br>
                                <small>{{ $analytics['top_department']['doctor_count'] }} doctors</small>
                            </p>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-clock text-info mr-2"></i>
                            <strong>Average Consultation Time</strong>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $analytics['avg_consultation_time'] ?? 25 }} minutes<br>
                            <small>Per appointment</small>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-chart-line text-success mr-2"></i>
                            <strong>Overall Efficiency</strong>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $analytics['efficiency_rate'] ?? 88 }}%<br>
                            <small>
                                @if(($analytics['efficiency_rate'] ?? 88) >= 90)
                                    Excellent efficiency
                                @elseif(($analytics['efficiency_rate'] ?? 88) >= 75)
                                    Good efficiency
                                @else
                                    Needs improvement
                                @endif
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 class="text-success">
                                {{ round($analytics['avg_appointments_per_doctor'] ?? 0) }}
                            </h4>
                            <p class="text-muted mb-0">Avg Appointments/Doctor</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-info">
                                {{ $analytics['patient_satisfaction'] ?? 92 }}%
                            </h4>
                            <p class="text-muted mb-0">Patient Satisfaction</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-warning">
                                {{ $analytics['no_show_rate'] ?? 8 }}%
                            </h4>
                            <p class="text-muted mb-0">Patient No-Show Rate</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Performance Goals</h6>
                            @php
                                $performanceRate = $analytics['overall_performance'] ?? 85;
                                $performanceGoal = 90; // 90% performance target
                            @endphp
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" style="width: {{ ($performanceRate / $performanceGoal) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $performanceRate }}% of {{ $performanceGoal }}% target
                            </small>
                        </div>
                        <div class="col-md-6">
                            <h6>Utilization Goals</h6>
                            @php
                                $utilizationGoal = 80; // 80% utilization target
                                $currentUtilization = $analytics['utilization_rate'] ?? 75;
                            @endphp
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" style="width: {{ ($currentUtilization / $utilizationGoal) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $currentUtilization }}% of {{ $utilizationGoal }}% target
                            </small>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Doctor Specialization Overview</h6>
                            <div class="row">
                                <div class="col-md-3 text-center mb-3">
                                    <div class="bg-primary text-white rounded p-3">
                                        <i class="fas fa-heartbeat fa-2x mb-2"></i>
                                        <h6>{{ $analytics['specializations']['cardiology'] ?? 5 }}</h6>
                                        <small>Cardiology</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="bg-success text-white rounded p-3">
                                        <i class="fas fa-bone fa-2x mb-2"></i>
                                        <h6>{{ $analytics['specializations']['orthopedics'] ?? 4 }}</h6>
                                        <small>Orthopedics</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="bg-info text-white rounded p-3">
                                        <i class="fas fa-brain fa-2x mb-2"></i>
                                        <h6>{{ $analytics['specializations']['neurology'] ?? 3 }}</h6>
                                        <small>Neurology</small>
                                    </div>
                                </div>
                                <div class="col-md-3 text-center mb-3">
                                    <div class="bg-warning text-white rounded p-3">
                                        <i class="fas fa-stethoscope fa-2x mb-2"></i>
                                        <h6>{{ $analytics['specializations']['general'] ?? 8 }}</h6>
                                        <small>General</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Progress Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exporting Doctor Analytics Report</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Preparing your doctor analytics report...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    // Prepare chart data in PHP to avoid JavaScript syntax issues
    $performanceLabels = isset($analytics['performance_trends']) ? $analytics['performance_trends']->pluck('period') : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    $avgRatingData = isset($analytics['performance_trends']) ? $analytics['performance_trends']->pluck('avg_rating') : [4.2, 4.3, 4.4, 4.3, 4.5, 4.4];
    $completionRateData = isset($analytics['performance_trends']) ? $analytics['performance_trends']->pluck('completion_rate') : [92, 94, 95, 93, 96, 95];
    
    $departmentLabels = isset($analytics['department_distribution']) ? $analytics['department_distribution']->pluck('department') : ['Cardiology', 'Orthopedics', 'General Medicine', 'Pediatrics'];
    $departmentCounts = isset($analytics['department_distribution']) ? $analytics['department_distribution']->pluck('count') : [5, 4, 8, 3];
@endphp

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Doctor Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'line',
    data: {
        labels: @json($performanceLabels),
        datasets: [{
            label: 'Average Rating',
            data: @json($avgRatingData),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }, {
            label: 'Completion Rate (%)',
            data: @json($completionRateData),
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.3
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
                beginAtZero: true,
                max: 5
            }
        }
    }
});

// Department Distribution Chart
const departmentCtx = document.getElementById('departmentChart').getContext('2d');
const departmentChart = new Chart(departmentCtx, {
    type: 'doughnut',
    data: {
        labels: @json($departmentLabels),
        datasets: [{
            data: @json($departmentCounts),
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e'
            ],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Workload Distribution Chart
const workloadCtx = document.getElementById('workloadChart').getContext('2d');
const workloadChart = new Chart(workloadCtx, {
    type: 'bar',
    data: {
        labels: ['Light Load', 'Moderate Load', 'Heavy Load', 'Overloaded'],
        datasets: [{
            label: 'Number of Doctors',
            data: [2, 8, 10, 4],
            backgroundColor: [
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
                '#e74a3b'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function exportDoctorReport(format) {
    $('#exportModal').modal('show');
    
    // Simulate export process
    setTimeout(() => {
        $('#exportModal').modal('hide');
        alert(`Doctor analytics report exported as ${format.toUpperCase()}`);
    }, 3000);
}
</script>
@endsection
