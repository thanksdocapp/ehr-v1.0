@extends('patient.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'BTCPay Payment - Invoice #' . $invoice->invoice_number)
@section('page-title', 'BTCPay Payment Processing')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header text-center bg-warning">
                    <h5 class="card-title mb-0">
                        <i class="fab fa-bitcoin me-2"></i>
                        BTCPay Server Payment
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fab fa-bitcoin text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h4 class="mb-3">Payment Amount: {{ CurrencyHelper::format($amount) }}</h4>
                    <p class="text-muted mb-4">Invoice #{{ $invoice->invoice_number }}</p>
                    
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle me-2"></i>Payment Instructions</h5>
                        <ol class="text-start">
                            <li>Click the "Open BTCPay Payment" button below</li>
                            <li>A new tab will open with your BTCPay payment page</li>
                            <li>Complete your payment in the BTCPay tab</li>
                            <li>Return to this page - it will automatically update when payment is confirmed</li>
                        </ol>
                    </div>
                    
                    <div class="mb-4">
                        <a href="{{ $payment_url }}" target="_blank" class="btn btn-warning btn-lg" id="open-btcpay">
                            <i class="fab fa-bitcoin me-2"></i>
                            Open BTCPay Payment
                        </a>
                    </div>
                    
                    <div class="payment-status mt-4" id="payment-status">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="spinner-border text-warning me-3" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div>
                                <h6 class="mb-0">Waiting for payment confirmation...</h6>
                                <small class="text-muted">This page will update automatically</small>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Payment usually takes 1-10 minutes to confirm
                            </small>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('patient.billing.show', $invoice) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Invoice
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Payment not working?</strong><br>
                                • Check if pop-ups are blocked<br>
                                • Try refreshing the BTCPay tab<br>
                                • Contact support if issues persist
                            </small>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Payment Status:</strong><br>
                                • Pending: Waiting for confirmation<br>
                                • Completed: Payment successful<br>
                                • Failed: Payment unsuccessful
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentId = {{ $payment->id }};
    const invoiceId = {{ $invoice->id }};
    let checkInterval;
    let checkCount = 0;
    const maxChecks = 60; // Check for 10 minutes (every 10 seconds)
    
    // Auto-open BTCPay payment tab
    const openBtcpayBtn = document.getElementById('open-btcpay');
    setTimeout(() => {
        if (openBtcpayBtn) {
            openBtcpayBtn.click();
        }
    }, 1000);
    
    // Start checking payment status
    function startStatusCheck() {
        checkInterval = setInterval(checkPaymentStatus, 10000); // Check every 10 seconds
    }
    
    function checkPaymentStatus() {
        checkCount++;
        
        // Stop checking after max attempts
        if (checkCount >= maxChecks) {
            clearInterval(checkInterval);
            updateStatusDisplay('timeout');
            return;
        }
        
        fetch(`/patient/api/payment-status/${paymentId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'completed') {
                clearInterval(checkInterval);
                updateStatusDisplay('completed');
                
                // Redirect to invoice page after 3 seconds
                setTimeout(() => {
                    window.location.href = `{{ route('patient.billing.show', $invoice) }}?payment_success=1`;
                }, 3000);
            } else if (data.status === 'failed' || data.status === 'cancelled') {
                clearInterval(checkInterval);
                updateStatusDisplay('failed');
            }
            // Continue checking if still pending
        })
        .catch(error => {
            console.error('Error checking payment status:', error);
            // Continue checking on error
        });
    }
    
    function updateStatusDisplay(status) {
        const statusDiv = document.getElementById('payment-status');
        
        switch (status) {
            case 'completed':
                statusDiv.innerHTML = `
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-check-circle text-success me-3" style="font-size: 2rem;"></i>
                            <div>
                                <h6 class="mb-0 text-success">Payment Completed Successfully!</h6>
                                <small class="text-muted">Redirecting you back to the invoice...</small>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'failed':
                statusDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-times-circle text-danger me-3" style="font-size: 2rem;"></i>
                            <div>
                                <h6 class="mb-0 text-danger">Payment Failed</h6>
                                <small class="text-muted">Please try again or choose a different payment method</small>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'timeout':
                statusDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="fas fa-clock text-warning me-3" style="font-size: 2rem;"></i>
                            <div>
                                <h6 class="mb-0 text-warning">Payment Status Check Timeout</h6>
                                <small class="text-muted">Please check your BTCPay tab or refresh this page to check status</small>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-sm btn-warning" onclick="location.reload()">
                                <i class="fas fa-refresh me-1"></i>Refresh Status
                            </button>
                        </div>
                    </div>
                `;
                break;
        }
    }
    
    // Start checking after 5 seconds to give user time to start payment
    setTimeout(startStatusCheck, 5000);
    
    // Handle page visibility change - resume checking when page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && checkCount < maxChecks) {
            // Page became visible, check status immediately
            checkPaymentStatus();
        }
    });
    
    // Handle beforeunload to warn user about leaving
    window.addEventListener('beforeunload', function(e) {
        if (checkInterval && checkCount < maxChecks) {
            e.preventDefault();
            e.returnValue = 'Your payment is still being processed. Are you sure you want to leave?';
        }
    });
});
</script>
@endpush
