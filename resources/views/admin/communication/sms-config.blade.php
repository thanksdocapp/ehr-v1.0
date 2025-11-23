@extends('admin.layouts.app')

@section('title', 'SMS Configuration')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item">Communication</li>
    <li class="breadcrumb-item active">SMS Configuration</li>
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
        <h1><i class="fas fa-sms me-2 text-primary"></i>SMS Configuration</h1>
        <p class="page-subtitle text-muted">Configure SMS settings for sending notifications</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="smsConfigForm" action="{{ contextRoute('sms-config') }}" method="POST">
                @csrf
                
                <!-- SMS Provider Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-cog me-2"></i>Provider Settings</h4>
                        <small class="opacity-75">Configure your SMS service provider</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="provider" class="form-label">
                                <i class="fas fa-building me-1"></i>SMS Provider *
                            </label>
                            <select class="form-select @error('provider') is-invalid @enderror" 
                                    id="provider" name="provider" required>
                                <option value="">Select SMS Provider</option>
                                <option value="twilio" {{ old('provider', $settings['provider'] ?? '') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                <option value="nexmo" {{ old('provider', $settings['provider'] ?? '') == 'nexmo' ? 'selected' : '' }}>Nexmo/Vonage</option>
                                <option value="clickatell" {{ old('provider', $settings['provider'] ?? '') == 'clickatell' ? 'selected' : '' }}>Clickatell</option>
                                <option value="textlocal" {{ old('provider', $settings['provider'] ?? '') == 'textlocal' ? 'selected' : '' }}>TextLocal</option>
                            </select>
                            <div class="form-help">Choose your SMS service provider</div>
                            @error('provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Authentication Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Authentication Settings</h4>
                        <small class="opacity-75">API credentials for your SMS provider</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_key" class="form-label">
                                        <i class="fas fa-key me-1"></i>API Key *
                                    </label>
                                    <input type="text" class="form-control @error('api_key') is-invalid @enderror" 
                                           id="api_key" name="api_key" 
                                           value="{{ old('api_key', $settings['api_key'] ?? '') }}" 
                                           placeholder="Enter your API key" required>
                                    <div class="form-help">Your SMS provider's API key</div>
                                    @error('api_key')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_secret" class="form-label">
                                        <i class="fas fa-lock me-1"></i>API Secret *
                                    </label>
                                    <div class="position-relative">
                                        <input type="password" class="form-control @error('api_secret') is-invalid @enderror" 
                                               id="api_secret" name="api_secret" 
                                               value="{{ old('api_secret', $settings['api_secret'] ?? '') }}" 
                                               placeholder="Enter your API secret" required>
                                        <button type="button" class="btn btn-outline-secondary btn-sm position-absolute" 
                                                id="toggleApiSecret"
                                                style="right: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-help">Your SMS provider's API secret</div>
                                    @error('api_secret')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sender Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Sender Settings</h4>
                        <small class="opacity-75">Configure sender identity</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sender_id" class="form-label">
                                        <i class="fas fa-signature me-1"></i>Sender ID *
                                    </label>
                                    <input type="text" class="form-control @error('sender_id') is-invalid @enderror" 
                                           id="sender_id" name="sender_id" 
                                           value="{{ old('sender_id', $settings['sender_id'] ?? '') }}" 
                                           placeholder="HOSPITAL" required>
                                    <div class="form-help">Name that appears as sender (max 11 characters)</div>
                                    @error('sender_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="custom_url" class="form-label">
                                        <i class="fas fa-link me-1"></i>Custom API URL
                                    </label>
                                    <input type="url" class="form-control @error('custom_url') is-invalid @enderror" 
                                           id="custom_url" name="custom_url" 
                                           value="{{ old('custom_url', $settings['custom_url'] ?? '') }}" 
                                           placeholder="https://api.provider.com/send">
                                    <div class="form-help">Optional: Custom API endpoint URL</div>
                                    @error('custom_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test SMS Settings -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Test SMS</h4>
                        <small class="opacity-75">Test your SMS configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="test_phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Test Phone Number
                            </label>
                            <input type="tel" class="form-control @error('test_phone') is-invalid @enderror" 
                                   id="test_phone" name="test_phone" 
                                   value="{{ old('test_phone') }}" 
                                   placeholder="+000123456789">
                            <div class="form-help">Enter phone number to send a test SMS (include country code)</div>
                            @error('test_phone')
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
                            <i class="fas fa-paper-plane me-2"></i>Send Test SMS
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
                    <strong>Provider:</strong> 
                    <span class="status-indicator {{ isset($settings['provider']) && $settings['provider'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['provider']) && $settings['provider'] ? ucfirst($settings['provider']) : 'Not Set' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>API Key:</strong> 
                    <span class="status-indicator {{ isset($settings['api_key']) && $settings['api_key'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['api_key']) && $settings['api_key'] ? 'Set' : 'Not Set' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Sender ID:</strong> 
                    <span class="status-indicator {{ isset($settings['sender_id']) && $settings['sender_id'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['sender_id']) && $settings['sender_id'] ? $settings['sender_id'] : 'Not Set' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>API Secret:</strong> 
                    <span class="status-indicator {{ isset($settings['api_secret']) && $settings['api_secret'] ? 'status-complete' : 'status-incomplete' }}">
                        {{ isset($settings['api_secret']) && $settings['api_secret'] ? 'Set' : 'Not Set' }}
                    </span>
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Configuration Tips</h6>
                <ul>
                    <li>Always include country code in phone numbers</li>
                    <li>Test your configuration before going live</li>
                    <li>Keep your API credentials secure</li>
                    <li>Sender ID should be descriptive but short</li>
                    <li>Monitor your SMS usage and credits</li>
                    <li>Check provider documentation for specific requirements</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-cogs me-2"></i>Popular Providers</h6>
                <ul>
                    <li><strong>Twilio:</strong> Global coverage, reliable delivery</li>
                    <li><strong>Nexmo/Vonage:</strong> Good rates, comprehensive API</li>
                    <li><strong>Clickatell:</strong> Easy integration, good support</li>
                    <li><strong>TextLocal:</strong> Good for local markets</li>
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
    $('#smsConfigForm').on('submit', function(e) {
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

        // Validate phone number format for test SMS
        const testPhone = $('#test_phone').val();
        if (testPhone && !isValidPhone(testPhone)) {
            $('#test_phone').addClass('is-invalid');
            isValid = false;
        }

        // Validate sender ID length
        const senderId = $('#sender_id').val();
        if (senderId && senderId.length > 11) {
            $('#sender_id').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // Phone validation helper
    function isValidPhone(phone) {
        const phoneRegex = /^\+?[1-9]\d{1,14}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    }

    // Remove validation errors on input
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Password visibility toggle
    $('#toggleApiSecret').on('click', function() {
        const passwordField = $('#api_secret');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Character counter for Sender ID
    $('#sender_id').on('input', function() {
        const maxLength = 11;
        const currentLength = $(this).val().length;
        const helpText = $(this).siblings('.form-help');
        
        if (currentLength > maxLength) {
            $(this).addClass('is-invalid');
            helpText.text(`Sender ID too long (${currentLength}/${maxLength} characters)`);
        } else {
            $(this).removeClass('is-invalid');
            helpText.text(`Name that appears as sender (${currentLength}/${maxLength} characters)`);
        }
    });
});
</script>
@endpush

