@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Manage your practice efficiently')

@section('content')
<div class="fade-in-up">
    <!-- Welcome Hero Section - Transparent and Clean -->
    <div class="doctor-card mb-4" style="background: transparent; border: 1px solid rgba(0, 0, 0, 0.1); box-shadow: none;">
        <div class="doctor-card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="mb-2 fw-bold" style="font-size: 1.75rem; color: #212529;">Welcome back, Dr. {{ Auth::user()->name }}!</h2>
                    <p class="mb-0" style="font-size: 1rem; color: #6c757d;">
                        <i class="fas fa-calendar-day me-2"></i>{{ \Carbon\Carbon::now()->format('l, F j, Y') }}
                        <span class="ms-3"><i class="fas fa-clock me-2"></i><span id="hero-current-time">{{ \Carbon\Carbon::now()->format('h:i A') }}</span></span>
                    </p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('staff.appointments.create') }}" class="btn btn-doctor-primary btn-lg me-2" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i>New Appointment
                    </a>
                    <a href="{{ route('staff.patients.create') }}" class="btn btn-outline-primary btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-user-plus me-2"></i>New Patient
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid - Minimal (Above Quick Actions) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card" style="padding: 0.75rem; cursor: pointer;" onclick="window.location.href='{{ route('staff.appointments.index') }}?date={{ now()->format('Y-m-d') }}'">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-day" style="color: #000; font-size: 1rem;"></i>
                    <div>
                        <div class="doctor-stat-number" style="color: var(--doctor-primary); font-size: 1.25rem; font-weight: 600; margin-bottom: 0.1rem; line-height: 1.2;">
                            {{ $stats['today_appointments'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label" style="font-size: 0.75rem; color: #6c757d; line-height: 1.2;">Today's Appointments</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card" style="padding: 0.75rem; cursor: pointer;" onclick="window.location.href='{{ route('staff.appointments.index') }}?status=pending'">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-clock" style="color: #000; font-size: 1rem;"></i>
                    <div>
                        <div class="doctor-stat-number" style="color: var(--doctor-warning); font-size: 1.25rem; font-weight: 600; margin-bottom: 0.1rem; line-height: 1.2;">
                            {{ $stats['pending_appointments'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label" style="font-size: 0.75rem; color: #6c757d; line-height: 1.2;">Pending Consultations</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card" style="padding: 0.75rem; cursor: pointer;" onclick="window.location.href='{{ route('staff.patients.index') }}'">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-user-injured" style="color: #000; font-size: 1rem;"></i>
                    <div>
                        <div class="doctor-stat-number" style="color: var(--doctor-success); font-size: 1.25rem; font-weight: 600; margin-bottom: 0.1rem; line-height: 1.2;">
                            {{ $stats['total_patients'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label" style="font-size: 0.75rem; color: #6c757d; line-height: 1.2;">Total Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="doctor-stat-card" style="padding: 0.75rem; cursor: pointer;" onclick="window.location.href='{{ route('staff.appointments.index') }}'">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle" style="color: #000; font-size: 1rem;"></i>
                    <div>
                        <div class="doctor-stat-number" style="color: var(--doctor-info); font-size: 1.25rem; font-weight: 600; margin-bottom: 0.1rem; line-height: 1.2;">
                            {{ $stats['total_appointments'] ?? 0 }}
                        </div>
                        <div class="doctor-stat-label" style="font-size: 0.75rem; color: #6c757d; line-height: 1.2;">Total Appointments</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions - Streamlined -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-bolt text-primary"></i>
                Quick Actions
            </h5>
        </div>
        <div class="doctor-card-body">
            <div class="row g-3">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.patients.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="doctor-quick-action-title">New Patient</div>
                        <div class="doctor-quick-action-subtitle">Register</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.appointments.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="doctor-quick-action-title">Schedule</div>
                        <div class="doctor-quick-action-subtitle">Appointment</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.medical-records.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="doctor-quick-action-title">Medical Record</div>
                        <div class="doctor-quick-action-subtitle">Create</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.prescriptions.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="doctor-quick-action-title">Prescription</div>
                        <div class="doctor-quick-action-subtitle">Write</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.lab-reports.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="doctor-quick-action-title">Lab Order</div>
                        <div class="doctor-quick-action-subtitle">Request</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.appointments.calendar') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="doctor-quick-action-title">Full Calendar</div>
                        <div class="doctor-quick-action-subtitle">View All</div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4 mb-4">
        <!-- Calendar Widget - Left Side -->
        <div class="col-xl-8 col-lg-7">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-calendar-alt text-primary"></i>
                            Appointments Calendar
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-sm btn-doctor-primary" title="View Full Calendar">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body p-0">
                    <div id="dashboard-calendar" style="height: 450px; min-height: 450px; width: 100%; background: #f8f9fa; border-radius: 0 0 8px 8px;">
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

        <!-- Today's Schedule - Right Side (Show by default if appointments exist) -->
        <div class="col-xl-4 col-lg-5">
            <div class="doctor-card" id="today-schedule-card-sidebar">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="doctor-card-title mb-0">
                            <i class="fas fa-clock text-info"></i>
                            Today's Schedule
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small" id="current-time">{{ now()->format('H:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body" style="max-height: 450px; overflow-y: auto;">
                    @if(isset($todayAppointments) && $todayAppointments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($todayAppointments->take(10) as $appointment)
                            <div class="list-group-item border-0 px-0 py-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="doctor-user-avatar" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            {{ strtoupper(substr($appointment->patient->first_name ?? 'N', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'TBD' }}
                                                </small>
                                            </div>
                                            <span class="badge 
                                                @if($appointment->status === 'confirmed') bg-success
                                                @elseif($appointment->status === 'pending') bg-warning
                                                @elseif($appointment->status === 'cancelled') bg-danger
                                                @else bg-secondary
                                                @endif
                                            " style="font-size: 0.7rem;">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                                                <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn btn-sm btn-success" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                    <i class="fas fa-video"></i> Join
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($todayAppointments->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('staff.appointments.index') }}?date={{ now()->format('Y-m-d') }}" class="btn btn-sm btn-doctor-primary">
                                View All Today's Appointments <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                            <h6 class="text-muted mb-2">No appointments today</h6>
                            <p class="text-muted small mb-3">You have a free schedule</p>
                            <a href="{{ route('staff.appointments.create') }}" class="btn btn-sm btn-doctor-primary">
                                <i class="fas fa-plus me-1"></i>Schedule Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Appointments & Patients Row -->
    <div class="row g-4">
        <!-- Recent Appointments -->
        <div class="col-xl-8 col-lg-7">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-history text-primary"></i>
                            Recent Appointments
                        </h5>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-doctor-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="doctor-card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAppointments->take(8) as $appointment)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $appointment->appointment_date->format('M d') }}</div>
                                            <small class="text-muted">{{ $appointment->appointment_date->format('Y') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'TBD' }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="doctor-user-avatar me-2" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
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
                                                    <i class="fas fa-video"></i>
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
                                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                                                    <a href="{{ $appointment->meeting_link }}" target="_blank" class="btn btn-success btn-sm" title="Join Meeting">
                                                        <i class="fas fa-video"></i>
                                                    </a>
                                                @endif
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
                            <h6 class="text-muted mb-2">No recent appointments</h6>
                            <p class="text-muted mb-4">Start by booking your first appointment</p>
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
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="doctor-card-title mb-0">
                            <i class="fas fa-users text-success"></i>
                            Recent Patients
                        </h6>
                        <a href="{{ route('staff.patients.index') }}" class="btn btn-sm btn-link text-primary p-0">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="doctor-card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        @foreach($recentAppointments->take(6) as $appointment)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="doctor-user-avatar me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>{{ $appointment->appointment_date->format('M d, Y') }}
                                </small>
                            </div>
                            <a href="{{ route('staff.patients.show', $appointment->patient_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-user-slash fa-2x text-muted mb-2" style="opacity: 0.3;"></i>
                            <p class="text-muted mb-0 small">No recent patients</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Insights -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-chart-line text-info"></i>
                        Quick Insights
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%); border: 1px solid rgba(13, 110, 253, 0.2);">
                                <div class="fs-3 fw-bold text-primary mb-1">{{ $stats['total_patients'] ?? 0 }}</div>
                                <small class="text-muted d-block">Total Patients</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%); border: 1px solid rgba(25, 135, 84, 0.2);">
                                <div class="fs-3 fw-bold text-success mb-1">{{ $stats['total_appointments'] ?? 0 }}</div>
                                <small class="text-muted d-block">Total Appointments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%); border: 1px solid rgba(255, 193, 7, 0.2);">
                                <div class="fs-3 fw-bold text-warning mb-1">{{ $stats['pending_appointments'] ?? 0 }}</div>
                                <small class="text-muted d-block">Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(13, 202, 240, 0.05) 100%); border: 1px solid rgba(13, 202, 240, 0.2);">
                                <div class="fs-3 fw-bold text-info mb-1">{{ $stats['today_appointments'] ?? 0 }}</div>
                                <small class="text-muted d-block">Today</small>
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
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<style>
    /* FullCalendar specific styles for dashboard widget */
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
    
    /* Enhanced Stat Cards */
    .doctor-stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .doctor-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Quick Action Enhancements */
    .doctor-quick-action {
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }
    
    .doctor-quick-action:hover {
        transform: translateY(-3px);
    }
    
    .doctor-quick-action-icon {
        transition: all 0.3s ease;
        color: white;
    }
    
    .doctor-quick-action:hover .doctor-quick-action-icon {
        transform: scale(1.1);
    }
    
    /* Table Enhancements */
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateX(3px);
    }
    
    /* List Group Enhancements */
    .list-group-item {
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.doctor-card, .doctor-stat-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in-up');
    });
    
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
        const heroTimeElement = document.getElementById('hero-current-time');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
        if (heroTimeElement) {
            heroTimeElement.textContent = timeString;
        }
    }
    
    updateTime();
    setInterval(updateTime, 60000);
});
</script>
@endpush

