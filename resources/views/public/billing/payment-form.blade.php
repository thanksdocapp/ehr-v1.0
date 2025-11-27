@extends('layouts.guest')

@push('styles')
<style>
    #proceed-payment-btn {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        z-index: 999 !important;
    }
    
    #proceed-payment-form {
        display: block !important;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Complete Payment
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        You will be redirected to {{ $selectedGateway->name }} to complete your payment securely.
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Invoice</h6>
                        <p class="mb-0"><strong>{{ $invoice->invoice_number }}</strong></p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Amount to Pay</h6>
                        <p class="mb-0 fs-4 fw-bold text-primary">${{ number_format($invoice->outstanding_amount, 2) }}</p>
                    </div>

                    <form method="POST" action="{{ route('public.billing.process-payment', ['token' => $token]) }}" id="proceed-payment-form">
                        @csrf
                        <input type="hidden" name="payment_gateway" value="{{ $selectedGateway->provider }}">
                        <input type="hidden" name="payment_method" value="card">
                        <input type="hidden" name="amount" value="{{ $invoice->outstanding_amount }}">

                        <div class="d-grid gap-2">
                            <button type="submit" id="proceed-payment-btn" class="btn btn-primary btn-lg" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                                <i class="fas fa-lock me-2"></i>
                                Proceed to {{ $selectedGateway->name }} Secure Payment
                            </button>
                            <a href="{{ route('public.billing.pay', ['token' => $token]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Invoice
                            </a>
                        </div>
                    </form>
                    
                    @push('scripts')
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('proceed-payment-form');
                            const submitBtn = document.getElementById('proceed-payment-btn');
                            
                            console.log('Payment form page loaded');
                            console.log('Form:', form);
                            console.log('Submit button:', submitBtn);
                            
                            if (form && submitBtn) {
                                // Force button visibility
                                submitBtn.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 999 !important;';
                                submitBtn.disabled = false;
                                
                                // Remove any hidden attributes
                                submitBtn.removeAttribute('hidden');
                                submitBtn.removeAttribute('style');
                                submitBtn.style.display = 'block';
                                submitBtn.style.visibility = 'visible';
                                submitBtn.style.opacity = '1';
                                submitBtn.style.position = 'relative';
                                submitBtn.style.zIndex = '999';
                                
                                console.log('Button styles applied');
                                
                                // Ensure form submits properly
                                form.addEventListener('submit', function(e) {
                                    console.log('Form submit event triggered');
                                    submitBtn.disabled = false;
                                    submitBtn.style.display = 'block';
                                    submitBtn.style.visibility = 'visible';
                                    submitBtn.style.opacity = '1';
                                    
                                    // Validate required fields
                                    const gateway = form.querySelector('input[name="payment_gateway"]');
                                    const amount = form.querySelector('input[name="amount"]');
                                    
                                    if (!gateway || !gateway.value) {
                                        e.preventDefault();
                                        alert('Payment gateway is required');
                                        return false;
                                    }
                                    
                                    if (!amount || !amount.value || parseFloat(amount.value) <= 0) {
                                        e.preventDefault();
                                        alert('Invalid payment amount');
                                        return false;
                                    }
                                    
                                    console.log('Form validation passed, submitting...');
                                    return true;
                                });
                                
                                // Also add click handler as backup
                                submitBtn.addEventListener('click', function(e) {
                                    console.log('Button clicked');
                                    const form = this.closest('form');
                                    if (form) {
                                        console.log('Submitting form via click handler');
                                        form.submit();
                                    }
                                });
                            } else {
                                console.error('Form or submit button not found!');
                            }
                        });
                    </script>
                    @endpush
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

