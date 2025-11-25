@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

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
    
    /* Today's Schedule Toggle Button */
    #toggle-today-schedule {
        transition: all 0.3s ease;
    }
    
    #toggle-today-schedule:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Today's Schedule Card Animations */
    .slide-down-enter-active, .slide-down-leave-active {
        transition: all 0.3s ease-in-out;
        overflow: hidden;
    }
    .slide-down-enter-from, .slide-down-leave-to {
        max-height: 0;
        opacity: 0;
        transform: translateY(-10px);
    }
    .slide-down-enter-to, .slide-down-leave-from {
        max-height: 500px;
        opacity: 1;
        transform: translateY(0);
    }
</style>
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2.5rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        position: relative;
        overflow: hidden;
    }

    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .dashboard-hero-content {
        position: relative;
        z-index: 1;
    }

    .modern-stat-card-dashboard {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .modern-stat-card-dashboard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--gradient-start, #667eea), var(--gradient-end, #764ba2));
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .modern-stat-card-dashboard:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .modern-stat-card-dashboard:hover::before {
        transform: scaleX(1);
    }

    .stat-card-icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 1.25rem;
        position: relative;
    }

    .stat-card-icon-wrapper::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 20px;
        padding: 4px;
        background: linear-gradient(135deg, var(--icon-gradient-start), var(--icon-gradient-end));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .modern-stat-card-dashboard:hover .stat-card-icon-wrapper::after {
        opacity: 0.3;
    }

    .stat-card-icon-wrapper.primary,
    .stat-card-icon-wrapper.success,
    .stat-card-icon-wrapper.info,
    .stat-card-icon-wrapper.warning {
        background: #000000 !important;
        color: white;
        --icon-gradient-start: #000000;
        --icon-gradient-end: #000000;
    }

    .stat-number-modern {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }

    .stat-label-modern {
        font-size: 0.95rem;
        font-weight: 600;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }

    .stat-subtitle-modern {
        font-size: 0.85rem;
        color: #718096;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-trend-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.75rem;
    }

    .stat-trend-badge.positive {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .stat-trend-badge.negative {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .modern-chart-card {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }

    .chart-header-modern {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f7fafc;
    }

    .chart-title-modern {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }

    .modern-table-card {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }

    .table-header-modern {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f7fafc;
    }

    .table-modern {
        margin: 0;
    }

    .table-modern thead th {
        border: none;
        padding: 1rem 0.75rem;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #718096;
        background: #f7fafc;
    }

    .table-modern tbody td {
        border: none;
        padding: 1.25rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .table-modern tbody tr {
        transition: all 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background: #f8fafc;
        transform: translateX(4px);
    }

    .patient-avatar-modern {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: white;
        flex-shrink: 0;
    }

    .quick-action-card {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: none;
        height: 100%;
    }

    .quick-action-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
        margin-bottom: 0.75rem;
    }

    .quick-action-item:hover {
        background: #f7fafc;
        transform: translateX(4px);
    }

    .quick-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        flex-shrink: 0;
    }

    .empty-state-modern {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: #667eea;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Hero Section -->
    <div class="dashboard-hero fade-in-up">
        <div class="dashboard-hero-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="mb-2 fw-bold" style="font-size: 2.5rem;">Welcome back, {{ Auth::user()->name }}!</h1>
                    <p class="mb-0 opacity-90" style="font-size: 1.1rem;">Here's your hospital overview for today</p>
                    <div class="mt-3 d-flex gap-3 flex-wrap">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-day"></i>
                            <span>{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-clock"></i>
                            <span id="currentTime">{{ \Carbon\Carbon::now()->format('h:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-3 mt-md-0">
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.appointments.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-plus me-2"></i>New Appointment
                        </a>
                        <a href="{{ route('admin.patients.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                            <i class="fas fa-user-plus me-2"></i>New Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Patients -->
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.patients.index') }}" class="text-decoration-none">
                <div class="modern-stat-card-dashboard fade-in-up stagger-1">
                    <div class="stat-card-icon-wrapper primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number-modern">{{ number_format($stats['total_patients'] ?? 0) }}</div>
                    <div class="stat-label-modern">Total Patients</div>
                    <div class="stat-subtitle-modern">
                        <i class="fas fa-user-plus"></i>
                        <span>{{ $stats['today_patients'] ?? 0 }} registered today</span>
                    </div>
                    @if(isset($stats['patient_growth']) && $stats['patient_growth'] > 0)
                        <span class="stat-trend-badge positive">
                            <i class="fas fa-arrow-up"></i>
                            +{{ $stats['patient_growth'] }}% this month
                        </span>
                    @endif
                </div>
            </a>
        </div>

        <!-- Total Appointments -->
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.appointments.index') }}" class="text-decoration-none">
                <div class="modern-stat-card-dashboard fade-in-up stagger-2">
                    <div class="stat-card-icon-wrapper success">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number-modern">{{ number_format($stats['total_appointments'] ?? 0) }}</div>
                    <div class="stat-label-modern">Total Appointments</div>
                    <div class="stat-subtitle-modern">
                        <i class="fas fa-calendar-day"></i>
                        <span>{{ $stats['today_appointments'] ?? 0 }} scheduled today</span>
                    </div>
                    @if(isset($stats['appointment_growth']) && $stats['appointment_growth'] > 0)
                        <span class="stat-trend-badge positive">
                            <i class="fas fa-arrow-up"></i>
                            +{{ $stats['appointment_growth'] }}% this month
                        </span>
                    @endif
                </div>
            </a>
        </div>

        <!-- Total Doctors -->
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.doctors.index') }}" class="text-decoration-none">
                <div class="modern-stat-card-dashboard fade-in-up stagger-3">
                    <div class="stat-card-icon-wrapper info">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-number-modern">{{ number_format($stats['total_doctors'] ?? 0) }}</div>
                    <div class="stat-label-modern">Total Doctors</div>
                    <div class="stat-subtitle-modern">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $stats['active_doctors'] ?? 0 }} active</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Total Departments -->
        <div class="col-xl-3 col-md-6">
            <a href="{{ route('admin.departments.index') }}" class="text-decoration-none">
                <div class="modern-stat-card-dashboard fade-in-up stagger-4">
                    <div class="stat-card-icon-wrapper warning">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-number-modern">{{ number_format($stats['total_departments'] ?? 0) }}</div>
                    <div class="stat-label-modern">Departments</div>
                    <div class="stat-subtitle-modern">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ $stats['active_departments'] ?? 0 }} active</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Calendar Widget - Moved to top for visibility -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="modern-card-title mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Appointments Calendar
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="toggle-today-schedule" title="Toggle Today's Schedule">
                                <i class="fas fa-list"></i>
                            </button>
                            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-sm btn-modern-primary">
                                <i class="fas fa-external-link-alt me-1"></i>View Full Calendar
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modern-card-body p-0">
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
    </div>

    <!-- Today's Schedule (Collapsible) -->
    <div class="row g-4 mb-4" id="today-schedule-card" style="display: none;">
        <div class="col-12">
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="modern-card-title mb-0">
                            <i class="fas fa-clock me-2 text-info"></i>Today's Schedule
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small" id="current-time-schedule">{{ now()->format('H:i A') }}</span>
                            <button type="button" class="btn btn-sm btn-link text-muted p-0" id="close-today-schedule" title="Close">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modern-card-body">
                    @if(isset($todaysAppointments) && $todaysAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-modern">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Doctor</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaysAppointments->take(10) as $appointment)
                                    <tr>
                                        <td>
                                            <strong>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'TBD' }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @php
                                                    $patientName = $appointment->patient ? ($appointment->patient->first_name . ' ' . $appointment->patient->last_name) : 'N/A';
                                                    $patientInitial = $appointment->patient ? strtoupper(substr($appointment->patient->first_name ?? 'N', 0, 1)) : 'N';
                                                @endphp
                                                <div class="patient-avatar-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 32px; height: 32px; font-size: 0.75rem;">
                                                    {{ $patientInitial }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $patientName }}</div>
                                                    <small class="text-muted">#{{ $appointment->appointment_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $appointment->doctor ? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name : 'Not assigned' }}</span>
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
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'success',
                                                    'completed' => 'info',
                                                    'cancelled' => 'danger'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}" style="padding: 0.5rem 1rem; border-radius: 8px;">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-outline-secondary btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($todaysAppointments->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.appointments.index') }}?date={{ now()->format('Y-m-d') }}" class="btn btn-sm btn-outline-primary">
                                View All Today's Appointments <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                            <h6 class="text-muted mb-2">No appointments scheduled for today</h6>
                            <p class="text-muted mb-4">Take some time to catch up on other tasks</p>
                            <a href="{{ route('admin.appointments.create') }}" class="btn btn-modern-primary">
                                <i class="fas fa-plus me-2"></i>Schedule New Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Appointment Chart -->
        <div class="col-lg-8">
            <div class="modern-chart-card fade-in-up">
                <div class="chart-header-modern">
                    <h5 class="chart-title-modern">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Appointment Overview (Last 30 Days)
                    </h5>
                </div>
                <div class="chart-body" style="height: 350px; position: relative;">
                    <canvas id="appointmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Patient Registration Chart -->
        <div class="col-lg-4">
            <div class="modern-chart-card fade-in-up">
                <div class="chart-header-modern">
                    <h5 class="chart-title-modern">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Patient Registration
                    </h5>
                </div>
                <div class="chart-body" style="height: 350px; position: relative;">
                    <canvas id="patientChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4 mb-4">
        <!-- Recent Appointments -->
        <div class="col-lg-8">
            <div class="modern-table-card fade-in-up">
                <div class="table-header-modern">
                    <h5 class="chart-title-modern mb-0">
                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Recent Appointments
                    </h5>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAppointments as $appointment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $patientName = $appointment->patient ? ($appointment->patient->first_name . ' ' . $appointment->patient->last_name) : 'N/A';
                                                $patientInitial = $appointment->patient ? strtoupper(substr($appointment->patient->first_name ?? 'N', 0, 1)) : 'N';
                                                $patientEmail = $appointment->patient->email ?? 'N/A';
                                            @endphp
                                            <div class="patient-avatar-modern" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                {{ $patientInitial }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $patientName }}</div>
                                                <small class="text-muted">{{ $patientEmail }}</small>
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
                                        <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}" style="padding: 0.5rem 1rem; border-radius: 8px;">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="empty-state-modern">
                        <div class="empty-state-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h6 class="text-muted">No appointments yet</h6>
                        <p class="text-muted mb-0">New appointments will appear here.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions & Today's Summary -->
        <div class="col-lg-4">
            <!-- Pending Appointments -->
            <div class="quick-action-card fade-in-up mb-4">
                <div class="table-header-modern">
                    <h5 class="chart-title-modern mb-0">
                        <i class="fas fa-clock me-2 text-warning"></i>Pending
                    </h5>
                    <span class="badge bg-warning" style="padding: 0.5rem 0.75rem; border-radius: 8px;">{{ $stats['pending_appointments'] ?? 0 }}</span>
                </div>
                @if(isset($pendingAppointments) && $pendingAppointments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($pendingAppointments->take(5) as $appointment)
                        <div class="quick-action-item">
                            <div class="quick-action-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $appointment->patient ? ($appointment->patient->first_name . ' ' . $appointment->patient->last_name) : 'N/A' }}</div>
                                <small class="text-muted">{{ $appointment->doctor ? ($appointment->doctor->first_name . ' ' . $appointment->doctor->last_name) : 'N/A' }} - {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-modern" style="padding: 2rem 1rem;">
                        <i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0 mt-2">No pending appointments</p>
                    </div>
                @endif
            </div>

            <!-- Today's Appointments -->
            <div class="quick-action-card fade-in-up">
                <div class="table-header-modern">
                    <h5 class="chart-title-modern mb-0">
                        <i class="fas fa-calendar-day me-2 text-info"></i>Today
                    </h5>
                    <span class="badge bg-info" style="padding: 0.5rem 0.75rem; border-radius: 8px;">{{ $stats['today_appointments'] ?? 0 }}</span>
                </div>
                @if(isset($todaysAppointments) && $todaysAppointments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($todaysAppointments->take(5) as $appointment)
                        <div class="quick-action-item">
                            <div class="quick-action-icon" style="background: linear-gradient(135deg, #3494E6 0%, #EC6EAD 100%);">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $appointment->patient ? ($appointment->patient->first_name . ' ' . $appointment->patient->last_name) : 'N/A' }}</div>
                                <small class="text-muted">{{ $appointment->appointment_time }} - {{ $appointment->doctor ? ($appointment->doctor->first_name . ' ' . $appointment->doctor->last_name) : 'N/A' }}</small>
                            </div>
                            <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : 'warning' }}" style="border-radius: 8px;">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-modern" style="padding: 2rem 1rem;">
                        <i class="fas fa-calendar-day text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0 mt-2">No appointments today</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom Row -->
    <div class="row g-4">
        <!-- Recent Patients -->
        <div class="col-lg-6">
            <div class="modern-table-card fade-in-up">
                <div class="table-header-modern">
                    <h5 class="chart-title-modern mb-0">
                        <i class="fas fa-users me-2 text-success"></i>Recent Patients
                    </h5>
                    <a href="{{ route('admin.patients.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @if(isset($recentPatients) && $recentPatients->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentPatients->take(8) as $patient)
                        <div class="quick-action-item">
                            <div class="patient-avatar-modern" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                {{ strtoupper(substr($patient->first_name ?? 'N', 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                <small class="text-muted">{{ $patient->email ?? 'N/A' }}</small>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Registered {{ $patient->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success mb-2" style="border-radius: 8px;">Active</span>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-phone me-1"></i>{{ $patient->phone ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-modern">
                        <div class="empty-state-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h6 class="text-muted">No patients yet</h6>
                        <p class="text-muted mb-0">New patient registrations will appear here.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Department Overview -->
        <div class="col-lg-6">
            <div class="modern-table-card fade-in-up">
                <div class="table-header-modern">
                    <h5 class="chart-title-modern mb-0">
                        <i class="fas fa-building me-2 text-warning"></i>Department Overview
                    </h5>
                    <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius: 8px;">
                        Manage <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @if(isset($departmentStats) && $departmentStats->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($departmentStats->take(8) as $dept)
                        <div class="quick-action-item">
                            <div class="quick-action-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                <i class="fas fa-hospital-alt"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $dept['name'] ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $dept['doctors'] ?? 0 }} doctors</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary mb-2" style="border-radius: 8px; padding: 0.5rem 0.75rem;">{{ $dept['appointments'] ?? 0 }}</span>
                                <br>
                                <small class="text-muted">appointments</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state-modern">
                        <div class="empty-state-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h6 class="text-muted">No departments yet</h6>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- FullCalendar JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    // Update time every minute
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        document.getElementById('currentTime').textContent = timeString;
    }
    setInterval(updateTime, 60000);

    // Chart data
    const appointmentData = @json($appointmentChartData ?? []);
    const patientData = @json($patientRegistrationData ?? []);
    
    // Appointment Chart
    const appointmentCtx = document.getElementById('appointmentChart');
    if (appointmentCtx) {
        new Chart(appointmentCtx, {
            type: 'line',
            data: {
                labels: appointmentData.map(d => {
                    const date = new Date(d.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }) || [],
                datasets: [{
                    label: 'Appointments',
                    data: appointmentData.map(d => d.count) || [],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Patient Registration Chart
    const patientCtx = document.getElementById('patientChart');
    if (patientCtx) {
        new Chart(patientCtx, {
            type: 'bar',
            data: {
                labels: patientData.map(d => d.month) || [],
                datasets: [{
                    label: 'New Patients',
                    data: patientData.map(d => d.patients) || [],
                    backgroundColor: 'rgba(52, 148, 230, 0.8)',
                    borderColor: '#3494E6',
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

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
                    
                    fetch(`{{ route('admin.api.appointments.calendar-data') }}?${params}`, {
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
                            url: `{{ route('admin.appointments.show', '') }}/${appointment.id}`
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
    
    // Toggle Today's Schedule
    const toggleButton = document.getElementById('toggle-today-schedule');
    const todayScheduleCard = document.getElementById('today-schedule-card');
    const closeScheduleButton = document.getElementById('close-today-schedule');

    if (toggleButton && todayScheduleCard && closeScheduleButton) {
        toggleButton.addEventListener('click', function() {
            if (todayScheduleCard.style.display === 'none') {
                todayScheduleCard.style.display = 'block';
                todayScheduleCard.classList.remove('slide-down-leave-active', 'slide-down-leave-to');
                todayScheduleCard.classList.add('slide-down-enter-active', 'slide-down-enter-to');
                toggleButton.innerHTML = '<i class="fas fa-calendar-alt"></i>';
                toggleButton.title = "View Calendar";
            } else {
                todayScheduleCard.classList.remove('slide-down-enter-active', 'slide-down-enter-to');
                todayScheduleCard.classList.add('slide-down-leave-active', 'slide-down-leave-to');
                todayScheduleCard.addEventListener('transitionend', function handler() {
                    if (todayScheduleCard.classList.contains('slide-down-leave-to')) {
                        todayScheduleCard.style.display = 'none';
                    }
                    todayScheduleCard.removeEventListener('transitionend', handler);
                });
                toggleButton.innerHTML = '<i class="fas fa-list"></i>';
                toggleButton.title = "View Today's Schedule";
            }
        });

        closeScheduleButton.addEventListener('click', function() {
            todayScheduleCard.classList.remove('slide-down-enter-active', 'slide-down-enter-to');
            todayScheduleCard.classList.add('slide-down-leave-active', 'slide-down-leave-to');
            todayScheduleCard.addEventListener('transitionend', function handler() {
                if (todayScheduleCard.classList.contains('slide-down-leave-to')) {
                    todayScheduleCard.style.display = 'none';
                }
                todayScheduleCard.removeEventListener('transitionend', handler);
            });
            toggleButton.innerHTML = '<i class="fas fa-list"></i>';
            toggleButton.title = "View Today's Schedule";
        });
    }
    
    // Update current time every minute
    function updateScheduleTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        const timeElement = document.getElementById('current-time-schedule');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
    }
    
    updateScheduleTime();
    setInterval(updateScheduleTime, 60000);

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

