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
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text"
                                               class="form-control"
                                               id="patientSearchInput"
                                               placeholder="Search patient by name or phone…"
                                               autocomplete="off">
                                        <button type="button" class="btn btn-outline-secondary" id="patientSearchClearBtn" style="display:none;">
                                            Clear
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mb-2" id="patientSearchMeta" style="display:none;"></small>
                                    <div id="patientSearchResults"
                                         class="list-group mb-2"
                                         style="display:none; max-height: 240px; overflow: auto;">
                                    </div>
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
                            </div>
                        </div>

                        <!-- Appointment Date / Duration / Time (full-width, below Appointment Type) -->
                        <div class="row">
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="estimated_duration" class="form-label">Estimated Duration <span class="text-danger">*</span></label>
                                    <select class="form-control @error('estimated_duration') is-invalid @enderror"
                                            id="estimated_duration" name="estimated_duration" required>
                                        <option value="15" {{ old('estimated_duration') === '15' ? 'selected' : '' }}>15 minutes</option>
                                        <option value="30" {{ old('estimated_duration', '30') === '30' ? 'selected' : '' }}>30 minutes</option>
                                        <option value="45" {{ old('estimated_duration') === '45' ? 'selected' : '' }}>45 minutes</option>
                                        <option value="60" {{ old('estimated_duration') === '60' ? 'selected' : '' }}>1 hour</option>
                                        <option value="90" {{ old('estimated_duration') === '90' ? 'selected' : '' }}>1.5 hours</option>
                                        <option value="120" {{ old('estimated_duration') === '120' ? 'selected' : '' }}>2 hours</option>
                                    </select>
                                    @error('estimated_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Duration affects available time ranges</small>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                                    <div id="timeSlotNotice" class="alert alert-warning mt-2 mb-0" style="display:none;">
                                        <div class="d-flex align-items-start justify-content-between gap-3">
                                            <div>
                                                <div class="fw-bold"><i class="fas fa-exclamation-triangle me-1"></i>Selected time isn’t available</div>
                                                <div id="timeSlotNoticeText" class="small mb-0">
                                                    This time is unavailable (past/closed). Please choose a later time or change the day.
                                                </div>
                                            </div>
                                            <button type="button" id="timeSlotTomorrowBtn" class="btn btn-sm btn-outline-warning flex-shrink-0">
                                                Set date to tomorrow
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason for Visit (full-width) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label for="reason" class="form-label">Reason for Visit</label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror"
                                              id="reason" name="reason" rows="3"
                                              placeholder="Brief description of the appointment reason...">{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                        // Simple function to toggle Whereby info panel visibility
                        function handleOnlineConsultationChange(checkbox) {
                            var meetingRow = document.getElementById('meeting_link_row');
                            if (!meetingRow) return;

                            var isChecked = checkbox && (checkbox.checked || checkbox.getAttribute('checked') !== null);

                            if (isChecked) {
                                meetingRow.style.display = 'block';
                            } else {
                                meetingRow.style.display = 'none';
                            }
                        }

                        // Initialize on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            var checkbox = document.getElementById('is_online');
                            if (checkbox) {
                                handleOnlineConsultationChange(checkbox);
                                checkbox.addEventListener('change', function() {
                                    handleOnlineConsultationChange(this);
                                });
                            }
                        });
                        </script>
                        
                        <div class="row" id="meeting_link_row" @if(!old('is_online')) style="display: none;" @endif>
                            <div class="col-12 mb-3">
                                <!-- Hidden field to set Whereby as the platform -->
                                <input type="hidden" name="meeting_platform" value="whereby">

                                <div class="alert alert-info mb-0" style="border-radius: 8px; border-left: 4px solid #6C63FF;">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <i class="fas fa-video fa-2x" style="color: #6C63FF;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1" style="color: #6C63FF;">
                                                <i class="fas fa-magic me-1"></i>Whereby Video Consultation
                                            </h6>
                                            <p class="mb-2 small">A secure Whereby meeting room will be automatically created when you schedule this appointment.</p>
                                            <ul class="mb-0 small" style="padding-left: 1.2rem;">
                                                <li><strong>Patient</strong> will receive a link to join the consultation</li>
                                                <li><strong>Doctor</strong> will receive a host link with meeting controls</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
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
    // ===== Patient search (vanilla JS) =====
    (function initPatientSearch() {
        const select = document.getElementById('patient_id');
        const input = document.getElementById('patientSearchInput');
        const clearBtn = document.getElementById('patientSearchClearBtn');
        const meta = document.getElementById('patientSearchMeta');
        const results = document.getElementById('patientSearchResults');
        if (!select || !input || !clearBtn || !meta) return;
        if (!results) return;

        // Cache options once (we won't mutate the <select> list; we just set its value on selection).
        const allPatients = Array.from(select.options)
          .slice(1)
          .filter(opt => opt && opt.value)
          .map(opt => ({
              value: opt.value,
              label: (opt.textContent || '').trim(),
              text: (opt.textContent || '').toLowerCase(),
          }));
        const totalPatients = allPatients.length;

        function setMeta(visibleCount, query) {
            if (!query) {
                meta.style.display = 'none';
                meta.textContent = '';
                return;
            }
            meta.style.display = 'block';
            meta.textContent = visibleCount > 0
                ? `Found ${visibleCount} of ${totalPatients} patients`
                : 'No patients match your search. Try a different name/phone or clear the search.';
        }

        function hideResults() {
            results.style.display = 'none';
            results.innerHTML = '';
        }

        function showResults(items, query) {
            results.innerHTML = '';
            if (!query) {
                hideResults();
                return;
            }

            if (!items.length) {
                hideResults();
                return;
            }

            const max = 20;
            for (const p of items.slice(0, max)) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.textContent = p.label;
                btn.addEventListener('click', () => {
                    select.value = p.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    input.value = p.label;
                    hideResults();
                    meta.style.display = 'none';
                    meta.textContent = '';
                });
                results.appendChild(btn);
            }

            results.style.display = 'block';
        }

        function render(queryRaw) {
            const query = (queryRaw || '').trim().toLowerCase();
            clearBtn.style.display = query ? 'inline-block' : 'none';

            if (!query) {
                setMeta(0, '');
                hideResults();
                return;
            }

            const matches = allPatients.filter(p => p.text.includes(query));
            setMeta(matches.length, query);
            showResults(matches, query);
        }

        // Small debounce to keep typing snappy even with large lists
        let t = null;
        input.addEventListener('input', () => {
            if (t) window.clearTimeout(t);
            t = window.setTimeout(() => render(input.value), 80);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            render('');
            input.focus();
        });

        // If there is an existing selection, reflect it in the search input for clarity.
        if (select.value) {
            const selected = allPatients.find(p => p.value === select.value);
            if (selected) {
                input.value = selected.label;
            }
        }

        // Hide results when clicking outside
        document.addEventListener('click', (e) => {
            if (!results.contains(e.target) && e.target !== input) {
                hideResults();
            }
        });

        render('');
    })();

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

    // Toggle Whereby info panel based on is_online checkbox
    function toggleMeetingLink() {
        const checkbox = $('#is_online');
        const isChecked = checkbox.is(':checked') || checkbox.prop('checked');

        if (isChecked) {
            $('#meeting_link_row').slideDown();
        } else {
            $('#meeting_link_row').slideUp();
        }
    }

    // Handle checkbox change
    $('#is_online').on('change', function() {
        toggleMeetingLink();
    });

    // Initialize on page load
    toggleMeetingLink();

    // ===== Appointment time slots (connect to Weekly Availability) =====
    // Source of truth: Public booking availability API (uses SlotAvailabilityService / doctor availability + exceptions)
    const $timeSelect = $('#appointment_time');
    const $dateInput = $('#appointment_date');
    const originalTimeOptions = $timeSelect.find('option').clone(true, true);

    function getDoctorIdForSlots() {
        const $doctorSelect = $('#doctor_id');
        if ($doctorSelect.length) return $doctorSelect.val();
        const $hidden = $('input[name="doctor_id"]');
        if ($hidden.length) return $hidden.val();
        return null;
    }

    function restoreStaticTimesFallback() {
        // Restore original static options
        $timeSelect.empty();
        originalTimeOptions.each(function() {
            $timeSelect.append($(this).clone());
        });
        applyTodayPastTimeDisabling();
    }

    function applyTodayPastTimeDisabling() {
        const dateVal = $dateInput.val();
        if (!dateVal) return;
        const selectedDate = new Date(dateVal);
        const today = new Date();

        // If appointment is today, disable past time slots
        if (selectedDate.toDateString() === today.toDateString()) {
            const currentHour = today.getHours();
            const currentMinute = today.getMinutes();
            const currentTimeInMinutes = currentHour * 60 + currentMinute;

            $timeSelect.find('option').each(function() {
                const timeValue = $(this).val();
                if (!timeValue) return;
                const [hour, minute] = timeValue.split(':').map(Number);
                const timeInMinutes = hour * 60 + minute;
                $(this).prop('disabled', timeInMinutes <= currentTimeInMinutes + 30); // 30 min buffer
            });
        } else {
            // Enable all time slots for future dates
            $timeSelect.find('option').prop('disabled', false);
        }

        // If all times are disabled for today, show "closed" guidance
        const enabledCount = $timeSelect.find('option[value!=""]').filter(function() { return !$(this).prop('disabled'); }).length;
        if (selectedDate.toDateString() === today.toDateString() && enabledCount === 0) {
            showTimeSlotNotice('Clinic hours for today are over. Please change the appointment date to tomorrow.');
        }
    }

    function populateAvailabilitySlots(slots) {
        const currentValue = $timeSelect.val();
        $timeSelect.empty();
        $timeSelect.append($('<option value="">Select Time</option>'));

        for (const slot of slots) {
            const start = slot.start || slot.time; // support both shapes
            const display = slot.display || start;
            if (!start) continue;
            $timeSelect.append($('<option></option>').attr('value', start).text(display));
        }

        // Keep selection if still present
        if (currentValue && $timeSelect.find(`option[value="${currentValue}"]`).length) {
            $timeSelect.val(currentValue);
        } else {
            $timeSelect.val('');
        }
    }

    async function loadAvailableTimeSlots() {
        const doctorId = getDoctorIdForSlots();
        const date = $dateInput.val();
        const duration = parseInt($('#estimated_duration').val(), 10) || 30;

        if (!doctorId || !date) {
            restoreStaticTimesFallback();
            hideTimeSlotNotice();
            return;
        }

        // Loading state
        $timeSelect.prop('disabled', true);
        const prevVal = $timeSelect.val();
        $timeSelect.empty().append($('<option value="">Loading available times…</option>'));

        try {
            const url = `/api/doctor/${encodeURIComponent(doctorId)}/available-slots?date=${encodeURIComponent(date)}&duration=${encodeURIComponent(duration)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data?.error || `Failed to load availability (${res.status})`);
            }

            const slots = Array.isArray(data?.slots) ? data.slots : [];

            // Apply a 30-min buffer client-side for today's date (matches previous UI behavior)
            const today = new Date();
            const selectedDate = new Date(date);
            const nowPlusBuffer = new Date(Date.now() + 30 * 60 * 1000);

            const filtered = slots.filter(s => {
                const start = s.start || s.time;
                if (!start) return false;
                if (selectedDate.toDateString() !== today.toDateString()) return true;
                const [hh, mm] = start.split(':').map(Number);
                const slotDt = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), hh, mm, 0, 0);
                return slotDt.getTime() > nowPlusBuffer.getTime();
            });

            populateAvailabilitySlots(filtered);

            if (filtered.length === 0) {
                showTimeSlotNotice('No available times for the selected day (based on weekly availability). Please change the day to tomorrow or another date.');
            } else {
                hideTimeSlotNotice();
            }

            // Restore previous value if still possible
            if (prevVal && $timeSelect.find(`option[value="${prevVal}"]`).length) {
                $timeSelect.val(prevVal);
            }
        } catch (e) {
            // Fallback to original hardcoded times to avoid breaking scheduling
            restoreStaticTimesFallback();
            showTimeSlotNotice('Could not load weekly availability right now. Using default times—please double-check the doctor schedule or choose tomorrow.');
        } finally {
            $timeSelect.prop('disabled', false);
        }
    }

    // Load slots when doctor/date changes
    $dateInput.on('change', loadAvailableTimeSlots);
    $('#doctor_id').on('change', loadAvailableTimeSlots);
    $('#estimated_duration').on('change', function() {
        // Duration affects the slot end time and available ranges
        loadAvailableTimeSlots();
    });
    // Also handle doctor-locked view (hidden input) - initial load
    setTimeout(loadAvailableTimeSlots, 0);

    function showTimeSlotNotice(message) {
        $('#timeSlotNoticeText').text(message);
        $('#timeSlotNotice').stop(true, true).fadeIn(150);
    }

    function hideTimeSlotNotice() {
        $('#timeSlotNotice').stop(true, true).fadeOut(150);
    }

    function setDateToTomorrow() {
        const d = new Date();
        d.setDate(d.getDate() + 1);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        $('#appointment_date').val(`${yyyy}-${mm}-${dd}`).trigger('change');
        $('#appointment_time').val('').trigger('change');
        hideTimeSlotNotice();
    }

    $('#timeSlotTomorrowBtn').on('click', function() {
        setDateToTomorrow();
    });

    // Also validate when time changes (e.g., user selects a disabled slot via keyboard)
    $('#appointment_time').on('change', function() {
        const opt = $(this).find('option:selected');
        if (opt.length && opt.val() && opt.prop('disabled')) {
            $(this).val('');
            showTimeSlotNotice('That time is unavailable. Please choose another time or change the day.');
            return;
        }
        hideTimeSlotNotice();
    });

    // Auto-dismiss alerts after 30 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 30000);
});
</script>
@endpush
