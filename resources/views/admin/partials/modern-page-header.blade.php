@props(['title', 'subtitle' => null, 'actions' => []])

<div class="modern-page-header fade-in-up">
    <div class="modern-page-header-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h1 class="modern-page-title">{{ $title }}</h1>
                @if($subtitle)
                    <p class="modern-page-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            @if(!empty($actions))
                <div class="mt-3 mt-md-0 d-flex gap-2 flex-wrap">
                    @foreach($actions as $action)
                        <a href="{{ $action['url'] }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                            @if(isset($action['icon']))
                                <i class="{{ $action['icon'] }} me-2"></i>
                            @endif
                            {{ $action['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

