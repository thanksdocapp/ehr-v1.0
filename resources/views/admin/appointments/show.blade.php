@extends('admin.layouts.app')

@section('title', 'Appointment Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Appointments</a></li>
    <li class="breadcrumb-item active">Appointment Details</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Appointment Details</h5>
            <small class="text-muted">View and manage appointment information</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Appointment Information -->
        <div class="col-lg-8">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header d-flex justify-content-between align-items-center">
                    <h5 class="doctor-card-title mb-0">Appointment Information</h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-{{ $appointment->status_badge }} fs-6">{{ ucfirst($appointment->status) }}</span>
                        <span class="badge bg-{{ $appointment->type_badge }} fs-6">{{ ucfirst($appointment->type) }}</span>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Appointment Number</label>
                            <div class="fw-bold">{{ $appointment->appointment_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Type</label>
                            <div class="fw-bold">{{ ucfirst($appointment->type) }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Date</label>
                            <div class="fw-bold">{{ formatDate($appointment->appointment_date) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Time</label>
                            <div class="fw-bold">{{ $appointment->appointment_time->format('h:i A') }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Fee</label>
                            <div class="fw-bold">
                                @if($appointment->fee)
                                    ${{ number_format($appointment->fee, 2) }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Consultation Mode</label>
                            <div class="fw-bold">
                                @if($appointment->is_online)
                                    <span class="text-info"><i class="fas fa-video me-1"></i>Online</span>
                                @else
                                    <span class="text-primary"><i class="fas fa-hospital me-1"></i>In-Person</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($appointment->is_online)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted">Consultation Type</label>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info fs-6">
                                        <i class="fas fa-video me-1"></i>Online Consultation
                                    </span>
                                    @if($appointment->meeting_platform)
                                        <span class="badge bg-primary fs-6">
                                            <i class="{{ $appointment->meeting_platform_icon }} me-1"></i>{{ $appointment->meeting_platform_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        @if($appointment->meeting_link)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Meeting Link</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" 
                                               value="{{ $appointment->meeting_link }}" 
                                               readonly id="meeting_link_display_{{ $appointment->id }}">
                                        <button class="btn btn-outline-secondary" 
                                                type="button" 
                                                onclick="copyMeetingLink({{ $appointment->id }})"
                                                title="Copy Meeting Link">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        @if($appointment->canJoinMeeting())
                                            <a href="{{ $appointment->meeting_link }}" 
                                               target="_blank" 
                                               class="btn btn-success">
                                                <i class="fas fa-video me-1"></i>Join Meeting
                                            </a>
                                        @endif
                                    </div>
                                    @if($appointment->canJoinMeeting())
                                        <small class="text-success mt-1 d-block">
                                            <i class="fas fa-check-circle me-1"></i>Meeting is available now
                                        </small>
                                    @elseif($appointment->meeting_countdown)
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-clock me-1"></i>{{ $appointment->meeting_countdown }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label text-muted">Reason for Visit</label>
                            <div class="fw-bold">{{ $appointment->reason }}</div>
                        </div>
                    </div>

                    @if($appointment->symptoms)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted">Symptoms</label>
                                <div class="fw-bold">{{ $appointment->symptoms }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->notes)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted">Notes</label>
                                <div class="fw-bold">{{ $appointment->notes }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="mb-0">Patient Information</h5>
                </div>
                <div class="doctor-card-body">
                    @if($appointment->patient)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Full Name</label>
                                <div class="fw-bold">{{ $appointment->patient->full_name ?? $appointment->patient->first_name . ' ' . $appointment->patient->last_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Patient ID</label>
                                <div class="fw-bold">{{ $appointment->patient->patient_id ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email</label>
                                <div class="fw-bold">{{ $appointment->patient->email }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Phone</label>
                                <div class="fw-bold">{{ $appointment->patient->phone ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Patient record has been deleted (ID: {{ $appointment->patient_id }})
                        </div>
                    @endif

                    @if($appointment->patient->date_of_birth)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Date of Birth</label>
                                <div class="fw-bold">{{ formatDate($appointment->patient->date_of_birth) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Age</label>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($appointment->patient->date_of_birth)->age }} years</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->patient->gender)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Gender</label>
                                <div class="fw-bold">{{ ucfirst($appointment->patient->gender) }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->patient->address)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted">Address</label>
                                <div class="fw-bold">{{ $appointment->patient->address }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="mb-0">Doctor Information</h5>
                </div>
                <div class="doctor-card-body">
                    @if($appointment->doctor)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Doctor Name</label>
                                <div class="fw-bold">{{ $appointment->doctor->full_name ?? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Specialisation</label>
                                <div class="fw-bold">{{ $appointment->doctor->specialization ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Clinic</label>
                                <div class="fw-bold">{{ $appointment->doctor->department ? $appointment->doctor->department->name : 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email</label>
                                <div class="fw-bold">{{ $appointment->doctor->email }}</div>
                            </div>
                        </div>

                        @if($appointment->doctor->phone)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Phone</label>
                                    <div class="fw-bold">{{ $appointment->doctor->phone }}</div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Doctor record has been deleted (ID: {{ $appointment->doctor_id }})
                        </div>
                    @endif
                </div>
            </div>

            <!-- Medical Records -->
            @if($appointment->diagnosis || $appointment->prescription || $appointment->follow_up_instructions)
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="mb-0">Medical Records</h5>
                    </div>
                    <div class="doctor-card-body">
                        @if($appointment->diagnosis)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Diagnosis</label>
                                    <div class="fw-bold">{{ $appointment->diagnosis }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->prescription)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Prescription</label>
                                    <div class="fw-bold">{{ $appointment->prescription }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->follow_up_instructions)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Follow-up Instructions</label>
                                    <div class="fw-bold">{{ $appointment->follow_up_instructions }}</div>
                                </div>
                            </div>
                        @endif

                        @if($appointment->next_appointment_date)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label text-muted">Next Appointment Date</label>
                                    <div class="fw-bold">{{ formatDate($appointment->next_appointment_date) }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        @if($appointment->status === 'pending')
                            <button class="btn btn-success" onclick="confirmAppointment({{ $appointment->id }})">
                                <i class="fas fa-check me-2"></i>Confirm Appointment
                            </button>
                        @endif

                        @if(in_array($appointment->status, ['pending', 'confirmed']))
                            <button class="btn btn-warning" onclick="rescheduleAppointment({{ $appointment->id }})">
                                <i class="fas fa-calendar-alt me-2"></i>Reschedule
                            </button>
                            <button class="btn btn-danger" onclick="cancelAppointment({{ $appointment->id }})">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                        @endif

                        @if($appointment->canBeCheckedIn())
                            <button class="btn btn-info" onclick="checkInPatient({{ $appointment->id }})">
                                <i class="fas fa-sign-in-alt me-2"></i>Check In
                            </button>
                        @endif

                        @if($appointment->canBeCheckedOut())
                            <button class="btn btn-success" onclick="checkOutPatient({{ $appointment->id }})">
                                <i class="fas fa-sign-out-alt me-2"></i>Check Out
                            </button>
                        @endif

                        @if($appointment->status === 'confirmed')
                            <button class="btn btn-doctor-primary" onclick="completeAppointment({{ $appointment->id }})">
                                <i class="fas fa-check-circle me-2"></i>Mark Complete
                            </button>
                        @endif

                        @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                            <div class="dropdown-divider"></div>
                            <a href="{{ $appointment->meeting_link }}" 
                               target="_blank" 
                               class="btn btn-success">
                                <i class="fas fa-video me-2"></i>Join Video Call
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Appointment Status -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="mb-0">Status Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Current Status</label>
                        <div>
                            <span class="badge bg-{{ $appointment->status_badge }} fs-6">{{ ucfirst($appointment->status) }}</span>
                        </div>
                    </div>

                    @if($appointment->check_in_time)
                        <div class="mb-3">
                            <label class="form-label text-muted">Check-in Time</label>
                            <div class="fw-bold">{{ $appointment->check_in_time->format('M j, Y h:i A') }}</div>
                        </div>
                    @endif

                    @if($appointment->check_out_time)
                        <div class="mb-3">
                            <label class="form-label text-muted">Check-out Time</label>
                            <div class="fw-bold">{{ $appointment->check_out_time->format('M j, Y h:i A') }}</div>
                        </div>
                    @endif

                    @if($appointment->duration)
                        <div class="mb-3">
                            <label class="form-label text-muted">Duration</label>
                            <div class="fw-bold">{{ $appointment->duration }} minutes</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-muted">Created</label>
                        <div class="fw-bold">{{ $appointment->created_at->format('M j, Y h:i A') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Last Updated</label>
                        <div class="fw-bold">{{ $appointment->updated_at->format('M j, Y h:i A') }}</div>
                    </div>
                </div>
            </div>

            <!-- Appointment Timeline -->
            @if($appointment->isOverdue())
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header bg-warning">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-exclamation-triangle me-2"></i>Overdue
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="text-warning mb-0">
                            This appointment is overdue. Please take appropriate action.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rescheduleModalLabel">Reschedule Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rescheduleForm">
                    <div class="mb-3">
                        <label for="new_date" class="form-label">New Date</label>
                        <input type="text" class="form-control" id="new_date" name="new_date" 
                               placeholder="dd-mm-yyyy" 
                               pattern="\d{2}-\d{2}-\d{4}" 
                               maxlength="10" required>
                        <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                    </div>
                    <div class="mb-3">
                        <label for="new_time" class="form-label">New Time</label>
                        <input type="time" class="form-control" id="new_time" name="new_time" required>
                    </div>
                    <div class="mb-3">
                        <label for="reschedule_reason" class="form-label">Reason for Rescheduling</label>
                        <textarea class="form-control" id="reschedule_reason" name="reason" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitReschedule()">Reschedule</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Date input mask for dd-mm-yyyy format in reschedule modal
$(document).ready(function() {
    $('#new_date').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        $(this).val(value);
    });

    // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission
    $('#rescheduleForm').on('submit', function() {
        const dateInput = $('#new_date');
        const dateStr = dateInput.val();
        if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
            const parts = dateStr.split('-');
            const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
            dateInput.val(yyyyMmDd);
        }
    });
});

function confirmAppointment(appointmentId) {
    if (confirm('Are you sure you want to confirm this appointment?')) {
        updateAppointmentStatus(appointmentId, 'confirmed');
    }
}

function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        updateAppointmentStatus(appointmentId, 'cancelled');
    }
}

function completeAppointment(appointmentId) {
    if (confirm('Are you sure you want to mark this appointment as complete?')) {
        updateAppointmentStatus(appointmentId, 'completed');
    }
}

function updateAppointmentStatus(appointmentId, status) {
    fetch(`/admin/appointments/${appointmentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating appointment status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating appointment status');
    });
}

function rescheduleAppointment(appointmentId) {
    document.getElementById('rescheduleForm').dataset.appointmentId = appointmentId;
    new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
}

function submitReschedule() {
    const form = document.getElementById('rescheduleForm');
    const appointmentId = form.dataset.appointmentId;
    const formData = new FormData(form);
    
    fetch(`/admin/appointments/${appointmentId}/reschedule`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('rescheduleModal')).hide();
            location.reload();
        } else {
            alert('Error rescheduling appointment');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error rescheduling appointment');
    });
}

function checkInPatient(appointmentId) {
    if (confirm('Are you sure you want to check in this patient?')) {
        fetch(`/admin/appointments/${appointmentId}/check-in`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error checking in patient');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error checking in patient');
        });
    }
}

function checkOutPatient(appointmentId) {
    if (confirm('Are you sure you want to check out this patient?')) {
        fetch(`/admin/appointments/${appointmentId}/check-out`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error checking out patient');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error checking out patient');
        });
    }
}

function copyMeetingLink(appointmentId) {
    const meetingLink = document.getElementById('meeting_link_display_' + appointmentId);
    if (!meetingLink) return;
    
    meetingLink.select();
    meetingLink.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(meetingLink.value).then(function() {
        const btn = event.target.closest('button');
        if (btn) {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.innerHTML = originalHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }
    });
}
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    border-radius: 10px;
}

.card-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    border-radius: 10px 10px 0 0;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.badge {
    font-size: 0.875rem;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style>
@endpush
