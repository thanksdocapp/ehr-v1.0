@extends('admin.layouts.app')

@section('title', 'Clinics Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Clinics</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Clinics Management</h5>
            <small class="text-muted">Manage clinics and their services</small>
        </div>
        <div>
            <a href="{{ contextRoute('departments.create') }}" class="btn btn-doctor-primary">
                <i class="fas fa-plus me-2"></i>Add New Clinic
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-primary" style="font-size: 1.75rem; font-weight: 600;">{{ $departments->total() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Clinics</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-building text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-success" style="font-size: 1.75rem; font-weight: 600;">{{ $departments->where('is_active', true)->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Active Clinics</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #16a34a); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-info" style="font-size: 1.75rem; font-weight: 600;">{{ $departments->sum('doctors_count') ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Doctors</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #0891b2); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-warning" style="font-size: 1.75rem; font-weight: 600;">{{ $departments->sum('appointments_count') ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Appointments</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ contextRoute('departments.index') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Clinic name, description..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-control">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="doctors" {{ request('sort') == 'doctors' ? 'selected' : '' }}>Doctors Count</option>
                        <option value="appointments" {{ request('sort') == 'appointments' ? 'selected' : '' }}>Appointments</option>
                        <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Recently Added</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Clinics Grid -->
    <div class="row">
        @if($departments->count() > 0)
            @foreach($departments as $department)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="doctor-card h-100">
                    @if($department->image)
                        <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover; border-radius: 0.5rem 0.5rem 0 0;"
                             alt="{{ $department->name }}">
                    @endif
                    
                    <div class="doctor-card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="doctor-card-title mb-0">{{ $department->name }}</h5>
                            @if($department->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                        
                        <p class="text-muted mb-3">
                            {{ Str::limit($department->description, 100) }}
                        </p>
                        
                        <!-- Clinic Stats -->
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="fw-bold text-primary">{{ $department->doctors_count }}</div>
                                <small class="text-muted">Doctors</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success">{{ $department->appointments_count }}</div>
                                <small class="text-muted">Appointments</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-info">{{ $department->services ? count($department->services) : 0 }}</div>
                                <small class="text-muted">Services</small>
                            </div>
                        </div>
                        
                        <!-- Clinic Info -->
                        @if($department->head_of_department)
                            <div class="mb-2">
                                <small class="text-muted">Head of Clinic:</small>
                                <div class="fw-bold">{{ $department->head_of_department }}</div>
                            </div>
                        @endif
                        
                        @if($department->location)
                            <div class="mb-3">
                                <small class="text-muted">Location:</small>
                                <div>{{ $department->location }}</div>
                            </div>
                        @endif
                        
                        <!-- Actions -->
                        <div class="mt-auto">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ contextRoute('departments.show', $department->id) }}" 
                                   class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ contextRoute('departments.edit', $department->id) }}" 
                                   class="btn btn-outline-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-outline-info" 
                                        onclick="viewDoctors({{ $department->id }})" 
                                        title="View Doctors">
                                    <i class="fas fa-user-md"></i>
                                </button>
                                <button class="btn btn-outline-{{ $department->is_active ? 'warning' : 'success' }}"
                                        onclick="toggleStatus({{ $department->id }})"
                                        title="{{ $department->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $department->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                                <button class="btn btn-outline-danger"
                                        onclick="deleteDepartment({{ $department->id }})"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="doctor-card">
                    <div class="doctor-card-body text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No clinics found</h5>
                        <p class="text-muted mb-4">No clinics match your current filters.</p>
                        <a href="{{ contextRoute('departments.create') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-2"></i>Create First Clinic
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($departments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $departments->links() }}
    </div>
    @endif

    <!-- Quick Actions Modal -->
    <div class="modal fade" id="quickActionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-doctor-primary" onclick="exportDepartments()">
                            <i class="fas fa-download me-2"></i>Export All Clinics
                        </button>
                        <button class="btn btn-outline-success" onclick="activateAll()">
                            <i class="fas fa-check me-2"></i>Activate All Clinics
                        </button>
                        <button class="btn btn-outline-warning" onclick="deactivateAll()">
                            <i class="fas fa-pause me-2"></i>Deactivate All Clinics
                        </button>
                        <button class="btn btn-outline-info" onclick="generateReport()">
                            <i class="fas fa-chart-bar me-2"></i>Generate Clinic Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <button class="btn btn-doctor-primary rounded-circle" 
                data-bs-toggle="modal" 
                data-bs-target="#quickActionsModal"
                style="width: 56px; height: 56px;">
            <i class="fas fa-cog"></i>
        </button>
    </div>
</div>


<!-- Application Footer -->
@if(shouldShowPoweredBy())
<div class="text-center mt-5 py-4" style="border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <i class="fas fa-building" style="color: #e94560;"></i>
        <span>Clinics Management - <strong>{{ getAppName() }} v{{ getAppVersion() }}</strong></span>
    </div>
    <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
        {{ getCopyrightText() }}
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Clinic actions
    function viewDoctors(departmentId) {
        window.location.href = `/admin/doctors?department_id=${departmentId}`;
    }

    function toggleStatus(departmentId) {
        // Show a confirmation dialog
        const confirmChange = confirm('Are you sure you want to change this clinic\'s status?\n\nThis action will affect the clinic\'s visibility in the system and patient booking availability.');
        
        // Check if confirmation is a Promise
        if (confirmChange && typeof confirmChange.then === 'function') {
            confirmChange.then(result => {
                if (result) sendStatusChangeRequest(departmentId);
            }).catch(() => {});
        } else if (confirmChange) {
            sendStatusChangeRequest(departmentId);
        }
    }

    function sendStatusChangeRequest(departmentId) {
        // Make the request to toggle the status
        fetch(`/admin/departments/${departmentId}/toggle-status`, {
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
                alert('Error changing clinic status');
            }
        })
        .catch(error => {
            console.error('Error toggling department status:', error);
            alert('An error occurred while changing the clinic\'s status. Please try again.');
        });
    }

    function deleteDepartment(departmentId) {
        console.log('Delete department called with ID:', departmentId);
        
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
                    form.action = `/admin/departments/${departmentId}`;
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
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this clinic?\n\nThis action cannot be undone and will remove all clinic data including:\n- Clinic information\n- Associated doctors\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.');
        
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

    // Quick actions
    function exportDepartments() {
        alert('Export functionality will be implemented');
    }

    function activateAll() {
        if (confirm('Are you sure you want to activate all clinics?')) {
            alert('Bulk activate functionality will be implemented');
        }
    }

    function deactivateAll() {
        if (confirm('Are you sure you want to deactivate all clinics?')) {
            alert('Bulk deactivate functionality will be implemented');
        }
    }

    function generateReport() {
        alert('Generate report functionality will be implemented');
    }

    // Auto-refresh data every 30 seconds
    setInterval(function() {
        // Update department stats without full page reload
        // fetch('/admin/departments/stats').then(response => response.json()).then(data => {
        //     // Update stats display
        // });
    }, 30000);
</script>
@endpush
