@extends('admin.layouts.app')

@section('title', 'Two-Factor Authentication Setup')

<!-- Status Update Modal pattern: place outside section to avoid z-index issues -->
<!-- Enable 2FA Modal -->
<div class="modal fade" id="enableModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Enable Two-Factor Authentication</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.two-factor.enable') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Choose your preferred 2FA method:</p>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="method" id="methodEmail" value="email" checked>
                        <label class="form-check-label" for="methodEmail">
                            <strong>Email</strong><br>
                            <small class="text-muted">Receive verification codes via email</small>
                        </label>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>After enabling 2FA, you'll receive recovery codes. Save them in a secure location.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Enable 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Disable 2FA Modal -->
<div class="modal fade" id="disableModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Disable Two-Factor Authentication</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.two-factor.disable') }}" method="POST">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Disable 2FA
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Regenerate Recovery Codes Modal -->
<div class="modal fade" id="regenerateModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Regenerate Recovery Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.two-factor.regenerate-codes') }}" method="POST">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
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
            <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>2FA Required</h5>
            <p class="mb-0">Two-factor authentication is required by your administrator. You cannot disable it until this policy is changed.</p>
        </div>
    @endif
    
    @if(isset($isForced) && $isForced)
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Setup Required</h5>
            <p class="mb-0">You must enable two-factor authentication to access your account. Please complete the setup below.</p>
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
                        <div class="col-md-8">
                            <h6 class="mb-2">Current Status</h6>
                            @if($status['enabled'])
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle me-1"></i>Enabled
                                </span>
                                @if($status['confirmed'])
                                    <span class="badge bg-info fs-6 ms-2">
                                        <i class="fas fa-shield-alt me-1"></i>Confirmed
                                    </span>
                                @else
                                    <span class="badge bg-warning fs-6 ms-2">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Pending Confirmation
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times-circle me-1"></i>Disabled
                                </span>
                            @endif
                            
                            @if($status['enabled'])
                                <p class="mt-3 mb-0">
                                    <strong>Method:</strong> {{ ucfirst($status['method']) }}<br>
                                    <strong>Recovery Codes:</strong> {{ $status['recovery_codes_count'] }} remaining<br>
                                    @if($status['last_used'])
                                        <strong>Last Used:</strong> {{ $status['last_used']->diffForHumans() }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            @if($status['enabled'])
                                @if(isset($isRequired) && $isRequired)
                                    <button class="btn btn-secondary" disabled title="2FA is required by administrator">
                                        <i class="fas fa-lock me-2"></i>Cannot Disable
                                    </button>
                                @else
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#disableModal">
                                        <i class="fas fa-times me-2"></i>Disable 2FA
                                    </button>
                                @endif
                            @else
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#enableModal">
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
                        
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#regenerateModal">
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
});
</script>
@endsection

