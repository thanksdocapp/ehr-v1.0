@extends('admin.layouts.app')

@section('title', 'Financial Analytics Dashboard')

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

.trend-up {
    color: #1cc88a;
}

.trend-down {
    color: #e74a3b;
}

.trend-neutral {
    color: #858796;
}

.chart-container {
    position: relative;
    height: 300px;
}

.revenue-summary {
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

.period-selector {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.department-revenue-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #e3e6f0;
}

.department-revenue-item:last-child {
    border-bottom: none;
}

.outstanding-payment {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.collection-rate-excellent {
    color: #1cc88a;
}

.collection-rate-good {
    color: #f6c23e;
}

.collection-rate-poor {
    color: #e74a3b;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-pie mr-2"></i>Financial Analytics Dashboard
        </h1>
        <div class="btn-group">
            <a href="{{ route('admin.advanced-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Back to Reports
            </a>
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="fas fa-download mr-1"></i>Export
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" onclick="exportFinancialReport('pdf')">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </a>
                <a class="dropdown-item" href="#" onclick="exportFinancialReport('excel')">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="period-selector">
        <form method="GET" action="{{ route('admin.advanced-reports.financial-analytics') }}" class="form-inline">
            <label class="mr-3">
                <strong>Analysis Period:</strong>
            </label>
            <select name="period" class="form-control mr-3" onchange="this.form.submit()">
                <option value="day" {{ $period === 'day' ? 'selected' : '' }}>Daily</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Monthly</option>
                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Quarterly</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Yearly</option>
            </select>
            <select name="year" class="form-control mr-3" onchange="this.form.submit()">
                @for($i = now()->year; $i >= now()->year - 5; $i--)
                    <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
            <button type="button" class="btn btn-outline-primary" onclick="refreshAnalytics()">
                <i class="fas fa-sync-alt mr-1"></i>Refresh
            </button>
        </form>
    </div>

    <!-- Key Performance Indicators -->
    <div class="kpi-grid">
        <!-- Total Revenue -->
        <div class="analytics-card">
            <div class="card-body revenue-summary text-center">
                <div class="metric-icon bg-light text-primary mx-auto">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3 class="mb-1">
                    ${{ number_format($analytics['revenue_overview']['summary']->total_revenue ?? 0, 2) }}
                </h3>
                <p class="mb-2">Total Revenue</p>
                @php
                    $growthRate = $analytics['revenue_overview']['growth_rate'] ?? 0;
                    $growthClass = $growthRate > 0 ? 'growth-positive' : ($growthRate < 0 ? 'growth-negative' : 'text-muted');
                    $growthIcon = $growthRate > 0 ? 'fa-arrow-up' : ($growthRate < 0 ? 'fa-arrow-down' : 'fa-minus');
                @endphp
                <span class="growth-indicator {{ $growthClass }}">
                    <i class="fas {{ $growthIcon }} mr-1"></i>
                    {{ abs($growthRate) }}% vs last year
                </span>
            </div>
        </div>

        <!-- Collection Rate -->
        <div class="analytics-card">
            <div class="card-body text-center">
                @php
                    $collectionRate = $analytics['revenue_overview']['collection_rate'] ?? 0;
                    $rateClass = $collectionRate >= 95 ? 'collection-rate-excellent' : 
                                ($collectionRate >= 80 ? 'collection-rate-good' : 'collection-rate-poor');
                @endphp
                <div class="metric-icon bg-success text-white mx-auto">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 class="mb-1 {{ $rateClass }}">{{ $collectionRate }}%</h3>
                <p class="text-muted mb-0">Collection Rate</p>
                <small class="text-muted">
                    ${{ number_format($analytics['revenue_overview']['summary']->total_paid ?? 0, 2) }} collected
                </small>
            </div>
        </div>

        <!-- Outstanding Amount -->
        <div class="analytics-card outstanding-payment">
            <div class="card-body text-center">
                <div class="metric-icon bg-warning text-white mx-auto">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="mb-1 text-warning">
                    ${{ number_format($analytics['revenue_overview']['summary']->outstanding ?? 0, 2) }}
                </h3>
                <p class="text-muted mb-0">Outstanding Payments</p>
                <small class="text-muted">
                    {{ number_format($analytics['revenue_overview']['summary']->total_bills ?? 0) }} bills
                </small>
            </div>
        </div>

        <!-- Average Bill Amount -->
        <div class="analytics-card">
            <div class="card-body text-center">
                <div class="metric-icon bg-info text-white mx-auto">
                    <i class="fas fa-receipt"></i>
                </div>
                <h3 class="mb-1 text-info">
                    ${{ number_format($analytics['revenue_overview']['summary']->avg_bill ?? 0, 2) }}
                </h3>
                <p class="text-muted mb-0">Average Bill Amount</p>
                <small class="text-muted">Per transaction</small>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Trends</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow">
                            <a class="dropdown-item" href="#" onclick="changeChartType('revenue', 'line')">Line Chart</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('revenue', 'bar')">Bar Chart</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Breakdown -->
        <div class="col-lg-4 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Methods</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Revenue Analysis -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Department Revenue Analysis</h6>
                </div>
                <div class="card-body">
                    @if(isset($analytics['department_revenue']) && $analytics['department_revenue']->count() > 0)
                        @foreach($analytics['department_revenue']->take(8) as $dept)
                        <div class="department-revenue-item">
                            <div>
                                <h6 class="mb-0">{{ $dept->department_name }}</h6>
                                <small class="text-muted">
                                    {{ $dept->total_bills }} bills • {{ $dept->unique_patients }} patients
                                </small>
                            </div>
                            <div class="text-right">
                                <h6 class="mb-0 text-success">
                                    ${{ number_format($dept->total_revenue, 0) }}
                                </h6>
                                <small class="text-muted">
                                    ${{ number_format($dept->avg_bill_amount, 0) }} avg
                                </small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No department revenue data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Revenue Forecast -->
        <div class="col-lg-6 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Revenue Forecast</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="forecastChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Insights -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card analytics-card">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Insights</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-trophy text-warning mr-2"></i>
                            <strong>Best Performing Department</strong>
                        </div>
                        @if(isset($analytics['department_revenue']) && $analytics['department_revenue']->count() > 0)
                            @php $topDept = $analytics['department_revenue']->first(); @endphp
                            <p class="text-muted mb-0">
                                {{ $topDept->department_name }}<br>
                                <small>${{ number_format($topDept->total_revenue, 0) }} revenue</small>
                            </p>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-clock text-info mr-2"></i>
                            <strong>Peak Revenue Period</strong>
                        </div>
                        <p class="text-muted mb-0">
                            Usually between 10 AM - 2 PM<br>
                            <small>Based on appointment patterns</small>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-chart-line text-success mr-2"></i>
                            <strong>Collection Efficiency</strong>
                        </div>
                        <p class="text-muted mb-0">
                            {{ $analytics['revenue_overview']['collection_rate'] ?? 0 }}% collection rate<br>
                            <small>
                                @if(($analytics['revenue_overview']['collection_rate'] ?? 0) >= 95)
                                    Excellent performance
                                @elseif(($analytics['revenue_overview']['collection_rate'] ?? 0) >= 80)
                                    Good performance
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
                    <h6 class="m-0 font-weight-bold text-primary">Financial Health Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <h4 class="text-success">
                                ${{ number_format(($analytics['revenue_overview']['summary']->total_paid ?? 0) / max(1, now()->day), 0) }}
                            </h4>
                            <p class="text-muted mb-0">Daily Average</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-info">
                                {{ number_format(($analytics['revenue_overview']['summary']->total_bills ?? 0) / max(1, now()->day), 0) }}
                            </h4>
                            <p class="text-muted mb-0">Bills/Day</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h4 class="text-warning">
                                @php
                                    $totalRevenue = $analytics['revenue_overview']['summary']->total_revenue ?? 0;
                                    $totalPaid = $analytics['revenue_overview']['summary']->total_paid ?? 0;
                                    $outstandingDays = $totalRevenue > 0 ? round(((($totalRevenue - $totalPaid) / $totalRevenue) * 30), 1) : 0;
                                @endphp
                                {{ $outstandingDays }}
                            </h4>
                            <p class="text-muted mb-0">Days Outstanding</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Revenue Goals</h6>
                            @php
                                $currentRevenue = $analytics['revenue_overview']['summary']->total_paid ?? 0;
                                $monthlyGoal = 100000; // This could be configurable
                                $goalProgress = ($currentRevenue / $monthlyGoal) * 100;
                            @endphp
                            <div class="progress mb-2">
                                <div class="progress-bar bg-success" style="width: {{ min(100, $goalProgress) }}%"></div>
                            </div>
                            <small class="text-muted">
                                ${{ number_format($currentRevenue, 0) }} of ${{ number_format($monthlyGoal, 0) }} goal
                                ({{ round($goalProgress, 1) }}%)
                            </small>
                        </div>
                        <div class="col-md-6">
                            <h6>Collection Goals</h6>
                            @php
                                $collectionGoal = 95; // 95% collection target
                                $currentCollection = $analytics['revenue_overview']['collection_rate'] ?? 0;
                            @endphp
                            <div class="progress mb-2">
                                <div class="progress-bar bg-info" style="width: {{ ($currentCollection / $collectionGoal) * 100 }}%"></div>
                            </div>
                            <small class="text-muted">
                                {{ $currentCollection }}% of {{ $collectionGoal }}% target
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
                <h5 class="modal-title">Exporting Financial Report</h5>
            </div>
            <div class="modal-body text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Preparing your financial analytics report...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Revenue Trend Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($analytics['revenue_overview']['trends']->pluck('period')),
        datasets: [{
            label: 'Total Revenue',
            data: @json($analytics['revenue_overview']['trends']->pluck('total_revenue')),
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.3
        }, {
            label: 'Paid Revenue',
            data: @json($analytics['revenue_overview']['trends']->pluck('paid_revenue')),
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
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Payment Methods Chart
const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
const paymentChart = new Chart(paymentCtx, {
    type: 'doughnut',
    data: {
        labels: @json($analytics['payment_methods']->pluck('payment_method')),
        datasets: [{
            data: @json($analytics['payment_methods']->pluck('total_amount')),
            backgroundColor: [
                '#4e73df',
                '#1cc88a', 
                '#36b9cc',
                '#f6c23e',
                '#e74a3b',
                '#858796'
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

// Revenue Forecast Chart (Mock data for demonstration)
const forecastCtx = document.getElementById('forecastChart').getContext('2d');
const forecastChart = new Chart(forecastCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Actual Revenue',
            data: [85000, 92000, 88000, 95000, 87000, 91000],
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 2,
            fill: false
        }, {
            label: 'Forecasted Revenue',
            data: [null, null, null, null, 91000, 94000],
            borderColor: '#e74a3b',
            backgroundColor: 'rgba(231, 74, 59, 0.1)',
            borderWidth: 2,
            borderDash: [5, 5],
            fill: false
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
                ticks: {
                    callback: function(value) {
                        return '$' + (value / 1000) + 'K';
                    }
                }
            }
        }
    }
});

function changeChartType(chartId, type) {
    // Implementation for changing chart types
    alert(`Changing ${chartId} chart to ${type} type`);
}

function exportFinancialReport(format) {
    $('#exportModal').modal('show');
    
    // Simulate export process
    setTimeout(() => {
        $('#exportModal').modal('hide');
        alert(`Financial report exported as ${format.toUpperCase()}`);
    }, 3000);
}

function refreshAnalytics() {
    // Add loading state and refresh data
    location.reload();
}

// Auto-refresh data every 5 minutes
setInterval(() => {
    if (document.hidden) return; // Don't refresh if page is not visible
    
    // You could implement AJAX refresh here
    console.log('Auto-refreshing financial data...');
}, 300000); // 5 minutes
</script>
@endsection
