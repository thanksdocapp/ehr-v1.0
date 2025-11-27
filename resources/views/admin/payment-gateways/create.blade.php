@extends('admin.layouts.app')

@section('title', 'Create New Payment Gateway')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a>
    </li>
    <li class="breadcrumb-item active">Create New Gateway</li>
@endsection

@include('admin.shared.modern-ui')

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
            background: #f8f9fc;
            color: #1a202c;
            padding: 1.5rem 2rem;
            border-radius: 12px 12px 0 0;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .form-section-header h4,
        .form-section-header i {
            color: #1a202c !important;
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
            background: #1a202c;
            border: none;
            color: white;
            box-shadow: 0 2px 8px rgba(26, 32, 44, 0.15);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
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

        .form-check-label {
            font-weight: 500;
            color: #5a5c69;
        }

        .form-switch .form-check-input {
            width: 2.5rem;
            height: 1.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="modern-page-header">
            <div class="modern-page-header-content">
                <h1 class="modern-page-title">
                    <i class="fas fa-credit-card" style="color: #1a202c;"></i>
                    Create New Payment Gateway
                </h1>
                <p class="modern-page-subtitle">Configure a new payment gateway for processing transactions</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form id="createGatewayForm" method="POST" action="{{ route('admin.payment-gateways.store') }}">
                    @csrf

                    <!-- Gateway Information -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-info-circle me-2" style="color: #1a202c;"></i>Gateway Information
                            </h4>
                            <small style="color: #4a5568;">Basic details of the payment gateway</small>
                        </div>
                        <div class="form-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="provider" class="form-label">
                                            <i class="fas fa-cogs me-1"></i>Gateway Provider *
                                        </label>
                                        <select name="provider" id="provider" class="form-select @error('provider') is-invalid @enderror" required>
                                            <option value="">Select a gateway provider</option>
                                            @foreach($availableProviders as $key => $provider)
                                                <option value="{{ $key }}" {{ old('provider') == $key ? 'selected' : '' }}>
                                                    {{ $provider['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('provider')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="display_name" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Display Name *
                                        </label>
                                        <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                               id="display_name" name="display_name" value="{{ old('display_name') }}" required>
                                        @error('display_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="sort_order" class="form-label">
                                            <i class="fas fa-sort-numeric-up me-1"></i>Sort Order
                                        </label>
                                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                               id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}">
                                        @error('sort_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transaction_fee_percentage" class="form-label">
                                            <i class="fas fa-percent me-1"></i>Transaction Fee (%)
                                        </label>
                                        <input type="number" step="0.01" min="0" max="100" 
                                               class="form-control @error('transaction_fee_percentage') is-invalid @enderror"
                                               id="transaction_fee_percentage" name="transaction_fee_percentage" 
                                               value="{{ old('transaction_fee_percentage', '0.00') }}">
                                        @error('transaction_fee_percentage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="transaction_fee_fixed" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>Fixed Fee
                                        </label>
                                        <input type="number" step="0.01" min="0" 
                                               class="form-control @error('transaction_fee_fixed') is-invalid @enderror"
                                               id="transaction_fee_fixed" name="transaction_fee_fixed" 
                                               value="{{ old('transaction_fee_fixed', '0.00') }}">
                                        @error('transaction_fee_fixed')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Configuration Switches -->
                                    <div class="form-group">
                                        <label class="form-label">Gateway Settings</label>
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                           value="1" {{ old('is_active') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        Active
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
                                                           value="1" {{ old('is_default') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_default">
                                                        Default
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="test_mode" name="test_mode" 
                                                           value="1" {{ old('test_mode') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="test_mode">
                                                        Test Mode
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-align-left me-1"></i>Description
                                        </label>
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-help">Optional description for this payment gateway</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API Credentials -->
                    <div class="form-section" id="credentials-section" style="display: none;">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-key me-2" style="color: #1a202c;"></i>API Credentials
                            </h4>
                            <small style="color: #4a5568;">Secure credentials for gateway integration</small>
                        </div>
                        <div class="form-section-body">
                            <div id="credentials-container">
                                <!-- Dynamic credentials fields will be added here -->
                            </div>
                        </div>
                    </div>

                    <!-- Webhook Configuration -->
                    <div class="form-section" id="webhook-section" style="display: none;">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-link me-2" style="color: #1a202c;"></i>Webhook Configuration
                            </h4>
                            <small style="color: #4a5568;">Setup webhooks for real-time payment notifications</small>
                        </div>
                        <div class="form-section-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Important:</strong> Webhooks require HTTPS URLs in production. For local development, you can use tools like ngrok or skip webhook setup initially.
                            </div>
                            
                            <div class="card" id="webhook-urls-card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-globe me-2"></i>Webhook URLs</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="webhook-urls-container">
                                        <!-- Dynamic webhook URLs will be shown here -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3" id="webhook-setup-instructions">
                                <!-- Provider-specific webhook setup instructions -->
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Gateway
                        </button>
                        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Helper Information -->
            <div class="col-lg-4">
                <div class="info-card">
                    <h6><i class="fas fa-info-circle me-2"></i>Gateway Setup Guidelines</h6>
                    <ul>
                        <li>All fields marked with * are required</li>
                        <li>Choose a clear display name for users</li>
                        <li>Enable test mode for development</li>
                        <li>Configure transaction fees accurately</li>
                        <li>Only one gateway can be set as default</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-cogs me-2"></i>Supported Providers</h6>
                    <ul>
                        <li><strong>Stripe:</strong> Credit cards, digital wallets</li>
                        <li><strong>PayPal:</strong> PayPal accounts, cards</li>
                        <li><strong>Paystack:</strong> African payment methods</li>
                        <!-- <li><strong>Razorpay:</strong> Indian payment methods</li> -->
                        <li><strong>Flutterwave:</strong> Global merchant solutions</li>
                        <li><strong>CoinGate:</strong> 70+ cryptocurrencies</li>
                        <li><strong>BTCPay Server:</strong> Self-hosted crypto</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                    <ul>
                        <li>Keep API credentials secure and confidential</li>
                        <li>Test transactions in test mode first</li>
                        <li>Monitor transaction fees regularly</li>
                        <li>Update gateway settings as needed</li>
                        <li>Review gateway documentation</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-shield-alt me-2"></i>Security & Compliance</h6>
                    <ul>
                        <li><strong>PCI Compliance:</strong> Secure card processing</li>
                        <li><strong>SSL Encryption:</strong> All data encrypted</li>
                        <li><strong>Audit Trail:</strong> All changes logged</li>
                        <li><strong>Access Control:</strong> Role-based permissions</li>
                        <li><strong>Data Protection:</strong> GDPR compliant</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>View All Gateways
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-form">
                            <i class="fas fa-undo me-1"></i>Reset Form
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" id="validate-form">
                            <i class="fas fa-check me-1"></i>Validate Form
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const providers = @json($availableProviders);

    // Provider change handler - show/hide credentials and webhook sections
    $('#provider').change(function() {
        const selectedProvider = $(this).val();
        const credentialsContainer = $('#credentials-container');
        const credentialsSection = $('#credentials-section');
        const webhookSection = $('#webhook-section');
        
        credentialsContainer.html('');
        
        if (selectedProvider && providers[selectedProvider]) {
            const credentials = providers[selectedProvider].credentials;
            
            if (credentials && credentials.length > 0) {
                credentialsSection.show();
                
                credentials.forEach(credential => {
                    const fieldHtml = createCredentialField(credential);
                    credentialsContainer.append(fieldHtml);
                });
            } else {
                credentialsSection.hide();
            }
            
            // Show webhook section for supported providers
            if (['stripe', 'paypal', 'paystack', 'flutterwave', 'coingate', 'btcpay'].includes(selectedProvider)) {
                webhookSection.show();
                showWebhookUrls(selectedProvider);
                showWebhookInstructions(selectedProvider);
            } else {
                webhookSection.hide();
            }
        } else {
            credentialsSection.hide();
            webhookSection.hide();
        }
    });

    // Create credential field HTML
    function createCredentialField(credential) {
        const selectedProvider = $('#provider').val(); // Get current provider value
        const displayName = credential.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const fieldType = (credential.includes('secret') || credential.includes('key')) ? 'password' : 'text';
        const isOptional = (credential === 'webhook_secret' || credential === 'webhook_id' || (selectedProvider === 'coingate' && (credential === 'app_id' || credential === 'webhook_secret')));
        
        let helpText = '';
        let placeholder = '';
        
        if (credential === 'publishable_key') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from Stripe Dashboard → Developers → API Keys → Publishable key (Test mode)</div>';
            placeholder = 'pk_test_...';
        } else if (credential === 'secret_key') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from Stripe Dashboard → Developers → API Keys → Secret key (Test mode)</div>';
            placeholder = 'sk_test_...';
        } else if (credential === 'webhook_secret') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Optional: Get from Stripe Dashboard → Developers → Webhooks → Signing secret<br><small class="text-success">You can leave this empty for basic testing!</small></div>';
            placeholder = 'whsec_... (optional)';
        } else if (credential === 'client_id') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from PayPal Developer Dashboard → My Apps & Credentials → Sandbox → Client ID</div>';
            placeholder = 'AQkquBDf1zctJOWGKWUEtKXm6qVhueUEMv...';
        } else if (credential === 'client_secret') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from PayPal Developer Dashboard → My Apps & Credentials → Sandbox → Secret</div>';
            placeholder = 'EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs...';
        } else if (credential === 'webhook_id') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Optional: PayPal Webhook ID for payment notifications<br><small class="text-success">Leave empty for sandbox testing - webhooks are not required!</small></div>';
            placeholder = '4JH86294D65818923 (optional)';
        } else if (credential === 'public_key') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from Paystack Dashboard → Settings → API Keys → Public Key (Test mode)</div>';
            placeholder = 'pk_test_0123456789abcdef0123456789abcdef01234567';
        } else if (credential === 'encryption_key') {
            helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Required for Flutterwave: Get from Flutterwave Dashboard → Settings → API Keys → Encryption Key<br><small class="text-danger">This is essential for Flutterwave transactions!</small></div>';
            placeholder = 'FLWSECK_TEST-0123456789abcdef01234567-X';
        } else {
            // Handle other provider-specific credentials
            if (credential === 'secret_key' && selectedProvider === 'paystack') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from Paystack Dashboard → Settings → API Keys → Secret Key (Test mode)</div>';
                placeholder = 'sk_test_0123456789abcdef0123456789abcdef01234567';
            } else if (credential === 'public_key' && selectedProvider === 'flutterwave') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from Flutterwave Dashboard → Settings → API Keys → Public Key (Test mode)</div>';
                placeholder = 'FLWPUBK_TEST-0123456789abcdef01234567-X';
            } else if (credential === 'secret_key' && selectedProvider === 'flutterwave') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from Flutterwave Dashboard → Settings → API Keys → Secret Key (Test mode)</div>';
                placeholder = 'FLWSECK_TEST-0123456789abcdef01234567-X';
            } else if (credential === 'webhook_secret' && selectedProvider === 'flutterwave') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Optional: Flutterwave webhook secret for security<br><small class="text-success">You can leave this empty for basic testing!</small></div>';
                placeholder = 'flw-webhook-secret-hash (optional)';
            } else if (credential === 'api_key' && selectedProvider === 'coingate') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from CoinGate Dashboard → Merchant → API → API Key (Sandbox mode)<br><small class="text-info">Use sandbox for testing with fake crypto!</small></div>';
                placeholder = 'VvQjcRHdR24CaTA91kBcwutjTtJaJEcp';
            } else if (credential === 'app_id' && selectedProvider === 'coingate') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Optional: Get from CoinGate Dashboard → Merchant → Apps → App ID<br><small class="text-success">Leave empty for basic testing!</small></div>';
                placeholder = '12345 (optional)';
            } else if (credential === 'webhook_secret' && selectedProvider === 'coingate') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Optional: CoinGate webhook secret for payment notifications<br><small class="text-success">You can leave this empty for basic testing!</small></div>';
                placeholder = 'webhook-secret-key (optional)';
            } else if (credential === 'server_url' && selectedProvider === 'btcpay') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Your BTCPay Server URL (e.g., https://btcpay.yourdomain.com)</div>';
                placeholder = 'https://btcpay.yourdomain.com';
            } else if (credential === 'store_id' && selectedProvider === 'btcpay') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from BTCPay Server → Stores → Store Settings → General → Store ID</div>';
                placeholder = 'Your BTCPay Store ID';
            } else if (credential === 'api_key' && selectedProvider === 'btcpay') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Get from BTCPay Server → Account → Manage Account → API Keys</div>';
                placeholder = 'BTCPay API Key';
            } else if (credential === 'webhook_secret' && selectedProvider === 'btcpay') {
                helpText = '<div class="form-help"><i class="fas fa-info-circle me-1"></i>Optional: BTCPay Server webhook secret for security<br><small class="text-success">You can leave this empty for basic testing!</small></div>';
                placeholder = 'btcpay-webhook-secret (optional)';
            } else {
                placeholder = `Enter your ${credential.replace(/_/g, ' ')}`;
            }
        }
        
        return `
            <div class="form-group">
                <label for="${credential}" class="form-label">
                    <i class="fas fa-key me-1"></i>${displayName} ${isOptional ? '<span class="text-muted">(Optional)</span>' : '*'}
                </label>
                <input type="${fieldType}" class="form-control" id="${credential}" 
                       name="credentials[${credential}]" ${isOptional ? '' : 'required'}
                       placeholder="${placeholder}">
                ${helpText}
            </div>
        `;
    }

    // Reset form functionality
    $('#reset-form').click(function() {
        if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
            $('#createGatewayForm')[0].reset();
            $('#credentials-container').html('');
            $('#credentials-section').hide();
            $('.is-invalid').removeClass('is-invalid');
        }
    });

    // Form validation functionality
    $('#validate-form').click(function() {
        let errors = [];
        
        if (!$('#provider').val()) errors.push('Gateway provider is required');
        if (!$('#display_name').val()) errors.push('Display name is required');
        
        // Check credentials if provider is selected
        if ($('#provider').val() && $('#credentials-section').is(':visible')) {
            $('#credentials-container input[required]').each(function() {
                if (!$(this).val()) {
                    errors.push(`${$(this).prev('label').text().replace(' *', '')} is required`);
                }
            });
        }
        
        // Validate transaction fees are numeric
        const percentageFee = $('#transaction_fee_percentage').val();
        const fixedFee = $('#transaction_fee_fixed').val();
        
        if (percentageFee && (isNaN(percentageFee) || percentageFee < 0 || percentageFee > 100)) {
            errors.push('Transaction fee percentage must be between 0 and 100');
        }
        
        if (fixedFee && (isNaN(fixedFee) || fixedFee < 0)) {
            errors.push('Fixed fee must be a positive number');
        }
        
        if (errors.length > 0) {
            alert('Please fix the following errors:\n' + errors.join('\n'));
        } else {
            alert('Form validation passed! Ready to save.');
        }
    });

    // Auto-format fee inputs
    $('#transaction_fee_percentage, #transaction_fee_fixed').on('blur', function() {
        const value = $(this).val();
        if (value) {
            const numericValue = parseFloat(value);
            if (!isNaN(numericValue)) {
                $(this).val(numericValue.toFixed(2));
            }
        }
    });

    // Form submission validation
    $('#createGatewayForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        if (!$('#provider').val()) {
            $('#provider').addClass('is-invalid');
            isValid = false;
        }
        if (!$('#display_name').val()) {
            $('#display_name').addClass('is-invalid');
            isValid = false;
        }
        
        // Check credentials if visible
        if ($('#credentials-section').is(':visible')) {
            $('#credentials-container input[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                }
            });
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...').prop('disabled', true);
    });

    // Show webhook URLs for selected provider
    function showWebhookUrls(provider) {
        const baseUrl = window.location.origin;
        const container = $('#webhook-urls-container');
        
        let webhookUrls = [];
        
        if (provider === 'stripe') {
            webhookUrls = [
                { name: 'Webhook URL', url: `${baseUrl}/webhooks/stripe`, description: 'Main webhook endpoint for all Stripe events' }
            ];
        } else if (provider === 'paypal') {
            webhookUrls = [
                { name: 'Webhook URL', url: `${baseUrl}/webhooks/paypal`, description: 'PayPal IPN and webhook endpoint' }
            ];
        } else if (provider === 'paystack') {
            webhookUrls = [
                { name: 'Webhook URL', url: `${baseUrl}/webhooks/paystack`, description: 'Paystack webhook endpoint' }
            ];
        } else if (provider === 'flutterwave') {
            webhookUrls = [
                { name: 'Webhook URL', url: `${baseUrl}/webhooks/flutterwave`, description: 'Flutterwave webhook endpoint' }
            ];
        } else if (provider === 'coingate') {
            webhookUrls = [
                { name: 'Success URL', url: `${baseUrl}/payment/success`, description: 'User redirect after successful payment' },
                { name: 'Cancel URL', url: `${baseUrl}/payment/cancel`, description: 'User redirect after cancelled payment' },
                { name: 'Callback URL', url: `${baseUrl}/webhooks/coingate`, description: 'Server-to-server payment notifications' }
            ];
        } else if (provider === 'btcpay') {
            webhookUrls = [
                { name: 'Webhook URL', url: `${baseUrl}/webhooks/btcpay`, description: 'BTCPay Server webhook endpoint' }
            ];
        }
        
        container.html('');
        
        webhookUrls.forEach(webhook => {
            const webhookHtml = `
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold">${webhook.name}</label>
                    <div class="input-group">
                        <input type="text" class="form-control" value="${webhook.url}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('${webhook.url}')">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted">${webhook.description}</small>
                </div>
            `;
            container.append(webhookHtml);
        });
    }
    
    // Show webhook setup instructions for selected provider
    function showWebhookInstructions(provider) {
        const container = $('#webhook-setup-instructions');
        let instructions = '';
        
        if (provider === 'stripe') {
            instructions = `
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fab fa-stripe me-2"></i>Stripe Webhook Setup</h6>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Go to your <strong>Stripe Dashboard</strong> → <strong>Developers</strong> → <strong>Webhooks</strong></li>
                            <li>Click <strong>"Add endpoint"</strong></li>
                            <li>Enter the webhook URL above in the <strong>"Endpoint URL"</strong> field</li>
                            <li>Select these events to listen for:
                                <ul class="mt-2">
                                    <li><code>payment_intent.succeeded</code></li>
                                    <li><code>payment_intent.payment_failed</code></li>
                                    <li><code>checkout.session.completed</code></li>
                                </ul>
                            </li>
                            <li>Click <strong>"Add endpoint"</strong> to save</li>
                            <li><em>Optional:</em> Copy the webhook signing secret and add it to your credentials above for enhanced security</li>
                        </ol>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i><strong>Note:</strong> Webhook signing secret is optional for testing but recommended for production.
                        </div>
                    </div>
                </div>
            `;
        } else if (provider === 'paystack') {
            instructions = `
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i>Paystack Webhook Setup</h6>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Go to your <strong>Paystack Dashboard</strong> → <strong>Settings</strong> → <strong>Webhooks</strong></li>
                            <li>Enter the webhook URL above</li>
                            <li>Select these events:
                                <ul class="mt-2">
                                    <li><code>charge.success</code></li>
                                    <li><code>charge.failed</code></li>
                                </ul>
                            </li>
                            <li>Save the webhook configuration</li>
                        </ol>
                    </div>
                </div>
            `;
        } else if (provider === 'flutterwave') {
            instructions = `
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-wave-square me-2"></i>Flutterwave Webhook Setup</h6>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Go to your <strong>Flutterwave Dashboard</strong> → <strong>Settings</strong> → <strong>Webhooks</strong></li>
                            <li>Enter the webhook URL above</li>
                            <li>Set a webhook hash (secret) and add it to your credentials above</li>
                            <li>Select these events:
                                <ul class="mt-2">
                                    <li><code>charge.completed</code></li>
                                    <li><code>charge.failed</code></li>
                                </ul>
                            </li>
                            <li>Save the webhook configuration</li>
                        </ol>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i><strong>Important:</strong> Webhook secret is highly recommended for Flutterwave.
                        </div>
                    </div>
                </div>
            `;
        } else if (provider === 'btcpay') {
            instructions = `
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fab fa-bitcoin me-2"></i>BTCPay Server Webhook Setup</h6>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Go to your <strong>BTCPay Server</strong> → <strong>Store Settings</strong> → <strong>Webhooks</strong></li>
                            <li>Click <strong>"Create Webhook"</strong></li>
                            <li>Enter the webhook URL above</li>
                            <li>Select these events:
                                <ul class="mt-2">
                                    <li><code>InvoiceSettled</code></li>
                                    <li><code>InvoiceExpired</code></li>
                                    <li><code>InvoiceInvalid</code></li>
                                </ul>
                            </li>
                            <li>Save the webhook</li>
                        </ol>
                    </div>
                </div>
            `;
        } else {
            instructions = `
                <div class="card">
                    <div class="card-body text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>Webhook setup instructions will appear here when you select a supported provider.</p>
                    </div>
                </div>
            `;
        }
        
        container.html(instructions);
    }
    
    // Copy to clipboard function
    window.copyToClipboard = function(text) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success feedback
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        });
    };

    // Trigger provider change on page load
    $('#provider').trigger('change');
});
</script>
@endpush
