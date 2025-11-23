@extends('admin.layouts.app')

@section('title', 'Prescriptions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Prescriptions</li>
@endsection

@push('styles')
<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.table-actions .btn {
    margin: 0 2px;
    padding: 0.375rem 0.75rem;
}

.prescription-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
}

.filter-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.medication-item {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}

.medication-item:last-child {
    margin-bottom: 0;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0"><i class="fas fa-prescription-bottle-alt me-2"></i>Prescriptions</h5>
            <small class="text-muted">Manage patient prescriptions and medication orders</small>
        </div>
        <div>
            <a href="{{ contextRoute('prescriptions.create') }}" class="btn btn-doctor-primary">
                <i class="fas fa-plus me-2"></i>New Prescription
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: white;">{{ $stats['total'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Total Prescriptions</div>
                    </div>
                    <div class="stat-icon" style="width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-prescription-bottle-alt text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem; background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%); color: white; border-radius: 15px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: white;">{{ $stats['active'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Active Prescriptions</div>
                    </div>
                    <div class="stat-icon" style="width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem; background: linear-gradient(135deg, #f6c23e 0%, #f093fb 100%); color: white; border-radius: 15px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: white;">{{ $stats['pending'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">Pending Approval</div>
                    </div>
                    <div class="stat-icon" style="width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem; background: linear-gradient(135deg, #e74a3b 0%, #fd79a8 100%); color: white; border-radius: 15px;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: white;">{{ $stats['this_month'] ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.9;">This Month</div>
                    </div>
                    <div class="stat-icon" style="width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2);">
                        <i class="fas fa-calendar text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="patient_id" class="form-label">Patient</label>
                <select class="form-control" id="patient_id" name="patient_id">
                    <option value="">All Patients</option>
                    @foreach($patients as $patient)
                        @if($patient && $patient->full_name)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="doctor_id" class="form-label">Doctor</label>
                <select class="form-control" id="doctor_id" name="doctor_id">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                            Dr. {{ $doctor->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="text" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                       placeholder="dd-mm-yyyy" 
                       pattern="\d{2}-\d{2}-\d{4}" 
                       maxlength="10">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="text" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                       placeholder="dd-mm-yyyy" 
                       pattern="\d{2}-\d{2}-\d{4}" 
                       maxlength="10">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by medication name, patient name, or notes">
            </div>
            <div class="col-md-6">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ contextRoute('prescriptions.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-refresh me-1"></i>Reset
                    </a>
                    <a href="{{ contextRoute('prescriptions.create') }}" class="btn btn-success">
                        <i class="fas fa-plus me-1"></i>Add Prescription
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Prescriptions Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="doctor-card-title mb-0">
                    <i class="fas fa-list me-2"></i>Prescriptions List
                </h5>
                <div class="dropdown">
                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" 
                            id="exportDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-download me-1"></i>Export
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="doctor-card-body p-0">
            @if($prescriptions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Medications</th>
                                <th>Date Prescribed</th>
                                <th>Status</th>
                                <th>Pharmacy</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescriptions as $prescription)
                                <tr>
                                    <td>
                                        <div>
                                            @if($prescription->patient)
                                                <strong class="text-primary">{{ $prescription->patient->full_name }}</strong><br>
                                                <small class="text-muted">{{ $prescription->patient->patient_id }}</small>
                                            @else
                                                <div class="text-danger">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    <strong>Patient Deleted</strong><br>
                                                    <small class="text-muted">Patient record no longer exists</small>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>Dr. {{ $prescription->doctor->full_name }}</strong><br>
                                            <small class="text-muted">{{ $prescription->doctor->specialization }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="medications-list">
                                            @if($prescription->medications && is_array($prescription->medications))
                                                @foreach(array_slice($prescription->medications, 0, 2) as $medication)
                                                    <div class="medication-item">
                                                        <strong>{{ $medication['name'] ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">
                                                            {{ $medication['dosage'] ?? 'N/A' }} - {{ $medication['frequency'] ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                @endforeach
                                                @if(count($prescription->medications) > 2)
                                                    <small class="text-muted">
                                                        +{{ count($prescription->medications) - 2 }} more
                                                    </small>
                                                @endif
                                            @else
                                                <span class="text-muted">No medications</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ formatDate($prescription->created_at) }}</strong><br>
                                            <small class="text-muted">{{ $prescription->created_at->format('g:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($prescription->status) {
                                                'active' => 'bg-success',
                                                'completed' => 'bg-info',
                                                'cancelled' => 'bg-danger',
                                                'pending' => 'bg-warning',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="prescription-badge badge {{ $statusClass }}">
                                            {{ ucfirst($prescription->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            {{ $prescription->pharmacy_name ?: 'Not specified' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ contextRoute('prescriptions.show', $prescription) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ contextRoute('prescriptions.edit', $prescription) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit Prescription">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletePrescription({{ $prescription->id }}); return false;" 
                                                    title="Delete Prescription">
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
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        Showing {{ $prescriptions->firstItem() }} to {{ $prescriptions->lastItem() }} of {{ $prescriptions->total() }} results
                    </div>
                    {{ $prescriptions->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-prescription-bottle-alt fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No prescriptions found</h5>
                    <p class="text-muted">No prescriptions match your current filters.</p>
                    <a href="{{ contextRoute('prescriptions.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-1"></i>Add First Prescription
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form when select changes
    $('#patient_id, #doctor_id, #status').change(function() {
        $(this).closest('form').submit();
    });

    // Clear search on escape
    $('#search').keyup(function(e) {
        if (e.keyCode === 27) { // Escape key
            $(this).val('');
        }
    });
});

// Delete prescription function
function deletePrescription(prescriptionId) {
    console.log('Delete prescription called with ID:', prescriptionId);
    
    // Prevent any default behavior if event exists
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }
    
    // Handle both sync and async confirm dialogs
    function handleConfirmation(confirmResult) {
        console.log('User confirmation result:', confirmResult);
        
        if (confirmResult === true) {
            console.log('User confirmed deletion, proceeding...');
            
            // Add a small delay to ensure the dialog is properly closed
            setTimeout(() => {
                console.log('Creating form for deletion...');
                
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/prescriptions/${prescriptionId}`;
                form.style.display = 'none';
                
                // Add CSRF token - try multiple methods
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                
                // Try to get CSRF token from meta tag or Laravel's global
                let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                    csrfTokenValue = Laravel.csrfToken;
                }
                if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                    csrfTokenValue = window.Laravel.csrfToken;
                }
                
                csrfToken.value = csrfTokenValue;
                form.appendChild(csrfToken);
                
                console.log('CSRF token:', csrfTokenValue);
                
                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                console.log('Form children:', form.children);
                
                // Add form to document and submit
                document.body.appendChild(form);
                console.log('Form added to document, submitting...');
                form.submit();
            }, 100);
        } else {
            console.log('User cancelled deletion');
        }
    }
    
    // Use a more explicit confirmation dialog
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this prescription?\\n\\nThis action cannot be undone and will remove:\\n- Prescription details and medications\\n- Patient prescription history\\n- All associated prescription data\\n\\nClick OK to confirm deletion or Cancel to abort.');
    
    // Handle both Promise and boolean returns
    if (confirmDelete && typeof confirmDelete.then === 'function') {
        // If it's a Promise, wait for it to resolve
        confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
    } else {
        // If it's a boolean, handle it directly
        handleConfirmation(confirmDelete);
    }
    
    return false;
}
</script>
@endpush
