@extends('layouts.guest')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Invalid Payment Link
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="mb-3">Link Invalid or Expired</h3>
                    <p class="lead mb-4">
                        @if(session('error'))
                            {{ session('error') }}
                        @else
                            This payment link is invalid or has expired.
                        @endif
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>What to do next:</strong>
                        <ul class="text-start mt-2 mb-0">
                            <li>Contact the hospital billing department for a new payment link</li>
                            <li>Or log into your patient portal to view and pay your invoices</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>
                            Return to Homepage
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

