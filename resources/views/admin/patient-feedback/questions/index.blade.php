@extends('admin.layouts.app')

@section('title', 'Patient Feedback Questions')

@section('content')
    @include('admin.shared.modern-ui')

    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="modern-page-title">
                        <i class="fas fa-list-check"></i>
                        Patient Feedback Questions
                    </div>
                    <p class="modern-page-subtitle">
                        Manage up to 10 enabled Likert questions (1–5) and map them to CQC domains.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.patient-feedback.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar me-1"></i> View Responses
                    </a>
                    <a href="{{ route('admin.patient-feedback.test-email.form') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-paper-plane me-1"></i> Send Test Email
                    </a>
                    <a href="{{ route('admin.patient-feedback.questions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Question
                    </a>
                </div>
            </div>
            <div class="mt-3 text-muted small">
                Enabled questions: <strong>{{ $enabledCount }}</strong> / 10
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-sliders-h me-2"></i>Question Bank</h5>
            <p class="modern-card-subtitle mb-0">Reorder with arrows. Changes only affect future feedback forms.</p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px;">Order</th>
                        <th>Question</th>
                        <th style="width:140px;">CQC Domain</th>
                        <th style="width:120px;">Enabled</th>
                        <th style="width:220px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($questions as $q)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark">#{{ $q->sort_order }}</span>
                                <div class="d-flex flex-column gap-1">
                                    <form method="POST" action="{{ route('admin.patient-feedback.questions.move-up', $q) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Move up">
                                            <i class="fas fa-chevron-up"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.patient-feedback.questions.move-down', $q) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Move down">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $q->question_text }}</div>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">{{ strtoupper(str_replace('_', '-', $q->cqc_domain)) }}</span>
                        </td>
                        <td>
                            @if($q->is_enabled)
                                <span class="badge bg-success">Enabled</span>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.patient-feedback.questions.edit', $q) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form method="POST" action="{{ route('admin.patient-feedback.questions.toggle', $q) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $q->is_enabled ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                    <i class="fas {{ $q->is_enabled ? 'fa-toggle-off' : 'fa-toggle-on' }} me-1"></i>
                                    {{ $q->is_enabled ? 'Disable' : 'Enable' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No questions yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection


