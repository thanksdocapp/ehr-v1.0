@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Process Payment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('billing.index') }}">Billing Management</a></li>
    <li class="breadcrumb-item active">Process Payment</li>
@endsection

@push('styles')
<style>
.payment-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.bill-info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.payment-summary {
    background: #e3f2fd;
    border: 1px solid #2196f3;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.amount-display {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
}

.balance-display {
    font-size: 1.2rem;
    font-weight: 600;
    color: #ffc107;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-credit-card me-2 text-primary"></i>Process Payment</h1>
        <p class="page-subtitle text-muted">Complete payment processing for bill #{{ $bill->bill_number }}</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Bill Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Bill Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Bill Number</label>
                                <div class="fw-bold text-primary fs-5">#{{ $bill->bill_number }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Patient</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-placeholder bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                         style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($bill->patient->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $bill->patient->full_name }}</div>
                                        <small class="text-muted">{{ $bill->patient->patient_id }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Doctor</label>
                                @if($bill->doctor)
                                    <div class="fw-bold">{{ $bill->doctor->full_name }}</div>
                                    <small class="text-muted">{{ $bill->doctor->specialization ?? 'General' }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Bill Type</label>
                                <div><span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $bill->type)) }}</span></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Bill Date</label>
                                <div class="fw-bold">{{ formatDate($bill->billing_date) }}</div>
                                @if($bill->due_date)
                                    <small class="text-muted">Due: {{ formatDate($bill->due_date) }}</small>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Current Status</label>
                                <div>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'partially_paid' => 'info',
                                            'overdue' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$bill->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <form id="paymentForm" method="POST" action="{{ contextRoute('billing.process-payment', $bill) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ CurrencyHelper::getCurrencySymbol() }}</span>
                                        <input type="number" class="form-control" id="payment_amount" name="payment_amount" 
                                               step="0.01" min="0.01" max="{{ $bill->total_amount - $bill->paid_amount }}" 
                                               value="{{ $bill->total_amount - $bill->paid_amount }}" required>
                                    </div>
                                    <div class="form-text">Maximum amount: {{ CurrencyHelper::format($bill->total_amount - $bill->paid_amount) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">Select payment method</option>
                                        <option value="cash">Cash</option>
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="insurance">Insurance Coverage</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="payment_reference" class="form-label">Reference/Transaction ID</label>
                                    <input type="text" class="form-control" id="payment_reference" name="payment_reference" 
                                           placeholder="Enter check number, card transaction ID, or insurance claim number">
                                    <div class="form-text">Optional reference number for tracking</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Summary -->
                        <div class="payment-summary" id="paymentSummary" style="display: none;">
                            <h6><i class="fas fa-calculator me-2"></i>Payment Summary</h6>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><strong>Payment Amount:</strong> {{ CurrencyHelper::getCurrencySymbol() }}<span id="summaryAmount">0.00</span></p>
                                    <p class="mb-1"><strong>Payment Method:</strong> <span id="summaryMethod">-</span></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><strong>Remaining Balance:</strong> {{ CurrencyHelper::getCurrencySymbol() }}<span id="summaryBalance">0.00</span></p>
                                    <p class="mb-1"><strong>New Status:</strong> <span id="summaryStatus" class="badge">-</span></p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ contextRoute('billing.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Bills
                            </a>
                            <button type="button" class="btn btn-success btn-lg" onclick="confirmPayment()">
                                <i class="fas fa-credit-card me-1"></i>Process Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Amount Summary -->
            <div class="card payment-card text-white">
                <h5 class="mb-3"><i class="fas fa-calculator me-2"></i>Amount Summary</h5>
                <div class="mb-3">
                    <label class="form-label opacity-75">Total Amount</label>
                    <div class="amount-display">{{ CurrencyHelper::format($bill->total_amount) }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label opacity-75">Amount Paid</label>
                    <div class="fs-4 fw-bold">{{ CurrencyHelper::format($bill->paid_amount) }}</div>
                </div>
                <hr class="border-light opacity-50">
                <div class="mb-0">
                    <label class="form-label opacity-75">Balance Due</label>
                    <div class="balance-display">{{ CurrencyHelper::format($bill->total_amount - $bill->paid_amount) }}</div>
                </div>
            </div>

            <!-- Bill Description -->
            @if($bill->description)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Bill Description</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $bill->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Handle payment amount and method changes to show summary
$(document).on('input change', '#payment_amount, #payment_method', function() {
    const paymentAmount = parseFloat($('#payment_amount').val()) || 0;
    const paymentMethod = $('#payment_method').val();
    const totalAmount = {{ $bill->total_amount }};
    const paidAmount = {{ $bill->paid_amount }};
    const balanceAmount = totalAmount - paidAmount;
    
    if (paymentAmount > 0 && paymentMethod) {
        const remainingBalance = balanceAmount - paymentAmount;
        let newStatus = 'Pending';
        let statusClass = 'bg-warning';
        
        if (remainingBalance <= 0) {
            newStatus = 'Paid';
            statusClass = 'bg-success';
        } else if (paymentAmount > 0) {
            newStatus = 'Partially Paid';
            statusClass = 'bg-info';
        }
        
        // Update summary
        $('#summaryAmount').text(paymentAmount.toFixed(2));
        $('#summaryMethod').text(paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1).replace('_', ' '));
        $('#summaryBalance').text(Math.max(0, remainingBalance).toFixed(2));
        $('#summaryStatus').removeClass().addClass('badge ' + statusClass).text(newStatus);
        
        // Show summary
        $('#paymentSummary').show();
    } else {
        $('#paymentSummary').hide();
    }
});

function confirmPayment() {
    const paymentAmount = parseFloat($('#payment_amount').val());
    const paymentMethod = $('#payment_method').val();
    const paymentReference = $('#payment_reference').val() || 'N/A';
    const balanceAmount = {{ $bill->total_amount - $bill->paid_amount }};
    
    // Validate
    if (!paymentAmount || paymentAmount <= 0) {
        alert('Please enter a valid payment amount.');
        return;
    }
    
    if (paymentAmount > balanceAmount) {
        alert(`Payment amount cannot exceed the balance due (£${balanceAmount.toFixed(2)}).`);
        return;
    }
    
    if (!paymentMethod) {
        alert('Please select a payment method.');
        return;
    }
    
    // Show confirmation dialog
    const confirmMessage = `⚠️ CONFIRM PAYMENT PROCESSING\n\n` +
                          `Bill: #{{ $bill->bill_number }}\n` +
                          `Patient: {{ $bill->patient->full_name }}\n` +
                          `Payment Amount: £${paymentAmount.toFixed(2)}\n` +
                          `Payment Method: ${paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1).replace('_', ' ')}\n` +
                          `Reference: ${paymentReference}\n\n` +
                          `Are you sure you want to process this payment?\n\n` +
                          `This action cannot be undone.`;
    
    if (confirm(confirmMessage)) {
        // Submit the form
        $('#paymentForm').submit();
    }
}
</script>
@endpush
@endsection
