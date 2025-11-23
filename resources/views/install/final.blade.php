@extends('install.layout')

@section('title', 'Installation Complete - ThanksDoc EHR')

@section('content')
<div class="text-center mb-4">
    <div class="success-animation">
        <i class="fas fa-check-circle fa-5x text-success mb-3 animated-check"></i>
    </div>
    <h2 class="step-title text-success">Installation Complete!</h2>
    <p class="text-muted fs-5">
        Congratulations! Your ThanksDoc EHR has been successfully installed and configured.
    </p>
</div>

<div class="installation-summary mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="summary-card">
                <div class="card-header">
                    <h5><i class="fas fa-list-check me-2"></i>Installation Summary</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>System Requirements:</strong> Verified
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>File Permissions:</strong> Configured
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Environment:</strong> Set up
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Database:</strong> Connected & Migrated
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <strong>Admin Account:</strong> Created
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="summary-card">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <strong>Application Name:</strong>
                        <span id="appName">ThanksDoc EHR</span>
                    </div>
                    <div class="info-item">
                        <strong>Environment:</strong>
                        <span id="appEnvironment">Production</span>
                    </div>
                    <div class="info-item">
                        <strong>Database:</strong>
                        <span id="dbConnection">MySQL</span>
                    </div>
                    <div class="info-item">
                        <strong>Installation Date:</strong>
                        <span id="installDate">{{ date('F j, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Version:</strong>
                        <span class="badge bg-primary">v1.0.0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="next-steps mt-4">
    <div class="alert alert-success">
        <h5><i class="fas fa-rocket me-2"></i>What's Next?</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="mb-0">
                    <li>Access the admin panel to configure your system</li>
                    <li>Set up patient management and medical records</li>
                    <li>Configure email and SMS notifications</li>
                    <li>Customize the healthcare portal appearance</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="mb-0">
                    <li>Create doctor and staff accounts</li>
                    <li>Set up GDPR and medical compliance settings</li>
                    <li>Configure medical workflows and processes</li>
                    <li>Review and test all healthcare functionality</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="security-recommendations mt-4">
    <div class="alert alert-warning">
        <h5><i class="fas fa-shield-alt me-2"></i>Important Security Recommendations</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="mb-0">
                    <li><strong>Remove Installation Files:</strong> Delete the installation directory for security</li>
                    <li><strong>Update Passwords:</strong> Change default passwords immediately</li>
                    <li><strong>SSL Certificate:</strong> Install and configure SSL/TLS encryption</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="mb-0">
                    <li><strong>Backup Strategy:</strong> Set up regular automated backups</li>
                    <li><strong>Security Updates:</strong> Keep the system updated with latest patches</li>
                    <li><strong>Access Logs:</strong> Monitor and review access logs regularly</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="quick-access mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="access-card">
                <div class="card-icon">
                    <i class="fas fa-user-shield fa-2x text-primary"></i>
                </div>
                <h6>Admin Panel</h6>
                <p class="text-muted small">Access the administrative dashboard to manage your hospital system</p>
                <a href="{{ route('admin.login') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-sign-in-alt me-1"></i> Admin Login
                </a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="access-card">
                <div class="card-icon">
                    <i class="fas fa-globe fa-2x text-info"></i>
                </div>
                <h6>Patient Portal</h6>
                <p class="text-muted small">View the patient-facing portal and medical services</p>
                <a href="{{ route('homepage') }}" class="btn btn-info btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> View Site
                </a>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="access-card">
                <div class="card-icon">
                    <i class="fas fa-book fa-2x text-success"></i>
                </div>
                <h6>Documentation</h6>
                <p class="text-muted small">Access comprehensive guides and API documentation</p>
                <a href="#" class="btn btn-success btn-sm" onclick="showDocumentation()">
                    <i class="fas fa-book-open me-1"></i> View Docs
                </a>
            </div>
        </div>
    </div>
</div>

<div class="support-info mt-4">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-life-ring me-2"></i>Support & Resources</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="support-item">
                        <i class="fas fa-envelope text-primary mb-2"></i>
                        <h6>Email Support</h6>
                        <small class="text-muted">support@newwaves.com</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="support-item">
                        <i class="fas fa-phone text-info mb-2"></i>
                        <h6>Phone Support</h6>
                        <small class="text-muted">+1 (555) 123-4567</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="support-item">
                        <i class="fas fa-comments text-success mb-2"></i>
                        <h6>Live Chat</h6>
                        <small class="text-muted">24/7 Available</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="support-item">
                        <i class="fas fa-question-circle text-warning mb-2"></i>
                        <h6>Knowledge Base</h6>
                        <small class="text-muted">Help Articles & FAQs</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Installation Cleanup Modal -->
<div class="modal fade" id="cleanupModal" tabindex="-1" aria-labelledby="cleanupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cleanupModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Clean Up Installation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Security Notice:</strong> For security reasons, we recommend removing the installation files after successful setup.
                </div>
                <p>This will:</p>
                <ul>
                    <li>Remove installation views and routes</li>
                    <li>Delete temporary installation files</li>
                    <li>Prevent unauthorized access to installation</li>
                    <li>Improve system security</li>
                </ul>
                <p><strong>Note:</strong> This action cannot be undone. Make sure your installation is working correctly before proceeding.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="cleanupInstallation()">
                    <i class="fas fa-trash-alt me-1"></i> Clean Up Now
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-check-circle me-1"></i>
        Installation Completed Successfully - ThanksDoc EHR v1.0.0
    </small>
</div>
<div>
    <button type="button" class="btn btn-outline-danger me-2" data-bs-toggle="modal" data-bs-target="#cleanupModal">
        <i class="fas fa-trash-alt me-2"></i>
        Clean Up Installation
    </button>
    <a href="{{ route('admin.login') }}" class="btn btn-success">
        <i class="fas fa-sign-in-alt me-2"></i>
        Access Admin Panel
    </a>
</div>
@endsection

@push('scripts')
<script>
    function showDocumentation() {
        showAlert('info', 'Documentation will be available in the admin panel under Help & Resources section.');
    }
    
    function cleanupInstallation() {
        // Show loading
        showLoading();
        document.querySelector('#loadingOverlay .loading-content h5').textContent = 'Cleaning Up Installation...';
        document.querySelector('#loadingOverlay .loading-content p').textContent = 'Removing installation files and securing the system.';
        
        fetch("{{ route('install.cleanup') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showAlert('success', 'Installation files cleaned up successfully! Redirecting to admin panel...');
                setTimeout(function() {
                    window.location.href = "{{ route('admin.login') }}";
                }, 3000);
            } else {
                showAlert('danger', 'Cleanup failed: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            showAlert('danger', 'Cleanup failed: ' + error.message);
        });
        
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('cleanupModal'));
        modal.hide();
    }
    
    // Celebration animation
    document.addEventListener('DOMContentLoaded', function() {
        // Add some celebratory effects
        setTimeout(function() {
            const checkIcon = document.querySelector('.animated-check');
            checkIcon.style.transform = 'scale(1.1)';
            checkIcon.style.transition = 'transform 0.3s ease';
            
            setTimeout(function() {
                checkIcon.style.transform = 'scale(1)';
            }, 300);
        }, 500);
        
        // Auto-populate system info if available
        try {
            const appName = localStorage.getItem('install_app_name');
            const appEnv = localStorage.getItem('install_app_env');
            
            if (appName) {
                document.getElementById('appName').textContent = appName;
            }
            if (appEnv) {
                document.getElementById('appEnvironment').textContent = appEnv.charAt(0).toUpperCase() + appEnv.slice(1);
            }
            
            // Clear installation data from localStorage
            localStorage.removeItem('install_app_name');
            localStorage.removeItem('install_app_env');
        } catch (e) {
            // Ignore localStorage errors
        }
    });
</script>
@endpush

@push('styles')
<style>
    .success-animation {
        margin: 2rem 0;
    }
    
    .animated-check {
        animation: bounceIn 1s ease-out;
    }
    
    @keyframes bounceIn {
        0% {
            opacity: 0;
            transform: scale(0.3);
        }
        50% {
            opacity: 1;
            transform: scale(1.05);
        }
        70% {
            transform: scale(0.9);
        }
        100% {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .summary-card, .access-card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1.5rem;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
        height: 100%;
    }
    
    .summary-card .card-header {
        background: none;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    
    .summary-card .card-body {
        padding: 0;
    }
    
    .access-card {
        text-align: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .access-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .card-icon {
        margin-bottom: 1rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .support-item {
        text-align: center;
        padding: 1rem;
    }
    
    .support-item i {
        font-size: 1.5rem;
        display: block;
    }
    
    .alert h5 {
        margin-bottom: 1rem;
    }
    
    .alert ul {
        margin-bottom: 0;
        padding-left: 1.25rem;
    }
    
    .alert li {
        margin-bottom: 0.5rem;
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .list-unstyled li {
        padding: 0.25rem 0;
    }
    
    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .card {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        border-radius: 10px 10px 0 0 !important;
    }
    
    @media (max-width: 768px) {
        .access-card {
            margin-bottom: 1rem;
        }
        
        .info-item {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }
        
        .support-item {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush