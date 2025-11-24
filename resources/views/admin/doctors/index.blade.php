@extends('admin.layouts.app')

@section('title', 'Doctors Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Doctors</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">Doctors Management</h1>
                    <p class="modern-page-subtitle">Manage hospital doctors and their information</p>
                </div>
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.doctors.export.csv', request()->all()) }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-file-export me-2"></i>Export CSV
                    </a>
                    <a href="{{ route('admin.doctors.import') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-file-import me-2"></i>Import CSV
                    </a>
                    <a href="{{ contextRoute('doctors.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i>Add New Doctor
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-modern fade-in-up stagger-1">
                <div class="stat-card-icon" style="background: var(--gradient-primary);">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="stat-card-number">{{ number_format($doctors->total() ?? 0) }}</div>
                <div class="stat-card-label">Total Doctors</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-modern fade-in-up stagger-2">
                <div class="stat-card-icon" style="background: var(--gradient-success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-number">{{ number_format($doctors->where('is_active', true)->count() ?? 0) }}</div>
                <div class="stat-card-label">Active Doctors</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-modern fade-in-up stagger-3">
                <div class="stat-card-icon" style="background: var(--gradient-info);">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-card-number">{{ number_format($departments->count() ?? 0) }}</div>
                <div class="stat-card-label">Clinics</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-modern fade-in-up stagger-4">
                <div class="stat-card-icon" style="background: var(--gradient-warning);">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-card-number">0</div>
                <div class="stat-card-label">Today's Appointments</div>
            </div>
        </div>
    </div>

    <!-- Modern Filters Card -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-filter"></i>Filters
            </h5>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ contextRoute('doctors.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="modern-form-label">Search</label>
                    <input type="text" name="search" class="modern-form-control" 
                           placeholder="Doctor name, email, phone..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="modern-form-label">Clinic</label>
                    <select name="department_id" class="modern-form-select">
                        <option value="">All Clinics</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="modern-form-label">Status</label>
                    <select name="status" class="modern-form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="modern-form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn-modern btn-modern-primary">
                            <i class="fas fa-search"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modern Doctors Table -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-list"></i>Doctors List
            </h5>
            <div class="d-flex gap-2">
                <button class="btn-modern btn-modern-outline btn-modern-sm" onclick="exportDoctors()">
                    <i class="fas fa-download"></i>Export
                </button>
                <button class="btn-modern btn-modern-outline btn-modern-sm" onclick="refreshTable()">
                    <i class="fas fa-sync"></i>Refresh
                </button>
            </div>
        </div>
        <div class="modern-card-body">
            @if($doctors->count() > 0)
                <div class="modern-table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Doctor</th>
                                <th>Clinic</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($doctors as $doctor)
                            @php
                                // Get clinics from departments relationship (many-to-many)
                                $clinics = $doctor->departments ?? collect();
                                
                                // Fallback to single department if no departments relationship
                                if ($clinics->isEmpty() && $doctor->department) {
                                    $clinics = collect([$doctor->department]);
                                }
                                
                                $clinicCount = $clinics->count();
                                $hasMultipleClinics = $clinicCount > 1;
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input doctor-checkbox" 
                                           value="{{ $doctor->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($doctor->photo)
                                            <img src="{{ Storage::url('uploads/doctors/' . $doctor->photo) }}" 
                                                 alt="{{ $doctor->name }}" 
                                                 class="rounded-circle me-3" 
                                                 style="width: 45px; height: 45px; object-fit: cover;">
                                        @else
                                            <div class="avatar-placeholder bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 45px; height: 45px;">
                                                {{ strtoupper(substr($doctor->name ?? $doctor->first_name ?? 'D', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $doctor->name ?? $doctor->title . ' ' . $doctor->first_name . ' ' . $doctor->last_name }}</div>
                                            @if($doctor->specialization)
                                                <small class="text-muted">{{ $doctor->specialization }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($clinicCount > 0)
                                        @if($hasMultipleClinics)
                                            <div>
                                                @foreach($clinics as $clinic)
                                                    @if($clinic && $clinic->name)
                                                        <div class="mb-1">
                                                            <span class="badge bg-info">{{ $clinic->name }}</span>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @else
                                            @if($clinics->first() && $clinics->first()->name)
                                                <span class="badge bg-info">{{ $clinics->first()->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-break">{{ $doctor->email ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div>{{ $doctor->phone ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @if($doctor->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group" style="flex-wrap: wrap;">
                                        <a href="{{ route('admin.doctors.show', $doctor->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.doctors.edit', $doctor->id) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($doctor->user_id)
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="resetPassword({{ $doctor->id }}, '{{ $doctor->full_name }}')" 
                                                title="Reset Password">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="resendCredentials({{ $doctor->id }}, '{{ $doctor->full_name }}')" 
                                                title="Resend Credentials">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-{{ $doctor->is_active ? 'warning' : 'success' }}" 
                                                onclick="toggleStatus({{ $doctor->id }})" 
                                                title="{{ $doctor->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $doctor->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteDoctor({{ $doctor->id }}); return false;" 
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
                <div class="doctor-card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $doctors->firstItem() }} to {{ $doctors->lastItem() }} 
                        of {{ $doctors->total() }} doctors
                    </div>
                    {{ $doctors->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-md text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-muted">No doctors found</h5>
                    <p class="text-muted mb-4">No doctors match your current filters.</p>
                    <a href="{{ contextRoute('doctors.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Add First Doctor
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
                        <span id="selectedCount">0</span> doctor(s) selected
                    </span>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-success" onclick="bulkActivate()">
                            <i class="fas fa-check me-1"></i>Activate Selected
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="bulkDeactivate()">
                            <i class="fas fa-pause me-1"></i>Deactivate Selected
                        </button>
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
        <i class="fas fa-user-md" style="color: #e94560;"></i>
        <span>Doctors Management - <strong>{{ getAppName() }} v{{ getAppVersion() }}</strong></span>
    </div>
    <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
        {{ getCopyrightText() }}
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.doctor-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });

    // Individual checkbox functionality
    document.querySelectorAll('.doctor-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });

    function updateBulkActions() {
        const selectedCheckboxes = document.querySelectorAll('.doctor-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (selectedCheckboxes.length > 0) {
            bulkActions.style.display = 'block';
            selectedCount.textContent = selectedCheckboxes.length;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    // Doctor actions
    function viewDepartment(departmentId) {
        if (departmentId && departmentId !== 0) {
            window.location.href = `/admin/departments/${departmentId}`;
        } else {
            alert('This doctor is not assigned to any clinic.');
        }
    }

    function toggleStatus(doctorId) {
        // Show a confirmation dialog
        const confirmChange = confirm('Are you sure you want to change this doctor\'s status?\n\nThis action can be reverted, but it will affect the doctor’s visibility in the system.');
        
        // Check if confirmation is a Promise
        if (confirmChange && typeof confirmChange.then === 'function') {
            confirmChange.then(result => {
                if (result) sendStatusChangeRequest(doctorId);
            }).catch(() => {});
        } else if (confirmChange) {
            sendStatusChangeRequest(doctorId);
        }
    }

    function sendStatusChangeRequest(doctorId) {
        // Make the request to toggle the status
        fetch(`/admin/doctors/${doctorId}/toggle-status`, {
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
                alert('Error changing doctor status');
            }
        })
        .catch(error => {
            console.error('Error toggling doctor status:', error);
            alert('An error occurred while changing the doctor\'s status. Please try again.');
        });
    }

    function resetPassword(doctorId, doctorName) {
        const reason = prompt(`Reset password for ${doctorName}?\n\nPlease enter a reason for this action:`);
        if (!reason || reason.trim() === '') {
            alert('Please enter a reason for the password reset.');
            return;
        }

        fetch(`/admin/doctors/${doctorId}/reset-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                reason: reason.trim(),
                notify_via: 'email',
                force_change: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Password reset link has been sent to the doctor.');
            } else {
                alert(data.message || 'Failed to reset password.');
            }
        })
        .catch(error => {
            console.error('Error resetting password:', error);
            alert('An error occurred while resetting the password.');
        });
    }

    function resendCredentials(doctorId, doctorName) {
        if (!confirm(`Resend login credentials to ${doctorName}?`)) {
            return;
        }

        fetch(`/admin/doctors/${doctorId}/resend-credentials`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                notify_via: 'email'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Login credentials have been sent to the doctor.');
            } else {
                alert(data.message || 'Failed to resend credentials.');
            }
        })
        .catch(error => {
            console.error('Error resending credentials:', error);
            alert('An error occurred while resending credentials.');
        });
    }

    function deleteDoctor(doctorId) {
        console.log('Delete doctor called with ID:', doctorId);
        
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
                    form.action = `/admin/doctors/${doctorId}`;
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
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this doctor?\n\nThis action cannot be undone and will remove all doctor data including:\n- Personal information\n- Professional credentials\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.');
        
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

    function refreshTable() {
        location.reload();
    }

    function exportDoctors() {
        alert('Export functionality will be implemented');
    }

    function bulkActivate() {
        const selected = Array.from(document.querySelectorAll('.doctor-checkbox:checked')).map(cb => cb.value);
        if (confirm(`Activate ${selected.length} doctor(s)?`)) {
            alert('Bulk activate functionality will be implemented');
        }
    }

    function bulkDeactivate() {
        const selected = Array.from(document.querySelectorAll('.doctor-checkbox:checked')).map(cb => cb.value);
        if (confirm(`Deactivate ${selected.length} doctor(s)?`)) {
            alert('Bulk deactivate functionality will be implemented');
        }
    }

    function bulkExport() {
        const selected = Array.from(document.querySelectorAll('.doctor-checkbox:checked')).map(cb => cb.value);
        alert(`Export ${selected.length} doctor(s) functionality will be implemented`);
    }

    function bulkDelete() {
        const selected = Array.from(document.querySelectorAll('.doctor-checkbox:checked')).map(cb => cb.value);
        
        if (selected.length === 0) {
            alert('Please select at least one doctor to delete.');
            return;
        }
        
        const confirmDelete = confirm(`⚠️ WARNING: Are you sure you want to permanently delete ${selected.length} doctor(s)?\n\nThis action cannot be undone and will remove all selected doctors' data including:\n- Personal information\n- Professional credentials\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.`);
        
        if (confirmDelete) {
            console.log('Bulk deleting doctors:', selected);
            
            // Add a small delay to ensure the dialog is properly closed
            setTimeout(() => {
                // Create a form to submit the bulk DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/doctors/bulk-delete';
                form.style.display = 'none';
                
                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                
                let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                    csrfTokenValue = Laravel.csrfToken;
                }
                if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                    csrfTokenValue = window.Laravel.csrfToken;
                }
                
                csrfToken.value = csrfTokenValue;
                form.appendChild(csrfToken);
                
                // Add selected doctor IDs
                selected.forEach(doctorId => {
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'doctor_ids[]';
                    idInput.value = doctorId;
                    form.appendChild(idInput);
                });
                
                // Add form to document and submit
                document.body.appendChild(form);
                form.submit();
            }, 100);
        }
    }
</script>
@endpush
