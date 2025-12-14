@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
/* Page Header */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
}

.page-header h4 {
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-header p {
    opacity: 0.9;
    margin-bottom: 0;
}

/* Profile Card */
.profile-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e3e6f0;
}

.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
}

.profile-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 40px;
    background: linear-gradient(to top, rgba(255,255,255,0.1), transparent);
}

.avatar-container {
    position: relative;
    display: inline-block;
    margin-bottom: 1rem;
}

.avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

.avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
    border: 4px solid rgba(255,255,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-indicator {
    position: absolute;
    bottom: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 3px solid white;
}

.status-indicator.active {
    background: #10b981;
}

.status-indicator.inactive {
    background: #6b7280;
}

.profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.profile-email {
    opacity: 0.9;
    margin-bottom: 1rem;
}

.profile-badges {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.profile-badge {
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

.profile-badge.active {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.profile-badge.inactive {
    background: rgba(107, 114, 128, 0.2);
    color: rgba(255,255,255,0.8);
    border: 1px solid rgba(107, 114, 128, 0.3);
}

.profile-badge.admin {
    background: rgba(245, 158, 11, 0.2);
    color: #fcd34d;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.profile-badge.role {
    background: rgba(255,255,255,0.2);
    color: white;
    border: 1px solid rgba(255,255,255,0.3);
}

.profile-body {
    padding: 1.5rem;
}

/* Action Buttons */
.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.2s ease;
    text-decoration: none;
    gap: 0.5rem;
}

.action-btn.primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
}

.action-btn.secondary {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #e5e7eb;
}

.action-btn.secondary:hover {
    background: #e5e7eb;
    color: #1f2937;
}

/* Info Sections */
.info-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.info-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
}

.info-card-header .header-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-size: 0.9rem;
}

