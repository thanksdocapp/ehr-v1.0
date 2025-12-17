@extends('layouts.doctor')

@section('title', 'Feedback Details')

@section('content')
@php
    $responsesByQuestion = $survey->responses->keyBy('survey_question_id');
    // Exclude "Not Applicable" (stored as score=0) from averages
    $avg = $survey->responses->where('score', '>', 0)->avg('score');
@endphp

<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-bold mb-1"><i class="fas fa-comment-dots me-2 text-primary"></i>Feedback Details</h2>
            <div class="text-muted">
                Submitted {{ $survey->submitted_at?->format('d M Y, H:i') }} ·
                @if($survey->is_anonymous || !$survey->patient)
                    Patient: Anonymous
                @else
                    Patient: {{ $survey->patient->full_name ?? ($survey->patient->first_name . ' ' . $survey->patient->last_name) }}
                @endif
            </div>
        </div>
        <a href="{{ route('staff.feedback.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="fw-bold mb-2">Overall average</div>
                    <div class="display-6 fw-bold">{{ $avg ? number_format($avg, 1) : '—' }}<span class="fs-6 text-muted">/5</span></div>
                    <div class="text-muted small mt-2">Scale: 1 Strongly disagree → 5 Strongly agree (N/A excluded)</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
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
</div>
@endsection


