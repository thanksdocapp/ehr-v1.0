@extends('layouts.doctor')

@section('title', 'Patient Feedback')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1"><i class="fas fa-comment-dots me-2 text-primary"></i>Patient Feedback</h2>
            <div class="text-muted">Only feedback from your consultations is shown. Anonymous responses hide patient identity.</div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Submitted</th>
                            <th>Patient</th>
                            <th class="text-end">Average</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($surveys as $s)
                        @php $avg = $s->responses_avg_score; @endphp
                        <tr>
                            <td class="text-muted">{{ $s->submitted_at?->format('d M Y, H:i') }}</td>
                            <td>
                                @if($s->is_anonymous || !$s->patient)
                                    <span class="badge bg-secondary">Anonymous</span>
                                @else
                                    {{ $s->patient->full_name ?? ($s->patient->first_name . ' ' . $s->patient->last_name) }}
                                @endif
                            </td>
                            <td class="text-end">
                                @if($avg)
                                    <span class="fw-semibold">{{ number_format($avg, 1) }}/5</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('staff.feedback.show', $s) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No feedback submitted yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0">
            {{ $surveys->links() }}
        </div>
    </div>
</div>
@endsection


