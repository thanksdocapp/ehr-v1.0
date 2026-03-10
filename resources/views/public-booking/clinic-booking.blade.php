@extends('layouts.public-booking')

@section('title', 'Book Appointment')

@section('content')
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        <p>Select a service and available time. A doctor from the clinic will confirm your booking.</p>
    </div>

    <div class="progress-steps">
        <div class="step active">
            <div class="step-circle">1</div>
            <div class="step-label">Service & Time</div>
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

    <div class="info-card">
        <h3>{{ $department->name }}</h3>
        @if($department->description)
        <p>{{ $department->description }}</p>
        @endif
    </div>

    <form id="clinic-booking-form" method="POST" action="{{ route('public.booking.clinic-patient-details') }}">
        @csrf
        <input type="hidden" name="department_id" value="{{ $department->id }}">
        <input type="hidden" name="consultation_type" id="consultation-type-input" value="in_person">

        <div class="form-card">
            <label class="form-label">Select Service <span class="text-danger">*</span></label>
            <div class="clinic-service-row">
                <div class="clinic-service-dropdown-col">
                    <select name="service_id" id="service-select" class="form-control form-control-sm" required>
                        <option value="">Select a service...</option>
                        @foreach($services as $svc)
                        @php
                            $price = $svc['price'] ?? 0;
                            $duration = $svc['duration'] ?? 60;
                            $ct = $svc['consultation_type'] ?? 'in_person';
                            $desc = $svc['description'] ?? '';
                        @endphp
                        <option value="{{ $svc['id'] }}" data-duration="{{ $duration }}" data-price="{{ $price }}" data-consultation-type="{{ $ct }}" data-description="{{ e($desc) }}">
                            {{ $svc['name'] }} - £{{ number_format($price, 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="clinic-service-details-col" id="clinic-service-details" style="display: none;">
                    <div class="summary-card-compact">
                        <div class="summary-description-compact" id="clinic-service-description"></div>
                        <div class="summary-row-compact">
                            <span class="summary-label-compact">Consultation Type:</span>
                            <span class="summary-value-compact" id="clinic-service-consultation-type">-</span>
                        </div>
                        <div class="summary-row-compact">
                            <span class="summary-label-compact">Duration:</span>
                            <span class="summary-value-compact" id="clinic-service-duration">-</span>
                        </div>
                        <div class="summary-row-compact">
                            <span class="summary-label-compact">Price:</span>
                            <span class="summary-value-compact" id="clinic-service-price">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card" id="schedule-card" style="display: none;">
            <label class="form-label">Select Date & Time <span class="text-danger">*</span></label>
            <div class="date-navigation mb-2">
                <div class="date-display" id="date-display"></div>
            </div>
            <div id="time-slots-container">
                <div id="time-slots-calendar" class="time-slots-calendar"></div>
                <div id="no-slots-message" class="empty-message" style="display: none;">
                    <i class="fas fa-info-circle me-2"></i>No available slots. Please try different dates.
                </div>
            </div>
            <div id="loading-slots" class="loading-spinner" style="display: none;">
                <div class="spinner-border text-primary" role="status"></div>
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

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('styles')
<style>
    .clinic-service-row { display: flex; gap: 1rem; align-items: flex-start; flex-wrap: wrap; }
    .clinic-service-dropdown-col { flex: 1; min-width: 200px; }
    .clinic-service-details-col { flex: 1; min-width: 200px; }
    .summary-card-compact { background: #f8fafc; border-radius: 8px; padding: 1rem; border: 1px solid #e2e8f0; }
    .summary-description-compact { font-size: 0.75rem; color: #6c757d; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; line-height: 1.4; }
    .summary-row-compact { display: flex; justify-content: space-between; padding: 0.25rem 0; font-size: 0.8125rem; }
    .summary-label-compact { color: #6c757d; }
    .summary-value-compact { font-weight: 600; color: #1a202c; }
    .date-display { display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center; }
    .date-item { padding: 0.5rem 0.75rem; border: 2px solid #e2e8f0; border-radius: 6px; cursor: pointer; min-width: 90px; text-align: center; }
    .date-item:hover { border-color: var(--booking-primary, #007bff); }
    .date-item.selected { border-color: var(--booking-primary); background: var(--booking-primary); color: #fff; }
    .time-slots-calendar { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 0.5rem; margin-top: 0.75rem; }
    .time-slot-btn { padding: 0.5rem; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; }
    .time-slot-btn:hover { border-color: var(--booking-primary); }
    .time-slot-btn.selected { background: var(--booking-primary); color: #fff; border-color: var(--booking-primary); }
    @media (max-width: 768px) { .clinic-service-row { flex-direction: column; } }
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('clinic-booking-form');
    const serviceSelect = document.getElementById('service-select');
    const scheduleCard = document.getElementById('schedule-card');
    const dateDisplay = document.getElementById('date-display');
    const slotsContainer = document.getElementById('time-slots-calendar');
    const noSlotsMsg = document.getElementById('no-slots-message');
    const loadingSlots = document.getElementById('loading-slots');
    const continueBtn = document.getElementById('continue-btn');
    const dateInput = document.getElementById('appointment-date');
    const timeInput = document.getElementById('appointment-time');

    const departmentId = {{ $department->id }};
    let currentDates = [];
    let selectedDate = null;
    let selectedTime = null;

    function buildDates() {
        currentDates = [];
        for (let i = 0; i < 14; i++) {
            const d = new Date();
            d.setDate(d.getDate() + i);
            currentDates.push(d.toISOString().slice(0, 10));
        }
    }

    function renderDates() {
        dateDisplay.innerHTML = currentDates.slice(0, 7).map(d => {
            const dt = new Date(d);
            const day = dt.toLocaleDateString('en-GB', { weekday: 'short' });
            const date = dt.getDate();
            const isSelected = d === selectedDate;
            return `<div class="date-item ${isSelected ? 'selected' : ''}" data-date="${d}">${day}<br>${date}</div>`;
        }).join('');
        dateDisplay.querySelectorAll('.date-item').forEach(el => {
            el.addEventListener('click', () => {
                selectedDate = el.dataset.date;
                dateInput.value = selectedDate;
                renderDates();
                loadSlots();
            });
        });
    }

    function loadSlots() {
        if (!selectedDate || !serviceSelect.value) return;
        const serviceId = serviceSelect.value;
        const opt = serviceSelect.options[serviceSelect.selectedIndex];
        const duration = opt?.dataset.duration || 30;

        loadingSlots.style.display = 'block';
        slotsContainer.innerHTML = '';
        noSlotsMsg.style.display = 'none';

        fetch(`/api/public/clinics/${departmentId}/slots?service_id=${serviceId}&date=${selectedDate}&duration=${duration}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            loadingSlots.style.display = 'none';
            if (data.slots && data.slots.length > 0) {
                slotsContainer.innerHTML = data.slots.map(s => `
                    <button type="button" class="time-slot-btn" data-time="${s.start}">${s.display || s.start}</button>
                `).join('');
                slotsContainer.querySelectorAll('.time-slot-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        slotsContainer.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
                        btn.classList.add('selected');
                        selectedTime = btn.dataset.time;
                        timeInput.value = selectedTime;
                        continueBtn.disabled = false;
                    });
                });
            } else {
                noSlotsMsg.style.display = 'block';
            }
        })
        .catch(() => {
            loadingSlots.style.display = 'none';
            noSlotsMsg.style.display = 'block';
        });
    }

    function getConsultationTypeDisplay(ct) {
        const t = (ct || 'in_person').toLowerCase();
        if (t === 'online') return 'Online (Video)';
        if (t === 'phone' || t === 'telephone') return 'Telephone';
        return 'In Person';
    }

    function updateClinicServiceDetails() {
        const opt = serviceSelect.options[serviceSelect.selectedIndex];
        const detailsEl = document.getElementById('clinic-service-details');
        if (!opt || !opt.value) {
            detailsEl.style.display = 'none';
            return;
        }
        const desc = opt.dataset.description || '';
        const duration = opt.dataset.duration || 30;
        const price = parseFloat(opt.dataset.price || 0).toFixed(2);
        const ct = opt.dataset.consultationType || 'in_person';

        document.getElementById('clinic-service-description').textContent = desc.trim() || '';
        document.getElementById('clinic-service-description').style.display = desc.trim() ? 'block' : 'none';
        document.getElementById('clinic-service-consultation-type').textContent = getConsultationTypeDisplay(ct);
        document.getElementById('clinic-service-duration').textContent = duration + ' minutes';
        document.getElementById('clinic-service-price').textContent = '£' + price;
        detailsEl.style.display = 'block';
    }

    serviceSelect.addEventListener('change', function() {
        if (this.value) {
            var opt = this.options[this.selectedIndex];
            var ct = opt && opt.dataset.consultationType ? opt.dataset.consultationType : 'in_person';
            document.getElementById('consultation-type-input').value = ct;
            updateClinicServiceDetails();
            scheduleCard.style.display = 'block';
            buildDates();
            selectedDate = currentDates[0];
            dateInput.value = selectedDate;
            renderDates();
            loadSlots();
        } else {
            document.getElementById('consultation-type-input').value = 'in_person';
            document.getElementById('clinic-service-details').style.display = 'none';
            scheduleCard.style.display = 'none';
            continueBtn.disabled = true;
        }
    });

    if (serviceSelect.value) {
        var opt = serviceSelect.options[serviceSelect.selectedIndex];
        var ct = opt && opt.dataset.consultationType ? opt.dataset.consultationType : 'in_person';
        document.getElementById('consultation-type-input').value = ct;
        updateClinicServiceDetails();
        scheduleCard.style.display = 'block';
        buildDates();
        selectedDate = currentDates[0];
        dateInput.value = selectedDate;
        renderDates();
        loadSlots();
    }
});
</script>
@endsection
