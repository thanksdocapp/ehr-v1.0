@extends('patient.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Stripe Payment')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Complete Your Payment
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="payment-element">
                                <!-- Stripe Elements will create form elements here -->
                            </div>
                            
                            <div id="payment-message" class="alert d-none mt-3"></div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button id="submit-payment" class="btn btn-primary btn-lg">
                                    <span id="button-text">
                                        <i class="fas fa-lock me-2"></i>
                                        Pay Now
                                    </span>
                                    <div id="spinner" class="spinner-border spinner-border-sm d-none ms-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Payment Summary</h5>
                                    @if(isset($transaction))
                                        <div class="mb-3">
                                            <small class="text-muted">Amount</small>
                                            <div class="fw-bold">{{ CurrencyHelper::format($transaction->amount) }}</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Currency</small>
                                            <div class="fw-bold">{{ strtoupper($transaction->currency) }}</div>
                                        </div>
                                        
                                        @if($transaction->billing)
                                            <div class="mb-3">
                                                <small class="text-muted">Bill Reference</small>
                                                <div class="fw-bold">#{{ $transaction->billing->id }}</div>
                                            </div>
                                        @endif
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Transaction ID</small>
                                            <div class="fw-bold">{{ $transaction->transaction_id }}</div>
                                        </div>
                                    @elseif(isset($payment))
                                        <div class="mb-3">
                                            <small class="text-muted">Amount</small>
                                            <div class="fw-bold">{{ CurrencyHelper::format($payment->amount) }}</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Invoice</small>
                                            <div class="fw-bold">#{{ $payment->invoice->invoice_number ?? $payment->invoice->id }}</div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">Transaction ID</small>
                                            <div class="fw-bold">{{ $payment->transaction_id }}</div>
                                        </div>
                                    @endif
                                    
                                    <hr>
                                    
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-shield-alt text-success me-2"></i>
                                        <small class="text-muted">Secured by Stripe</small>
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

<!-- Stripe JS -->
<script src="https://js.stripe.com/v3/"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Starting Stripe payment initialization...');
    
    try {
    // Initialize Stripe with publishable key from payment gateway
    @php
        $publishableKey = 'pk_test_your_publishable_key_here';
        
        // Handle both transaction (general payments) and payment (patient payments)
        if (isset($transaction) && $transaction->paymentGateway) {
            $publishableKey = $transaction->paymentGateway->credentials['publishable_key'] ?? $publishableKey;
        } elseif (isset($payment) && $payment->payment_gateway) {
            // For patient payments, get the gateway credentials
            $gateway = \App\Models\PaymentGateway::where('provider', $payment->payment_gateway)->first();
            if ($gateway) {
                $publishableKey = $gateway->credentials['publishable_key'] ?? $publishableKey;
            }
        }
    @endphp
    
    console.log('Initializing Stripe with key:', '{{ $publishableKey }}');
    console.log('Client secret:', '{{ $clientSecret ?? "pi_mock_client_secret" }}');
    
    // Validate client secret format
    const clientSecret = '{{ $clientSecret ?? "pi_mock_client_secret" }}';
    const isValidFormat = clientSecret.includes('_secret_');
    console.log('Client secret format valid:', isValidFormat);
    
    if (!isValidFormat) {
        console.error('Invalid client secret format:', clientSecret);
        showMessage('Invalid payment configuration. Please contact support.', 'danger');
        return;
    }
    
    // Check if Stripe is loaded
    if (typeof Stripe === 'undefined') {
        console.error('Stripe library not loaded');
        showMessage('Stripe library failed to load. Please refresh the page.', 'danger');
        return;
    }
    
    const stripe = Stripe('{{ $publishableKey }}');
    
    const elements = stripe.elements({
        clientSecret: '{{ $clientSecret ?? "pi_mock_client_secret" }}',
        appearance: {
            theme: 'stripe',
            variables: {
                colorPrimary: '#0066cc',
            }
        }
    });

let paymentElement;
    try {
        paymentElement = elements.create('payment');
        if (!paymentElement) throw new Error('Payment element creation failed');
    } catch (error) {
        console.error('Failed to create payment element:', error.message);
        showMessage('Failed to create payment form: ' + error.message, 'danger');
        return;
    }
    
    paymentElement.mount('#payment-element').then(function() {
        console.log('Payment element mounted successfully');
    }).catch(function(error) {
        console.error('Error mounting payment element:', error);
        showMessage('Failed to load payment form: ' + error.message, 'danger');
    });

    const submitButton = document.getElementById('submit-payment');
    const spinner = document.getElementById('spinner');
    const buttonText = document.getElementById('button-text');
    const messageContainer = document.getElementById('payment-message');
    
    console.log('Elements found:', {
        submitButton: !!submitButton,
        spinner: !!spinner,
        buttonText: !!buttonText,
        messageContainer: !!messageContainer
    });

    if (!submitButton) {
        console.error('Submit button not found!');
        return;
    }
    
    submitButton.addEventListener('click', async (e) => {
        console.log('Pay Now button clicked!');
        e.preventDefault();
        setLoading(true);
        
        // For mock payments, simulate success
        @if(str_starts_with($clientSecret ?? '', 'pi_mock_'))
            console.log('Processing mock payment...');
            setTimeout(() => {
                showMessage('Payment completed successfully! (Mock Payment)', 'success');
                setTimeout(() => {
                    @if(isset($payment))
                        window.location.href = '{{ route("patient.billing.show", $payment->invoice->id) }}?payment_success=1';
                    @else
                        window.location.href = '{{ route("payment.success") }}?transaction_id={{ $transaction->transaction_id ?? "mock" }}';
                    @endif
                }, 2000);
            }, 2000);
            return;
        @endif

        const {error} = await stripe.confirmPayment({
            elements,
            confirmParams: {
                @if(isset($payment))
                    return_url: '{{ route("patient.billing.show", $payment->invoice->id) }}?payment_success=1',
                @else
                    return_url: '{{ route("payment.success") }}?transaction_id={{ $transaction->transaction_id ?? "" }}',
                @endif
            },
        });

        if (error) {
            if (error.type === "card_error" || error.type === "validation_error") {
                showMessage(error.message, 'danger');
            } else {
                showMessage("An unexpected error occurred.", 'danger');
            }
        }

        setLoading(false);
    });

    function setLoading(isLoading) {
        if (isLoading) {
            submitButton.disabled = true;
            spinner.classList.remove('d-none');
            buttonText.innerHTML = '<i class="fas fa-lock me-2"></i>Processing...';
        } else {
            submitButton.disabled = false;
            spinner.classList.add('d-none');
            buttonText.innerHTML = '<i class="fas fa-lock me-2"></i>Pay Now';
        }
    }

    function showMessage(message, type = 'info') {
        if (messageContainer) {
            messageContainer.className = `alert alert-${type}`;
            messageContainer.textContent = message;
            messageContainer.classList.remove('d-none');
            
            setTimeout(() => {
                messageContainer.classList.add('d-none');
            }, 5000);
        } else {
            console.error('Message container not found');
        }
    }
    
    } catch (error) {
        console.error('Error initializing Stripe payment:', error);
    }
});
</script>
@endsection
