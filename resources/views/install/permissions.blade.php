@extends('install.layout')

@section('title', 'Permissions Check - Hospital Management System Installation')

@section('content')
<div class="text-center mb-4">
    <i class="fas fa-lock-open fa-3x text-primary mb-3"></i>
    <h2 class="step-title">File Permissions Check</h2>
    <p class="text-muted">
        Verifying that your server has the correct file permissions for Hospital Management System
    </p>
</div>

@if($allPassed)
    <div class="alert alert-success">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Perfect!</strong> All directories have the correct permissions. You can proceed to the next step.
    </div>
@else
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Permission Issues Detected!</strong> Please fix the directory permissions below before proceeding.
    </div>
@endif

<div class="permissions-container">
    <h5 class="mb-3">
        <i class="fas fa-folder-open me-2"></i>
        Directory Permissions
    </h5>
    
    @foreach($permissions as $permission)
        <div class="permission-item">
            <div class="permission-info">
                <div class="requirement-name">{{ $permission['name'] }}</div>
                <small class="text-muted">
                    Path: {{ $permission['path'] }} | Current: {{ $permission['permission'] }}
                </small>
            </div>
            <div class="requirement-status">
                <span class="status-badge {{ $permission['status'] ? 'success' : 'danger' }}">
                    <i class="fas fa-{{ $permission['status'] ? 'check' : 'times' }} me-1"></i>
                    {{ $permission['status'] ? 'Writable' : 'Not Writable' }}
                </span>
            </div>
        </div>
    @endforeach
</div>

@if(!$allPassed)
    <div class="mt-4">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>How to fix permission issues:</strong>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-terminal me-2"></i>
                            Via Command Line (SSH)
                        </h6>
                        <div class="permission-commands">
                            <p class="mb-2">Run these commands:</p>
                            <div class="bg-dark text-white p-2 rounded mb-2" style="font-family: monospace; font-size: 0.85rem;">
                                chmod -R 775 storage/<br>
                                chmod -R 775 bootstrap/cache/<br>
                                chmod -R 775 public/uploads/
                            </div>
                            <p class="mb-0 small text-muted">Make sure to run from your project root directory</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-mouse-pointer me-2"></i>
                            Via Control Panel (cPanel)
                        </h6>
                        <ol class="permission-steps small">
                            <li>Open File Manager in cPanel</li>
                            <li>Navigate to your project directory</li>
                            <li>Right-click on each directory above</li>
                            <li>Select "Change Permissions"</li>
                            <li>Set permissions to 755 or 775</li>
                            <li>Check "Recurse into subdirectories"</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <button onclick="window.location.reload()" class="btn btn-outline-primary">
                <i class="fas fa-sync me-2"></i>
                Re-check Permissions
            </button>
        </div>
    </div>
@endif

<div class="mt-4">
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Security Note:</strong> These directories need write permissions for the application to function properly. 
        The installer will create necessary subdirectories automatically if they don't exist.
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <h6>What these directories are used for:</h6>
            <ul class="list-unstyled text-muted small">
                <li><i class="fas fa-folder me-2"></i> <strong>storage/app</strong> - Application files and uploads</li>
                <li><i class="fas fa-folder me-2"></i> <strong>storage/framework</strong> - Framework cache and sessions</li>
                <li><i class="fas fa-folder me-2"></i> <strong>storage/logs</strong> - Application log files</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="list-unstyled text-muted small" style="margin-top: 1.8rem;">
                <li><i class="fas fa-folder me-2"></i> <strong>bootstrap/cache</strong> - Application cache files</li>
                <li><i class="fas fa-folder me-2"></i> <strong>public/uploads</strong> - User uploaded files</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-info-circle me-1"></i>
        Step 3 of 7 - File Permissions Validation
    </small>
</div>
<div>
    <a href="{{ route('install.step', 'requirements') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-2"></i>
        Back
    </a>
    @if($allPassed)
        <a href="{{ route('install.step', 'environment') }}" class="btn btn-primary">
            <i class="fas fa-arrow-right me-2"></i>
            Continue to Environment
        </a>
    @else
        <button class="btn btn-primary" disabled>
            <i class="fas fa-times me-2"></i>
            Fix Permissions First
        </button>
    @endif
</div>
@endsection

@push('styles')
<style>
    .permissions-container {
        background: #f8fafc;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .permission-commands {
        font-size: 0.9rem;
    }
    
    .permission-steps {
        margin-bottom: 0;
        padding-left: 1.2rem;
    }
    
    .permission-steps li {
        margin-bottom: 0.3rem;
    }
    
    .permission-item:hover {
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush
