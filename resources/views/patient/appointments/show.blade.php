@extends('patient.layouts.app')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment Details')

@section('content')
    <!-- Appointment Header -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="fas fa-calendar-check me-2"></i>
                        Appointment #{{ $appointment->appointment_number }}
                    </h5>
                    <div class="d-flex align-items-center">
                        @php
                            $statusClass = match($appointment->status) {
                                'confirmed' => 'bg-success',
                                'pending' => 'bg-warning',
                                'cancelled' => 'bg-danger',
                                'completed' => 'bg-info',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }} me-2">{{ ucfirst($appointment->status) }}</span>
                        @if($appointment->priority && $appointment->priority !== 'normal')
                            <span class="badge bg-{{ $appointment->priority === 'urgent' ? 'danger' : ($appointment->priority === 'high' ? 'warning' : 'info') }}">
                                {{ ucfirst($appointment->priority) }} Priority
                            </span>
                        @endif
                    </div>
                </div>
                <div class="btn-group">
                    <a href="{{ route('patient.appointments.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Appointments
                    </a>
                    @if($appointment->status !== 'cancelled' && $appointment->status !== 'completed')
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-times me-1"></i>
                            Cancel Appointment
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointment Details -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Appointment Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Date & Time</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <div>
                                    <strong>{{ $appointment->appointment_date->format('l, M d, Y') }}</strong><br>
                                    <span class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Doctor</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-md text-success me-2"></i>
                                <div>
                                    <strong>{{ $appointment->doctor->full_name }}</strong><br>
                                    <span class="text-muted">{{ $appointment->doctor->specialization }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Department</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building text-info me-2"></i>
                                <div>
                                    <strong>{{ $appointment->department->name }}</strong><br>
                                    <span class="text-muted">{{ $appointment->department->description ?? 'Medical Department' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Booking Date</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <div>
                                    <strong>{{ $appointment->created_at->format('M d, Y') }}</strong><br>
                                    <span class="text-muted">{{ $appointment->created_at->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($appointment->is_online)
                        <div class="alert alert-info mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-video me-2"></i>
                                <div class="flex-grow-1">
                                    <strong>Online Consultation</strong>
                                    @if($appointment->meeting_platform)
                                        <br><small><i class="{{ $appointment->meeting_platform_icon }} me-1"></i>{{ $appointment->meeting_platform_name }}</small>
                                    @endif
                                </div>
                                @if($appointment->meeting_link)
                                    <span class="badge bg-primary">{{ $appointment->meeting_platform_name ?? 'Online' }}</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($appointment->meeting_link && $appointment->canJoinMeeting())
                            <div class="alert alert-success mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong><i class="fas fa-calendar-check me-2"></i>Meeting Available</strong>
                                        <br><small>{{ $appointment->meeting_countdown ?? 'Join now' }}</small>
                                    </div>
                                    <a href="{{ $appointment->meeting_link }}" 
                                       target="_blank" 
                                       class="btn btn-success btn-lg">
                                        <i class="fas fa-video me-2"></i>Join Meeting
                                    </a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meeting Link:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" 
                                           value="{{ $appointment->meeting_link }}" 
                                           readonly id="meeting_link_display">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="copyMeetingLink()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        @elseif($appointment->meeting_link && $appointment->status === 'confirmed')
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong><i class="fas fa-clock me-2"></i>Meeting Link</strong>
                                        <br>
                                        @if($appointment->meeting_countdown)
                                            <small>{{ $appointment->meeting_countdown }}</small>
                                        @else
                                            <small>Meeting link will be available 15 minutes before your appointment.</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meeting Link:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" 
                                           value="{{ $appointment->meeting_link }}" 
                                           readonly id="meeting_link_display">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            onclick="copyMeetingLink()">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                                <small class="text-muted">You can join the meeting 15 minutes before your scheduled time.</small>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary mb-3">
                            <i class="fas fa-building me-2"></i><strong>In-Person Consultation</strong>
                        </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-12 mb-3">
                            <h6 class="text-muted mb-2">Reason for Visit</h6>
                            <p class="mb-0">{{ $appointment->reason }}</p>
                        </div>
                        @if($appointment->notes)
                            <div class="col-12">
                                <h6 class="text-muted mb-2">Additional Notes</h6>
                                <p class="mb-0">{{ $appointment->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Appointment Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Appointment Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Appointment Booked</h6>
                                <p class="text-muted mb-0">{{ $appointment->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        
                        @if($appointment->status === 'confirmed' || $appointment->status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Appointment Confirmed</h6>
                                    <p class="text-muted mb-0">Confirmed by medical staff</p>
                                </div>
                            </div>
                        @endif

                        @if($appointment->status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Appointment Completed</h6>
                                    <p class="text-muted mb-0">{{ $appointment->appointment_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($appointment->status === 'cancelled')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Appointment Cancelled</h6>
                                    <p class="text-muted mb-0">
                                        {{ $appointment->cancelled_at ? $appointment->cancelled_at->format('M d, Y g:i A') : 'Recently cancelled' }}
                                        @if($appointment->cancelled_by)
                                            <br><small>Cancelled by: {{ ucfirst($appointment->cancelled_by) }}</small>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($appointment->status !== 'cancelled' && $appointment->status !== 'completed')
                        <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-times me-1"></i>
                            Cancel Appointment
                        </button>
                    @endif
                    
                    @if($appointment->status === 'pending')
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            <small>Your appointment is pending confirmation. You will be notified once confirmed.</small>
                        </div>
                    @endif

                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-plus me-1"></i>
                        Book Another Appointment
                    </a>
                    
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        Print Details
                    </button>
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-md me-2"></i>
                        Doctor Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($appointment->doctor->photo)
                            <img src="{{ asset('storage/uploads/doctors/' . $appointment->doctor->photo) }}" 
                                 alt="{{ $appointment->doctor->full_name }}" 
                                 class="rounded-circle" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user-md fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-center">
                        <h6 class="mb-1">{{ $appointment->doctor->full_name }}</h6>
                        <p class="text-muted mb-2">{{ $appointment->doctor->specialization }}</p>
                        @if($appointment->doctor->experience_years)
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $appointment->doctor->experience_years }} years experience
                            </small>
                        @endif
                    </div>

                    @if($appointment->doctor->bio)
                        <hr>
                        <p class="small text-muted mb-0">{{ Str::limit($appointment->doctor->bio, 100) }}</p>
                    @endif
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-phone me-2"></i>
                        Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-1">Hospital</h6>
                        <p class="text-muted mb-0">
                            <i class="fas fa-phone me-1"></i>
                            (555) 123-4567
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-envelope me-1"></i>
                            appointments@hospital.com
                        </p>
                    </div>
                    
                    <div class="alert alert-light">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            For appointment changes or questions, please call at least 24 hours in advance.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Appointment Modal -->
    @if($appointment->status !== 'cancelled' && $appointment->status !== 'completed')
        <div class="modal fade" id="cancelModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            Cancel Appointment
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Please note:</strong> Appointments cannot be cancelled less than 24 hours before the scheduled time.
                        </div>
                        
                        <p>Are you sure you want to cancel this appointment?</p>
                        
                        <div class="appointment-summary bg-light p-3 rounded">
                            <h6 class="mb-2">Appointment Details:</h6>
                            <p class="mb-1"><strong>Date:</strong> {{ $appointment->appointment_date->format('M d, Y') }}</p>
                            <p class="mb-1"><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
                            <p class="mb-0"><strong>Doctor:</strong> {{ $appointment->doctor->full_name }}</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Keep Appointment
                        </button>
                        <form action="{{ route('patient.appointments.cancel', $appointment) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-1"></i>
                                Yes, Cancel Appointment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function copyMeetingLink() {
        const meetingLink = document.getElementById('meeting_link_display');
        meetingLink.select();
        meetingLink.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(meetingLink.value).then(function() {
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        });
    }
</script>
@endpush

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        height: 100%;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -23px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #dee2e6;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #007bff;
    }

    @media print {
        .btn, .card-header .btn-group, .modal {
            display: none !important;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection
