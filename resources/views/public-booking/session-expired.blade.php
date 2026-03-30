@extends('layouts.public-booking')

@php
    $pbEmbed = request()->boolean('embed') || session('embed', false);
    $restartUrl = $booking_restart_url ?? url('/');
@endphp

@section('title', 'Session Expired')
@section('container-width', '500px')

@section('content')
    <div class="booking-header">
        <h1><i class="fas fa-clock text-warning me-2"></i>Session expired</h1>
        <p class="text-muted mb-0">Your booking could not continue because the session timed out or the page was left open too long.</p>
    </div>

    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ $message ?? 'Your booking session has expired. Please start a new booking.' }}
    </div>

    <div class="info-card text-start">
        <h3 class="h6 mb-2"><i class="fas fa-circle-info text-primary me-2"></i>Browser message about “resubmitting” data?</h3>
        <p class="small text-muted mb-0">
            That usually means the page needed information from an earlier step that is no longer available. Do not resubmit—your session has likely timed out.
            Use <strong>Start booking again</strong> below to reopen your booking link and begin from the start.
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
        <a href="{{ $restartUrl }}" class="btn btn-primary btn-lg" @if($pbEmbed) target="_top" rel="noopener noreferrer" @endif id="pb-session-restart-link">
            <i class="fas fa-calendar-plus me-2"></i>Start booking again
        </a>
        @if($restartUrl !== url('/'))
            <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg" @if($pbEmbed) target="_top" rel="noopener noreferrer" @endif>
                <i class="fas fa-home me-2"></i>App home
            </a>
        @endif
    </div>

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
