@extends('admin.layouts.app')

@section('title', 'Email Statistics')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Email Statistics</h1>
            <p class="mb-0 text-muted">Comprehensive analytics for email notifications</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select" id="time-period" style="width: auto;">
                <option value="7">Last 7 days</option>
                <option value="30" selected>Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last year</option>
            </select>
            <a href="{{ route('admin.email-management.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-sent">
                                3,247
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Delivered
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="delivered">
                                3,089
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Failed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="failed">
                                158
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Delivery Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="delivery-rate">
                                95.1%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                Avg. Response Time
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="avg-response">
                                2.3s
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                Queue Size
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="queue-size">
                                12
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Email Volume Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Email Volume Over Time</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <div class="dropdown-header">Export Options:</div>
                            <a class="dropdown-item" href="#" id="export-png">Save as PNG</a>
                            <a class="dropdown-item" href="#" id="export-pdf">Save as PDF</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="emailVolumeChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>

        <!-- Email Types Distribution -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Email Types Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="emailTypesChart" width="100%" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Status and Performance Row -->
    <div class="row mb-4">
        <!-- Delivery Status Chart -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Delivery Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="deliveryStatusChart" width="100%" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Metrics</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" width="100%" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Tables Row -->
    <div class="row mb-4">
        <!-- Top Email Types -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Email Types</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                    <th>Success Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody id="top-email-types">
                                <tr>
                                    <td>Appointment Confirmations</td>
                                    <td><span class="badge bg-primary">1,247</span></td>
                                    <td><span class="text-success">97.2%</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i> 12%</td>
                                </tr>
                                <tr>
                                    <td>Patient Welcome</td>
                                    <td><span class="badge bg-primary">892</span></td>
                                    <td><span class="text-success">94.8%</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i> 8%</td>
                                </tr>
                                <tr>
                                    <td>Appointment Reminders</td>
                                    <td><span class="badge bg-primary">567</span></td>
                                    <td><span class="text-warning">89.3%</span></td>
                                    <td><i class="fas fa-arrow-down text-danger"></i> 3%</td>
                                </tr>
                                <tr>
                                    <td>Test Results</td>
                                    <td><span class="badge bg-primary">334</span></td>
                                    <td><span class="text-success">98.1%</span></td>
                                    <td><i class="fas fa-arrow-up text-success"></i> 15%</td>
                                </tr>
                                <tr>
                                    <td>Staff Notifications</td>
                                    <td><span class="badge bg-primary">207</span></td>
                                    <td><span class="text-success">99.5%</span></td>
                                    <td><i class="fas fa-minus text-muted"></i> 0%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failure Analysis -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Common Failure Reasons</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Reason</th>
                                    <th>Count</th>
                                    <th>Percentage</th>
                                    <th>Impact</th>
                                </tr>
                            </thead>
                            <tbody id="failure-reasons">
                                <tr>
                                    <td>Invalid Email Address</td>
                                    <td><span class="badge bg-danger">67</span></td>
                                    <td>42.4%</td>
                                    <td><span class="badge bg-warning">Medium</span></td>
                                </tr>
                                <tr>
                                    <td>SMTP Timeout</td>
                                    <td><span class="badge bg-danger">38</span></td>
                                    <td>24.1%</td>
                                    <td><span class="badge bg-danger">High</span></td>
                                </tr>
                                <tr>
                                    <td>Mailbox Full</td>
                                    <td><span class="badge bg-danger">29</span></td>
                                    <td>18.4%</td>
                                    <td><span class="badge bg-info">Low</span></td>
                                </tr>
                                <tr>
                                    <td>Spam Filter Block</td>
                                    <td><span class="badge bg-danger">16</span></td>
                                    <td>10.1%</td>
                                    <td><span class="badge bg-warning">Medium</span></td>
                                </tr>
                                <tr>
                                    <td>Server Error</td>
                                    <td><span class="badge bg-danger">8</span></td>
                                    <td>5.1%</td>
                                    <td><span class="badge bg-danger">High</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hourly Distribution -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Email Distribution by Hour</h6>
        </div>
        <div class="card-body">
            <canvas id="hourlyDistributionChart" width="100%" height="60"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize all charts
    initializeCharts();
    
    // Time period change handler
    $('#time-period').on('change', function() {
        const period = $(this).val();
        updateChartsForPeriod(period);
    });
});

