@extends('layouts.doctor')

@section('title', 'My Schedule & Availability')
@section('page-title', 'My Schedule')
@section('page-subtitle', 'Manage your working hours and availability for patient bookings')

@section('content')
<div class="fade-in-up">
    <div class="row">
        <!-- Weekly Availability Section -->
        <div class="col-lg-8">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-calendar-week me-2"></i>Weekly Availability
                        </h5>
                        <span class="badge bg-info">
                            <i class="fas fa-info-circle me-1"></i>Visible to patients during booking
                        </span>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="alert alert-light border mb-4">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        <strong>How it works:</strong> For each day you can add one or more <strong>time windows</strong> (e.g. morning 09:00–12:00, afternoon 14:00–17:00, evening 18:00–21:00). Patients can only book during these windows. Block specific dates for time off in the section on the right.
                    </div>

                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <i class="fas fa-video me-2"></i>
                            <strong>Different hours for online / telephone / in-person?</strong>
                            Set availability per consultation type so patients only see slots that match the service.
                        </div>
                        <a href="{{ route('staff.schedule.availability-rules.index') }}" class="btn btn-sm btn-info text-white ms-3 flex-shrink-0">
                            Manage by type
                        </a>
                    </div>

                    <form action="{{ route('staff.schedule.update-availability') }}" method="POST" id="availabilityForm">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 140px;">Day</th>
                                        <th style="width: 100px;" class="text-center">Available</th>
                                        <th>Time windows</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysOfWeek as $day)
                                        @php
                                            $dayData = $availability[$day] ?? ['available' => false, 'sessions' => [['start' => '09:00', 'end' => '17:00']]];
                                            $isAvailable = $dayData['available'] ?? false;
                                            $sessions = $dayData['sessions'] ?? [];
                                            if (empty($sessions) && $isAvailable) {
                                                $sessions = [['start' => '09:00', 'end' => '17:00']];
                                            }
                                        @endphp
                                        <tr class="day-row {{ $isAvailable ? '' : 'table-secondary' }}" data-day="{{ $day }}">
                                            <td>
                                                <strong class="text-capitalize">
                                                    <i class="fas fa-calendar-day me-2 text-muted"></i>{{ ucfirst($day) }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input type="hidden" name="availability[{{ $day }}][available]" value="0">
                                                    <input type="checkbox"
                                                           class="form-check-input day-toggle"
                                                           id="available_{{ $day }}"
                                                           name="availability[{{ $day }}][available]"
                                                           value="1"
                                                           {{ $isAvailable ? 'checked' : '' }}
                                                           style="width: 3em; height: 1.5em;">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="sessions-container {{ $isAvailable ? '' : 'opacity-50' }}" data-day="{{ $day }}">
                                                    @foreach($sessions as $idx => $session)
                                                        <div class="d-flex align-items-center gap-2 mb-2 session-row">
                                                            <input type="time" class="form-control form-control-sm session-start" name="availability[{{ $day }}][sessions][{{ $idx }}][start]" value="{{ $session['start'] ?? '09:00' }}" style="width: 120px; min-width: 120px;" {{ $isAvailable ? '' : 'disabled' }}>
                                                            <span class="text-muted">to</span>
                                                            <input type="time" class="form-control form-control-sm session-end" name="availability[{{ $day }}][sessions][{{ $idx }}][end]" value="{{ $session['end'] ?? '17:00' }}" style="width: 120px; min-width: 120px;" {{ $isAvailable ? '' : 'disabled' }}>
                                                            <button type="button" class="btn btn-sm btn-outline-danger remove-session" title="Remove this window"><i class="fas fa-minus"></i></button>
                                                        </div>
                                                    @endforeach
                                                    <button type="button" class="btn btn-sm btn-outline-primary add-session" data-day="{{ $day }}" title="Add another time window"><i class="fas fa-plus me-1"></i>Add window</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" id="copyMondayBtn">
                                    <i class="fas fa-copy me-1"></i>Copy Monday to All Weekdays
                                </button>
                            </div>
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-1"></i>Save Availability
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Upcoming Schedule Preview -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Next 1 Month Preview
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row g-2 schedule-preview-month">
                        @foreach($schedulePreviewDates as $date)
                            @php
                                $dayName = strtolower($date->format('l'));
                                $dayData = $availability[$dayName] ?? ['available' => false];
                                $isAvailable = $dayData['available'] ?? false;
                                $appointmentCount = $upcomingAppointments[$date->format('Y-m-d')] ?? 0;
                                $isBlocked = $blockedDates->contains(function($blocked) use ($date) {
                                    return $blocked->exception_date->format('Y-m-d') === $date->format('Y-m-d');
                                });
                            @endphp
                            <div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
                                <div class="text-center p-2 rounded-3 schedule-preview-day {{ $isBlocked ? 'bg-danger bg-opacity-10 border border-danger' : ($isAvailable ? 'bg-success bg-opacity-10 border border-success' : 'bg-secondary bg-opacity-10 border') }}">
                                    <div class="fw-bold small text-uppercase {{ $date->isToday() ? 'text-primary' : '' }}">
                                        {{ $date->isToday() ? 'Today' : $date->format('D') }}
                                    </div>
                                    <div class="fs-6 fw-bold my-1">{{ $date->format('j') }}</div>
                                    <div class="small text-muted">{{ $date->format('M') }}</div>
                                    @if($isBlocked)
                                        <span class="badge bg-danger mt-1 small">Blocked</span>
                                    @elseif($isAvailable)
                                        <span class="badge bg-success mt-1 small">{{ $appointmentCount }} appts</span>
                                    @else
                                        <span class="badge bg-secondary mt-1 small">Off</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocked Dates / Days Off Section -->
        <div class="col-lg-4">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-calendar-times me-2"></i>Blocked Dates
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-muted small mb-3">
                        Block specific dates when you're unavailable (holidays, vacation, personal days).
                    </p>

                    <!-- Add New Blocked Date Form -->
                    <form id="addBlockedDateForm" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                <i class="fas fa-calendar-plus me-1"></i>Add Blocked Date
                            </label>
                            <input type="date"
                                   class="form-control"
                                   name="exception_date"
                                   id="newBlockedDate"
                                   min="{{ now()->format('Y-m-d') }}"
                                   required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">
                                <i class="fas fa-comment me-1"></i>Reason (Optional)
                            </label>
                            <input type="text"
                                   class="form-control"
                                   name="reason"
                                   id="blockReason"
                                   placeholder="e.g., Annual Leave, Conference">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold d-block">
                                <i class="fas fa-clock me-1"></i>Block Type
                            </label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input block-type-radio" type="radio"
                                       name="block_type" id="blockTypeWholeDay" value="whole_day" checked>
                                <label class="form-check-label small" for="blockTypeWholeDay">Whole day</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input block-type-radio" type="radio"
                                       name="block_type" id="blockTypeInterval" value="interval">
                                <label class="form-check-label small" for="blockTypeInterval">Specific time(s)</label>
                            </div>
                        </div>

                        <div id="blockIntervalsWrap" class="mb-3 d-none">
                            <label class="form-label small fw-bold">Blocked time intervals</label>
                            <div id="blockIntervalsList"></div>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="addIntervalRow">
                                <i class="fas fa-plus me-1"></i>Add another interval
                            </button>
                            <div class="form-text small">Slots outside these intervals stay bookable.</div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-ban me-1"></i>Block This Date
                        </button>
                    </form>

                    <hr>

                    <!-- Existing Blocked Dates -->
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-list me-1"></i>Upcoming Blocked Dates
                    </h6>

                    <div id="blockedDatesList">
                        @if($blockedDates->count() > 0)
                            @foreach($blockedDates as $blocked)
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-2 blocked-date-item" data-id="{{ $blocked->id }}">
                                    <div>
                                        <div class="fw-bold small">{{ $blocked->exception_date->format('l, j M Y') }}</div>
                                        @if($blocked->is_all_day)
                                            <span class="badge bg-danger-subtle text-danger small">All day</span>
                                        @elseif($blocked->start_time && $blocked->end_time)
                                            <span class="badge bg-warning-subtle text-warning-emphasis small">{{ $blocked->start_time->format('H:i') }} - {{ $blocked->end_time->format('H:i') }}</span>
                                        @endif
                                        @if($blocked->reason)
                                            <small class="text-muted d-block">{{ $blocked->reason }}</small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-blocked-date" data-id="{{ $blocked->id }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-muted" id="noBlockedDates">
                                <i class="fas fa-calendar-check fa-2x mb-2 opacity-50"></i>
                                <p class="mb-0 small">No blocked dates</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>This Week Summary
                    </h5>
                </div>
                <div class="doctor-card-body">
                    @php
                        $workingDays = collect($availability)->filter(fn($day) => $day['available'] ?? false)->count();
                        $totalHours = collect($availability)->filter(fn($day) => $day['available'] ?? false)->sum(function($day) {
                            $sessions = $day['sessions'] ?? [];
                            if (empty($sessions)) {
                                $start = Carbon\Carbon::parse($day['start'] ?? '09:00');
                                $end = Carbon\Carbon::parse($day['end'] ?? '17:00');
                                return $end->diffInMinutes($start) / 60;
                            }
                            $mins = 0;
                            foreach ($sessions as $s) {
                                $sStart = Carbon\Carbon::parse($s['start']);
                                $sEnd = Carbon\Carbon::parse($s['end']);
                                $mins += $sEnd->diffInMinutes($sStart);
                            }
                            return $mins / 60;
                        });
                    @endphp

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 rounded-3 summary-stat-card">
                                <div class="fs-3 fw-bold text-white">{{ $workingDays }}</div>
                                <small class="text-white text-opacity-90">Working Days</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded-3 summary-stat-card">
                                <div class="fs-3 fw-bold text-white">{{ $totalHours }}</div>
                                <small class="text-white text-opacity-90">Hours/Week</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded-3 summary-stat-card">
                                <div class="fs-3 fw-bold text-white">{{ $blockedDates->count() }}</div>
                                <small class="text-white text-opacity-90">Blocked Dates</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded-3 summary-stat-card">
                                <div class="fs-3 fw-bold text-white">{{ $upcomingAppointments->sum() }}</div>
                                <small class="text-white text-opacity-90">Upcoming Appts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.summary-stat-card {
    background: var(--doctor-primary, #0d6efd);
    color: #fff;
}
.schedule-preview-day {
    min-height: 5.5rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle day availability
    document.querySelectorAll('.day-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const row = this.closest('.day-row');
            const container = row.querySelector('.sessions-container');
            const inputs = container.querySelectorAll('input, button');

            if (this.checked) {
                row.classList.remove('table-secondary');
                container.classList.remove('opacity-50');
                inputs.forEach(input => { if (input) input.disabled = false; });
            } else {
                row.classList.add('table-secondary');
                container.classList.add('opacity-50');
                inputs.forEach(input => { if (input) input.disabled = true; });
            }
        });
    });

    // Reindex session inputs for a day (0, 1, 2, ...)
    function reindexSessions(day) {
        const container = document.querySelector('.sessions-container[data-day="' + day + '"]');
        if (!container) return;
        const rows = container.querySelectorAll('.session-row');
        rows.forEach(function(row, idx) {
            row.querySelector('.session-start').setAttribute('name', 'availability[' + day + '][sessions][' + idx + '][start]');
            row.querySelector('.session-end').setAttribute('name', 'availability[' + day + '][sessions][' + idx + '][end]');
        });
    }

    // Add session row
    document.querySelectorAll('.add-session').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const day = this.getAttribute('data-day');
            const container = this.closest('.sessions-container');
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 mb-2 session-row';
            const idx = container.querySelectorAll('.session-row').length;
            row.innerHTML = '<input type="time" class="form-control form-control-sm session-start" name="availability[' + day + '][sessions][' + idx + '][start]" value="09:00" style="width: 120px; min-width: 120px;">' +
                '<span class="text-muted">to</span>' +
                '<input type="time" class="form-control form-control-sm session-end" name="availability[' + day + '][sessions][' + idx + '][end]" value="17:00" style="width: 120px; min-width: 120px;">' +
                '<button type="button" class="btn btn-sm btn-outline-danger remove-session" title="Remove this window"><i class="fas fa-minus"></i></button>';
            this.parentElement.insertBefore(row, this);
            row.querySelector('.remove-session').addEventListener('click', function() {
                if (container.querySelectorAll('.session-row').length <= 1) return;
                row.remove();
                reindexSessions(day);
            });
            reindexSessions(day);
        });
    });

    // Remove session row
    document.querySelectorAll('.remove-session').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const row = this.closest('.session-row');
            const container = row.closest('.sessions-container');
            const day = container.getAttribute('data-day');
            if (container.querySelectorAll('.session-row').length <= 1) return;
            row.remove();
            reindexSessions(day);
        });
    });

    // Copy Monday to all weekdays (copy all time windows)
    document.getElementById('copyMondayBtn').addEventListener('click', function() {
        const mondayRow = document.querySelector('[data-day="monday"]');
        const mondayAvailable = mondayRow.querySelector('.day-toggle').checked;
        const mondayContainer = mondayRow.querySelector('.sessions-container');
        const mondaySessions = [];
        mondayContainer.querySelectorAll('.session-row').forEach(function(r) {
            mondaySessions.push({
                start: r.querySelector('.session-start').value,
                end: r.querySelector('.session-end').value
            });
        });
        if (mondaySessions.length === 0) {
            mondaySessions.push({ start: '09:00', end: '17:00' });
        }

        const weekdays = ['tuesday', 'wednesday', 'thursday', 'friday'];
        weekdays.forEach(function(day) {
            const row = document.querySelector('[data-day="' + day + '"]');
            const toggle = row.querySelector('.day-toggle');
            const container = row.querySelector('.sessions-container');
            toggle.checked = mondayAvailable;
            toggle.dispatchEvent(new Event('change'));

            container.querySelectorAll('.session-row').forEach(r => r.remove());
            const addBtn = container.querySelector('.add-session');
            mondaySessions.forEach(function(sess, i) {
                const sessionRow = document.createElement('div');
                sessionRow.className = 'd-flex align-items-center gap-2 mb-2 session-row';
                sessionRow.innerHTML = '<input type="time" class="form-control form-control-sm session-start" value="' + sess.start + '" style="width: 120px; min-width: 120px;">' +
                    '<span class="text-muted">to</span>' +
                    '<input type="time" class="form-control form-control-sm session-end" value="' + sess.end + '" style="width: 120px; min-width: 120px;">' +
                    '<button type="button" class="btn btn-sm btn-outline-danger remove-session" title="Remove this window"><i class="fas fa-minus"></i></button>';
                container.insertBefore(sessionRow, addBtn);
                sessionRow.querySelector('.remove-session').addEventListener('click', function() {
                    if (container.querySelectorAll('.session-row').length <= 1) return;
                    sessionRow.remove();
                    reindexSessions(day);
                });
            });
            reindexSessions(day);
        });

        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Monday schedule copied to all weekdays.',
            toast: true,
            position: 'top-end',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // --- Block type toggle + time-interval rows ---
    const blockIntervalsWrap = document.getElementById('blockIntervalsWrap');
    const blockIntervalsList = document.getElementById('blockIntervalsList');

    function buildIntervalRow() {
        const row = document.createElement('div');
        row.className = 'interval-row d-flex align-items-center gap-2 mb-2';
        row.innerHTML = `
            <input type="time" class="form-control form-control-sm interval-start" aria-label="Start time">
            <span class="small text-muted">to</span>
            <input type="time" class="form-control form-control-sm interval-end" aria-label="End time">
            <button type="button" class="btn btn-sm btn-outline-danger remove-interval-row" aria-label="Remove interval">
                <i class="fas fa-times"></i>
            </button>
        `;
        row.querySelector('.remove-interval-row').addEventListener('click', function() {
            row.remove();
            if (blockIntervalsList.querySelectorAll('.interval-row').length === 0) {
                blockIntervalsList.appendChild(buildIntervalRow());
            }
        });
        return row;
    }

    function getSelectedBlockType() {
        const checked = document.querySelector('input[name="block_type"]:checked');
        return checked ? checked.value : 'whole_day';
    }

    function syncBlockTypeUI() {
        if (getSelectedBlockType() === 'interval') {
            blockIntervalsWrap.classList.remove('d-none');
            if (blockIntervalsList.querySelectorAll('.interval-row').length === 0) {
                blockIntervalsList.appendChild(buildIntervalRow());
            }
        } else {
            blockIntervalsWrap.classList.add('d-none');
        }
    }

    document.querySelectorAll('.block-type-radio').forEach(function(radio) {
        radio.addEventListener('change', syncBlockTypeUI);
    });
    document.getElementById('addIntervalRow').addEventListener('click', function() {
        blockIntervalsList.appendChild(buildIntervalRow());
    });

    // Add blocked date form submission
    document.getElementById('addBlockedDateForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const dateInput = document.getElementById('newBlockedDate');
        const reasonInput = document.getElementById('blockReason');

        if (!dateInput.value) {
            Swal.fire({
                icon: 'error',
                title: 'Date Required',
                text: 'Please select a date to block.'
            });
            return;
        }

        const blockType = getSelectedBlockType();
        const intervals = [];

        if (blockType === 'interval') {
            const rows = blockIntervalsList.querySelectorAll('.interval-row');
            for (const row of rows) {
                const start = row.querySelector('.interval-start').value;
                const end = row.querySelector('.interval-end').value;
                if (!start && !end) {
                    continue; // skip empty rows
                }
                if (!start || !end) {
                    Swal.fire({ icon: 'error', title: 'Incomplete Interval', text: 'Each interval needs both a start and end time.' });
                    return;
                }
                if (end <= start) {
                    Swal.fire({ icon: 'error', title: 'Invalid Interval', text: `End time must be after start time (${start} - ${end}).` });
                    return;
                }
                intervals.push({ start, end });
            }
            if (intervals.length === 0) {
                Swal.fire({ icon: 'error', title: 'No Intervals', text: 'Add at least one time interval to block.' });
                return;
            }
        }

        const formData = new FormData(form);
        formData.delete('block_type');
        formData.set('is_all_day', blockType === 'interval' ? '0' : '1');
        intervals.forEach(function(interval, i) {
            formData.append(`intervals[${i}][start]`, interval.start);
            formData.append(`intervals[${i}][end]`, interval.end);
        });

        fetch('{{ route("staff.schedule.add-blocked-date") }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async function(response) {
            const text = await response.text();
            var data = {};
            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                throw new Error(response.status === 419 ? 'Session expired. Please refresh the page and try again.' : 'Unexpected server response.');
            }
            if (!response.ok) {
                var msg = data.message;
                if (!msg && data.errors) {
                    var first = Object.values(data.errors)[0];
                    msg = Array.isArray(first) ? first[0] : first;
                }
                throw new Error(msg || 'Could not save blocked date.');
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                // Add to list
                const listContainer = document.getElementById('blockedDatesList');
                const noBlockedDates = document.getElementById('noBlockedDates');
                if (noBlockedDates) {
                    noBlockedDates.remove();
                }

                const date = new Date(dateInput.value);
                const formattedDate = date.toLocaleDateString('en-GB', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });

                const createdList = (data.exceptions && data.exceptions.length) ? data.exceptions : [data.exception];
                createdList.forEach(function(exc) {
                    if (!exc) return;
                    let badge;
                    if (exc.is_all_day) {
                        badge = '<span class="badge bg-danger-subtle text-danger small">All day</span>';
                    } else if (exc.start_time && exc.end_time) {
                        badge = `<span class="badge bg-warning-subtle text-warning-emphasis small">${exc.start_time} - ${exc.end_time}</span>`;
                    } else {
                        badge = '';
                    }

                    const newItem = document.createElement('div');
                    newItem.className = 'd-flex justify-content-between align-items-center p-2 bg-light rounded mb-2 blocked-date-item';
                    newItem.setAttribute('data-id', exc.id);
                    newItem.innerHTML = `
                        <div>
                            <div class="fw-bold small">${formattedDate}</div>
                            ${badge}
                            ${reasonInput.value ? `<small class="text-muted d-block">${reasonInput.value}</small>` : ''}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-blocked-date" data-id="${exc.id}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    newItem.querySelector('.remove-blocked-date').addEventListener('click', function() {
                        removeBlockedDate(this.getAttribute('data-id'));
                    });
                    listContainer.prepend(newItem);
                });

                // Reset form
                dateInput.value = '';
                reasonInput.value = '';
                document.getElementById('blockTypeWholeDay').checked = true;
                blockIntervalsList.innerHTML = '';
                syncBlockTypeUI();

                Swal.fire({
                    icon: 'success',
                    title: 'Date Blocked',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    showConfirmButton: false
                });

                // Reload page to update preview
                setTimeout(() => location.reload(), 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to block date. Please try again.'
            });
        });
    });

    // Remove blocked date
    function removeBlockedDate(id) {
        Swal.fire({
            title: 'Remove Blocked Date?',
            text: 'This date will become available for bookings again.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('staff/schedule/blocked-date') }}/${id}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async function(response) {
                    const text = await response.text();
                    var data = {};
                    try {
                        data = text ? JSON.parse(text) : {};
                    } catch (e) {
                        throw new Error(response.status === 419 ? 'Session expired. Please refresh the page and try again.' : 'Unexpected server response.');
                    }
                    if (!response.ok) {
                        throw new Error(data.message || 'Could not remove blocked date.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`.blocked-date-item[data-id="${id}"]`);
                        if (item) {
                            item.remove();
                        }

                        // Check if list is empty
                        const listContainer = document.getElementById('blockedDatesList');
                        if (listContainer.querySelectorAll('.blocked-date-item').length === 0) {
                            listContainer.innerHTML = `
                                <div class="text-center py-4 text-muted" id="noBlockedDates">
                                    <i class="fas fa-calendar-check fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0 small">No blocked dates</p>
                                </div>
                            `;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Removed',
                            text: 'Blocked date has been removed.',
                            toast: true,
                            position: 'top-end',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Reload page to update preview
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to remove blocked date.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to remove blocked date.'
                    });
                });
            }
        });
    }

    // Attach remove event listeners to existing items
    document.querySelectorAll('.remove-blocked-date').forEach(function(btn) {
        btn.addEventListener('click', function() {
            removeBlockedDate(this.getAttribute('data-id'));
        });
    });
});
</script>
@endpush
