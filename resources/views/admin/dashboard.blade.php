@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Title -->
    <div class="page-title" style="margin-bottom: 15px;">
        <h1>{{ getAppName() }} Dashboard</h1>
        <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}! Here's your hospital overview.</p>
    </div>

    <!-- Modern Stats Grid -->
    <div class="modern-stats-grid" style="gap: 1rem; margin-bottom: 1.5rem;">
        <!-- Total Patients Widget -->
        <a href="{{ route('admin.patients.index') }}" class="modern-stat-card patients-card" data-aos="fade-up" data-aos-delay="50">
            <div class="stat-card-bg"></div>
            <div class="stat-card-content">
                <div class="stat-header">
                    <div class="stat-icon-modern primary">
                        <i class="fas fa-users"></i>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span class="trend-value">+{{ $stats['today_patients'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="stat-body">
                    <div class="stat-number">{{ number_format($stats['total_patients'] ?? 0) }}</div>
                    <div class="stat-title">Total Patients</div>
                    <div class="stat-subtitle">{{ $stats['today_patients'] ?? 0 }} registered today</div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 75%;"></div>
                    </div>
                </div>
            </div>
        </a>

        <!-- Total Appointments Widget -->
        <a href="{{ route('admin.appointments.index') }}" class="modern-stat-card appointments-card" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card-bg"></div>
            <div class="stat-card-content">
                <div class="stat-header">
                    <div class="stat-icon-modern success">
                        <i class="fas fa-calendar-check"></i>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-plus"></i>
                        <span class="trend-value">{{ $stats['today_appointments'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="stat-body">
                    <div class="stat-number">{{ number_format($stats['total_appointments'] ?? 0) }}</div>
                    <div class="stat-title">Total Appointments</div>
                    <div class="stat-subtitle">{{ $stats['today_appointments'] ?? 0 }} scheduled today</div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar">
                        <div class="progress-fill success" style="width: 85%;"></div>
                    </div>
                </div>
            </div>
        </a>

        <!-- Active Doctors Widget -->
        <a href="{{ route('admin.doctors.index') }}" class="modern-stat-card doctors-card" data-aos="fade-up" data-aos-delay="150">
            <div class="stat-card-bg"></div>
            <div class="stat-card-content">
                <div class="stat-header">
                    <div class="stat-icon-modern info">
                        <i class="fas fa-user-md"></i>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-check-circle"></i>
                        <span class="trend-value">{{ $stats['active_doctors'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="stat-body">
                    <div class="stat-number">{{ number_format($stats['total_doctors'] ?? 0) }}</div>
                    <div class="stat-title">Total Doctors</div>
                    <div class="stat-subtitle">{{ $stats['active_doctors'] ?? 0 }} active today</div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar">
                        <div class="progress-fill info" style="width: 92%;"></div>
                    </div>
                </div>
            </div>
        </a>

        <!-- Departments Widget -->
        <a href="{{ route('admin.departments.index') }}" class="modern-stat-card departments-card" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card-bg"></div>
            <div class="stat-card-content">
                <div class="stat-header">
                    <div class="stat-icon-modern warning">
                        <i class="fas fa-building"></i>
                        <div class="icon-pulse"></div>
                    </div>
                    <div class="stat-trend neutral">
                        <i class="fas fa-chart-line"></i>
                        <span class="trend-value">{{ $stats['active_departments'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="stat-body">
                    <div class="stat-number">{{ number_format($stats['total_departments'] ?? 0) }}</div>
                    <div class="stat-title">Departments</div>
                    <div class="stat-subtitle">{{ $stats['active_departments'] ?? 0 }} active</div>
                </div>
                <div class="stat-footer">
                    <div class="progress-bar">
                        <div class="progress-fill warning" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Appointment Chart -->
        <div class="col-lg-8 mb-4">
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="card-title">Appointment Overview (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="appointmentChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Patient Registration Chart -->
        <div class="col-lg-4 mb-4">
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="card-title">Patient Registration (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="patientChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Tables Row -->
    <div class="row">
        <!-- Recent Appointments -->
        <div class="col-lg-6 mb-4">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Appointments</h5>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAppointments as $appointment)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->patient_name }}</div>
                                                    <small class="text-muted">{{ $appointment->patient_email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $appointment->doctor ? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name : 'N/A' }}</div>
                                            <small class="text-muted">{{ $appointment->doctor->department->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ formatDate($appointment->appointment_date) }}</div>
                                            <small class="text-muted">{{ $appointment->appointment_time }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'success',
                                                    'completed' => 'info',
                                                    'cancelled' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt text-muted mb-3" style="font-size: 48px;"></i>
                            <h6 class="text-muted">No appointments yet</h6>
                            <p class="text-muted mb-0">New appointments will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Patients -->
        <div class="col-lg-6 mb-4">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Recent Patients</h5>
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    @if(isset($recentPatients) && $recentPatients->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentPatients as $patient)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $patient->first_name }} {{ $patient->last_name }}</h6>
                                        <p class="mb-1 text-muted">{{ $patient->email }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Registered {{ $patient->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success mb-1">Active</span>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i>{{ $patient->phone }}
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users text-muted mb-3" style="font-size: 48px;"></i>
                            <h6 class="text-muted">No patients yet</h6>
                            <p class="text-muted mb-0">New patient registrations will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row">
        <!-- Pending Appointments -->
        <div class="col-lg-4 mb-4">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Pending Appointments</h5>
                    <span class="badge bg-warning">{{ $stats['pending_appointments'] ?? 0 }}</span>
                </div>
                <div class="card-body">
                    @if(isset($pendingAppointments) && $pendingAppointments->count() > 0)
                        @foreach($pendingAppointments as $appointment)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ $appointment->patient_name }}</h6>
                                <p class="mb-0 text-muted">{{ $appointment->doctor ? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name : 'N/A' }} - {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d') }}</p>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-sm btn-success me-1" onclick="confirmAppointment({{ $appointment->id }})">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="cancelAppointment({{ $appointment->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check text-muted mb-3" style="font-size: 48px;"></i>
                            <h6 class="text-muted">No pending appointments</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="col-lg-4 mb-4">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Today's Appointments</h5>
                    <span class="badge bg-info">{{ $stats['today_appointments'] ?? 0 }}</span>
                </div>
                <div class="card-body">
                    @if(isset($todaysAppointments) && $todaysAppointments->count() > 0)
                        @foreach($todaysAppointments as $appointment)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ $appointment->patient_name }}</h6>
                                <p class="mb-0 text-muted">{{ $appointment->appointment_time }} - {{ $appointment->doctor ? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name : 'N/A' }}</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($appointment->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-day text-muted mb-3" style="font-size: 48px;"></i>
                            <h6 class="text-muted">No appointments today</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Department Overview -->
        <div class="col-lg-4 mb-4">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Department Overview</h5>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
                <div class="card-body">
                    @if(isset($departmentStats) && $departmentStats->count() > 0)
                        @foreach($departmentStats as $dept)
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <h6 class="mb-1">{{ $dept['name'] ?? 'N/A' }}</h6>
                                <p class="mb-0 text-muted">{{ $dept['doctors_count'] ?? 0 }} doctors</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary">{{ $dept['appointments_count'] ?? 0 }}</span>
                                <br>
                                <small class="text-muted">appointments</small>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-building text-muted mb-3" style="font-size: 48px;"></i>
                            <h6 class="text-muted">No departments yet</h6>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Application Footer -->
    @if(shouldShowPoweredBy())
    <div class="text-center mt-5 py-4" style="border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="fas fa-hospital" style="color: #e94560;"></i>
            <span>{!! getPoweredByText() !!}</span>
        </div>
        <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
            {{ getCopyrightText() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Sample data - replace with actual data from controller
    const appointmentData = @json($appointmentChartData ?? []);
    const patientData = @json($patientRegistrationData ?? []);
    
    // Appointment Chart
    const appointmentCtx = document.getElementById('appointmentChart').getContext('2d');
    const appointmentChart = new Chart(appointmentCtx, {
        type: 'line',
        data: {
            labels: appointmentData.map(d => d.date) || [],
            datasets: [{
                label: 'Appointments',
                data: appointmentData.map(d => d.count) || [],
                borderColor: '#e94560',
                backgroundColor: 'rgba(233, 69, 96, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Appointments'
                    }
                }
            }
        }
    });

    // Patient Registration Chart
    const patientCtx = document.getElementById('patientChart').getContext('2d');
    const patientChart = new Chart(patientCtx, {
        type: 'bar',
        data: {
            labels: patientData.map(d => d.month) || [],
            datasets: [{
                label: 'New Patients',
                data: patientData.map(d => d.patients) || [],
                backgroundColor: '#667eea',
                borderColor: '#667eea',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Patients'
                    }
                }
            }
        }
    });

    // Quick actions
    function confirmAppointment(appointmentId) {
        if (confirm('Are you sure you want to confirm this appointment?')) {
            fetch(`/admin/appointments/${appointmentId}/confirm`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error confirming appointment');
                }
            });
        }
    }

    function cancelAppointment(appointmentId) {
        const reason = prompt('Please enter cancellation reason:');
        if (reason) {
            fetch(`/admin/appointments/${appointmentId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ reason: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error cancelling appointment');
                }
            });
        }
    }
</script>
@endpush
