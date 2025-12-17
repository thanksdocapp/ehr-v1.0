@extends('admin.layouts.app')

@section('title', 'Patient Feedback Details')

@section('content')
    @include('admin.shared.modern-ui')

    @php
        $responsesByQuestion = $survey->responses->keyBy('survey_question_id');
        // Exclude "Not Applicable" (stored as score=0) from averages
        $avg = $survey->responses->where('score', '>', 0)->avg('score');
        $domainAvgs = [];
        foreach ($survey->responses as $r) {
            if ((int) $r->score <= 0) continue;
            $domain = $r->surveyQuestion->cqc_domain ?? null;
            if (!$domain) continue;
            $domainAvgs[$domain][] = $r->score;
        }
        foreach ($domainAvgs as $domain => $scores) {
            $domainAvgs[$domain] = array_sum($scores) / max(count($scores), 1);
        }
    @endphp

    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="modern-page-title">
                        <i class="fas fa-comment-dots"></i>
                        Feedback Details
                    </div>
                    <p class="modern-page-subtitle">
                        Submitted {{ $survey->submitted_at?->format('d M Y, H:i') }} ·
                        Doctor: {{ $survey->doctor?->name ?? 'N/A' }} ·
                        @if($survey->is_anonymous || !$survey->patient)
                            Patient: Anonymous
                        @else
                            Patient: {{ $survey->patient->full_name ?? ($survey->patient->first_name . ' ' . $survey->patient->last_name) }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.patient-feedback.reset', $survey) }}"
                          onsubmit="return confirm('Reset this feedback submission so it can be submitted again? This will delete all existing scores and comments for this survey.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger" {{ $survey->submitted_at ? '' : 'disabled' }}
                                title="{{ $survey->submitted_at ? '' : 'Reset is available after the survey has been submitted at least once.' }}">
                            <i class="fas fa-rotate-left me-1"></i> Reset submission (test)
                        </button>
                    </form>
                    <a href="{{ route('admin.patient-feedback.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title mb-0"><i class="fas fa-star me-2"></i>Summary</h5>
                </div>
                <div class="mt-2">
                    <div class="d-flex justify-content-between">
                        <div class="text-muted">Overall average</div>
                        <div class="fw-bold">{{ $avg ? number_format($avg, 1) : '—' }}/5</div>
                    </div>
                    <hr>
                    @foreach(['safe','effective','caring','responsive','well_led'] as $d)
                        <div class="d-flex justify-content-between mb-2">
                            <div class="text-muted">{{ ucfirst(str_replace('_',' ', $d)) }}</div>
                            <div class="fw-semibold">{{ isset($domainAvgs[$d]) ? number_format($domainAvgs[$d], 1) . '/5' : '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title mb-0"><i class="fas fa-list me-2"></i>Responses</h5>
                </div>
                <div class="mt-2">
                    @foreach($survey->questions as $q)
                        @php $r = $responsesByQuestion->get($q->id); @endphp
                        <div class="border rounded-3 p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="fw-semibold">{{ $q->question_text }}</div>
                                <span class="badge bg-info text-dark">{{ strtoupper(str_replace('_','-', $q->cqc_domain)) }}</span>
                            </div>
                            <div class="mt-2">
                                @if($r && (int) $r->score > 0)
                                    <span class="badge bg-primary">{{ $r->score }}/5</span>
                                @elseif($r && (int) $r->score === 0)
                                    <span class="badge bg-secondary">N/A</span>
                                @else
                                    <span class="badge bg-light text-dark">—</span>
                                @endif
                                <span class="text-muted small ms-2">
                                    (1 Strongly disagree · 2 Disagree · 3 Neutral · 4 Agree · 5 Strongly agree · N/A Not applicable)
                                </span>
                            </div>
                        </div>
                    @endforeach

                    @if(!empty($survey->additional_comments))
                        <div class="border rounded-3 p-3 mt-3">
                            <div class="fw-semibold mb-1"><i class="fas fa-comment-alt me-2 text-secondary"></i>Additional comments</div>
                            <div style="white-space: pre-wrap;">{{ $survey->additional_comments }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


