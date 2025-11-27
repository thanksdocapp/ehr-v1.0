@extends('admin.layouts.app')

@section('title', 'Payment Gateway Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a></li>
    <li class="breadcrumb-item active">{{ $paymentGateway->display_name }}</li>
@endsection

@include('admin.shared.modern-ui')

@push('styles')
<style>
.record-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.record-section-header {
    background: #f8f9fc;
    color: #1a202c;
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.record-section-header h4,
.record-section-header i {
    color: #1a202c !important;
}

.record-section-body {
    padding: 2rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #5a5c69;
    min-width: 150px;
}

.info-value {
    color: #858796;
    flex: 1;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
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
    color: white;
}

.btn-secondary {
    background: #858796;
    border: none;
    color: white;
}

.btn-secondary:hover {
    background: #5a5c69;
    color: white;
}

.btn-danger {
    background: #e74a3b;
    border: none;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    color: white;
}

.quick-info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.quick-info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.timeline {
    position: relative;
}

.timeline-item {
    display: flex;
    margin-bottom: 1rem;
}

.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 1rem;
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
}

@media (max-width: 768px) {
    .action-buttons {
        justify-content: center;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <h1 class="modern-page-title">
                <i class="fas fa-credit-card" style="color: #1a202c;"></i>
                Payment Gateway Details
            </h1>
            <p class="modern-page-subtitle">Comprehensive gateway configuration and status information</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Gateway Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-info-circle me-2" style="color: #1a202c;"></i>Gateway Information</h4>
                    <small style="color: #4a5568;">Basic gateway details and configuration</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-cogs me-1"></i>Provider:</div>
                        <div class="info-value"><strong>{{ ucfirst($paymentGateway->provider) }}</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-tag me-1"></i>Display Name:</div>
                        <div class="info-value">{{ $paymentGateway->display_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-info-circle me-1"></i>Status:</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $paymentGateway->is_active ? 'success' : 'secondary' }}">
                                <i class="fas fa-{{ $paymentGateway->is_active ? 'check' : 'times' }} me-1"></i>
                                {{ $paymentGateway->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-star me-1"></i>Default Gateway:</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $paymentGateway->is_default ? 'primary' : 'secondary' }}">
                                <i class="fas fa-{{ $paymentGateway->is_default ? 'star' : 'star-o' }} me-1"></i>
                                {{ $paymentGateway->is_default ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-flask me-1"></i>Mode:</div>
                        <div class="info-value">
                            <span class="badge bg-{{ $paymentGateway->test_mode ? 'warning' : 'success' }}">
                                <i class="fas fa-{{ $paymentGateway->test_mode ? 'flask' : 'globe' }} me-1"></i>
                                {{ $paymentGateway->test_mode ? 'Test Mode' : 'Live Mode' }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-sort-numeric-up me-1"></i>Sort Order:</div>
                        <div class="info-value">{{ $paymentGateway->sort_order }}</div>
                    </div>
                    @if($paymentGateway->description)
                    <div class="mt-4">
                        <h6><i class="fas fa-align-left me-1"></i>Description</h6>
                        <div class="bg-light p-3 rounded">{{ $paymentGateway->description }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Transaction Fees -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-percentage me-2" style="color: #1a202c;"></i>Transaction Fees</h4>
                    <small style="color: #4a5568;">Fee structure and calculation details</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-percent me-1"></i>Percentage Fee:</div>
                        <div class="info-value">{{ $paymentGateway->transaction_fee_percentage }}%</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-dollar-sign me-1"></i>Fixed Fee:</div>
                        <div class="info-value">${{ number_format($paymentGateway->transaction_fee_fixed, 2) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calculator me-1"></i>Total Fee (per $100):</div>
                        <div class="info-value">
                            <strong>${{ number_format((($paymentGateway->transaction_fee_percentage * 100) / 100) + $paymentGateway->transaction_fee_fixed, 2) }}</strong>
                        </div>
                    </div>
                    
                    @if($paymentGateway->transaction_fee_percentage > 0 || $paymentGateway->transaction_fee_fixed > 0)
                    <div class="mt-4">
                        <h6><i class="fas fa-chart-line me-1"></i>Fee Calculation Examples</h6>
                        <div class="bg-light p-3 rounded">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <strong>$50 Transaction</strong><br>
                                    <small class="text-muted">
                                        ${{ number_format((($paymentGateway->transaction_fee_percentage * 50) / 100) + $paymentGateway->transaction_fee_fixed, 2) }} fee
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <strong>$100 Transaction</strong><br>
                                    <small class="text-muted">
                                        ${{ number_format((($paymentGateway->transaction_fee_percentage * 100) / 100) + $paymentGateway->transaction_fee_fixed, 2) }} fee
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <strong>$500 Transaction</strong><br>
                                    <small class="text-muted">
                                        ${{ number_format((($paymentGateway->transaction_fee_percentage * 500) / 100) + $paymentGateway->transaction_fee_fixed, 2) }} fee
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Technical Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-cog me-2" style="color: #1a202c;"></i>Technical Information</h4>
                    <small style="color: #4a5568;">System details and timestamps</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-plus me-1"></i>Created:</div>
                        <div class="info-value">{{ formatDateTime($paymentGateway->created_at) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-edit me-1"></i>Last Updated:</div>
                        <div class="info-value">
                            {{ formatDateTime($paymentGateway->updated_at) }}
                            @if($paymentGateway->updated_at != $paymentGateway->created_at)
                                <small class="text-muted">({{ $paymentGateway->updated_at->diffForHumans() }})</small>
                            @endif
                        </div>
                    </div>
                    @if(isset($paymentGateway->webhook_url))
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-link me-1"></i>Webhook URL:</div>
                        <div class="info-value">
                            @if($paymentGateway->webhook_url)
                                <code>{{ $paymentGateway->webhook_url }}</code>
                            @else
                                <span class="text-muted">Not configured</span>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-key me-1"></i>Credentials:</div>
                        <div class="info-value">
                            @if($paymentGateway->credentials && count($paymentGateway->credentials) > 0)
                                <span class="badge bg-success">{{ count($paymentGateway->credentials) }} credentials configured</span>
                            @else
                                <span class="badge bg-warning">No credentials configured</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="quick-info-card">
                <h6>Quick Actions</h6>
                <div class="action-buttons">
                    <a href="{{ route('admin.payment-gateways.edit', $paymentGateway) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Gateway
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="testConnection()">
                        <i class="fas fa-plug"></i> Test Connection
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteGateway({{ $paymentGateway->id }})">
                        <i class="fas fa-trash"></i> Delete Gateway
                    </button>
                </div>
            </div>

            <div class="quick-info-card">
                <h6>Gateway Statistics</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">0</h4>
                            <small class="text-muted">Total Transactions</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">$0.00</h4>
                        <small class="text-muted">Total Revenue</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-info">0</h4>
                        <small class="text-muted">Successful</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-danger">0</h4>
                        <small class="text-muted">Failed</small>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">Statistics will be available once transactions are processed through this gateway.</small>
                </div>
            </div>

            <div class="quick-info-card">
                <h6>Gateway Features</h6>
                <div class="mb-3">
                    <strong>Supported Methods:</strong><br>
                    @php
                        $methods = [];
                        if (in_array($paymentGateway->provider, ['stripe', 'paypal', 'paystack', 'razorpay', 'flutterwave'])) {
                            $methods = ['Credit Cards', 'Debit Cards'];
                        }
                        
                        switch($paymentGateway->provider) {
                            case 'paypal':
                                $methods[] = 'PayPal';
                                break;
                            case 'stripe':
                                $methods[] = 'Digital Wallets';
                                break;
                            case 'coingate':
                                $methods = ['Bitcoin', 'Ethereum', 'Litecoin', 'Bitcoin Cash', 'Ripple', 'Cardano', 'Polkadot', 'USDT', 'USDC'];
                                break;
                            case 'btcpay':
                                $methods = ['Bitcoin', 'Lightning Network', 'Ethereum', 'Litecoin', 'Bitcoin Cash', 'Monero', 'Dash', 'Zcash', 'Dogecoin'];
                                break;
                        }
                    @endphp
                    @foreach ($methods as $method)
                        <span class="badge bg-success me-1">{{ $method }}</span>
                    @endforeach
                </div>
                <div class="mb-3">
                    <strong>Security Features:</strong><br>
                    <span class="badge bg-info me-1">SSL Encryption</span>
                    <span class="badge bg-info me-1">PCI Compliant</span>
                    <span class="badge bg-info me-1">Fraud Detection</span>
                    @if($paymentGateway->provider === 'btcpay')
                        <span class="badge bg-info me-1">Self-Hosted</span>
                        <span class="badge bg-info me-1">No Fees</span>
                        <span class="badge bg-info me-1">Privacy Focused</span>
                    @endif
                    @if($paymentGateway->provider === 'coingate')
                        <span class="badge bg-info me-1">Real-time Rates</span>
                        <span class="badge bg-info me-1">Auto Conversion</span>
                    @endif
                </div>
                <div>
                    <strong>Supported Currencies:</strong><br>
                    <span class="badge bg-secondary me-1">USD</span>
                    <span class="badge bg-secondary me-1">EUR</span>
                    <span class="badge bg-secondary me-1">GBP</span>
                </div>
            </div>

            <div class="quick-info-card">
                <h6>Recent Activity</h6>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <small class="text-muted">{{ $paymentGateway->created_at->diffForHumans() }}</small>
                            <p class="mb-0">Gateway created</p>
                        </div>
                    </div>
                    
                    @if($paymentGateway->updated_at != $paymentGateway->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $paymentGateway->updated_at->diffForHumans() }}</small>
                                <p class="mb-0">Configuration updated</p>
                            </div>
                        </div>
                    @endif

                    @if($paymentGateway->is_active)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Current</small>
                                <p class="mb-0">Gateway is active and ready</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="quick-info-card">
                <h6>Gateway Health</h6>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Connection Status:</span>
                    <span class="badge bg-{{ $paymentGateway->is_active ? 'success' : 'warning' }}">
                        {{ $paymentGateway->is_active ? 'Connected' : 'Inactive' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Configuration:</span>
                    <span class="badge bg-{{ $paymentGateway->credentials ? 'success' : 'danger' }}">
                        {{ $paymentGateway->credentials ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span>Mode:</span>
                    <span class="badge bg-{{ $paymentGateway->test_mode ? 'warning' : 'primary' }}">
                        {{ $paymentGateway->test_mode ? 'Test' : 'Production' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function testConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
    
    // Simulate API call - replace with actual test endpoint
    setTimeout(() => {
        const isSuccess = Math.random() > 0.3; // 70% success rate for demo
        
        if (isSuccess) {
            alert('✅ Connection Test Successful!\n\nGateway is properly configured and ready to process transactions.');
        } else {
            alert('❌ Connection Test Failed!\n\nPlease check your credentials and gateway configuration.');
        }
        
        button.disabled = false;
        button.innerHTML = originalText;
    }, 2000);
}

function deleteGateway(gatewayId) {
    console.log('Delete gateway called with ID:', gatewayId);
    
    // Prevent any default behavior if event exists
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }
    
    // Handle both sync and async confirm dialogs
    function handleConfirmation(confirmResult) {
        console.log('User confirmation result:', confirmResult);
        
        if (confirmResult === true) {
            console.log('User confirmed deletion, proceeding...');
            
            // Add a small delay to ensure the dialog is properly closed
            setTimeout(() => {
                console.log('Creating form for deletion...');
                
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/payment-gateways/${gatewayId}`;
                form.style.display = 'none';
                
                // Add CSRF token - try multiple methods
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                
                // Try to get CSRF token from meta tag or Laravel's global
                let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                    csrfTokenValue = Laravel.csrfToken;
                }
                if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                    csrfTokenValue = window.Laravel.csrfToken;
                }
                
                csrfToken.value = csrfTokenValue;
                form.appendChild(csrfToken);
                
                console.log('CSRF token:', csrfTokenValue);
                
                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                console.log('Form children:', form.children);
                
                // Add form to document and submit
                document.body.appendChild(form);
                console.log('Form added to document, submitting...');
                form.submit();
            }, 100);
        } else {
            console.log('User cancelled deletion');
        }
    }
    
    // Use a more explicit confirmation dialog
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this payment gateway?\n\nThis action cannot be undone and will remove:\n- Gateway configuration\n- API credentials\n- Transaction history\n- All associated data\n\nClick OK to confirm deletion or Cancel to abort.');
    
    // Handle both Promise and boolean returns
    if (confirmDelete && typeof confirmDelete.then === 'function') {
        // If it's a Promise, wait for it to resolve
        confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
    } else {
        // If it's a boolean, handle it directly
        handleConfirmation(confirmDelete);
    }
    
    return false;
}
</script>
@endpush
