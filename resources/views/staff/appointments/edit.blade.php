@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment')
@section('page-subtitle', 'Appointment #' . $appointment->appointment_number . ' - ' . $appointment->patient->first_name . ' ' . $appointment->patient->last_name)

@section('content')
<div class="fade-in-up">

    <!-- Status Alert -->
    @if($appointment->status !== 'pending')
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Note:</strong> This appointment has status "{{ ucfirst($appointment->status) }}". 
            Some changes may not be appropriate for non-pending appointments.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Current Status Card -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Current appointment status</h5>
        </div>
        <div class="doctor-card-body">
            <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-calendar-check me-1"></i>Status & schedule</p>
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-0">Status</label>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'confirmed' => 'success',
                            'completed' => 'primary',
                            'cancelled' => 'danger'
                        ];
                        $color = $statusColors[$appointment->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $color }} fs-6">{{ ucfirst($appointment->status) }}</span>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-0">Scheduled for</label>
                    <div class="fw-bold">{{ formatDate($appointment->appointment_date) }}</div>
                    <small class="text-muted">{{ $appointment->appointment_time }}</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-0">Doctor</label>
                    <div class="fw-bold">
                        @if($appointment->doctor)
                            {{ formatDoctorName($appointment->doctor->name) }}
                        @else
                            <span class="text-muted">Not assigned</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-0">Created</label>
                    <div class="fw-bold">{{ formatDate($appointment->created_at) }}</div>
                    <small class="text-muted">{{ $appointment->created_at->diffForHumans() }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-edit me-2 text-primary"></i>Edit appointment details</h5>
        </div>
        <div class="doctor-card-body">
            <form action="{{ route('staff.appointments.update', $appointment) }}" method="POST" id="appointmentEditForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Patient Information (Read-only in edit) -->
                    <div class="col-md-6">
                        <h6 class="text-primary font-weight-bold mb-3">
                            <i class="fas fa-user me-2"></i>Patient Information
                        </h6>
                        
                        <div class="form-group mb-3">
                            <label class="form-label">Patient</label>
                            <div class="form-control-plaintext border rounded p-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                        <small class="text-muted">{{ $appointment->patient->phone ?? 'No phone' }} | {{ $appointment->patient->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Patient cannot be changed in edit mode</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="appointment_type" class="form-label">Appointment Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('appointment_type') is-invalid @enderror" 
                                    id="appointment_type" name="appointment_type" required
                                    {{ $appointment->status === 'completed' ? 'disabled' : '' }}>
                                <option value="">Select Type</option>
                                <option value="consultation" {{ old('appointment_type', $appointment->type) === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                <option value="follow_up" {{ old('appointment_type', $appointment->type === 'followup' ? 'follow_up' : ($appointment->type === 'follow_up' ? 'follow_up' : '')) === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            </select>
                            @error('appointment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="reason" class="form-label">Reason for Visit</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason" rows="3" 
                                      placeholder="Brief description of the appointment reason..."
                                      {{ $appointment->status === 'completed' ? 'readonly' : '' }}>{{ old('reason', $appointment->reason) }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Doctor & Schedule -->
                    <div class="col-md-6">
                        <h6 class="text-primary font-weight-bold mb-3">
                            <i class="fas fa-user-md me-2"></i>Doctor & Schedule
                        </h6>
                        
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
                                        id="department_id" name="department_id" required
                                        {{ $appointment->status === 'completed' ? 'disabled' : '' }}>
                                    <option value="">Select Clinic</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('department_id', $appointment->department_id) == $department->id ? 'selected' : '' }}>
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
                                        id="doctor_id" name="doctor_id" required
                                        {{ $appointment->status === 'completed' ? 'disabled' : '' }}>
                                    <option value="">Select Doctor</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" 
                                                {{ old('doctor_id', $appointment->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                            {{ formatDoctorName($doctor->name) }} - {{ $doctor->specialization ?? 'General' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave empty to unassign doctor</small>
                            @endif
                            @error('doctor_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="appointment_date" class="form-label">Appointment Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control uk-date @error('appointment_date') is-invalid @enderror" 
                                           id="appointment_date" name="appointment_date" 
                                           value="{{ old('appointment_date') ? (old('appointment_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('appointment_date')) ? \Carbon\Carbon::parse(old('appointment_date'))->format('d/m/Y') : old('appointment_date')) : ($appointment->appointment_date ? $appointment->appointment_date->format('d/m/Y') : '') }}" 
                                           placeholder="dd/mm/yyyy"
                                           pattern="\d{2}/\d{2}/\d{4}"
                                           maxlength="10"
                                           data-min-date="{{ $appointment->status === 'pending' ? date('Y-m-d') : $appointment->appointment_date->format('Y-m-d') }}"
                                           required
                                           {{ in_array($appointment->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                                    <small class="text-muted">Format: dd/mm/yyyy (e.g., 15/01/2025)</small>
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(in_array($appointment->status, ['completed', 'cancelled']))
                                        <small class="text-muted d-block">Date cannot be changed for {{ $appointment->status }} appointments</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="appointment_time" class="form-label">Appointment Time <span class="text-danger">*</span></label>
                                    <select class="form-control @error('appointment_time') is-invalid @enderror" 
                                            id="appointment_time" name="appointment_time" required
                                            {{ in_array($appointment->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                                        @php
                                            $editTimeSelected = old('appointment_time');
                                            if ($editTimeSelected === null && $appointment->appointment_time) {
                                                $t = $appointment->appointment_time;
                                                $editTimeSelected = $t instanceof \Carbon\CarbonInterface
                                                    ? $t->format('H:i')
                                                    : substr((string) $t, 0, 5);
                                            }
                                        @endphp
                                        @include('staff.appointments.partials.time-slot-options', [
                                            'selectedTime' => $editTimeSelected,
                                            'incrementMinutes' => (int) old('estimated_duration', $appointment->estimated_duration ?? 30),
                                        ])
                                    </select>
                                    @error('appointment_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="consultation_type" class="form-label">Consultation Type</label>
                            <select class="form-control @error('consultation_type') is-invalid @enderror" id="consultation_type" name="consultation_type"
                                    {{ in_array($appointment->status, ['completed', 'cancelled']) ? 'disabled' : '' }}
                                    onchange="handleOnlineConsultationChange(this)">
                                @php
                                    $currentCt = old('consultation_type', $appointment->consultation_type ?? ($appointment->is_online ? 'online' : 'in_person'));
                                @endphp
                                <option value="in_person" {{ $currentCt === 'in_person' ? 'selected' : '' }}>In Person</option>
                                <option value="online" {{ $currentCt === 'online' ? 'selected' : '' }}>Online (Video)</option>
                                <option value="telephone" {{ $currentCt === 'telephone' ? 'selected' : '' }}>Telephone</option>
                            </select>
                            @error('consultation_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <script>
                        var wherebyAutoGenerate = @json($wherebyAutoGenerate ?? false);

                        function isManualMeetingLinkRequired() {
                            var consultationTypeSelect = document.getElementById('consultation_type');
                            if (!consultationTypeSelect || consultationTypeSelect.value !== 'online') {
                                return false;
                            }
                            if (!wherebyAutoGenerate) {
                                return true;
                            }
                            var platformSelect = document.getElementById('meeting_platform');
                            var platformValue = platformSelect ? platformSelect.value : '';
                            return platformValue !== '' && platformValue !== 'whereby';
                        }

                        function syncWherebyMeetingUi() {
                            var meetingLink = document.getElementById('meeting_link');
                            var notice = document.getElementById('whereby_auto_notice');
                            var requiredMark = document.getElementById('meeting_link_required_mark');
                            var helpText = document.getElementById('meeting_link_help');
                            var manualRequired = isManualMeetingLinkRequired();

                            if (meetingLink) {
                                meetingLink.required = manualRequired;
                                if (manualRequired) {
                                    meetingLink.setAttribute('required', 'required');
                                } else {
                                    meetingLink.removeAttribute('required');
                                    meetingLink.classList.remove('is-invalid');
                                }
                            }

                            if (notice) {
                                var consultationTypeSelect = document.getElementById('consultation_type');
                                var isOnline = consultationTypeSelect && consultationTypeSelect.value === 'online';
                                notice.style.display = (isOnline && wherebyAutoGenerate && !manualRequired) ? '' : 'none';
                            }

                            if (requiredMark) {
                                requiredMark.style.display = manualRequired ? '' : 'none';
                            }

                            if (helpText) {
                                helpText.textContent = manualRequired
                                    ? 'Required for online consultations'
                                    : 'Leave blank — ThanksDoc will create the Whereby room when you save.';
                            }
                        }

                        // Show meeting link row only when "Online (Video)" is selected.
                        function handleOnlineConsultationChange(control) {
                            var meetingRow = document.getElementById('meeting_link_row');
                            var meetingLink = document.getElementById('meeting_link');
                            var meetingPlatform = document.getElementById('meeting_platform');
                            if (!meetingRow) return;

                            var isOnline = control && (control.value === 'online' || (control.type === 'checkbox' && control.checked));
                            if (control && control.tagName === 'SELECT') {
                                isOnline = control.value === 'online';
                            }
                            
                            if (isOnline) {
                                // Force show - use multiple methods
                                meetingRow.style.display = 'block';
                                meetingRow.style.visibility = 'visible';
                                meetingRow.style.opacity = '1';
                                meetingRow.removeAttribute('style');
                                meetingRow.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                                syncWherebyMeetingUi();
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
                                syncWherebyMeetingUi();
                            }
                        }

                        // Update meeting link placeholder based on selected platform - make it globally accessible
                        window.updateMeetingLinkPlaceholder = function() {
                            var platformSelect = document.getElementById('meeting_platform');
                            var meetingLinkInput = document.getElementById('meeting_link');
                            
                            if (!platformSelect || !meetingLinkInput) {
                                // Retry if elements not ready
                                setTimeout(window.updateMeetingLinkPlaceholder, 100);
                                return;
                            }
                            
                            var platform = platformSelect.value;
                            var placeholders = {
                                'zoom': 'https://zoom.us/j/xxxxxxxxxx',
                                'google_meet': 'https://meet.google.com/xxx-xxxx-xxx',
                                'teams': 'https://teams.microsoft.com/l/meetup-join/xxx',
                                'whereby': 'https://subdomain.whereby.com/room-name',
                                'custom': 'https://your-platform.com/meeting-link',
                                '': 'Enter meeting link based on selected platform'
                            };
                            
                            var placeholder = placeholders[platform] || placeholders[''];
                            meetingLinkInput.setAttribute('placeholder', placeholder);
                            meetingLinkInput.placeholder = placeholder; // Also set property directly
                            
                            // Also update via jQuery if available
                            if (typeof $ !== 'undefined' && $('#meeting_link').length) {
                                $('#meeting_link').attr('placeholder', placeholder);
                            }
                        };
                        
                        // Initialize on page load
                        (function() {
                            var consultationTypeSelect = document.getElementById('consultation_type');
                            if (consultationTypeSelect) {
                                setTimeout(function() {
                                    handleOnlineConsultationChange(consultationTypeSelect);
                                }, 100);
                                consultationTypeSelect.addEventListener('change', function() {
                                    handleOnlineConsultationChange(this);
                                });
                            }
                            
                            // Setup placeholder update for meeting platform
                            var platformSelect = document.getElementById('meeting_platform');
                            if (platformSelect) {
                                // Remove any existing listeners to avoid duplicates
                                var newSelect = platformSelect.cloneNode(true);
                                platformSelect.parentNode.replaceChild(newSelect, platformSelect);
                                
                                // Add fresh event listener
                                newSelect.addEventListener('change', function() {
                                    window.updateMeetingLinkPlaceholder();
                                    syncWherebyMeetingUi();
                                });
                                
                                // Also add onchange attribute as backup
                                newSelect.setAttribute('onchange', 'window.updateMeetingLinkPlaceholder(); syncWherebyMeetingUi();');
                                
                                // Update placeholder on page load if platform is already selected
                                setTimeout(function() {
                                    window.updateMeetingLinkPlaceholder();
                                }, 200);
                            } else {
                                // Retry if element not ready
                                setTimeout(function() {
                                    var retrySelect = document.getElementById('meeting_platform');
                                    if (retrySelect) {
                                        retrySelect.addEventListener('change', function() {
                                            window.updateMeetingLinkPlaceholder();
                                            syncWherebyMeetingUi();
                                        });
                                        window.updateMeetingLinkPlaceholder();
                                        syncWherebyMeetingUi();
                                    }
                                }, 100);
                            }
                        })();
                        </script>
                        
                        @php
                            $showMeetingRow = (old('consultation_type', $appointment->consultation_type ?? ($appointment->is_online ? 'online' : 'in_person')) === 'online');
                            $selectedMeetingPlatform = old('meeting_platform', $appointment->meeting_platform ?? (($wherebyAutoGenerate ?? false) ? 'whereby' : ''));
                        @endphp
                        <div class="row" id="meeting_link_row" style="{{ $showMeetingRow ? '' : 'display: none;' }}">
                            @if($wherebyAutoGenerate ?? false)
                            <div class="col-12 mb-3" id="whereby_auto_notice" style="{{ $showMeetingRow ? '' : 'display: none;' }}">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-magic me-1"></i>
                                    Whereby is enabled. Leave the meeting link blank and ThanksDoc will create the video room when you save.
                                </div>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label for="meeting_platform" class="form-label">Meeting Platform</label>
                                <select class="form-control @error('meeting_platform') is-invalid @enderror" 
                                        id="meeting_platform" name="meeting_platform"
                                        {{ in_array($appointment->status, ['completed', 'cancelled']) ? 'disabled' : '' }}>
                                    <option value="">Select Platform</option>
                                    <option value="zoom" {{ $selectedMeetingPlatform == 'zoom' ? 'selected' : '' }}>Zoom</option>
                                    <option value="google_meet" {{ $selectedMeetingPlatform == 'google_meet' ? 'selected' : '' }}>Google Meet</option>
                                    <option value="teams" {{ $selectedMeetingPlatform == 'teams' ? 'selected' : '' }}>Microsoft Teams</option>
                                    <option value="whereby" {{ $selectedMeetingPlatform == 'whereby' ? 'selected' : '' }}>Whereby</option>
                                    <option value="custom" {{ $selectedMeetingPlatform == 'custom' ? 'selected' : '' }}>Custom Platform</option>
                                </select>
                                @error('meeting_platform')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="meeting_link" class="form-label">
                                    Meeting Link
                                    <span class="text-danger" id="meeting_link_required_mark">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="url" class="form-control @error('meeting_link') is-invalid @enderror" 
                                           id="meeting_link" name="meeting_link" 
                                           value="{{ old('meeting_link', $appointment->meeting_link) }}" 
                                           placeholder="Enter meeting link based on selected platform"
                                           {{ in_array($appointment->status, ['completed', 'cancelled']) ? 'readonly' : '' }}>
                                    <button type="button" class="btn btn-outline-secondary" id="copy_meeting_link" 
                                            title="Copy Meeting Link" style="{{ old('meeting_link', $appointment->meeting_link) ? '' : 'display: none;' }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                @error('meeting_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted" id="meeting_link_help">Required for online consultations</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Additional Information -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary font-weight-bold mb-3">
                            <i class="fas fa-info-circle me-2"></i>Additional Information
                        </h6>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="priority" class="form-label">Priority Level</label>
                            <select class="form-control @error('priority') is-invalid @enderror" 
                                    id="priority" name="priority">
                                <option value="normal" {{ old('priority', $appointment->priority ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority', $appointment->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority', $appointment->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="estimated_duration" class="form-label">Estimated Duration (minutes)</label>
                            <select class="form-control @error('estimated_duration') is-invalid @enderror" 
                                    id="estimated_duration" name="estimated_duration">
                                @include('staff.appointments.partials.duration-select-options', ['selectedDuration' => old('estimated_duration', $appointment->estimated_duration ?? '30')])
                            </select>
                            <small class="text-muted">5-minute increments, up to 2 hours.</small>
                            @error('estimated_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                @if(auth()->user()->role === 'doctor')
                                    <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                @endif
                                <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                @if(auth()->user()->role !== 'doctor')
                                    Only doctors can mark appointments as completed
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">Reason for booking &amp; appointment notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="The patient's reason for booking appears here. You may edit or add internal notes.">{{ old('notes', $appointment->notes) }}</textarea>
                            <small class="text-muted">Includes the patient&rsquo;s reason for booking from online booking. Status updates may append to this field.</small>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if(\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'exclude_from_consultation_report') && !empty($canManageConsultationReportExclusion))
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="exclude_from_consultation_report"
                                       name="exclude_from_consultation_report" value="1"
                                       {{ old('exclude_from_consultation_report', $appointment->exclude_from_consultation_report) ? 'checked' : '' }}>
                                <label class="form-check-label" for="exclude_from_consultation_report">
                                    Exclude from consultation report <span class="text-muted">(demo / training)</span>
                                </label>
                            </div>
                            <small class="text-muted">These appointments are omitted from Admin → Consultations Report.</small>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Edit History -->
                <div class="row">
                    <div class="col-12">
                        <div class="form-group mb-3">
                            <label for="edit_reason" class="form-label">Reason for Edit <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('edit_reason') is-invalid @enderror" 
                                      id="edit_reason" name="edit_reason" rows="2" 
                                      placeholder="Please provide a reason for this appointment modification..." required>{{ old('edit_reason') }}</textarea>
                            @error('edit_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This will be logged for audit purposes</small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                @if($appointment->status === 'pending')
                                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                        <i class="fas fa-trash me-1"></i>Cancel Appointment
                                    </button>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('staff.appointments.show', $appointment) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Cancel Changes
                                </a>
                                <button type="submit" class="btn btn-doctor-primary">
                                    <i class="fas fa-save me-1"></i>Update Appointment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedule Conflict Warning -->
    <div class="doctor-card border-warning mt-4" id="conflictWarning" style="display: none;">
        <div class="doctor-card-body">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-warning">Potential Schedule Conflict</h6>
                    <p class="mb-0 text-muted small" id="conflictMessage">
                        <!-- Conflict message will be populated by JavaScript -->
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Guidelines -->
    <div class="doctor-card border-info mt-4">
        <div class="doctor-card-body">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-info-circle text-info fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-info">Appointment Edit Guidelines</h6>
                    <ul class="mb-0 text-muted small">
                        <li><strong>Pending appointments:</strong> All fields can be modified</li>
                        <li><strong>Confirmed appointments:</strong> Limited modifications allowed</li>
                        <li><strong>Completed appointments:</strong> Only notes and status can be changed</li>
                        <li><strong>Cancelled appointments:</strong> Cannot be modified</li>
                        <li>Patient information cannot be changed - create a new appointment if needed</li>
                        <li>All changes are logged for audit purposes</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('staff.appointments.cancel', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action will cancel the appointment permanently.
                    </div>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" 
                                  rows="3" placeholder="Please provide a reason for cancelling this appointment..." required></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_patient" name="notify_patient" value="1" checked>
                        <label class="form-check-label" for="notify_patient">
                            Notify patient about cancellation
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Appointment</button>
                    <button type="submit" class="btn btn-danger">Cancel Appointment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
$(document).ready(function() {
    // Wait for Flatpickr to load
    if (typeof flatpickr === 'undefined') {
        console.error('Flatpickr failed to load');
    }

    const appointmentStatus = '{{ $appointment->status }}';
    
    // Disable fields based on appointment status
    if (appointmentStatus === 'cancelled') {
        $('#appointmentEditForm input, #appointmentEditForm select, #appointmentEditForm textarea').not('#edit_reason').prop('disabled', true);
        $('#appointmentEditForm button[type="submit"]').prop('disabled', true);
    }

    // Department filter for doctors
    $('#department_id').on('change', function() {
        const departmentId = $(this).val();
        const doctorSelect = $('#doctor_id');
        
        // Show all doctors initially
        doctorSelect.find('option').show();
        
        if (departmentId) {
            // In a real implementation, you'd filter doctors by department
            // For now, we'll keep all doctors visible
        }
    });

    // Check for scheduling conflicts
    function checkScheduleConflicts() {
        const doctorId = $('#doctor_id').val();
        const appointmentDate = $('#appointment_date').val();
        const appointmentTime = $('#appointment_time').val();
        
        if (doctorId && appointmentDate && appointmentTime) {
            // Simulate conflict check
            setTimeout(() => {
                const conflictExists = Math.random() < 0.2; // 20% chance of conflict
                
                if (conflictExists) {
                    $('#conflictMessage').text('The selected doctor may have another appointment around this time. Please verify the schedule.');
                    $('#conflictWarning').show();
                } else {
                    $('#conflictWarning').hide();
                }
            }, 500);
        } else {
            $('#conflictWarning').hide();
        }
    }

    // Check conflicts when relevant fields change
    $('#doctor_id, #appointment_date, #appointment_time').on('change', checkScheduleConflicts);

    // Form validation
    $('#appointmentEditForm').on('submit', function(e) {
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
        
        // Check appointment date logic
        const appointmentDate = new Date($('#appointment_date').val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (appointmentStatus === 'pending' && appointmentDate < today) {
            $('#appointment_date').addClass('is-invalid');
            alert('Appointment date cannot be in the past for pending appointments.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...').prop('disabled', true);
    });
    
    // Real-time validation
    $('input, select, textarea').on('blur', function() {
        if ($(this).prop('required') && !$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Auto-populate duration based on type
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
        }
    });

    // Status change warnings
    $('#status').on('change', function() {
        const newStatus = $(this).val();
        const currentStatus = appointmentStatus;
        
        if (currentStatus === 'completed' && newStatus !== 'completed') {
            if (!confirm('Are you sure you want to change the status of a completed appointment? This action should be done carefully.')) {
                $(this).val(currentStatus);
            }
        }
        
        if (newStatus === 'cancelled') {
            if (!confirm('Changing status to cancelled will effectively cancel this appointment. Continue?')) {
                $(this).val(currentStatus);
            }
        }
    });

    // Filter time slots based on selected date
    $('#appointment_date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        const timeSelect = $('#appointment_time');
        
        if (appointmentStatus === 'pending' && selectedDate.toDateString() === today.toDateString()) {
            const currentHour = today.getHours();
            const currentMinute = today.getMinutes();
            
            timeSelect.find('option').each(function() {
                const timeValue = $(this).val();
                if (timeValue) {
                    const [hour, minute] = timeValue.split(':').map(Number);
                    const timeInMinutes = hour * 60 + minute;
                    const currentTimeInMinutes = currentHour * 60 + currentMinute;
                    
                    if (timeInMinutes <= currentTimeInMinutes + 15) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                }
            });
        } else {
            timeSelect.find('option').prop('disabled', false);
        }
    });

    // Trigger date change to set initial time restrictions
    $('#appointment_date').trigger('change');

    // Toggle meeting link fields based on consultation type
    $('#consultation_type').on('change', function() {
        if ($(this).val() === 'online') {
            $('#meeting_link_row').slideDown();
        } else {
            $('#meeting_link_row').slideUp();
            $('#meeting_link').val('');
            $('#meeting_platform').val('');
        }
        if (typeof syncWherebyMeetingUi === 'function') {
            syncWherebyMeetingUi();
        }
    });

    if (typeof syncWherebyMeetingUi === 'function') {
        syncWherebyMeetingUi();
    }

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
    $('#appointmentEditForm').on('submit', function(e) {
        if (typeof isManualMeetingLinkRequired === 'function' && isManualMeetingLinkRequired() && !$('#meeting_link').val().trim()) {
            e.preventDefault();
            $('#meeting_link').addClass('is-invalid');
            alert('Meeting link is required for online (video) consultations.');
            return false;
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

    // Appointment Date UK format (dd/mm/yyyy) with Flatpickr calendar picker
    (function initAppointmentDatePicker() {
        const appointmentDateInput = document.getElementById('appointment_date');
        if (!appointmentDateInput) return;

        // Skip if readonly (completed/cancelled appointments)
        if (appointmentDateInput.readOnly) return;

        // Wait for Flatpickr to be available
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr library not loaded');
            return;
        }

        // Get min date from data attribute
        const minDateAttr = appointmentDateInput.getAttribute('data-min-date');
        const minDateValue = minDateAttr || 'today';

        // Initialize Flatpickr with UK format
        const appointmentPicker = flatpickr(appointmentDateInput, {
            dateFormat: "d/m/Y",
            altInput: false,
            altFormat: "d/m/Y",
            locale: {
                firstDayOfWeek: 1 // Monday
            },
            minDate: minDateValue,
            allowInput: true, // Allow manual typing
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                // Ensure format is dd/mm/yyyy
                if (dateStr && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const date = new Date(dateStr);
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear();
                    instance.input.value = dd + '/' + mm + '/' + yyyy;
                }
                // Trigger change event for time slot loading
                $(appointmentDateInput).trigger('change');
            }
        });
        
        // Convert dd/mm/yyyy to yyyy-mm-dd before form submission
        const form = document.getElementById('appointmentEditForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const dateValue = appointmentDateInput.value.trim();
                
                if (dateValue) {
                    // Check if it's in dd/mm/yyyy format
                    if (dateValue.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                        const parts = dateValue.split('/');
                        // Convert to yyyy-mm-dd format
                        const convertedDate = parts[2] + '-' + parts[1] + '-' + parts[0];
                        appointmentDateInput.value = convertedDate;
                    }
                }
            });
        }
    })();
});

// Cancel appointment function
function confirmDelete() {
    $('#cancelModal').modal('show');
}
</script>
@endpush
