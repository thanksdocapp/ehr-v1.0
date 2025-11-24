@extends('admin.layouts.app')

@section('title', 'Appointments Calendar')

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    @include('admin.partials.modern-page-header', [
        'title' => 'Appointments Calendar',
        'subtitle' => 'View and manage appointments in calendar view',
        'icon' => 'fas fa-calendar-alt'
    ])

    <!-- Calendar Container -->
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="modern-card-title mb-0">
                    <i class="fas fa-calendar me-2"></i>Calendar View
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-modern-outline" onclick="changeViewType('Day');">
                        <i class="fas fa-calendar-day"></i> Day
                    </button>
                    <button type="button" class="btn btn-sm btn-modern-outline" onclick="changeViewType('Week');">
                        <i class="fas fa-calendar-week"></i> Week
                    </button>
                    <button type="button" class="btn btn-sm btn-modern-outline active" onclick="changeViewType('Month');">
                        <i class="fas fa-calendar-alt"></i> Month
                    </button>
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
<!-- DayPilot Lite CSS -->
<link rel="stylesheet" href="https://cdn.daypilot.org/daypilot-lite.min.css" />
<style>
    #calendar {
        width: 100%;
        height: 600px;
    }

    /* DayPilot Calendar Customization */
    .calendar_default_main {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .calendar_default_header {
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }

    .calendar_default_cell {
        border-color: #e9ecef;
    }

    .calendar_default_event {
        border-radius: 4px;
        padding: 2px 4px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .calendar_default_event:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Status Colors */
    .event-pending {
        background-color: #ffc107;
        color: #000;
        border-left: 3px solid #ff9800;
    }

    .event-confirmed {
        background-color: #17a2b8;
        color: #fff;
        border-left: 3px solid #138496;
    }

    .event-completed {
        background-color: #28a745;
        color: #fff;
        border-left: 3px solid #218838;
    }

    .event-cancelled {
        background-color: #dc3545;
        color: #fff;
        border-left: 3px solid #c82333;
    }

    .event-rescheduled {
        background-color: #6c757d;
        color: #fff;
        border-left: 3px solid #5a6268;
    }

    @media (max-width: 768px) {
        #calendar {
            height: 500px;
        }
    }
</style>
@endpush

@push('scripts')
<!-- DayPilot Lite JavaScript -->
<script src="https://cdn.daypilot.org/daypilot-lite.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DayPilot Calendar
    const calendar = new DayPilot.Calendar("calendar", {
        viewType: "Month",
        startDate: DayPilot.Date.today().firstDayOfMonth(),
        timeRangeSelectedHandling: "Enabled",
        eventMoveHandling: "Update",
        eventResizeHandling: "Update",
        eventDeleteHandling: "Disabled",
        eventClickHandling: "Enabled",
        onTimeRangeSelected: function(args) {
            // Create new appointment on time range selection
            const start = args.start;
            const end = args.end;
            window.location.href = `{{ route('admin.appointments.create') }}?date=${start.toString('yyyy-MM-dd')}&time=${start.toString('HH:mm')}`;
        },
        onEventClick: function(args) {
            // Show appointment details
            loadAppointmentDetails(args.e.id());
        },
        onEventMoved: function(args) {
            // Handle appointment rescheduling
            rescheduleAppointment(args.e.id(), args.newStart, args.newEnd);
        },
        onEventResized: function(args) {
            // Handle appointment duration change
            updateAppointmentDuration(args.e.id(), args.newStart, args.newEnd);
        },
        onVisibleRangeChanged: function(args) {
            // Reload data when calendar view changes
            loadCalendarData();
        }
    });

    // Load calendar data
    loadCalendarData();

    function loadCalendarData() {
        const start = calendar.visibleStart();
        const end = calendar.visibleEnd();
        
        const params = new URLSearchParams({
            start: start.toString('yyyy-MM-dd'),
            end: end.toString('yyyy-MM-dd')
        });
        
        fetch(`{{ route('admin.api.appointments.calendar-data') }}?${params}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            // Convert data to DayPilot format
            const events = data.map(appointment => {
                const start = new DayPilot.Date(appointment.start);
                const end = new DayPilot.Date(appointment.end);
                
                return {
                    id: appointment.id,
                    text: appointment.title,
                    start: start,
                    end: end,
                    backColor: appointment.backgroundColor,
                    borderColor: appointment.borderColor,
                    barColor: appointment.borderColor,
                    cssClass: `event-${appointment.extendedProps.status}`,
                    toolTip: `${appointment.extendedProps.patient} with ${appointment.extendedProps.doctor}\nStatus: ${appointment.extendedProps.status}\nType: ${appointment.extendedProps.type || 'Consultation'}`,
                    resource: appointment.extendedProps.doctor_id || null
                };
            });

            calendar.events.list = events;
            calendar.update();

            // Update stats
            updateStats(data);
        })
        .catch(error => {
            console.error('Error loading calendar data:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load calendar data. Please refresh the page.'
            });
        });
    }
    
    // Reload calendar when view changes
    calendar.onTimeRangeSelected = function(args) {
        loadCalendarData();
    };

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
        fetch(`{{ url('admin/appointments') }}/${appointmentId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const modalBody = document.getElementById('appointmentModalBody');
            const viewBtn = document.getElementById('viewAppointmentBtn');
            
            viewBtn.href = `{{ url('admin/appointments') }}/${appointmentId}`;
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user me-2"></i>Patient:</strong>
                        <div class="mt-1">${data.patient?.full_name || 'N/A'}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong><i class="fas fa-user-md me-2"></i>Doctor:</strong>
                        <div class="mt-1">${data.doctor?.full_name || 'N/A'}</div>
                    </div>
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
                            <span class="badge bg-${getStatusBadgeColor(data.status)}">${data.status || 'N/A'}</span>
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
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('appointmentModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error loading appointment details:', error);
            // Fallback: redirect to appointment page
            window.location.href = `{{ url('admin/appointments') }}/${appointmentId}`;
        });
    }

    function rescheduleAppointment(appointmentId, newStart, newEnd) {
        Swal.fire({
            title: 'Reschedule Appointment?',
            text: 'Do you want to reschedule this appointment?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reschedule',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('admin/appointments') }}/${appointmentId}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        new_date: newStart.toString('yyyy-MM-dd'),
                        new_time: newStart.toString('HH:mm'),
                        reason: 'Rescheduled via calendar'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Success!', 'Appointment rescheduled successfully.', 'success');
                        loadCalendarData();
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to reschedule appointment.', 'error');
                        loadCalendarData(); // Reload to revert changes
                    }
                })
                .catch(error => {
                    console.error('Error rescheduling appointment:', error);
                    Swal.fire('Error!', 'Failed to reschedule appointment.', 'error');
                    loadCalendarData(); // Reload to revert changes
                });
            } else {
                loadCalendarData(); // Reload to revert changes
            }
        });
    }

    function updateAppointmentDuration(appointmentId, newStart, newEnd) {
        // Similar to reschedule but for duration changes
        rescheduleAppointment(appointmentId, newStart, newEnd);
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

    function changeViewType(viewType) {
        calendar.viewType = viewType;
        calendar.update();
        loadCalendarData();
        
        // Update active button
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.closest('.btn').classList.add('active');
    }

    // Make calendar available globally for view type buttons
    window.calendar = calendar;
    window.changeViewType = changeViewType;
});
</script>
@endpush

