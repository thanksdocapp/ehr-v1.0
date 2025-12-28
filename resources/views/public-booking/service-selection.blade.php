@extends('layouts.public-booking')

@section('title', 'Book Appointment')

@section('content')
    <!-- Header -->
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        <p>Select a doctor and service to continue</p>
    </div>

    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step active">
            <div class="step-circle">1</div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">2</div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <!-- Clinic/Doctor Info -->
    @if(isset($department))
    <div class="info-card">
        <h3>{{ $department->name }}</h3>
        @if($department->description)
        <p>{{ $department->description }}</p>
        @endif
    </div>
    @elseif(isset($doctor) && $doctors->count() == 1)
    <div class="info-card">
        <h3>{{ $doctor->full_name }}</h3>
        <p>{{ $doctor->specialization }}</p>
    </div>
    @endif

    <!-- Doctor and Service Selection Form -->
    <form id="service-form" method="POST" action="{{ route('public.booking.patient-details') }}">
        @csrf
        @if(isset($department))
        <input type="hidden" name="department_id" value="{{ $department->id }}">
        @endif

        <!-- Doctor Selection (Dropdown) -->
        <div class="form-card">
            <label class="form-label">Select Doctor <span class="text-danger">*</span></label>
            @if($doctors->isEmpty())
        <div class="empty-state">
                <i class="fas fa-user-md"></i>
                <p>No doctors available at this time.</p>
        </div>
        @else
            <select name="doctor_id" id="doctor-select" class="form-control form-control-lg" required>
                <option value="">Select a doctor...</option>
                @foreach($doctors as $doc)
                <option value="{{ $doc->id }}" {{ (isset($doctor) && $doctor->id == $doc->id) ? 'selected' : '' }}>
                    {{ $doc->full_name }} - {{ $doc->specialization ?? 'General Practitioner' }}
                </option>
                @endforeach
            </select>
                    @endif
        </div>

        <!-- Service Selection (Dropdown) - Shown when doctor is selected -->
        <div class="form-card" id="service-selection-card" style="display: {{ (isset($doctor) && $doctors->count() == 1) ? 'block' : 'none' }};">
            <label class="form-label">Select Service <span class="text-danger">*</span></label>
            <select name="service_id" id="service-select" class="form-control form-control-lg" required {{ (isset($doctor) && $doctors->count() == 1) ? '' : 'disabled' }}>
                <option value="">Select a service...</option>
            </select>
            <div id="service-details" class="mt-3" style="display: none;">
                <div class="summary-card">
                    <div class="summary-row">
                        <span class="summary-label">Duration:</span>
                        <span class="summary-value" id="service-duration">-</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Price:</span>
                        <span class="summary-value" id="service-price">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Selection - Shown when service is selected -->
        <div class="form-card" id="schedule-selection-card" style="display: none;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <label class="form-label mb-0">Select Date & Time <span class="text-danger">*</span></label>
                <div class="consultation-type-compact">
                    <select name="consultation_type" id="consultation-type-select" class="form-select form-select-sm consultation-type-select" required>
                        <option value="in_person">In-Person</option>
                        <option value="online">Online</option>
                    </select>
                </div>
            </div>
            
            <!-- Date Display (5 days) -->
            <div class="date-navigation mb-3">
                <div class="date-display" id="date-display"></div>
            </div>

            <!-- Time Slots Grid - Calendar Style -->
            <div id="time-slots-container">
                <div id="time-slots-calendar" class="time-slots-calendar"></div>
                <div id="no-slots-message" class="empty-message" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>No available slots on these dates. Please try different dates.
                </div>
            </div>

            <div id="loading-slots" class="loading-spinner" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading available slots...</p>
            </div>

            <input type="hidden" name="appointment_date" id="appointment-date" required>
            <input type="hidden" name="appointment_time" id="appointment-time" required>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg" id="continue-btn" disabled>
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('styles')
<style>
    .date-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .date-display {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .date-item {
        padding: 0.5rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 120px;
        text-align: center;
    }

    .date-item:hover {
        border-color: var(--booking-primary);
    }

    .date-item.selected {
        border-color: var(--booking-primary);
        background-color: var(--booking-primary);
        color: #ffffff;
    }

    .date-item .date-day {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .date-item .date-date {
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Calendar-style time slots */
    .time-slots-calendar {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1.5rem;
        margin-top: 1rem;
    }

    .date-column {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
    }

    .date-column-header {
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .date-column-slots {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .date-column .time-slot-btn {
        width: 100%;
        margin: 0;
    }

    .date-column .more-slots {
        margin-top: 0.5rem;
        padding: 0.5rem;
        text-align: center;
        color: var(--booking-primary);
        font-size: 0.875rem;
        cursor: pointer;
        border: 1px dashed #e2e8f0;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .date-column .more-slots:hover {
        border-color: var(--booking-primary);
        background-color: color-mix(in srgb, var(--booking-primary) 5%, white);
    }

    @media (max-width: 1200px) {
        .time-slots-calendar {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .time-slots-calendar {
            grid-template-columns: 1fr;
        }
    }

    /* Compact Consultation Type Selector */
    .consultation-type-compact {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .consultation-type-select {
        min-width: 140px;
        font-size: 0.875rem;
        padding: 0.375rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }

    .consultation-type-select:focus {
        border-color: var(--booking-primary, #007bff);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: 0;
    }

    .consultation-type-select:hover {
        border-color: #adb5bd;
    }

    @media (max-width: 576px) {
        .consultation-type-compact {
            width: 100%;
        }
        .consultation-type-select {
            width: 100%;
        }
        .d-flex.justify-content-between.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const doctorSelect = document.getElementById('doctor-select');
        const serviceSelectionCard = document.getElementById('service-selection-card');
        const scheduleSelectionCard = document.getElementById('schedule-selection-card');
        const serviceSelect = document.getElementById('service-select');
        const serviceDetails = document.getElementById('service-details');
        const continueBtn = document.getElementById('continue-btn');
        const form = document.getElementById('service-form');

        let selectedDoctorId = null;
        let selectedServiceId = null;
        let selectedDate = null;
        let selectedTime = null;
        let currentDates = [];
        let currentDateIndex = 0;

        // If single doctor is pre-selected (from doctor link) or only one doctor available, load services immediately
        @if(isset($doctor) && $doctors->count() == 1)
        selectedDoctorId = {{ $doctor->id }};
        doctorSelect.value = {{ $doctor->id }};
        loadDoctorServices(selectedDoctorId);
        @elseif($doctors->count() == 1)
        // Auto-select if only one doctor
        const singleDoctor = doctorSelect.options[1]; // First option after "Select..."
        if (singleDoctor) {
            selectedDoctorId = singleDoctor.value;
            doctorSelect.value = selectedDoctorId;
            loadDoctorServices(selectedDoctorId);
        }
        @endif

        // Doctor selection
        doctorSelect.addEventListener('change', function() {
            selectedDoctorId = this.value;
            if (selectedDoctorId) {
                loadDoctorServices(selectedDoctorId);
            } else {
                serviceSelectionCard.style.display = 'none';
                scheduleSelectionCard.style.display = 'none';
                continueBtn.disabled = true;
            }
        });

        // Service selection
        serviceSelect.addEventListener('change', function() {
            selectedServiceId = this.value;
            if (selectedServiceId) {
                updateServiceDetails();
                loadSchedule();
            } else {
                serviceDetails.style.display = 'none';
                scheduleSelectionCard.style.display = 'none';
                continueBtn.disabled = true;
            }
        });

        // Date navigation
        document.getElementById('prev-dates').addEventListener('click', function() {
            if (currentDateIndex > 0) {
                currentDateIndex--;
                renderDates();
                loadTimeSlotsForDate(currentDates[currentDateIndex]);
            }
        });

        document.getElementById('next-dates').addEventListener('click', function() {
            if (currentDateIndex < currentDates.length - 5) {
                currentDateIndex++;
                renderDates();
                loadTimeSlotsForDate(currentDates[currentDateIndex]);
            }
        });

        // Load doctor services
        function loadDoctorServices(doctorId) {
            serviceSelectionCard.style.display = 'block';
            serviceSelect.disabled = true;
            serviceSelect.innerHTML = '<option value="">Loading services...</option>';
            scheduleSelectionCard.style.display = 'none';
            continueBtn.disabled = true;

            fetch(`{{ url('/api/public/doctors') }}/${doctorId}/services`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                serviceSelect.innerHTML = '<option value="">Select a service...</option>';
                if (data.services && data.services.length > 0) {
                    data.services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = `${service.name} - £${parseFloat(service.price).toFixed(2)}`;
                        option.dataset.duration = service.duration;
                        option.dataset.price = service.price;
                        serviceSelect.appendChild(option);
                    });
                    serviceSelect.disabled = false;
                } else {
                    serviceSelect.innerHTML = '<option value="">No services available</option>';
                }
            })
            .catch(error => {
                console.error('Error loading services:', error);
                serviceSelect.innerHTML = '<option value="">Error loading services</option>';
            });
        }

        // Update service details
        function updateServiceDetails() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                document.getElementById('service-duration').textContent = selectedOption.dataset.duration + ' minutes';
                document.getElementById('service-price').textContent = '£' + parseFloat(selectedOption.dataset.price).toFixed(2);
                serviceDetails.style.display = 'block';
            }
        }

        // Load schedule
        function loadSchedule() {
            if (!selectedDoctorId || !selectedServiceId) return;

            scheduleSelectionCard.style.display = 'block';
            
            // Generate next 5 days
            currentDates = [];
            const today = new Date();
            for (let i = 0; i < 5; i++) {
                const date = new Date(today);
                date.setDate(today.getDate() + i);
                currentDates.push(date.toISOString().split('T')[0]);
            }
            currentDateIndex = 0;
            renderDates();
            // Load slots for all visible dates (5 days)
            loadTimeSlotsForDate(currentDates[0]);
        }

        // Render date navigation (5 days)
        function renderDates() {
            const dateDisplay = document.getElementById('date-display');
            dateDisplay.innerHTML = '';
            
            // Show all 5 days (no pagination needed since we only have 5 days)
            currentDates.forEach((dateStr, index) => {
                const date = new Date(dateStr);
                const dayNames = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                const dateItem = document.createElement('div');
                dateItem.className = 'date-item' + (index === 0 ? ' selected' : '');
                dateItem.dataset.date = dateStr;
                dateItem.innerHTML = `
                    <div class="date-day">${dayNames[date.getDay()]}</div>
                    <div class="date-date">${monthNames[date.getMonth()]} ${date.getDate()}</div>
                `;
                dateItem.addEventListener('click', function() {
                    document.querySelectorAll('.date-item').forEach(item => item.classList.remove('selected'));
                    this.classList.add('selected');
                    // Reload slots when date is clicked (to highlight selected date)
                    loadTimeSlotsForDate(this.dataset.date);
                });
                dateDisplay.appendChild(dateItem);
            });

            // Hide navigation buttons since we only show 5 days
            document.getElementById('prev-dates').style.display = 'none';
            document.getElementById('next-dates').style.display = 'none';
        }

        // Load time slots for all visible dates (5 days)
        function loadTimeSlotsForDate(date) {
            if (!selectedDoctorId || !selectedServiceId) return;

            selectedDate = date;
            document.getElementById('appointment-date').value = date;
            document.getElementById('appointment-time').value = '';
            selectedTime = null;
            continueBtn.disabled = true;

            const timeSlotsCalendar = document.getElementById('time-slots-calendar');
            const noSlotsMessage = document.getElementById('no-slots-message');
            const loadingSlots = document.getElementById('loading-slots');

            loadingSlots.style.display = 'block';
            timeSlotsCalendar.innerHTML = '';
            noSlotsMessage.style.display = 'none';

            // Load slots for all 5 dates
            const visibleDates = currentDates;
            const datePromises = visibleDates.map(dateStr => {
                return fetch(`{{ url('/api/public/doctors') }}/${selectedDoctorId}/slots?date=${dateStr}&service_id=${selectedServiceId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => ({ date: dateStr, slots: data.slots || [] }));
            });

            Promise.all(datePromises)
            .then(results => {
                loadingSlots.style.display = 'none';
                
                let hasAnySlots = false;
                results.forEach(({ date: dateStr, slots }) => {
                    const date = new Date(dateStr);
                    const dayNames = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
                    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    
                    const dateColumn = document.createElement('div');
                    dateColumn.className = 'date-column';
                    dateColumn.dataset.date = dateStr;
                    
                    const header = document.createElement('div');
                    header.className = 'date-column-header';
                    header.textContent = `${dayNames[date.getDay()]} ${monthNames[date.getMonth()]} ${date.getDate()}`;
                    dateColumn.appendChild(header);
                    
                    const slotsContainer = document.createElement('div');
                    slotsContainer.className = 'date-column-slots';
                    
                    if (slots && slots.length > 0) {
                        hasAnySlots = true;
                        // Show first 4 slots, then "more" button
                        const slotsToShow = slots.slice(0, 4);
                        slotsToShow.forEach(slot => {
                            const slotBtn = document.createElement('button');
                            slotBtn.type = 'button';
                            slotBtn.className = 'time-slot-btn' + (dateStr === selectedDate && slot.start === selectedTime ? ' selected' : '');
                            slotBtn.textContent = slot.display || slot.start;
                            slotBtn.dataset.time = slot.start;
                            slotBtn.dataset.date = dateStr;
                            slotBtn.addEventListener('click', function() {
                                document.querySelectorAll('.time-slot-btn').forEach(btn => {
                                    btn.classList.remove('selected');
                                });
                                this.classList.add('selected');
                                selectedDate = this.dataset.date;
                                selectedTime = this.dataset.time;
                                document.getElementById('appointment-date').value = selectedDate;
                                document.getElementById('appointment-time').value = selectedTime;
                                
                                // Update date selection
                                document.querySelectorAll('.date-item').forEach(item => {
                                    item.classList.remove('selected');
                                    if (item.dataset.date === selectedDate) {
                                        item.classList.add('selected');
                                    }
                                });
                                
                                continueBtn.disabled = false;
                            });
                            slotsContainer.appendChild(slotBtn);
                        });
                        
                        // Add "more" button if there are more slots
                        if (slots.length > 4) {
                            const moreBtn = document.createElement('div');
                            moreBtn.className = 'more-slots';
                            moreBtn.textContent = 'more';
                            moreBtn.addEventListener('click', function() {
                                // Show all remaining slots
                                slots.slice(4).forEach(slot => {
                                    const slotBtn = document.createElement('button');
                                    slotBtn.type = 'button';
                                    slotBtn.className = 'time-slot-btn';
                                    slotBtn.textContent = slot.display || slot.start;
                                    slotBtn.dataset.time = slot.start;
                                    slotBtn.dataset.date = dateStr;
                                    slotBtn.addEventListener('click', function() {
                                        document.querySelectorAll('.time-slot-btn').forEach(btn => {
                                            btn.classList.remove('selected');
                                        });
                                        this.classList.add('selected');
                                        selectedDate = this.dataset.date;
                                        selectedTime = this.dataset.time;
                                        document.getElementById('appointment-date').value = selectedDate;
                                        document.getElementById('appointment-time').value = selectedTime;
                                        continueBtn.disabled = false;
                                    });
                                    slotsContainer.insertBefore(slotBtn, moreBtn);
                                });
                                moreBtn.style.display = 'none';
                            });
                            slotsContainer.appendChild(moreBtn);
                        }
                    } else {
                        // No slots available for this date
                        const noSlots = document.createElement('div');
                        noSlots.className = 'time-slot-btn';
                        noSlots.style.opacity = '0.5';
                        noSlots.style.cursor = 'not-allowed';
                        noSlots.textContent = '-';
                        slotsContainer.appendChild(noSlots);
                    }
                    
                    dateColumn.appendChild(slotsContainer);
                    timeSlotsCalendar.appendChild(dateColumn);
                });
                
                if (!hasAnySlots) {
                    noSlotsMessage.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                loadingSlots.style.display = 'none';
                alert('Failed to load available time slots. Please try again.');
            });
        }

        // Form submission
        form.addEventListener('submit', function(e) {
            if (!selectedDoctorId || !selectedServiceId || !selectedDate || !selectedTime) {
                e.preventDefault();
                alert('Please complete all selections before continuing.');
                return false;
            }
        });
    });
</script>
@endsection
