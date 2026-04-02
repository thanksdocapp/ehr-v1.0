@extends('layouts.public-booking')

@section('title', 'Select Date & Time')

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
            <div class="step-label">Date &amp; time</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Date of birth</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
            <div class="step-label">Your details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">5</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <div class="summary-card">
        <h4>{{ $service->name }}</h4>
        <p>{{ $service->getDurationForDoctor($doctor->id) ?? $service->default_duration_minutes ?? 60 }} minutes •
        @php $price = $service->getPriceForDoctor($doctor->id) ?? $service->default_price ?? 0; @endphp
        @if($price > 0)
        £{{ number_format($price, 2) }}
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
            <input type="text" name="appointment_date" id="appointment-date" class="form-control uk-date" data-min-date="{{ date('Y-m-d') }}" data-uk-date="true" required autocomplete="off">
            <small class="text-muted d-block mt-2">Select a date to see available time slots</small>

            <div id="time-slots-container" style="display: none; margin-top: 2rem;">
                <div id="time-slots-picker" class="time-slots-picker"></div>
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

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('styles')
    <style>
        .time-slots-picker { margin-top: 0.25rem; max-width: 28rem; }
        .time-slots-picker .form-label { font-weight: 600; color: #334155; }
        .time-slots-picker select { border-radius: 8px; border-width: 2px; }
        .time-slot-hint { font-size: 0.8125rem; color: #64748b; margin-top: 0.5rem; }
    </style>
@endsection

@section('scripts')
@php
    $serviceDurationMinutes = (int) ($service->getDurationForDoctor($doctor->id) ?? $service->default_duration_minutes ?? 60);
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('appointment-date');
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const timeSlotsPicker = document.getElementById('time-slots-picker');
        const noSlotsMessage = document.getElementById('no-slots-message');
        const loadingSlots = document.getElementById('loading-slots');
        const continueBtn = document.getElementById('continue-btn');
        const form = document.getElementById('datetime-form');
        const durationMinutes = {{ $serviceDurationMinutes }};
        let selectedTime = null;

        function getDateValueYmd(input) {
            var val = (input && input.value) ? input.value.trim() : '';
            if (!val) return '';
            if (val.match(/^\d{4}-\d{2}-\d{2}$/)) return val;
            if (val.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                var parts = val.split('/');
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }
            return '';
        }

        function onDateChange() {
            const selectedDate = getDateValueYmd(dateInput);
            if (!selectedDate) {
                timeSlotsContainer.style.display = 'none';
                continueBtn.disabled = true;
                return;
            }

            loadingSlots.style.display = 'block';
            timeSlotsContainer.style.display = 'none';
            continueBtn.disabled = true;
            selectedTime = null;

            fetch(`{{ route('public.api.available-slots', $doctor->id) }}?date=${selectedDate}&service_id={{ $service->id }}&duration=${durationMinutes}`, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingSlots.style.display = 'none';
                timeSlotsContainer.style.display = 'block';
                timeSlotsPicker.innerHTML = '';

                if (data.slots && data.slots.length > 0) {
                    noSlotsMessage.style.display = 'none';
                    const lbl = document.createElement('label');
                    lbl.className = 'form-label';
                    lbl.htmlFor = 'doctor-slot-time-select';
                    lbl.textContent = 'Start time';
                    const sel = document.createElement('select');
                    sel.id = 'doctor-slot-time-select';
                    sel.className = 'form-select form-select-lg';
                    sel.required = true;
                    const ph = document.createElement('option');
                    ph.value = '';
                    ph.textContent = 'Choose a time…';
                    sel.appendChild(ph);
                    data.slots.forEach(function(slot) {
                        const o = document.createElement('option');
                        o.value = slot.start;
                        o.textContent = slot.display || slot.start;
                        sel.appendChild(o);
                    });
                    const hint = document.createElement('p');
                    hint.className = 'time-slot-hint mb-0';
                    hint.textContent = 'Each option is one appointment (' + durationMinutes + ' minutes).';
                    timeSlotsPicker.appendChild(lbl);
                    timeSlotsPicker.appendChild(sel);
                    timeSlotsPicker.appendChild(hint);
                    sel.addEventListener('change', function() {
                        if (sel.value) {
                            selectedTime = sel.value;
                            continueBtn.disabled = false;
                        } else {
                            selectedTime = null;
                            continueBtn.disabled = true;
                        }
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
        }

        dateInput.addEventListener('change', onDateChange);
        dateInput.addEventListener('blur', function() { if (getDateValueYmd(dateInput)) onDateChange(); });

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
