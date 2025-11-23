@extends('admin.layouts.app')

@section('title', 'Advanced Reports Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line mr-2"></i>Advanced Reports Dashboard
        </h1>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.advanced-reports.custom-reports') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>Create Custom Report
            </a>
            <a href="{{ route('admin.advanced-reports.financial-analytics') }}" class="btn btn-success">
                <i class="fas fa-dollar-sign mr-1"></i>Financial Analytics
            </a>
            <a href="{{ route('admin.advanced-reports.patient-analytics') }}" class="btn btn-info">
                <i class="fas fa-users mr-1"></i>Patient Analytics
            </a>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Monthly Revenue
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($quickStats['total_revenue_month'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Monthly Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($quickStats['total_appointments_month']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                New Patients (Month)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($quickStats['new_patients_month']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Outstanding Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ${{ number_format($quickStats['outstanding_amount'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Section -->
    <div class="row">
        <!-- Report Categories -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Report Categories</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('admin.advanced-reports.custom-reports') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-cogs mb-2 d-block"></i>
                                Custom Reports
                                <small class="d-block text-muted">Build your own reports</small>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('admin.advanced-reports.financial-analytics') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-chart-pie mb-2 d-block"></i>
                                Financial Analytics
                                <small class="d-block text-muted">Revenue & billing insights</small>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('admin.advanced-reports.patient-analytics') }}" class="btn btn-outline-info btn-block">
                                <i class="fas fa-users mb-2 d-block"></i>
                                Patient Analytics
                                <small class="d-block text-muted">Patient demographics & trends</small>
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('admin.advanced-reports.doctor-analytics') }}" class="btn btn-outline-warning btn-block">
                                <i class="fas fa-user-md mb-2 d-block"></i>
                                Doctor Analytics
                                <small class="d-block text-muted">Performance & efficiency</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Reports</h6>
                </div>
                <div class="card-body">
                    @if($recentReports->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentReports as $report)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $report->name }}</h6>
                                    <small class="text-muted">
                                        Created {{ \Carbon\Carbon::parse($report->created_at)->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-sm" onclick="runSavedReport({{ $report->id }})">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="editReport({{ $report->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.advanced-reports.custom-reports') }}" class="btn btn-sm btn-outline-primary">
                                View All Reports
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-gray-300 mb-3"></i>
                            <p class="text-muted">No saved reports yet</p>
                            <a href="{{ route('admin.advanced-reports.custom-reports') }}" class="btn btn-primary">
                                Create Your First Report
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-success btn-block" onclick="generateQuickReport('revenue_today')">
                                <i class="fas fa-dollar-sign mb-1 d-block"></i>
                                Today's Revenue Report
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-info btn-block" onclick="generateQuickReport('appointments_today')">
                                <i class="fas fa-calendar-day mb-1 d-block"></i>
                                Today's Appointments
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-warning btn-block" onclick="generateQuickReport('outstanding_payments')">
                                <i class="fas fa-exclamation-circle mb-1 d-block"></i>
                                Outstanding Payments
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-outline-primary btn-block" onclick="generateQuickReport('department_summary')">
                                <i class="fas fa-building mb-1 d-block"></i>
                                Department Summary
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Report Modal -->
<div class="modal fade" id="quickReportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Report</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="quickReportContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportQuickReport()">Export</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function generateQuickReport(type) {
    $('#quickReportModal').modal('show');
    $('#quickReportContent').html(`
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Generating report...</p>
        </div>
    `);

    // Mock data for demonstration
    setTimeout(() => {
        let content = '';
        switch(type) {
            case 'revenue_today':
                content = `
                    <h6>Today's Revenue Report</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Appointments</th>
                                    <th>Revenue</th>
                                    <th>Collection Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Cardiology</td><td>15</td><td>$2,500</td><td>95%</td></tr>
                                <tr><td>Orthopedics</td><td>12</td><td>$1,800</td><td>90%</td></tr>
                                <tr><td>General Medicine</td><td>25</td><td>$1,250</td><td>100%</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
                break;
            case 'appointments_today':
                content = `
                    <h6>Today's Appointments Summary</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>52</h3>
                                    <p>Total Appointments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>45</h3>
                                    <p>Completed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
            default:
                content = '<p>Report data would be displayed here.</p>';
        }
        $('#quickReportContent').html(content);
    }, 1500);
}

function runSavedReport(reportId) {
    // Implementation for running saved reports
    alert('Running saved report ID: ' + reportId);
}

function editReport(reportId) {
    // Implementation for editing reports
    window.location.href = `/admin/reports/custom-builder?edit=${reportId}`;
}

function exportQuickReport() {
    // Implementation for exporting quick reports
    alert('Export functionality would be implemented here');
}
</script>
@endsection
