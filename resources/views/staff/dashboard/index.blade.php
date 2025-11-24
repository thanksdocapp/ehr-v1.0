@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')
@section('page-subtitle', '')

@section('content')
<div class="fade-in">
    <!-- Calendar Widget - Moved to top for visibility -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Appointments Calendar
                        </h5>
                        <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-external-link-alt me-1"></i>View Full Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="dashboard-calendar" style="height: 400px; min-height: 400px; width: 100%; background: #f8f9fa; border-radius: 8px;">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading calendar...</span>
                                </div>
                                <p class="text-muted mb-0">Loading calendar...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-primary" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['total_patients'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Patients</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-users text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-success" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['total_appointments'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Appointments</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-warning" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['pending_appointments'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Pending Appointments</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-info" style="font-size: 1.75rem; font-weight: 600;">{{ $stats['today_appointments'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Today's Schedule</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-calendar-day text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Grid -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt text-primary me-2"></i>
                            Quick Actions
                        </h5>
                        <small class="text-muted">Streamline your workflow</small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.patients.create') }}" class="btn btn-primary w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-user-plus fs-4 mb-2"></i>
                                    <span class="fw-semibold">Add New Patient</span>
                                    <small class="opacity-75">Register a new patient</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.appointments.create') }}" class="btn btn-success w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-calendar-plus fs-4 mb-2"></i>
                                    <span class="fw-semibold">Book Appointment</span>
                                    <small class="opacity-75">Schedule new appointment</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.patients.index') }}" class="btn btn-info w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-users fs-4 mb-2"></i>
                                    <span class="fw-semibold">View Patients</span>
                                    <small class="opacity-75">Manage patient records</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.appointments.index') }}" class="btn btn-warning w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-calendar-alt fs-4 mb-2"></i>
                                    <span class="fw-semibold">View Schedule</span>
                                    <small class="opacity-75">Check appointments</small>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.billing.create') }}" class="btn btn-secondary w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-receipt fs-4 mb-2"></i>
                                    <span class="fw-semibold">Create Bill</span>
                                    <small class="opacity-75">Generate new bill</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.billing.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-file-invoice-dollar fs-4 mb-2"></i>
                                    <span class="fw-semibold">View Bills</span>
                                    <small class="opacity-75">Manage billing records</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.medical-records.create') }}" class="btn btn-outline-success w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-file-medical fs-4 mb-2"></i>
                                    <span class="fw-semibold">Medical Record</span>
                                    <small class="opacity-75">Add medical record</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('staff.prescriptions.create') }}" class="btn btn-outline-info w-100 py-3">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="fas fa-prescription-bottle-alt fs-4 mb-2"></i>
                                    <span class="fw-semibold">Prescription</span>
                                    <small class="opacity-75">Create prescription</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="row g-4">
        <!-- Recent Appointments -->
        <div class="col-xl-8 col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            Recent Appointments
                        </h5>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 fw-semibold">Patient</th>
                                        <th class="border-0 fw-semibold">Doctor</th>
                                        <th class="border-0 fw-semibold">Date & Time</th>
                                        <th class="border-0 fw-semibold">Status</th>
                                        <th class="border-0 fw-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAppointments as $appointment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                                    <small class="text-muted">#{{ $appointment->appointment_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $appointment->doctor->name ?? 'Not assigned' }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-medium">{{ $appointment->appointment_date->format('M d, Y') }}</div>
                                                <small class="text-muted">{{ $appointment->appointment_time ?? 'TBD' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($appointment->status === 'confirmed') bg-success
                                                @elseif($appointment->status === 'pending') bg-warning
                                                @elseif($appointment->status === 'cancelled') bg-danger
                                                @else bg-secondary
                                                @endif
                                            ">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('staff.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-outline-success" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-calendar-times fa-4x text-muted"></i>
                            </div>
                            <h6 class="text-muted">No Recent Appointments</h6>
                            <p class="text-muted mb-4">Start by booking your first appointment</p>
                            <a href="{{ route('staff.appointments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Book Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activity Feed & Insights -->
        <div class="col-xl-4 col-lg-5">
            <!-- Today's Schedule -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-clock text-info me-2"></i>
                        Today's Schedule
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted">Current Time</span>
                        <span class="fw-semibold" id="current-time">{{ now()->format('H:i A') }}</span>
                    </div>
                    
                    @if(isset($todayAppointments) && $todayAppointments->count() > 0)
                        @foreach($todayAppointments->take(3) as $appointment)
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0">
                                <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                <small class="text-muted">{{ $appointment->appointment_time ?? 'TBD' }}</small>
                            </div>
                            <span class="badge bg-light text-dark">{{ $appointment->status }}</span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No appointments today</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-line text-success me-2"></i>
                        Quick Insights
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fs-4 fw-bold text-primary">{{ $stats['total_patients'] ?? 0 }}</div>
                                <small class="text-muted">Total Patients</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fs-4 fw-bold text-success">{{ $stats['today_appointments'] ?? 0 }}</div>
                                <small class="text-muted">Today</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fs-4 fw-bold text-warning">{{ $stats['pending_appointments'] ?? 0 }}</div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="fs-4 fw-bold text-info">{{ date('d') }}</div>
                                <small class="text-muted">Day of Month</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Weekly Progress</small>
                            <small class="text-success">+15%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-gradient" role="progressbar" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Modal -->
<div class="modal fade" id="chartModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <canvas id="appointmentChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-xs {
        width: 24px;
        height: 24px;
        font-size: 0.75rem;
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.875rem;
    }
    
    .progress-bar.bg-gradient {
        background: linear-gradient(90deg, var(--primary), var(--info));
    }
    
    .slide-up {
        animation: slideUp 0.5s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<style>
    #dashboard-calendar {
        font-size: 0.9rem;
    }
    
    #dashboard-calendar .fc-header-toolbar {
        margin-bottom: 1rem;
        padding: 0.5rem;
    }
    
    #dashboard-calendar .fc-button {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }
    
    #dashboard-calendar .fc-event {
        font-size: 0.75rem;
        padding: 1px 3px;
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dashboard Calendar Widget - Initialize with delay to ensure DOM is ready
    setTimeout(function() {
        const dashboardCalendarEl = document.getElementById('dashboard-calendar');
        if (dashboardCalendarEl) {
            console.log('Initializing dashboard calendar...');
            console.log('Calendar element found:', dashboardCalendarEl);
            
            // Check if FullCalendar is loaded
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar library not loaded!');
                dashboardCalendarEl.innerHTML = '<div class="alert alert-warning p-3"><i class="fas fa-exclamation-triangle me-2"></i>Calendar library failed to load. Please refresh the page.</div>';
                return;
            }
            
            console.log('FullCalendar library loaded, creating calendar instance...');
            
            // Clear loading message
            dashboardCalendarEl.innerHTML = '';
            
            const dashboardCalendar = new FullCalendar.Calendar(dashboardCalendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                const params = new URLSearchParams({
                    start: fetchInfo.start.toISOString().split('T')[0],
                    end: fetchInfo.end.toISOString().split('T')[0]
                });
                
                fetch(`{{ route('staff.api.appointments.calendar-data') }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const events = data.map(appointment => ({
                        id: appointment.id,
                        title: appointment.title,
                        start: appointment.start,
                        end: appointment.end,
                        backgroundColor: appointment.backgroundColor,
                        borderColor: appointment.borderColor,
                        textColor: appointment.textColor || '#fff',
                        url: `{{ route('staff.appointments.show', '') }}/${appointment.id}`
                    }));
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error loading calendar data:', error);
                    if (failureCallback) failureCallback(error);
                });
            },
            eventClick: function(arg) {
                arg.jsEvent.preventDefault();
                window.location.href = arg.event.url;
            }
            });
            
            dashboardCalendar.render();
            console.log('Dashboard calendar rendered successfully');
        } else {
            console.error('Dashboard calendar element not found!');
            console.error('Available elements:', document.querySelectorAll('[id*="calendar"]'));
        }
    }, 500); // Wait 500ms to ensure DOM is ready
    
    // Update current time every minute
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }
    
    updateTime();
    setInterval(updateTime, 60000);
    
    // Animate cards on load
    const cards = document.querySelectorAll('.stat-card, .card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('slide-up');
    });
    
    // Initialize chart if needed
    const chartCanvas = document.getElementById('appointmentChart');
    if (chartCanvas) {
        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Appointments',
                    data: [12, 19, 3, 5, 2, 3, 9],
                    borderColor: 'rgb(79, 70, 229)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endpush
