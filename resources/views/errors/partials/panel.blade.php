{{--
    EHR error panel — $icon (e.g. fa-shield-halved), $tone, $heading, $body, $show_sign_in (optional bool)
--}}
@php
    $accent = match ($tone ?? 'primary') {
        'danger' => 'var(--danger-color, #dc2626)',
        'warning' => 'var(--warning-color, #d97706)',
        'info' => 'var(--info-color, #0284c7)',
        'muted' => '#64748b',
        default => 'var(--primary-color, #2563eb)',
    };
    $show_sign_in = $show_sign_in ?? false;
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
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="fa-solid fa-house-chimney me-2" aria-hidden="true"></i>{{ __('errors.web.action_home') }}
            </a>
            @php
                $prev = url()->previous();
                $here = url()->current();
            @endphp
            @if($prev && $prev !== $here)
                <a href="{{ $prev }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>{{ __('errors.web.action_back') }}
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary" onclick="if (history.length > 1) { history.back(); } else { window.location.href = @json(url('/')); }">
                    <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>{{ __('errors.web.action_back') }}
                </button>
            @endif
            @if($show_sign_in && \Illuminate\Support\Facades\Route::has('login'))
                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-right-to-bracket me-2" aria-hidden="true"></i>{{ __('errors.web.action_sign_in') }}
                </a>
            @endif
        </div>
    </div>
</div>
