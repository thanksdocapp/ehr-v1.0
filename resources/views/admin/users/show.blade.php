@extends('admin.layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@push('styles')
<style>
.profile-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.profile-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px 12px 0 0;
    text-align: center;
}

.profile-body {
    padding: 2rem;
}

.stats-card {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.info-section {
    background: #f8f9fc;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e3e6f0;
}

.info-section h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
    font-weight: 600;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e3e6f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #5a5c69;
    flex: 1;
}

.info-value {
    color: #858796;
    flex: 2;
    text-align: right;
}

.avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.3);
    margin-bottom: 1rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.quick-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-user me-2 text-primary"></i>{{ $user->name }}</h1>
        <p class="page-subtitle text-muted">Complete staff member profile and details</p>
    </div>

    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-header">
                    @if($user->avatar)
                        <img src="{{ asset('storage/avatars/' . $user->avatar) }}" 
                             alt="User Avatar" 
                             class="avatar-large">
                    @else
                        <div class="avatar-large bg-secondary d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="mb-3 opacity-75">{{ $user->email }}</p>
                    
                    @if($user->is_active)
                        <span class="status-badge bg-success text-white">Active</span>
                    @else
                        <span class="status-badge bg-secondary text-white">Inactive</span>
                    @endif
                    
                    @if($user->is_admin)
                        <span class="status-badge bg-warning text-dark ms-2">Admin</span>
                    @endif
                </div>
                
                <div class="profile-body">
                    <div class="quick-actions">
                        <a href="{{ contextRoute('users.edit', $user->id) }}" class="btn btn-primary flex-fill">
                            <i class="fas fa-edit me-2"></i>Edit User
                        </a>
                        <a href="{{ contextRoute('users.index') }}" class="btn btn-secondary flex-fill">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="info-section">
                <h6><i class="fas fa-user me-2"></i>Personal Information</h6>
                <div class="info-row">
                    <span class="info-label">Employee ID:</span>
                    <span class="info-value">{{ $user->employee_id ?? 'Not Set' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone Number:</span>
                    <span class="info-value">{{ $user->phone ?? 'Not Provided' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Role:</span>
                    <span class="info-value">
                        <span class="badge bg-primary">{{ ucfirst($user->role ?? 'Not Set') }}</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Clinics:</span>
                    <span class="info-value">
                        @php
                            $allDepartments = $user->departments->isNotEmpty() 
                                ? $user->departments 
                                : collect([$user->department])->filter();
                        @endphp
                        @if($allDepartments->isNotEmpty())
                            @foreach($allDepartments as $dept)
                                <span class="badge bg-primary me-1 mb-1">
                                    {{ $dept->name }}
                                    @if($dept->pivot && $dept->pivot->is_primary)
                                        <span class="badge bg-warning text-dark ms-1">Primary</span>
                                    @elseif(!$dept->pivot && $dept->id == $user->department_id)
                                        <span class="badge bg-warning text-dark ms-1">Primary</span>
                                    @endif
                                </span>
                            @endforeach
                        @else
                            <span class="text-danger">Not assigned</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Specialisation:</span>
                    <span class="info-value">{{ $user->specialization ?? 'Not Specified' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Hire Date:</span>
                    <span class="info-value">{{ $user->hire_date ? formatDate($user->hire_date) : 'Not Set' }}</span>
                </div>
            </div>

            <!-- Account Information -->
            <div class="info-section">
                <h6><i class="fas fa-cog me-2"></i>Account Information</h6>
                <div class="info-row">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">#{{ $user->id }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Registration Date:</span>
                    <span class="info-value">{{ formatDateTime($user->created_at) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Last Updated:</span>
                    <span class="info-value">{{ formatDateTime($user->updated_at) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account Status:</span>
                    <span class="info-value">
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Admin Privileges:</span>
                    <span class="info-value">
                        @if($user->is_admin)
                            <span class="badge bg-warning text-dark">Administrator</span>
                        @else
                            <span class="badge bg-light text-dark">Regular User</span>
                        @endif
                    </span>
                </div>
            </div>

            <!-- Bio Section -->
            @if($user->bio)
            <div class="info-section">
                <h6><i class="fas fa-info-circle me-2"></i>Bio/Description</h6>
                <p class="mb-0 text-muted">{{ $user->bio }}</p>
            </div>
            @endif

            <!-- Activity Statistics -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="text-primary mb-2">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                        <h5 class="mb-1">{{ $user->created_at->diffInDays() }}</h5>
                        <small class="text-muted">Days Since Registration</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="text-success mb-2">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <h5 class="mb-1">{{ $user->updated_at->diffInDays() }}</h5>
                        <small class="text-muted">Days Since Last Update</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="text-info mb-2">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                        <h5 class="mb-1">{{ $user->is_active ? 'Active' : 'Inactive' }}</h5>
                        <small class="text-muted">Current Status</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Add any user-specific JavaScript here
$(document).ready(function() {
    // Example: Add tooltip to badges
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush
