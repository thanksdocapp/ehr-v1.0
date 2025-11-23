@extends('install.layout')

@section('title', 'System Requirements - ' . $productInfo['name'])

@section('content')
<div class="install-step" data-step="requirements">
    <div class="step-header text-center">
        <div class="step-icon">
            <i class="fas fa-server text-info"></i>
        </div>
        <h2>System Requirements</h2>
        <p class="step-description">Checking your server compatibility and auto-fixing permissions</p>
    </div>

    <!-- Overall Status -->
    <div class="status-overview mb-4">
        @if($allPassed)
            <div class="alert alert-success d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">✅ Server Ready!</h5>
                    <p class="mb-0">Your server meets all requirements. Permissions have been automatically configured.</p>
                </div>
            </div>
        @else
            <div class="alert alert-warning d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div>
                    <h5 class="mb-1">⚠️ Action Required</h5>
                    <p class="mb-0">Some requirements need attention. Please contact your hosting provider if issues persist.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Requirements Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-cogs me-2"></i>
                PHP Extensions & Requirements
            </h5>
            <button class="btn btn-sm btn-outline-primary" onclick="recheckRequirements()">
                <i class="fas fa-sync-alt"></i> Recheck
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="requirementsTable">
                    <thead>
                        <tr>
                            <th>Requirement</th>
                            <th>Required</th>
                            <th>Current</th>
                            <th>Status</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requirements as $requirement)
                        <tr class="{{ !$requirement['status'] && ($requirement['critical'] ?? true) ? 'table-danger' : (!$requirement['status'] ? 'table-warning' : '') }}">
                            <td>
                                <div>
                                    <strong>{{ $requirement['name'] }}</strong>
                                    @if(isset($requirement['description']))
                                        <br><small class="text-muted">{{ $requirement['description'] }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $requirement['required'] }}</td>
                            <td>
                                <span class="{{ $requirement['status'] ? 'text-success' : 'text-danger' }}">
                                    {{ $requirement['current'] }}
                                </span>
                            </td>
                            <td>
                                @if($requirement['status'])
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Passed
                                    </span>
                                @else
                                    <span class="badge bg-{{ ($requirement['critical'] ?? true) ? 'danger' : 'warning' }}">
                                        <i class="fas fa-{{ ($requirement['critical'] ?? true) ? 'times' : 'exclamation' }} me-1"></i>
                                        {{ ($requirement['critical'] ?? true) ? 'Failed' : 'Warning' }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($requirement['critical'] ?? true)
                                    <span class="badge bg-danger">Critical</span>
                                @else
                                    <span class="badge bg-warning">Optional</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- File Permissions -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-folder-lock me-2"></i>
                File Permissions
            </h5>
            <span class="badge bg-info">Auto-Fixed</span>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($permissions as $permission)
                <div class="col-md-6 mb-3">
                    <div class="permission-item d-flex justify-content-between align-items-center p-3 border rounded">
                        <div>
                            <strong>{{ $permission['name'] }}</strong>
                            <br>
                            <small class="text-muted">{{ $permission['permission'] }}</small>
                            @if($permission['auto_fixed'])
                                <br><small class="text-success"><i class="fas fa-wrench me-1"></i>Auto-fixed</small>
                            @endif
                        </div>
                        <div>
                            @if($permission['status'])
                                <span class="badge bg-success">
                                    <i class="fas fa-check"></i>
                                </span>
                            @else
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Server Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Server Information
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>PHP Version:</strong><br>
                    <span class="badge bg-primary">{{ PHP_VERSION }}</span>
                </div>
                <div class="col-md-4">
                    <strong>Server Software:</strong><br>
                    <span class="badge bg-secondary">{{ $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' }}</span>
                </div>
                <div class="col-md-4">
                    <strong>Max Execution Time:</strong><br>
                    <span class="badge bg-info">{{ ini_get('max_execution_time') }}s</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$allPassed)
    <div class="mt-4">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>❌ Critical Extensions Missing!</strong>
            <p class="mb-3">Your server is missing critical PHP extensions required for Hospital Management System to function properly.</p>
            
            <div class="row">
                <div class="col-md-6">
                    <h6><i class="fas fa-server me-2"></i>For Shared Hosting (cPanel/Plesk):</h6>
                    <ol class="small">
                        <li>Log into your hosting control panel</li>
                        <li>Find "PHP Extensions" or "PHP Modules"</li>
                        <li>Enable the missing extensions listed above</li>
                        <li>Save changes and reload this page</li>
                    </ol>
                </div>
                <div class="col-md-6">
                    <h6><i class="fas fa-terminal me-2"></i>For VPS/Dedicated Server:</h6>
                    <div class="small">
                        <strong>Ubuntu/Debian:</strong><br>
                        <code class="bg-dark text-light p-2 d-block mb-2">sudo apt update && sudo apt install php8.1-curl php8.1-mbstring php8.1-mysql php8.1-xml php8.1-bcmath php8.1-gd php8.1-zip</code>
                        
                        <strong>CentOS/RHEL:</strong><br>
                        <code class="bg-dark text-light p-2 d-block">sudo yum install php-curl php-mbstring php-mysqlnd php-xml php-bcmath php-gd php-zip</code>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info">
            <i class="fas fa-lightbulb me-2"></i>
            <strong>💡 Troubleshooting Tips:</strong>
            <ul class="mt-2 mb-0 small">
                <li><strong>Different PHP versions:</strong> Web server might use different PHP than command line</li>
                        <li><strong>Contact hosting support:</strong> Mention you need PHP {{ $productInfo['min_php_version'] }}+ with hospital management application extensions</li>
                <li><strong>Extension list to request:</strong> OpenSSL, PDO MySQL, BCMath, cURL, Mbstring, XML, Tokenizer, JSON, Fileinfo</li>
                <li><strong>Memory limit:</strong> Request at least 256MB PHP memory limit</li>
            </ul>
        </div>
        
        <div class="text-center">
            <button onclick="window.location.reload()" class="btn btn-primary">
                <i class="fas fa-sync me-2"></i>
                Re-check Requirements After Changes
            </button>
            <a href="https://www.php.net/manual/en/extensions.php" target="_blank" class="btn btn-outline-info ms-2">
                <i class="fas fa-external-link-alt me-2"></i>
                PHP Extensions Documentation
            </a>
        </div>
    </div>
@endif

<div class="mt-4">
    <div class="row">
        <div class="col-md-6">
            <h6>Recommended Server Specifications:</h6>
            <ul class="list-unstyled text-muted small">
                <li><i class="fas fa-microchip me-2"></i> 2+ CPU cores</li>
                <li><i class="fas fa-memory me-2"></i> 4GB+ RAM</li>
                <li><i class="fas fa-hdd me-2"></i> 10GB+ storage space</li>
                <li><i class="fas fa-network-wired me-2"></i> Stable internet connection</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h6>Recommended Environment:</h6>
            <ul class="list-unstyled text-muted small">
                <li><i class="fas fa-shield-alt me-2"></i> SSL Certificate (HTTPS)</li>
                <li><i class="fas fa-database me-2"></i> MySQL 8.0+ or MariaDB 10.3+</li>
                <li><i class="fas fa-server me-2"></i> Nginx or Apache web server</li>
                <li><i class="fas fa-cloud me-2"></i> Regular backup solution</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-info-circle me-1"></i>
        Step 3 of 5 - System Requirements & Permissions
    </small>
</div>
<div>
    <a href="{{ route('install.step', 'license') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-2"></i>
        Back to License
    </a>
    @if($allPassed)
        <a href="{{ route('install.step', 'database') }}" class="btn btn-primary">
            <i class="fas fa-arrow-right me-2"></i>
            Continue to Database
        </a>
    @else
        <button class="btn btn-primary" disabled>
            <i class="fas fa-times me-2"></i>
            Fix Requirements First
        </button>
    @endif
</div>
@endsection

@push('scripts')
<script>
function recheckRequirements() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
    button.disabled = true;
    
    // Reload the page to recheck
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Auto-proceed if all requirements are met
@if($allPassed)
setTimeout(() => {
    if (confirm('All requirements are met! Proceed to database configuration?')) {
        window.location.href = '{{ route("install.step", "database") }}';
    }
}, 3000);
@endif
</script>
@endpush

@push('styles')
<style>
    .requirements-container {
        background: #f8fafc;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .requirement-info small {
        font-size: 0.8rem;
    }
    
    .requirement-item:hover {
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }
</style>
@endpush
