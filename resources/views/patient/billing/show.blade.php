@extends('patient.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Invoice #' . $invoice->invoice_number)
@section('page-title', 'Invoice #' . $invoice->invoice_number)

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <!-- Invoice Details -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            Invoice Details
                        </h5>
                        <div class="btn-group">
                            @if($invoice->status !== 'paid')
                                <a href="{{ route('patient.billing.pay', $invoice) }}" class="btn btn-success">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Pay Now
                                </a>
                            @endif
                            <a href="{{ route('patient.billing.download', $invoice) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>
                                Download
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Invoice Number</h6>
                            <h4 class="text-primary">{{ $invoice->invoice_number }}</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            @php
                                $statusClass = match($invoice->status) {
                                    'paid' => 'bg-success',
                                    'pending' => $invoice->due_date->lt(today()) ? 'bg-danger' : 'bg-warning',
                                    'partial' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                $statusText = match($invoice->status) {
                                    'paid' => 'Paid',
                                    'pending' => $invoice->due_date->lt(today()) ? 'Overdue' : 'Pending',
                                    'partial' => 'Partial',
                                    default => ucfirst($invoice->status)
                                };
                            @endphp
                            <h6 class="text-muted mb-2">Status</h6>
                            <span class="badge {{ $statusClass }} fs-6 px-3 py-2">{{ $statusText }}</span>
                        </div>
                    </div>

                    <!-- Invoice Information -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Invoice Date</h6>
                            <p class="mb-0">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Due Date</h6>
                            <p class="mb-0 {{ $invoice->due_date->lt(today()) && $invoice->status !== 'paid' ? 'text-danger' : '' }}">
                                {{ $invoice->due_date->format('M d, Y') }}
                                @if($invoice->due_date->lt(today()) && $invoice->status !== 'paid')
                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            @if($invoice->paid_date)
                                <h6 class="text-muted mb-2">Paid Date</h6>
                                <p class="mb-0 text-success">{{ $invoice->paid_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    @if($invoice->description)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Description</h6>
                            <p class="mb-0">{{ $invoice->description }}</p>
                        </div>
                    @endif

                    @if($invoice->appointment)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Related Appointment</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                <span>{{ $invoice->appointment->appointment_date->format('M d, Y g:i A') }}</span>
                                @if($invoice->appointment->doctor)
                                    <span class="ms-2 text-muted">with Dr. {{ $invoice->appointment->doctor->full_name }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Invoice Items -->
                    @if($invoice->invoiceItems && $invoice->invoiceItems->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Invoice Items</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Description</th>
                                            <th>Quantity</th>
                                            <th>Rate</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->invoiceItems as $item)
                                            <tr>
                                                <td>{{ $item->description }}</td>
                                                <td>{{ $item->quantity ?? 1 }}</td>
                                                <td>{{ CurrencyHelper::format($item->rate ?? $item->amount) }}</td>
                                                <td class="text-end">{{ CurrencyHelper::format($item->amount) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Amount Summary -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-borderless">
                                @if($invoice->subtotal && $invoice->subtotal != $invoice->total_amount)
                                    <tr>
                                        <td class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end">{{ CurrencyHelper::format($invoice->subtotal) }}</td>
                                    </tr>
                                @endif
                                @if($invoice->tax_amount && $invoice->tax_amount > 0)
                                    <tr>
                                        <td class="text-end"><strong>Tax:</strong></td>
                                        <td class="text-end">{{ CurrencyHelper::format($invoice->tax_amount) }}</td>
                                    </tr>
                                @endif
                                @if($invoice->discount_amount && $invoice->discount_amount > 0)
                                    <tr>
                                        <td class="text-end"><strong>Discount:</strong></td>
                                        <td class="text-end text-success">-{{ CurrencyHelper::format($invoice->discount_amount) }}</td>
                                    </tr>
                                @endif
                                <tr class="border-top">
                                    <td class="text-end"><strong>Total Amount:</strong></td>
                                    <td class="text-end"><strong class="fs-5">{{ CurrencyHelper::format($invoice->total_amount) }}</strong></td>
                                </tr>
                                @if($invoice->payments->where('status', 'completed')->count() > 0)
                                    <tr>
                                        <td class="text-end"><strong>Amount Paid:</strong></td>
                                        <td class="text-end text-success">
                                            <strong>{{ CurrencyHelper::format($invoice->payments->where('status', 'completed')->sum('amount')) }}</strong>
                                        </td>
                                    </tr>
                                @endif
                                @if($invoice->outstanding_amount > 0)
                                    <tr class="border-top">
                                        <td class="text-end"><strong>Outstanding Balance:</strong></td>
                                        <td class="text-end">
                                            <strong class="fs-5 text-danger">{{ CurrencyHelper::format($invoice->outstanding_amount) }}</strong>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($invoice->notes)
                        <div class="mt-4">
                            <h6 class="text-muted mb-2">Notes</h6>
                            <div class="alert alert-light">
                                {{ $invoice->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Payment History -->
            @if($invoice->payments && $invoice->payments->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>
                            Payment History
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($invoice->payments as $payment)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <div class="fw-bold">{{ CurrencyHelper::format($payment->amount) }}</div>
                                    <small class="text-muted">
                                        {{ $payment->payment_date->format('M d, Y g:i A') }}
                                    </small>
                                    @if($payment->transaction_id)
                                        <br><small class="text-muted">{{ $payment->transaction_id }}</small>
                                    @endif
                                </div>
                                <div class="text-end">
                                    @php
                                        $paymentStatusClass = match($payment->status) {
                                            'completed' => 'bg-success',
                                            'pending' => 'bg-warning',
                                            'failed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $paymentStatusClass }}">{{ ucfirst($payment->status) }}</span>
                                    <br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($invoice->status !== 'paid')
                        <a href="{{ route('patient.billing.pay', $invoice) }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-credit-card me-1"></i>
                            Make Payment
                        </a>
                    @endif
                    <a href="{{ route('patient.billing.download', $invoice) }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-download me-1"></i>
                        Download Invoice
                    </a>
                    <a href="{{ route('patient.billing.index') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Billing
                    </a>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        Need Help?
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">If you have questions about this invoice or need payment assistance, please contact us:</p>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <span>(555) 123-4567</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-envelope text-primary me-2"></i>
                        <span>billing@hospital.com</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <span>Mon-Fri 8AM-6PM</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
