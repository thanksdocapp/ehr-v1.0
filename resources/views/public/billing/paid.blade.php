@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Invoice Already Paid
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">This Invoice Has Been Paid</h3>
                    <p class="lead mb-4">Invoice #{{ $invoice->invoice_number }} has already been paid in full.</p>

                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-3">Payment Details</h6>
                            <div class="row text-start">
                                <div class="col-6 mb-2">
                                    <strong>Invoice Number:</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    {{ $invoice->invoice_number }}
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Total Amount:</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    @php
                                        use App\Helpers\CurrencyHelper;
                                        $currency = $invoice->currency ?? CurrencyHelper::getDefaultCurrency();
                                        $currencySymbol = $currency === 'GBP' ? '£' : ($currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : CurrencyHelper::getCurrencySymbol()));
                                    @endphp
                                    {{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}
                                </div>
                                @if($invoice->paid_date)
                                <div class="col-6 mb-2">
                                    <strong>Paid Date:</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    {{ $invoice->paid_date->format('M d, Y g:i A') }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-muted">
                        If you have any questions about this invoice, please contact the billing department.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

