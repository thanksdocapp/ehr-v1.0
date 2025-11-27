@extends('layouts.guest')

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

                    <form method="POST" action="{{ route('public.billing.process-payment', ['token' => $token]) }}">
                        @csrf
                        <input type="hidden" name="payment_gateway" value="{{ $selectedGateway->provider }}">
                        <input type="hidden" name="payment_method" value="card">
                        <input type="hidden" name="amount" value="{{ $invoice->outstanding_amount }}">

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-lock me-2"></i>
                                Proceed to {{ $selectedGateway->name }} Secure Payment
                            </button>
                            <a href="{{ route('public.billing.pay', ['token' => $token]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Back to Invoice
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

