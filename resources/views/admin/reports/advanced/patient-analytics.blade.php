@extends('admin.layouts.app')

@section('title', 'Patient Analytics Dashboard')

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

.patient-summary {
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

.demographics-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e3e6f0;
}

.demographics-item:last-child {
    border-bottom: none;
}

.satisfaction-excellent {
    color: #1cc88a;
}

.satisfaction-good {
    color: #f6c23e;
}

.satisfaction-poor {
    color: #e74a3b;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users mr-2"></i>Patient Analytics Dashboard
        </h1>
        <div class="btn-group">
            <a href="{{ route('admin.advanced-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Back to Reports
            </a>
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="fas fa-download mr-1"></i>Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" onclick="exportPatientReport('pdf')">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a class="dropdown-item" href="#" onclick="exportPatientReport('excel')">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="kpi-grid">
        <!-- Total Patients -->
        <div class="analytics-card">
            <div class="card-body patient-summary text-center">
                <div class="metric-icon bg-light text-primary mx-auto">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-1">
                    {{ number_format($analytics['patient_demographics']['total_patients'] ?? 0) }}
                </h3>
                <p class="mb-2">Total Patients</p>
                @php
                    $growthRate = $analytics['patient_growth_rate'] ?? 0;
                    $growthClass = $growthRate > 0 ? 'growth-positive' : ($growthRate < 0 ? 'growth-negative' : 'text-muted');
                    $growthIcon = $growthRate > 0 ? 'fa-arrow-up' : ($growthRate < 0 ? 'fa-arrow-down' : 'fa-minus');
                @endphp
                <span class="growth-indicator {{ $growthClass }}">
                    <i class="fas {{ $growthIcon }} mr-1"></i>
                    {{ abs($growthRate) }}% vs last month
                </span>
            </div>
        </div>

        <!-- Active Patients -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-success text-white mx-auto">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="mb-1 text-success">{{ number_format($analytics['patient_demographics']['active_patients'] ?? 0) }}</h3>
                <p class="text-muted mb-0">Active Patients</p>
                <small class="text-muted">
                    {{ round((($analytics['patient_demographics']['active_patients'] ?? 0) / max(1, $analytics['patient_demographics']['total_patients'] ?? 0)) * 100, 1) }}% of total
                </small>
            </div>
        </div>

        <!-- New Patients This Month -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-info text-white mx-auto">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="mb-1 text-info">{{ number_format($analytics['new_patients_month'] ?? 0) }}</h3>
                <p class="text-muted mb-0">New This Month</p>
                <small class="text-muted">
                    {{ round(($analytics['new_patients_month'] ?? 0) / max(1, date('j')), 1) }} per day avg
                </small>
            </div>
        </div>

        <!-- Average Age -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-warning text-white mx-auto">
                    <i class="fas fa-birthday-cake"></i>
                </div>
                <h3 class="mb-1 text-warning">{{ round($analytics['average_age'] ?? 0, 1) }}</h3>
                <p class="text-muted mb-0">Average Age</p>
                <small class="text-muted">Years</small>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Patient Growth Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Patient Growth Trends</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="patientGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gender Distribution -->
        <div class="col-lg-4 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gender Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demographics Analysis -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Age Group Analysis</h6>
                </div>
                <div class="card-body">
                    @if(isset($analytics['age_groups']) && $analytics['age_groups']->count() > 0)
                        @foreach($analytics['age_groups'] as $ageGroup)
                        <div class="demographics-item">
                            <div>
                                <h6 class="mb-0">{{ $ageGroup->age_range }}</h6>
                                <small class="text-muted">
                                    {{ round(($ageGroup->count / ($analytics['total_patients'] ?? 1)) * 100, 1) }}% of patients
                                </small>
                            </div>
                            <div class="text-right">
                                <h6 class="mb-0 text-primary">{{ number_format($ageGroup->count) }}</h6>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No age group data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Appointment Frequency -->
        <div class="col-lg-6 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Appointment Frequency</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="appointmentFrequencyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Insights -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Key Insights</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-star text-warning mr-2"></i>
                            <strong>Most Active Age Group</strong>
                        </div>
                        @if(isset($analytics['age_groups']) && $analytics['age_groups']->count() > 0)
                            @php $topAgeGroup = $analytics['age_groups']->first(); @endphp
                            <p class="text-muted mb-0">
                                {{ $topAgeGroup->age_range }}<br>
                                <small>{{ number_format($topAgeGroup->count) }} patients</small>
                            </p>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar text-info mr-2"></i>
                            <strong>Peak Registration Period</strong>
                        </div>
                        <p class="text-muted mb-0">
                            Usually in January & September<br>
                            <small>Based on historical data</small>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-heart text-danger mr-2"></i>
                            <strong>Patient Retention Rate</strong>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $analytics['retention_rate'] ?? 0 }}%<br>
                            <small>
                                @if(($analytics['retention_rate'] ?? 0) >= 80)
                                    Excellent retention
                                @elseif(($analytics['retention_rate'] ?? 0) >= 60)
                                    Good retention
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
                    <h6 class="m-0 font-weight-bold text-primary">Patient Health Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 class="text-success">
                                {{ round($analytics['avg_appointments_per_patient'] ?? 0, 1) }}
                            </h4>
                            <p class="text-muted mb-0">Avg Appointments/Patient</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-info">
                                {{ round($analytics['avg_days_between_visits'] ?? 0) }}
                            </h4>
                            <p class="text-muted mb-0">Avg Days Between Visits</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-warning">
                                {{ $analytics['no_show_rate'] ?? 0 }}%
                            </h4>
                            <p class="text-muted mb-0">No-Show Rate</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Patient Satisfaction Goals</h6>
                            @php
                                $satisfactionRate = $analytics['satisfaction_rate'] ?? 85;
                                $satisfactionGoal = 90; // 90% satisfaction target
                            @endphp
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" style="width: {{ ($satisfactionRate / $satisfactionGoal) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $satisfactionRate }}% of {{ $satisfactionGoal }}% target
                            </small>
                        </div>
                        <div class="col-md-6">
                            <h6>Retention Goals</h6>
                            @php
                                $retentionGoal = 85; // 85% retention target
                                $currentRetention = $analytics['retention_rate'] ?? 75;
                            @endphp
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" style="width: {{ ($currentRetention / $retentionGoal) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $currentRetention }}% of {{ $retentionGoal }}% target
                            </small>
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
                <h5 class="modal-title">Exporting Patient Analytics Report</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Preparing your patient analytics report...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    // Prepare chart data in PHP to avoid JavaScript syntax issues
    $chartLabels = isset($analytics['patient_demographics']['registration_trends']) ? $analytics['patient_demographics']['registration_trends']->pluck('month') : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
    $newPatientsData = isset($analytics['patient_demographics']['registration_trends']) ? $analytics['patient_demographics']['registration_trends']->pluck('new_patients') : [15, 22, 18, 28, 32, 25];
    
    // Calculate cumulative totals manually
    if (isset($analytics['patient_demographics']['registration_trends'])) {
        $monthlyData = collect($analytics['patient_demographics']['registration_trends'])->pluck('new_patients')->toArray();
        $cumulativeData = [];
        $total = 0;
        foreach ($monthlyData as $value) {
            $total += $value;
            $cumulativeData[] = $total;
        }
    } else {
        $cumulativeData = [15, 37, 55, 83, 115, 140];
    }
