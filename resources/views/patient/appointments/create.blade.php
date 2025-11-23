@extends('patient.layouts.app')

@section('title', 'Book Appointment')
@section('page-title', 'Book New Appointment')

@section('content')
    <form method="POST" action="{{ route('patient.appointments.store') }}" id="appointmentForm">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-plus me-2"></i>
                            Appointment Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Clinic <span class="text-danger">*</span></label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" name="department_id" required>
                                    <option value="">Select Clinic</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('department_id', request('department')) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select class="form-select @error('doctor_id') is-invalid @enderror" 
                                        id="doctor_id" name="doctor_id" required disabled>
                                    <option value="">Select Doctor</option>
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Appointment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                       id="appointment_date" name="appointment_date" 
                                       value="{{ old('appointment_date') }}" 
                                       min="{{ date('Y-m-d') }}" required>
                                @error('appointment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">Appointment Time <span class="text-danger">*</span></label>
                                <select class="form-select @error('appointment_time') is-invalid @enderror" 
                                        id="appointment_time" name="appointment_time" required disabled>
                                    <option value="">Select Time Slot</option>
                                </select>
                                @error('appointment_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" name="priority">
                                    <option value="normal" {{ old('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="reason" class="form-label">Reason for Visit <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" 
                                          id="reason" name="reason" rows="4" 
                                          placeholder="Please describe your symptoms or reason for the appointment..." required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Any additional information you'd like to share...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Patient Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            Patient Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="{{ Auth::guard('patient')->user()->photo_url }}" alt="Profile" 
                                 class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                        </div>
                        <div class="text-center">
                            <h6>{{ Auth::guard('patient')->user()->full_name }}</h6>
                            <p class="text-muted mb-2">ID: {{ Auth::guard('patient')->user()->patient_id }}</p>
                            @if(Auth::guard('patient')->user()->phone)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-phone me-1"></i>
                                    {{ Auth::guard('patient')->user()->phone }}
                                </p>
                            @endif
                            @if(Auth::guard('patient')->user()->blood_group)
                                <span class="badge bg-info">{{ Auth::guard('patient')->user()->blood_group }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Selected Appointment Info -->
                <div class="card mb-4" id="appointmentSummary" style="display: none;">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            Appointment Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="summaryContent">
                            <!-- Summary will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2" id="submitBtn" disabled>
                            <i class="fas fa-calendar-plus me-2"></i>
                            Book Appointment
                        </button>
                        <a href="{{ route('patient.appointments.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department_id');
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const submitBtn = document.getElementById('submitBtn');
    const appointmentSummary = document.getElementById('appointmentSummary');
    const summaryContent = document.getElementById('summaryContent');

    // Create loading indicator
    function showLoading(element, text = 'Loading...') {
        element.innerHTML = `<option value="">${text}</option>`;
        element.disabled = true;
    }

    // Show error modal
    function showErrorModal(title, message) {
        const modalHtml = `
            <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${title}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i> Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('errorModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body and show
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modal = new bootstrap.Modal(document.getElementById('errorModal'));
        modal.show();
        
        // Remove modal from DOM when hidden
        document.getElementById('errorModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // Department change handler
    departmentSelect.addEventListener('change', function() {
        const departmentId = this.value;
        
        // Reset and disable dependent fields
        doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
        doctorSelect.disabled = !departmentId;
        resetTimeSlots();
        updateSubmitButton();
        
        if (departmentId) {
            showLoading(doctorSelect, 'Loading doctors...');
            
            // Fetch doctors for selected department
            fetch(`/patient/appointments/doctors/${departmentId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(doctors => {
                    doctorSelect.innerHTML = '<option value="">Select Doctor</option>';
                    
                    if (doctors.length === 0) {
                        doctorSelect.innerHTML = '<option value="">No doctors available</option>';
                        showErrorModal('No Doctors Available', 'There are currently no active doctors in this department. Please select a different department or try again later.');
                        return;
                    }
                    
                    doctors.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.id;
                        option.textContent = `${doctor.name} - ${doctor.specialization}`;
                        doctorSelect.appendChild(option);
                    });
                    
                    doctorSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching doctors:', error);
                    doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
                    showErrorModal('Error Loading Doctors', 'Unable to load doctors for the selected department. Please check your internet connection and try again.');
                });
        }
    });

    // Doctor and date change handlers
    [doctorSelect, dateInput].forEach(element => {
        element.addEventListener('change', loadTimeSlots);
    });

    // Time selection handler
    timeSelect.addEventListener('change', function() {
        updateSubmitButton();
        updateSummary();
    });

    function loadTimeSlots() {
        const doctorId = doctorSelect.value;
        const date = dateInput.value;
        
        resetTimeSlots();
        updateSubmitButton();
        
        if (doctorId && date) {
            showLoading(timeSelect, 'Loading time slots...');
            
            // Fetch available time slots
            fetch(`/patient/appointments/slots/${doctorId}?date=${date}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(slots => {
                    timeSelect.innerHTML = '<option value="">Select Time Slot</option>';
                    
                    if (slots.length === 0) {
                        timeSelect.innerHTML = '<option value="">No slots available</option>';
                        showErrorModal('No Time Slots Available', 'There are no available time slots for the selected doctor on this date. Please choose a different date or doctor.');
                        return;
                    }
                    
                    slots.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.time;
                        option.textContent = slot.display;
                        timeSelect.appendChild(option);
                    });
                    
                    timeSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching time slots:', error);
                    timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                    showErrorModal('Error Loading Time Slots', 'Unable to load available time slots. Please check your internet connection and try again.');
                });
        }
    }

    function resetTimeSlots() {
        timeSelect.innerHTML = '<option value="">Select Time Slot</option>';
        timeSelect.disabled = true;
        appointmentSummary.style.display = 'none';
    }

    function updateSubmitButton() {
        const isValid = departmentSelect.value && doctorSelect.value && 
                       dateInput.value && timeSelect.value;
        submitBtn.disabled = !isValid;
    }

    function updateSummary() {
        if (departmentSelect.value && doctorSelect.value && dateInput.value && timeSelect.value) {
            const departmentText = departmentSelect.options[departmentSelect.selectedIndex].text;
            const doctorText = doctorSelect.options[doctorSelect.selectedIndex].text;
            const timeText = timeSelect.options[timeSelect.selectedIndex].text;
            const dateObj = new Date(dateInput.value);
            const dateText = dateObj.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });

            summaryContent.innerHTML = `
                <div class="mb-2">
                    <strong>Department:</strong><br>
                    <small class="text-muted">${departmentText}</small>
                </div>
                <div class="mb-2">
                    <strong>Doctor:</strong><br>
                    <small class="text-muted">${doctorText}</small>
                </div>
                <div class="mb-2">
                    <strong>Date:</strong><br>
                    <small class="text-muted">${dateText}</small>
                </div>
                <div class="mb-2">
                    <strong>Time:</strong><br>
                    <small class="text-muted">${timeText}</small>
                </div>
            `;
            
            appointmentSummary.style.display = 'block';
        } else {
            appointmentSummary.style.display = 'none';
        }
    }

    // Initialize form state
    if (departmentSelect.value) {
        departmentSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
