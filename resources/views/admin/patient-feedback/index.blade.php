@extends('admin.layouts.app')

@section('title', 'Patient Feedback')

@section('content')
    @include('admin.shared.modern-ui')

    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="modern-page-title">
                        <i class="fas fa-comment-medical"></i>
                        Patient Feedback
                    </div>
                    <p class="modern-page-subtitle">View submitted feedback linked to completed consultations.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.patient-feedback.questions.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-list-check me-1"></i> Manage Questions
                    </a>
                    <a href="{{ route('admin.patient-feedback.test-email.form') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-paper-plane me-1"></i> Send Test Email
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Submitted</th>
                        <th>Doctor</th>
                        <th>Patient</th>
                        <th>Consultation Date</th>
                        <th class="text-end">Average</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($surveys as $s)
                    @php
                        $avg = $s->responses_avg_score;
                    @endphp
                    <tr>
                        <td class="text-muted">{{ $s->submitted_at?->format('d M Y, H:i') }}</td>
                        <td>{{ $s->doctor?->name ?? 'N/A' }}</td>
                        <td>
                            @if($s->is_anonymous || !$s->patient)
                                <span class="badge bg-secondary">Anonymous</span>
                            @else
                                {{ $s->patient->full_name ?? ($s->patient->first_name . ' ' . $s->patient->last_name) }}
                            @endif
                        </td>
                        <td>{{ $s->appointment?->appointment_date?->format('d M Y') ?? 'N/A' }}</td>
                        <td class="text-end">
                            @if($avg)
                                <span class="fw-semibold">{{ number_format($avg, 1) }}/5</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.patient-feedback.show', $s) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No feedback submitted yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $surveys->links() }}
        </div>
    </div>
@endsection


