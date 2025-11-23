@extends('patient.layouts.app')

@section('title', 'Lab Reports')
@section('page-title', 'Lab Reports')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning-gradient">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="stat-number text-warning">{{ $stats['total'] ?? 0 }}</div>
                <div class="stat-label">Total Reports</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-success-gradient">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number text-success">{{ $stats['completed'] ?? 0 }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-info-gradient">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-number text-info">{{ $stats['pending'] ?? 0 }}</div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary-gradient">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number text-primary">{{ $stats['this_month'] ?? 0 }}</div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filter Lab Reports
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('patient.lab-reports.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="test_type">Test Type</label>
                            <select name="test_type" id="test_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="blood" {{ request('test_type') === 'blood' ? 'selected' : '' }}>Blood Test</option>
                                <option value="urine" {{ request('test_type') === 'urine' ? 'selected' : '' }}>Urine Test</option>
                                <option value="imaging" {{ request('test_type') === 'imaging' ? 'selected' : '' }}>Imaging</option>
                                <option value="microbiology" {{ request('test_type') === 'microbiology' ? 'selected' : '' }}>Microbiology</option>
                                <option value="biochemistry" {{ request('test_type') === 'biochemistry' ? 'selected' : '' }}>Biochemistry</option>
                                <option value="hematology" {{ request('test_type') === 'hematology' ? 'selected' : '' }}>Hematology</option>
                                <option value="immunology" {{ request('test_type') === 'immunology' ? 'selected' : '' }}>Immunology</option>
                                <option value="other" {{ request('test_type') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_from">From Date</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_to">To Date</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('patient.lab-reports.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lab Reports List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-flask me-2"></i>
                    Lab Reports
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>
                        Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>Export PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Export Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($labReports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Test Date</th>
                                <th>Test Name</th>
                                <th>Doctor</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labReports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $report->test_date->format('M d, Y') }}</strong>
                                            <small class="text-muted">{{ $report->test_date->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $report->test_name }}</strong>
                                            <small class="text-muted">#{{ $report->report_number }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $report->doctor->full_name }}</strong>
                                            <small class="text-muted">{{ $report->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $report->test_type)) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($report->status) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'in_progress' => 'info',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                                    </td>
                                    <td>
                                        @if($report->priority !== 'normal')
                                            @php
                                                $priorityClass = match($report->priority) {
                                                    'urgent' => 'warning',
                                                    'stat' => 'danger',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $priorityClass }}">{{ ucfirst($report->priority) }}</span>
                                        @else
                                            <span class="badge bg-secondary">Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('patient.lab-reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($report->file_path && $report->status === 'completed')
                                                <a href="{{ route('patient.lab-reports.download', $report) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $labReports->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Lab Reports Found</h5>
                    <p class="text-muted">You don't have any lab reports yet. Reports will appear here after your doctor orders laboratory tests.</p>
                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Book Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
