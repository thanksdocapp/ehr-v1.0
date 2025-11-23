@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Manage your practice efficiently')

@section('content')
<div class="fade-in-up">
    <!-- Stats Grid -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(13, 110, 253, 0.1); color: var(--doctor-primary);">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-primary);">
                            {{ $stats['today_appointments'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label">Today's Appointments</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(255, 193, 7, 0.1); color: var(--doctor-warning);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-warning);">
                            {{ $stats['pending_appointments'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label">Pending Consultations</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(25, 135, 84, 0.1); color: var(--doctor-success);">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-success);">
                            {{ $stats['total_patients'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label">Total Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(13, 202, 240, 0.1); color: var(--doctor-info);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-info);">
                            {{ $stats['total_appointments'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label">Total Appointments</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title">
                <i class="fas fa-bolt text-primary"></i>
                Quick Actions
            </h5>
        </div>
        <div class="doctor-card-body">
            <div class="row g-3">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.patients.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="doctor-quick-action-title">New Patient</div>
                        <div class="doctor-quick-action-subtitle">Register</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.appointments.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="doctor-quick-action-title">Schedule</div>
                        <div class="doctor-quick-action-subtitle">Appointment</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.medical-records.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="doctor-quick-action-title">Medical Record</div>
                        <div class="doctor-quick-action-subtitle">Create</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.prescriptions.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="doctor-quick-action-title">Prescription</div>
                        <div class="doctor-quick-action-subtitle">Write</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.lab-reports.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="doctor-quick-action-title">Lab Order</div>
                        <div class="doctor-quick-action-subtitle">Request</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.appointments.index') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="doctor-quick-action-title">My Schedule</div>
                        <div class="doctor-quick-action-subtitle">View All</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Today's Appointments -->
        <div class="col-xl-8 col-lg-7">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-calendar-day text-primary"></i>
                            Today's Schedule
                        </h5>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-doctor-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="doctor-card-body">
                    @if(isset($todayAppointments) && $todayAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayAppointments->take(10) as $appointment)
                                    <tr>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="doctor-user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                    {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                                    <small class="text-muted">#{{ $appointment->appointment_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ ucfirst(str_replace('_', ' ', $appointment->type ?? 'consultation')) }}
                                            </span>
                                            @if($appointment->is_online)
                                                <span class="badge bg-info ms-1">
                                                    <i class="fas fa-video"></i> Online
                                                </span>
                                            @endif
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
                                            <div class="btn-group btn-group-sm">
                                                @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                                                    <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn btn-success btn-sm" title="Join Meeting">
                                                        <i class="fas fa-video"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('staff.appointments.edit', $appointment->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
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
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                            <h6 class="text-muted mb-2">No appointments scheduled for today</h6>
                            <p class="text-muted mb-4">Take some time to catch up on patient records</p>
                            <a href="{{ route('staff.appointments.create') }}" class="btn btn-doctor-primary">
                                <i class="fas fa-plus me-2"></i>Schedule New Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="col-xl-4 col-lg-5">
            <!-- Recent Patients -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-users text-success"></i>
                        Recent Patients
                    </h6>
                </div>
                <div class="doctor-card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        @foreach($recentAppointments->take(5) as $appointment)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="doctor-user-avatar me-3" style="width: 40px; height: 40px;">
                                {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                <small class="text-muted">{{ $appointment->appointment_date->format('M d, Y') }}</small>
                            </div>
                            <a href="{{ route('staff.patients.show', $appointment->patient_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('staff.patients.index') }}" class="btn btn-sm btn-doctor-primary w-100">
                                View All Patients
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-user-slash fa-2x text-muted mb-2" style="opacity: 0.3;"></i>
                            <p class="text-muted mb-0 small">No recent patients</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-chart-line text-info"></i>
                        Quick Insights
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(13, 110, 253, 0.1);">
                                <div class="fs-3 fw-bold text-primary">{{ $stats['total_patients'] ?? 0 }}</div>
                                <small class="text-muted">Patients</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded" style="background: rgba(25, 135, 84, 0.1);">
                                <div class="fs-3 fw-bold text-success">{{ $stats['total_appointments'] ?? 0 }}</div>
                                <small class="text-muted">Appointments</small>
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
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.doctor-card, .doctor-stat-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in-up');
    });
});
</script>
@endpush