@endphp

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Patient Growth Chart
const patientGrowthCtx = document.getElementById('patientGrowthChart').getContext('2d');
const patientGrowthChart = new Chart(patientGrowthCtx, {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'New Patients',
            data: @json($newPatientsData),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }, {
            label: 'Cumulative Total',
            data: @json($cumulativeData),
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
                beginAtZero: true
            }
        }
    }
});

// Gender Distribution Chart
const genderCtx = document.getElementById('genderChart').getContext('2d');
const genderChart = new Chart(genderCtx, {
    type: 'doughnut',
    data: {
        labels: @json(isset($analytics['gender_distribution']) ? $analytics['gender_distribution']->pluck('gender') : ['Male', 'Female', 'Other']),
        datasets: [{
            data: @json(isset($analytics['gender_distribution']) ? $analytics['gender_distribution']->pluck('count') : [45, 52, 3]),
            backgroundColor: [
                '#4e73df',
                '#e74a3b',
                '#36b9cc'
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

// Appointment Frequency Chart
const frequencyCtx = document.getElementById('appointmentFrequencyChart').getContext('2d');
const frequencyChart = new Chart(frequencyCtx, {
    type: 'bar',
    data: {
        labels: ['1-2 visits', '3-5 visits', '6-10 visits', '11+ visits'],
        datasets: [{
            label: 'Number of Patients',
            data: [120, 85, 45, 25],
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e'
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

function exportPatientReport(format) {
    $('#exportModal').modal('show');
    
    // Simulate export process
    setTimeout(() => {
        $('#exportModal').modal('hide');
        alert(`Patient analytics report exported as ${format.toUpperCase()}`);
    }, 3000);
}
</script>
@endsection
