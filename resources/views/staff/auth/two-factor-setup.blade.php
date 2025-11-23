@extends(auth()->check() && auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Two-Factor Authentication Setup')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Staff</a></li>
    <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}">Profile</a></li>
    <li class="breadcrumb-item active">2FA Setup</li>
@endsection

<!-- Status Update Modal pattern: place outside section to avoid z-index issues -->
<!-- Enable 2FA Modal -->
<div class="modal fade" id="enableModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Enable Two-Factor Authentication</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('staff.two-factor.enable') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">Choose your preferred 2FA method:</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="method" id="methodEmail" value="email" checked>
                        <label class="form-check-label" for="methodEmail">
                            <strong>Email</strong><br>
                            <small class="text-muted">Receive verification codes via email</small>
                        </label>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>After enabling 2FA, you'll receive recovery codes. Save them in a secure location.</small>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
                    <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success w-100 w-sm-auto">
                        <i class="fas fa-check me-2"></i>Enable 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div class="modal fade" id="disableModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Disable Two-Factor Authentication</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('staff.two-factor.disable') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Disabling 2FA will make your account less secure.
                    </div>
                    <p>Please enter your password to confirm:</p>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
                    <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger w-100 w-sm-auto">
                        <i class="fas fa-times me-2"></i>Disable 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Regenerate Recovery Codes Modal -->
