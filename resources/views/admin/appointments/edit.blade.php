@extends('admin.layouts.app')

@section('title', 'Edit Appointment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('appointments.index') }}">Appointments</a></li>
    <li class="breadcrumb-item active">Edit Appointment</li>
@endsection

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.form-section-header {
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
    border-bottom: 2px solid #e2e8f0;
}

.form-section-header h4,
.form-section-header h5 {
    color: #1a202c;
    font-weight: 700;
}

.form-section-header i {
    color: #1a202c;
}

.form-section-header small {
    color: #4a5568;
}

.form-section-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}

.form-check-input:checked {
    background-color: #1cc88a;
    border-color: #1cc88a;
}

.input-group-text {
    background-color: #f8f9fc;
    border: 2px solid #e3e6f0;
    border-right: none;
    border-radius: 8px 0 0 8px;
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 8px 8px 0;
}

.input-group .form-control:focus {
    border-color: #1cc88a;
    box-shadow: none;
}

.input-group .form-control:focus + .input-group-text {
    border-color: #1cc88a;
}

textarea.form-control {
    resize: vertical;
}

.alert {
    border-radius: 8px;
    border: none;
}

@media (max-width: 768px) {
    .form-section-body {
        padding: 1rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Appointment</h5>
        <small class="text-muted">Update appointment details</small>
        <p class="page-subtitle text-muted">Update appointment information and details</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form id="editAppointmentForm" action="{{ contextRoute('appointments.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Basic Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h4>
                        <small class="opacity-75">Update basic appointment details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_number" class="form-label">
                                        <i class="fas fa-hashtag me-1"></i>Appointment Number
                                    </label>
                                    <input type="text" class="form-control" id="appointment_number" name="appointment_number" 
                                           value="{{ old('appointment_number', $appointment->appointment_number) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status *
                                    </label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="pending" {{ old('status', $appointment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ old('status', $appointment->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="completed" {{ old('status', $appointment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ old('status', $appointment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="rescheduled" {{ old('status', $appointment->status) == 'rescheduled' ? 'selected' : '' }}>Rescheduled</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Appointment Type *
                                    </label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="consultation" {{ old('type', $appointment->type) == 'consultation' ? 'selected' : '' }}>Consultation</option>
                                        <option value="followup" {{ old('type', $appointment->type) == 'followup' ? 'selected' : '' }}>Follow-up</option>
                                        <option value="emergency" {{ old('type', $appointment->type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                        <option value="checkup" {{ old('type', $appointment->type) == 'checkup' ? 'selected' : '' }}>Check-up</option>
                                        <option value="surgery" {{ old('type', $appointment->type) == 'surgery' ? 'selected' : '' }}>Surgery</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fee" class="form-label">
                                        <i class="fas fa-dollar-sign me-1"></i>Fee
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control @error('fee') is-invalid @enderror" 
                                               id="fee" name="fee" value="{{ old('fee', $appointment->fee) }}" 
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    @error('fee')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_date" class="form-label">
                                        <i class="fas fa-calendar-day me-1"></i>Date *
                                    </label>
                                    <input type="text" class="form-control @error('appointment_date') is-invalid @enderror"
                                           id="appointment_date" name="appointment_date" 
                                           value="{{ old('appointment_date', formatDate($appointment->appointment_date)) }}" 
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10" required>
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="appointment_time" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Time *
                                    </label>
                                    <input type="time" class="form-control @error('appointment_time') is-invalid @enderror"
                                           id="appointment_time" name="appointment_time" 
                                           value="{{ old('appointment_time', $appointment->appointment_time->format('H:i')) }}" required>
                                    @error('appointment_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_online" name="is_online" value="1" 
                                       {{ old('is_online', $appointment->is_online) ? 'checked' : '' }}
                                       onchange="handleOnlineConsultationChange(this)">
                                <label class="form-check-label" for="is_online" onclick="setTimeout(function(){handleOnlineConsultationChange(document.getElementById('is_online'));}, 10);">
                                    <i class="fas fa-video me-1"></i>Online Consultation
                                </label>
                            </div>
                        </div>
                        
                        <script>
                        // Inline function to handle Online Consultation - runs immediately
                        function handleOnlineConsultationChange(checkbox) {
                            var meetingRow = document.getElementById('meeting_link_row');
                            var meetingLink = document.getElementById('meeting_link');
                            var meetingPlatform = document.getElementById('meeting_platform');
                            
                            if (!meetingRow) return;
                            
                            var isChecked = checkbox && (checkbox.checked || checkbox.getAttribute('checked') !== null);
                            
                            if (isChecked) {
                                // Force show - use multiple methods
                                meetingRow.style.display = 'block';
                                meetingRow.style.visibility = 'visible';
                                meetingRow.style.opacity = '1';
                                meetingRow.removeAttribute('style');
                                meetingRow.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                                
                                // Set required field
                                if (meetingLink) {
                                    meetingLink.required = true;
                                    meetingLink.setAttribute('required', 'required');
                                }
                            } else {
                                // Force hide
                                meetingRow.style.display = 'none';
                                meetingRow.style.visibility = 'hidden';
                                meetingRow.removeAttribute('style');
                                meetingRow.setAttribute('style', 'display: none !important;');
                                
                                // Remove required and clear fields
                                if (meetingLink) {
                                    meetingLink.required = false;
                                    meetingLink.removeAttribute('required');
                                    meetingLink.value = '';
                                }
                                if (meetingPlatform) {
                                    meetingPlatform.value = '';
                                }
                            }
                        }
                        
                        // Update meeting link placeholder based on selected platform
                        function updateMeetingLinkPlaceholder() {
                            var platformSelect = document.getElementById('meeting_platform');
                            var meetingLinkInput = document.getElementById('meeting_link');
                            
                            if (!platformSelect || !meetingLinkInput) return;
                            
                            var platform = platformSelect.value;
                            var placeholders = {
                                'zoom': 'https://zoom.us/j/xxxxxxxxxx',
                                'google_meet': 'https://meet.google.com/xxx-xxxx-xxx',
                                'teams': 'https://teams.microsoft.com/l/meetup-join/xxx',
                                'whereby': 'https://subdomain.whereby.com/room-name',
                                'custom': 'https://your-platform.com/meeting-link',
                                '': 'Enter meeting link based on selected platform'
                            };
                            
                            meetingLinkInput.setAttribute('placeholder', placeholders[platform] || placeholders['']);
                        }
                        
                        // Initialize on page load
                        (function() {
                            var checkbox = document.getElementById('is_online');
                            if (checkbox) {
                                // Check initial state
                                setTimeout(function() {
                                    handleOnlineConsultationChange(checkbox);
                                }, 100);
                                
                                // Also add event listeners
                                checkbox.addEventListener('change', function() {
                                    handleOnlineConsultationChange(this);
                                });
                                checkbox.addEventListener('click', function() {
                                    setTimeout(function() {
                                        handleOnlineConsultationChange(checkbox);
                                    }, 10);
                                });
                            }
                            
                            // Setup placeholder update for meeting platform
                            var platformSelect = document.getElementById('meeting_platform');
                            if (platformSelect) {
                                platformSelect.addEventListener('change', updateMeetingLinkPlaceholder);
                                
                                // Update placeholder on page load if platform is already selected
                                setTimeout(function() {
                                    updateMeetingLinkPlaceholder();
                                }, 100);
                            }
                        })();
                        </script>
                        
                        <div class="row" id="meeting_link_row" style="{{ old('is_online', $appointment->is_online) ? '' : 'display: none;' }}">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="meeting_platform" class="form-label">Meeting Platform</label>
                                    <select class="form-control @error('meeting_platform') is-invalid @enderror" 
                                            id="meeting_platform" name="meeting_platform">
                                        <option value="">Select Platform</option>
                                        <option value="zoom" {{ old('meeting_platform', $appointment->meeting_platform) == 'zoom' ? 'selected' : '' }}>
                                            Zoom
                                        </option>
                                        <option value="google_meet" {{ old('meeting_platform', $appointment->meeting_platform) == 'google_meet' ? 'selected' : '' }}>
                                            Google Meet
                                        </option>
                                        <option value="teams" {{ old('meeting_platform', $appointment->meeting_platform) == 'teams' ? 'selected' : '' }}>
                                            Microsoft Teams
                                        </option>
                                        <option value="whereby" {{ old('meeting_platform', $appointment->meeting_platform) == 'whereby' ? 'selected' : '' }}>
                                            Whereby
                                        </option>
                                        <option value="custom" {{ old('meeting_platform', $appointment->meeting_platform) == 'custom' ? 'selected' : '' }}>
                                            Custom Platform
                                        </option>
                                    </select>
                                    @error('meeting_platform')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="meeting_link" class="form-label">Meeting Link <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="url" class="form-control @error('meeting_link') is-invalid @enderror" 
                                               id="meeting_link" name="meeting_link" 
                                               value="{{ old('meeting_link', $appointment->meeting_link) }}" 
                                               placeholder="Enter meeting link based on selected platform">
                                        <button type="button" class="btn btn-outline-secondary" id="copy_meeting_link" 
                                                title="Copy Meeting Link" 
                                                style="{{ old('meeting_link', $appointment->meeting_link) ? '' : 'display: none;' }}">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                    @error('meeting_link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Required for online consultations</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient & Doctor Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                        <small class="opacity-75">Update patient and doctor details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="patient_id" class="form-label">
                                        <i class="fas fa-user me-1"></i>Patient *
                                    </label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id', $appointment->patient_id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->full_name ?? ($patient->first_name . ' ' . $patient->last_name) }} 
                                                ({{ $patient->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department_id" class="form-label">
                                        <i class="fas fa-building me-1"></i>Clinic *
                                    </label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" 
                                            id="department_id" name="department_id" required>
                                        <option value="">Select Clinic</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id', $appointment->department_id) == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="doctor_id" class="form-label">
                                <i class="fas fa-user-md me-1"></i>Doctor *
                            </label>
                            <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                    id="doctor_id" name="doctor_id" required>
                                <option value="">Select Doctor</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" 
                                            data-department="{{ $doctor->department_id ?? '' }}"
                                            {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name ?? ($doctor->first_name . ' ' . $doctor->last_name) }} 
                                        @if($doctor->specialization)
                                            ({{ $doctor->specialization }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Appointment Details Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Appointment Details</h4>
                        <small class="opacity-75">Update appointment reason and details</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="reason" class="form-label">
                                <i class="fas fa-comments me-1"></i>Reason for Visit *
                            </label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason" rows="3" required>{{ old('reason', $appointment->reason) }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="symptoms" class="form-label">
                                <i class="fas fa-heartbeat me-1"></i>Symptoms
                            </label>
                            <textarea class="form-control @error('symptoms') is-invalid @enderror" 
                                      id="symptoms" name="symptoms" rows="3">{{ old('symptoms', $appointment->symptoms) }}</textarea>
                            @error('symptoms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="diagnosis" class="form-label">
                                        <i class="fas fa-diagnoses me-1"></i>Diagnosis
                                    </label>
                                    <textarea class="form-control @error('diagnosis') is-invalid @enderror" 
                                              id="diagnosis" name="diagnosis" rows="3">{{ old('diagnosis', $appointment->diagnosis) }}</textarea>
                                    @error('diagnosis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="prescription" class="form-label">
                                        <i class="fas fa-file-prescription me-1"></i>Prescription
                                    </label>
                                    <textarea class="form-control @error('prescription') is-invalid @enderror" 
                                              id="prescription" name="prescription" rows="3">{{ old('prescription', $appointment->prescription) }}</textarea>
                                    @error('prescription')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="follow_up_instructions" class="form-label">
                                <i class="fas fa-notes-medical me-1"></i>Follow-up Instructions
                            </label>
                            <textarea class="form-control @error('follow_up_instructions') is-invalid @enderror" 
                                      id="follow_up_instructions" name="follow_up_instructions" rows="3">{{ old('follow_up_instructions', $appointment->follow_up_instructions) }}</textarea>
                            @error('follow_up_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="next_appointment_date" class="form-label">
                                        <i class="fas fa-calendar-check me-1"></i>Next Appointment Date
                                    </label>
                                    <input type="text" class="form-control @error('next_appointment_date') is-invalid @enderror" 
                                           id="next_appointment_date" name="next_appointment_date" 
                                           value="{{ old('next_appointment_date', $appointment->next_appointment_date ? formatDate($appointment->next_appointment_date) : '') }}"
                                           placeholder="dd-mm-yyyy" 
                                           pattern="\d{2}-\d{2}-\d{4}" 
                                           maxlength="10">
                                    <small class="form-text text-muted">Format: dd-mm-yyyy (e.g., 15-01-2025)</small>
                                    @error('next_appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i>Notes
                                    </label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-doctor-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Appointment
                        </button>
                        <a href="{{ contextRoute('appointments.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Current Status</h6>
                <div class="mb-3">
                    <strong>Status:</strong> <span class="badge bg-{{ $appointment->status_badge ?? 'secondary' }} fs-6">{{ ucfirst($appointment->status) }}</span>
                </div>
                @if($appointment->check_in_time)
                    <div class="mb-3">
                        <strong>Check-in Time:</strong><br>
                        <span class="text-muted">{{ $appointment->check_in_time->format('M j, Y h:i A') }}</span>
                    </div>
                @endif
                @if($appointment->check_out_time)
                    <div class="mb-3">
                        <strong>Check-out Time:</strong><br>
                        <span class="text-muted">{{ $appointment->check_out_time->format('M j, Y h:i A') }}</span>
                    </div>
                @endif
                <div class="mb-3">
                    <strong>Created:</strong><br>
                    <span class="text-muted">{{ $appointment->created_at->format('M j, Y h:i A') }}</span>
                </div>
                <div class="mb-3">
                    <strong>Last Updated:</strong><br>
                    <span class="text-muted">{{ $appointment->updated_at->format('M j, Y h:i A') }}</span>
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-edit me-2"></i>Appointment Update Guidelines</h6>
                <ul>
                    <li>Fill in all required fields marked with *</li>
                    <li>Update status based on appointment progress</li>
                    <li>Provide clear reason for any changes</li>
                    <li>Add diagnosis and prescription after consultation</li>
                    <li>Set next appointment date if needed</li>
                    <li>Update meeting link for online consultations</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-calendar-alt me-2"></i>Status Guidelines</h6>
                <ul>
                    <li><strong>Pending:</strong> Appointment requested but not confirmed</li>
                    <li><strong>Confirmed:</strong> Appointment confirmed and scheduled</li>
                    <li><strong>Completed:</strong> Appointment finished successfully</li>
                    <li><strong>Cancelled:</strong> Appointment cancelled by patient/staff</li>
                    <li><strong>Rescheduled:</strong> Appointment moved to different time</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Privacy & Security</h6>
                <ul>
                    <li><strong>Data Protection:</strong> All information is securely stored</li>
                    <li><strong>Access Control:</strong> Only authorized staff can access</li>
                    <li><strong>Audit Trail:</strong> All changes are tracked</li>
                    <li><strong>Compliance:</strong> GDPR compliant system</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('appointments.show', $appointment->id) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-eye me-1"></i>View Details
                    </a>
                    <a href="{{ contextRoute('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Appointments List
                    </a>
                    <a href="{{ contextRoute('patients.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-users me-1"></i>Manage Patients
                    </a>
                    <a href="{{ contextRoute('doctors.index') }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-user-md me-1"></i>Manage Doctors
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Date input mask for dd-mm-yyyy format
    $('#appointment_date, #next_appointment_date').on('input', function() {
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
    $('form').on('submit', function() {
        $('#appointment_date, #next_appointment_date').each(function() {
            const dateStr = $(this).val();
            if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                const parts = dateStr.split('-');
                const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
                $(this).val(yyyyMmDd);
            }
        });
    });

    // Handle online consultation toggle
    $('#is_online').change(function() {
        if (this.checked) {
            $('#meeting_link_row').show();
            $('#meeting_link').prop('required', true);
        } else {
            $('#meeting_link_row').hide();
            $('#meeting_link').prop('required', false);
            $('#meeting_link').val('');
            $('#meeting_platform').val('');
        }
    });

    // Copy meeting link to clipboard
    $('#copy_meeting_link').click(function() {
        const meetingLink = $('#meeting_link').val();
        if (meetingLink) {
            navigator.clipboard.writeText(meetingLink).then(function() {
                const btn = $('#copy_meeting_link');
                const originalHtml = btn.html();
                btn.html('<i class="fas fa-check"></i>');
                btn.removeClass('btn-outline-secondary').addClass('btn-success');
                setTimeout(function() {
                    btn.html(originalHtml);
                    btn.removeClass('btn-success').addClass('btn-outline-secondary');
                }, 2000);
            });
        }
    });

    // Show/hide copy button based on meeting link input
    $('#meeting_link').on('input', function() {
        if ($(this).val()) {
            $('#copy_meeting_link').show();
        } else {
            $('#copy_meeting_link').hide();
        }
    });
    
    // Update meeting link placeholder based on selected platform
    $('#meeting_platform').off('change.placeholder').on('change.placeholder', function() {
        // Call the global function if available, otherwise use jQuery
        if (typeof updateMeetingLinkPlaceholder === 'function') {
            updateMeetingLinkPlaceholder();
        } else {
            const platform = $(this).val();
            const meetingLinkInput = $('#meeting_link');
            const placeholders = {
                'zoom': 'https://zoom.us/j/xxxxxxxxxx',
                'google_meet': 'https://meet.google.com/xxx-xxxx-xxx',
                'teams': 'https://teams.microsoft.com/l/meetup-join/xxx',
                'whereby': 'https://subdomain.whereby.com/room-name',
                'custom': 'https://your-platform.com/meeting-link',
                '': 'Enter meeting link based on selected platform'
            };
            meetingLinkInput.attr('placeholder', placeholders[platform] || placeholders['']);
        }
    });
    
    // Trigger change on page load if platform is already selected
    setTimeout(function() {
        if ($('#meeting_platform').val()) {
            $('#meeting_platform').trigger('change.placeholder');
        }
        // Also call the global function
        if (typeof updateMeetingLinkPlaceholder === 'function') {
            updateMeetingLinkPlaceholder();
        }
    }, 300);

    // Handle department change to filter doctors
    $('#department_id').change(function() {
        const selectedDepartment = $(this).val();
        const doctorOptions = $('#doctor_id option');
        
        doctorOptions.each(function() {
            const option = $(this);
            if (option.val() === '') {
                option.show();
                return;
            }
            
            const doctorDepartment = option.data('department');
            if (selectedDepartment === '' || doctorDepartment == selectedDepartment) {
                option.show();
            } else {
                option.hide();
            }
        });
        
        // Reset doctor selection if current selection is not valid
        const currentDoctorOption = $('#doctor_id option:selected');
        if (currentDoctorOption.length && currentDoctorOption.is(':hidden')) {
            $('#doctor_id').val('');
        }
    });

    // Trigger department change on page load to filter doctors
    $('#department_id').trigger('change');

    // Date validation
    $('#appointment_date').change(function() {
        const appointmentDate = $(this).val();
        const nextAppointmentDate = $('#next_appointment_date');
        
        if (nextAppointmentDate.val() && appointmentDate && new Date(nextAppointmentDate.val()) <= new Date(appointmentDate)) {
            nextAppointmentDate.val('');
        }
        nextAppointmentDate.attr('min', appointmentDate);
    });

    // Set initial minimum date for next appointment
    if ($('#appointment_date').val()) {
        $('#next_appointment_date').attr('min', $('#appointment_date').val());
    }

    // Form validation
    $('#editAppointmentForm').submit(function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['status', 'type', 'appointment_date', 'appointment_time', 'patient_id', 'department_id', 'doctor_id', 'reason'];
        
        requiredFields.forEach(function(field) {
            const input = $('#' + field);
            if (!input.val()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        // Check meeting link if online consultation
        if ($('#is_online').is(':checked') && !$('#meeting_link').val()) {
            $('#meeting_link').addClass('is-invalid');
            isValid = false;
        } else {
            $('#meeting_link').removeClass('is-invalid');
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush
