@extends('patient.layouts.app')

@section('title', 'Medical Records')
@section('page-title', 'Medical Records')

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-info-gradient">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="stat-number text-info">{{ $stats['total_records'] }}</div>
                <div class="stat-label">Total Records</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-success-gradient">
                    <i class="fas fa-prescription-bottle"></i>
                </div>
                <div class="stat-number text-success">{{ $stats['prescriptions'] }}</div>
                <div class="stat-label">Prescriptions</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning-gradient">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="stat-number text-warning">{{ $stats['lab_reports'] }}</div>
                <div class="stat-label">Lab Reports</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary-gradient">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="stat-number text-primary">{{ $stats['consultations'] }}</div>
                <div class="stat-label">Consultations</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>
                Filter Records
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('patient.medical-records.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type">Record Type</label>
                            <select name="type" id="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="consultation" {{ request('type') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                <option value="follow_up" {{ request('type') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                                <option value="emergency" {{ request('type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="surgery" {{ request('type') === 'surgery' ? 'selected' : '' }}>Surgery</option>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search diagnosis, symptoms..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i>
                            Filter
                        </button>
                        <a href="{{ route('patient.medical-records.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Medical Records List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-medical me-2"></i>
                    Medical Records
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
            @if($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Doctor</th>
                                <th>Type</th>
                                <th>Diagnosis</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $record)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $record->created_at->format('M d, Y') }}</strong>
                                            <small class="text-muted">{{ $record->created_at->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $record->doctor->full_name }}</strong>
                                            <small class="text-muted">{{ $record->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $record->record_type)) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $record->diagnosis ?? 'General Consultation' }}</strong>
                                            @if($record->symptoms)
                                                <small class="text-muted">{{ Str::limit($record->symptoms, 50) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Complete</span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('patient.medical-records.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="downloadRecord({{ $record->id }})">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $records->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Medical Records Found</h5>
                    <p class="text-muted">You don't have any medical records yet.</p>
                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Book Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Access Sections -->
    <div class="row mt-4">
        <!-- Recent Prescriptions -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-prescription-bottle me-2"></i>
                            Recent Prescriptions
                        </h5>
                        <a href="{{ route('patient.prescriptions.index') }}" class="btn btn-sm btn-outline-light">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentPrescriptions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentPrescriptions as $prescription)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $prescription->medication_name }}</h6>
                                            <p class="mb-1 text-muted">Dr. {{ $prescription->doctor->full_name }}</p>
                                            <small class="text-muted">{{ $prescription->created_at->format('M d, Y') }}</small>
                                        </div>
                                        <span class="badge bg-{{ $prescription->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($prescription->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-prescription-bottle fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No recent prescriptions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Lab Reports -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-flask me-2"></i>
                            Recent Lab Reports
                        </h5>
                        <a href="{{ route('patient.lab-reports.index') }}" class="btn btn-sm btn-outline-light">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($recentLabReports->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentLabReports as $report)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $report->test_name }}</h6>
                                            <p class="mb-1 text-muted">{{ $report->test_type }}</p>
                                            <small class="text-muted">{{ $report->test_date->format('M d, Y') }}</small>
                                        </div>
                                        <span class="badge bg-{{ $report->status === 'completed' ? 'success' : ($report->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-flask fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No recent lab reports</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function downloadRecord(recordId) {
        // Implement download functionality
        window.location.href = '/patient/medical-records/' + recordId + '/download';
    }
</script>
@endsection
