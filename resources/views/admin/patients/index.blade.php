@extends('admin.layouts.app')

@section('title', 'Patients Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Patients</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">Patients Management</h1>
                    <p class="modern-page-subtitle">Manage and track all patient records</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.patients.export.csv', request()->all()) }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-file-export me-2"></i>Export CSV
                    </a>
                    <a href="{{ route('admin.patients.import') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-file-import me-2"></i>Import CSV
                    </a>
                    <a href="{{ contextRoute('patients.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-user-plus me-2"></i>New Patient
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced fade-in-up stagger-1">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($patients->total() ?? 0) }}</div>
                        <div class="stat-label">Total Patients</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced fade-in-up stagger-2">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($patients->where('created_at', '>=', today())->count() ?? 0) }}</div>
                        <div class="stat-label">New Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced fade-in-up stagger-3">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($patients->where('created_at', '>=', now()->startOfWeek())->count() ?? 0) }}</div>
                        <div class="stat-label">This Week</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced fade-in-up stagger-4">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($patients->where('created_at', '>=', now()->startOfMonth())->count() ?? 0) }}</div>
                        <div class="stat-label">This Month</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Search Bar -->
    <div class="modern-card mb-3">
        <div class="card-body">
            <div class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label class="form-label fw-semibold">Quick Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               id="quickSearch" 
                               name="search" 
                               class="form-control form-control-lg" 
                               placeholder="Search by name, ID, phone, or email..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="toggleFilters()">
                        <i class="fas fa-filter me-1"></i>Filters
                        @php
                            $activeFiltersCount = count(array_filter(request()->except(['page', 'search'])));
                        @endphp
                        @if($activeFiltersCount > 0)
                            <span class="badge bg-primary ms-1">{{ $activeFiltersCount }}</span>
                        @endif
                    </button>
                </div>
                <div>
                    @if(request()->hasAny(['search', 'first_name', 'last_name', 'gender', 'age_min', 'age_max', 'status', 'has_alert', 'department_id', 'assigned_doctor_id']))
                        <a href="{{ contextRoute('patients.index') }}" class="btn btn-outline-secondary">
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
        if(request('first_name')) $activeFilters[] = ['key' => 'first_name', 'label' => 'First Name: ' . request('first_name')];
        if(request('last_name')) $activeFilters[] = ['key' => 'last_name', 'label' => 'Last Name: ' . request('last_name')];
        if(request('gender')) $activeFilters[] = ['key' => 'gender', 'label' => 'Gender: ' . ucfirst(request('gender'))];
        if(request('age_min')) $activeFilters[] = ['key' => 'age_min', 'label' => 'Min Age: ' . request('age_min')];
        if(request('age_max')) $activeFilters[] = ['key' => 'age_max', 'label' => 'Max Age: ' . request('age_max')];
        if(request('status')) $activeFilters[] = ['key' => 'status', 'label' => 'Status: ' . ucfirst(request('status'))];
        if(request('has_alert')) $activeFilters[] = ['key' => 'has_alert', 'label' => 'Has Alert: ' . (request('has_alert') === 'true' ? 'Yes' : 'No')];
        if(request('alert_severity')) $activeFilters[] = ['key' => 'alert_severity', 'label' => 'Alert Severity: ' . ucfirst(request('alert_severity'))];
        if(request('department_id')) {
            $dept = $departments->firstWhere('id', request('department_id'));
            if($dept) $activeFilters[] = ['key' => 'department_id', 'label' => 'Department: ' . $dept->name];
        }
        if(request('assigned_doctor_id')) {
            $doc = collect($doctors)->firstWhere('id', request('assigned_doctor_id'));
            if($doc) $activeFilters[] = ['key' => 'assigned_doctor_id', 'label' => 'Doctor: ' . $doc['name']];
        }
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

    <!-- Modern Filter Panel -->
    <div class="modern-card mb-4" id="filterPanel" style="display: {{ request()->hasAny(['first_name', 'last_name', 'gender', 'age_min', 'age_max', 'status', 'has_alert', 'department_id', 'assigned_doctor_id']) ? 'block' : 'none' }};">
        <div class="modern-card-header">
            <h5 class="modern-card-title">
                <i class="fas fa-filter"></i>Advanced Filters
            </h5>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ contextRoute('patients.index') }}" id="filterForm">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                <div class="row g-3">
                    <!-- Demographics Section -->
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-user me-2"></i>Demographics
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ request('first_name') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ request('last_name') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">All</option>
                            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Age Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="age_min" class="form-control" placeholder="Min" value="{{ request('age_min') }}" min="0" max="120">
                            </div>
                            <div class="col-6">
                                <input type="number" name="age_max" class="form-control" placeholder="Max" value="{{ request('age_max') }}" min="0" max="120">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DOB From</label>
                        <input type="date" name="dob_from" class="form-control" value="{{ request('dob_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DOB To</label>
                        <input type="date" name="dob_to" class="form-control" value="{{ request('dob_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ request('city') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Postal Code</label>
                        <input type="text" name="postal_code" class="form-control" value="{{ request('postal_code') }}">
                    </div>

                    <!-- Registration & Status Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-check me-2"></i>Registration & Status
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Registration From</label>
                        <input type="date" name="reg_from" class="form-control" value="{{ request('reg_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Registration To</label>
                        <input type="date" name="reg_to" class="form-control" value="{{ request('reg_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Patient Type</label>
                        <select name="patient_type" class="form-select">
                            <option value="">All</option>
                            <option value="insurance" {{ request('patient_type') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                            <option value="private" {{ request('patient_type') == 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Guest Status</label>
                        <select name="is_guest" class="form-select">
                            <option value="">All Patients</option>
                            <option value="1" {{ request('is_guest') === '1' ? 'selected' : '' }}>Guests Only</option>
                            <option value="0" {{ request('is_guest') === '0' ? 'selected' : '' }}>Full Patients Only</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assigned Doctor</label>
                        <select name="assigned_doctor_id" class="form-select">
                            <option value="">All Doctors</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor['id'] }}" {{ request('assigned_doctor_id') == $doctor['id'] ? 'selected' : '' }}>
                                    {{ $doctor['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Alerts Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-danger border-bottom pb-2 mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>Alerts & Flags
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Alerts</label>
                        <select name="has_alert" class="form-select">
                            <option value="">All</option>
                            <option value="true" {{ request('has_alert') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_alert') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alert Severity</label>
                        <select name="alert_severity" class="form-select">
                            <option value="">All</option>
                            @foreach(config('alerts.severities', ['critical', 'high', 'medium', 'low', 'info']) as $severity)
                                <option value="{{ $severity }}" {{ request('alert_severity') == $severity ? 'selected' : '' }}>
                                    {{ ucfirst($severity) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alert Type</label>
                        <select name="alert_type" class="form-select">
                            <option value="">All</option>
                            @foreach(config('alerts.types', ['clinical', 'safeguarding', 'behaviour', 'communication', 'admin', 'medication']) as $type)
                                <option value="{{ $type }}" {{ request('alert_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Appointments Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-info border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>Appointments & Encounters
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Appointment From</label>
                        <input type="date" name="last_appt_from" class="form-control" value="{{ request('last_appt_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Appointment To</label>
                        <input type="date" name="last_appt_to" class="form-control" value="{{ request('last_appt_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Next Appointment From</label>
                        <input type="date" name="next_appt_from" class="form-control" value="{{ request('next_appt_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Next Appointment To</label>
                        <input type="date" name="next_appt_to" class="form-control" value="{{ request('next_appt_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Appointment Type</label>
                        <select name="appointment_type" class="form-select">
                            <option value="">All</option>
                            <option value="online" {{ request('appointment_type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="in_person" {{ request('appointment_type') == 'in_person' ? 'selected' : '' }}>In-Person</option>
                            <option value="phone" {{ request('appointment_type') == 'phone' ? 'selected' : '' }}>Phone</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Visits in Last (Months)</label>
                        <input type="number" name="visits_in_last" class="form-control" value="{{ request('visits_in_last') }}" min="1" max="24">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Visit Count Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="visit_count_min" class="form-control" placeholder="Min" value="{{ request('visit_count_min') }}" min="0">
                            </div>
                            <div class="col-6">
                                <input type="number" name="visit_count_max" class="form-control" placeholder="Max" value="{{ request('visit_count_max') }}" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Admin/Documentation Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-warning border-bottom pb-2 mb-3">
                            <i class="fas fa-file-alt me-2"></i>Admin & Documentation
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has ID Document</label>
                        <select name="has_id_document" class="form-select">
                            <option value="">All</option>
                            <option value="true" {{ request('has_id_document') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_id_document') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Consent</label>
                        <select name="has_consent" class="form-select">
                            <option value="">All</option>
                            <option value="true" {{ request('has_consent') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_consent') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has GP Details</label>
                        <select name="has_gp_details" class="form-select">
                            <option value="">All</option>
                            <option value="true" {{ request('has_gp_details') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_gp_details') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Missing Data</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="missing_phone" value="1" id="missing_phone" {{ request('missing_phone') ? 'checked' : '' }}>
                            <label class="form-check-label" for="missing_phone">Missing Phone</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="missing_email" value="1" id="missing_email" {{ request('missing_email') ? 'checked' : '' }}>
                            <label class="form-check-label" for="missing_email">Missing Email</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="missing_address" value="1" id="missing_address" {{ request('missing_address') ? 'checked' : '' }}>
                            <label class="form-check-label" for="missing_address">Missing Address</label>
                        </div>
                    </div>

                    <!-- Communication & Portal Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-secondary border-bottom pb-2 mb-3">
                            <i class="fas fa-envelope me-2"></i>Communication & Portal
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Email Verified</label>
                        <select name="email_verified" class="form-select">
                            <option value="">All</option>
                            <option value="true" {{ request('email_verified') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('email_verified') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Form Actions -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ contextRoute('patients.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modern Patients Table -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-list"></i>Patients List
            </h5>
            <div class="d-flex gap-2">
                <button class="btn-modern btn-modern-outline btn-modern-sm" onclick="exportPatients()">
                    <i class="fas fa-download"></i>Export
                </button>
                <button class="btn-modern btn-modern-outline btn-modern-sm" onclick="refreshTable()">
                    <i class="fas fa-sync"></i>Refresh
                </button>
            </div>
        </div>
        <div class="modern-card-body">
            @if($patients->count() > 0)
                <div class="modern-table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Patient</th>
                                <th>Contact Info</th>
                                <th>Age/Gender</th>
                                <th>Registration Date</th>
                                <th>Assigned Clinic</th>
                                <th>Alerts</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input patient-checkbox" 
                                           value="{{ $patient->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="patient-avatar-modern" style="background: var(--gradient-primary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; color: white; font-size: 1.1rem;">
                                            {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold d-flex align-items-center gap-2">
                                                {{ $patient->full_name }}
                                                @if($patient->is_guest)
                                                <span class="badge bg-secondary" style="font-size: 0.7rem;">Guest</span>
                                                @endif
                                            </div>
                                            <small class="text-muted">ID: {{ $patient->patient_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold">{{ $patient->email }}</div>
                                        <small class="text-muted">{{ $patient->phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @if($patient->date_of_birth)
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} years</div>
                                        @else
                                            <div class="text-muted">Age not set</div>
                                        @endif
                                        <small class="text-muted">{{ $patient->gender ? ucfirst($patient->gender) : 'Not specified' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ formatDate($patient->created_at) }}</div>
                                    <small class="text-muted">{{ $patient->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @php
                                        $patientDepartments = [];
                                        
                                        // Get departments from many-to-many relationship (primary method)
                                        if ($patient->relationLoaded('departments') || $patient->departments()->exists()) {
                                            if (!$patient->relationLoaded('departments')) {
                                                $patient->load('departments');
                                            }
                                            foreach ($patient->departments as $dept) {
                                                $isPrimary = $dept->pivot->is_primary ?? false;
                                                $patientDepartments[] = [
                                                    'name' => $dept->name,
                                                    'is_primary' => $isPrimary
                                                ];
                                            }
                                        }
                                        
                                        // Fallback to legacy department_id if no pivot records exist
                                        if (empty($patientDepartments) && $patient->department_id && $patient->department) {
                                            if (!$patient->relationLoaded('department')) {
                                                $patient->load('department');
                                            }
                                            if ($patient->department) {
                                                $patientDepartments[] = [
                                                    'name' => $patient->department->name,
                                                    'is_primary' => true
                                                ];
                                            }
                                        }
                                    @endphp
                                    
                                    @if(!empty($patientDepartments))
                                        <div>
                                            @foreach($patientDepartments as $index => $dept)
                                                <div class="{{ $index > 0 ? 'mt-1' : '' }}">
                                                    <i class="fas fa-building me-1 text-primary"></i>
                                                    <strong>{{ $dept['name'] }}</strong>
                                                    @if($dept['is_primary'] && count($patientDepartments) > 1)
                                                        <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Primary</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-minus-circle me-1"></i>Not Assigned
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        // Get active alerts as a collection
                                        try {
                                            if ($patient->relationLoaded('activeAlerts')) {
                                                $activeAlertsCollection = collect($patient->activeAlerts ?? []);
                                            } else {
                                                $activeAlertsCollection = collect($patient->activeAlerts()->get() ?? []);
                                            }
                                            
                                            // Filter by permissions
                                            $activeAlerts = $activeAlertsCollection->filter(function($alert) {
                                                try {
                                                    return auth()->check() && auth()->user()->can('view', $alert);
                                                } catch (\Exception $e) {
                                                    return false;
                                                }
                                            });
                                            
                                            $alertCount = count($activeAlerts);
                                        } catch (\Exception $e) {
                                            $activeAlerts = collect([]);
                                            $alertCount = 0;
                                        }
                                    @endphp
                                    @if($alertCount > 0)
                                        <div class="d-flex flex-wrap gap-1" style="max-width: 200px;">
                                            @foreach($activeAlerts->take(3) as $alert)
                                                <span class="badge bg-{{ $alert->severity_color }} badge-sm" 
                                                      title="{{ $alert->title }}"
                                                      style="font-size: 0.75rem; cursor: help;">
                                                    <i class="fas fa-{{ $alert->type_icon }} me-1"></i>
                                                    @if($alert->severity === 'critical' || $alert->severity === 'high')
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                    @endif
                                                    {{ $alert->severity === 'critical' ? 'CRIT' : strtoupper(substr($alert->severity, 0, 1)) }}
                                                    @if($alert->restricted)
                                                        <i class="fas fa-lock ms-1" style="font-size: 0.65rem;"></i>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if($alertCount > 3)
                                                <span class="badge bg-secondary badge-sm" style="font-size: 0.75rem;">
                                                    +{{ $alertCount - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                                            {{ $alertCount }} active {{ Str::plural('alert', $alertCount) }}
                                        </small>
                                    @else
                                        <span class="text-muted" style="font-size: 0.875rem;">
                                            <i class="fas fa-check-circle text-success me-1"></i>No alerts
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.patients.show', $patient->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.patients.edit', $patient->id) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($patient->is_guest)
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="convertGuest({{ $patient->id }})" 
                                                title="Convert to Full Patient">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="viewAppointments({{ $patient->id }})" 
                                                title="Appointments">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deletePatient({{ $patient->id }}); return false;" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $patients->firstItem() }} to {{ $patients->lastItem() }} 
                        of {{ $patients->total() }} patients
                    </div>
                    {{ $patients->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-muted">No patients found</h5>
                    <p class="text-muted mb-4">No patients match your current filters.</p>
                    <a href="{{ contextRoute('patients.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Register First Patient
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="mt-3" id="bulkActions" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted">
                        <span id="selectedCount">0</span> patient(s) selected
                    </span>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info" onclick="bulkExport()">
                            <i class="fas fa-download me-1"></i>Export Selected
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="bulkDelete()">
                            <i class="fas fa-trash me-1"></i>Delete Selected
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
        <i class="fas fa-users" style="color: #e94560;"></i>
        <span>Patients Management - <strong>{{ getAppName() }} v{{ getAppVersion() }}</strong></span>
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

    // Auto-submit filter form on change (optional - for live filtering)
    // Uncomment if you want filters to auto-apply without clicking "Apply Filters"
    /*
    $('#filterForm select, #filterForm input[type="date"], #filterForm input[type="number"]').on('change', function() {
        $('#filterForm').submit();
    });
    */
});
    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.patient-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    document.querySelectorAll('.patient-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.patient-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (selectedCheckboxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = selectedCheckboxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Patient actions
    function viewAppointments(patientId) {
        // Navigate to appointments index with patient filter in same tab
        window.location.href = `/admin/appointments?patient_id=${patientId}`;
    }

    function deletePatient(patientId) {
        // Prevent any default behavior if event exists
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Use the modern modal confirmation system
        ModalSystem.confirm({
            title: 'Delete Patient',
            message: 'Are you sure you want to permanently delete this patient? This action will remove all patient data including personal information, medical records, and appointment history. This cannot be undone.',
            confirmText: 'Yes, Delete Patient',
            cancelText: 'Cancel',
            icon: 'fas fa-exclamation-triangle',
            confirmClass: 'btn-danger',
            onConfirm: function() {
                // Show loading modal
                const loader = ModalSystem.loading('Deleting patient...');
                
                // Use AJAX for better error handling
                $.ajax({
                    url: `/admin/patients/${patientId}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        loader.hide();
                        
                        if (response.success) {
                            ModalSystem.notify({
                                type: 'success',
                                title: 'Patient Deleted',
                                message: response.message || 'Patient has been successfully deleted.',
                                duration: 3000
                            });
                            
                            // Reload page after short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            ModalSystem.notify({
                                type: 'error',
                                title: 'Deletion Failed',
                                message: response.message || 'Failed to delete patient.',
                                duration: 5000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        loader.hide();
                        
                        let errorMessage = 'An error occurred while deleting the patient.';
                        
                        // Try to extract error message from various response formats
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            } else if (xhr.responseJSON.errors) {
                                // Laravel validation errors
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join(', ');
                            }
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                } else if (response.error) {
                                    errorMessage = response.error;
                                }
                            } catch (e) {
                                // If response is HTML (like error page), extract text
                                if (xhr.responseText.includes('<title>')) {
                                    const match = xhr.responseText.match(/<title>(.*?)<\/title>/);
                                    if (match) {
                                        errorMessage = match[1];
                                    }
                                } else if (xhr.responseText.length < 500) {
                                    // If it's a short text response, use it
                                    errorMessage = xhr.responseText;
                                }
                            }
                        }
                        
                        // Log full error details for debugging
                        console.error('Delete patient error:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            response: xhr.responseJSON || xhr.responseText,
                            error: error
                        });
                        
                        ModalSystem.notify({
                            type: 'error',
                            title: 'Deletion Failed',
                            message: errorMessage,
                            duration: 0 // Don't auto-hide error messages
                        });
                    }
                });
            }
        });
        
        return false;
    }

    function refreshTable() {
        location.reload();
    }

    function convertGuest(patientId) {
        window.location.href = `{{ route('admin.patients.index') }}/${patientId}/convert-guest`;
    }

    function exportPatients() {
        // Add export functionality here
        alert('Export functionality will be implemented');
    }

    function bulkExport() {
        const selected = Array.from(document.querySelectorAll('.patient-checkbox:checked')).map(cb => cb.value);
        alert(`Export ${selected.length} patient(s) functionality will be implemented`);
    }

    function bulkDelete() {
        const selected = Array.from(document.querySelectorAll('.patient-checkbox:checked')).map(cb => cb.value);
        if (confirm(`Delete ${selected.length} patient(s)? This action cannot be undone.`)) {
            // Implement bulk delete
            alert('Bulk delete functionality will be implemented');
        }
    }
</script>
@endpush
