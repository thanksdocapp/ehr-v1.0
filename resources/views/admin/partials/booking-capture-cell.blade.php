@php
    $formatter = app(\App\Services\PatientBookingSourceService::class);
    $primary = $capture['primary_label'] ?? null;
    $clinic = trim((string) ($capture['clinic_name'] ?? ''));
    $evidence = trim((string) ($capture['evidence_line'] ?? ''));
    $showClinic = $clinic !== '' && ! $formatter->captureTextContains($evidence, $clinic);
    $showPrimary = $primary && $primary !== '—' && $primary !== 'Invoice' && ! $formatter->captureTextContains($evidence, $primary);
    $hasContent = $showClinic || $showPrimary || $evidence !== '';
@endphp
@if($hasContent)
    @if($showClinic)
        <span class="d-block fw-semibold">{{ $clinic }}</span>
    @endif
    @if($showPrimary)
        <span class="d-block small text-muted">{{ $primary }}</span>
    @endif
    @if($evidence !== '')
        <span class="d-block small text-muted" style="font-size: 0.75rem;">{{ $evidence }}</span>
    @endif
@else
    <span class="text-muted">—</span>
@endif
