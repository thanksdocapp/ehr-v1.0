@extends('layouts.doctor')

@section('title', 'Appointments Calendar')

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="page-header-modern mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="page-title">
                    <i class="fas fa-calendar-alt me-2"></i>Appointments Calendar
                </h2>
                <p class="page-subtitle text-muted mb-0">View and manage your appointments in calendar view</p>
            </div>
            <div>
                <a href="{{ route('staff.appointments.index') }}" class="btn btn-modern-outline">
                    <i class="fas fa-list me-1"></i>List View
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="modern-card-title mb-0">
                    <i class="fas fa-calendar me-2"></i>Calendar View
                </h5>
                <div id="calendar-toolbar" class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-modern-outline" id="prev-btn">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-modern-outline" id="today-btn">
                        Today
                    </button>
                    <button type="button" class="btn btn-sm btn-modern-outline" id="next-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="btn-group ms-2">
                        <button type="button" class="btn btn-sm btn-modern-outline" id="day-btn">Day</button>
                        <button type="button" class="btn btn-sm btn-modern-outline active" id="week-btn">Week</button>
                        <button type="button" class="btn btn-sm btn-modern-outline" id="month-btn">Month</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modern-card-body p-0">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mt-4">
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="stat-card-icon" style="background: var(--gradient-warning);">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-number" id="pendingCount">0</div>
                <div class="stat-card-label">Pending</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="stat-card-icon" style="background: var(--gradient-info);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-number" id="confirmedCount">0</div>
                <div class="stat-card-label">Confirmed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="stat-card-icon" style="background: var(--gradient-success);">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-card-number" id="completedCount">0</div>
                <div class="stat-card-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern">
                <div class="stat-card-icon" style="background: var(--gradient-danger);">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-card-number" id="cancelledCount">0</div>
                <div class="stat-card-label">Cancelled</div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewAppointmentBtn" class="btn btn-modern-primary">
                    <i class="fas fa-eye me-1"></i>View Full Details
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<style>
    #calendar {
        width: 100%;
        height: 600px;
        padding: 1rem;
    }

    /* FullCalendar Modern Styling */
    .fc {
        font-family: inherit;
    }

    .fc-header-toolbar {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .fc-button {
        background: #fff;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .fc-button:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }

    .fc-button-active {
        background: var(--doctor-primary, #0d6efd);
        border-color: var(--doctor-primary, #0d6efd);
        color: #fff;
    }

    .fc-button-primary:not(:disabled):active,
    .fc-button-primary:not(:disabled).fc-button-active {
        background: var(--doctor-primary, #0d6efd);
        border-color: var(--doctor-primary, #0d6efd);
    }

    .fc-event {
        border-radius: 4px;
        padding: 2px 4px;
        cursor: pointer;
        border: none;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Status Colors */
    .fc-event-pending {
        background-color: #ffc107;
        color: #000;
        border-left: 3px solid #ff9800;
    }

    .fc-event-confirmed {
        background-color: #17a2b8;
        color: #fff;
        border-left: 3px solid #138496;
    }

    .fc-event-completed {
        background-color: #28a745;
        color: #fff;
        border-left: 3px solid #218838;
    }

    .fc-event-cancelled {
        background-color: #dc3545;
        color: #fff;
        border-left: 3px solid #c82333;
    }

    .fc-event-rescheduled {
        background-color: #6c757d;
        color: #fff;
        border-left: 3px solid #5a6268;
    }

    .fc-daygrid-day {
        border-color: #e9ecef;
    }

    .fc-col-header-cell {
        background: #f8f9fa;
        border-color: #dee2e6;
        padding: 0.75rem;
        font-weight: 600;
    }

    .fc-timegrid-slot {
        border-color: #e9ecef;
    }

    .page-header-modern {
        padding: 1.5rem 0;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .page-subtitle {
        font-size: 0.9rem;
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        #calendar {
            height: 500px;
            padding: 0.5rem;
        }
        
        .page-header-modern {
            padding: 1rem 0;
        }
        
        .page-title {
            font-size: 1.5rem;
        }

        .fc-header-toolbar {
            flex-direction: column;
            gap: 0.5rem;
        }

        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        return;
    }

    let calendar;
    try {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false, // We'll use custom toolbar
            height: 'auto',
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            select: function(arg) {
                // Create new appointment on date selection
                const date = arg.startStr.split('T')[0];
                const time = arg.startStr.split('T')[1] ? arg.startStr.split('T')[1].substring(0, 5) : '09:00';
                window.location.href = `{{ route('staff.appointments.create') }}?date=${date}&time=${time}`;
            },
            eventClick: function(arg) {
                // Show appointment details
                loadAppointmentDetails(arg.event.id);
            },
            eventDrop: function(arg) {
                // Handle appointment rescheduling
                rescheduleAppointment(arg.event.id, arg.event.start, arg.event.end);
            },
            eventResize: function(arg) {
                // Handle appointment duration change
                rescheduleAppointment(arg.event.id, arg.event.start, arg.event.end);
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                // Load events from API
                loadCalendarData(fetchInfo.start, fetchInfo.end, successCallback, failureCallback);
            }
        });

        calendar.render();
        console.log('Calendar initialized successfully');

        // Custom toolbar buttons
        document.getElementById('prev-btn').addEventListener('click', function() {
            calendar.prev();
        });

        document.getElementById('next-btn').addEventListener('click', function() {
            calendar.next();
        });

        document.getElementById('today-btn').addEventListener('click', function() {
            calendar.today();
        });

        document.getElementById('day-btn').addEventListener('click', function() {
            calendar.changeView('timeGridDay');
            updateViewButtons('day');
        });

        document.getElementById('week-btn').addEventListener('click', function() {
            calendar.changeView('timeGridWeek');
            updateViewButtons('week');
        });

        document.getElementById('month-btn').addEventListener('click', function() {
            calendar.changeView('dayGridMonth');
            updateViewButtons('month');
        });

        // Update view buttons based on current view
        calendar.on('viewDidMount', function() {
            const view = calendar.view.type;
            if (view.includes('Day')) {
                updateViewButtons('day');
            } else if (view.includes('Week')) {
                updateViewButtons('week');
            } else if (view.includes('Month')) {
                updateViewButtons('month');
            }
        });

    } catch (error) {
        console.error('Error initializing calendar:', error);
        alert('Failed to initialize calendar: ' + error.message);
    }

    function updateViewButtons(activeView) {
        document.querySelectorAll('#calendar-toolbar .btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById(activeView + '-btn').classList.add('active');
    }

    function loadCalendarData(start, end, successCallback, failureCallback) {
        const params = new URLSearchParams({
            start: start.toISOString().split('T')[0],
            end: end.toISOString().split('T')[0]
        });
        
        fetch(`{{ route('staff.api.appointments.calendar-data') }}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Calendar data loaded:', data);
            
            // Convert to FullCalendar format
            const events = data.map(appointment => ({
                id: appointment.id,
                title: appointment.title,
                start: appointment.start,
                end: appointment.end,
                backgroundColor: appointment.backgroundColor,
                borderColor: appointment.borderColor,
                textColor: appointment.textColor || '#fff',
                className: `fc-event-${appointment.extendedProps.status}`,
                extendedProps: appointment.extendedProps
            }));

            console.log('Events to display:', events);
            
            // Update stats
            updateStats(data);
            
            // Call success callback with events
            successCallback(events);
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            if (failureCallback) {
                failureCallback(error);
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load calendar data: ' + error.message
                });
            }
        });
    }

    function updateStats(appointments) {
        const stats = {
            pending: 0,
            confirmed: 0,
            completed: 0,
            cancelled: 0
        };

        appointments.forEach(apt => {
            const status = apt.extendedProps.status;
            if (stats.hasOwnProperty(status)) {
                stats[status]++;
            }
        });

        document.getElementById('pendingCount').textContent = stats.pending;
        document.getElementById('confirmedCount').textContent = stats.confirmed;
        document.getElementById('completedCount').textContent = stats.completed;
        document.getElementById('cancelledCount').textContent = stats.cancelled;
    }

    function loadAppointmentDetails(appointmentId) {
        fetch(`{{ route('staff.appointments.show', '') }}/${appointmentId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const modalBody = document.getElementById('appointmentModalBody');
            const viewBtn = document.getElementById('viewAppointmentBtn');
            
            viewBtn.href = `{{ route('staff.appointments.show', '') }}/${appointmentId}`;
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user me-2"></i>Patient:</strong>
                        <div class="mt-1">${data.patient?.full_name || 'N/A'}</div>
                    </div>
                    ${data.doctor ? `
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user-md me-2"></i>Doctor:</strong>
                        <div class="mt-1">${data.doctor?.full_name || 'N/A'}</div>
                    </div>
                    ` : ''}
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-calendar me-2"></i>Date:</strong>
                        <div class="mt-1">${data.appointment_date || 'N/A'}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-clock me-2"></i>Time:</strong>
                        <div class="mt-1">${data.appointment_time || 'N/A'}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-tag me-2"></i>Status:</strong>
                        <div class="mt-1">
                            <span class="badge bg-${getStatusBadgeColor(data.status)}">${(data.status || 'N/A').charAt(0).toUpperCase() + (data.status || 'N/A').slice(1)}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-building me-2"></i>Department:</strong>
                        <div class="mt-1">${data.department?.name || 'N/A'}</div>
                    </div>
                    ${data.reason ? `
                    <div class="col-12 mb-3">
                        <strong><i class="fas fa-comment me-2"></i>Reason:</strong>
                        <div class="mt-1">${data.reason}</div>
                    </div>
                    ` : ''}
                    ${data.appointment_number ? `
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-hashtag me-2"></i>Appointment #:</strong>
                        <div class="mt-1">${data.appointment_number}</div>
                    </div>
                    ` : ''}
                    ${data.is_online ? `
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-video me-2"></i>Type:</strong>
                        <div class="mt-1"><span class="badge bg-info">Online Appointment</span></div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error loading appointment details:', error);
            window.location.href = `{{ route('staff.appointments.show', '') }}/${appointmentId}`;
        });
    }

    function rescheduleAppointment(appointmentId, newStart, newEnd) {
        if (typeof Swal === 'undefined') {
            if (!confirm('Do you want to reschedule this appointment?')) {
                calendar.refetchEvents();
                return;
            }
        } else {
            Swal.fire({
                title: 'Reschedule Appointment?',
                text: 'Do you want to reschedule this appointment?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reschedule',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) {
                    calendar.refetchEvents();
                    return;
                }
                performReschedule(appointmentId, newStart, newEnd);
            });
            return;
        }
        performReschedule(appointmentId, newStart, newEnd);
    }

    function performReschedule(appointmentId, newStart, newEnd) {
        const date = newStart.toISOString().split('T')[0];
        const time = newStart.toTimeString().split(' ')[0].substring(0, 5);
        
        fetch(`{{ route('staff.appointments.show', '') }}/${appointmentId}/reschedule`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                new_date: date,
                new_time: time,
                reason: 'Rescheduled via calendar'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Success!', 'Appointment rescheduled successfully.', 'success');
                } else {
                    alert('Appointment rescheduled successfully.');
                }
                calendar.refetchEvents();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error!', data.message || 'Failed to reschedule appointment.', 'error');
                } else {
                    alert(data.message || 'Failed to reschedule appointment.');
                }
                calendar.refetchEvents();
            }
        })
        .catch(error => {
            console.error('Error rescheduling appointment:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error!', 'Failed to reschedule appointment.', 'error');
            } else {
                alert('Failed to reschedule appointment.');
            }
            calendar.refetchEvents();
        });
    }

    function getStatusBadgeColor(status) {
        const colors = {
            'pending': 'warning',
            'confirmed': 'info',
            'completed': 'success',
            'cancelled': 'danger',
            'rescheduled': 'secondary'
        };
        return colors[status] || 'secondary';
    }
});
</script>
@endpush
