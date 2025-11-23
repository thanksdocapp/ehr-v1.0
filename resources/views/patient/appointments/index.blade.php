@extends('patient.layouts.app')

@section('title', 'My Appointments')
@section('page-title', 'My Appointments')

@section('content')
    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('patient.appointments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-refresh me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-primary-gradient">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number text-primary">{{ $stats['total'] }}</div>
                <div class="stat-label">Total Appointments</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-warning-gradient">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number text-warning">{{ $stats['upcoming'] }}</div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-success-gradient">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number text-success">{{ $stats['completed'] }}</div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon bg-danger-gradient">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-number text-danger">{{ $stats['cancelled'] }}</div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Appointments List
                </h5>
                <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Book New Appointment
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($appointments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Doctor</th>
                                <th>Department</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong><br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $appointment->doctor->full_name }}</strong><br>
                                            <small class="text-muted">{{ $appointment->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $appointment->department->name }}</td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px; display: inline-block;" 
                                              title="{{ $appointment->reason }}">
                                            {{ Str::limit($appointment->reason, 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $appointment->status === 'confirmed' ? 'success' : 
                                            ($appointment->status === 'pending' ? 'warning' : 
                                            ($appointment->status === 'completed' ? 'info' : 'danger')) 
                                        }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                        @if($appointment->is_online)
                                            <br><span class="badge bg-info mt-1">
                                                <i class="fas fa-video me-1"></i>Online
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('patient.appointments.show', $appointment) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                                                <a href="{{ $appointment->meeting_link }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-success" 
                                                   title="Join Meeting">
                                                    <i class="fas fa-video"></i>
                                                </a>
                                            @endif
                                            @if($appointment->status !== 'cancelled' && $appointment->status !== 'completed')
                                                <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" 
                                                      class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this appointment?')">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel Appointment">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @elseif($appointment->status === 'cancelled')
                                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                        title="Delete Cancelled Appointment" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $appointment->id }}">
                                                    <i class="fas fa-trash"></i>
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
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} of {{ $appointments->total() }} results
                    </div>
                    {{ $appointments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No appointments found</h5>
                    <p class="text-muted">You haven't booked any appointments with the current filters.</p>
                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Book Your First Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modals -->
    @foreach($appointments as $appointment)
        @if($appointment->status === 'cancelled')
            <div class="modal fade" id="deleteModal{{ $appointment->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $appointment->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel{{ $appointment->id }}">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Delete Appointment
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Warning:</strong> This action cannot be undone. The appointment will be permanently removed from your records.
                            </div>
                            
                            <p>Are you sure you want to permanently delete this cancelled appointment?</p>
                            
                            <div class="appointment-summary bg-light p-3 rounded">
                                <h6 class="mb-2"><i class="fas fa-calendar-alt me-2"></i>Appointment Details:</h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="mb-1"><strong>Number:</strong> {{ $appointment->appointment_number }}</p>
                                        <p class="mb-1"><strong>Date:</strong> {{ $appointment->appointment_date->format('M d, Y') }}</p>
                                        <p class="mb-1"><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-1"><strong>Doctor:</strong> {{ $appointment->doctor->full_name }}</p>
                                        <p class="mb-1"><strong>Department:</strong> {{ $appointment->department->name }}</p>
                                        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-danger">{{ ucfirst($appointment->status) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>
                                Keep Appointment
                            </button>
                            <form method="POST" action="{{ route('patient.appointments.destroy', $appointment) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i>
                                    Yes, Delete Permanently
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
