{{--
    EHR error panel — $icon (e.g. fa-shield-halved), $tone, $heading, $body
    Optional: $fallback_url (default app url) — used when Back/Refresh cannot use history (iframe, popup).
--}}
@php
    $accent = match ($tone ?? 'primary') {
        'danger' => 'var(--danger-color, #dc2626)',
        'warning' => 'var(--warning-color, #d97706)',
        'info' => 'var(--info-color, #0284c7)',
        'muted' => '#64748b',
        default => 'var(--primary-color, #2563eb)',
    };
    $ehrErrorFallbackUrl = $fallback_url ?? url('/');
@endphp
<div class="card ehr-error-card border-0" data-ehr-error-fallback-url="{{ e($ehrErrorFallbackUrl) }}">
    <div class="ehr-error-card__accent" style="--accent: {{ $accent }};"></div>
    <div class="card-body p-4 p-md-5 text-center">
        <div class="ehr-error-icon-wrap" style="--accent: {{ $accent }};">
            <i class="fa-solid {{ $icon }} fa-fw" aria-hidden="true"></i>
        </div>
        <h1 class="ehr-error-heading mb-3" id="ehr-error-title">{{ $heading }}</h1>
        <p class="ehr-error-body mb-4">{{ $body }}</p>
        <div class="ehr-error-actions d-flex flex-wrap gap-2 justify-content-center">
            @php
                $prev = url()->previous();
                $here = url()->current();
            @endphp
            @if($prev && $prev !== $here)
                <a href="{{ $prev }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>{{ __('errors.web.action_back') }}
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary" id="ehr-error-btn-back">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>{{ __('errors.web.action_back') }}
                </button>
            @endif
            <button type="button" class="btn btn-primary" id="ehr-error-btn-refresh">
                <i class="fa-solid fa-rotate-right me-2" aria-hidden="true"></i>{{ __('errors.web.action_refresh') }}
            </button>
        </div>
    </div>
</div>
@once
<script>
(function () {
    function fallbackUrl() {
        var el = document.querySelector('[data-ehr-error-fallback-url]');
        var u = el && el.getAttribute('data-ehr-error-fallback-url');
        return (u && u.length) ? u : (window.location.origin + '/');
    }
    function sameOrigin(href) {
        try {
            return new URL(href, window.location.href).origin === window.location.origin;
        } catch (e) {
            return false;
        }
    }
    /** Refresh: replace() works more reliably than reload() in iframes and avoids some POST-resubmit loops. */
    function refresh() {
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
    /** Back: history is often length 1 inside an iframe or popup; use referrer or fallback GET URL. */
    function goBack() {
        try {
            if (window.history.length > 1) {
                window.history.back();
                return;
            }
        } catch (e) {}
        var ref = document.referrer;
        if (ref && sameOrigin(ref)) {
            window.location.assign(ref);
            return;
        }
        var fb = fallbackUrl();
        if (window.self !== window.top) {
            try {
                if (window.top.location.origin === window.location.origin) {
                    window.top.location.href = fb;
                    return;
                }
            } catch (e) {
                /* Cross-origin parent (e.g. WordPress iframe): only this frame can navigate. */
            }
        }
        window.location.href = fb;
    }
    function bindErrorButtons() {
        var r = document.getElementById('ehr-error-btn-refresh');
        if (r) {
            r.addEventListener('click', function (ev) { ev.preventDefault(); refresh(); });
        }
        var b = document.getElementById('ehr-error-btn-back');
        if (b) {
            b.addEventListener('click', function (ev) { ev.preventDefault(); goBack(); });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindErrorButtons);
    } else {
        bindErrorButtons();
    }
})();
</script>
@endonce
