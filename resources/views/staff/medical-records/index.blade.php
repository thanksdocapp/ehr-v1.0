@extends(strtolower(auth()->user()->role ?? '') === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Medical Records')
@section('page-title', 'Medical Records')
@section('page-subtitle', strtolower(auth()->user()->role ?? '') === 'doctor' ? 'Manage all medical records - Full access' : (strtolower(auth()->user()->role ?? '') === 'nurse' ? 'Create and view medical records' : 'View medical records you\'re involved with'))

@section('content')
<div class="fade-in-up">
    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $medicalRecords->total() }}</div>
                        <div class="stat-label">Total Records</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $medicalRecords->filter(function($record) { return $record->created_at >= now()->startOfMonth(); })->count() }}
                        </div>
                        <div class="stat-label">This Month</div>
                    </div>
                </div>
            </div>
        </div>

        @if(strtolower(auth()->user()->role ?? '') === 'doctor')
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $medicalRecords->filter(function($record) { return $record->doctor_id == auth()->id(); })->count() }}
                        </div>
                        <div class="stat-label">My Records</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-prescription-bottle-alt"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">
                            {{ $medicalRecords->filter(function($record) { return $record->prescriptions && $record->prescriptions->count() > 0; })->count() }}
                        </div>
                        <div class="stat-label">With Prescriptions</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search Bar -->
    <div class="doctor-card mb-3">
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.medical-records.index') }}" id="quickSearchForm">
                @foreach(request()->except(['search', 'page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="d-flex gap-2 align-items-end flex-wrap">
                    <div class="flex-grow-1">
                        <label class="form-label fw-semibold">Quick Search</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text"
                                   id="quickSearch"
                                   name="search"
                                   class="form-control form-control-lg"
                                   placeholder="Search by patient name, diagnosis, symptoms, treatment, doctor..."
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-search"></i>
                            </button>
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
                    @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                    <div>
                        <a href="{{ route('staff.medical-records.create') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-1"></i>New Record
                        </a>
                    </div>
                    @endif
                    <div>
                        @if(request()->hasAny(['search', 'patient_name', 'doctor_id', 'department_id', 'record_type', 'date_from', 'date_to']))
                            <a href="{{ route('staff.medical-records.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Filters Chips -->
    @php
        $activeFilters = [];
        if(request('record_type')) $activeFilters[] = ['key' => 'record_type', 'label' => 'Type: ' . ucfirst(str_replace('_', ' ', request('record_type')))];
        if(request('patient_name')) $activeFilters[] = ['key' => 'patient_name', 'label' => 'Patient: ' . request('patient_name')];
        if(request('doctor_id')) {
            $doc = collect($doctors)->firstWhere('id', request('doctor_id'));
            if($doc) $activeFilters[] = ['key' => 'doctor_id', 'label' => 'Doctor: ' . $doc['name']];
        }
        if(request('department_id')) {
            $dept = $departments->firstWhere('id', request('department_id'));
            if($dept) $activeFilters[] = ['key' => 'department_id', 'label' => 'Department: ' . $dept->name];
        }
        if(request('date_from')) $activeFilters[] = ['key' => 'date_from', 'label' => 'Date From: ' . request('date_from')];
        if(request('date_to')) $activeFilters[] = ['key' => 'date_to', 'label' => 'Date To: ' . request('date_to')];
        if(request('date_range')) $activeFilters[] = ['key' => 'date_range', 'label' => 'Range: ' . ucfirst(str_replace('_', ' ', request('date_range')))];
        if(request('has_prescriptions') == 'yes') $activeFilters[] = ['key' => 'has_prescriptions', 'label' => 'Has Prescriptions: Yes'];
        if(request('has_lab_reports') == 'yes') $activeFilters[] = ['key' => 'has_lab_reports', 'label' => 'Has Lab Reports: Yes'];
        if(request('follow_up_overdue')) $activeFilters[] = ['key' => 'follow_up_overdue', 'label' => 'Follow-up Overdue: Yes'];
        if(request('my_records')) $activeFilters[] = ['key' => 'my_records', 'label' => 'My Records: Yes'];
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
    <div class="doctor-card mb-4" id="filterPanel" style="display: none;">
        <div class="doctor-card-header">
            <h6 class="doctor-card-title mb-0">
                <i class="fas fa-filter me-2"></i>Advanced Filters
            </h6>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.medical-records.index') }}" id="filterForm">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                <div class="row g-3">
                    <!-- Patient & Doctor Section -->
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
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
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Record Type</label>
                        <select name="record_type" class="form-control">
                            <option value="">All Types</option>
                            @foreach($recordTypes ?? [] as $type)
                                <option value="{{ $type }}" {{ request('record_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
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
                            <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Record Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Record Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Specific Record Date</label>
                        <input type="date" name="record_date" class="form-control" value="{{ request('record_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created From</label>
                        <input type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created To</label>
                        <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
                    </div>

                    <!-- Medical Information Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-stethoscope me-2"></i>Medical Information
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Diagnosis</label>
                        <input type="text" name="diagnosis" class="form-control" value="{{ request('diagnosis') }}" placeholder="Search diagnosis...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Symptoms</label>
                        <input type="text" name="symptoms" class="form-control" value="{{ request('symptoms') }}" placeholder="Search symptoms...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Treatment</label>
                        <input type="text" name="treatment" class="form-control" value="{{ request('treatment') }}" placeholder="Search treatment...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Presenting Complaint</label>
                        <input type="text" name="presenting_complaint" class="form-control" value="{{ request('presenting_complaint') }}" placeholder="Search presenting complaint...">
                    </div>

                    <!-- Relationship Filters Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-warning border-bottom pb-2 mb-3">
                            <i class="fas fa-link me-2"></i>Related Records
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Appointment</label>
                        <select name="has_appointment" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_appointment') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_appointment') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Prescriptions</label>
                        <select name="has_prescriptions" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_prescriptions') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_prescriptions') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Lab Reports</label>
                        <select name="has_lab_reports" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_lab_reports') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_lab_reports') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Attachments</label>
                        <select name="has_attachments" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_attachments') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_attachments') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Follow-up Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-danger border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-check me-2"></i>Follow-up
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Follow-up</label>
                        <select name="has_follow_up" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_follow_up') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_follow_up') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Follow-up Date From</label>
                        <input type="date" name="follow_up_from" class="form-control" value="{{ request('follow_up_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Follow-up Date To</label>
                        <input type="date" name="follow_up_to" class="form-control" value="{{ request('follow_up_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Follow-up Overdue</label>
                        <select name="follow_up_overdue" class="form-control">
                            <option value="">All</option>
                            <option value="1" {{ request('follow_up_overdue') ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>

                    <!-- Additional Filters Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-secondary border-bottom pb-2 mb-3">
                            <i class="fas fa-filter me-2"></i>Additional Filters
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Privacy</label>
                        <select name="is_private" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('is_private') == 'yes' ? 'selected' : '' }}>Private</option>
                            <option value="no" {{ request('is_private') == 'no' ? 'selected' : '' }}>Public</option>
                        </select>
                    </div>
                    @if(strtolower(auth()->user()->role ?? '') === 'doctor')
                    <div class="col-md-3">
                        <label class="form-label">My Records</label>
                        <select name="my_records" class="form-control">
                            <option value="">All Records</option>
                            <option value="1" {{ request('my_records') ? 'selected' : '' }}>Only My Records</option>
                        </select>
                    </div>
                    @endif

                    <!-- Form Actions -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('staff.medical-records.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Medical Records Table -->
    <div class="doctor-card">
        <div class="doctor-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-list me-2"></i>Medical Records
                <small class="text-muted">({{ $medicalRecords->total() }} total)</small>
            </h5>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small" id="selectedCount">0 selected</span>
                <button type="button" class="btn btn-sm btn-outline-info" id="printSelectedBtn" disabled onclick="printSelectedRecords()">
                    <i class="fas fa-print me-1"></i>Print selected
                </button>
            </div>
        </div>
        <div class="doctor-card-body">
            @if($medicalRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="medicalRecordsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" title="Select all on this page">
                                </th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Presenting Complaint</th>
                                <th>Date</th>
                                <th>Assessment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $currentDoctorForMrEditIndex = strtolower(auth()->user()->role ?? '') === 'doctor'
                                    ? \App\Models\Doctor::where('user_id', auth()->id())->first()
                                    : null;
                            @endphp
                            @foreach($medicalRecords as $record)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input record-checkbox" value="{{ $record->id }}" data-record-id="{{ $record->id }}">
                                </td>
                                <td>
                                    @if($record->patient)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($record->patient->first_name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $record->patient->first_name }} {{ $record->patient->last_name }}</div>
                                                <small class="text-muted">{{ $record->patient->phone ?? 'No phone' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="fas fa-user-slash me-1"></i>Patient record deleted
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($record->doctor)
                                        <div class="fw-bold">{{ $record->doctor->name }}</div>
                                        <small class="text-muted">{{ $record->doctor->specialization ?? 'General' }}</small>
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $record->presenting_complaint ?? $record->chief_complaint ?? 'N/A' }}">
                                        {{ $record->presenting_complaint ?? $record->chief_complaint ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ formatDateUk($record->created_at) }}</div>
                                    <small class="text-muted">{{ $record->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 150px;" title="{{ $record->assessment }}">
                                        {{ $record->assessment }}
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('staff.medical-records.show', $record) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if($currentDoctorForMrEditIndex && $record->patient && !$record->patient->is_guest && (!$record->doctor_id || (int) $record->doctor_id === (int) $currentDoctorForMrEditIndex->id))
                                            <a href="{{ route('staff.medical-records.edit', $record) }}"
                                               class="btn btn-sm btn-outline-secondary" title="Edit medical record">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        {{-- Create prescription from medical record --}}
                                        @if(in_array(auth()->user()->role, ['doctor', 'pharmacist']))
                                            <a href="{{ route('staff.prescriptions.create', ['medical_record_id' => $record->id]) }}" 
                                               class="btn btn-sm btn-outline-success" title="Create Prescription">
                                                <i class="fas fa-prescription-bottle-alt"></i>
                                            </a>
                                        @endif
                                        
                                        {{-- Print medical record --}}
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="printRecord({{ $record->id }})" 
                                                title="Print Record">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        
                                        {{-- Archive/Delete for admins --}}
                                        @if(auth()->user()->role === 'admin')
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="archiveRecord({{ $record->id }})" 
                                                    title="Archive Record">
                                                <i class="fas fa-archive"></i>
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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $medicalRecords->firstItem() }} to {{ $medicalRecords->lastItem() }} 
                        of {{ $medicalRecords->total() }} results
                    </div>
                    <div>
                        {{ $medicalRecords->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-file-medical fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Medical Records Found</h5>
                    <p class="text-muted mb-4">
                        @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                            Start by creating your first medical record.
                        @else
                            No medical records available to view at this time.
                        @endif
                    </p>
                    
                    @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                        <a href="{{ route('staff.medical-records.create') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-2"></i>Create First Record
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toggle filter panel - vanilla JS
function toggleFilters() {
    var panel = document.getElementById('filterPanel');
    if (panel.style.display === 'none' || !panel.style.display) {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}

// Remove individual filter - vanilla JS
function removeFilter(filterKey) {
    var url = new URL(window.location.href);
    url.searchParams.delete(filterKey);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Debounced Quick Search - vanilla JS
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('quickSearch');
    var searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var searchValue = this.value;

            searchTimeout = setTimeout(function() {
                var url = new URL(window.location.href);
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }, 500);
        });

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(searchTimeout);
                document.getElementById('quickSearchForm').submit();
            }
        });
    }

    // Initialize DataTable (if jQuery and DataTable are available)
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
        var table = document.getElementById('medicalRecordsTable');
        if (table) {
            jQuery('#medicalRecordsTable').DataTable({
                "paging": false,
                "info": false,
                "searching": false,
                "ordering": true,
                "order": [[ 4, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [0, 6] }
                ]
            });
        }
    }

    // Select all / Print selected
    var selectAll = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.record-checkbox');
    var selectedCountEl = document.getElementById('selectedCount');
    var printSelectedBtn = document.getElementById('printSelectedBtn');

    function updateSelectedState() {
        var checked = document.querySelectorAll('.record-checkbox:checked');
        var count = checked.length;
        selectedCountEl.textContent = count + ' selected';
        printSelectedBtn.disabled = count === 0;
        if (selectAll) {
            selectAll.checked = count > 0 && count === checkboxes.length;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(function(cb) { cb.checked = selectAll.checked; });
            updateSelectedState();
        });
    }
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', updateSelectedState);
    });
});

// Print medical record (single) - uses same print layout as Print selected
function printRecord(recordId) {
    var url = '{{ route("staff.medical-records.print-multiple") }}?ids[]=' + recordId;
    window.open(url, '_blank', 'noopener,noreferrer');
}

// Print selected medical records
function printSelectedRecords() {
    var checked = document.querySelectorAll('.record-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select at least one medical record to print.');
        return;
    }
    var ids = Array.from(checked).map(function(cb) { return cb.value; });
    var url = '{{ route("staff.medical-records.print-multiple") }}?' + ids.map(function(id) { return 'ids[]=' + id; }).join('&');
    window.open(url, '_blank', 'noopener,noreferrer');
}

// Archive medical record
function archiveRecord(recordId) {
    if (confirm('Are you sure you want to archive this medical record?')) {
        $.ajax({
            url: `/staff/medical-records/${recordId}/archive`,
            method: 'PATCH',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error archiving record: ' + response.message);
                }
            },
            error: function() {
                alert('Error archiving medical record. Please try again.');
            }
        });
    }
}
</script>
@endpush
