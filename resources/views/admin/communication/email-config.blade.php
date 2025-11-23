@extends('admin.layouts.app')

@section('title', 'Email Configuration')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">Email Configuration</li>
@endsection

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.form-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
}

.form-section-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
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

.btn-info {
    background: linear-gradient(135deg, #36b9cc 0%, #1cc88a 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(54, 185, 204, 0.3);
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(54, 185, 204, 0.4);
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}

.status-indicator {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-complete {
    background-color: #d4edda;
    color: #155724;
}

.status-incomplete {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-envelope me-2 text-primary"></i>Email Configuration</h1>
        <p class="page-subtitle text-muted">Configure SMTP settings for sending emails</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="emailConfigForm" action="{{ contextRoute('email-config') }}" method="POST">
                @csrf
                
                <!-- SMTP Server Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-server me-2"></i>SMTP Server Settings</h4>
                        <small class="opacity-75">Configure your email server connection</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_host" class="form-label">
                                        <i class="fas fa-globe me-1"></i>SMTP Host *
                                    </label>
                                    <input type="text" class="form-control @error('smtp_host') is-invalid @enderror" 
                                           id="smtp_host" name="smtp_host" 
                                           value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" 
                                           placeholder="smtp.gmail.com" required>
                                    <div class="form-help">Your email provider's SMTP server address</div>
                                    @error('smtp_host')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_port" class="form-label">
                                        <i class="fas fa-plug me-1"></i>SMTP Port *
                                    </label>
                                    <input type="number" class="form-control @error('smtp_port') is-invalid @enderror" 
                                           id="smtp_port" name="smtp_port" 
                                           value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}" 
                                           placeholder="587" required>
                                    <div class="form-help">Common ports: 587 (TLS), 465 (SSL), 25 (unsecured)</div>
                                    @error('smtp_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="smtp_encryption" class="form-label">
                                <i class="fas fa-shield-alt me-1"></i>Encryption Type *
                            </label>
                            <select class="form-select @error('smtp_encryption') is-invalid @enderror" 
                                    id="smtp_encryption" name="smtp_encryption" required>
                                <option value="">Select Encryption</option>
                                <option value="tls" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                                <option value="ssl" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') == 'none' ? 'selected' : '' }}>None (Not Recommended)</option>
                            </select>
                            <div class="form-help">TLS is recommended for most email providers</div>
                            @error('smtp_encryption')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Authentication Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Authentication Settings</h4>
                        <small class="opacity-75">Login credentials for your email account</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_username" class="form-label">
                                        <i class="fas fa-user me-1"></i>SMTP Username *
                                    </label>
                                    <input type="text" class="form-control @error('smtp_username') is-invalid @enderror" 
                                           id="smtp_username" name="smtp_username" 
                                           value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}" 
                                           placeholder="your-email@gmail.com" required>
                                    <div class="form-help">Usually your email address</div>
                                    @error('smtp_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="smtp_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>SMTP Password *
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control @error('smtp_password') is-invalid @enderror" 
                                               id="smtp_password" name="smtp_password" 
                                               value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}" 
                                               placeholder="Enter password" required>
                                        <button type="button" class="btn btn-outline-secondary btn-sm position-absolute" 
                                                id="toggleSmtpPassword"
                                                style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-help">Your email account password or app password</div>
                                    @error('smtp_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Identity Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-id-card me-2"></i>Email Identity</h4>
                        <small class="opacity-75">How emails will appear to recipients</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_email" class="form-label">
                                        <i class="fas fa-at me-1"></i>From Email *
                                    </label>
                                    <input type="email" class="form-control @error('from_email') is-invalid @enderror" 
                                           id="from_email" name="from_email" 
                                           value="{{ old('from_email', $settings['from_email'] ?? '') }}" 
                                           placeholder="noreply@hospital.com" required>
                                    <div class="form-help">Email address that appears in the "From" field</div>
                                    @error('from_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_name" class="form-label">
                                        <i class="fas fa-signature me-1"></i>From Name *
                                    </label>
                                    <input type="text" class="form-control @error('from_name') is-invalid @enderror" 
                                           id="from_name" name="from_name" 
                                           value="{{ old('from_name', $settings['from_name'] ?? '') }}" 
                                           placeholder="ThanksDoc EHR" required>
                                    <div class="form-help">Name that appears in the "From" field</div>
                                    @error('from_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Email Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Test Email</h4>
                        <small class="opacity-75">Test your email configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="test_email" class="form-label">
                                <i class="fas fa-envelope-open me-1"></i>Test Email Address
                            </label>
                            <input type="email" class="form-control @error('test_email') is-invalid @enderror" 
                                   id="test_email" name="test_email" 
                                   value="{{ old('test_email') }}" 
                                   placeholder="test@example.com">
                            <div class="form-help">Enter email address to send a test email (optional)</div>
                            @error('test_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" name="action" value="save" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                        <button type="submit" name="action" value="test" class="btn btn-info btn-lg me-3">
                            <i class="fas fa-paper-plane me-2"></i>Send Test Email
                        </button>
                        <a href="{{ contextRoute('dashboard') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Configuration Status</h6>
                <div class="mb-3">
                    <strong>SMTP Host:</strong> 
                    <span class="status-indicator {{ isset($settings['smtp_host']) && $settings['smtp_host'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['smtp_host']) && $settings['smtp_host'] ? 'Set' : 'Not Set' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>SMTP Username:</strong> 
                    <span class="status-indicator {{ isset($settings['smtp_username']) && $settings['smtp_username'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['smtp_username']) && $settings['smtp_username'] ? 'Set' : 'Not Set' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>From Email:</strong> 
                    <span class="status-indicator {{ isset($settings['from_email']) && $settings['from_email'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['from_email']) && $settings['from_email'] ? 'Set' : 'Not Set' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Encryption:</strong> 
                    <span class="status-indicator {{ isset($settings['smtp_encryption']) && $settings['smtp_encryption'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['smtp_encryption']) && $settings['smtp_encryption'] ? ucfirst($settings['smtp_encryption']) : 'Not Set' }}
                    </span>
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Configuration Tips</h6>
                <ul>
                    <li>Use TLS encryption for Gmail and most providers</li>
                    <li>Port 587 is standard for TLS, 465 for SSL</li>
                    <li>Test your configuration before saving</li>
                    <li>Use app passwords for Gmail (not your regular password)</li>
                    <li>Check your email provider's documentation</li>
                    <li>Ensure your host allows outbound SMTP connections</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-cogs me-2"></i>Common Providers</h6>
                <ul>
                    <li><strong>Gmail:</strong> smtp.gmail.com, Port 587, TLS</li>
                    <li><strong>Outlook:</strong> smtp-mail.outlook.com, Port 587, TLS</li>
                    <li><strong>Yahoo:</strong> smtp.mail.yahoo.com, Port 587, TLS</li>
                    <li><strong>SendGrid:</strong> smtp.sendgrid.net, Port 587, TLS</li>
                    <li><strong>Mailgun:</strong> smtp.mailgun.org, Port 587, TLS</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('dashboard') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    <a href="#" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-book me-1"></i>Documentation
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#emailConfigForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $('.form-control[required], .form-select[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate email format
        const fromEmail = $('#from_email').val();
        if (fromEmail && !isValidEmail(fromEmail)) {
            $('#from_email').addClass('is-invalid');
            isValid = false;
        }

        const testEmail = $('#test_email').val();
        if (testEmail && !isValidEmail(testEmail)) {
            $('#test_email').addClass('is-invalid');
            isValid = false;
        }

        // Validate port number
        const port = $('#smtp_port').val();
        if (port && (port < 1 || port > 65535)) {
            $('#smtp_port').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Remove validation errors on input
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Port suggestions based on encryption
    $('#smtp_encryption').on('change', function() {
        const encryption = $(this).val();
        const portField = $('#smtp_port');
        
        if (encryption === 'tls') {
            portField.val('587');
        } else if (encryption === 'ssl') {
            portField.val('465');
        } else if (encryption === 'none') {
            portField.val('25');
        }
    });

    // Password visibility toggle
    $('#toggleSmtpPassword').on('click', function() {
        const passwordField = $('#smtp_password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

});
</script>
@endpush
