@extends('layouts.public-booking')

@section('title', 'Book Appointment')

@section('content')
    @if(session('warning'))
    <div class="alert alert-warning border-0 mb-3">{{ session('warning') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger border-0 mb-3">{{ session('error') }}</div>
    @endif
    <!-- Header -->
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        <p>Select a doctor, service, and appointment time to continue</p>
        @if(!empty($bookingDob))
        <form method="POST" action="{{ route('public.booking.clear-dob') }}" class="mt-2 mb-0">
            @csrf
            <button type="submit" class="btn btn-link btn-sm text-muted p-0">Clear saved date of birth</button>
        </form>
        @endif
    </div>

    <!-- Progress Steps -->
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

        <!-- Service selection (cards) -->
        <div class="form-card" id="service-selection-card" style="display: {{ (isset($doctor) && $doctors->count() == 1) ? 'block' : 'none' }};">
            <label class="form-label" id="service-cards-label">Choose a service <span class="text-danger">*</span></label>
            <p class="text-muted small mb-2" id="service-loading-msg" style="display: none;"><i class="fas fa-spinner fa-spin me-1"></i>Loading services…</p>
            <input type="hidden" name="service_id" id="service-id-input" value="@if(isset($service)){{ $service->id }}@endif" @if((isset($doctor) && $doctors->count() == 1) || isset($service)) required @endif>
            <div class="public-booking-service-grid" id="service-cards" role="group" aria-labelledby="service-cards-label">
                @if(isset($service) && isset($doctor))
                @php
                    $servicePrice = $service->getPriceForDoctor($doctor->id) ?? $service->default_price ?? 0;
                    $serviceDuration = $service->getDurationForDoctor($doctor->id) ?? $service->default_duration_minutes ?? 60;
                    $serviceDescription = $service->description ?? '';
                    $serviceConsultationType = $service->getConsultationTypeForDoctor($doctor->id);
                    $serviceName = $service->name;
                @endphp
                <button type="button"
                        class="pb-service-card is-selected"
                        data-service-id="{{ $service->id }}"
                        data-duration="{{ (int) $serviceDuration }}"
                        data-price="{{ $servicePrice }}"
                        data-description="{{ e($serviceDescription) }}"
                        data-consultation-type="{{ e($serviceConsultationType) }}"
                        data-is-non-consultation="{{ $service->isNonConsultation() ? '1' : '0' }}"
                        data-name="{{ e($serviceName) }}"
                        aria-pressed="true">
                    <div class="pb-service-card__title">{{ $serviceName }}</div>
                    @if($serviceDescription)
                    <div class="pb-service-card__desc">{{ \Illuminate\Support\Str::limit(strip_tags($serviceDescription), 120) }}</div>
                    @endif
                    @if(!$service->isNonConsultation())
                    <div class="pb-service-card__meta">
                        <span><i class="far fa-clock me-1"></i>{{ (int) $serviceDuration }} min</span>
                    </div>
                    @endif
                    <div class="pb-service-card__price">£{{ number_format((float) $servicePrice, 2) }}</div>
                </button>
                @endif
            </div>
            <div class="service-details-col mt-2" id="service-details" style="display: {{ (isset($service) && isset($doctor)) ? 'block' : 'none' }};">
                <div class="summary-card-compact">
                    <div class="summary-description-compact" id="service-description"></div>
                    <div class="summary-row-compact" id="service-consultation-type-row">
                        <span class="summary-label-compact">Consultation Type:</span>
                        <span class="summary-value-compact" id="service-consultation-type">-</span>
                    </div>
                    <div class="summary-row-compact" id="service-duration-row">
                        <span class="summary-label-compact">Duration:</span>
                        <span class="summary-value-compact" id="service-duration">-</span>
                    </div>
                    <div class="summary-row-compact">
                        <span class="summary-label-compact">Price:</span>
                        <span class="summary-value-compact" id="service-price">-</span>
                    </div>
                </div>
            </div>
            <input type="hidden" name="consultation_type" id="consultation-type-input" value="in_person">
        </div>

        <!-- Schedule Selection - Shown when service is selected -->
        <div class="form-card" id="schedule-selection-card" style="display: none;">
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
                    <i class="fas fa-info-circle me-2"></i>No available slots on this date. Please select another day.
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

    .date-item.unavailable {
        background: #eef2f7;
        border-color: #d6dce5;
        color: #8a94a6;
        cursor: not-allowed;
    }

    .date-item.unavailable:hover {
        border-color: #d6dce5;
    }

    .calendar-legend {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8125rem;
        color: #6b7280;
    }

    .legend-dot {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        display: inline-block;
    }

    .legend-available {
        background: #ffffff;
    }

    .legend-unavailable {
        background: #eef2f7;
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

    .time-slots-picker {
        margin-top: 0.75rem;
        max-width: 28rem;
        margin-left: auto;
        margin-right: auto;
    }

    .time-slots-picker .form-label {
        font-weight: 600;
        color: #334155;
    }

    .time-slots-picker select {
        border-radius: 8px;
        border-width: 2px;
    }

    .time-slot-hint {
        font-size: 0.8125rem;
        color: #64748b;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .date-navigation { flex-wrap: wrap; gap: 0.5rem; }
        .date-display { gap: 0.5rem; }
        .date-item { min-width: 72px; padding: 0.5rem; min-height: 44px; }
        .date-item .date-day { font-size: 0.65rem; }
        .date-item .date-date { font-size: 0.75rem; }
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

    .public-booking-service-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
        width: 100%;
    }

    @media (min-width: 768px) {
        .public-booking-service-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
    }

    .pb-service-card {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        width: 100%;
        min-width: 0;
        text-align: left;
        padding: 1rem 0.9rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        font: inherit;
        color: inherit;
        min-height: 5.5rem;
        -webkit-tap-highlight-color: transparent;
    }

    .pb-service-card:hover {
        border-color: var(--booking-primary);
    }

    .pb-service-card:active {
        transform: scale(0.99);
    }

    .pb-service-card:focus-visible {
        outline: 2px solid var(--booking-primary);
        outline-offset: 2px;
    }

    .pb-service-card.is-selected {
        border-color: var(--booking-primary);
        box-shadow: 0 0 0 1px var(--booking-primary);
    }

    .pb-service-card__title {
        font-weight: 700;
        font-size: 0.9375rem;
        line-height: 1.25;
        margin-bottom: 0.35rem;
        color: #1a202c;
    }

    .pb-service-card__desc {
        font-size: 0.75rem;
        color: #64748b;
        line-height: 1.35;
        margin-bottom: 0.35rem;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
        flex: 1;
        min-height: 0;
    }

    .pb-service-card__meta {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: auto;
    }

    .pb-service-card__meta .fa-clock {
        font-size: 0.7rem;
    }

    .pb-service-card__price {
        font-size: 1rem;
        font-weight: 700;
        color: var(--booking-primary);
        margin-top: 0.35rem;
    }

    @media (max-width: 576px) {
        .pb-service-card {
            padding: 0.65rem 0.45rem;
            border-radius: 8px;
            min-height: 4.75rem;
        }

        .pb-service-card__title {
            font-size: 0.8125rem;
            margin-bottom: 0.2rem;
        }

        .pb-service-card__desc {
            font-size: 0.6875rem;
            -webkit-line-clamp: 2;
            margin-bottom: 0.25rem;
        }

        .pb-service-card__meta {
            font-size: 0.6875rem;
        }

        .pb-service-card__price {
            font-size: 0.9375rem;
            margin-top: 0.25rem;
        }
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const doctorSelect = document.getElementById('doctor-select');
        const serviceSelectionCard = document.getElementById('service-selection-card');
        const scheduleSelectionCard = document.getElementById('schedule-selection-card');
        const serviceIdInput = document.getElementById('service-id-input');
        const serviceCardsRoot = document.getElementById('service-cards');
        const serviceLoadingMsg = document.getElementById('service-loading-msg');
        const serviceDetails = document.getElementById('service-details');
        const continueBtn = document.getElementById('continue-btn');
        const form = document.getElementById('service-form');
        const loadingSlots = document.getElementById('loading-slots');
        const noSlotsMessage = document.getElementById('no-slots-message');
        const timeSlotsPicker = document.getElementById('time-slots-picker');
        const appointmentDateInput = document.getElementById('appointment-date');
        const appointmentTimeInput = document.getElementById('appointment-time');

        let selectedDoctorId = null;
        let selectedNonConsultation = false;
        const selectDatetimeUrl = @json(route('public.booking.select-datetime'));
        const patientDetailsUrl = @json(route('public.booking.patient-details'));
        let selectedServiceId = null;
        let selectedDate = null;
        let selectedTime = null;
        let dateRange = [];
        let monthKeys = [];
        let currentMonthIndex = 0;
        const slotsByDate = {};
        const RANGE_DAYS = 90;

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

        function clearSlotsCache() {
            Object.keys(slotsByDate).forEach(function(k) {
                delete slotsByDate[k];
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = (text === null || text === undefined) ? '' : String(text);
            return div.innerHTML;
        }

        function renderDoctorServiceCard(svc, isSelected) {
            if (!serviceCardsRoot) {
                return;
            }
            const id = String(svc.id);
            const name = svc.name != null ? svc.name : 'Service';
            const duration = String(svc.duration != null ? svc.duration : 60);
            const priceNum = parseFloat(svc.price != null ? svc.price : 0);
            const descRaw = svc.description || '';
            const descPlain = String(descRaw).replace(/<[^>]*>/g, '');
            const ct = svc.consultation_type || svc.consultationType || 'in_person';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'pb-service-card' + (isSelected ? ' is-selected' : '');
            btn.dataset.serviceId = id;
            btn.dataset.duration = duration;
            btn.dataset.price = String(priceNum);
            btn.dataset.description = descPlain;
            btn.dataset.consultationType = ct;
            btn.dataset.isNonConsultation = (svc.is_non_consultation || svc.isNonConsultation) ? '1' : '0';
            btn.dataset.name = name;
            btn.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
            const descShort = descPlain.length > 120 ? descPlain.slice(0, 117) + '...' : descPlain;
            var inner = '<div class="pb-service-card__title">' + escapeHtml(name) + '</div>';
            if (descShort.trim()) {
                inner += '<div class="pb-service-card__desc">' + escapeHtml(descShort) + '</div>';
            }
            if (btn.dataset.isNonConsultation !== '1') {
                inner += '<div class="pb-service-card__meta"><span><i class="far fa-clock me-1"></i>' + escapeHtml(duration) + ' min</span></div>';
            }
            inner += '<div class="pb-service-card__price">' + escapeHtml('£' + priceNum.toFixed(2)) + '</div>';
            btn.innerHTML = inner;
            if (isSelected && serviceIdInput) {
                serviceIdInput.value = id;
                selectedServiceId = id;
            }
            serviceCardsRoot.appendChild(btn);
        }

        function getConsultationTypeDisplay(consultationType, serviceName) {
            const type = (consultationType || 'in_person').toLowerCase();
            if (type === 'online') return { display: 'Online (Video)', submitValue: 'online' };
            if (type === 'phone' || type === 'telephone') return { display: 'Telephone', submitValue: 'telephone' };
            return { display: 'In Person', submitValue: 'in_person' };
        }

        function updateServiceDetails() {
            const card = serviceCardsRoot.querySelector('.pb-service-card.is-selected');
            if (!card || !card.dataset.serviceId) {
                serviceDetails.style.display = 'none';
                return;
            }
            const description = card.dataset.description || '';
            const durNum = card.dataset.duration || '30';
            const duration = durNum + ' minutes';
            const price = '£' + parseFloat(card.dataset.price || 0).toFixed(2);
            const consultationType = card.dataset.consultationType || 'in_person';
            const serviceName = card.dataset.name || '';

            const descriptionEl = document.getElementById('service-description');
            if (description && description.trim()) {
                descriptionEl.textContent = description;
                descriptionEl.style.display = 'block';
            } else {
                descriptionEl.style.display = 'none';
            }

            const isNc = card.dataset.isNonConsultation === '1';
            const ctRow = document.getElementById('service-consultation-type-row');
            const durRow = document.getElementById('service-duration-row');
            if (ctRow) {
                ctRow.style.display = isNc ? 'none' : '';
            }
            if (durRow) {
                durRow.style.display = isNc ? 'none' : '';
            }
            if (!isNc) {
                const ctResult = getConsultationTypeDisplay(consultationType, serviceName);
                document.getElementById('service-consultation-type').textContent = ctResult.display;
                document.getElementById('consultation-type-input').value = ctResult.submitValue;
                document.getElementById('service-duration').textContent = duration;
            }
            document.getElementById('service-price').textContent = price;
            serviceDetails.style.display = 'block';
        }

        function applyNonConsultationService() {
            selectedNonConsultation = true;
            if (form) {
                form.action = selectDatetimeUrl;
            }
            if (appointmentDateInput) {
                appointmentDateInput.removeAttribute('required');
                appointmentDateInput.value = '';
            }
            if (appointmentTimeInput) {
                appointmentTimeInput.removeAttribute('required');
                appointmentTimeInput.value = '';
            }
            scheduleSelectionCard.style.display = 'none';
            continueBtn.disabled = false;
        }

        function applyConsultationService() {
            selectedNonConsultation = false;
            if (form) {
                form.action = patientDetailsUrl;
            }
            if (appointmentDateInput) {
                appointmentDateInput.setAttribute('required', 'required');
            }
            if (appointmentTimeInput) {
                appointmentTimeInput.setAttribute('required', 'required');
            }
            loadSchedule();
        }

        function loadSchedule() {
            if (!selectedDoctorId || !selectedServiceId || !serviceIdInput.value) {
                return;
            }
            if (selectedNonConsultation) {
                return;
            }

            clearSlotsCache();
            scheduleSelectionCard.style.display = 'block';

            buildDateRange();
            currentMonthIndex = 0;
            selectedDate = null;
            selectedTime = null;
            document.getElementById('appointment-date').value = '';
            document.getElementById('appointment-time').value = '';
            continueBtn.disabled = true;

            renderMonthNavigation();
            renderDates();
            hydrateCurrentMonth();
        }

        function selectServiceCard(card) {
            if (!card || !serviceIdInput || !serviceCardsRoot) {
                return;
            }
            serviceCardsRoot.querySelectorAll('.pb-service-card').forEach(function(b) {
                b.classList.remove('is-selected');
                b.setAttribute('aria-pressed', 'false');
            });
            card.classList.add('is-selected');
            card.setAttribute('aria-pressed', 'true');
            serviceIdInput.value = card.dataset.serviceId || '';
            selectedServiceId = serviceIdInput.value || null;
            clearSlotsCache();
            updateServiceDetails();
            if (card.dataset.isNonConsultation === '1') {
                applyNonConsultationService();
            } else {
                applyConsultationService();
            }
            syncPbServiceCardTabOrder(serviceCardsRoot);
        }

        function loadDoctorServices(doctorId) {
            const preselectedServiceId = selectedServiceId;
            var savedMeta = null;
            if (preselectedServiceId && serviceCardsRoot) {
                var escPre = (typeof CSS !== 'undefined' && CSS.escape)
                    ? CSS.escape(String(preselectedServiceId))
                    : String(preselectedServiceId).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
                var preCard = serviceCardsRoot.querySelector('.pb-service-card[data-service-id="' + escPre + '"]');
                if (preCard && preCard.dataset.duration) {
                    savedMeta = {
                        id: preCard.dataset.serviceId,
                        name: preCard.dataset.name || '',
                        duration: preCard.dataset.duration,
                        price: preCard.dataset.price,
                        description: preCard.dataset.description || '',
                        consultation_type: preCard.dataset.consultationType || 'in_person',
                        is_non_consultation: preCard.dataset.isNonConsultation === '1',
                    };
                }
            }

            serviceSelectionCard.style.display = 'block';
            if (serviceLoadingMsg) {
                serviceLoadingMsg.style.display = 'block';
            }
            if (serviceCardsRoot) {
                serviceCardsRoot.style.opacity = '0.65';
                serviceCardsRoot.style.pointerEvents = 'none';
            }
            if (serviceIdInput) {
                serviceIdInput.value = '';
                serviceIdInput.setAttribute('required', 'required');
            }
            selectedServiceId = null;
            if (serviceCardsRoot) {
                serviceCardsRoot.innerHTML = '';
            }
            scheduleSelectionCard.style.display = 'none';
            continueBtn.disabled = true;

            fetch(`{{ url('/api/public/doctors') }}/${doctorId}/services`, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (serviceLoadingMsg) {
                    serviceLoadingMsg.style.display = 'none';
                }
                if (serviceCardsRoot) {
                    serviceCardsRoot.style.opacity = '';
                    serviceCardsRoot.style.pointerEvents = '';
                    serviceCardsRoot.innerHTML = '';
                }

                var appendedIds = {};
                if (savedMeta) {
                    renderDoctorServiceCard(savedMeta, true);
                    appendedIds[String(savedMeta.id)] = true;
                }

                if (data.services && data.services.length > 0) {
                    data.services.forEach(function(service) {
                        if (appendedIds[String(service.id)]) {
                            var escId = (typeof CSS !== 'undefined' && CSS.escape)
                                ? CSS.escape(String(service.id))
                                : String(service.id);
                            var existing = serviceCardsRoot.querySelector('.pb-service-card[data-service-id="' + escId + '"]');
                            if (existing) {
                                existing.dataset.consultationType = service.consultation_type || 'in_person';
                            }
                            return;
                        }
                        var isPre = preselectedServiceId && String(service.id) === String(preselectedServiceId) && !savedMeta;
                        renderDoctorServiceCard(service, isPre);
                        appendedIds[String(service.id)] = true;
                    });

                    var cards = serviceCardsRoot.querySelectorAll('.pb-service-card');
                    if (!serviceIdInput.value && cards.length === 1) {
                        selectServiceCard(cards[0]);
                    } else if (serviceIdInput.value) {
                        updateServiceDetails();
                        const activeCard = serviceCardsRoot.querySelector('.pb-service-card.is-selected');
                        if (activeCard && activeCard.dataset.isNonConsultation === '1') {
                            applyNonConsultationService();
                        } else {
                            applyConsultationService();
                        }
                    }
                } else {
                    if (!savedMeta && serviceCardsRoot) {
                        serviceCardsRoot.innerHTML = '<p class="text-danger small mb-0">No services available for this doctor.</p>';
                    } else if (savedMeta) {
                        updateServiceDetails();
                        if (savedMeta.is_non_consultation) {
                            applyNonConsultationService();
                        } else {
                            applyConsultationService();
                        }
                    }
                }
                syncPbServiceCardTabOrder(serviceCardsRoot);
            })
            .catch(function(error) {
                console.error('Error loading services:', error);
                if (serviceLoadingMsg) {
                    serviceLoadingMsg.style.display = 'none';
                }
                if (serviceCardsRoot) {
                    serviceCardsRoot.style.opacity = '';
                    serviceCardsRoot.style.pointerEvents = '';
                }
                if (!savedMeta && serviceCardsRoot) {
                    serviceCardsRoot.innerHTML = '<p class="text-danger small mb-0">Error loading services. Please try again.</p>';
                } else if (savedMeta) {
                    renderDoctorServiceCard(savedMeta, true);
                    updateServiceDetails();
                    if (savedMeta.is_non_consultation) {
                        applyNonConsultationService();
                    } else {
                        applyConsultationService();
                    }
                }
                syncPbServiceCardTabOrder(serviceCardsRoot);
            });
        }

        // If service is pre-selected (from service booking link), set it up after handlers are wired
        function initPreselectedServiceBookingLink() {
            @if(isset($service) && isset($doctor))
            selectedDoctorId = String({{ $doctor->id }});
            if (doctorSelect) {
                doctorSelect.value = selectedDoctorId;
            }
            serviceSelectionCard.style.display = 'block';
            if (serviceIdInput) {
                serviceIdInput.value = String({{ $service->id }});
            }
            selectedServiceId = serviceIdInput ? serviceIdInput.value : null;
            const preselectedCard = serviceCardsRoot
                ? serviceCardsRoot.querySelector('.pb-service-card.is-selected')
                : null;
            if (preselectedCard) {
                selectServiceCard(preselectedCard);
            } else {
                updateServiceDetails();
                @if($service->isNonConsultation())
                applyNonConsultationService();
                @else
                applyConsultationService();
                @endif
            }
            syncPbServiceCardTabOrder(serviceCardsRoot);
            @endif
        }

        @if(isset($doctor) && $doctors->count() == 1 && !isset($service))
        // If single doctor is pre-selected (from doctor link), load services immediately
        selectedDoctorId = {{ $doctor->id }};
        if (doctorSelect) {
            doctorSelect.value = {{ $doctor->id }};
        }
        loadDoctorServices(selectedDoctorId);
        @elseif(!isset($service) && $doctors->count() == 1)
        // Auto-select if only one doctor
        if (doctorSelect) {
            const singleDoctor = doctorSelect.options[1]; // First option after "Select..."
            if (singleDoctor) {
                selectedDoctorId = singleDoctor.value;
                doctorSelect.value = selectedDoctorId;
                loadDoctorServices(selectedDoctorId);
            }
        }
        @endif

        // Doctor selection
        if (doctorSelect) {
        doctorSelect.addEventListener('change', function() {
            selectedDoctorId = this.value;
            if (selectedDoctorId) {
                if (serviceIdInput) {
                    serviceIdInput.setAttribute('required', 'required');
                }
                loadDoctorServices(selectedDoctorId);
            } else {
                serviceSelectionCard.style.display = 'none';
                scheduleSelectionCard.style.display = 'none';
                continueBtn.disabled = true;
                if (serviceCardsRoot) {
                    serviceCardsRoot.innerHTML = '';
                }
                if (serviceIdInput) {
                    serviceIdInput.value = '';
                    serviceIdInput.removeAttribute('required');
                }
                selectedServiceId = null;
                clearSlotsCache();
                serviceDetails.style.display = 'none';
            }
        });
        }

        if (serviceCardsRoot) {
            serviceCardsRoot.addEventListener('click', function(e) {
                const card = e.target.closest('.pb-service-card');
                if (!card) {
                    return;
                }
                selectServiceCard(card);
            });
            bindPbServiceGroupKeyboard(serviceCardsRoot, selectServiceCard);
        }

        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        if (prevMonthBtn && nextMonthBtn) {
        prevMonthBtn.addEventListener('click', function() {
            if (currentMonthIndex > 0) {
                currentMonthIndex--;
                selectedDate = null;
                renderMonthNavigation();
                renderDates();
                hydrateCurrentMonth();
            }
        });
        nextMonthBtn.addEventListener('click', function() {
            if (currentMonthIndex < monthKeys.length - 1) {
                currentMonthIndex++;
                selectedDate = null;
                renderMonthNavigation();
                renderDates();
                hydrateCurrentMonth();
            }
        });
        }

        function buildDateRange() {
            dateRange = [];
            monthKeys = [];
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const seen = {};
            for (let i = 0; i < RANGE_DAYS; i++) {
                const d = new Date(today);
                d.setDate(today.getDate() + i);
                const ymd = d.toISOString().split('T')[0];
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
            const dateDisplay = document.getElementById('date-display');
            dateDisplay.innerHTML = '';
            const visibleDates = getVisibleMonthDates();
            visibleDates.forEach((dateStr) => {
                const date = new Date(dateStr);
                const dayNames = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

                const unavailable = Array.isArray(slotsByDate[dateStr]) && slotsByDate[dateStr].length === 0;
                const dateItem = document.createElement('div');
                dateItem.className = 'date-item' + (dateStr === selectedDate ? ' selected' : '') + (unavailable ? ' unavailable' : '');
                dateItem.dataset.date = dateStr;
                dateItem.innerHTML = `
                    <div class="date-day">${dayNames[date.getDay()]}</div>
                    <div class="date-date">${monthNames[date.getMonth()]} ${date.getDate()}</div>
                `;
                dateItem.addEventListener('click', function() {
                    if (this.classList.contains('unavailable')) {
                        return;
                    }
                    loadTimeSlotsForDate(this.dataset.date);
                });
                dateDisplay.appendChild(dateItem);
            });
        }

        function fetchSlotsForDate(dateStr) {
            if (Array.isArray(slotsByDate[dateStr])) {
                return Promise.resolve(slotsByDate[dateStr]);
            }
            return fetch(`{{ url('/api/public/doctors') }}/${selectedDoctorId}/slots?date=${dateStr}&service_id=${selectedServiceId}`, {
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
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
                        loadTimeSlotsForDate(firstAvailable);
                    } else {
                        timeSlotsPicker.innerHTML = '';
                        timeSlotsPicker.style.display = 'none';
                        noSlotsMessage.style.display = 'block';
                    }
                })
                .catch(() => {
                    loadingSlots.style.display = 'none';
                });
        }

        function loadTimeSlotsForDate(date) {
            if (!selectedDoctorId || !selectedServiceId) return;
            loadingSlots.style.display = 'block';
            timeSlotsPicker.innerHTML = '';
            timeSlotsPicker.style.display = 'none';
            noSlotsMessage.style.display = 'none';
            fetchSlotsForDate(date).then((slots) => {
                loadingSlots.style.display = 'none';
                selectedDate = date;
                selectedTime = null;
                document.getElementById('appointment-date').value = selectedDate;
                document.getElementById('appointment-time').value = '';
                continueBtn.disabled = true;
                renderDates();

                if (!slots || slots.length === 0) {
                    noSlotsMessage.style.display = 'block';
                    return;
                }

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
                slots.forEach(function(slot) {
                    const o = document.createElement('option');
                    o.value = slot.start;
                    o.textContent = slot.display || slot.start;
                    sel.appendChild(o);
                });
                const selectedSvcCard = serviceCardsRoot.querySelector('.pb-service-card.is-selected');
                const duration = (selectedSvcCard && selectedSvcCard.dataset.duration) ? selectedSvcCard.dataset.duration : '30';
                const hint = document.createElement('p');
                hint.className = 'time-slot-hint mb-0';
                hint.textContent = 'Each option is one appointment (' + duration + ' minutes).';
                timeSlotsPicker.appendChild(lbl);
                timeSlotsPicker.appendChild(sel);
                timeSlotsPicker.appendChild(hint);
                timeSlotsPicker.style.display = 'block';
                sel.addEventListener('change', function() {
                    if (sel.value) {
                        selectedTime = sel.value;
                        document.getElementById('appointment-time').value = selectedTime;
                        continueBtn.disabled = false;
                    } else {
                        selectedTime = null;
                        document.getElementById('appointment-time').value = '';
                        continueBtn.disabled = true;
                    }
                });
            });
        }

        initPreselectedServiceBookingLink();

        // Form submission
        if (form) {
        form.addEventListener('submit', function(e) {
            if (!selectedDoctorId || !selectedServiceId) {
                e.preventDefault();
                alert('Please select a doctor and service.');
                return false;
            }
            if (selectedNonConsultation) {
                return true;
            }
            if (!selectedDate || !selectedTime) {
                e.preventDefault();
                alert('Please complete all selections before continuing.');
                return false;
            }
        });
        }
    });
</script>
@endsection
