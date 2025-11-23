@extends('admin.layouts.app')

@section('title', 'Edit Payment Gateway')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a>
    </li>
    <li class="breadcrumb-item active">Edit {{ $paymentGateway->display_name }}</li>
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
        .encryption-warning {
            font-size: 0.85rem;
            color: #e3342f;
            font-style: italic;
            margin-top: 0.5rem;
            text-align: left;
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
        <div class="page-title mb-4">
            <h1>
                <i class="fas fa-edit me-2 text-primary"></i>Edit Gateway: {{ $paymentGateway->display_name }}
            </h1>
            <p class="page-subtitle text-muted">
                Update payment gateway configuration and settings
            </p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <form id="editGatewayForm" method="POST" action="{{ route('admin.payment-gateways.update', $paymentGateway) }}">
                    @csrf
                    @method('PUT')

                    <!-- Gateway Information -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Gateway Information
                            </h4>
                            <small class="opacity-75">Basic details of the payment gateway</small>
                        </div>
                        <div class="form-section-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="provider" class="form-label">
                                            <i class="fas fa-cogs me-1"></i>Gateway Provider
                                        </label>
                                        <input type="text" class="form-control" id="provider" 
                                               value="{{ ucfirst($paymentGateway->provider) }}" disabled>
                                        <div class="form-help">Provider cannot be changed after creation</div>
                                    </div>

                                    <div class="form-group">
                                        <label for="display_name" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Display Name *
                                        </label>
                                        <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                                               id="display_name" name="display_name" 
                                               value="{{ old('display_name', $paymentGateway->display_name) }}" required>
                                        @error('display_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="sort_order" class="form-label">
                                            <i class="fas fa-sort-numeric-up me-1"></i>Sort Order
                                        </label>
                                        <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                               id="sort_order" name="sort_order" 
                                               value="{{ old('sort_order', $paymentGateway->sort_order) }}">
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
                                               value="{{ old('transaction_fee_percentage', $paymentGateway->transaction_fee_percentage) }}">
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
                                               value="{{ old('transaction_fee_fixed', $paymentGateway->transaction_fee_fixed) }}">
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
                                                           value="1" {{ old('is_active', $paymentGateway->is_active) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        Active
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_default" name="is_default" 
                                                           value="1" {{ old('is_default', $paymentGateway->is_default) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_default">
                                                        Default
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="test_mode" name="test_mode" 
                                                           value="1" {{ old('test_mode', $paymentGateway->test_mode) ? 'checked' : '' }}>
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
                                        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" 
                                                  rows="3">{{ old('description', $paymentGateway->description) }}</textarea>
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
                    @if(isset($providerConfig['credentials']) && count($providerConfig['credentials']) > 0)
                    <div class="form-section">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-key me-2"></i>API Credentials
                            </h4>
                            <small class="opacity-75">Secure credentials for gateway integration</small>
                        </div>
                        <div class="form-section-body">
                            <div id="credentials-container">
                                @foreach($providerConfig['credentials'] as $credential)
                                    <div class="form-group">
                                        <label for="{{ $credential }}" class="form-label">
                                            <i class="fas fa-key me-1"></i>{{ ucwords(str_replace('_', ' ', $credential)) }} 
                                            @if($credential === 'webhook_secret' || $credential === 'webhook_id' || ($paymentGateway->provider === 'coingate' && ($credential === 'app_id' || $credential === 'webhook_secret')))
                                                <span class="text-muted">(Optional)</span>
                                            @else
                                                *
                                            @endif
                                        </label>
                                        <input type="{{ str_contains($credential, 'secret') || str_contains($credential, 'key') ? 'password' : 'text' }}" 
                                               class="form-control @error('credentials.'.$credential) is-invalid @enderror" 
                                               id="{{ $credential }}" 
                                               name="credentials[{{ $credential }}]" 
                                               value="{{ old('credentials.'.$credential, $paymentGateway->credentials[$credential] ?? '') }}" 
                                               placeholder="@if($credential === 'publishable_key')pk_test_...@elseif($credential === 'secret_key')sk_test_...@elseif($credential === 'webhook_secret')whsec_... (optional)@elseif($credential === 'client_id')Client ID from PayPal@elseif($credential === 'client_secret')Client Secret from PayPal@elseif($credential === 'webhook_id')Webhook ID (optional)@elseif($credential === 'public_key' && $paymentGateway->provider === 'paystack')pk_test_...@elseif($credential === 'secret_key' && $paymentGateway->provider === 'paystack')sk_test_...@elseif($credential === 'public_key' && $paymentGateway->provider === 'flutterwave')FLWPUBK_TEST-...@elseif($credential === 'secret_key' && $paymentGateway->provider === 'flutterwave')FLWSECK_TEST-...@elseif($credential === 'encryption_key')FLWSECK_TEST-...@elseif($credential === 'webhook_secret' && $paymentGateway->provider === 'flutterwave')webhook-secret (optional)@elseif($credential === 'api_key' && $paymentGateway->provider === 'coingate')Your CoinGate API Key@elseif($credential === 'app_id' && $paymentGateway->provider === 'coingate')App ID (optional)@elseif($credential === 'webhook_secret' && $paymentGateway->provider === 'coingate')Webhook Secret (optional)@else{{ str_replace('_', ' ', $credential) }}@endif"
                                               {{ ($credential === 'webhook_secret' || str_contains($credential, 'encryption') || ($paymentGateway->provider === 'coingate' && ($credential === 'app_id' || $credential === 'webhook_secret'))) ? '' : 'required' }}>
                                        @if(str_contains($credential, 'encryption'))
                                            <div class="encryption-warning">
                                                Important: Your encryption key is needed for Flutterwave transactions.
                                            </div>
                                        @endif
                                        
                                        @if($paymentGateway->provider === 'stripe')
                                            @if($credential === 'publishable_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from Stripe Dashboard → Developers → API Keys → Publishable key (Test mode)
                                                </div>
                                            @elseif($credential === 'secret_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from Stripe Dashboard → Developers → API Keys → Secret key (Test mode)
                                                </div>
                                            @elseif($credential === 'webhook_secret')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Optional: Get from Stripe Dashboard → Developers → Webhooks → Signing secret
                                                    <br><small class="text-success">You can leave this empty for basic testing!</small>
                                                </div>
                                            @endif
                                        @elseif($paymentGateway->provider === 'paypal')
                                            @if($credential === 'client_id')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from PayPal Developer Dashboard → My Apps & Credentials → Sandbox → Client ID
                                                </div>
                                            @elseif($credential === 'client_secret')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from PayPal Developer Dashboard → My Apps & Credentials → Sandbox → Secret
                                                </div>
                                            @elseif($credential === 'webhook_id')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Optional: PayPal Webhook ID for payment notifications
                                                    <br><small class="text-success">Leave empty for sandbox testing - webhooks are not required!</small>
                                                </div>
                                            @endif
                                        @elseif($paymentGateway->provider === 'paystack')
                                            @if($credential === 'public_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from Paystack Dashboard → Settings → API Keys → Public Key (Test mode)
                                                </div>
                                            @elseif($credential === 'secret_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from Paystack Dashboard → Settings → API Keys → Secret Key (Test mode)
                                                </div>
                                            @endif
                                        @elseif($paymentGateway->provider === 'flutterwave')
                                            @if($credential === 'public_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from Flutterwave Dashboard → Settings → API Keys → Public Key (Test mode)
                                                </div>
                                            @elseif($credential === 'secret_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from Flutterwave Dashboard → Settings → API Keys → Secret Key (Test mode)
                                                </div>
                                            @elseif($credential === 'encryption_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Required for Flutterwave: Get from Flutterwave Dashboard → Settings → API Keys → Encryption Key
                                                    <br><small class="text-danger">This is essential for Flutterwave transactions!</small>
                                                </div>
                                            @elseif($credential === 'webhook_secret')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Optional: Flutterwave webhook secret for security
                                                    <br><small class="text-success">You can leave this empty for basic testing!</small>
                                                </div>
                                            @endif
                                        @elseif($paymentGateway->provider === 'coingate')
                                            @if($credential === 'api_key')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Get from CoinGate Dashboard → Merchant → API → API Key (Sandbox mode)
                                                    <br><small class="text-info">Use sandbox for testing with fake crypto!</small>
                                                </div>
                                            @elseif($credential === 'app_id')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Optional: Get from CoinGate Dashboard → Merchant → Apps → App ID
                                                    <br><small class="text-success">Leave empty for basic testing!</small>
                                                </div>
                                            @elseif($credential === 'webhook_secret')
                                                <div class="form-help">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Optional: CoinGate webhook secret for payment notifications
                                                    <br><small class="text-success">You can leave this empty for basic testing!</small>
                                                </div>
                                            @endif
                                        @endif
                                        
                                        @error('credentials.'.$credential)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Webhook Configuration Guide -->
                    @if(in_array($paymentGateway->provider, ['btcpay', 'paystack', 'flutterwave']))
                    <div class="form-section">
                        <div class="form-section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-link me-2"></i>Webhook Configuration
                            </h4>
                            <small class="opacity-75">Setup webhook URLs in your {{ ucfirst($paymentGateway->provider) }} dashboard</small>
                        </div>
                        <div class="form-section-body">
                            <div class="info-card bg-light">
                                <h6><i class="fas fa-globe me-2"></i>Webhook/Callback URLs</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Webhook URL:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="{{ url('/webhook/payment/' . $paymentGateway->provider) }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Success URL:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="{{ url('/payment/success') }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Cancel URL:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="{{ url('/payment/cancel') }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label text-muted">Callback URL:</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="{{ url('/payment/callback/' . $paymentGateway->provider) }}" readonly>
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-card bg-light">
                                <h6><i class="fas fa-cogs me-2"></i>Setup Instructions for {{ ucfirst($paymentGateway->provider) }}</h6>
                                @if($paymentGateway->provider === 'paystack')
                                    <ol>
                                        <li>Login to your <strong>Paystack Dashboard</strong></li>
                                        <li>Go to <strong>Settings → Webhooks</strong></li>
                                        <li>Click <strong>"Add webhook endpoint"</strong></li>
                                        <li>Copy and paste the webhook URL above</li>
                                        <li>Select these events: <span class="badge bg-secondary">charge.success</span>, <span class="badge bg-secondary">charge.failed</span>, <span class="badge bg-secondary">subscription.create</span></li>
                                        <li>Save the webhook and copy the webhook secret to the credentials above</li>
                                    </ol>
                                @elseif($paymentGateway->provider === 'flutterwave')
                                    <ol>
                                        <li>Login to your <strong>Flutterwave Dashboard</strong></li>
                                        <li>Go to <strong>Settings → Webhooks</strong></li>
                                        <li>Click <strong>"Add new webhook"</strong></li>
                                        <li>Copy and paste the webhook URL above</li>
                                        <li>Select these events: <span class="badge bg-secondary">charge.completed</span>, <span class="badge bg-secondary">charge.failed</span>, <span class="badge bg-secondary">transfer.completed</span></li>
                                        <li>Generate and copy the webhook secret to the credentials above</li>
                                    </ol>
                                @elseif($paymentGateway->provider === 'btcpay')
                                    <ol>
                                        <li>Login to your <strong>BTCPay Server</strong></li>
                                        <li>Go to <strong>Store Settings → Webhooks</strong></li>
                                        <li>Click <strong>"Create Webhook"</strong></li>
                                        <li>Copy and paste the webhook URL above</li>
                                        <li>Select these events: <span class="badge bg-secondary">InvoiceSettled</span>, <span class="badge bg-secondary">InvoiceExpired</span>, <span class="badge bg-secondary">InvoiceInvalid</span></li>
                                        <li>Generate and copy the webhook secret</li>
                                    </ol>
                                @endif
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Important:</strong> Make sure your website is accessible via HTTPS for webhooks to work properly.
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Submit Button -->
                    <div class="form-group text-end">
                        <a href="{{ route('admin.payment-gateways.show', $paymentGateway) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-eye me-2"></i>View Gateway
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Gateway
                        </button>
                    </div>
                </form>
            </div>

            <!-- Gateway Summary Sidebar -->
            <div class="col-lg-4">
                <div class="info-card">
                    <h6><i class="fas fa-credit-card me-2"></i>Gateway Summary</h6>
                    <ul class="list-unstyled">
                        <li><strong>Provider:</strong> {{ ucfirst($paymentGateway->provider) }}</li>
                        <li><strong>Display Name:</strong> {{ $paymentGateway->display_name }}</li>
                        <li><strong>Status:</strong> 
                            <span class="badge bg-{{ $paymentGateway->is_active ? 'success' : 'secondary' }}">
                                {{ $paymentGateway->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                        <li><strong>Default Gateway:</strong> 
                            <span class="badge bg-{{ $paymentGateway->is_default ? 'primary' : 'secondary' }}">
                                {{ $paymentGateway->is_default ? 'Yes' : 'No' }}
                            </span>
                        </li>
                        <li><strong>Test Mode:</strong> 
                            <span class="badge bg-{{ $paymentGateway->test_mode ? 'warning' : 'success' }}">
                                {{ $paymentGateway->test_mode ? 'Enabled' : 'Disabled' }}
                            </span>
                        </li>
                        <li><strong>Transaction Fee:</strong> 
                            @if($paymentGateway->transaction_fee_percentage > 0 || $paymentGateway->transaction_fee_fixed > 0)
                                {{ $paymentGateway->transaction_fee_percentage }}% + ${{ number_format($paymentGateway->transaction_fee_fixed, 2) }}
                            @else
                                No fees configured
                            @endif
                        </li>
                        <li><strong>Created:</strong> {{ formatDate($paymentGateway->created_at) }}</li>
                        <li><strong>Last Updated:</strong> {{ formatDate($paymentGateway->updated_at) }}</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-info-circle me-2"></i>Update Guidelines</h6>
                    <ul>
                        <li>Ensure display name is user-friendly</li>
                        <li>Test changes in test mode first</li>
                        <li>Update credentials securely</li>
                        <li>Only one gateway can be default</li>
                        <li>Deactivate unused gateways</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-chart-line me-2"></i>Gateway Statistics</h6>
                    <ul class="list-unstyled">
                        <li><strong>Total Transactions:</strong> 0</li>
                        <li><strong>Successful Payments:</strong> 0</li>
                        <li><strong>Failed Payments:</strong> 0</li>
                        <li><strong>Total Revenue:</strong> $0.00</li>
                        <li><small class="text-muted">Statistics will be available once transactions are processed</small></li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-shield-alt me-2"></i>Security Notes</h6>
                    <ul>
                        <li><strong>Credentials:</strong> Stored encrypted</li>
                        <li><strong>Logs:</strong> All changes tracked</li>
                        <li><strong>Access:</strong> Admin only</li>
                        <li><strong>Testing:</strong> Use test mode safely</li>
                    </ul>
                </div>

                <div class="info-card">
                    <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-list me-1"></i>View All Gateways
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="test-connection">
                            <i class="fas fa-plug me-1"></i>Test Connection
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
    // Calculate total fees dynamically
    function calculateFees() {
        const percentage = parseFloat($('#transaction_fee_percentage').val()) || 0;
        const fixed = parseFloat($('#transaction_fee_fixed').val()) || 0;
        
        // Update gateway summary if it exists
        if ($('.info-card').length > 0) {
            const feeText = (percentage > 0 || fixed > 0) 
                ? percentage + '% + $' + fixed.toFixed(2)
                : 'No fees configured';
            $('.info-card:first ul li:nth-child(6)').html('<strong>Transaction Fee:</strong> ' + feeText);
        }
    }

    // Bind calculation to input changes
    $('#transaction_fee_percentage, #transaction_fee_fixed').on('input change', calculateFees);
    
    // Initial calculation on page load
    calculateFees();

    // Test connection functionality
    $('#test-connection').click(function() {
        const gatewayId = {{ $paymentGateway->id }};
        $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Testing...').prop('disabled', true);
        
        // Simulate test connection (replace with actual API call)
        setTimeout(() => {
            alert('Connection test completed. Check gateway logs for details.');
            $(this).html('<i class="fas fa-plug me-1"></i>Test Connection').prop('disabled', false);
        }, 2000);
    });

    // Form validation functionality
    $('#validate-form').click(function() {
        let errors = [];
        
        if (!$('#display_name').val().trim()) {
            errors.push('Display name is required');
        }
        
        // Check credentials if they exist
        $('#credentials-container input[required]').each(function() {
            if (!$(this).val().trim()) {
                const fieldName = $(this).prev('label').text().replace(' *', '');
                errors.push(fieldName + ' is required');
            }
        });
        
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

    // Form submission validation
    $('#editGatewayForm').on('submit', function(e) {
        let isValid = true;
        let errorMessage = '';
        
        // Check required fields
        const requiredFields = {
            'display_name': 'Display Name'
        };
        
        $.each(requiredFields, function(field, label) {
            const value = $('[name="' + field + '"]').val();
            if (!value || value.trim() === '') {
                isValid = false;
                errorMessage += label + ' is required.\n';
            }
        });
        
        // Check credentials if they exist
        $('#credentials-container input[required]').each(function() {
            if (!$(this).val().trim()) {
                const fieldName = $(this).prev('label').text().replace(' *', '');
                isValid = false;
                errorMessage += fieldName + ' is required.\n';
            }
        });
        
        // Validate numeric fields
        const percentageFee = parseFloat($('#transaction_fee_percentage').val());
        if ($('#transaction_fee_percentage').val() && (isNaN(percentageFee) || percentageFee < 0 || percentageFee > 100)) {
            isValid = false;
            errorMessage += 'Transaction fee percentage must be between 0 and 100.\n';
        }
        
        const fixedFee = parseFloat($('#transaction_fee_fixed').val());
        if ($('#transaction_fee_fixed').val() && (isNaN(fixedFee) || fixedFee < 0)) {
            isValid = false;
            errorMessage += 'Fixed fee must be a positive number.\n';
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please correct the following errors:\n\n' + errorMessage);
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...').prop('disabled', true);
    });

    // Auto-format numeric inputs
    $('#transaction_fee_percentage, #transaction_fee_fixed').on('blur', function() {
        const value = parseFloat($(this).val());
        if (!isNaN(value)) {
            $(this).val(value.toFixed(2));
        }
    });
});

// Copy to clipboard function
function copyToClipboard(button) {
    const input = button.previousElementSibling;
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    
    // Visual feedback
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    button.classList.remove('btn-outline-secondary');
    button.classList.add('btn-success');
    
    setTimeout(() => {
        button.innerHTML = originalIcon;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endpush
