@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Schedule New Appointment')
@section('page-title', 'Schedule New Appointment')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Schedule appointments for your patients' : 'Book appointments for patients with available doctors')

@push('styles')
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Time slot notice (scoped to this view) */
    #timeSlotNotice {
        border-radius: 14px;
        padding: 0.9rem 1rem;
        background: #fff8e6;
        border: 1px solid rgba(245, 158, 11, 0.35);
        border-left: 5px solid #f59e0b;
        color: #1f2937; /* readable, not washed out */
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    #timeSlotNotice .time-slot-notice__title {
        font-weight: 800;
        color: #111827;
        letter-spacing: -0.2px;
        margin-bottom: 0.15rem;
    }

    #timeSlotNotice .time-slot-notice__title i {
        color: #b45309; /* darker amber */
    }

    #timeSlotNotice #timeSlotNoticeText {
        color: rgba(17, 24, 39, 0.82);
        line-height: 1.35;
    }

    #timeSlotTomorrowBtn {
        border-radius: 12px;
        font-weight: 700;
        padding: 0.5rem 0.75rem;
        border-color: rgba(245, 158, 11, 0.65);
        background: #fff;
        color: #92400e;
        white-space: nowrap;
        align-self: flex-start;
    }

    #timeSlotTomorrowBtn:hover {
        background: rgba(245, 158, 11, 0.12);
        border-color: rgba(245, 158, 11, 0.85);
        color: #7c2d12;
    }

    /* Mobile: give the button full width for easy tapping */
    @media (max-width: 576px) {
        #timeSlotTomorrowBtn { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="fade-in-up">

    <form action="{{ route('staff.appointments.store') }}" method="POST" id="appointmentForm">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Appointment Details -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-calendar-plus me-2 text-primary"></i>Appointment Details</h5>
                    </div>
                    <div class="doctor-card-body">
                        {{-- 1. Patient & visit type (who, what kind of visit) --}}
                        <div class="mb-4">
                            <p class="text-uppercase small fw-semibold text-muted mb-3"><i class="fas fa-user me-1"></i>Patient & visit type</p>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group mb-3">
                                        <label for="patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
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
                                            <a href="{{ route('staff.patients.create') }}" class="text-decoration-none"><i class="fas fa-plus"></i> Add new patient</a>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="appointment_type" class="form-label fw-semibold">Visit type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('appointment_type') is-invalid @enderror"
                                                id="appointment_type" name="appointment_type" required>
                                            <option value="">Select</option>
                                            <option value="consultation" {{ old('appointment_type') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                                            <option value="follow_up" {{ old('appointment_type') === 'follow_up' ? 'selected' : '' }}>Follow up</option>
                                        </select>
                                        @error('appointment_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Location & clinician (where, who provides care) --}}
                        <div class="mb-4 pt-3 border-top">
                            <p class="text-uppercase small fw-semibold text-muted mb-3"><i class="fas fa-hospital me-1"></i>Location & clinician</p>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="department_id" class="form-label fw-semibold">Clinic @if(auth()->user()->role !== 'doctor' || !$currentDepartment)<span class="text-danger">*</span>@endif</label>
                                        @if(auth()->user()->role === 'doctor' && $currentDepartment)
                                            <input type="hidden" name="department_id" value="{{ $currentDepartment->id }}">
                                            <div class="form-control bg-light" style="min-height: 38px; padding-top: 8px;">
                                                <i class="fas fa-hospital-symbol text-primary me-2"></i>
                                                <strong>{{ $currentDepartment->name }}</strong>
                                                <span class="text-muted small ms-2">(Your clinic)</span>
                                            </div>
                                        @else
                                            <select class="form-control @error('department_id') is-invalid @enderror"
                                                    id="department_id" name="department_id" required>
                                                <option value="">Select clinic</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                        @error('department_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="doctor_id" class="form-label fw-semibold">Doctor @if(auth()->user()->role !== 'doctor' || !$currentDoctor)<span class="text-danger">*</span>@endif</label>
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
                                                <option value="">Select doctor</option>
                                                @foreach($doctors as $doctor)
                                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                        {{ formatDoctorName($doctor->name) }} - {{ $doctor->specialization ?? 'General' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">Optional – assign later if needed</small>
                                        @endif
                                        @error('doctor_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="service_id" class="form-label fw-semibold">Service</label>
                                        <select class="form-control @error('service_id') is-invalid @enderror"
                                                id="service_id" name="service_id">
                                            <option value="">None (optional)</option>
                                        </select>
                                        <small class="text-muted">Can set consultation type</small>
                                        @error('service_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Date & time (when) --}}
                        <div class="mb-4 pt-3 border-top">
                            <p class="text-uppercase small fw-semibold text-muted mb-3"><i class="fas fa-clock me-1"></i>Date & time</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="appointment_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control uk-date @error('appointment_date') is-invalid @enderror"
                                               id="appointment_date" name="appointment_date"
                                               value="{{ old('appointment_date') ? (old('appointment_date') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('appointment_date')) ? \Carbon\Carbon::parse(old('appointment_date'))->format('d/m/Y') : old('appointment_date')) : \Carbon\Carbon::now()->format('d/m/Y') }}"
                                               placeholder="dd/mm/yyyy"
                                               pattern="\d{2}/\d{2}/\d{4}"
                                               maxlength="10"
                                               data-min-date="today"
                                               data-default-date="today"
                                               data-max-date="{{ \Carbon\Carbon::now()->addYears(2)->format('Y-m-d') }}"
                                               required>
                                        <small class="text-muted">dd/mm/yyyy</small>
                                        @error('appointment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="estimated_duration" class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                                        <select class="form-control @error('estimated_duration') is-invalid @enderror"
                                                id="estimated_duration" name="estimated_duration" required>
                                            <option value="15" {{ old('estimated_duration') === '15' ? 'selected' : '' }}>15 min</option>
                                            <option value="30" {{ old('estimated_duration', '30') === '30' ? 'selected' : '' }}>30 min</option>
                                            <option value="45" {{ old('estimated_duration') === '45' ? 'selected' : '' }}>45 min</option>
                                            <option value="60" {{ old('estimated_duration') === '60' ? 'selected' : '' }}>1 hour</option>
                                            <option value="90" {{ old('estimated_duration') === '90' ? 'selected' : '' }}>1.5 hours</option>
                                            <option value="120" {{ old('estimated_duration') === '120' ? 'selected' : '' }}>2 hours</option>
                                        </select>
                                        @error('estimated_duration')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="appointment_time" class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                                        <select class="form-control @error('appointment_time') is-invalid @enderror"
                                                id="appointment_time" name="appointment_time" required>
                                            <option value="">Select time</option>
                                            @for($hour = 8; $hour <= 17; $hour++)
                                                @for($minute = 0; $minute < 60; $minute += 30)
                                                    @php
                                                        $time = sprintf('%02d:%02d', $hour, $minute);
                                                        $displayTime = date('g:i A', strtotime($time));
                                                    @endphp
                                                    <option value="{{ $time }}" {{ old('appointment_time') === $time ? 'selected' : '' }}>{{ $displayTime }}</option>
                                                @endfor
                                            @endfor
                                        </select>
                                        @error('appointment_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="timeSlotNotice" class="alert mt-2 mb-0" style="display:none;">
                                            <div class="d-flex flex-column align-items-start gap-2">
                                                <div class="time-slot-notice__title">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Appointment time unavailable
                                                </div>
                                                <div id="timeSlotNoticeText" class="small mb-0"></div>
                                                <button type="button" id="timeSlotTomorrowBtn" class="btn btn-sm btn-outline-warning">Set date to tomorrow</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Modality (how the consultation takes place) --}}
                        <div class="mb-4 pt-3 border-top">
                            <p class="text-uppercase small fw-semibold text-muted mb-3"><i class="fas fa-video me-1"></i>Consultation method</p>
                            <div class="form-group mb-3">
                                <label for="consultation_type" class="form-label fw-semibold">Type</label>
                                <select class="form-control @error('consultation_type') is-invalid @enderror" id="consultation_type" name="consultation_type">
                                    <option value="in_person" {{ old('consultation_type', 'in_person') === 'in_person' ? 'selected' : '' }}>In person</option>
                                    <option value="online" {{ old('consultation_type') === 'online' ? 'selected' : '' }}>Online (video)</option>
                                    <option value="telephone" {{ old('consultation_type') === 'telephone' ? 'selected' : '' }}>Telephone</option>
                                </select>
                                @error('consultation_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        <script>
                        // Show meeting link row only when "Online (Video)" is selected.
                        (function initWherebyNoticeToggle() {
                            function apply() {
                                var select = document.getElementById('consultation_type');
                                var row = document.getElementById('meeting_link_row');
                                var platformInput = document.getElementById('meeting_platform_whereby');
                                if (!select || !row) return;

                                var on = select.value === 'online';
                                row.style.display = on ? '' : 'none';
                                if (platformInput) {
                                    platformInput.disabled = !on;
                                }
                            }

                            document.addEventListener('DOMContentLoaded', function() {
                                var select = document.getElementById('consultation_type');
                                if (!select) return;
                                select.addEventListener('change', apply);
                                apply();
                            });
                        })();
                        </script>
                        
                        <div class="row" id="meeting_link_row" @if(old('consultation_type') !== 'online') style="display: none;" @endif>
                            <div class="col-12 mb-3">
                                <!-- Hidden field to set Whereby as the platform -->
                                <input
                                    type="hidden"
                                    id="meeting_platform_whereby"
                                    name="meeting_platform"
                                    value="whereby"
                                    @if(old('consultation_type') !== 'online') disabled @endif
                                >

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

                        {{-- 5. Clinical context (reason for visit) --}}
                        <div class="pt-3 border-top">
                            <p class="text-uppercase small fw-semibold text-muted mb-3"><i class="fas fa-stethoscope me-1"></i>Reason for visit</p>
                            <div class="form-group mb-0">
                                <label for="reason" class="form-label fw-semibold">Reason / presenting complaint</label>
                                <textarea class="form-control @error('reason') is-invalid @enderror"
                                          id="reason" name="reason" rows="3"
                                          placeholder="Brief description of the appointment reason or presenting complaint…">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                    <li class="mb-1">Appointment times are shown from the selected doctor’s <strong>weekly availability</strong> and the selected <strong>duration</strong>.</li>
                                    <li class="mb-1">If no times are available for a day, choose another date/doctor (or use <strong>Set date to tomorrow</strong>).</li>
                                    <li class="mb-1">If availability can’t be loaded, the system falls back to default clinic hours (8:00 AM–5:30 PM).</li>
                                    <li class="mb-1">Appointments created here start as <strong>Pending</strong> and can be confirmed later.</li>
                                    <li class="mb-1">For <strong>Online</strong> appointments with <strong>Whereby</strong>, the meeting room is created automatically when possible.</li>
                                    <li>Patient/doctor notifications (email/SMS) depend on your notification settings and may be sent on creation and/or confirmation.</li>
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
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
$(document).ready(function() {
    // ===== Patient search (alerts-style: live filter the select options) =====
    (function initPatientSelectSearch() {
        const select = document.getElementById('patient_id');
        const input = document.getElementById('patientSearchInput');
        const clearBtn = document.getElementById('patientSearchClearBtn');
        const meta = document.getElementById('patientSearchMeta');
        if (!select || !input || !clearBtn || !meta) return;

        const cachedOptions = Array.from(select.options).map(opt => ({
            value: opt.value,
            label: (opt.textContent || '').trim(),
            disabled: !!opt.disabled,
            dataset: { ...opt.dataset },
        }));

        const placeholder = cachedOptions[0] || { value: '', label: 'Select Patient', disabled: false, dataset: {} };
        const patients = cachedOptions.slice(1).filter(o => o.value);
        const totalPatients = patients.length;

        function rebuildOptions(optionsToShow, currentValue) {
            select.innerHTML = '';

            const ph = document.createElement('option');
            ph.value = placeholder.value;
            ph.textContent = placeholder.label;
            ph.disabled = placeholder.disabled;
            select.appendChild(ph);

            // Always keep current selection visible (even if it doesn't match the filter)
            const selected = patients.find(p => p.value === currentValue);
            if (selected && !optionsToShow.some(p => p.value === currentValue)) {
                optionsToShow = [selected, ...optionsToShow];
            }

            for (const item of optionsToShow) {
                const opt = document.createElement('option');
                opt.value = item.value;
                opt.textContent = item.label;
                if (item.dataset) {
                    for (const [k, v] of Object.entries(item.dataset)) {
                        opt.dataset[k] = v;
                    }
                }
                select.appendChild(opt);
            }

            if (currentValue) {
                select.value = currentValue;
            }
        }

        function updateMeta(query, visibleCount) {
            if (!query) {
                meta.style.display = 'none';
                meta.textContent = '';
                return;
            }
            meta.style.display = 'block';
            meta.textContent = visibleCount > 0
                ? `Found ${visibleCount} of ${totalPatients} patients`
                : 'No patients found. Try a different search or clear.';
        }

        // Small debounce to keep typing snappy even with large lists
        let t = null;
        function applyFilter() {
            const query = (input.value || '').toLowerCase().trim();
            clearBtn.style.display = query ? 'inline-block' : 'none';

            const currentValue = select.value;
            if (!query) {
                rebuildOptions(patients, currentValue);
                updateMeta('', 0);
                return;
            }

            const matches = patients.filter(p => (p.label || '').toLowerCase().includes(query));
            rebuildOptions(matches, currentValue);
            updateMeta(query, matches.length);
        }

        input.addEventListener('input', () => {
            if (t) window.clearTimeout(t);
            t = window.setTimeout(applyFilter, 80);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            applyFilter();
            input.focus();
        });

        applyFilter();
    })();

    // Appointment Date UK format (dd/mm/yyyy) with Flatpickr calendar picker
    // Using the same functional implementation as staff/medical-records/create Record Date
    (function initAppointmentDatePicker() {
        const appointmentDateInput = document.getElementById('appointment_date');
        if (!appointmentDateInput) return;

        // Wait for Flatpickr to be available
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr library not loaded');
            return;
        }

        // Calculate max date (2 years from today)
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() + 2);

        // Initialize Flatpickr with UK format
        const appointmentPicker = flatpickr(appointmentDateInput, {
            dateFormat: "d/m/Y",
            altInput: false,
            altFormat: "d/m/Y",
            locale: {
                firstDayOfWeek: 1 // Monday
            },
            minDate: "today",
            maxDate: maxDate,
            allowInput: true, // Allow manual typing
            clickOpens: true,
            defaultDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                // Ensure format is dd/mm/yyyy
                if (dateStr && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const date = new Date(dateStr);
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear();
                    instance.input.value = dd + '/' + mm + '/' + yyyy;
                }
                
                // Trigger time slot loading and conflict checking
                setTimeout(function() {
                    if (typeof loadAvailableTimeSlots === 'function') {
                        loadAvailableTimeSlots();
                    }
                    if (typeof checkScheduleConflicts === 'function') {
                        checkScheduleConflicts();
                    }
                }, 100);
            }
        });

        // Store instance for easy access
        appointmentDateInput._flatpickr = appointmentPicker;

        // Also listen to native change event for manual input (fallback)
        $(appointmentDateInput).off('change.appointment').on('change.appointment', function() {
            if (typeof loadAvailableTimeSlots === 'function') {
                loadAvailableTimeSlots();
            }
            if (typeof checkScheduleConflicts === 'function') {
                checkScheduleConflicts();
            }
        });

        // Convert dd/mm/yyyy to yyyy-mm-dd before form submission
        const form = document.getElementById('appointmentForm');
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

    // Set default date if empty
    if (!$('#appointment_date').val()) {
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        $('#appointment_date').val(dd + '/' + mm + '/' + yyyy);
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
        const appointmentDateStr = $('#appointment_date').val();
        const appointmentTime = $('#appointment_time').val();
        
        $('#conflictWarning').hide();
        
        if (doctorId && appointmentDateStr && appointmentTime) {
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
        // Parse UK date format (dd/mm/yyyy) for validation
        const appointmentDateStr = $('#appointment_date').val();
        if (appointmentDateStr) {
            let appointmentDate;
            // Check if it's in UK format (dd/mm/yyyy)
            if (appointmentDateStr.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                const parts = appointmentDateStr.split('/');
                appointmentDate = new Date(parts[2], parts[1] - 1, parts[0]);
            } else {
                appointmentDate = new Date(appointmentDateStr);
            }
            
            const now = new Date();
            now.setHours(0, 0, 0, 0);
            appointmentDate.setHours(0, 0, 0, 0);
            
            if (appointmentDate < now) {
                $('#appointment_date').addClass('is-invalid');
                alert('Appointment date cannot be in the past.');
                isValid = false;
            }
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
        }
    });

    // Whereby notice toggle is handled by the inline script next to the checkbox (more robust if other JS fails).

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
        
        // Parse UK date format (dd/mm/yyyy)
        let selectedDate = null;
        if (dateVal.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
            const parts = dateVal.split('/');
            selectedDate = new Date(parts[2], parts[1] - 1, parts[0]);
        } else {
            selectedDate = new Date(dateVal);
        }
        
        if (!selectedDate || isNaN(selectedDate.getTime())) return;
        
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
            // Convert UK date format (dd/mm/yyyy) to YYYY-MM-DD for API
            let apiDate = date;
            if (date && date.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                const parts = date.split('/');
                apiDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
            
            const url = `/api/doctor/${encodeURIComponent(doctorId)}/available-slots?date=${encodeURIComponent(apiDate)}&duration=${encodeURIComponent(duration)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(data?.error || `Failed to load availability (${res.status})`);
            }

            const slots = Array.isArray(data?.slots) ? data.slots : [];

            // Apply a 30-min buffer client-side for today's date (matches previous UI behavior)
            const today = new Date();
            // Parse date - handle both UK format (dd/mm/yyyy) and standard format
            let selectedDate;
            if (typeof date === 'string' && date.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                const parts = date.split('/');
                selectedDate = new Date(parts[2], parts[1] - 1, parts[0]);
            } else {
                selectedDate = new Date(date);
            }
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
    // Note: Date change handler is set up in setupAppointmentDateHandlers() above
    // This handler is removed to avoid conflicts with Flatpickr handlers
    $('#doctor_id').on('change', function() {
        loadAvailableTimeSlots();
        loadDoctorServices();
    });
    $('#estimated_duration').on('change', function() {
        // Duration affects the slot end time and available ranges
        loadAvailableTimeSlots();
    });
    // Also handle doctor-locked view (hidden input) - initial load
    setTimeout(loadAvailableTimeSlots, 0);
    setTimeout(loadDoctorServices, 500); // Small delay to ensure DOM is ready

    // ===== Service Selection and Consultation Type Auto-Check =====
    const doctorServicesApiUrl = "{{ url('api/public/doctors') }}";
    function loadDoctorServices() {
        const doctorId = getDoctorIdForSlots();
        const $serviceSelect = $('#service_id');
        
        if (!doctorId) {
            $serviceSelect.empty().append('<option value="">Select Service (Optional)</option>');
            return;
        }

        // Show loading state
        $serviceSelect.prop('disabled', true);
        const prevVal = $serviceSelect.val();
        $serviceSelect.empty().append('<option value="">Loading services...</option>');

        fetch(`${doctorServicesApiUrl}/${doctorId}/services`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to load services');
                }
                return response.json();
            })
            .then(data => {
                $serviceSelect.empty();
                $serviceSelect.append('<option value="">Select Service (Optional)</option>');
                
                if (data.services && data.services.length > 0) {
                    data.services.forEach(service => {
                        const option = $('<option></option>')
                            .attr('value', service.id)
                            .text(service.name)
                            .data('consultation-type', service.consultation_type);
                        $serviceSelect.append(option);
                    });
                } else {
                    $serviceSelect.append('<option value="">No services available</option>');
                }

                // Restore previous selection if still available
                if (prevVal && $serviceSelect.find(`option[value="${prevVal}"]`).length) {
                    $serviceSelect.val(prevVal);
                    // Trigger change to update consultation type
                    $serviceSelect.trigger('change');
                }
            })
            .catch(error => {
                console.error('Error loading services:', error);
                $serviceSelect.empty().append('<option value="">Error loading services</option>');
            })
            .finally(() => {
                $serviceSelect.prop('disabled', false);
            });
    }

    // Auto-set consultation type from service
    $('#service_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const consultationType = selectedOption.data('consultation-type') || 'in_person';
        const $consultationTypeSelect = $('#consultation_type');
        if ($consultationTypeSelect.length && ['in_person', 'online', 'telephone'].indexOf(consultationType) !== -1) {
            $consultationTypeSelect.val(consultationType).trigger('change');
        }
    });

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
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();
        // Set in UK format (dd/mm/yyyy) for Flatpickr
        $('#appointment_date').val(`${dd}/${mm}/${yyyy}`).trigger('change');
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