.info-card-header .header-icon.personal {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.info-card-header .header-icon.account {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
}

.info-card-header .header-icon.bio {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
}

.info-card-header .header-icon.address {
    background: linear-gradient(135deg, #fa709a, #fee140);
    color: white;
}

.info-card-header h6 {
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0;
    font-size: 0.9rem;
}

.info-card-body {
    padding: 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f3f4f6;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-label i {
    color: #6b7280;
    width: 16px;
}

.info-value {
    color: #6b7280;
    font-size: 0.875rem;
    text-align: right;
}

.info-value .not-set {
    color: #9ca3af;
    font-style: italic;
}

/* Badges */
.role-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.role-badge.admin { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
.role-badge.doctor { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.role-badge.nurse { background: linear-gradient(135deg, #20c997, #17a2b8); color: white; }
.role-badge.receptionist { background: linear-gradient(135deg, #fd7e14, #e55a00); color: white; }
.role-badge.pharmacist { background: linear-gradient(135deg, #28a745, #218838); color: white; }
.role-badge.technician { background: linear-gradient(135deg, #6c757d, #5a6268); color: white; }
.role-badge.staff { background: linear-gradient(135deg, #17a2b8, #138496); color: white; }

.clinic-tag {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    background: #eef2ff;
    color: #667eea;
    margin: 0.15rem;
}

.clinic-tag.primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.clinic-tag .primary-indicator {
    font-size: 0.65rem;
    margin-left: 0.35rem;
    background: rgba(255,255,255,0.2);
    padding: 0.15rem 0.35rem;
    border-radius: 4px;
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge.active {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.status-badge.inactive {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

/* Stats Cards */
.stats-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    flex: 1;
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    border: 1px solid #e3e6f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.25rem;
}

.stat-icon.days {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    color: #667eea;
}

.stat-icon.update {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
    color: #10b981;
}

.stat-icon.status {
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.1), rgba(0, 242, 254, 0.1));
    color: #4facfe;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1a202c;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.8rem;
    color: #6b7280;
}

/* Bio Section */
.bio-content {
    padding: 1.25rem;
    color: #4b5563;
    line-height: 1.6;
}

/* Quick Links */
.quick-links {
    margin-top: 1rem;
}

.quick-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    text-decoration: none;
    transition: all 0.2s ease;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
}

.quick-link:last-child {
    margin-bottom: 0;
}

.quick-link:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.quick-link-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    font-size: 0.85rem;
}

.quick-link-icon.edit {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.quick-link-icon.list {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    color: white;
}

.quick-link-icon.add {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.quick-link-text {
    flex: 1;
}

.quick-link-title {
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.quick-link-desc {
    font-size: 0.75rem;
    color: #6b7280;
}

.quick-link-arrow {
    color: #9ca3af;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fas fa-user-circle me-2"></i>User Profile</h4>
                <p>Complete staff member profile and account details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ contextRoute('users.edit', $user->id) }}" class="btn btn-light">
                    <i class="fas fa-edit me-2"></i>Edit User
                </a>
                <a href="{{ contextRoute('users.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile Card Column -->
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="avatar-container">
                        @if($user->avatar)
                            <img src="{{ asset('assets/images/avatars/' . $user->avatar) }}"
                                 alt="{{ $user->name }}"
                                 class="avatar-large">
                        @else
                            <div class="avatar-placeholder">
                                <i class="fas fa-user fa-3x text-white opacity-75"></i>
                            </div>
                        @endif
                        <div class="status-indicator {{ $user->is_active ? 'active' : 'inactive' }}"></div>
                    </div>
                    <h4 class="profile-name">{{ $user->name }}</h4>
                    <p class="profile-email">{{ $user->email }}</p>

                    <div class="profile-badges">
                        @if($user->role)
                            <span class="profile-badge role">
                                <i class="fas fa-user-tag"></i>{{ ucfirst($user->role) }}
                            </span>
                        @endif
                        @if($user->is_active)
                            <span class="profile-badge active">
                                <i class="fas fa-check-circle"></i>Active
                            </span>
                        @else
                            <span class="profile-badge inactive">
                                <i class="fas fa-times-circle"></i>Inactive
                            </span>
                        @endif
                        @if($user->is_admin)
                            <span class="profile-badge admin">
                                <i class="fas fa-crown"></i>Admin
                            </span>
                        @endif
                    </div>
                </div>

                <div class="profile-body">
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('users.edit', $user->id) }}" class="action-btn primary">
                            <i class="fas fa-edit"></i>Edit Profile
                        </a>
                        <a href="{{ contextRoute('users.index') }}" class="action-btn secondary">
                            <i class="fas fa-arrow-left"></i>Back to Users
                        </a>
                    </div>

                    <div class="quick-links">
                        <a href="{{ contextRoute('users.edit', $user->id) }}" class="quick-link">
                            <div class="quick-link-icon edit">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="quick-link-text">
                                <div class="quick-link-title">Edit User</div>
                                <div class="quick-link-desc">Modify account details</div>
                            </div>
                            <i class="fas fa-chevron-right quick-link-arrow"></i>
                        </a>
                        <a href="{{ contextRoute('users.index') }}" class="quick-link">
                            <div class="quick-link-icon list">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="quick-link-text">
                                <div class="quick-link-title">All Users</div>
                                <div class="quick-link-desc">View user list</div>
                            </div>
                            <i class="fas fa-chevron-right quick-link-arrow"></i>
                        </a>
                        <a href="{{ contextRoute('users.create') }}" class="quick-link">
                            <div class="quick-link-icon add">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="quick-link-text">
                                <div class="quick-link-title">Add User</div>
                                <div class="quick-link-desc">Create new account</div>
                            </div>
                            <i class="fas fa-chevron-right quick-link-arrow"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Column -->
        <div class="col-lg-8">
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon days">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value">{{ $user->created_at->diffInDays() }}</div>
                    <div class="stat-label">Days Since Registration</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon update">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ $user->updated_at->diffInDays() }}</div>
                    <div class="stat-label">Days Since Last Update</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon status">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-value">{{ $user->is_active ? 'Active' : 'Inactive' }}</div>
                    <div class="stat-label">Current Status</div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <div class="header-icon personal">
                        <i class="fas fa-user"></i>
                    </div>
                    <h6>Personal Information</h6>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-id-badge"></i>Employee ID
                        </span>
                        <span class="info-value">
                            @if($user->employee_id)
                                <strong>{{ $user->employee_id }}</strong>
                            @else
                                <span class="not-set">Not Set</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-phone"></i>Phone Number
                        </span>
                        <span class="info-value">
                            @if($user->phone)
                                {{ $user->phone }}
                            @else
                                <span class="not-set">Not Provided</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-user-tag"></i>Role
                        </span>
                        <span class="info-value">
                            @if($user->role)
                                <span class="role-badge {{ $user->role }}">{{ ucfirst($user->role) }}</span>
                            @else
                                <span class="not-set">Not Set</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-hospital"></i>Clinics
                        </span>
                        <span class="info-value">
                            @php
                                $allDepartments = $user->departments->isNotEmpty()
                                    ? $user->departments
                                    : collect([$user->department])->filter();
                            @endphp
                            @if($allDepartments->isNotEmpty())
                                @foreach($allDepartments as $dept)
                                    @php
                                        $isPrimary = ($dept->pivot && $dept->pivot->is_primary) ||
                                                    (!$dept->pivot && $dept->id == $user->department_id);
                                    @endphp
                                    <span class="clinic-tag {{ $isPrimary ? 'primary' : '' }}">
                                        {{ $dept->name }}
                                        @if($isPrimary)
                                            <span class="primary-indicator">Primary</span>
                                        @endif
                                    </span>
                                @endforeach
                            @else
                                <span class="not-set">Not Assigned</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-stethoscope"></i>Specialisation
                        </span>
                        <span class="info-value">
                            @if($user->specialization)
                                {{ $user->specialization }}
                            @else
                                <span class="not-set">Not Specified</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-calendar"></i>Hire Date
                        </span>
                        <span class="info-value">
                            @if($user->hire_date)
                                {{ formatDate($user->hire_date) }}
                            @else
                                <span class="not-set">Not Set</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="info-card">
                <div class="info-card-header">
                    <div class="header-icon account">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h6>Account Information</h6>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-hashtag"></i>User ID
                        </span>
                        <span class="info-value">
                            <strong>#{{ $user->id }}</strong>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-calendar-plus"></i>Registration Date
                        </span>
                        <span class="info-value">{{ formatDateTime($user->created_at) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-edit"></i>Last Updated
                        </span>
                        <span class="info-value">{{ formatDateTime($user->updated_at) }}</span>
                    </div>
                    @if($user->last_login_at)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-sign-in-alt"></i>Last Login
                        </span>
                        <span class="info-value">{{ formatDateTime($user->last_login_at) }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-toggle-on"></i>Account Status
                        </span>
                        <span class="info-value">
                            <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                                <i class="fas fa-{{ $user->is_active ? 'check' : 'times' }}-circle me-1"></i>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-shield-alt"></i>Admin Privileges
                        </span>
                        <span class="info-value">
                            @if($user->is_admin)
                                <span class="status-badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                    <i class="fas fa-crown me-1"></i>Administrator
                                </span>
                            @else
                                <span class="status-badge inactive">
                                    <i class="fas fa-user me-1"></i>Regular User
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            @if($user->address || $user->city || $user->postal_code)
            <div class="info-card">
                <div class="info-card-header">
                    <div class="header-icon address">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <h6>Address Information</h6>
                </div>
                <div class="info-card-body">
                    @if($user->address)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-home"></i>Address Line 1
                        </span>
                        <span class="info-value">{{ $user->address }}</span>
                    </div>
                    @endif
                    @if($user->address_line_2)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-building"></i>Address Line 2
                        </span>
                        <span class="info-value">{{ $user->address_line_2 }}</span>
                    </div>
                    @endif
                    @if($user->city)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-city"></i>Town/City
                        </span>
                        <span class="info-value">{{ $user->city }}</span>
                    </div>
                    @endif
                    @if($user->state)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-map"></i>County
                        </span>
                        <span class="info-value">{{ $user->state }}</span>
                    </div>
                    @endif
                    @if($user->postal_code)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-mail-bulk"></i>Postcode
                        </span>
                        <span class="info-value">{{ strtoupper($user->postal_code) }}</span>
                    </div>
                    @endif
                    @if($user->country)
                    <div class="info-row">
                        <span class="info-label">
                            <i class="fas fa-flag"></i>Country
                        </span>
                        <span class="info-value">{{ $user->country }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Bio Section -->
            @if($user->bio)
            <div class="info-card">
                <div class="info-card-header">
                    <div class="header-icon bio">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h6>Bio / Description</h6>
                </div>
                <div class="bio-content">
                    {{ $user->bio }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
