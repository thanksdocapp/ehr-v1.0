{{-- Doctors only: $amendmentHistory is non-null when the viewer may see this record's audit trail. --}}
@once
@push('styles')
<style>
    .audit-diff-table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; color: #5a5c69; }
    .audit-diff-table td { vertical-align: top; font-size: 0.95rem; }
    .audit-diff-cell {
        max-width: 420px;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.45;
    }
    .audit-diff-before { background: rgba(239, 68, 68, 0.06); border-left: 3px solid rgba(239, 68, 68, 0.45) !important; }
    .audit-diff-after { background: rgba(34, 197, 94, 0.08); border-left: 3px solid rgba(34, 197, 94, 0.5) !important; }
</style>
@endpush
@endonce

@if($amendmentHistory !== null)
<div class="doctor-card mb-4">
    <div class="doctor-card-header">
        <h5 class="doctor-card-title mb-0"><i class="fas fa-history me-2 text-primary"></i>Amendment &amp; audit history</h5>
        <small class="text-muted">Activity for this record only (your responsibility as the clinician on file).</small>
    </div>
    <div class="doctor-card-body">
        @forelse($amendmentHistory as $activity)
            @php
                $iconMap = ['create' => 'plus', 'update' => 'edit', 'delete' => 'trash', 'view' => 'eye', 'file_upload' => 'paperclip', 'pre_consultation_verified' => 'clipboard-check'];
                $icon = $iconMap[$activity->action] ?? 'circle';
            @endphp
            <div class="border rounded mb-3 overflow-hidden">
                <div class="d-flex flex-wrap align-items-center gap-2 px-3 py-2 bg-light border-bottom">
                    <span class="badge bg-secondary"><i class="fas fa-{{ $icon }} me-1"></i>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</span>
                    <span class="small text-muted">{{ $activity->created_at->format('d M Y, H:i') }}</span>
                    @if($activity->user)
                        <span class="small"><strong>{{ $activity->user->name }}</strong></span>
                    @else
                        <span class="small text-muted">System</span>
                    @endif
                </div>
                <div class="px-3 py-2">
                    <p class="mb-2 small mb-0">{{ $activity->description }}</p>
                    @if($activity->action === 'update' && ($activity->old_values || $activity->new_values))
                        <div class="mt-3 pt-3 border-top">
                            @include('partials.audit-change-diff', ['oldValues' => $activity->old_values, 'newValues' => $activity->new_values])
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-muted mb-0 small">No amendments or audit events are recorded for this clinical record yet.</p>
        @endforelse
    </div>
</div>
@endif
