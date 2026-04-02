{{-- Expects $oldValues and $newValues (nullable arrays) for UserActivity payloads. --}}
@if($oldValues || $newValues)
@php
    $auditChangeRows = \App\Support\AuditChangePresentation::buildRows($oldValues ?? null, $newValues ?? null);
@endphp
<div class="audit-change-diff-block">
    <h6 class="mb-2"><i class="fas fa-exchange-alt me-2 text-primary"></i>What changed</h6>
    <p class="text-muted small mb-3">Each row is one field. “Before” is the previous value; “After” is the value after this action.</p>

    @if(count($auditChangeRows) > 0)
    <div class="table-responsive border rounded overflow-hidden">
        <table class="table table-hover audit-diff-table mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 26%;">Field</th>
                    <th scope="col" style="width: 37%;"><i class="fas fa-arrow-left text-danger me-1"></i>Before</th>
                    <th scope="col" style="width: 37%;"><i class="fas fa-arrow-right text-success me-1"></i>After</th>
                </tr>
            </thead>
            <tbody>
                @foreach($auditChangeRows as $row)
                <tr>
                    <td class="fw-semibold text-body">{{ $row['label'] }}</td>
                    <td class="audit-diff-before"><div class="audit-diff-cell">{{ $row['before'] }}</div></td>
                    <td class="audit-diff-after"><div class="audit-diff-cell">{{ $row['after'] }}</div></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-light border mb-0">
        <p class="small text-muted mb-2">No field-by-field diff could be built. Raw payload:</p>
        <div class="row g-2">
            @if($oldValues)
            <div class="col-md-6">
                <div class="small text-muted mb-1">Old values (JSON)</div>
                <pre class="small bg-white border rounded p-2 mb-0" style="max-height: 280px; overflow: auto;">{{ json_encode($oldValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
            @if($newValues)
            <div class="col-md-6">
                <div class="small text-muted mb-1">New values (JSON)</div>
                <pre class="small bg-white border rounded p-2 mb-0" style="max-height: 280px; overflow: auto;">{{ json_encode($newValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endif
