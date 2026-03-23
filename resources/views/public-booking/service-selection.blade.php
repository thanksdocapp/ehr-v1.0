@extends('layouts.public-booking')

@section('title', 'Book Appointment')

@section('content')
@if(empty($bookingDob))
    @include('public-booking.partials.booking-dob-step', [
        'dobIntro' => 'Enter the patient\'s date of birth first. You will then see services available for this age.',
    ])
@else
    @if(session('warning'))
    <div class="alert alert-warning border-0 mb-3">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger border-0 mb-3">{{ session('error') }}</div>
    @endif
    <!-- Header -->
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        <p>Select a doctor and service to continue</p>
        <form method="POST" action="{{ route('public.booking.clear-dob') }}" class="mt-2 mb-0">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-muted p-0">Change date of birth</button>
        </form>
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
            <label class="form-label mb-2">Select Doctor <span class="text-danger">*</span></label>
            @if($doctors->isEmpty())
        <div class="empty-state">
                <i class="fas fa-user-md"></i>
                <p>No doctors available at this time.</p>
        </div>
        @else
            <select name="doctor_id" id="doctor-select" class="form-control form-control-sm" required>
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
            <div class="service-selection-row">
                <div class="service-dropdown-col">
                    <select name="service_id" id="service-select" class="form-control form-control-sm" required {{ (isset($doctor) && $doctors->count() == 1) || isset($service) ? '' : 'disabled' }}>
                        <option value="">Select a service...</option>
                        @if(isset($service))
                        @php
                            $servicePrice = $service->getPriceForDoctor($doctor->id ?? 0) ?? $service->default_price ?? 0;
                            $serviceDuration = $service->getDurationForDoctor($doctor->id ?? 0) ?? $service->default_duration_minutes ?? 60;
                            $serviceDescription = $service->description ?? '';
                            $serviceConsultationType = $service->getConsultationTypeForDoctor($doctor->id ?? 0);
                        @endphp
                        <option value="{{ $service->id }}" 
                                selected 
                                data-duration="{{ $serviceDuration }}"
                                data-price="{{ $servicePrice }}"
                                data-description="{{ $serviceDescription }}"
                                data-consultation-type="{{ $serviceConsultationType }}">
                            {{ $service->name }} - £{{ number_format($servicePrice, 2) }}
                        </option>
                        @endif
                    </select>
                </div>
                <div class="service-details-col" id="service-details" style="display: none;">
                    <div class="summary-card-compact">
                        <div class="summary-description-compact" id="service-description"></div>
                        <div class="summary-row-compact">
                            <span class="summary-label-compact">Consultation Type:</span>
                            <span class="summary-value-compact" id="service-consultation-type">-</span>
                        </div>
                        <div class="summary-row-compact">
                            <span class="summary-label-compact">Duration:</span>
                            <span class="summary-value-compact" id="service-duration">-</span>
                        </div>
                        <div class="summary-row-compact">
                            <span class="summary-label-compact">Price:</span>
                            <span class="summary-value-compact" id="service-price">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="consultation_type" id="consultation-type-input" value="in_person">
        </div>

        <!-- Schedule Selection - Shown when service is selected -->
        <div class="form-card" id="schedule-selection-card" style="display: none;">
            <label class="form-label">Select Date & Time <span class="text-danger">*</span></label>
            
            <!-- Date Display (5 days) -->
            <div class="date-navigation mb-2">
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
        padding: 0.375rem 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 100px;
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
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .date-item .date-date {
        font-size: 0.8rem;
        margin-top: 0.2rem;
    }

    /* Calendar-style time slots - Compact */
    .time-slots-calendar {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .date-column {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.625rem;
    }

    .date-column-header {
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.5rem;
        padding-bottom: 0.375rem;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.8125rem;
    }

    .date-column-slots {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .date-column .time-slot-btn {
        width: 100%;
        margin: 0;
        padding: 0.375rem 0.5rem;
        font-size: 0.8125rem;
    }

    .date-column .more-slots {
        margin-top: 0.375rem;
        padding: 0.375rem;
        text-align: center;
        color: var(--booking-primary);
        font-size: 0.75rem;
        cursor: pointer;
        border: 1px dashed #e2e8f0;
        border-radius: 4px;
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
        .date-navigation { flex-wrap: wrap; gap: 0.5rem; }
        .date-display { gap: 0.5rem; }
        .date-item { min-width: 72px; padding: 0.5rem; min-height: 44px; }
        .date-item .date-day { font-size: 0.65rem; }
        .date-item .date-date { font-size: 0.75rem; }
        .time-slots-calendar { grid-template-columns: 1fr; gap: 1rem; }
        .date-column .time-slot-btn { min-height: 44px; padding: 0.5rem; }
    }

    /* Compact Service Selection Row */
    .service-selection-row {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .service-dropdown-col {
        flex: 1;
        min-width: 0;
    }

    .service-details-col {
        flex: 1;
        min-width: 0;
    }

    .summary-card-compact {
        background: #f8f9fa;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.5rem;
    }

    .summary-row-compact {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.25rem;
    }

    .summary-row-compact:last-child {
        margin-bottom: 0;
    }

    .summary-label-compact {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
    }

    .summary-value-compact {
        font-size: 0.8125rem;
        color: #1a202c;
        font-weight: 600;
    }

    .summary-description-compact {
        font-size: 0.75rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
        line-height: 1.4;
    }

    @media (max-width: 768px) {
        .service-selection-row {
            flex-direction: column;
            gap: 0.5rem;
        }
        .service-dropdown-col,
        .service-details-col {
            width: 100%;
        }
    }

    /* Compact Doctor Selection Card */
    .form-card:first-of-type {
        padding: 1rem 1.5rem;
    }

    .form-card:first-of-type .form-label {
        margin-bottom: 0.5rem;
        font-size: 0.9375rem;
    }
</style>
@endsection

@section('scripts')
@if(empty($bookingDob))
<script>
(function() {
    function initPublicBookingDobPicker() {
        var el = document.getElementById('public_booking_date_of_birth');
        if (!el || el.hasAttribute('data-flatpickr-initialized') || typeof flatpickr === 'undefined') return;
        try {
            flatpickr(el, {
                dateFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: true,
                maxDate: 'today',
                minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 150)),
                locale: { firstDayOfWeek: 1 },
            });
            el.setAttribute('data-flatpickr-initialized', 'true');
        } catch (e) { console.error('Public booking DOB Flatpickr init error:', e); }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { setTimeout(initPublicBookingDobPicker, 150); });
    } else {
        setTimeout(initPublicBookingDobPicker, 150);
    }
})();
</script>
@else
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

        // If service is pre-selected (from service booking link), set it up first and skip loading services
        @if(isset($service) && isset($doctor))
        selectedServiceId = {{ $service->id }};
        selectedDoctorId = {{ $doctor->id }};
        doctorSelect.value = {{ $doctor->id }};
        // Service is already in dropdown with data attributes, just use it
        serviceSelect.value = {{ $service->id }};
        serviceSelect.disabled = false;
        serviceSelectionCard.style.display = 'block';
        updateServiceDetails();
        loadSchedule();
        @elseif(isset($doctor) && $doctors->count() == 1)
        // If single doctor is pre-selected (from doctor link) or only one doctor available, load services immediately
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

        // Date navigation (optional controls — guard if absent from DOM)
        var prevDatesEl = document.getElementById('prev-dates');
        if (prevDatesEl) {
            prevDatesEl.addEventListener('click', function() {
                if (currentDateIndex > 0) {
                    currentDateIndex--;
                    renderDates();
                    loadTimeSlotsForDate(currentDates[currentDateIndex]);
                }
            });
        }
        var nextDatesEl = document.getElementById('next-dates');
        if (nextDatesEl) {
            nextDatesEl.addEventListener('click', function() {
                if (currentDateIndex < currentDates.length - 5) {
                    currentDateIndex++;
                    renderDates();
                    loadTimeSlotsForDate(currentDates[currentDateIndex]);
                }
            });
        }

        // Load doctor services
        function loadDoctorServices(doctorId) {
            // If service is already pre-selected and has data, don't reload
            if (selectedServiceId && serviceSelect.querySelector(`option[value="${selectedServiceId}"]`) && 
                serviceSelect.querySelector(`option[value="${selectedServiceId}"]`).dataset.duration) {
                // Service already loaded, just ensure it's selected
                serviceSelect.value = selectedServiceId;
                updateServiceDetails();
                loadSchedule();
                return;
            }

            serviceSelectionCard.style.display = 'block';
            serviceSelect.disabled = true;
            const preselectedServiceId = selectedServiceId; // Preserve pre-selected service
            const preselectedOption = preselectedServiceId ? serviceSelect.querySelector(`option[value="${preselectedServiceId}"]`) : null;
            
            // Save the pre-selected option if it exists
            let savedPreselectedOption = null;
            if (preselectedOption && preselectedOption.dataset.duration) {
                savedPreselectedOption = {
                    value: preselectedOption.value,
                    text: preselectedOption.textContent,
                    duration: preselectedOption.dataset.duration,
                    price: preselectedOption.dataset.price,
                    description: preselectedOption.dataset.description || '',
                    consultationType: preselectedOption.dataset.consultationType || 'in_person'
                };
            }
            
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
                
                // If we have a saved pre-selected option, add it first
                if (savedPreselectedOption) {
                    const option = document.createElement('option');
                    option.value = savedPreselectedOption.value;
                    option.textContent = savedPreselectedOption.text;
                    option.dataset.duration = savedPreselectedOption.duration;
                    option.dataset.price = savedPreselectedOption.price;
                    option.dataset.description = savedPreselectedOption.description || '';
                    option.dataset.consultationType = savedPreselectedOption.consultationType || 'in_person';
                    option.selected = true;
                    selectedServiceId = savedPreselectedOption.value;
                    serviceSelect.appendChild(option);
                }
                
                if (data.services && data.services.length > 0) {
                    data.services.forEach(service => {
                        // If pre-selected service is in API response, update its consultation type from API (for correct doctor)
                        if (savedPreselectedOption && service.id == savedPreselectedOption.value) {
                            const opt = serviceSelect.querySelector(`option[value="${service.id}"]`);
                            if (opt) {
                                opt.dataset.consultationType = service.consultation_type || 'in_person';
                            }
                            return;
                        }
                        
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = `${service.name} - £${parseFloat(service.price).toFixed(2)}`;
                        option.dataset.duration = service.duration;
                        option.dataset.price = service.price;
                        option.dataset.description = service.description || '';
                        option.dataset.consultationType = service.consultation_type || 'in_person';
                        // Re-select if this was the pre-selected service
                        if (preselectedServiceId && service.id == preselectedServiceId && !savedPreselectedOption) {
                            option.selected = true;
                            selectedServiceId = preselectedServiceId;
                        }
                        serviceSelect.appendChild(option);
                    });
                    serviceSelect.disabled = false;
                    
                    // If service was pre-selected, trigger update and load schedule
                    if (preselectedServiceId && selectedServiceId == preselectedServiceId) {
                        updateServiceDetails();
                        loadSchedule();
                    }
                } else {
                    // If no services from API but we have pre-selected, keep it
                    if (!savedPreselectedOption) {
                        serviceSelect.innerHTML = '<option value="">No services available</option>';
                    }
                }
            })
            .catch(error => {
                console.error('Error loading services:', error);
                // If we have a pre-selected service, keep it even on error
                if (!savedPreselectedOption) {
                    serviceSelect.innerHTML = '<option value="">Error loading services</option>';
                }
            });
        }

        // Map consultation type to display label and submit value
        function getConsultationTypeDisplay(consultationType, serviceName) {
            const type = (consultationType || 'in_person').toLowerCase();
            if (type === 'online') return { display: 'Online (Video)', submitValue: 'online' };
            if (type === 'phone' || type === 'telephone') return { display: 'Telephone', submitValue: 'telephone' };
            return { display: 'In Person', submitValue: 'in_person' };
        }

        // Update service details
        function updateServiceDetails() {
            const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const description = selectedOption.dataset.description || '';
                const duration = selectedOption.dataset.duration + ' minutes';
                const price = '£' + parseFloat(selectedOption.dataset.price).toFixed(2);
                const consultationType = selectedOption.dataset.consultationType || 'in_person';
                const serviceName = selectedOption.textContent || '';

                const descriptionEl = document.getElementById('service-description');
                if (description && description.trim()) {
                    descriptionEl.textContent = description;
                    descriptionEl.style.display = 'block';
                } else {
                    descriptionEl.style.display = 'none';
                }

                const { display: consultationTypeDisplay, submitValue } = getConsultationTypeDisplay(consultationType, serviceName);
                document.getElementById('service-consultation-type').textContent = consultationTypeDisplay;
                document.getElementById('consultation-type-input').value = submitValue;

                document.getElementById('service-duration').textContent = duration;
                document.getElementById('service-price').textContent = price;
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
            if (document.getElementById('prev-dates')) {
                document.getElementById('prev-dates').style.display = 'none';
            }
            if (document.getElementById('next-dates')) {
                document.getElementById('next-dates').style.display = 'none';
            }
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
@endif
@endsection
