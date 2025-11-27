@extends('layouts.guest')

@push('styles')
<style>
    .invoice-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px 16px 0 0;
        padding: 2rem;
        color: white;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }
    
    .invoice-card {
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        border: none;
        overflow: hidden;
    }
    
    .invoice-body {
        padding: 2rem;
    }
    
    .info-section {
        background: #f8f9fc;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .amount-display {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
    }
    
    .gateway-card {
        border: 2px solid #e3e6f0;
        border-radius: 12px;
        padding: 1.25rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .gateway-card:hover {
        border-color: #667eea;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
        transform: translateY(-2px);
    }
    
    .gateway-card input[type="radio"]:checked + label {
        color: #667eea;
        font-weight: 600;
    }
    
    .gateway-card.border-primary {
        border-color: #667eea !important;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
    }
    
    .btn-pay {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        z-index: 999 !important;
    }
    
    .btn-pay:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    #pay-invoice-btn {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
        z-index: 999 !important;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="invoice-card">
                <div class="invoice-header">
                    <h3 class="mb-2">
                        <i class="fas fa-file-invoice me-2"></i>
                        Invoice #{{ $invoice->invoice_number }}
                    </h3>
                    <p class="mb-0 opacity-90">Secure Payment Portal</p>
                </div>
                <div class="invoice-body">
                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Invoice Number</h6>
                            <h5 class="text-primary">{{ $invoice->invoice_number }}</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            @php
                                $statusClass = match($invoice->status) {
                                    'paid' => 'bg-success',
                                    'pending' => $invoice->due_date && $invoice->due_date->lt(today()) ? 'bg-danger' : 'bg-warning',
                                    'partial' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                $statusText = match($invoice->status) {
                                    'paid' => 'Paid',
                                    'pending' => $invoice->due_date && $invoice->due_date->lt(today()) ? 'Overdue' : 'Pending',
                                    'partial' => 'Partially Paid',
                                    default => ucfirst($invoice->status)
                                };
                            @endphp
                            <h6 class="text-muted mb-2">Status</h6>
                            <span class="badge {{ $statusClass }} fs-6 px-3 py-2">{{ $statusText }}</span>
                        </div>
                    </div>

                    <!-- Patient Info -->
                    @if($invoice->patient)
                    <div class="info-section">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-user me-2"></i>Bill To
                        </h6>
                        <p class="mb-1">
                            <strong>{{ $invoice->patient->full_name }}</strong>
                        </p>
                        @if($invoice->patient->email)
                            <p class="mb-1 text-muted">
                                <i class="fas fa-envelope me-1"></i>{{ $invoice->patient->email }}
                            </p>
                        @endif
                        @if($invoice->patient->phone)
                            <p class="mb-0 text-muted">
                                <i class="fas fa-phone me-1"></i>{{ $invoice->patient->phone }}
                            </p>
                        @endif
                    </div>
                    @endif

                    <!-- Invoice Details -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Invoice Date</h6>
                            <p class="mb-0">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Due Date</h6>
                            <p class="mb-0 {{ $invoice->due_date && $invoice->due_date->lt(today()) && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                                {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                                @if($invoice->due_date && $invoice->due_date->lt(today()) && $invoice->status !== 'paid')
                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Total Amount</h6>
                            <p class="mb-0 amount-display">${{ number_format($invoice->total_amount, 2) }}</p>
                        </div>
                    </div>

                    @if($invoice->description)
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Description</h6>
                        <p class="mb-0">{{ $invoice->description }}</p>
                    </div>
                    @endif

                    <!-- Amount Breakdown -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">${{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td>Discount</td>
                                    <td class="text-end text-success">-${{ number_format($invoice->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td>Tax</td>
                                    <td class="text-end">${{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td><strong>Total</strong></td>
                                    <td class="text-end"><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                                @if($invoice->paid_amount > 0)
                                <tr>
                                    <td>Paid Amount</td>
                                    <td class="text-end text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Balance Due</strong></td>
                                    <td class="text-end"><strong>${{ number_format($invoice->outstanding_amount, 2) }}</strong></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Payment Section -->
                    @if($invoice->status !== 'paid' && $invoice->outstanding_amount > 0)
                    <div class="border-top pt-4">
                        <h5 class="mb-3">Pay Invoice</h5>
                        @if($gateways->count() > 0)
                        <form method="POST" action="{{ route('public.billing.select-gateway', ['token' => $token]) }}">
                            @csrf
                            <div class="row g-3">
                                @foreach($gateways as $index => $gateway)
                                <div class="col-md-6 mb-3">
                                    <div class="gateway-card h-100 {{ $index === 0 ? 'border-primary' : '' }}">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_gateway" 
                                                   id="gateway_{{ $gateway->id }}" value="{{ $gateway->provider}}" 
                                                   {{ $index === 0 ? 'checked="checked"' : '' }} required>
                                            <label class="form-check-label w-100" for="gateway_{{ $gateway->id }}">
                                                <strong>{{ $gateway->name }}</strong>
                                                @if($gateway->description)
                                                    <br><small class="text-muted">{{ $gateway->description }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                <button type="submit" id="pay-invoice-btn" class="btn btn-pay w-100 text-white" style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 999 !important;">
                                    <i class="fas fa-credit-card me-2"></i>
                                    Continue to Payment
                                </button>
                            </div>
                        </form>
                        
                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Ensure first radio is checked
                                const firstRadio = document.querySelector('input[name="payment_gateway"]');
                                if (firstRadio) {
                                    firstRadio.checked = true;
                                    console.log('First payment gateway preselected');
                                }
                                
                                // Ensure submit button is visible and functional
                                const submitBtn = document.getElementById('pay-invoice-btn');
                                if (submitBtn) {
                                    submitBtn.style.display = 'block';
                                    submitBtn.style.visibility = 'visible';
                                    submitBtn.style.opacity = '1';
                                    submitBtn.style.position = 'relative';
                                    submitBtn.style.zIndex = '999';
                                    submitBtn.disabled = false;
                                    console.log('Submit button made visible');
                                }
                                
                                // Ensure form can submit
                                const form = submitBtn ? submitBtn.closest('form') : null;
                                if (form) {
                                    form.addEventListener('submit', function(e) {
                                        const selectedGateway = document.querySelector('input[name="payment_gateway"]:checked');
                                        if (!selectedGateway) {
                                            e.preventDefault();
                                            alert('Please select a payment gateway');
                                            return false;
                                        }
                                        console.log('Form submitting with gateway:', selectedGateway.value);
                                        return true;
                                    });
                                }
                            });
                        </script>
                        @endpush
                        @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No payment gateways are currently available. Please contact the hospital for payment options.
                        </div>
                        @endif
                    </div>
                    @endif

                    @if($invoice->notes)
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2">Notes</h6>
                        <p class="mb-0 small">{{ $invoice->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-4">
                <div class="alert alert-light border">
                    <i class="fas fa-shield-alt text-success me-2"></i>
                    <strong>Secure Payment Processing</strong><br>
                    <small class="text-muted">Your payment information is encrypted and secure. We use industry-standard SSL encryption.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

