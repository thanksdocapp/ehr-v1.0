@extends('admin.layouts.app')

@section('title', 'User Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
    .user-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .user-avatar:hover {
        border-color: #667eea;
        transform: scale(1.1);
    }

    .user-info-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-name-block {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 0.125rem;
    }

    .user-email {
        font-size: 0.8rem;
        color: #64748b;
    }

    .role-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .role-admin { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
    .role-doctor { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
    .role-nurse { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }
    .role-receptionist { background: linear-gradient(135deg, #28a745, #218838); color: white; }
    .role-pharmacist { background: linear-gradient(135deg, #6f42c1, #5a32a3); color: white; }
    .role-technician { background: linear-gradient(135deg, #fd7e14, #e36209); color: white; }
    .role-staff { background: linear-gradient(135deg, #6c757d, #5a6268); color: white; }

    .clinic-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 500;
        margin: 0.125rem;
    }

    .clinic-tag.primary {
        background: #fef3c7;
        color: #92400e;
    }

    .action-btn-group {
        display: flex;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        transition: all 0.2s ease;
        font-size: 0.8rem;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .stats-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .stat-card {
        flex: 1;
        min-width: 150px;
        background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
        font-size: 1.25rem;
        color: white;
    }

    .stat-icon.total { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-icon.active { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-icon.doctors { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-icon.staff { background: linear-gradient(135deg, #f59e0b, #d97706); }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2d3748;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .modern-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background: #f8fafc;
    }

    .modern-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .sort-link {
        color: inherit;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .sort-link:hover {
        color: #667eea;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #f1f5f9;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        font-size: 2rem;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="modern-page-title">
                        <i class="fas fa-users-cog"></i>
                        User Management
                    </h1>
                    <p class="modern-page-subtitle">Manage system users, roles, and access permissions</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ contextRoute('users.create') }}" class="btn btn-modern btn-modern-primary">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row fade-in-up">
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value">{{ $users->total() }}</div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-value">{{ $users->where('is_active', true)->count() }}</div>
            <div class="stat-label">Active Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon doctors">
                <i class="fas fa-user-md"></i>
            </div>
            <div class="stat-value">{{ $users->where('role', 'doctor')->count() }}</div>
            <div class="stat-label">Doctors</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon staff">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-value">{{ $users->whereNotIn('role', ['doctor', 'admin'])->count() }}</div>
            <div class="stat-label">Staff Members</div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="modern-card fade-in-up mb-4">
        <div class="modern-card-header">
            <h6 class="modern-card-title">
                <i class="fas fa-filter"></i>
                Search & Filters
            </h6>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ contextRoute('users.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="modern-form-label">Search Users</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control modern-form-control border-start-0"
                                   name="search" value="{{ request('search') }}"
                                   placeholder="Name, email, phone...">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="modern-form-label">Role</label>
                        <select class="form-select modern-form-select" name="role">
                            <option value="">All Roles</option>
                            @foreach($roles as $key => $role)
                                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="modern-form-label">Clinic</label>
                        <select class="form-select modern-form-select" name="department">
                            <option value="">All Clinics</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label class="modern-form-label">Status</label>
                        <select class="form-select modern-form-select" name="status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-modern btn-modern-primary flex-fill">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ contextRoute('users.index') }}" class="btn btn-modern btn-modern-outline">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="modern-card fade-in-up">
        <div class="modern-card-header">
            <h6 class="modern-card-title">
                <i class="fas fa-list"></i>
                Users List
            </h6>
            <span class="badge bg-primary">{{ $users->total() }} total</span>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'role', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                    Role
                                    @if(request('sort') === 'role')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Clinic(s)</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="sort-link">
                                    Created
                                    @if(request('sort') === 'created_at')
                                        <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="user-avatar">
                                        <div class="user-name-block">
                                            <span class="user-name">
                                                {{ $user->name }}
                                                @if($user->is_admin)
                                                    <i class="fas fa-shield-alt text-danger ms-1" title="Administrator"></i>
                                                @endif
                                            </span>
                                            <span class="user-email">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge role-{{ $user->role ?? 'staff' }}">
                                        {{ $user->role_display ?? ucfirst($user->role ?? 'Staff') }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        if ($user->role === 'doctor' && $user->doctor) {
                                            $allDepartments = $user->doctor->departments ?? collect();
                                            if ($allDepartments->isEmpty() && $user->doctor->department) {
                                                $allDepartments = collect([$user->doctor->department]);
                                            }
                                        } else {
                                            $allDepartments = $user->departments->isNotEmpty()
                                                ? $user->departments
                                                : collect([$user->department])->filter();
                                        }
                                    @endphp
                                    @if($allDepartments->isNotEmpty())
                                        @foreach($allDepartments->take(2) as $dept)
                                            @if($dept && $dept->name)
                                                @php
                                                    $isPrimary = ($dept->pivot && $dept->pivot->is_primary) ||
                                                        (!$dept->pivot && $user->role === 'doctor' && $user->doctor && $dept->id == $user->doctor->department_id) ||
                                                        (!$dept->pivot && $user->role !== 'doctor' && $dept->id == $user->department_id);
                                                @endphp
                                                <span class="clinic-tag {{ $isPrimary ? 'primary' : '' }}">
                                                    {{ Str::limit($dept->name, 15) }}
                                                    @if($isPrimary)
                                                        <i class="fas fa-star" style="font-size: 0.6rem;"></i>
                                                    @endif
                                                </span>
                                            @endif
                                        @endforeach
                                        @if($allDepartments->count() > 2)
                                            <span class="clinic-tag">+{{ $allDepartments->count() - 2 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->phone)
                                        <span class="text-muted">{{ $user->phone }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        if ($user->role === 'doctor' && $user->doctor) {
                                            $isActive = $user->doctor->is_active ?? $user->is_active;
                                        } else {
                                            $isActive = $user->is_active;
                                        }
                                    @endphp
                                    @if($isActive)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            <i class="fas fa-pause-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small">{{ formatDate($user->created_at) }}</span>
                                </td>
                                <td>
                                    <div class="action-btn-group justify-content-center">
                                        <a href="{{ contextRoute('users.show', $user) }}"
                                           class="action-btn btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ contextRoute('users.edit', $user) }}"
                                           class="action-btn btn btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="action-btn btn btn-{{ $user->is_active ? 'warning' : 'success' }}"
                                                onclick="toggleStatus({{ $user->id }})"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $user->is_active ? 'user-times' : 'user-check' }}"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button type="button" class="action-btn btn btn-secondary"
                                                    onclick="resetUserPassword({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <button type="button" class="action-btn btn btn-outline-info"
                                                    onclick="resendUserCredentials({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Resend Credentials">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                            <button type="button" class="action-btn btn btn-danger"
                                                    onclick="deleteUser({{ $user->id }}); return false;"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No Users Found</h5>
                                        <p class="text-muted mb-4">No users match your current filters.</p>
                                        <a href="{{ contextRoute('users.create') }}" class="btn btn-modern btn-modern-primary">
                                            <i class="fas fa-user-plus me-2"></i>Add New User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="d-flex justify-content-center p-4 border-top">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Reset User Password
async function resetUserPassword(userId, userName) {
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }

    const { value: reason } = await Swal.fire({
        title: '<i class="fas fa-key text-warning"></i> Reset Password',
        html: `<p class="mb-3">Reset password for: <strong>${userName}</strong></p>`,
        input: 'textarea',
        inputLabel: 'Reason for Reset (Required)',
        inputPlaceholder: 'Enter reason for password reset...',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#667eea',
        inputValidator: (value) => {
            if (!value || value.trim() === '') {
                return 'Reason is required!';
            }
        }
    });

    if (!reason) return;

    const result = await Swal.fire({
        title: 'Confirm Password Reset',
        html: `
            <div class="text-start">
                <p><strong>User:</strong> ${userName}</p>
                <p><strong>Reason:</strong> ${reason}</p>
                <hr>
                <p class="text-muted small mb-0">A secure reset link will be sent via email.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reset Password',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#667eea'
    });

    if (result.isConfirmed) {
        fetch(`/admin/users/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason.trim(), notify_via: 'email', force_change: true })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success!', 'Password reset link sent!', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error!', data.message || 'Unknown error', 'error');
            }
        })
        .catch(() => Swal.fire('Error!', 'An error occurred.', 'error'));
    }
}

// Resend User Credentials
async function resendUserCredentials(userId, userName) {
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }

    const result = await Swal.fire({
        title: '<i class="fas fa-envelope text-info"></i> Resend Credentials',
        html: `<p>Send login credentials to: <strong>${userName}</strong></p>
               <p class="text-muted small">A secure password reset link will be sent.</p>`,
        showCancelButton: true,
        confirmButtonText: 'Yes, Send',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#667eea'
    });

    if (!result.isConfirmed) return;

    fetch(`/admin/users/${userId}/resend-credentials`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ notify_via: 'email' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'Credentials sent!', 'success').then(() => location.reload());
        } else {
            Swal.fire('Error!', data.message || 'Unknown error', 'error');
        }
    })
    .catch(() => Swal.fire('Error!', 'An error occurred.', 'error'));
}

// Toggle Status
function toggleStatus(userId) {
    Swal.fire({
        title: 'Change User Status?',
        text: 'This will affect the user\'s access to the system.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Change',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#667eea'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/users/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'User status updated!', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', data.message || 'Unknown error', 'error');
                }
            })
            .catch(() => Swal.fire('Error!', 'An error occurred.', 'error'));
        }
    });
}

// Delete User
function deleteUser(userId) {
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }

    Swal.fire({
        title: 'Delete User?',
        html: '<p class="text-danger">This action cannot be undone!</p><p class="small text-muted">All user data will be permanently removed.</p>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}`;
            form.style.display = 'none';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });

    return false;
}
</script>
@endpush
