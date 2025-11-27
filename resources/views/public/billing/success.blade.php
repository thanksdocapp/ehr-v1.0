@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Payment Successful
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">Thank You!</h3>
                    <p class="lead mb-4">Your payment has been processed successfully.</p>

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
                                    <strong>Amount Paid:</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    @php
                                        // Get the latest completed payment
                                        $latestPayment = $invoice->payments->where('status', 'completed')->sortByDesc('id')->first();
                                        
                                        // If no completed payment found, try to get any payment
                                        if (!$latestPayment) {
                                            $latestPayment = $invoice->payments->sortByDesc('id')->first();
                                        }
                                        
                                        // Get amount from payment or invoice
                                        $amountPaid = $latestPayment ? $latestPayment->amount : ($invoice->paid_amount ?? 0);
                                        
                                        // Get currency from payment gateway response or invoice
                                        $currency = 'GBP';
                                        if ($latestPayment && isset($latestPayment->gateway_response['currency'])) {
                                            $currency = strtoupper($latestPayment->gateway_response['currency']);
                                        } elseif ($invoice->currency) {
                                            $currency = $invoice->currency;
                                        }
                                        
                                        $currencySymbol = $currency === 'GBP' ? '£' : ($currency === 'USD' ? '$' : ($currency === 'EUR' ? '€' : $currency . ' '));
                                    @endphp
                                    {{ $currencySymbol }}{{ number_format($amountPaid, 2) }}
                                </div>
                                @if($invoice->payments->count() > 0)
                                <div class="col-6 mb-2">
                                    <strong>Transaction ID:</strong>
                                </div>
                                <div class="col-6 mb-2">
                                    {{ $invoice->payments->first()->transaction_id }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-muted">
                        A receipt has been sent to your email address.
                        @if($invoice->patient && $invoice->patient->email)
                            <br><strong>{{ $invoice->patient->email }}</strong>
                        @endif
                    </p>

                    <div class="mt-4">
                        <a href="{{ route('public.billing.pay', ['token' => $token]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-invoice me-2"></i>
                            View Invoice
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

