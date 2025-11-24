@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Patients')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-900">Patients</h1>
                    <p class="text-muted mb-0">
                        @if(auth()->user()->role === 'doctor')
                            Manage patient records and medical history
                        @elseif(auth()->user()->role === 'nurse')
                            View and update patient information
                        @else
                            Manage patient registration and records
                        @endif
                    </p>
                </div>
                
                <div class="btn-group">
                    <a href="{{ route('staff.patients.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>New Patient
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="doctor-stat-card primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(13, 110, 253, 0.1); color: var(--doctor-primary);">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-primary);">{{ $patients->total() }}</div>
                        <div class="doctor-stat-label">Total Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="doctor-stat-card success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(25, 135, 84, 0.1); color: var(--doctor-success);">
                            <i class="fas fa-male"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-success);">
                            {{ $patients->filter(function($patient) { return $patient->gender === 'male'; })->count() }}
                        </div>
                        <div class="doctor-stat-label">Male Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="doctor-stat-card danger h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(220, 53, 69, 0.1); color: var(--doctor-danger);">
                            <i class="fas fa-female"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-danger);">
                            {{ $patients->filter(function($patient) { return $patient->gender === 'female'; })->count() }}
                        </div>
                        <div class="doctor-stat-label">Female Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="doctor-stat-card info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="doctor-stat-icon" style="background: rgba(13, 202, 240, 0.1); color: var(--doctor-info);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="doctor-stat-number" style="color: var(--doctor-info);">
                            {{ $patients->filter(function($patient) { return $patient->created_at->isCurrentMonth(); })->count() }}
                        </div>
                        <div class="doctor-stat-label">New This Month</div>
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
                               placeholder="Search by name, ID, phone, or email..." 
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
                <div>
                    @if(request()->hasAny(['search', 'first_name', 'last_name', 'gender', 'age_min', 'age_max', 'status', 'has_alert', 'department_id', 'assigned_doctor_id']))
                        <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary">
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

    <!-- Filter Sidebar (Collapsible) -->
    <div class="doctor-card mb-4" id="filterPanel" style="display: {{ request()->hasAny(['first_name', 'last_name', 'gender', 'age_min', 'age_max', 'status', 'has_alert', 'department_id', 'assigned_doctor_id']) ? 'block' : 'none' }};">
        <div class="doctor-card-header">
            <h6 class="doctor-card-title mb-0">
                <i class="fas fa-filter me-2"></i>Advanced Filters
            </h6>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.patients.index') }}" id="filterForm">
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
                        <select name="gender" class="form-control">
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
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                        <select name="patient_type" class="form-control">
                            <option value="">All</option>
                            <option value="insurance" {{ request('patient_type') == 'insurance' ? 'selected' : '' }}>Insurance</option>
                            <option value="private" {{ request('patient_type') == 'private' ? 'selected' : '' }}>Private</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assigned Doctor</label>
                        <select name="assigned_doctor_id" class="form-control">
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
                        <select name="department_id" class="form-control">
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
                        <select name="has_alert" class="form-control">
                            <option value="">All</option>
                            <option value="true" {{ request('has_alert') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_alert') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Alert Severity</label>
                        <select name="alert_severity" class="form-control">
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
                        <select name="alert_type" class="form-control">
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
                        <select name="appointment_type" class="form-control">
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
                        <select name="has_id_document" class="form-control">
                            <option value="">All</option>
                            <option value="true" {{ request('has_id_document') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_id_document') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Consent</label>
                        <select name="has_consent" class="form-control">
                            <option value="">All</option>
                            <option value="true" {{ request('has_consent') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('has_consent') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has GP Details</label>
                        <select name="has_gp_details" class="form-control">
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
                        <select name="email_verified" class="form-control">
                            <option value="">All</option>
                            <option value="true" {{ request('email_verified') === 'true' ? 'selected' : '' }}>Yes</option>
                            <option value="false" {{ request('email_verified') === 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Form Actions -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h6 class="doctor-card-title mb-0">
                <i class="fas fa-list me-2"></i>Patients
                <small class="text-muted">({{ $patients->total() }} total)</small>
            </h6>
        </div>
        <div class="doctor-card-body">
            @if($patients->count() > 0)
                <!-- Desktop Table View -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover" id="patientsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Patient ID</th>
                                <th>Patient Details</th>
                                <th>Contact Info</th>
                                <th>Demographics</th>
                                <th>Assigned Clinic(s)</th>
                                <th>Medical Summary</th>
                                <th>Alerts</th>
                                <th>Registration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">#{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <div class="rounded-circle bg-{{ $patient->gender === 'male' ? 'primary' : ($patient->gender === 'female' ? 'danger' : 'secondary') }} text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                                            @if($patient->date_of_birth)
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} years old</small>
                                            @else
                                                <small class="text-muted">Age not specified</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $patient->email ?? 'No email' }}</div>
                                    <small class="text-muted">{{ $patient->phone ?? 'No phone' }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">
                                        @php
                                            $genderColors = [
                                                'male' => 'primary',
                                                'female' => 'danger', 
                                                'other' => 'secondary'
                                            ];
                                            $color = $genderColors[$patient->gender] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst($patient->gender) }}</span>
                                    </div>
                                    @if($patient->blood_group)
                                        <small class="text-muted">Blood: {{ $patient->blood_group }}</small>
                                    @endif
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
                                                    <i class="fas fa-building me-1 text-primary" style="font-size: 0.875rem;"></i>
                                                    <strong style="font-size: 0.875rem;">{{ $dept['name'] }}</strong>
                                                    @if($dept['is_primary'] && count($patientDepartments) > 1)
                                                        <span class="badge bg-primary ms-1" style="font-size: 0.6rem;">Primary</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 0.875rem;">
                                            <i class="fas fa-minus-circle me-1"></i>Not Assigned
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-info">{{ $patient->appointments->count() }} appointments</div>
                                    @if($patient->medical_records_count ?? 0 > 0)
                                        <small class="text-muted">{{ $patient->medical_records_count }} records</small>
                                    @else
                                        <small class="text-muted">No medical records</small>
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
                                    <div class="fw-bold">{{ $patient->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $patient->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('staff.patients.show', $patient->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.patients.edit', $patient->id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit Patient">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(auth()->user()->role === 'doctor')
                                            <a href="{{ route('staff.medical-records.create', ['patient_id' => $patient->id]) }}" 
                                               class="btn btn-sm btn-outline-success" title="Add Medical Record">
                                                <i class="fas fa-notes-medical"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="d-md-none">
                    @foreach($patients as $patient)
                        @php
                            // Get patient departments
                            $patientDepartments = [];
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
                            
                            // Get active alerts
                            try {
                                if ($patient->relationLoaded('activeAlerts')) {
                                    $activeAlertsCollection = collect($patient->activeAlerts ?? []);
                                } else {
                                    $activeAlertsCollection = collect($patient->activeAlerts()->get() ?? []);
                                }
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
                            
                            $genderColors = [
                                'male' => 'primary',
                                'female' => 'danger', 
                                'other' => 'secondary'
                            ];
                            $color = $genderColors[$patient->gender] ?? 'secondary';
                        @endphp
                        <div class="card mb-3 border shadow-sm">
                            <div class="card-body">
                                <!-- Header with Avatar and Name -->
                                <div class="d-flex align-items-start mb-3">
                                    <div class="rounded-circle bg-{{ $color }} text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.25rem; font-weight: bold;">
                                        {{ strtoupper(substr($patient->first_name, 0, 1)) }}
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1 fw-bold">{{ $patient->first_name }} {{ $patient->last_name }}</h6>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="badge bg-primary">#{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</span>
                                                    <span class="badge bg-{{ $color }}">{{ ucfirst($patient->gender) }}</span>
                                                    @if($patient->date_of_birth)
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} years</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Info -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1"><i class="fas fa-envelope me-1"></i>Email</small>
                                            <div class="fw-semibold">{{ $patient->email ?? 'No email' }}</div>
                                        </div>
                                        <div class="col-12">
                                            <small class="text-muted d-block mb-1"><i class="fas fa-phone me-1"></i>Phone</small>
                                            <div class="fw-semibold">{{ $patient->phone ?? 'No phone' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Demographics -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-2"><i class="fas fa-info-circle me-1"></i>Demographics</small>
                                    <div class="d-flex flex-wrap gap-2">
                                        @if($patient->blood_group)
                                            <span class="badge bg-info">Blood: {{ $patient->blood_group }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Assigned Clinic(s) -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-2"><i class="fas fa-building me-1"></i>Assigned Clinic(s)</small>
                                    @if(!empty($patientDepartments))
                                        <div>
                                            @foreach($patientDepartments as $index => $dept)
                                                <div class="mb-1">
                                                    <i class="fas fa-building me-1 text-primary"></i>
                                                    <strong>{{ $dept['name'] }}</strong>
                                                    @if($dept['is_primary'] && count($patientDepartments) > 1)
                                                        <span class="badge bg-primary ms-1">Primary</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-minus-circle me-1"></i>Not Assigned
                                        </span>
                                    @endif
                                </div>

                                <!-- Medical Summary -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-2"><i class="fas fa-notes-medical me-1"></i>Medical Summary</small>
                                    <div class="d-flex gap-3">
                                        <div>
                                            <div class="fw-bold text-info">{{ $patient->appointments->count() }}</div>
                                            <small class="text-muted">Appointments</small>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-success">{{ $patient->medical_records_count ?? 0 }}</div>
                                            <small class="text-muted">Records</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Alerts -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Alerts</small>
                                    @if($alertCount > 0)
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            @foreach($activeAlerts->take(5) as $alert)
                                                <span class="badge bg-{{ $alert->severity_color }}" title="{{ $alert->title }}">
                                                    <i class="fas fa-{{ $alert->type_icon }} me-1"></i>
                                                    @if($alert->severity === 'critical' || $alert->severity === 'high')
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                    @endif
                                                    {{ $alert->severity === 'critical' ? 'CRIT' : strtoupper(substr($alert->severity, 0, 1)) }}
                                                    @if($alert->restricted)
                                                        <i class="fas fa-lock ms-1"></i>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if($alertCount > 5)
                                                <span class="badge bg-secondary">+{{ $alertCount - 5 }}</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $alertCount }} active {{ Str::plural('alert', $alertCount) }}</small>
                                    @else
                                        <span class="text-muted">
                                            <i class="fas fa-check-circle text-success me-1"></i>No alerts
                                        </span>
                                    @endif
                                </div>

                                <!-- Registration -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted d-block mb-1"><i class="fas fa-calendar me-1"></i>Registration</small>
                                    <div class="fw-semibold">{{ $patient->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $patient->created_at->format('h:i A') }}</small>
                                </div>

                                <!-- Actions -->
                                <div>
                                    <small class="text-muted d-block mb-2"><i class="fas fa-cog me-1"></i>Actions</small>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('staff.patients.show', $patient->id) }}" 
                                           class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="{{ route('staff.patients.edit', $patient->id) }}" 
                                           class="btn btn-sm btn-outline-warning flex-fill">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        @if(auth()->user()->role === 'doctor')
                                            <a href="{{ route('staff.medical-records.create', ['patient_id' => $patient->id]) }}" 
                                               class="btn btn-sm btn-outline-success flex-fill">
                                                <i class="fas fa-notes-medical me-1"></i>Record
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $patients->firstItem() }} to {{ $patients->lastItem() }} 
                        of {{ $patients->total() }} results
                    </div>
                    <div>
                        {{ $patients->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Patients Found</h5>
                    <p class="text-muted mb-4">
                        Start by registering your first patient.
                    </p>
                    
                    <a href="{{ route('staff.patients.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Add First Patient
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Mobile Card Styles */
    @media (max-width: 767.98px) {
        .card.mb-3 {
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card.mb-3:active {
            transform: scale(0.98);
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .card-body .btn {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        
        .card-body .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
        }
        
        .card-body small {
            font-size: 0.75rem;
        }
        
        .card-body h6 {
            font-size: 1rem;
        }
    }
    
    /* Ensure table is scrollable on tablets */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .table {
            min-width: 1200px;
        }
    }
</style>
@endpush

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

    // Initialize DataTable (if table exists) - only on desktop
    if ($('#patientsTable').length && window.innerWidth >= 768) {
        $('#patientsTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "ordering": true,
            "order": [[ 7, "desc" ]],
            "columnDefs": [
                { "orderable": false, "targets": [8] }
            ],
            "responsive": false
        });
    }
});
</script>
@endpush
