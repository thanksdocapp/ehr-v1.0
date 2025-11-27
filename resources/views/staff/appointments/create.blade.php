@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Schedule New Appointment')
@section('page-title', 'Schedule New Appointment')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Schedule appointments for your patients' : 'Book appointments for patients with available doctors')

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

    <form action="{{ route('staff.appointments.store') }}" method="POST" id="appointmentForm">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Appointment Details -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-calendar-plus me-2"></i>Appointment Details</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="patient_id" class="form-label">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control @error('patient_id') is-invalid @enderror" 
                                            id="patient_id" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->phone ?? 'No phone' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        <a href="{{ route('staff.patients.create') }}" class="text-decoration-none">
                                            <i class="fas fa-plus"></i> Add new patient
                                        </a>
                                    </small>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="appointment_type" class="form-label">Appointment Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('appointment_type') is-invalid @enderror" 
                                            id="appointment_type" name="appointment_type" required>
                                        <option value="">Select Type</option>
                                        <option value="consultation" {{ old('appointment_type') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                        <option value="follow_up" {{ old('appointment_type') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                                        <option value="routine_checkup" {{ old('appointment_type') === 'routine_checkup' ? 'selected' : '' }}>Routine Checkup</option>
                                        <option value="emergency" {{ old('appointment_type') === 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    </select>
                                    @error('appointment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="reason" class="form-label">Reason for Visit</label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror" 
                                              id="reason" name="reason" rows="3" placeholder="Brief description of the appointment reason...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="department_id" class="form-label">Clinic @if(auth()->user()->role !== 'doctor' || !$currentDepartment)<span class="text-danger">*</span>@endif</label>
                                    @if(auth()->user()->role === 'doctor' && $currentDepartment)
                                        <input type="hidden" name="department_id" value="{{ $currentDepartment->id }}">
                                        <div class="form-control bg-light" style="min-height: 38px; padding-top: 8px;">
                                            <i class="fas fa-hospital-symbol text-primary me-2"></i>
                                            <strong>{{ $currentDepartment->name }}</strong>
                                            <span class="text-muted small ms-2">(Your Clinic)</span>
                                        </div>
                                    @else
                                        <select class="form-control @error('department_id') is-invalid @enderror" 
                                                id="department_id" name="department_id" required>
                                            <option value="">Select Clinic</option>
                                            @foreach($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="doctor_id" class="form-label">Doctor @if(auth()->user()->role !== 'doctor' || !$currentDoctor)<span class="text-danger">*</span>@endif</label>
                                    @if(auth()->user()->role === 'doctor' && $currentDoctor)
                                        <input type="hidden" name="doctor_id" value="{{ $currentDoctor->id }}">
                                        <div class="form-control bg-light" style="min-height: 38px; padding-top: 8px;">
                                            <i class="fas fa-user-md text-success me-2"></i>
                                            <strong>{{ formatDoctorName($currentDoctor->name) }}</strong>
                                            @if($currentDoctor->specialization)
                                                <span class="text-muted small ms-2">({{ $currentDoctor->specialization }})</span>
                                            @endif
                                            <span class="text-muted small ms-2">(You)</span>
                                        </div>
                                    @else
                                        <select class="form-control @error('doctor_id') is-invalid @enderror" 
                                                id="doctor_id" name="doctor_id" required>
                                            <option value="">Select Doctor</option>
                                            @foreach($doctors as $doctor)
                                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                    {{ formatDoctorName($doctor->name) }} - {{ $doctor->specialization ?? 'General' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Leave empty to assign later</small>
                                    @endif
                                    @error('doctor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="appointment_date" class="form-label">Appointment Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                                   id="appointment_date" name="appointment_date" 
                                                   value="{{ old('appointment_date', date('Y-m-d')) }}" 
                                                   min="{{ date('Y-m-d') }}" required>
                                            @error('appointment_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="appointment_time" class="form-label">Appointment Time <span class="text-danger">*</span></label>
                                            <select class="form-control @error('appointment_time') is-invalid @enderror" 
                                                    id="appointment_time" name="appointment_time" required>
                                                <option value="">Select Time</option>
                                                @for($hour = 8; $hour <= 17; $hour++)
                                                    @for($minute = 0; $minute < 60; $minute += 30)
                                                        @php
                                                            $time = sprintf('%02d:%02d', $hour, $minute);
                                                            $displayTime = date('g:i A', strtotime($time));
                                                        @endphp
                                                        <option value="{{ $time }}" {{ old('appointment_time') === $time ? 'selected' : '' }}>
                                                            {{ $displayTime }}
                                                        </option>
                                                    @endfor
                                                @endfor
                                            </select>
                                            @error('appointment_time')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_online" name="is_online" value="1" 
                                       {{ old('is_online') ? 'checked' : '' }}
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
                        })();
                        </script>
                        
                        <div class="row" id="meeting_link_row" style="{{ old('is_online') ? '' : 'display: none;' }}">
                            <div class="col-md-6 mb-3">
                                <label for="meeting_platform" class="form-label">Meeting Platform</label>
                                <select class="form-control @error('meeting_platform') is-invalid @enderror" 
                                        id="meeting_platform" name="meeting_platform">
                                    <option value="">Select Platform</option>
                                    <option value="zoom" {{ old('meeting_platform') == 'zoom' ? 'selected' : '' }}>Zoom</option>
                                    <option value="google_meet" {{ old('meeting_platform') == 'google_meet' ? 'selected' : '' }}>Google Meet</option>
                                    <option value="teams" {{ old('meeting_platform') == 'teams' ? 'selected' : '' }}>Microsoft Teams</option>
                                    <option value="whereby" {{ old('meeting_platform') == 'whereby' ? 'selected' : '' }}>Whereby</option>
                                    <option value="custom" {{ old('meeting_platform') == 'custom' ? 'selected' : '' }}>Custom Platform</option>
                                </select>
                                @error('meeting_platform')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="meeting_link" class="form-label">Meeting Link <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="url" class="form-control @error('meeting_link') is-invalid @enderror" 
                                           id="meeting_link" name="meeting_link" 
                                           value="{{ old('meeting_link') }}" 
                                           placeholder="Enter meeting link based on selected platform">
                                    <button type="button" class="btn btn-outline-secondary" id="copy_meeting_link" 
                                            title="Copy Meeting Link" style="display: none;">
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

                <!-- Additional Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Additional Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="priority" class="form-label">Priority Level</label>
                                    <select class="form-control @error('priority') is-invalid @enderror" 
                                            id="priority" name="priority">
                                        <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="estimated_duration" class="form-label">Estimated Duration (minutes)</label>
                                    <select class="form-control @error('estimated_duration') is-invalid @enderror" 
                                            id="estimated_duration" name="estimated_duration">
                                        <option value="30" {{ old('estimated_duration', '30') === '30' ? 'selected' : '' }}>30 minutes</option>
                                        <option value="45" {{ old('estimated_duration') === '45' ? 'selected' : '' }}>45 minutes</option>
                                        <option value="60" {{ old('estimated_duration') === '60' ? 'selected' : '' }}>1 hour</option>
                                        <option value="90" {{ old('estimated_duration') === '90' ? 'selected' : '' }}>1.5 hours</option>
                                        <option value="120" {{ old('estimated_duration') === '120' ? 'selected' : '' }}>2 hours</option>
                                    </select>
                                    @error('estimated_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">Staff Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Any additional notes for this appointment...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-calendar-plus me-1"></i>Schedule Appointment
                            </button>
                            <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Schedule Conflict Warning -->
                <div class="doctor-card border-warning mb-4" id="conflictWarning" style="display: none;">
                    <div class="doctor-card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-warning">Potential Schedule Conflict</h6>
                                <p class="mb-0 text-muted small" id="conflictMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="doctor-card border-info">
                    <div class="doctor-card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-info-circle text-info fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-info">Appointment Scheduling Guidelines</h6>
                                <ul class="mb-0 text-muted small">
                                    <li class="mb-1">Appointments can be scheduled from 8:00 AM to 5:30 PM</li>
                                    <li class="mb-1">Emergency appointments take priority over regular appointments</li>
                                    <li class="mb-1">Patients will receive confirmation via email/SMS if contact details are available</li>
                                    <li>You can assign a doctor now or leave it for later assignment</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Set default appointment date to today
    const today = new Date().toISOString().split('T')[0];
    $('#appointment_date').attr('min', today);
    if (!$('#appointment_date').val()) {
        $('#appointment_date').val(today);
    }

    // Department filter for doctors
    $('#department_id').on('change', function() {
        const departmentId = $(this).val();
        const doctorSelect = $('#doctor_id');
        
        // Show all doctors initially
        doctorSelect.find('option').show();
        
        if (departmentId) {
            // In a real implementation, you would filter doctors by department
            // For now, we'll keep all doctors visible
        }
    });

    // Check for scheduling conflicts
    function checkScheduleConflicts() {
        const doctorId = $('#doctor_id').val();
        const appointmentDate = $('#appointment_date').val();
        const appointmentTime = $('#appointment_time').val();
        
        $('#conflictWarning').hide();
        
        if (doctorId && appointmentDate && appointmentTime) {
            // Simulate conflict check (in production, this would be an AJAX call)
            setTimeout(() => {
                const conflictExists = Math.random() < 0.3; // 30% chance of conflict
                
                if (conflictExists) {
                    $('#conflictMessage').text('Dr. may have another appointment around this time. Please verify the schedule.');
                    $('#conflictWarning').show();
                }
            }, 500);
        }
    }

    // Check conflicts when relevant fields change
    $('#doctor_id, #appointment_date, #appointment_time').on('change', checkScheduleConflicts);

    // Form validation
    $('#appointmentForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Check appointment date is not in the past
        const appointmentDate = new Date($('#appointment_date').val());
        const now = new Date();
        now.setHours(0, 0, 0, 0);
        
        if (appointmentDate < now) {
            $('#appointment_date').addClass('is-invalid');
            alert('Appointment date cannot be in the past.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Scheduling...').prop('disabled', true);
    });
    
    // Real-time validation
    $('input, select, textarea').on('blur', function() {
        if ($(this).prop('required') && !$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Auto-populate duration based on appointment type
    $('#appointment_type').on('change', function() {
        const type = $(this).val();
        const durationSelect = $('#estimated_duration');
        
        switch(type) {
            case 'consultation':
                durationSelect.val('45');
                break;
            case 'follow_up':
                durationSelect.val('30');
                break;
            case 'routine_checkup':
                durationSelect.val('30');
                break;
            case 'emergency':
                durationSelect.val('60');
                $('#priority').val('urgent');
                break;
        }
    });

    // Toggle meeting link fields based on is_online checkbox - ensure checkbox state changes
    function toggleMeetingLink() {
        const checkbox = $('#is_online');
        const isChecked = checkbox.is(':checked') || checkbox.prop('checked');
        
        if (isChecked) {
            $('#meeting_link_row').slideDown();
            $('#meeting_link').prop('required', true);
        } else {
            $('#meeting_link_row').slideUp();
            $('#meeting_link').prop('required', false);
            $('#meeting_link').val('');
            $('#meeting_platform').val('');
        }
    }
    
    // Handle checkbox change
    $('#is_online').on('change', function() {
        toggleMeetingLink();
    });
    
    // Handle checkbox click directly - use setTimeout to ensure state is updated
    $('#is_online').on('click', function(e) {
        // Use setTimeout to check state after browser processes the click
        setTimeout(function() {
            toggleMeetingLink();
        }, 10);
    });
    
    // Handle label click - ensure it toggles the checkbox
    $('label[for="is_online"]').on('click', function(e) {
        // Don't prevent default - let label naturally toggle checkbox
        setTimeout(function() {
            toggleMeetingLink();
        }, 10);
    });
    
    // Initialize on page load
    toggleMeetingLink();

    // Show/hide copy button based on meeting link value
    $('#meeting_link').on('input', function() {
        if ($(this).val()) {
            $('#copy_meeting_link').show();
        } else {
            $('#copy_meeting_link').hide();
        }
    });

    // Copy meeting link function
    $('#copy_meeting_link').on('click', function() {
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
            }).catch(function(err) {
                // Fallback for older browsers
                $('#meeting_link').select();
                document.execCommand('copy');
                alert('Meeting link copied to clipboard!');
            });
        }
    });

    // Client-side validation for meeting link
    $('#appointmentForm').on('submit', function(e) {
        if ($('#is_online').is(':checked') && !$('#meeting_link').val().trim()) {
            e.preventDefault();
            $('#meeting_link').addClass('is-invalid');
            alert('Meeting link is required for online consultations.');
            return false;
        }
    });
    
    // Update meeting link placeholder based on selected platform
    $('#meeting_platform').on('change', function() {
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
    });
    
    // Trigger change on page load if platform is already selected
    if ($('#meeting_platform').val()) {
        $('#meeting_platform').trigger('change');
    }

    // Filter time slots based on selected date
    $('#appointment_date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        const timeSelect = $('#appointment_time');
        
        // If appointment is today, disable past time slots
        if (selectedDate.toDateString() === today.toDateString()) {
            const currentHour = today.getHours();
            const currentMinute = today.getMinutes();
            
            timeSelect.find('option').each(function() {
                const timeValue = $(this).val();
                if (timeValue) {
                    const [hour, minute] = timeValue.split(':').map(Number);
                    const timeInMinutes = hour * 60 + minute;
                    const currentTimeInMinutes = currentHour * 60 + currentMinute;
                    
                    if (timeInMinutes <= currentTimeInMinutes + 30) { // Add 30 min buffer
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                }
            });
        } else {
            // Enable all time slots for future dates
            timeSelect.find('option').prop('disabled', false);
        }
    }).trigger('change');

    // Auto-dismiss alerts after 30 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 30000);
});
</script>
@endpush
