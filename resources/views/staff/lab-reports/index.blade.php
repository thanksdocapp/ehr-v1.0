@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Lab Reports')
@section('page-title', 'Lab Reports')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Order lab reports and view results' : (auth()->user()->role === 'technician' ? 'Create and manage lab reports - Full access' : 'Create and view lab reports'))

@section('content')
<div class="fade-in-up">
                
                <div class="btn-group">
                    <a href="{{ route('staff.lab-reports.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>New Lab Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-vial"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $labReports->total() }}</div>
                        <div class="stat-label">Total Reports</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $labReports->filter(function($report) { return $report->status === 'pending'; })->count() }}
                        </div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $labReports->filter(function($report) { return $report->status === 'completed'; })->count() }}
                        </div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'doctor')
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            @php
                                $userDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
                                $doctorId = $userDoctor ? $userDoctor->id : null;
                            @endphp
                            {{ $labReports->filter(function($report) use ($doctorId) { return $report->doctor_id == $doctorId; })->count() }}
                        </div>
                        <div class="stat-label">My Orders</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="doctor-card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filter Lab Reports
            </h6>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.lab-reports.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="patient_search" class="form-label">Patient Name</label>
                    <input type="text" name="patient_search" id="patient_search" class="form-control" 
                           placeholder="Search by name..." value="{{ request('patient_search') }}">
                </div>
                <div class="col-md-2">
                    <label for="test_type" class="form-label">Test Type</label>
                    <select name="test_type" id="test_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="blood" {{ request('test_type') === 'blood' ? 'selected' : '' }}>Blood Test</option>
                        <option value="urine" {{ request('test_type') === 'urine' ? 'selected' : '' }}>Urine Test</option>
                        <option value="imaging" {{ request('test_type') === 'imaging' ? 'selected' : '' }}>Imaging</option>
                        <option value="biopsy" {{ request('test_type') === 'biopsy' ? 'selected' : '' }}>Biopsy</option>
                        <option value="other" {{ request('test_type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('staff.lab-reports.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lab Reports Table -->
    <div class="card">
        <div class="doctor-card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Lab Reports
                <small class="text-muted">({{ $labReports->total() }} total)</small>
            </h6>
        </div>
        <div class="doctor-card-body">
            @if($labReports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="labReportsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Report #</th>
                                <th>Patient</th>
                                <th>Test Details</th>
                                <th>Doctor/Technician</th>
                                <th>Collection Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($labReports as $report)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $report->report_number }}</div>
                                    <small class="text-muted">{{ ucfirst($report->test_category) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($report->patient->first_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $report->patient->first_name }} {{ $report->patient->last_name }}</div>
                                            <small class="text-muted">{{ $report->patient->phone ?? 'No phone' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $report->test_name }}</div>
                                    <small class="text-muted">
                                        {{ ucfirst($report->test_type) }} | {{ ucfirst($report->specimen_type) }}
                                    </small>
                                </td>
                                <td>
                                    @if($report->doctor)
                                        <div class="fw-bold text-success">Dr. {{ $report->doctor->first_name }} {{ $report->doctor->last_name }}</div>
                                        <small class="text-muted">Ordered by</small>
                                    @endif
                                    @if($report->technician)
                                        <div class="fw-bold text-primary">{{ $report->technician->name }}</div>
                                        <small class="text-muted">Technician</small>
                                    @endif
                                    @if(!$report->doctor && !$report->technician && $report->createdBy)
                                        <div class="fw-bold text-info">{{ $report->createdBy->name }}</div>
                                        <small class="text-muted">Created by</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $report->collection_date->format('M d, Y') }}</div>
                                    @if($report->report_date)
                                        <small class="text-muted">Report: {{ $report->report_date->format('M d, Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $color = $statusColors[$report->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                                    @if($report->report_file)
                                        <div><small class="text-success"><i class="fas fa-file"></i> File attached</small></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('staff.lab-reports.show', $report) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($report->report_file)
                                            <a href="{{ route('staff.lab-reports.download', $report) }}" 
                                               class="btn btn-sm btn-outline-success" title="Download Report">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        
                                        @php
                                            $canEdit = false;
                                            $canUpdateStatus = false;
                                            
                                            if (auth()->user()->role === 'technician') {
                                                // Technicians can edit and update status of all reports
                                                $canEdit = in_array($report->status, ['pending', 'in_progress']);
                                                $canUpdateStatus = in_array($report->status, ['pending', 'in_progress']);
                                            } elseif (auth()->user()->role === 'doctor') {
                                                // Doctors can edit their own reports if still pending
                                                $userDoctor = \App\Models\Doctor::where('user_id', auth()->id())->first();
                                                $doctorId = $userDoctor ? $userDoctor->id : null;
                                                $canEdit = ($report->doctor_id == $doctorId && $report->status === 'pending');
                                            }
                                        @endphp
                                        
                                        @if($canEdit)
                                            <a href="{{ route('staff.lab-reports.edit', $report) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit Report">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        @if($canUpdateStatus)
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="updateStatus({{ $report->id }}, 'completed')" 
                                                    title="Mark as Completed">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $labReports->firstItem() }} to {{ $labReports->lastItem() }} 
                        of {{ $labReports->total() }} results
                    </div>
                    <div>
                        {{ $labReports->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-vial fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Lab Reports Found</h5>
                    <p class="text-muted mb-4">
                        @if(in_array(auth()->user()->role, ['doctor', 'technician']))
                            Start by creating your first lab report.
                        @else
                            No lab reports available to view at this time.
                        @endif
                    </p>
                    
                    @if(in_array(auth()->user()->role, ['doctor', 'technician']))
                        <a href="{{ route('staff.lab-reports.create') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-2"></i>Create First Report
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Status Update Modal -->
@if(auth()->user()->role === 'technician')
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Lab Report Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status_select" class="form-label">New Status</label>
                        <select id="status_select" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="technician_notes" class="form-label">Notes (Optional)</label>
                        <textarea id="technician_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-doctor-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#labReportsTable').DataTable({
        "paging": false,
        "info": false,
        "searching": false,
        "ordering": true,
        "order": [[ 4, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [6] }
        ]
    });
});

@if(auth()->user()->role === 'technician')
let currentReportId = null;

function updateStatus(reportId, status = null) {
    currentReportId = reportId;
    
    if (status) {
        $('#status_select').val(status);
    }
    
    $('#statusModal').modal('show');
}

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    const status = $('#status_select').val();
    const notes = $('#technician_notes').val();
    
    $.ajax({
        url: `/staff/lab-reports/${currentReportId}/status`,
        method: 'PATCH',
        data: {
            status: status,
            notes: notes,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + response.message);
            }
        },
        error: function() {
            alert('Error updating lab report status. Please try again.');
        }
    });
});
@endif
</script>
@endpush
