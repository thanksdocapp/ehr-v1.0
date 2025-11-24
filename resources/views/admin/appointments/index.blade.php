@extends('admin.layouts.app')

@section('title', 'Appointments Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Appointments</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">Appointments Management</h1>
                    <p class="modern-page-subtitle">Manage and track all patient appointments</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ contextRoute('appointments.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i>New Appointment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Search Bar -->
    <div class="modern-card mb-3">
        <div class="modern-card-body">
            <div class="d-flex gap-2 align-items-end flex-wrap">
                <div class="flex-grow-1" style="min-width: 300px;">
                    <label class="modern-form-label">Quick Search</label>
                    <div class="modern-input-group">
                        <i class="fas fa-search modern-input-group-icon"></i>
                        <input type="text" 
                               id="quickSearch" 
                               name="search" 
                               class="modern-form-control" 
                               placeholder="Search by appointment #, patient name, email, phone, or doctor..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div>
                    <button type="button" class="btn-modern btn-modern-outline" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i>Filters
                        @php
                            $activeFiltersCount = count(array_filter(request()->except(['page', 'search'])));
                        @endphp
                        @if($activeFiltersCount > 0)
                            <span class="badge-modern badge-modern-primary ms-1">{{ $activeFiltersCount }}</span>
                        @endif
                    </button>
                </div>
                <div>
                    @if(request()->hasAny(['search', 'status', 'type', 'is_online', 'date_from', 'date_to', 'doctor_id', 'department_id']))
                        <a href="{{ contextRoute('appointments.index') }}" class="btn-modern btn-modern-outline">
                            <i class="fas fa-times"></i>Clear All
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
        if(request('type')) $activeFilters[] = ['key' => 'type', 'label' => 'Type: ' . ucfirst(request('type'))];
        if(request('is_online')) $activeFilters[] = ['key' => 'is_online', 'label' => 'Consultation: ' . (request('is_online') == '1' ? 'Online' : 'In-Person')];
        if(request('date_from')) $activeFilters[] = ['key' => 'date_from', 'label' => 'Date From: ' . request('date_from')];
        if(request('date_to')) $activeFilters[] = ['key' => 'date_to', 'label' => 'Date To: ' . request('date_to')];
        if(request('doctor_id')) {
            $doc = collect($doctors)->first(function($d) { return $d->id == request('doctor_id'); });
            if($doc) $activeFilters[] = ['key' => 'doctor_id', 'label' => 'Doctor: ' . ($doc->full_name ?? $doc->first_name . ' ' . $doc->last_name)];
        }
        if(request('department_id')) {
            $dept = collect($departments)->firstWhere('id', request('department_id'));
            if($dept) $activeFilters[] = ['key' => 'department_id', 'label' => 'Department: ' . $dept->name];
        }
        if(request('date_range')) $activeFilters[] = ['key' => 'date_range', 'label' => 'Range: ' . ucfirst(str_replace('_', ' ', request('date_range')))];
        if(request('overdue')) $activeFilters[] = ['key' => 'overdue', 'label' => 'Overdue: Yes'];
        if(request('meeting_platform')) $activeFilters[] = ['key' => 'meeting_platform', 'label' => 'Platform: ' . ucfirst(str_replace('_', ' ', request('meeting_platform')))];
    @endphp
    @if(count($activeFilters) > 0)
        <div class="mb-3" id="activeFilters">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small">Active filters:</span>
                @foreach($activeFilters as $filter)
                    <span class="badge-modern badge-modern-primary d-flex align-items-center gap-1">
                        {{ $filter['label'] }}
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.65rem;" onclick="removeFilter('{{ $filter['key'] }}')"></button>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Modern Filter Panel -->
    <div class="modern-card mb-4" id="filterPanel" style="display: {{ request()->hasAny(['status', 'type', 'is_online', 'date_from', 'date_to', 'doctor_id', 'department_id', 'date_range']) ? 'block' : 'none' }};">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-filter"></i>Advanced Filters
            </h5>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ contextRoute('appointments.index') }}" id="filterForm">
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
                            @foreach(['pending', 'confirmed', 'completed', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Appointment Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="consultation" {{ request('type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                            <option value="followup" {{ request('type') == 'followup' ? 'selected' : '' }}>Follow Up</option>
                            <option value="checkup" {{ request('type') == 'checkup' ? 'selected' : '' }}>Checkup</option>
                            <option value="emergency" {{ request('type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                            <option value="surgery" {{ request('type') == 'surgery' ? 'selected' : '' }}>Surgery</option>
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
                            @foreach($doctors ?? [] as $doctor)
                                <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->full_name ?? $doctor->first_name . ' ' . $doctor->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
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
                        <input type="text" name="date_from" id="date_from" class="form-control" 
                               value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                               placeholder="dd-mm-yyyy" 
                               pattern="\d{2}-\d{2}-\d{4}" 
                               maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="text" name="date_to" id="date_to" class="form-control" 
                               value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                               placeholder="dd-mm-yyyy" 
                               pattern="\d{2}-\d{2}-\d{4}" 
                               maxlength="10">
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
                            <a href="{{ contextRoute('appointments.index') }}" class="btn btn-outline-secondary">
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
        <div class="doctor-card-header d-flex justify-content-between align-items-center">
            <h5 class="doctor-card-title mb-0">Appointments List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="exportAppointments()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="doctor-card-body p-0">
            @if($appointments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Appointment #</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Clinic</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input appointment-checkbox" 
                                           value="{{ $appointment->id }}">
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $appointment->appointment_number }}</span>
                                </td>
                                <td>
                                    @if($appointment->patient)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; font-size: 12px;">
                                                {{ strtoupper(substr($appointment->patient->full_name ?? $appointment->patient->first_name ?? 'N', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $appointment->patient->full_name ?? $appointment->patient->first_name . ' ' . $appointment->patient->last_name }}</div>
                                                <small class="text-muted">{{ $appointment->patient->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder bg-danger text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px; font-size: 12px;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-danger">Patient Deleted</div>
                                                <small class="text-muted">Patient ID: {{ $appointment->patient_id ?? 'Unknown' }}</small>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->doctor)
                                        <div class="fw-bold">{{ $appointment->doctor->full_name ?? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name }}</div>
                                        <small class="text-muted">{{ $appointment->doctor->specialization ?? '' }}</small>
                                    @else
                                        <div class="text-danger fw-bold">Doctor Deleted</div>
                                        <small class="text-muted">ID: {{ $appointment->doctor_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($appointment->doctor && $appointment->doctor->department)
                                        <span class="badge bg-info">{{ $appointment->doctor->department->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
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
                                            'completed' => 'info',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$appointment->status] ?? 'secondary' }}">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ contextRoute('appointments.show', $appointment->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ contextRoute('appointments.edit', $appointment->id) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($appointment->status === 'pending')
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="confirmAppointment({{ $appointment->id }})" 
                                                    title="Confirm">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if(in_array($appointment->status, ['pending', 'confirmed']))
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelAppointment({{ $appointment->id }})" 
                                                    title="Cancel">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if($appointment->status !== 'completed')
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteAppointment({{ $appointment->id }})" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                        @if($appointment->status === 'cancelled')
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="confirmAppointment({{ $appointment->id }})" 
                                                    title="Reconfirm">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="doctor-card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $appointments->firstItem() }} to {{ $appointments->lastItem() }} 
                        of {{ $appointments->total() }} appointments
                    </div>
                    {{ $appointments->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-alt text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-muted">No appointments found</h5>
                    <p class="text-muted mb-4">No appointments match your current filters.</p>
                    <a href="{{ contextRoute('appointments.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Create First Appointment
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="mt-3" id="bulkActions" style="display: none;">
        <div class="doctor-card">
            <div class="doctor-card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">
                        <span id="selectedCount">0</span> appointment(s) selected
                    </span>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-success" onclick="bulkConfirm()">
                            <i class="fas fa-check me-1"></i>Confirm Selected
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="bulkCancel()">
                            <i class="fas fa-times me-1"></i>Cancel Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Footer -->
@if(shouldShowPoweredBy())
<div class="text-center mt-5 py-4" style="border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <i class="fas fa-calendar-check" style="color: #e94560;"></i>
        <span>Appointments Management - <strong>{{ getAppName() }} v{{ getAppVersion() }}</strong></span>
    </div>
    <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
        {{ getCopyrightText() }}
    </div>
</div>
@endif
@endsection

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

    // Date input mask for dd-mm-yyyy format
    $('input[pattern="\\d{2}-\\d{2}-\\d{4}"]').on('input', function() {
        let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
        if (value.length >= 2) {
            value = value.substring(0, 2) + '-' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 9);
        }
        $(this).val(value);
    });

    // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission
    $('#filterForm').on('submit', function() {
        $(this).find('input[pattern="\\d{2}-\\d{2}-\\d{4}"]').each(function() {
            const dateStr = $(this).val();
            if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                const parts = dateStr.split('-');
                const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
                $(this).val(yyyyMmDd);
            }
        });
    });
});

    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.appointment-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    document.querySelectorAll('.appointment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.appointment-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (selectedCheckboxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = selectedCheckboxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Quick actions
    function confirmAppointment(appointmentId) {
        // Show a confirmation dialog
        const confirmChange = confirm('Are you sure you want to confirm this appointment?\n\nThis will change the status from "Pending" to "Confirmed".');
        
        // Handle both sync and async confirm dialogs
        function handleConfirmation(confirmResult) {
            if (confirmResult === true) {
                // Add a small delay to ensure the dialog is properly closed
                setTimeout(() => {
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
                            alert('Error confirming appointment: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error confirming appointment:', error);
                        alert('An error occurred while confirming the appointment. Please try again.');
                    });
                }, 100);
            }
        }
        
        // Handle both Promise and boolean returns
        if (confirmChange && typeof confirmChange.then === 'function') {
            // If it's a Promise, wait for it to resolve
            confirmChange.then(handleConfirmation).catch(() => handleConfirmation(false));
        } else {
            // If it's a boolean, handle it directly
            handleConfirmation(confirmChange);
        }
    }

    function cancelAppointment(appointmentId) {
        Swal.fire({
            title: 'Cancel Appointment',
            text: 'Are you sure you want to cancel this appointment?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show input dialog for cancellation reason
                Swal.fire({
                    title: 'Cancellation Reason',
                    text: 'Please enter the reason for cancellation (optional):',
                    input: 'textarea',
                    inputPlaceholder: 'Enter cancellation reason...',
                    showCancelButton: true,
                    confirmButtonText: 'Cancel Appointment',
                    cancelButtonText: 'Back',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d'
                }).then((reasonResult) => {
                    if (reasonResult.isConfirmed) {
                        const reason = reasonResult.value || '';
                        
                        // Show loading
                        Swal.fire({
                            title: 'Cancelling...',
                            text: 'Please wait while we cancel the appointment.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Send cancellation request
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
                                Swal.fire({
                                    title: 'Cancelled!',
                                    text: 'The appointment has been successfully cancelled.',
                                    icon: 'success',
                                    confirmButtonColor: '#28a745'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error cancelling appointment: ' + (data.message || 'Unknown error'),
                                    icon: 'error',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error cancelling appointment:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while cancelling the appointment. Please try again.',
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        });
                    }
                });
            }
        });
    }

    function deleteAppointment(appointmentId) {
        Swal.fire({
            title: 'Delete Appointment',
            text: 'Are you sure you want to permanently delete this appointment? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the appointment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send delete request
                fetch(`/admin/appointments/${appointmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        // Handle non-200 responses
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Response was not JSON:', text);
                                throw new Error('Server returned an error: ' + response.status);
                            }
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message || 'The appointment has been permanently deleted.',
                            icon: 'success',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Error deleting appointment.',
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error deleting appointment:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while deleting the appointment. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
        });
    }

    function refreshTable() {
        location.reload();
    }

    function exportAppointments() {
        // Add export functionality here
        alert('Export functionality will be implemented');
    }

    function bulkConfirm() {
        const selected = Array.from(document.querySelectorAll('.appointment-checkbox:checked')).map(cb => cb.value);
        if (confirm(`Confirm ${selected.length} appointment(s)?`)) {
            // Implement bulk confirm
            alert('Bulk confirm functionality will be implemented');
        }
    }

    function bulkCancel() {
        const selected = Array.from(document.querySelectorAll('.appointment-checkbox:checked')).map(cb => cb.value);
        if (confirm(`Cancel ${selected.length} appointment(s)?`)) {
            // Implement bulk cancel
            alert('Bulk cancel functionality will be implemented');
        }
    }
</script>
@endpush
