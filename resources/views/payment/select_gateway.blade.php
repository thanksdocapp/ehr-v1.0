@extends('patient.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Select Payment Gateway - Invoice #' . $invoice->invoice_number)
@section('page-title', 'Select Payment Gateway - Invoice #' . $invoice->invoice_number)

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Gateway Selection Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Select Payment Gateway
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('patient.billing.select-gateway', $invoice) }}" id="gateway-form">
                        @csrf
                        
                        @if($gateways->count() > 0)
                            <div class="mb-4">
                                <p class="text-muted mb-3">Please select your preferred payment method to proceed with the payment.</p>
                                
                                @foreach($gateways as $gateway)
                                    <div class="form-check mb-3 p-3 border rounded gateway-option">
                                        <input class="form-check-input" type="radio" name="payment_gateway" 
                                               id="gateway_{{ $gateway->provider }}" value="{{ $gateway->provider }}" required>
                                        <label class="form-check-label w-100" for="gateway_{{ $gateway->provider }}">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    @if($gateway->provider === 'credit_card')
                                                        <i class="fas fa-credit-card text-primary fa-2x"></i>
                                                    @elseif($gateway->provider === 'paypal')
                                                        <i class="fab fa-paypal text-info fa-2x"></i>
                                                    @elseif($gateway->provider === 'crypto' || $gateway->provider === 'btcpay' || $gateway->provider === 'coingate')
                                                        <i class="fab fa-bitcoin text-warning fa-2x"></i>
                                                    @else
                                                        <i class="fas fa-money-check-alt text-success fa-2x"></i>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">{{ $gateway->display_name }}</h6>
                                                    @if($gateway->description)
                                                        <small class="text-muted">{{ $gateway->description }}</small>
                                                    @endif
                                                    @if($gateway->transaction_fee_percentage > 0 || $gateway->transaction_fee_fixed > 0)
                                                        <div class="mt-1">
                                                            <small class="text-warning">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Fee: 
                                                                @if($gateway->transaction_fee_percentage > 0)
                                                                    {{ $gateway->transaction_fee_percentage }}%
                                                                @endif
                                                                @if($gateway->transaction_fee_fixed > 0)
                                                                    @if($gateway->transaction_fee_percentage > 0) + @endif
                                                                    {{ CurrencyHelper::format($gateway->transaction_fee_fixed) }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($gateway->is_default)
                                                    <div class="ms-auto">
                                                        <span class="badge bg-primary">Default</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No payment gateways are currently available. Please contact support for assistance.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('patient.billing.show', $invoice) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Invoice
                            </a>
                            @if($gateways->count() > 0)
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-arrow-right me-1"></i>
                                    Continue to Payment
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Invoice Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Invoice Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Invoice Number:</span>
                        <strong>{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Invoice Date:</span>
                        <span>{{ $invoice->invoice_date->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Due Date:</span>
                        <span class="{{ $invoice->due_date->lt(today()) && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                            {{ $invoice->due_date->format('M d, Y') }}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Amount:</span>
                        <strong>{{ CurrencyHelper::format($invoice->total_amount) }}</strong>
                    </div>
                    @if($invoice->payments->where('status', 'completed')->count() > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Amount Paid:</span>
                            <span>{{ CurrencyHelper::format($invoice->payments->where('status', 'completed')->sum('amount')) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-3">
                        <span><strong>Outstanding:</strong></span>
                        <strong class="text-danger">{{ CurrencyHelper::format($invoice->outstanding_amount) }}</strong>
                    </div>

                    @if($invoice->invoiceItems && $invoice->invoiceItems->count() > 0)
                        <hr>
                        <h6>Invoice Items:</h6>
                        @foreach($invoice->invoiceItems as $item)
                            <div class="d-flex justify-content-between mb-1">
                                <small>{{ $item->description }}</small>
                                <small>{{ CurrencyHelper::format($item->amount) }}</small>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Security Notice -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <h6 class="mb-0">Secure Payment</h6>
                    </div>
                    <small class="text-muted">
                        Your payment information is encrypted and secure. We use industry-standard security measures to protect your data.
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to gateway options
        const gatewayOptions = document.querySelectorAll('.gateway-option');
        gatewayOptions.forEach(option => {
            option.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            option.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });

        // Form submission
        document.getElementById('gateway-form').addEventListener('submit', function(e) {
            const selectedGateway = document.querySelector('input[name="payment_gateway"]:checked');
            
            if (!selectedGateway) {
                e.preventDefault();
                alert('Please select a payment gateway to continue.');
                return;
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
            }
        });
    });
</script>
@endpush

