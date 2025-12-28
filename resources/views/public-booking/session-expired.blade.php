@extends('layouts.public-booking')

@section('title', 'Session Expired')
@section('container-width', '500px')

@section('content')
    <div class="booking-header">
        <h1><i class="fas fa-clock text-warning me-2"></i>Session Expired</h1>
    </div>

    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ $message ?? 'Your booking session has expired. Please start a new booking.' }}
    </div>

    <div class="text-center mt-4">
        <a href="/" class="btn btn-primary btn-lg">
            <i class="fas fa-home me-2"></i>Return to Homepage
        </a>
    </div>

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
