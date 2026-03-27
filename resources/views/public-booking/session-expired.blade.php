@extends('layouts.public-booking')

@php
    $pbEmbed = request()->boolean('embed') || session('embed', false);
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
            <strong>Refresh this page</strong> (or use the button below), then open your booking link again from the practice website and start from the beginning if needed.
        </p>
    </div>

    <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
        <button type="button" class="btn btn-primary btn-lg" id="pb-session-refresh-btn">
            <i class="fas fa-rotate-right me-2"></i>Refresh page
        </button>
        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-lg" @if($pbEmbed) target="_top" rel="noopener noreferrer" @endif>
            <i class="fas fa-home me-2"></i>Homepage
        </a>
    </div>

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('scripts')
<script>
(function () {
    function pbSessionRefresh() {
        var href = window.location.href.split('#')[0];
        try {
            window.location.replace(href);
        } catch (e1) {
            try {
                window.location.href = href;
            } catch (e2) {
                window.location.reload();
            }
        }
    }
    function bindPbRefresh() {
        var btn = document.getElementById('pb-session-refresh-btn');
        if (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                pbSessionRefresh();
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindPbRefresh);
    } else {
        bindPbRefresh();
    }
})();
</script>
@endsection
