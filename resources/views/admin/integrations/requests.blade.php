@extends('admin.layouts.app')

@section('title', $module->name . ' - Requests')
@section('page-title', $module->name . ' Requests')

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.integrations.show', $module) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to {{ $module->name }}
        </a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Request Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @if($module->type === 'lab_tests')
                            <option value="order_test" {{ request('type') === 'order_test' ? 'selected' : '' }}>Order Test</option>
                            <option value="check_status" {{ request('type') === 'check_status' ? 'selected' : '' }}>Check Status</option>
                            <option value="fetch_results" {{ request('type') === 'fetch_results' ? 'selected' : '' }}>Fetch Results</option>
                        @elseif($module->type === 'prescriptions')
                            <option value="send_prescription" {{ request('type') === 'send_prescription' ? 'selected' : '' }}>Send Prescription</option>
                            <option value="check_status" {{ request('type') === 'check_status' ? 'selected' : '' }}>Check Status</option>
                            <option value="search_pharmacy" {{ request('type') === 'search_pharmacy' ? 'selected' : '' }}>Search Pharmacy</option>
                        @elseif($module->type === 'imaging')
                            <option value="submit_referral" {{ request('type') === 'submit_referral' ? 'selected' : '' }}>Submit Referral</option>
                            <option value="book_appointment" {{ request('type') === 'book_appointment' ? 'selected' : '' }}>Book Appointment</option>
                            <option value="fetch_report" {{ request('type') === 'fetch_report' ? 'selected' : '' }}>Fetch Report</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Date Range</label>
                    <select name="date_range" class="form-select">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-warning">{{ $stats['pending'] ?? 0 }}</h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-info">{{ $stats['processing'] ?? 0 }}</h4>
                    <small class="text-muted">Processing</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-success">{{ $stats['completed'] ?? 0 }}</h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-danger">{{ $stats['failed'] ?? 0 }}</h4>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-secondary">{{ $stats['cancelled'] ?? 0 }}</h4>
                    <small class="text-muted">Cancelled</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-list me-2 text-primary"></i>Integration Requests
            </h5>
        </div>
        <div class="card-body p-0">
            @if($requests->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No requests found matching your criteria.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                <tr>
                                    <td>
                                        <code class="small">{{ $request->internal_reference }}</code>
                                        @if($request->external_reference)
                                            <br><small class="text-muted">Ext: {{ $request->external_reference }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            {{ str_replace('_', ' ', ucfirst($request->request_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($request->patient)
                                            <a href="{{ route('admin.patients.show', $request->patient) }}">
                                                {{ $request->patient->full_name }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($request->doctor)
                                            {{ $request->doctor->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $request->getStatusBadgeClass() }}">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>{{ $request->created_at->format('d M Y H:i') }}</small>
                                        <br><small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#requestModal{{ $request->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Request Details Modal -->
                                <div class="modal fade" id="requestModal{{ $request->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-uppercase text-muted small mb-3">Request Information</h6>
                                                        <table class="table table-sm">
                                                            <tr>
                                                                <th class="w-50">Internal Ref:</th>
                                                                <td><code>{{ $request->internal_reference }}</code></td>
                                                            </tr>
                                                            <tr>
                                                                <th>External Ref:</th>
                                                                <td>{{ $request->external_reference ?? 'N/A' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Type:</th>
                                                                <td>{{ str_replace('_', ' ', ucfirst($request->request_type)) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Status:</th>
                                                                <td><span class="badge {{ $request->getStatusBadgeClass() }}">{{ $request->getStatusLabel() }}</span></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Created:</th>
                                                                <td>{{ $request->created_at->format('d M Y H:i:s') }}</td>
                                                            </tr>
                                                            @if($request->submitted_at)
                                                                <tr>
                                                                    <th>Submitted:</th>
                                                                    <td>{{ $request->submitted_at->format('d M Y H:i:s') }}</td>
                                                                </tr>
                                                            @endif
                                                            @if($request->completed_at)
                                                                <tr>
                                                                    <th>Completed:</th>
                                                                    <td>{{ $request->completed_at->format('d M Y H:i:s') }}</td>
                                                                </tr>
                                                            @endif
                                                        </table>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6 class="text-uppercase text-muted small mb-3">Related Records</h6>
                                                        <table class="table table-sm">
                                                            <tr>
                                                                <th class="w-50">Patient:</th>
                                                                <td>{{ $request->patient?->full_name ?? 'N/A' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Doctor:</th>
                                                                <td>{{ $request->doctor?->name ?? 'N/A' }}</td>
                                                            </tr>
                                                            <tr>
                                                                <th>Appointment:</th>
                                                                <td>{{ $request->appointment_id ?? 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>

                                                @if($request->request_data)
                                                    <hr>
                                                    <h6 class="text-uppercase text-muted small mb-3">Request Data</h6>
                                                    <pre class="bg-light p-3 rounded small" style="max-height: 200px; overflow: auto;">{{ json_encode($request->request_data, JSON_PRETTY_PRINT) }}</pre>
                                                @endif

                                                @if($request->response_data)
                                                    <hr>
                                                    <h6 class="text-uppercase text-muted small mb-3">Response Data</h6>
                                                    <pre class="bg-light p-3 rounded small" style="max-height: 200px; overflow: auto;">{{ json_encode($request->response_data, JSON_PRETTY_PRINT) }}</pre>
                                                @endif

                                                @if($request->error_message)
                                                    <hr>
                                                    <div class="alert alert-danger">
                                                        <h6 class="alert-heading small">Error Message</h6>
                                                        <p class="mb-0 small">{{ $request->error_message }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-white">
                    {{ $requests->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
