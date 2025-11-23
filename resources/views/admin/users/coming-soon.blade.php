@extends('admin.layouts.app')

@section('title', 'User Management - Coming Soon')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">User Management</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(248,249,250,0.9)); backdrop-filter: blur(10px);">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="coming-soon-icon mb-3">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h1 class="display-4 fw-bold text-primary mb-3">Coming Soon</h1>
                        <h3 class="text-muted mb-4">User Management System</h3>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h5 class="mt-3">Role-Based Access</h5>
                                <p class="text-muted small">Manage user roles and permissions for different hospital staff</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="mt-3">Staff Management</h5>
                                <p class="text-muted small">Add, edit, and manage hospital staff members</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <h5 class="mt-3">Access Control</h5>
                                <p class="text-muted small">Control what each user can access in the system</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Under Development:</strong> This feature is currently being developed and will be available soon.
                    </div>
                    
                    <div class="planned-features mt-4">
                        <h5 class="mb-3">Planned Features:</h5>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Admin User Management</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Doctor User Profiles</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Nurse User Profiles</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Receptionist Access</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Role-Based Permissions</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>User Activity Logs</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Password Management</li>
                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Two-Factor Authentication</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ contextRoute('dashboard') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.coming-soon-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    animation: pulse 2s infinite;
}

.coming-soon-icon i {
    font-size: 48px;
    color: white;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.feature-card {
    padding: 20px;
    border-radius: 15px;
    background: rgba(255,255,255,0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.feature-icon i {
    font-size: 24px;
    color: white;
}

.planned-features {
    background: rgba(248,249,250,0.8);
    border-radius: 15px;
    padding: 20px;
    backdrop-filter: blur(5px);
}
</style>
@endsection
