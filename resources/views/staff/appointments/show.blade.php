@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Appointment Details')
@section('page-title', 'Appointment Details')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Review patient appointment details and medical information' : 'View appointment information and status')

@section('content')
<div class="fade-in-up">

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Patient Alert Bar -->
    @if($appointment->patient)
        @include('components.patient-alert-bar', ['patient' => $appointment->patient])
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Appointment Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-calendar-alt me-2"></i>Appointment Information</h5>
                        <div class="d-flex gap-2">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger'
                                ];
                                $statusColor = $statusColors[$appointment->status] ?? 'secondary';
                                
                                $typeColors = [
                                    'consultation' => 'primary',
                                    'follow_up' => 'info',
                                    'routine_checkup' => 'success',
                                    'emergency' => 'danger'
                                ];
                                $typeColor = $typeColors[$appointment->appointment_type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($appointment->status) }}</span>
                            <span class="badge bg-{{ $typeColor }} fs-6">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-hashtag me-1"></i>Appointment Number</label>
                            <div class="fw-bold text-primary">{{ $appointment->appointment_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-clipboard-list me-1"></i>Type</label>
                            <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $appointment->appointment_type)) }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-calendar me-1"></i>Date</label>
                            <div class="fw-bold">{{ formatDate($appointment->appointment_date) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-clock me-1"></i>Time</label>
                            <div class="fw-bold">{{ date('g:i A', strtotime($appointment->appointment_time)) }}</div>
                        </div>
                    </div>

                    @if($appointment->estimated_duration)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-hourglass-half me-1"></i>Estimated Duration</label>
                                <div class="fw-bold">{{ $appointment->estimated_duration }} minutes</div>
                            </div>
                            @if($appointment->priority)
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><i class="fas fa-exclamation me-1"></i>Priority</label>
                                    <div class="fw-bold">
                                        @php
                                            $priorityColors = ['normal' => 'success', 'high' => 'warning', 'urgent' => 'danger'];
                                            $priorityColor = $priorityColors[$appointment->priority] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $priorityColor }}">{{ ucfirst($appointment->priority) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($appointment->reason)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted"><i class="fas fa-notes-medical me-1"></i>Reason for Visit</label>
                                <div class="fw-bold">{{ $appointment->reason }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->notes)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted"><i class="fas fa-sticky-note me-1"></i>Staff Notes</label>
                                <div class="fw-bold">{{ $appointment->notes }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->is_online)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted"><i class="fas fa-video me-1"></i>Consultation Type</label>
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
                                    <label class="form-label text-muted"><i class="fas fa-link me-1"></i>Meeting Link</label>
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
                                            <i class="fas fa-check-circle me-1"></i>Meeting is available now - Click "Join Meeting" to start
                                        </small>
                                    @elseif($appointment->meeting_countdown)
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-clock me-1"></i>{{ $appointment->meeting_countdown }}
                                        </small>
                                    @else
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-info-circle me-1"></i>Meeting link will be available 15 minutes before appointment time
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted"><i class="fas fa-building me-1"></i>Consultation Type</label>
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-building me-1"></i>In-Person Consultation
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-id-card me-1"></i>Full Name</label>
                            <div class="fw-bold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                        </div>
                        @if($appointment->patient->patient_id)
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-barcode me-1"></i>Patient ID</label>
                                <div class="fw-bold">{{ $appointment->patient->patient_id }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-envelope me-1"></i>Email</label>
                            <div class="fw-bold">
                                @if($appointment->patient->email)
                                    <a href="mailto:{{ $appointment->patient->email }}" class="text-decoration-none">
                                        {{ $appointment->patient->email }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-phone me-1"></i>Phone</label>
                            <div class="fw-bold">
                                @if($appointment->patient->phone)
                                    <a href="tel:{{ $appointment->patient->phone }}" class="text-decoration-none">
                                        {{ $appointment->patient->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($appointment->patient->date_of_birth)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-birthday-cake me-1"></i>Date of Birth</label>
                                <div class="fw-bold">{{ formatDate($appointment->patient->date_of_birth) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-calculator me-1"></i>Age</label>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($appointment->patient->date_of_birth)->age }} years</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->patient->gender)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-venus-mars me-1"></i>Gender</label>
                                <div class="fw-bold">{{ ucfirst($appointment->patient->gender) }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->patient->address)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted"><i class="fas fa-map-marker-alt me-1"></i>Address</label>
                                <div class="fw-bold">{{ $appointment->patient->address }}</div>
                            </div>
                        </div>
                    @endif

                    @if($appointment->patient->emergency_contact_name || $appointment->patient->emergency_contact_phone)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-user-shield me-1"></i>Emergency Contact</label>
                                <div class="fw-bold">{{ $appointment->patient->emergency_contact_name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-phone-alt me-1"></i>Emergency Phone</label>
                                <div class="fw-bold">
                                    @if($appointment->patient->emergency_contact_phone)
                                        <a href="tel:{{ $appointment->patient->emergency_contact_phone }}" class="text-decoration-none">
                                            {{ $appointment->patient->emergency_contact_phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Doctor Information -->
            @if($appointment->doctor)
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>Doctor Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-user-tie me-1"></i>Doctor Name</label>
                                <div class="fw-bold">{{ formatDoctorName($appointment->doctor->name) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-stethoscope me-1"></i>Specialisation</label>
                                <div class="fw-bold">{{ $appointment->doctor->specialization ?? 'GP' }}</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            @if($appointment->department)
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><i class="fas fa-hospital me-1"></i>Clinic</label>
                                    <div class="fw-bold">{{ $appointment->department->name }}</div>
                                </div>
                            @endif
                            @if($appointment->doctor->email)
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><i class="fas fa-envelope me-1"></i>Email</label>
                                    <div class="fw-bold">
                                        <a href="mailto:{{ $appointment->doctor->email }}" class="text-decoration-none">
                                            {{ $appointment->doctor->email }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if($appointment->doctor->phone)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted"><i class="fas fa-phone me-1"></i>Phone</label>
                                    <div class="fw-bold">
                                        <a href="tel:{{ $appointment->doctor->phone }}" class="text-decoration-none">
                                            {{ $appointment->doctor->phone }}
                                        </a>
                                    </div>
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
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-cogs me-2"></i>Quick Actions</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Appointments
                        </a>

                        @if($appointment->status === 'pending')
                            <a href="{{ route('staff.appointments.edit', $appointment->id) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-1"></i>Edit Appointment
                            </a>
                            <button class="btn btn-success" onclick="updateStatus({{ $appointment->id }}, 'confirmed')">
                                <i class="fas fa-check me-1"></i>Confirm Appointment
                            </button>
                        @endif

                        @if($appointment->status === 'confirmed' && auth()->user()->role === 'doctor')
                            <button class="btn btn-primary" onclick="updateStatus({{ $appointment->id }}, 'completed')">
                                <i class="fas fa-check-double me-1"></i>Mark as Completed
                            </button>
                        @endif

                        @if(in_array($appointment->status, ['pending', 'confirmed']))
                            <button class="btn btn-outline-danger" onclick="updateStatus({{ $appointment->id }}, 'cancelled')">
                                <i class="fas fa-times me-1"></i>Cancel Appointment
                            </button>
                        @endif

                        @if(auth()->user()->role === 'doctor')
                            <div class="dropdown-divider"></div>
                            <h6 class="dropdown-header text-uppercase fw-bold">Workflow Actions</h6>
                            @if($appointment->status === 'confirmed' || $appointment->status === 'completed')
                                @php
                                    $hasMedicalRecord = $appointment->medicalRecord ? true : false;
                                    $medicalRecordId = $appointment->medicalRecord ? $appointment->medicalRecord->id : null;
                                @endphp
                                @if(!$hasMedicalRecord)
                                    <a href="{{ route('staff.medical-records.create', ['appointment_id' => $appointment->id, 'patient_id' => $appointment->patient_id]) }}" 
                                       class="btn btn-doctor-primary w-100 mb-2">
                                        <i class="fas fa-file-medical me-2"></i>Create Medical Record
                                    </a>
                                @else
                                    <a href="{{ route('staff.medical-records.show', $medicalRecordId) }}" 
                                       class="btn btn-info w-100 mb-2">
                                        <i class="fas fa-file-medical me-2"></i>View Medical Record
                                    </a>
                                @endif
                                
                                @if($hasMedicalRecord && !$appointment->medicalRecord->prescriptions->count())
                                    <a href="{{ route('staff.prescriptions.create', ['medical_record_id' => $medicalRecordId, 'patient_id' => $appointment->patient_id]) }}" 
                                       class="btn btn-success w-100 mb-2">
                                        <i class="fas fa-prescription-bottle-alt me-2"></i>Write Prescription
                                    </a>
                                @elseif($hasMedicalRecord)
                                    <a href="{{ route('staff.prescriptions.index', ['medical_record_id' => $medicalRecordId]) }}" 
                                       class="btn btn-outline-success w-100 mb-2">
                                        <i class="fas fa-prescription-bottle me-2"></i>View Prescriptions ({{ $appointment->medicalRecord->prescriptions->count() }})
                                    </a>
                                @endif
                                
                                <a href="{{ route('staff.lab-reports.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id]) }}" 
                                   class="btn btn-outline-info w-100 mb-2">
                                    <i class="fas fa-vial me-2"></i>Order Lab Test
                                </a>
                            @endif
                        @endif

                        @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                            <div class="dropdown-divider"></div>
                            <a href="{{ $appointment->meeting_link }}" 
                               target="_blank" 
                               class="btn btn-success">
                                <i class="fas fa-video me-1"></i>Join Video Call
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Status Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Current Status</label>
                        <div>
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($appointment->status) }}</span>
                        </div>
                    </div>

                    @if($appointment->completed_at)
                        <div class="mb-3">
                            <label class="form-label text-muted">Completed At</label>
                            <div class="fw-bold">{{ $appointment->completed_at->format('M j, Y h:i A') }}</div>
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

                    @if($appointment->created_by)
                        <div class="mb-3">
                            <label class="form-label text-muted">Created By</label>
                            <div class="fw-bold">Staff Member</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Guidelines -->
            <div class="doctor-card border-info">
                <div class="doctor-card-body">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-lightbulb text-info fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-info">Appointment Guidelines</h6>
                            <ul class="mb-0 text-muted small">
                                <li class="mb-1">Confirm appointments at least 30 minutes before scheduled time</li>
                                <li class="mb-1">Contact patients for any schedule changes</li>
                                <li class="mb-1">Ensure all patient information is up to date</li>
                                <li>Medical records can be created during or after the appointment</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Appointment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status_select" class="form-label">New Status</label>
                        <select id="status_select" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="staff_notes" class="form-label">Notes (Optional)</label>
                        <textarea id="staff_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentAppointmentId = null;

function updateStatus(appointmentId, status = null) {
    currentAppointmentId = appointmentId;
    
    if (status) {
        $('#status_select').val(status);
    }
    
    $('#statusModal').modal('show');
}

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    const status = $('#status_select').val();
    const notes = $('#staff_notes').val();
    
    $.ajax({
        url: `/staff/appointments/${currentAppointmentId}/status`,
        method: 'PATCH',
        data: {
            status: status,
            notes: notes,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#statusModal').modal('hide');
            location.reload();
        },
        error: function(xhr) {
            alert('Error updating status. Please try again.');
        }
    });
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut();
}, 30000);

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
    }).catch(function(err) {
        // Fallback for older browsers
        document.execCommand('copy');
        alert('Meeting link copied to clipboard!');
    });
}
</script>
@endpush