<div class="modal fade" id="regenerateModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Regenerate Recovery Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('staff.two-factor.regenerate-codes') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will invalidate all existing recovery codes.
                    </div>
                    <p>Please enter your password to confirm:</p>
                    <div class="mb-3">
                        <label for="regenerate_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="regenerate_password" name="password" required>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-column flex-sm-row gap-2">
                    <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning w-100 w-sm-auto">
                        <i class="fas fa-sync me-2"></i>Regenerate Codes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Two-Factor Authentication</h1>
        <p class="page-subtitle text-muted">Enhance your account security with 2FA</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(isset($isRequired) && $isRequired)
        <div class="alert alert-info" role="alert">
            <h5 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i>2FA Required</h5>
            <p class="mb-0 text-break">Two-factor authentication is required by your administrator. You cannot disable it until this policy is changed.</p>
        </div>
    @endif
    
    @if(isset($isForcedSetup) && $isForcedSetup || (isset($isForced) && $isForced))
        <div class="alert alert-danger border-2" role="alert" style="border-color: #dc3545 !important;">
            <h5 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>2FA Setup Required - Navigation Locked</h5>
            <p class="mb-2"><strong>You must enable two-factor authentication to access the system.</strong></p>
            <p class="mb-0 small text-break">You cannot navigate away from this page until 2FA is enabled. All other navigation has been disabled.</p>
        </div>
    @endif
    
    @if(isset($isForced) && $isForced && !isset($isForcedSetup))
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Setup Required</h5>
            <p class="mb-0 text-break">You must enable two-factor authentication to access your account. Please complete the setup below.</p>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Recovery Codes Display -->
    @if(session('recovery_codes'))
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading"><i class="fas fa-key me-2"></i>Save Your Recovery Codes</h5>
            <p>Store these recovery codes in a safe place. You can use them to access your account if you lose access to your email.</p>
            <hr>
            <div class="row">
                @foreach(session('recovery_codes') as $code)
                    <div class="col-md-3 col-sm-6 mb-2">
                        <code class="d-block p-2 bg-white text-dark border">{{ $code }}</code>
                    </div>
                @endforeach
            </div>
            <hr>
            <button class="btn btn-sm btn-outline-dark" onclick="printRecoveryCodes()">
                <i class="fas fa-print me-1"></i>Print Codes
            </button>
            <button class="btn btn-sm btn-outline-dark" onclick="downloadRecoveryCodes()">
                <i class="fas fa-download me-1"></i>Download Codes
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Current Status -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>2FA Status</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-8 mb-3 mb-md-0">
                            <h6 class="mb-2">Current Status</h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @if($status['enabled'])
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle me-1"></i>Enabled
                                    </span>
                                    @if($status['confirmed'])
                                        <span class="badge bg-info fs-6">
                                            <i class="fas fa-shield-alt me-1"></i>Confirmed
                                        </span>
                                    @else
                                        <span class="badge bg-warning fs-6">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Pending Confirmation
                                        </span>
                                    @endif
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-times-circle me-1"></i>Disabled
                                    </span>
                                @endif
                            </div>
                            
                            @if($status['enabled'])
                                <p class="mt-2 mb-0">
                                    <strong>Method:</strong> {{ ucfirst($status['method']) }}<br>
                                    <strong>Recovery Codes:</strong> {{ $status['recovery_codes_count'] }} remaining<br>
                                    @if($status['last_used'])
                                        <strong>Last Used:</strong> {{ $status['last_used']->diffForHumans() }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 text-start text-md-end">
                            @if($status['enabled'])
                                @if(isset($isRequired) && $isRequired)
                                    <button class="btn btn-secondary w-100 w-md-auto" disabled title="2FA is required by administrator">
                                        <i class="fas fa-lock me-2"></i>Cannot Disable
                                    </button>
                                @else
                                    <button class="btn btn-danger w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#disableModal">
                                        <i class="fas fa-times me-2"></i>Disable 2FA
                                    </button>
                                @endif
                            @else
                                <button class="btn btn-success w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#enableModal">
                                    <i class="fas fa-shield-alt me-2"></i>Enable 2FA
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recovery Codes Management -->
            @if($status['enabled'])
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Recovery Codes</h5>
                    </div>
                    <div class="card-body">
                        <p>Recovery codes can be used to access your account if you lose access to your email. Each code can only be used once.</p>
                        <p class="mb-3">
                            <strong>Remaining Codes:</strong> 
                            <span class="badge bg-{{ $status['recovery_codes_count'] > 3 ? 'success' : ($status['recovery_codes_count'] > 0 ? 'warning' : 'danger') }}">
                                {{ $status['recovery_codes_count'] }}
                            </span>
                        </p>
                        
                        @if($status['recovery_codes_count'] <= 3)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You're running low on recovery codes. Consider regenerating them.
                            </div>
                        @endif
                        
                        <button class="btn btn-warning w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#regenerateModal">
                            <i class="fas fa-sync me-2"></i>Regenerate Recovery Codes
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Information -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>About 2FA</h5>
                </div>
                <div class="card-body">
                    <h6>What is Two-Factor Authentication?</h6>
                    <p class="small">Two-factor authentication adds an extra layer of security to your account by requiring a verification code in addition to your password.</p>
                    
                    <h6 class="mt-3">How it works:</h6>
                    <ol class="small">
                        <li>Enter your email and password</li>
                        <li>Receive a 6-digit code via email</li>
                        <li>Enter the code to complete login</li>
                    </ol>
                    
                    <h6 class="mt-3">Benefits:</h6>
                    <ul class="small">
                        <li>Enhanced account security</li>
                        <li>Protection against unauthorized access</li>
                        <li>Peace of mind</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Mobile Responsive Styles */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
            max-width: calc(100% - 1rem);
        }
        
        .modal-content {
            border-radius: 0.5rem;
        }
        
        .modal-header {
            padding: 1rem;
        }
        
        .modal-body {
            padding: 1rem;
        }
        
        .modal-footer {
            padding: 1rem;
        }
        
        .alert {
            padding: 0.75rem;
            font-size: 0.875rem;
        }
        
        .alert-heading {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .page-title h1 {
            font-size: 1.5rem;
        }
        
        .page-subtitle {
            font-size: 0.875rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        
        .col-lg-8, .col-lg-4 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('scripts')
<script>
function printRecoveryCodes() {
    window.print();
}

function downloadRecoveryCodes() {
    const codes = @json(session('recovery_codes', []));
    const text = 'Two-Factor Authentication Recovery Codes\n\n' + 
                 'Save these codes in a secure location.\n' +
                 'Each code can only be used once.\n\n' +
                 codes.join('\n');
    
    const blob = new Blob([text], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = '2fa-recovery-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

// Ensure Bootstrap modals are attached to <body> to avoid parent transforms/overflow breaking layout
document.addEventListener('DOMContentLoaded', function () {
    const modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(function (el) {
        el.addEventListener('show.bs.modal', function () {
            if (this.parentElement !== document.body) {
                document.body.appendChild(this);
            }
        });
    });
    
    // Prevent navigation away from 2FA setup page when forced setup is active
    @if(isset($isForcedSetup) && $isForcedSetup)
    const isForcedSetup = true;
    const setupRoute = '{{ route("staff.two-factor.setup") }}';
    
    // Prevent clicks on all navigation links (except logout)
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && target.href) {
            // Allow logout
            if (target.closest('form[action*="logout"]') || target.textContent.includes('Logout')) {
                return true;
            }
            
            // Allow 2FA setup/verify routes
            if (target.href.includes('two-factor') || target.href.includes('2fa')) {
                return true;
            }
            
            // Prevent all other navigation
            e.preventDefault();
            e.stopPropagation();
            
            // Show warning
            alert('Navigation is locked. Please complete 2FA setup to access the system.');
            
            // Redirect back to setup page if somehow navigated away
            if (!window.location.href.includes('two-factor/setup')) {
                window.location.href = setupRoute;
            }
            
            return false;
        }
    }, true);
    
    // Prevent browser back/forward navigation
    window.addEventListener('popstate', function(e) {
        if (!window.location.href.includes('two-factor/setup')) {
            window.history.pushState(null, null, setupRoute);
            alert('Navigation is locked. Please complete 2FA setup to access the system.');
        }
    });
    
    // Push current state to prevent back navigation
    window.history.pushState(null, null, setupRoute);
    
    // Intercept form submissions that would navigate away (except 2FA-related forms)
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.tagName === 'FORM') {
            const formAction = form.getAttribute('action') || '';
            // Allow 2FA-related form submissions
            if (formAction.includes('two-factor') || formAction.includes('2fa') || 
                formAction.includes('logout') || formAction.includes('enable') || 
                formAction.includes('disable') || formAction.includes('regenerate')) {
                return true;
            }
            
            // Check if form is inside a modal (which is allowed for 2FA setup)
            if (form.closest('.modal')) {
                return true;
            }
            
            // Prevent other form submissions that navigate away
            e.preventDefault();
            alert('Please complete 2FA setup before performing other actions.');
            return false;
        }
    }, true);
    
    // Show a message in console for debugging
    console.log('2FA Setup is locked. Navigation is disabled until 2FA is enabled.');
    @endif
});
</script>
@endsection

