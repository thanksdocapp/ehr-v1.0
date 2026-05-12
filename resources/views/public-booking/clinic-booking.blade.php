@extends('layouts.public-booking')

@section('title', 'Book Appointment')

@section('content')
@if(empty($services))
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        @if(!empty($bookingDob))
            <p>No services are available for the age on your profile.</p>
        @else
            <p>No bookable services are available at this clinic right now.</p>
        @endif
    </div>
    @if(!empty($bookingDob))
    <div class="alert alert-info border-0">Try clearing your saved date of birth and booking again, or contact the clinic.</div>
    <form method="POST" action="{{ route('public.booking.clear-dob') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-outline-primary">Clear saved date of birth</button>
    </form>
    @else
    <div class="alert alert-info border-0 text-center">Please try again later or contact the clinic.</div>
    @endif
@else
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        <p>Select a service and available time. A doctor from the clinic will confirm your booking.</p>
        @if(!empty($bookingDob))
        <form method="POST" action="{{ route('public.booking.clear-dob') }}" class="mt-2 mb-0">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-muted p-0">Clear saved date of birth</button>
        </form>
        @endif
    </div>

    <div class="progress-steps">
        <div class="step active">
            <div class="step-circle">1</div>
            <div class="step-label">Service &amp; time</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">2</div>
            <div class="step-label">Date of birth</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Your details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
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
            <label class="form-label" id="service-cards-label">Choose a service <span class="text-danger">*</span></label>
            <input type="hidden" name="service_id" id="service-id-input" value="" required>
            <div class="public-booking-service-grid" id="service-cards" role="group" aria-labelledby="service-cards-label">
                @foreach($services as $svc)
                @php
                    $price = $svc['price'] ?? 0;
                    $duration = $svc['duration'] ?? 60;
                    $ct = $svc['consultation_type'] ?? 'in_person';
                    $desc = $svc['description'] ?? '';
                    $name = $svc['name'] ?? 'Service';
                @endphp
                <button type="button"
                        class="pb-service-card"
                        data-service-id="{{ $svc['id'] }}"
                        data-duration="{{ (int) $duration }}"
                        data-price="{{ $price }}"
                        data-consultation-type="{{ e($ct) }}"
                        data-description="{{ e($desc) }}"
                        data-name="{{ e($name) }}"
                        aria-pressed="false">
                    <div class="pb-service-card__title">{{ $name }}</div>
                    @if($desc)
                    <div class="pb-service-card__desc">{{ \Illuminate\Support\Str::limit(strip_tags($desc), 120) }}</div>
                    @endif
                    <div class="pb-service-card__meta">
                        @if($duration)
                        <span><i class="far fa-clock me-1"></i>{{ (int) $duration }} min</span>
                        @endif
                    </div>
                    <div class="pb-service-card__price">£{{ number_format((float) $price, 2) }}</div>
                </button>
                @endforeach
            </div>
            <div class="clinic-service-details-wrap mt-3" id="clinic-service-details" style="display: none;">
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

        <div class="form-card" id="schedule-card" style="display: none;">
            <label class="form-label">Select Date & Time <span class="text-danger">*</span></label>
            <div class="date-navigation mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="prev-month" aria-label="Previous month">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div id="month-label" class="fw-semibold text-center"></div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="next-month" aria-label="Next month">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="date-navigation mb-2">
                <div class="date-display" id="date-display"></div>
            </div>
            <div class="calendar-legend mb-2">
                <span class="legend-item"><span class="legend-dot legend-available"></span>Available</span>
                <span class="legend-item"><span class="legend-dot legend-unavailable"></span>Unavailable</span>
            </div>
            <div id="time-slots-container">
                <div id="time-slots-picker" class="time-slots-picker" style="display: none;"></div>
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
@endif
@endsection

@section('styles')
<style>
    .public-booking-service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 0.75rem;
    }
    .pb-service-card {
        display: block;
        width: 100%;
        text-align: left;
        padding: 1rem 1.1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        font: inherit;
        color: inherit;
        min-height: 44px;
    }
    .pb-service-card:hover { border-color: var(--booking-primary, #007bff); }
    .pb-service-card:focus-visible { outline: 2px solid var(--booking-primary, #007bff); outline-offset: 2px; }
    .pb-service-card.is-selected {
        border-color: var(--booking-primary, #007bff);
        box-shadow: 0 0 0 1px var(--booking-primary, #007bff);
    }
    .pb-service-card__title { font-weight: 700; font-size: 1rem; margin-bottom: 0.35rem; color: #1a202c; }
    .pb-service-card__desc { font-size: 0.8125rem; color: #64748b; line-height: 1.4; margin-bottom: 0.5rem; }
    .pb-service-card__meta { font-size: 0.8125rem; color: #64748b; }
    .pb-service-card__price { font-size: 1.05rem; font-weight: 700; color: var(--booking-primary, #007bff); margin-top: 0.5rem; }
    .clinic-service-details-wrap { max-width: 100%; }
    .summary-card-compact { background: #f8fafc; border-radius: 8px; padding: 1rem; border: 1px solid #e2e8f0; }
    .summary-description-compact { font-size: 0.75rem; color: #6c757d; margin-bottom: 0.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; line-height: 1.4; }
    .summary-row-compact { display: flex; justify-content: space-between; padding: 0.25rem 0; font-size: 0.8125rem; }
    .summary-label-compact { color: #6c757d; }
    .summary-value-compact { font-weight: 600; color: #1a202c; }
    .date-navigation { display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; }
    .date-display { display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center; }
    .date-item { padding: 0.5rem 0.75rem; border: 2px solid #e2e8f0; border-radius: 6px; cursor: pointer; min-width: 90px; text-align: center; }
    .date-item:hover { border-color: var(--booking-primary, #007bff); }
    .date-item.selected { border-color: var(--booking-primary); background: var(--booking-primary); color: #fff; }
    .date-item.unavailable { background: #eef2f7; border-color: #d6dce5; color: #8a94a6; cursor: not-allowed; }
    .date-item.unavailable:hover { border-color: #d6dce5; }
    .calendar-legend { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
    .legend-item { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8125rem; color: #6b7280; }
    .legend-dot { width: 0.75rem; height: 0.75rem; border-radius: 999px; border: 1px solid #cbd5e1; display: inline-block; }
    .legend-available { background: #ffffff; }
    .legend-unavailable { background: #eef2f7; }
    .time-slots-picker { margin-top: 0.75rem; max-width: 28rem; margin-left: auto; margin-right: auto; }
    .time-slots-picker .form-label { font-weight: 600; color: #334155; }
    .time-slots-picker select { border-radius: 8px; border-width: 2px; }
    .time-slot-hint { font-size: 0.8125rem; color: #64748b; margin-top: 0.5rem; }
</style>
@endsection

@section('scripts')
@if(!empty($services))
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceIdInput = document.getElementById('service-id-input');
    const serviceCardsRoot = document.getElementById('service-cards');
    const scheduleCard = document.getElementById('schedule-card');
    const dateDisplay = document.getElementById('date-display');
    const slotsContainer = document.getElementById('time-slots-picker');
    const noSlotsMsg = document.getElementById('no-slots-message');
    const loadingSlots = document.getElementById('loading-slots');
    const continueBtn = document.getElementById('continue-btn');
    const dateInput = document.getElementById('appointment-date');
    const timeInput = document.getElementById('appointment-time');

    const departmentId = {{ $department->id }};
    let dateRange = [];
    let monthKeys = [];
    let currentMonthIndex = 0;
    const slotsByDate = {};
    const RANGE_DAYS = 90;
    let selectedDate = null;
    let selectedTime = null;
    const prevMonthBtn = document.getElementById('prev-month');
    const nextMonthBtn = document.getElementById('next-month');

    function getSelectedServiceCard() {
        return serviceCardsRoot ? serviceCardsRoot.querySelector('.pb-service-card.is-selected') : null;
    }

    function getServiceId() {
        return serviceIdInput ? serviceIdInput.value : '';
    }

    function getServiceDuration() {
        const c = getSelectedServiceCard();
        return (c && c.dataset.duration) ? String(c.dataset.duration) : '30';
    }

    function getPbServiceCards(root) {
        if (!root) return [];
        return Array.prototype.slice.call(root.querySelectorAll('.pb-service-card'));
    }

    function syncPbServiceCardTabOrder(root) {
        if (!root) return;
        var cards = getPbServiceCards(root);
        if (cards.length === 0) return;
        var selected = root.querySelector('.pb-service-card.is-selected');
        cards.forEach(function(c, i) {
            if (selected) {
                c.tabIndex = c.classList.contains('is-selected') ? 0 : -1;
            } else {
                c.tabIndex = i === 0 ? 0 : -1;
            }
        });
    }

    function bindPbServiceGroupKeyboard(root, onSelect) {
        if (!root) return;
        root.addEventListener('keydown', function(e) {
            var cards = getPbServiceCards(root);
            if (cards.length === 0) return;
            var key = e.key;
            if (key !== 'ArrowRight' && key !== 'ArrowLeft' && key !== 'ArrowDown' && key !== 'ArrowUp' && key !== 'Home' && key !== 'End' && key !== ' ' && key !== 'Enter') {
                return;
            }
            var active = document.activeElement;
            var idx = cards.indexOf(active);
            if (key === ' ' || key === 'Enter') {
                if (idx >= 0) {
                    e.preventDefault();
                    onSelect(cards[idx]);
                }
                return;
            }
            if (idx < 0) {
                idx = cards.findIndex(function(c) { return c.classList.contains('is-selected'); });
                if (idx < 0) idx = 0;
                e.preventDefault();
                cards[idx].focus();
                return;
            }
            var next = idx;
            if (key === 'Home') next = 0;
            else if (key === 'End') next = cards.length - 1;
            else if (key === 'ArrowRight' || key === 'ArrowDown') next = Math.min(cards.length - 1, idx + 1);
            else if (key === 'ArrowLeft' || key === 'ArrowUp') next = Math.max(0, idx - 1);
            if (key === 'Home' || key === 'End' || key.indexOf('Arrow') === 0) {
                e.preventDefault();
                if (next !== idx) {
                    onSelect(cards[next]);
                    cards[next].focus();
                }
            }
        });
    }

    function applyClinicServiceFromCard(card) {
        if (!card || !serviceIdInput || !serviceCardsRoot) return;
        Object.keys(slotsByDate).forEach(function(k) { delete slotsByDate[k]; });
        serviceCardsRoot.querySelectorAll('.pb-service-card').forEach(function(btn) {
            btn.classList.remove('is-selected');
            btn.setAttribute('aria-pressed', 'false');
        });
        card.classList.add('is-selected');
        card.setAttribute('aria-pressed', 'true');
        serviceIdInput.value = card.dataset.serviceId || '';
        var ct = card.dataset.consultationType ? card.dataset.consultationType : 'in_person';
        document.getElementById('consultation-type-input').value = ct;
        updateClinicServiceDetails();
        scheduleCard.style.display = 'block';
        buildDateRange();
        currentMonthIndex = 0;
        selectedDate = null;
        selectedTime = null;
        dateInput.value = '';
        timeInput.value = '';
        continueBtn.disabled = true;
        renderMonthNavigation();
        renderDates();
        hydrateCurrentMonth();
        syncPbServiceCardTabOrder(serviceCardsRoot);
    }

    function buildDateRange() {
        dateRange = [];
        monthKeys = [];
        const seen = {};
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        for (let i = 0; i < RANGE_DAYS; i++) {
            const d = new Date(today);
            d.setDate(today.getDate() + i);
            const ymd = d.toISOString().slice(0, 10);
            const monthKey = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
            dateRange.push({ ymd, monthKey });
            if (!seen[monthKey]) {
                seen[monthKey] = true;
                monthKeys.push(monthKey);
            }
        }
    }

    function getVisibleMonthDates() {
        const monthKey = monthKeys[currentMonthIndex];
        return dateRange.filter(d => d.monthKey === monthKey).map(d => d.ymd);
    }

    function renderMonthNavigation() {
        const labelEl = document.getElementById('month-label');
        const monthKey = monthKeys[currentMonthIndex];
        const [year, month] = monthKey.split('-').map(Number);
        const dateObj = new Date(year, month - 1, 1);
        labelEl.textContent = dateObj.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        prevMonthBtn.disabled = currentMonthIndex === 0;
        nextMonthBtn.disabled = currentMonthIndex >= monthKeys.length - 1;
    }

    function renderDates() {
        const visibleDates = getVisibleMonthDates();
        dateDisplay.innerHTML = visibleDates.map(d => {
            const dt = new Date(d);
            const day = dt.toLocaleDateString('en-GB', { weekday: 'short' });
            const date = dt.getDate();
            const isSelected = d === selectedDate;
            const unavailable = Array.isArray(slotsByDate[d]) && slotsByDate[d].length === 0;
            const unavailableClass = unavailable ? ' unavailable' : '';
            return `<div class="date-item ${isSelected ? 'selected' : ''}${unavailableClass}" data-date="${d}">${day}<br>${date}</div>`;
        }).join('');
        dateDisplay.querySelectorAll('.date-item').forEach(el => {
            el.addEventListener('click', () => {
                if (el.classList.contains('unavailable')) {
                    return;
                }
                selectedDate = el.dataset.date;
                dateInput.value = selectedDate;
                renderDates();
                loadSlots();
            });
        });
    }

    function fetchSlotsForDate(dateStr) {
        if (Array.isArray(slotsByDate[dateStr])) {
            return Promise.resolve(slotsByDate[dateStr]);
        }
        const serviceId = getServiceId();
        const duration = getServiceDuration();
        return fetch(`/api/public/clinics/${departmentId}/slots?service_id=${serviceId}&date=${dateStr}&duration=${duration}`, {
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(r => r.json())
        .then(data => {
            const slots = data && Array.isArray(data.slots) ? data.slots : [];
            slotsByDate[dateStr] = slots;
            return slots;
        })
        .catch(() => {
            slotsByDate[dateStr] = [];
            return [];
        });
    }

    function hydrateCurrentMonth() {
        const visibleDates = getVisibleMonthDates();
        loadingSlots.style.display = 'block';
        Promise.all(visibleDates.map(fetchSlotsForDate))
            .then(() => {
                loadingSlots.style.display = 'none';
                renderDates();
                const firstAvailable = visibleDates.find(d => Array.isArray(slotsByDate[d]) && slotsByDate[d].length > 0);
                if (firstAvailable) {
                    selectedDate = firstAvailable;
                    dateInput.value = firstAvailable;
                    renderDates();
                    loadSlots();
                } else {
                    slotsContainer.innerHTML = '';
                    slotsContainer.style.display = 'none';
                    noSlotsMsg.style.display = 'block';
                }
            })
            .catch(() => {
                loadingSlots.style.display = 'none';
            });
    }

    function loadSlots() {
        if (!selectedDate || !getServiceId()) return;
        const duration = getServiceDuration();

        loadingSlots.style.display = 'block';
        slotsContainer.innerHTML = '';
        slotsContainer.style.display = 'none';
        noSlotsMsg.style.display = 'none';

        fetchSlotsForDate(selectedDate).then((slots) => {
            loadingSlots.style.display = 'none';
            if (slots && slots.length > 0) {
                slotsContainer.innerHTML = '';
                const lbl = document.createElement('label');
                lbl.className = 'form-label';
                lbl.htmlFor = 'clinic-slot-time-select';
                lbl.textContent = 'Start time';
                const sel = document.createElement('select');
                sel.id = 'clinic-slot-time-select';
                sel.className = 'form-select form-select-lg';
                sel.required = true;
                const ph = document.createElement('option');
                ph.value = '';
                ph.textContent = 'Choose a time…';
                sel.appendChild(ph);
                slots.forEach(s => {
                    const o = document.createElement('option');
                    o.value = s.start;
                    o.textContent = s.display || s.start;
                    sel.appendChild(o);
                });
                const hint = document.createElement('p');
                hint.className = 'time-slot-hint mb-0';
                hint.textContent = 'Each option is one appointment (' + duration + ' minutes). Only times that fit the clinic schedule are listed.';
                slotsContainer.appendChild(lbl);
                slotsContainer.appendChild(sel);
                slotsContainer.appendChild(hint);
                slotsContainer.style.display = 'block';
                sel.addEventListener('change', () => {
                    if (sel.value) {
                        selectedTime = sel.value;
                        timeInput.value = selectedTime;
                        continueBtn.disabled = false;
                    } else {
                        selectedTime = null;
                        timeInput.value = '';
                        continueBtn.disabled = true;
                    }
                });
            } else {
                noSlotsMsg.style.display = 'block';
            }
            renderDates();
        });
    }

    function getConsultationTypeDisplay(ct) {
        const t = (ct || 'in_person').toLowerCase();
        if (t === 'online') return 'Online (Video)';
        if (t === 'phone' || t === 'telephone') return 'Telephone';
        return 'In Person';
    }

    function updateClinicServiceDetails() {
        const card = getSelectedServiceCard();
        const detailsEl = document.getElementById('clinic-service-details');
        if (!card || !getServiceId()) {
            detailsEl.style.display = 'none';
            return;
        }
        const desc = card.dataset.description || '';
        const duration = card.dataset.duration || 30;
        const price = parseFloat(card.dataset.price || 0).toFixed(2);
        const ct = card.dataset.consultationType || 'in_person';

        document.getElementById('clinic-service-description').textContent = desc.trim() || '';
        document.getElementById('clinic-service-description').style.display = desc.trim() ? 'block' : 'none';
        document.getElementById('clinic-service-consultation-type').textContent = getConsultationTypeDisplay(ct);
        document.getElementById('clinic-service-duration').textContent = duration + ' minutes';
        document.getElementById('clinic-service-price').textContent = '£' + price;
        detailsEl.style.display = 'block';
    }

    if (serviceCardsRoot) {
        serviceCardsRoot.addEventListener('click', function(e) {
            var card = e.target.closest('.pb-service-card');
            if (!card) return;
            applyClinicServiceFromCard(card);
        });
        bindPbServiceGroupKeyboard(serviceCardsRoot, applyClinicServiceFromCard);
        var cards = serviceCardsRoot.querySelectorAll('.pb-service-card');
        if (cards.length === 1) {
            applyClinicServiceFromCard(cards[0]);
        } else {
            syncPbServiceCardTabOrder(serviceCardsRoot);
        }
    }

    prevMonthBtn.addEventListener('click', function() {
        if (currentMonthIndex > 0) {
            currentMonthIndex--;
            selectedDate = null;
            selectedTime = null;
            dateInput.value = '';
            timeInput.value = '';
            continueBtn.disabled = true;
            renderMonthNavigation();
            renderDates();
            hydrateCurrentMonth();
        }
    });

    nextMonthBtn.addEventListener('click', function() {
        if (currentMonthIndex < monthKeys.length - 1) {
            currentMonthIndex++;
            selectedDate = null;
            selectedTime = null;
            dateInput.value = '';
            timeInput.value = '';
            continueBtn.disabled = true;
            renderMonthNavigation();
            renderDates();
            hydrateCurrentMonth();
        }
    });
});
</script>
@endif
@endsection
