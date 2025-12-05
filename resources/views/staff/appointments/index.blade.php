@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('page-subtitle', auth()->user()->role === 'doctor' ? 'Manage your appointments and patient consultations' : (auth()->user()->role === 'nurse' ? 'Assist with appointment coordination and patient care' : 'Schedule and manage patient appointments'))

@push('header-actions')
<div class="d-flex gap-2">
    <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-primary">
        <i class="fas fa-calendar-alt me-1"></i>Calendar View
    </a>
    @if(auth()->user()->can('appointments.create'))
    <a href="{{ route('staff.appointments.create') }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i>New Appointment
    </a>
    @endif
</div>
@endpush

@push('styles')
<style>
    /* Fix modal positioning */
    body.modal-open {
        overflow: hidden;
    }
    .modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 10000 !important;
        width: 100% !important;
        height: 100% !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }
    .modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        z-index: 9999 !important;
        width: 100vw !important;
        height: 100vh !important;
    }
    .modal-dialog {
        position: relative;
        width: auto;
        margin: 1.75rem auto;
    }
</style>
@endpush

@section('content')
<div class="fade-in-up">
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $appointments->total() }}</div>
                        <div class="stat-label">Total Appointments</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $appointments->filter(function($appointment) { return $appointment->status === 'pending'; })->count() }}
                        </div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $appointments->filter(function($appointment) { return $appointment->status === 'confirmed'; })->count() }}
                        </div>
                        <div class="stat-label">Confirmed</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $appointments->filter(function($appointment) { return $appointment->appointment_date->isToday(); })->count() }}
                        </div>
                        <div class="stat-label">Today's Appointments</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search Bar -->
    <div class="doctor-card mb-3">
        <div class="doctor-card-body">
            <div class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label class="form-label fw-semibold">Quick Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               id="quickSearch" 
                               name="search" 
                               class="form-control form-control-lg" 
                               placeholder="Search by appointment #, patient name, email, phone, or doctor..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-doctor-primary" onclick="toggleFilters()">
                        <i class="fas fa-filter me-1"></i>Filters
                        @php
                            $activeFiltersCount = count(array_filter(request()->except(['page', 'search'])));
                        @endphp
                        @if($activeFiltersCount > 0)
                            <span class="badge bg-primary ms-1">{{ $activeFiltersCount }}</span>
                        @endif
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-info">
                        <i class="fas fa-calendar-alt me-1"></i>Calendar View
                    </a>
                    <a href="{{ route('staff.appointments.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-1"></i>New Appointment
                    </a>
                </div>
                <div>
                    @if(request()->hasAny(['search', 'status', 'appointment_type', 'is_online', 'date_from', 'date_to', 'doctor_id', 'department_id']))
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear All
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters Chips -->
    @php
        $activeFilters = [];
        if(request('status')) $activeFilters[] = ['key' => 'status', 'label' => 'Status: ' . ucfirst(request('status'))];
        if(request('appointment_type')) $activeFilters[] = ['key' => 'appointment_type', 'label' => 'Type: ' . ucfirst(str_replace('_', ' ', request('appointment_type')))];
        if(request('is_online')) $activeFilters[] = ['key' => 'is_online', 'label' => 'Consultation: ' . (request('is_online') == '1' ? 'Online' : 'In-Person')];
        if(request('date_from')) $activeFilters[] = ['key' => 'date_from', 'label' => 'Date From: ' . request('date_from')];
        if(request('date_to')) $activeFilters[] = ['key' => 'date_to', 'label' => 'Date To: ' . request('date_to')];
        if(request('doctor_id')) {
            $doc = collect($doctors)->firstWhere('id', request('doctor_id'));
            if($doc) $activeFilters[] = ['key' => 'doctor_id', 'label' => 'Doctor: ' . $doc['name']];
        }
        if(request('department_id')) {
            $dept = $departments->firstWhere('id', request('department_id'));
            if($dept) $activeFilters[] = ['key' => 'department_id', 'label' => 'Department: ' . $dept->name];
        }
        if(request('date_range')) $activeFilters[] = ['key' => 'date_range', 'label' => 'Range: ' . ucfirst(str_replace('_', ' ', request('date_range')))];
        if(request('overdue')) $activeFilters[] = ['key' => 'overdue', 'label' => 'Overdue: Yes'];
    @endphp
    @if(count($activeFilters) > 0)
        <div class="mb-3" id="activeFilters">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small">Active filters:</span>
                @foreach($activeFilters as $filter)
                    <span class="badge bg-primary d-flex align-items-center gap-1">
                        {{ $filter['label'] }}
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.65rem;" onclick="removeFilter('{{ $filter['key'] }}')"></button>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter Sidebar (Collapsible) -->
    <div class="doctor-card mb-4" id="filterPanel" style="display: {{ request()->hasAny(['status', 'appointment_type', 'is_online', 'date_from', 'date_to', 'doctor_id', 'department_id', 'date_range']) ? 'block' : 'none' }};">
        <div class="doctor-card-header">
            <h6 class="doctor-card-title mb-0">
                <i class="fas fa-filter me-2"></i>Advanced Filters
            </h6>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.appointments.index') }}" id="filterForm">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                <div class="row g-3">
                    <!-- Status & Type Section -->
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-check me-2"></i>Status & Type
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Appointment Type</label>
                        <select name="appointment_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="consultation" {{ request('appointment_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                            <option value="follow_up" {{ request('appointment_type') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                            <option value="routine_checkup" {{ request('appointment_type') == 'routine_checkup' ? 'selected' : '' }}>Routine Checkup</option>
                            <option value="emergency" {{ request('appointment_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Consultation Type</label>
                        <select name="consultation_type" class="form-control">
                            <option value="">All</option>
                            <option value="online" {{ request('consultation_type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="in_person" {{ request('consultation_type') == 'in_person' ? 'selected' : '' }}>In-Person</option>
                            <option value="phone" {{ request('consultation_type') == 'phone' ? 'selected' : '' }}>Phone</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Meeting Platform</label>
                        <select name="meeting_platform" class="form-control">
                            <option value="">All Platforms</option>
                            <option value="zoom" {{ request('meeting_platform') == 'zoom' ? 'selected' : '' }}>Zoom</option>
                            <option value="google_meet" {{ request('meeting_platform') == 'google_meet' ? 'selected' : '' }}>Google Meet</option>
                            <option value="teams" {{ request('meeting_platform') == 'teams' ? 'selected' : '' }}>Microsoft Teams</option>
                            <option value="whereby" {{ request('meeting_platform') == 'whereby' ? 'selected' : '' }}>Whereby</option>
                            <option value="custom" {{ request('meeting_platform') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>

                    <!-- Patient & Doctor Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-user-md me-2"></i>Patient & Doctor
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Patient Name</label>
                        <input type="text" name="patient_name" class="form-control" value="{{ request('patient_name') }}" placeholder="Search patient...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Doctor</label>
                        <select name="doctor_id" class="form-control">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor['id'] }}" {{ request('doctor_id') == $doctor['id'] ? 'selected' : '' }}>
                                    {{ $doctor['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-info border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>Date & Time
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quick Date Range</label>
                        <select name="date_range" class="form-control">
                            <option value="">Select Range</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="tomorrow" {{ request('date_range') == 'tomorrow' ? 'selected' : '' }}>Tomorrow</option>
                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="next_week" {{ request('date_range') == 'next_week' ? 'selected' : '' }}>Next Week</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="next_month" {{ request('date_range') == 'next_month' ? 'selected' : '' }}>Next Month</option>
                            <option value="upcoming" {{ request('date_range') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="past" {{ request('date_range') == 'past' ? 'selected' : '' }}>Past</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Specific Date</label>
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Time From</label>
                        <input type="time" name="time_from" class="form-control" value="{{ request('time_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Time To</label>
                        <input type="time" name="time_to" class="form-control" value="{{ request('time_to') }}">
                    </div>

                    <!-- Additional Filters Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-warning border-bottom pb-2 mb-3">
                            <i class="fas fa-filter me-2"></i>Additional Filters
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Medical Record</label>
                        <select name="has_medical_record" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_medical_record') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_medical_record') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Checked In</label>
                        <select name="checked_in" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('checked_in') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('checked_in') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Checked Out</label>
                        <select name="checked_out" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('checked_out') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('checked_out') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Overdue</label>
                        <select name="overdue" class="form-control">
                            <option value="">All</option>
                            <option value="1" {{ request('overdue') ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Conflict</label>
                        <select name="has_conflict" class="form-control">
                            <option value="">All</option>
                            <option value="1" {{ request('has_conflict') ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fee Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="fee_min" class="form-control" placeholder="Min" value="{{ request('fee_min') }}" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" name="fee_max" class="form-control" placeholder="Max" value="{{ request('fee_max') }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reason/Symptoms</label>
                        <input type="text" name="reason" class="form-control" value="{{ request('reason') }}" placeholder="Search reason...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Symptoms</label>
                        <input type="text" name="symptoms" class="form-control" value="{{ request('symptoms') }}" placeholder="Search symptoms...">
                    </div>

                    <!-- Form Actions -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-list me-2"></i>Appointments
                <small class="text-muted">({{ $appointments->total() }} total)</small>
            </h5>
        </div>
        <div class="doctor-card-body">
            @if($appointments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="appointmentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Appointment #</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Appointment Details</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $appointment->appointment_number }}</div>
                                    @if($appointment->department)
                                        <small class="text-muted">{{ $appointment->department->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                            <small class="text-muted">{{ $appointment->patient->phone ?? 'No phone' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($appointment->doctor)
                                        <div class="fw-bold text-success">{{ formatDoctorName($appointment->doctor->name) }}</div>
                                        <small class="text-muted">{{ $appointment->doctor->specialization ?? 'General' }}</small>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $appointment->type)) }}</div>
                                    @if($appointment->reason)
                                        <small class="text-muted">{{ Str::limit($appointment->reason, 30) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ formatDate($appointment->appointment_date) }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</small>
                                    @if($appointment->is_online)
                                        <br><span class="badge bg-info mt-1">
                                            <i class="fas fa-video me-1"></i>Online
                                            @if($appointment->meeting_platform)
                                                - {{ $appointment->meeting_platform_name }}
                                            @endif
                                        </span>
                                    @else
                                        <br><span class="badge bg-secondary mt-1">
                                            <i class="fas fa-building me-1"></i>In-Person
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'completed' => 'primary',
                                            'cancelled' => 'danger'
                                        ];
                                        $color = $statusColors[$appointment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($appointment->status) }}</span>
                                    @if($appointment->completed_at)
                                        <div><small class="text-muted">{{ formatDate($appointment->completed_at) }}</small></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($appointment->is_online && $appointment->meeting_link && $appointment->canJoinMeeting())
                                            <a href="{{ $appointment->meeting_link }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-success" 
                                               title="Join Meeting">
                                                <i class="fas fa-video"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('staff.appointments.show', $appointment->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        {{-- Edit button for appropriate users and statuses --}}
                                        @if(
                                            (auth()->user()->role === 'admin') ||
                                            (auth()->user()->role === 'doctor' && $appointment->doctor_id === auth()->user()->id && in_array($appointment->status, ['pending', 'confirmed'])) ||
                                            (in_array(auth()->user()->role, ['nurse', 'receptionist']) && $appointment->status === 'pending')
                                        )
                                            <a href="{{ route('staff.appointments.edit', $appointment->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit Appointment">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        {{-- Confirm appointment --}}
                                        @if($appointment->status === 'pending' && in_array(auth()->user()->role, ['admin', 'doctor', 'nurse', 'receptionist']))
                                            <button type="button" class="btn btn-sm btn-outline-success update-status-btn" 
                                                    data-appointment-id="{{ $appointment->id }}" 
                                                    data-status="confirmed"
                                                    title="Confirm Appointment">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Complete appointment (only doctors) --}}
                                        @if($appointment->status === 'confirmed' && auth()->user()->role === 'doctor' && $appointment->doctor_id === auth()->user()->id)
                                            <button type="button" class="btn btn-sm btn-outline-info update-status-btn" 
                                                    data-appointment-id="{{ $appointment->id }}" 
                                                    data-status="completed"
                                                    title="Mark as Completed">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Cancel appointment --}}
                                        @if(
                                            in_array($appointment->status, ['pending', 'confirmed']) &&
                                            (
                                                auth()->user()->role === 'admin' ||
                                                (auth()->user()->role === 'doctor' && $appointment->doctor_id === auth()->user()->id) ||
                                                in_array(auth()->user()->role, ['nurse', 'receptionist'])
                                            )
                                        )
                                            <button type="button" class="btn btn-sm btn-outline-danger update-status-btn" 
                                                    data-appointment-id="{{ $appointment->id }}" 
                                                    data-status="cancelled"
                                                    title="Cancel Appointment">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Create medical record from appointment --}}
                                        @if($appointment->status === 'completed' && in_array(auth()->user()->role, ['doctor', 'nurse']))
                                            <a href="{{ route('staff.medical-records.create', ['appointment_id' => $appointment->id]) }}" 
                                               class="btn btn-sm btn-outline-info" title="Create Medical Record">
                                                <i class="fas fa-file-medical"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} 
                        of {{ $appointments->total() }} results
                    </div>
                    <div>
                        {{ $appointments->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-calendar-alt fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Appointments Found</h5>
                    <p class="text-muted mb-4">
                        Start by scheduling your first appointment.
                    </p>
                    
                    <a href="{{ route('staff.appointments.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Schedule First Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<!-- Status Update Modal (placed outside to avoid z-index issues) -->
<div class="modal fade" id="statusModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Appointment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status_select" class="form-label">New Status</label>
                        <select id="status_select" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="staff_notes" class="form-label">Notes (Optional)</label>
                        <textarea id="staff_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-doctor-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Debounced Quick Search
let searchTimeout;
$(document).ready(function() {
    $('#quickSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = $(this).val();
        
        searchTimeout = setTimeout(function() {
            const url = new URL(window.location.href);
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }, 400); // 400ms debounce
    });

    // Toggle filter panel
    window.toggleFilters = function() {
        const panel = document.getElementById('filterPanel');
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    };

    // Remove individual filter
    window.removeFilter = function(filterKey) {
        const url = new URL(window.location.href);
        url.searchParams.delete(filterKey);
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    };

    // Initialize DataTable
    if ($('#appointmentsTable').length) {
        $('#appointmentsTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "ordering": true,
            "order": [[ 4, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ]
        });
    }
});

let currentAppointmentId = null;

// Make updateStatus globally accessible (for any legacy calls)
window.updateStatus = function(appointmentId, status = null) {
    currentAppointmentId = appointmentId;

    if (status) {
        $('#status_select').val(status);
    }

    // Use Bootstrap 5 native modal API
    var modalElement = document.getElementById('statusModal');
    if (modalElement) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    }
};

// Event delegation for update status buttons (works with DataTables)
$(document).on('click', '.update-status-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();

    const appointmentId = $(this).data('appointment-id');
    const status = $(this).data('status');

    if (!appointmentId) {
        alert('Error: Appointment ID not found');
        return;
    }

    currentAppointmentId = appointmentId;

    if (status) {
        $('#status_select').val(status);
    }

    // Use Bootstrap 5 native modal API
    var modalElement = document.getElementById('statusModal');
    if (modalElement) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();
    }
});

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!currentAppointmentId) {
        alert('Error: No appointment selected');
        return;
    }
    
    const status = $('#status_select').val();
    const notes = $('#staff_notes').val();
    
    // Disable submit button
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
    
    $.ajax({
        url: `/staff/appointments/${currentAppointmentId}/status`,
        method: 'PATCH',
        data: {
            status: status,
            notes: notes,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            // Use Bootstrap 5 native modal API to hide
            var modalElement = document.getElementById('statusModal');
            if (modalElement) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
            if (response.success) {
                location.reload();
            } else {
                alert('Error updating status: ' + (response.message || 'Unknown error'));
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            submitBtn.prop('disabled', false).html(originalText);
            const errorMessage = xhr.responseJSON && xhr.responseJSON.message 
                ? xhr.responseJSON.message 
                : 'Error updating appointment status. Please try again.';
            alert(errorMessage);
        }
    });
});
</script>
@endpush