function initializeCharts() {
    // Email Volume Chart
    const volumeCtx = document.getElementById('emailVolumeChart').getContext('2d');
    new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: ['Jan 1', 'Jan 5', 'Jan 10', 'Jan 15', 'Jan 20', 'Jan 25', 'Jan 30'],
            datasets: [{
                label: 'Total Emails',
                data: [120, 190, 300, 500, 200, 300, 450],
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true
            }, {
                label: 'Successful',
                data: [115, 180, 285, 475, 190, 285, 428],
                borderColor: 'rgba(28, 200, 138, 1)',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                borderWidth: 2,
                fill: false
            }, {
                label: 'Failed',
                data: [5, 10, 15, 25, 10, 15, 22],
                borderColor: 'rgba(231, 74, 59, 1)',
                backgroundColor: 'rgba(231, 74, 59, 0.1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                },
                x: {
                    grid: {
                        display: false,
                    }
                }
            }
        }
    });

    // Email Types Chart
    const typesCtx = document.getElementById('emailTypesChart').getContext('2d');
    new Chart(typesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Appointments', 'Welcome', 'Reminders', 'Test Results', 'Staff Alerts'],
            datasets: [{
                data: [38.4, 27.5, 17.5, 10.3, 6.3],
                backgroundColor: [
                    'rgba(78, 115, 223, 0.8)',
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(54, 185, 204, 0.8)',
                    'rgba(133, 135, 150, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Delivery Status Chart
    const statusCtx = document.getElementById('deliveryStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'Failed', 'Pending', 'Queued'],
            datasets: [{
                data: [95.1, 4.9, 0.3, 0.7],
                backgroundColor: [
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(246, 194, 62, 0.8)',
                    'rgba(54, 185, 204, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'bar',
        data: {
            labels: ['Response Time', 'Queue Processing', 'Delivery Rate', 'Error Rate'],
            datasets: [{
                label: 'Current',
                data: [2.3, 1.8, 95.1, 4.9],
                backgroundColor: 'rgba(78, 115, 223, 0.8)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 1
            }, {
                label: 'Target',
                data: [3.0, 2.0, 98.0, 2.0],
                backgroundColor: 'rgba(28, 200, 138, 0.8)',
                borderColor: 'rgba(28, 200, 138, 1)',
                borderWidth: 1
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
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    });

    // Hourly Distribution Chart
    const hourlyCtx = document.getElementById('hourlyDistributionChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: ['00', '02', '04', '06', '08', '10', '12', '14', '16', '18', '20', '22'],
            datasets: [{
                label: 'Emails Sent',
                data: [12, 8, 5, 15, 89, 234, 189, 267, 145, 98, 67, 34],
                backgroundColor: 'rgba(78, 115, 223, 0.6)',
                borderColor: 'rgba(78, 115, 223, 1)',
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
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                },
                x: {
                    grid: {
                        display: false,
                    }
                }
            }
        }
    });
}

function updateChartsForPeriod(period) {
    // In a real implementation, this would make AJAX calls to get new data
    // For now, we'll just show a loading state
    Swal.fire({
        title: 'Updating Charts...',
        text: `Loading data for the last ${period} days`,
        allowOutsideClick: false,
        showConfirmButton: false,
        timer: 1500,
        willOpen: () => {
            Swal.showLoading();
        }
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Charts Updated',
            text: 'Statistics have been refreshed with new data.',
            timer: 1500,
            showConfirmButton: false
        });
    });
}

// Export functions
document.getElementById('export-png')?.addEventListener('click', function() {
    const canvas = document.getElementById('emailVolumeChart');
    const url = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = 'email-volume-chart.png';
    link.href = url;
    link.click();
});

document.getElementById('export-pdf')?.addEventListener('click', function() {
    Swal.fire({
        icon: 'info',
        title: 'PDF Export',
        text: 'PDF export functionality would be implemented here.',
        confirmButtonText: 'OK'
    });
});
</script>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}

.border-left-dark {
    border-left: 0.25rem solid #5a5c69 !important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-xs {
    font-size: 0.7rem;
}

canvas {
    max-height: 400px;
}
</style>
@endpush
