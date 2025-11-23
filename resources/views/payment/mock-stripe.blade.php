@extends('layouts.patient')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Mock Stripe Payment')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Test Payment Page (Mock)
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>This is a test payment page.</strong> 
                        You're seeing this because test Stripe API keys are not configured or are invalid.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Mock Payment Form</h5>
                            <p class="text-muted">This simulates the Stripe payment flow for testing purposes.</p>
                            
                            <form id="mock-payment-form">
                                <div class="mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" value="4242 4242 4242 4242" readonly>
                                    <small class="text-muted">Test card number</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Expiry Date</label>
                                            <input type="text" class="form-control" value="12/25" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">CVC</label>
                                            <input type="text" class="form-control" value="123" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" value="Test User" readonly>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-lightbulb me-2"></i>To set up real Stripe payments:</h6>
                                    <ol class="mb-2">
                                        <li>Get your Stripe test keys from <a href="https://stripe.com" target="_blank">stripe.com</a></li>
                                        <li>Go to Dashboard → Developers → API Keys (Test mode ON)</li>
                                        <li>Copy <strong>Publishable Key</strong> (starts with <code>pk_test_</code>)</li>
                                        <li>Copy <strong>Secret Key</strong> (starts with <code>sk_test_</code>)</li>
                                        <li>Add them to your payment gateway configuration in the admin panel</li>
                                    </ol>
                                    
                                    <div class="alert alert-info mt-3 mb-0">
                                        <h6><i class="fas fa-info-circle me-2"></i>About Webhook Secret:</h6>
                                        <p class="mb-2"><strong>Webhook Secret is optional for testing!</strong></p>
                                        <p class="mb-2">If you want to set it up for complete integration:</p>
                                        <ol class="mb-1">
                                            <li>Go to Dashboard → Developers → Webhooks</li>
                                            <li>Click "Add endpoint"</li>
                                            <li>Use URL: <code>{{ url('/payment/webhook/stripe') }}</code></li>
                                            <li>Select events: <code>payment_intent.succeeded</code>, <code>payment_intent.payment_failed</code></li>
                                            <li>Copy the "Signing secret" (starts with <code>whsec_</code>)</li>
                                        </ol>
                                        <small class="text-muted">You can leave webhook secret empty for basic testing.</small>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <button type="button" id="mock-pay-button" class="btn btn-success btn-lg">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Simulate Successful Payment
                                    </button>
                                    <button type="button" id="mock-fail-button" class="btn btn-danger btn-lg">
                                        <i class="fas fa-times-circle me-2"></i>
                                        Simulate Failed Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Payment Summary</h5>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Amount</small>
                                        <div class="fw-bold">{{ CurrencyHelper::getCurrencySymbol() }}{{ request('amount', '0.00') }}</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Payment Intent</small>
                                        <div class="fw-bold">{{ request('intent', 'pi_mock_' . uniqid()) }}</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted">Status</small>
                                        <div class="fw-bold">
                                            <span class="badge bg-warning">Test Mode</span>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-vial text-warning me-2"></i>
                                        <small class="text-muted">Mock Payment System</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mockPayButton = document.getElementById('mock-pay-button');
    const mockFailButton = document.getElementById('mock-fail-button');
    
    mockPayButton.addEventListener('click', function() {
        // Simulate processing
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        this.disabled = true;
        
        setTimeout(() => {
            // Redirect to success page
            window.location.href = '{{ route("payment.success") }}?transaction_id=mock_' + Date.now();
        }, 2000);
    });
    
    mockFailButton.addEventListener('click', function() {
        // Simulate processing
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        this.disabled = true;
        
        setTimeout(() => {
            // Redirect to cancelled page
            window.location.href = '{{ route("payment.cancelled") }}?reason=test_failure';
        }, 2000);
    });
});
</script>
@endsection
