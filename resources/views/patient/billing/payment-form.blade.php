@extends('patient.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Payment - Invoice #' . $invoice->invoice_number)
@section('page-title', 'Payment - Invoice #' . $invoice->invoice_number)

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Payment Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card me-2"></i>
                        Payment Details
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('patient.billing.process-payment', $invoice) }}" id="payment-form">
                        @csrf
                        
                        <!-- Selected Gateway Information -->
                        @if(isset($selectedGateway))
                            <div class="alert alert-info mb-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        @if($selectedGateway->provider === 'credit_card')
                                            <i class="fas fa-credit-card text-primary fa-2x"></i>
                                        @elseif($selectedGateway->provider === 'paypal')
                                            <i class="fab fa-paypal text-info fa-2x"></i>
                                        @elseif(in_array($selectedGateway->provider, ['crypto', 'btcpay', 'coingate']))
                                            <i class="fab fa-bitcoin text-warning fa-2x"></i>
                                        @else
                                            <i class="fas fa-money-check-alt text-success fa-2x"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">Payment Method: {{ $selectedGateway->display_name }}</h6>
                                        @if($selectedGateway->description)
                                            <small class="text-muted">{{ $selectedGateway->description }}</small>
                                        @endif
                                    </div>
                                    <a href="{{ route('patient.billing.pay', $invoice) }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit me-1"></i>Change
                                    </a>
                                </div>
                            </div>
                            <input type="hidden" name="payment_gateway" value="{{ $selectedGateway->provider }}">
                            <input type="hidden" name="payment_method" value="{{ $selectedGateway->provider }}">
                        @endif
                        
                        <div class="row">
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ CurrencyHelper::getCurrencySymbol() }}</span>
                                        <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                               step="0.01" min="0.01" max="{{ $invoice->outstanding_amount }}" 
                                               value="{{ old('amount', $invoice->outstanding_amount) }}" required>
                                    </div>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Maximum: {{ CurrencyHelper::format($invoice->outstanding_amount) }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Specific Fields -->
                        <div id="card-details" class="payment-details" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input type="text" id="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="expiry" class="form-label">Expiry Date</label>
                                        <input type="text" id="expiry" class="form-control" placeholder="MM/YY" maxlength="5">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" id="cvv" class="form-control" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="cardholder_name" class="form-label">Cardholder Name</label>
                                        <input type="text" id="cardholder_name" class="form-control" placeholder="John Doe">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="bank-details" class="payment-details" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Please transfer the payment amount to our bank account and provide the transaction reference below.
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="bank_name" class="form-label">Bank Name</label>
                                        <input type="text" id="bank_name" class="form-control" placeholder="Your Bank Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="transaction_ref" class="form-label">Transaction Reference</label>
                                        <input type="text" id="transaction_ref" class="form-control" placeholder="Transaction Reference Number">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Any additional notes or comments">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('patient.billing.show', $invoice) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back to Invoice
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-credit-card me-1"></i>
                                Process Payment ({{ CurrencyHelper::getCurrencySymbol() }}<span id="payment-amount">{{ number_format($invoice->outstanding_amount, 2) }}</span>)
                            </button>
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
                        Your payment information is encrypted and secure. We never store your credit card details.
                    </small>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="mb-3">Accepted Payment Methods</h6>
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <i class="fas fa-credit-card text-primary fa-2x"></i>
                            <small class="d-block mt-1">Credit Cards</small>
                        </div>
                        <div class="col-6 mb-2">
                            <i class="fas fa-university text-info fa-2x"></i>
                            <small class="d-block mt-1">Bank Transfer</small>
                        </div>
                        <div class="col-6 mb-2">
                            <i class="fas fa-money-bill text-success fa-2x"></i>
                            <small class="d-block mt-1">Cash</small>
                        </div>
                        <div class="col-6 mb-2">
                            <i class="fas fa-shield-alt text-warning fa-2x"></i>
                            <small class="d-block mt-1">Insurance</small>
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
        const amountInput = document.getElementById('amount');
        const paymentAmountSpan = document.getElementById('payment-amount');
        const selectedGateway = document.querySelector('input[name="payment_gateway"]');
        
        // Show gateway-specific fields on page load
        if (selectedGateway) {
            const gatewayType = selectedGateway.value;
            
            // Hide all payment details first
            document.querySelectorAll('.payment-details').forEach(function(element) {
                element.style.display = 'none';
            });
            
            // Show relevant payment details based on selected gateway
            if (gatewayType === 'credit_card' || gatewayType === 'debit_card') {
                document.getElementById('card-details').style.display = 'block';
            } else if (gatewayType === 'bank_transfer') {
                document.getElementById('bank-details').style.display = 'block';
            }
        }
        
        // Update payment amount display
        if (amountInput && paymentAmountSpan) {
            amountInput.addEventListener('input', function() {
                const amount = parseFloat(this.value) || 0;
                paymentAmountSpan.textContent = amount.toFixed(2);
            });
        }
        
        // Card number formatting
        const cardNumberInput = document.getElementById('card_number');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function() {
                let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let matches = value.match(/\d{4,16}/g);
                let match = matches && matches[0] || '';
                let parts = [];
                
                for (let i = 0, len = match.length; i < len; i += 4) {
                    parts.push(match.substring(i, i + 4));
                }
                
                if (parts.length) {
                    this.value = parts.join(' ');
                } else {
                    this.value = value;
                }
            });
        }
        
        // Expiry date formatting
        const expiryInput = document.getElementById('expiry');
        if (expiryInput) {
            expiryInput.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    this.value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
            });
        }
        
        // CVV input - only numbers
        const cvvInput = document.getElementById('cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });
        }
        
        // Form submission
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            const paymentMethod = selectedGateway ? selectedGateway.value : null;
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Payment method not selected. Please go back and select a payment gateway.');
                return;
            }
            
            if (!amount || amount <= 0) {
                e.preventDefault();
                alert('Please enter a valid payment amount.');
                return;
            }
            
            if (amount > {{ $invoice->outstanding_amount }}) {
                e.preventDefault();
                alert('Payment amount cannot exceed the outstanding balance.');
                return;
            }
            
            // Additional validation for card payments
            if (paymentMethod === 'credit_card' || paymentMethod === 'debit_card') {
                const cardNumber = document.getElementById('card_number').value.replace(/\s+/g, '');
                const expiry = document.getElementById('expiry').value;
                const cvv = document.getElementById('cvv').value;
                const cardholderName = document.getElementById('cardholder_name').value;
                
                if (!cardNumber || cardNumber.length < 13) {
                    e.preventDefault();
                    alert('Please enter a valid card number.');
                    return;
                }
                
                if (!expiry || expiry.length !== 5) {
                    e.preventDefault();
                    alert('Please enter a valid expiry date (MM/YY).');
                    return;
                }
                
                if (!cvv || cvv.length < 3) {
                    e.preventDefault();
                    alert('Please enter a valid CVV.');
                    return;
                }
                
                if (!cardholderName.trim()) {
                    e.preventDefault();
                    alert('Please enter the cardholder name.');
                    return;
                }
            }
            
            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
            }
        });
    });
</script>
@endpush
