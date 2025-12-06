@extends('layouts.public-booking')

@section('title', 'Select Date & Time')
@section('container-width', '900px')

@section('content')
    <div class="booking-header">
        <h1>Select Date & Time</h1>
        <p>Choose your preferred appointment slot</p>
    </div>

    <div class="progress-steps">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">2</div>
            <div class="step-label">Date & Time</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <div class="summary-card">
        <h4>{{ $service->name }}</h4>
        <p>{{ $service->default_duration_minutes ?? 60 }} minutes •
        @if($service->default_price)
        £{{ number_format($service->default_price, 2) }}
        @else
        Price on request
        @endif
        </p>
    </div>

    <form id="datetime-form" method="POST" action="{{ route('public.booking.patient-details') }}">
        @csrf
        <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        @if(isset($department_id))
        <input type="hidden" name="department_id" value="{{ $department_id }}">
        @endif

        <div class="form-card">
            <label class="form-label">Select Date</label>
            <input type="date" name="appointment_date" id="appointment-date" class="form-control" min="{{ date('Y-m-d') }}" required>
            <small class="text-muted d-block mt-2">Select a date to see available time slots</small>

            <div id="time-slots-container" style="display: none; margin-top: 2rem;">
                <label class="form-label">Available Time Slots</label>
                <div id="time-slots-grid" class="time-slots-grid"></div>
                <div id="no-slots-message" class="empty-message" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>No available slots on this date. Please select another date.
                </div>
            </div>

            <div id="loading-slots" class="loading-spinner" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading available slots...</p>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ isset($department) ? route('public.booking.clinic', $department->slug) : route('public.booking.doctor', $doctor->slug) }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="continue-btn" disabled>
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('appointment-date');
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const timeSlotsGrid = document.getElementById('time-slots-grid');
        const noSlotsMessage = document.getElementById('no-slots-message');
        const loadingSlots = document.getElementById('loading-slots');
        const continueBtn = document.getElementById('continue-btn');
        const form = document.getElementById('datetime-form');
        let selectedTime = null;

        dateInput.addEventListener('change', function() {
            const selectedDate = this.value;
            if (!selectedDate) {
                timeSlotsContainer.style.display = 'none';
                continueBtn.disabled = true;
                return;
            }

            loadingSlots.style.display = 'block';
            timeSlotsContainer.style.display = 'none';
            continueBtn.disabled = true;
            selectedTime = null;

            fetch(`{{ route('public.api.available-slots', $doctor->id) }}?date=${selectedDate}&service_id={{ $service->id }}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingSlots.style.display = 'none';
                timeSlotsContainer.style.display = 'block';
                timeSlotsGrid.innerHTML = '';

                if (data.slots && data.slots.length > 0) {
                    noSlotsMessage.style.display = 'none';
                    data.slots.forEach(slot => {
                        const slotBtn = document.createElement('button');
                        slotBtn.type = 'button';
                        slotBtn.className = 'time-slot-btn';
                        slotBtn.textContent = slot.display || slot.start;
                        slotBtn.dataset.time = slot.start;
                        slotBtn.addEventListener('click', function() {
                            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                                btn.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            selectedTime = this.dataset.time;
                            continueBtn.disabled = false;
                        });
                        timeSlotsGrid.appendChild(slotBtn);
                    });
                } else {
                    noSlotsMessage.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading slots:', error);
                loadingSlots.style.display = 'none';
                alert('Failed to load available time slots. Please try again.');
            });
        });

        form.addEventListener('submit', function(e) {
            if (!selectedTime) {
                e.preventDefault();
                alert('Please select a time slot.');
                return false;
            }

            const timeInput = document.createElement('input');
            timeInput.type = 'hidden';
            timeInput.name = 'appointment_time';
            timeInput.value = selectedTime;
            form.appendChild(timeInput);
        });
    });
</script>
@endsection
