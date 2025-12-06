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
                        <strong>How it works:</strong> Set your regular working hours below. Patients will only be able to book appointments during these times. You can block specific dates for holidays or time off in the section on the right.
                    </div>

                    <form action="{{ route('staff.schedule.update-availability') }}" method="POST" id="availabilityForm">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 150px;">Day</th>
                                        <th style="width: 100px;" class="text-center">Available</th>
                                        <th>Working Hours</th>
                                        <th>Break Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daysOfWeek as $day)
                                        @php
                                            $dayData = $availability[$day] ?? ['available' => false, 'start' => '09:00', 'end' => '17:00', 'breaks' => []];
                                            $isAvailable = $dayData['available'] ?? false;
                                            $startTime = $dayData['start'] ?? '09:00';
                                            $endTime = $dayData['end'] ?? '17:00';
                                            $breaks = $dayData['breaks'] ?? [];
                                            $firstBreak = $breaks[0] ?? ['start' => '12:00', 'end' => '13:00'];
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
                                                <div class="d-flex align-items-center gap-2 working-hours {{ $isAvailable ? '' : 'opacity-50' }}">
                                                    <input type="time"
                                                           class="form-control form-control-sm"
                                                           name="availability[{{ $day }}][start]"
                                                           value="{{ $startTime }}"
                                                           style="width: 120px;"
                                                           {{ $isAvailable ? '' : 'disabled' }}>
                                                    <span class="text-muted">to</span>
                                                    <input type="time"
                                                           class="form-control form-control-sm"
                                                           name="availability[{{ $day }}][end]"
                                                           value="{{ $endTime }}"
                                                           style="width: 120px;"
                                                           {{ $isAvailable ? '' : 'disabled' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2 break-times {{ $isAvailable ? '' : 'opacity-50' }}">
                                                    <input type="time"
                                                           class="form-control form-control-sm"
                                                           name="availability[{{ $day }}][breaks][0][start]"
                                                           value="{{ $firstBreak['start'] ?? '12:00' }}"
                                                           style="width: 110px;"
                                                           {{ $isAvailable ? '' : 'disabled' }}>
                                                    <span class="text-muted">-</span>
                                                    <input type="time"
                                                           class="form-control form-control-sm"
                                                           name="availability[{{ $day }}][breaks][0][end]"
                                                           value="{{ $firstBreak['end'] ?? '13:00' }}"
                                                           style="width: 110px;"
                                                           {{ $isAvailable ? '' : 'disabled' }}>
                                                    <small class="text-muted">(lunch)</small>
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
                        <i class="fas fa-calendar-check me-2"></i>Next 7 Days Preview
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row g-2">
                        @for($i = 0; $i < 7; $i++)
                            @php
                                $date = now()->addDays($i);
                                $dayName = strtolower($date->format('l'));
                                $dayData = $availability[$dayName] ?? ['available' => false];
                                $isAvailable = $dayData['available'] ?? false;
                                $appointmentCount = $upcomingAppointments[$date->format('Y-m-d')] ?? 0;
                                $isBlocked = $blockedDates->contains(function($blocked) use ($date) {
                                    return $blocked->exception_date->format('Y-m-d') === $date->format('Y-m-d');
                                });
                            @endphp
                            <div class="col">
                                <div class="text-center p-3 rounded-3 {{ $isBlocked ? 'bg-danger bg-opacity-10 border border-danger' : ($isAvailable ? 'bg-success bg-opacity-10 border border-success' : 'bg-secondary bg-opacity-10 border') }}">
                                    <div class="fw-bold small text-uppercase {{ $i === 0 ? 'text-primary' : '' }}">
                                        {{ $i === 0 ? 'Today' : $date->format('D') }}
                                    </div>
                                    <div class="fs-4 fw-bold my-1">{{ $date->format('j') }}</div>
                                    <div class="small">{{ $date->format('M') }}</div>
                                    @if($isBlocked)
                                        <span class="badge bg-danger mt-2">Blocked</span>
                                    @elseif($isAvailable)
                                        <span class="badge bg-success mt-2">{{ $appointmentCount }} appts</span>
                                    @else
                                        <span class="badge bg-secondary mt-2">Off</span>
                                    @endif
                                </div>
                            </div>
                        @endfor
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
                        <input type="hidden" name="is_all_day" value="1">
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
                                        @if($blocked->reason)
                                            <small class="text-muted">{{ $blocked->reason }}</small>
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
                            $start = Carbon\Carbon::parse($day['start'] ?? '09:00');
                            $end = Carbon\Carbon::parse($day['end'] ?? '17:00');
                            $breakHours = 0;
                            foreach ($day['breaks'] ?? [] as $break) {
                                $breakStart = Carbon\Carbon::parse($break['start']);
                                $breakEnd = Carbon\Carbon::parse($break['end']);
                                $breakHours += $breakEnd->diffInHours($breakStart);
                            }
                            return $end->diffInHours($start) - $breakHours;
                        });
                    @endphp

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded-3">
                                <div class="fs-3 fw-bold text-primary">{{ $workingDays }}</div>
                                <small class="text-muted">Working Days</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded-3">
                                <div class="fs-3 fw-bold text-success">{{ $totalHours }}</div>
                                <small class="text-muted">Hours/Week</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded-3">
                                <div class="fs-3 fw-bold text-warning">{{ $blockedDates->count() }}</div>
                                <small class="text-muted">Blocked Dates</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded-3">
                                <div class="fs-3 fw-bold text-info">{{ $upcomingAppointments->sum() }}</div>
                                <small class="text-muted">Upcoming Appts</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle day availability
    document.querySelectorAll('.day-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const row = this.closest('.day-row');
            const workingHours = row.querySelector('.working-hours');
            const breakTimes = row.querySelector('.break-times');
            const inputs = row.querySelectorAll('.working-hours input, .break-times input');

            if (this.checked) {
                row.classList.remove('table-secondary');
                workingHours.classList.remove('opacity-50');
                breakTimes.classList.remove('opacity-50');
                inputs.forEach(input => input.disabled = false);
            } else {
                row.classList.add('table-secondary');
                workingHours.classList.add('opacity-50');
                breakTimes.classList.add('opacity-50');
                inputs.forEach(input => input.disabled = true);
            }
        });
    });

    // Copy Monday to all weekdays
    document.getElementById('copyMondayBtn').addEventListener('click', function() {
        const mondayRow = document.querySelector('[data-day="monday"]');
        const mondayAvailable = mondayRow.querySelector('.day-toggle').checked;
        const mondayStart = mondayRow.querySelector('input[name*="[start]"]').value;
        const mondayEnd = mondayRow.querySelector('input[name*="[end]"]').value;
        const mondayBreakStart = mondayRow.querySelector('input[name*="[breaks][0][start]"]').value;
        const mondayBreakEnd = mondayRow.querySelector('input[name*="[breaks][0][end]"]').value;

        const weekdays = ['tuesday', 'wednesday', 'thursday', 'friday'];
        weekdays.forEach(function(day) {
            const row = document.querySelector('[data-day="' + day + '"]');
            const toggle = row.querySelector('.day-toggle');

            toggle.checked = mondayAvailable;
            toggle.dispatchEvent(new Event('change'));

            row.querySelector('input[name*="[start]"]').value = mondayStart;
            row.querySelector('input[name*="[end]"]').value = mondayEnd;
            row.querySelector('input[name*="[breaks][0][start]"]').value = mondayBreakStart;
            row.querySelector('input[name*="[breaks][0][end]"]').value = mondayBreakEnd;
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

        const formData = new FormData(form);

        fetch('{{ route("staff.schedule.add-blocked-date") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
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

                const newItem = document.createElement('div');
                newItem.className = 'd-flex justify-content-between align-items-center p-2 bg-light rounded mb-2 blocked-date-item';
                newItem.setAttribute('data-id', data.exception.id);
                newItem.innerHTML = `
                    <div>
                        <div class="fw-bold small">${formattedDate}</div>
                        ${reasonInput.value ? `<small class="text-muted">${reasonInput.value}</small>` : ''}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-blocked-date" data-id="${data.exception.id}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                listContainer.prepend(newItem);

                // Attach event listener to new remove button
                newItem.querySelector('.remove-blocked-date').addEventListener('click', function() {
                    removeBlockedDate(this.getAttribute('data-id'));
                });

                // Reset form
                dateInput.value = '';
                reasonInput.value = '';

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
                text: 'Failed to block date. Please try again.'
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
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
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
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to remove blocked date.'
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
