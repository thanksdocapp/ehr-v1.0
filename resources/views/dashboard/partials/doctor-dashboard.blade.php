<!-- Enhanced Doctor Dashboard Stats -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card primary animate__animated animate__fadeInUp">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Today's Appointments</div>
                    <div class="stat-number">{{ number_format($data['today_appointments'] ?? 0) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +2 from yesterday
                    </div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card warning animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Pending Consultations</div>
                    <div class="stat-number">{{ number_format($data['pending_consultations'] ?? 0) }}</div>
                    <div class="stat-change {{ ($data['pending_consultations'] ?? 0) > 5 ? 'negative' : 'positive' }}">
                        <i class="fas fa-{{ ($data['pending_consultations'] ?? 0) > 5 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ ($data['pending_consultations'] ?? 0) > 5 ? 'High' : 'Manageable' }}
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706);">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card success animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-number">{{ number_format($data['total_patients'] ?? 0) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        +{{ rand(5, 15) }}% this month
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                    <i class="fas fa-user-injured"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stat-card info animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="flex-grow-1">
                    <div class="stat-label">Completed Today</div>
                    <div class="stat-number">{{ number_format($data['completed_today'] ?? 0) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-check"></i>
                        {{ round((($data['completed_today'] ?? 0) / max(($data['today_appointments'] ?? 1), 1)) * 100) }}% completion
                    </div>
                </div>
                <div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #0891b2);">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Doctor Content -->
<div class="row">
    <!-- Today's Schedule -->
    <div class="col-xl-8 col-lg-7">
        <div class="enhanced-card mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-calendar-alt me-2"></i>Today's Schedule
                </h6>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshSchedule()" data-bs-toggle="tooltip" title="Refresh">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-calendar me-2"></i>View Full Schedule</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Export Schedule</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-plus me-2"></i>Add Appointment</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(!empty($data['upcoming_appointments']) && count($data['upcoming_appointments']) > 0)
                    <div class="row g-3">
                        @foreach($data['upcoming_appointments'] as $index => $appointment)
                            <div class="col-12">
                                <div class="enhanced-card border-0" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border-left: 4px solid {{ ['var(--primary)', 'var(--success)', 'var(--info)', 'var(--warning)'][$index % 4] }} !important;">
                                    <div class="card-body py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-circle me-3" style="background: {{ ['var(--primary)', 'var(--success)', 'var(--info)', 'var(--warning)'][$index % 4] }}; width: 2.5rem; height: 2.5rem;">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-bold">{{ $appointment->patient_name ?? 'John Doe' }}</h6>
                                                    <div class="d-flex align-items-center gap-3 text-muted small">
                                                        <span><i class="fas fa-clock me-1"></i>{{ $appointment->appointment_time ?? '09:00 AM' }}</span>
                                                        <span><i class="fas fa-stethoscope me-1"></i>{{ $appointment->appointment_type ?? 'Consultation' }}</span>
                                                        <span><i class="fas fa-id-card me-1"></i>{{ $appointment->patient_id ?? 'P001' }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : 'warning' }} px-3 py-2">
                                                    {{ ucfirst($appointment->status ?? 'Pending') }}
                                                </span>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-primary" onclick="startConsultation('{{ $appointment->id ?? 1 }}')" data-bs-toggle="tooltip" title="Start Consultation">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewAppointment('{{ $appointment->id ?? 1 }}')" data-bs-toggle="tooltip" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="rescheduleAppointment('{{ $appointment->id ?? 1 }}')" data-bs-toggle="tooltip" title="Reschedule">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="icon-circle mx-auto mb-3" style="background: var(--gray-200); color: var(--gray-500); width: 4rem; height: 4rem;">
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </div>
                        <h5 class="text-muted mb-2">No appointments scheduled for today</h5>
                        <p class="text-muted mb-3">Take some time to catch up on patient records or prepare for upcoming appointments.</p>
                        <button class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Schedule New Appointment
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Enhanced Doctor Sidebar -->
    <div class="col-xl-4 col-lg-5">
        <!-- Smart Patient Search -->
        <div class="enhanced-card mb-3" style="height: 180px;">
            <div class="card-header py-2 d-flex align-items-center justify-content-center" style="height: 45px;">
                <h6 class="m-0 font-weight-bold text-primary text-center">
                    <i class="fas fa-search me-2"></i>Smart Patient Search
                </h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-center p-3" style="height: 135px;">
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="patientSearch" placeholder="Search patient by name or ID..." style="border-radius: 8px 0 0 8px; height: 38px;">
                    <button class="btn btn-primary d-flex align-items-center justify-content-center" type="button" onclick="searchPatient()" style="border-radius: 0 8px 8px 0; width: 45px; height: 38px;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div id="searchResults" class="d-none">
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action rounded mb-1">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/32" class="rounded-circle me-3" width="32" height="32">
                                <div>
                                    <h6 class="mb-1 fw-semibold">John Doe</h6>
                                    <small class="text-muted">ID: P001 • Age: 45</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="mt-2 text-center">
                    <small class="text-muted d-block mb-2 fw-medium">Recent searches:</small>
                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="cursor: pointer; font-size: 0.75rem;" onclick="quickSearch('John Doe')">John Doe</span>
                        <span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="cursor: pointer; font-size: 0.75rem;" onclick="quickSearch('Jane Smith')">Jane Smith</span>
                        <span class="badge bg-light text-dark px-2 py-1 rounded-pill" style="cursor: pointer; font-size: 0.75rem;" onclick="quickSearch('P001')">P001</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Alerts -->
        <div class="enhanced-card mb-3" style="height: 240px;">
            <div class="card-header py-2 d-flex justify-content-between align-items-center" style="height: 45px;">
                <h6 class="m-0 font-weight-bold text-danger d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>Priority Alerts
                </h6>
                <span class="badge bg-danger rounded-pill">{{ count($data['alerts'] ?? []) ?: 2 }}</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center p-3" style="height: 195px; overflow-y: auto;">
                <div class="alert alert-warning border-0 shadow-sm mb-2" role="alert" style="border-radius: 8px; border-left: 3px solid var(--warning) !important; padding: 0.75rem;">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="background: var(--warning); width: 1.75rem; height: 1.75rem; font-size: 0.7rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="alert-heading mb-1 fw-semibold" style="font-size: 0.875rem;">High Priority Follow-up</h6>
                            <p class="mb-1 small">Patient John Doe requires immediate follow-up after surgery.</p>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>2 hours ago</small>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-warning" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">View Patient</button>
                        <button class="btn btn-sm btn-outline-warning ms-1" onclick="dismissAlert(this)" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Dismiss</button>
                    </div>
                </div>
                
                <div class="alert alert-info border-0 shadow-sm mb-2" role="alert" style="border-radius: 8px; border-left: 3px solid var(--info) !important; padding: 0.75rem;">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle me-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="background: var(--info); width: 1.75rem; height: 1.75rem; font-size: 0.7rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="alert-heading mb-1 fw-semibold" style="font-size: 0.875rem;">Lab Results Pending</h6>
                            <p class="mb-1 small">3 patients have lab results pending review.</p>
                            <small class="text-muted"><i class="fas fa-clock me-1"></i>4 hours ago</small>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <button class="btn btn-sm btn-info" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Review Results</button>
                        <button class="btn btn-sm btn-outline-info ms-1" onclick="dismissAlert(this)" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">Dismiss</button>
                    </div>
                </div>
                
                <div class="text-center mt-2">
                    <button class="btn btn-outline-primary btn-sm" style="font-size: 0.8rem;">
                        <i class="fas fa-bell me-1"></i>View All Alerts
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row g-2">
            <div class="col-6">
                <div class="enhanced-card text-center" style="height: 90px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center p-2">
                        <div class="stat-number fw-bold" style="font-size: 1.75rem; color: var(--success); line-height: 1;">{{ $data['prescriptions_today'] ?? 12 }}</div>
                        <div class="stat-label text-uppercase fw-medium" style="font-size: 0.65rem; color: var(--gray-600); letter-spacing: 0.5px;">Prescriptions</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="enhanced-card text-center" style="height: 90px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center p-2">
                        <div class="stat-number fw-bold" style="font-size: 1.75rem; color: var(--info); line-height: 1;">{{ $data['lab_orders_today'] ?? 2 }}</div>
                        <div class="stat-label text-uppercase fw-medium" style="font-size: 0.65rem; color: var(--gray-600); letter-spacing: 0.5px;">Lab Orders</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Medical Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Medical Activities</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Consultation Completed</h6>
                            <p class="timeline-description">Patient John Doe - Routine checkup completed successfully</p>
                            <small class="text-muted">2 hours ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Prescription Issued</h6>
                            <p class="timeline-description">Prescribed medication for Patient Jane Smith</p>
                            <small class="text-muted">4 hours ago</small>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Lab Results Reviewed</h6>
                            <p class="timeline-description">Reviewed blood work results for Patient Mike Johnson</p>
                            <small class="text-muted">Yesterday</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action Buttons -->
<div class="row mt-4">
    <div class="col-12">
        <div class="enhanced-card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if($user->hasPermission('patients.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.patients.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-user-plus fa-2x mb-2"></i>
                            <span class="fw-bold">New Patient</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('prescriptions.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.prescriptions.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-prescription-bottle fa-2x mb-2"></i>
                            <span class="fw-bold">Prescribe</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('medical_records.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.medical-records.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-file-medical fa-2x mb-2"></i>
                            <span class="fw-bold">Medical Record</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('lab_reports.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.lab-reports.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-vial fa-2x mb-2"></i>
                            <span class="fw-bold">Lab Order</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('appointments.create'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.create') }}" class="quick-action-btn w-100">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <span class="fw-bold">Schedule</span>
                        </a>
                    </div>
                    @endif
                    @if($user->hasPermission('appointments.view'))
                    <div class="col-lg-2 col-md-4 col-sm-6">
                        <a href="{{ route('admin.appointments.index') }}" class="quick-action-btn w-100">
                            <i class="fas fa-chart-line fa-2x mb-2"></i>
                            <span class="fw-bold">My Appointments</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Doctor dashboard specific functions
function searchPatient() {
    const searchTerm = document.getElementById('patientSearch').value;
    const resultsDiv = document.getElementById('searchResults');
    
    if (searchTerm.length > 2) {
        resultsDiv.classList.remove('d-none');
        // Simulate search with animation
        resultsDiv.classList.add('animate__animated', 'animate__fadeIn');
    } else {
        resultsDiv.classList.add('d-none');
    }
}

function quickSearch(term) {
    document.getElementById('patientSearch').value = term;
    searchPatient();
}

function startConsultation(appointmentId) {
    showNotification(`Starting consultation for appointment #${appointmentId}`, 'info');
    // Add actual consultation logic here
}

function viewAppointment(appointmentId) {
    showNotification(`Viewing details for appointment #${appointmentId}`, 'info');
    // Add actual view logic here
}

function rescheduleAppointment(appointmentId) {
    showNotification(`Rescheduling appointment #${appointmentId}`, 'warning');
    // Add actual reschedule logic here
}

function refreshSchedule() {
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    icon.classList.add('fa-spin');
    
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        showNotification('Schedule refreshed successfully!', 'success');
    }, 1000);
}

function dismissAlert(button) {
    const alert = button.closest('.alert');
    alert.classList.add('animate__animated', 'animate__fadeOut');
    setTimeout(() => {
        alert.remove();
    }, 300);
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('patientSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            if (this.value.length === 0) {
                document.getElementById('searchResults').classList.add('d-none');
            }
        });
    }
});
</script>
