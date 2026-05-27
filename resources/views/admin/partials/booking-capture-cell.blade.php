@php
    $primary = $capture['primary_label'] ?? null;
    $clinic = $capture['clinic_name'] ?? null;
    $evidence = $capture['evidence_line'] ?? null;
    $hasContent = ($primary && $primary !== '—') || $clinic || $evidence;
@endphp
@if($hasContent)
    @if($clinic)
        <span class="d-block fw-semibold">{{ $clinic }}</span>
    @endif
    @if($primary && $primary !== '—')
        <span class="d-block small text-muted">{{ $primary }}</span>
    @endif
    @if($evidence)
        <span class="d-block small text-muted" style="font-size: 0.75rem;">{{ $evidence }}</span>
    @endif
@else
    <span class="text-muted">—</span>
@endif
