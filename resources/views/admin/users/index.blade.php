@extends('admin.layouts.app')

@section('title', 'User Management')

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">User Management</h1>
                    <p class="modern-page-subtitle">Manage system users and their access</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ contextRoute('users.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i>Add New User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Modern Filters Card -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-filter"></i>Search & Filters
            </h5>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ contextRoute('users.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="modern-form-label">Search Users</label>
                        <input type="text" class="modern-form-control" id="search" name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Name, email, phone, employee ID...">
                    </div>
                    <div class="col-md-2">
                        <label class="modern-form-label">Role</label>
                        <select class="modern-form-select" id="role" name="role">
                            <option value="">All Roles</option>
                            @foreach($roles as $key => $role)
                                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="modern-form-label">Clinic</label>
                        <select class="modern-form-select" id="department" name="department">
                            <option value="">All Clinics</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                        {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="modern-form-label">Status</label>
                        <select class="modern-form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="modern-form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-modern btn-modern-primary">
                                <i class="fas fa-search"></i>Filter
                            </button>
                            <a href="{{ contextRoute('users.index') }}" class="btn-modern btn-modern-outline">
                                <i class="fas fa-times"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-list me-2"></i>Users ({{ $users->total() }} total)
            </h5>
        </div>
        <div class="doctor-card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none">
                                    Name
                                    @if(request('sort') === 'name')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Clinic</th>
                            <th>Employee ID</th>
                            <th>Status</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" 
                                   class="text-decoration-none">
                                    Created
                                    @if(request('sort') === 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <img src="{{ $user->avatar_url }}" 
                                         alt="{{ $user->name }}" 
                                         class="rounded-circle" 
                                         width="40" height="40">
                                </td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->is_admin)
                                        <span class="badge badge-danger badge-sm ml-1">Admin</span>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?: 'N/A' }}</td>
                                <td>
                                    <span class="badge badge-primary" style="color: white !important; background-color: #007bff !important;">{{ $user->role_display }}</span>
                                </td>
                                <td>
                                    @php
                                        // For doctors, get clinics from Doctor model
                                        if ($user->role === 'doctor' && $user->doctor) {
                                            $allDepartments = $user->doctor->departments ?? collect();
                                            
                                            // Fallback to single department if no departments relationship
                                            if ($allDepartments->isEmpty() && $user->doctor->department) {
                                                $allDepartments = collect([$user->doctor->department]);
                                            }
                                        } else {
                                            // For other roles, use user's departments
                                            $allDepartments = $user->departments->isNotEmpty() 
                                                ? $user->departments 
                                                : collect([$user->department])->filter();
                                        }
                                    @endphp
                                    @if($allDepartments->isNotEmpty())
                                        @foreach($allDepartments as $dept)
                                            @if($dept && $dept->name)
                                                <div class="mb-1">
                                                    <span class="badge bg-info">
                                                        {{ $dept->name }}
                                                        @if($dept->pivot && $dept->pivot->is_primary)
                                                            <span class="badge bg-warning text-dark ms-1">Primary</span>
                                                        @elseif(!$dept->pivot && $user->role === 'doctor' && $user->doctor && $dept->id == $user->doctor->department_id)
                                                            <span class="badge bg-warning text-dark ms-1">Primary</span>
                                                        @elseif(!$dept->pivot && $user->role !== 'doctor' && $dept->id == $user->department_id)
                                                            <span class="badge bg-warning text-dark ms-1">Primary</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>{{ $user->employee_id ?: 'N/A' }}</td>
                                <td>
                                    @php
                                        // For doctors, sync status with Doctor model
                                        if ($user->role === 'doctor' && $user->doctor) {
                                            $isActive = $user->doctor->is_active ?? $user->is_active;
                                        } else {
                                            $isActive = $user->is_active;
                                        }
                                        $statusBadge = $isActive ? 'success' : 'danger';
                                    @endphp
                                    <span class="badge badge-{{ $statusBadge }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ formatDate($user->created_at) }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ contextRoute('users.show', $user) }}" 
                                           class="btn btn-sm btn-info" 
                                           title="View User">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ contextRoute('users.edit', $user) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}" 
                                                onclick="toggleStatus({{ $user->id }})"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }} User">
                                            <i class="fas fa-{{ $user->is_active ? 'user-times' : 'user-check' }}"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning" 
                                                    onclick="resetUserPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-info" 
                                                    onclick="resendUserCredentials({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Resend Credentials">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deleteUser({{ $user->id }}); return false;"
                                                    title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-users fa-3x mb-3 text-gray-300"></i>
                                    <p class="mb-0">No users found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Reset User Password - Using SweetAlert2 for better UI
    async function resetUserPassword(userId, userName) {
        // Prevent event propagation
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Ask for reason using SweetAlert2
        const { value: reason } = await Swal.fire({
            title: '<i class="fas fa-key"></i> Reset Password',
            html: `<p>You are about to reset the password for: <strong>${userName}</strong></p>`,
            input: 'textarea',
            inputLabel: 'Reason for Reset (Required for audit trail)',
            inputPlaceholder: 'Enter reason for password reset...',
            inputAttributes: {
                'aria-label': 'Enter reason for password reset'
            },
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'Reason is required for audit trail!';
                }
            }
        });
        
        if (!reason) {
            return; // User cancelled
        }
        
        // Confirm action using SweetAlert2
        const result = await Swal.fire({
            title: '⚠️ Confirm Password Reset',
            html: `
                <div style="text-align: left;">
                    <p><strong>User:</strong> ${userName}</p>
                    <p><strong>Reason:</strong> ${reason}</p>
                    <hr>
                    <p class="text-muted small">The user will receive a secure reset link via email and will be required to change their password on next login.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reset Password',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
        });
        
        if (result.isConfirmed) {
            sendPasswordResetRequest(userId, reason);
        }
    }
    
    function sendPasswordResetRequest(userId, reason) {
        fetch(`/admin/users/${userId}/reset-password`, {
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
                Swal.fire({
                    title: 'Success!',
                    text: 'Password reset link has been sent to the user!',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Unknown error occurred',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while resetting the password.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        });
    }
    
    // Resend User Credentials - Using SweetAlert2
    async function resendUserCredentials(userId, userName) {
        // Prevent event propagation
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Confirm action using SweetAlert2
        const result = await Swal.fire({
            title: '<i class="fas fa-envelope"></i> Resend Login Credentials',
            html: `
                <div style="text-align: left;">
                    <p><strong>User:</strong> ${userName}</p>
                    <hr>
                    <p class="text-muted small">The user will receive their username and a secure link to set their password via email.</p>
                    <p class="text-info small"><i class="fas fa-info-circle"></i> <strong>Note:</strong> No plaintext passwords will be sent. The link will be valid for 72 hours.</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send Credentials',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
        });
        
        if (!result.isConfirmed) {
            return;
        }
        
        // Send request
        fetch(`/admin/users/${userId}/resend-credentials`, {
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
                Swal.fire({
                    title: 'Success!',
                    text: 'Login credentials have been sent to the user!',
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Unknown error occurred',
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while sending credentials.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        });
    }
</script>
<script>
    function toggleStatus(userId) {
        console.log('toggleStatus called with userId:', userId);
        
        // Show a confirmation dialog
        const confirmChange = confirm('Are you sure you want to change this user\'s status?\n\nThis action can be reverted, but it will affect the user\'s access to the system.');
        
        console.log('User confirmation result:', confirmChange);
        
        // Check if confirmation is a Promise
        if (confirmChange && typeof confirmChange.then === 'function') {
            confirmChange.then(result => {
                if (result) sendUserStatusChangeRequest(userId);
            }).catch(() => {});
        } else if (confirmChange) {
            sendUserStatusChangeRequest(userId);
        }
    }

    function sendUserStatusChangeRequest(userId) {
        console.log('sendUserStatusChangeRequest called with userId:', userId);
        
        // Make the request to toggle the status
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                alert('User status updated successfully!');
                location.reload();
            } else {
                alert('Error changing user status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error toggling user status:', error);
            alert('An error occurred while changing the user\'s status. Please try again.');
        });
    }

    function deleteUser(userId) {
        console.log('Delete user called with ID:', userId);
        
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
                    form.action = `/admin/users/${userId}`;
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
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this user?\n\nThis action cannot be undone and will remove all user data including:\n- Personal information\n- Account access\n- Role assignments\n- Activity history\n\nClick OK to confirm deletion or Cancel to abort.');
        
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
