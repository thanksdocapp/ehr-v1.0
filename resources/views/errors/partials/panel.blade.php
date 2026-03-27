{{--
    EHR error panel — $icon (e.g. fa-shield-halved), $tone, $heading, $body
--}}
@php
    $accent = match ($tone ?? 'primary') {
        'danger' => 'var(--danger-color, #dc2626)',
        'warning' => 'var(--warning-color, #d97706)',
        'info' => 'var(--info-color, #0284c7)',
        'muted' => '#64748b',
        default => 'var(--primary-color, #2563eb)',
    };
@endphp
<div class="card ehr-error-card border-0">
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
                <button type="button" class="btn btn-outline-secondary" onclick="if (history.length > 1) { history.back(); }">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>{{ __('errors.web.action_back') }}
                </button>
            @endif
            <button type="button" class="btn btn-primary" onclick="location.reload()">
                <i class="fa-solid fa-rotate-right me-2" aria-hidden="true"></i>{{ __('errors.web.action_refresh') }}
            </button>
        </div>
    </div>
</div>
