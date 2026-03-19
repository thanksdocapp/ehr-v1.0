@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Sent Emails')
@section('page-title', 'Sent Emails')

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-envelope-open-text me-2 text-primary"></i>Sent Emails
                    </h1>
                    <p class="text-muted mb-0">View emails you've sent to patients</p>
                </div>
                <a href="{{ route('staff.patient-email.compose') }}" class="btn btn-doctor-primary">
                    <i class="fas fa-plus me-2"></i>Compose New Email
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-wrapper bg-primary text-white rounded-circle p-3">
                                <i class="fas fa-inbox fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-number h3 mb-0">{{ number_format($stats['total_emails'] ?? 0) }}</div>
                            <div class="stat-label text-muted">Total Emails</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-wrapper bg-success text-white rounded-circle p-3">
                                <i class="fas fa-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-number h3 mb-0">{{ number_format($stats['sent_emails'] ?? 0) }}</div>
                            <div class="stat-label text-muted">Sent Successfully</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="stat-icon-wrapper bg-danger text-white rounded-circle p-3">
                                <i class="fas fa-exclamation-triangle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="stat-number h3 mb-0">{{ number_format($stats['failed_emails'] ?? 0) }}</div>
                            <div class="stat-label text-muted">Failed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.patient-email.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="patient_id" class="form-label">Patient</label>
                    <select class="form-control" id="patient_id" name="patient_id">
                        <option value="">All Patients</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Email, subject..." value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('staff.patient-email.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Logs Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-list me-2"></i>Email History</h5>
        </div>
        <div class="doctor-card-body">
            @if($emailLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date Sent</th>
                                <th>Patient</th>
                                <th>Recipient Email</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emailLogs as $emailLog)
                                <tr>
                                    <td>
                                        <div>{{ formatDateUk($emailLog->created_at) }}</div>
                                        <small class="text-muted">{{ $emailLog->created_at->format('g:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($emailLog->patient)
                                            <a href="{{ route('staff.patients.show', $emailLog->patient) }}" class="text-decoration-none">
                                                {{ $emailLog->patient->full_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $emailLog->recipient_email }}</small>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 300px;" title="{{ $emailLog->subject }}">
                                            {{ $emailLog->subject }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($emailLog->status === 'sent')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Sent
                                            </span>
                                        @elseif($emailLog->status === 'failed')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-circle me-1"></i>Failed
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>{{ ucfirst($emailLog->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('staff.patient-email.show', $emailLog->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $emailLogs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No emails found.</p>
                    <a href="{{ route('staff.patient-email.compose') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Compose Your First Email
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

