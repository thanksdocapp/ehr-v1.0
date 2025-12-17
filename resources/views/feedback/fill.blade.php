<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Feedback - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <!-- Dynamic Theme CSS - Uses admin appearance settings -->
    <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ time() }}">
    <style>
        :root {
            /* Pull from Appearance settings (ThemeController), with safe fallbacks */
            --ehr-primary: var(--primary-color, #0d6efd);
            --ehr-primary-hover: var(--button-hover-primary, #0b5ed7);
            --ehr-primary-rgb: var(--primary-rgb, 13,110,253);
            --border-color: #dee2e6;
            --bg-light: #f8f9fa;
        }
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .container-narrow { max-width: 900px; margin: 0 auto; padding: 20px; }
        .card-shell { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .card-headerx { background: #fff; border-bottom: 1px solid var(--border-color); padding: 22px 28px; }
        .card-headerx h1 { margin: 0; font-size: 1.35rem; font-weight: 700; color: #212529; }
        .card-headerx p { margin: 8px 0 0; color: #6c757d; }
        .card-bodyx { padding: 28px; }
        .meta-box { background: var(--bg-light); border: 1px solid var(--border-color); border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; }
        /* Question card (EHR themed) */
        .q-row {
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 12px;
            background: #fff;
        }
        .q-title { font-weight: 600; color: #212529; margin: 0 0 10px; }
        .likert { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 8px; }
        /* Modern EHR-style rating boxes */
        .likert label {
            display: block;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 12px;
            padding: 8px;
            text-align: center;
            cursor: pointer;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            transition: transform .12s ease, box-shadow .16s ease, border-color .16s ease, background-color .16s ease;
            position: relative;
            user-select: none;
            overflow: hidden;
        }
        .likert input { display: none; }
        /* Hover/active should only apply to UNSELECTED options */
        .likert label:hover input:not(:checked) + .opt {
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.10);
        }
        .likert label:hover input:not(:checked) + .opt,
        .likert label:active input:not(:checked) + .opt {
            transform: translateY(-1px);
        }
        .likert label:active input:not(:checked) + .opt {
            transform: translateY(0);
        }
        .likert label:focus-within {
            outline: 0;
            box-shadow: 0 0 0 3px rgba(var(--ehr-primary-rgb), 0.18), 0 10px 22px rgba(15, 23, 42, 0.10);
            border-color: rgba(var(--ehr-primary-rgb), 0.45);
        }
        /* Selected state must fully override hover visuals */
        .likert input:checked + .opt {
            border-color: var(--ehr-primary);
            box-shadow: 0 0 0 3px rgba(var(--ehr-primary-rgb), 0.12);
            transform: none !important;
        }
        .opt {
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 6px 6px;
            position: relative;
            display: block;
        }
        .opt .num { font-weight: 800; font-size: .95rem; color: #0f172a; line-height: 1; }
        .opt .txt { font-size: .70rem; color: #64748b; margin-top: 3px; line-height: 1.15; }

        /* Selected checkmark */
        .likert input:checked + .opt::after {
            content: '✓';
            position: absolute;
            top: 6px;
            right: 8px;
            font-weight: 900;
            font-size: .85rem;
            opacity: 0.95;
            color: var(--ehr-primary);
        }

        /* Theme-primary hover + selected states (apply hover styling ONLY when not selected) */
        .likert label:hover input:not(:checked) + .opt {
            border-color: rgba(var(--ehr-primary-rgb), 0.65);
            background: rgba(var(--ehr-primary-rgb), 0.08);
        }

        /* Strong, full-card selected styles (no edge-only indicators) */
        .likert label input:checked + .opt {
            border: 2px solid var(--ehr-primary);
            box-shadow: 0 0 0 3px rgba(var(--ehr-primary-rgb), 0.16);
            background: rgba(var(--ehr-primary-rgb), 0.10);
        }

        /* Radios (anonymous/identified) match theme primary */
        .form-check-input:checked {
            background-color: var(--ehr-primary);
            border-color: var(--ehr-primary);
        }
        .form-check-input:focus {
            border-color: rgba(var(--ehr-primary-rgb), 0.55);
            box-shadow: 0 0 0 .25rem rgba(var(--ehr-primary-rgb), 0.20);
        }

        /* Submit button (uses theme primary + hover) */
        .btn-primaryx { background: var(--ehr-primary); border: none; padding: 12px 26px; border-radius: 8px; font-weight: 600; color: #fff; }
        .btn-primaryx:hover { background: var(--ehr-primary-hover); color: #fff; }
        /* CQC domain labels are intentionally hidden on the patient form */
        .domain-pill { display: none !important; }
        @media (max-width: 768px) {
            .likert { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container-narrow py-4">
        <div class="card-shell">
            <div class="card-headerx">
                <h1><i class="fas fa-comment-dots me-2 text-primary"></i>Patient Feedback</h1>
                <p>Your answers help us improve our service. This should take about 1 minute.</p>
            </div>

            <div class="card-bodyx">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Please answer all questions before submitting.
                    </div>
                @endif

                <div class="meta-box">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold">Consultation</div>
                            <div class="text-muted small">
                                {{ $survey->appointment?->appointment_date?->format('F d, Y') ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="text-md-end">
                            <div class="fw-semibold">Clinician</div>
                            <div class="text-muted small">{{ $survey->doctor?->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('feedback.submit', $token) }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Submit feedback</label>
                        <div class="d-flex flex-column gap-2">
                            <label class="form-check">
                                <input class="form-check-input" type="radio" name="submission_mode" value="identified" {{ old('submission_mode', 'identified') === 'identified' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    With my details
                                    <span class="text-muted small d-block">Helps the team follow up if needed. Your answers are still handled confidentially.</span>
                                </span>
                            </label>
                            <label class="form-check">
                                <input class="form-check-input" type="radio" name="submission_mode" value="anonymous" {{ old('submission_mode') === 'anonymous' ? 'checked' : '' }}>
                                <span class="form-check-label">
                                    Submit anonymously
                                    <span class="text-muted small d-block">Your identity will not be shown to clinicians or admins.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3 text-muted small">
                        <div class="fw-semibold text-dark mb-1">Scale</div>
                        <div>1 = Strongly disagree, 2 = Disagree, 3 = Neutral, 4 = Agree, 5 = Strongly agree, N/A = Not applicable</div>
                    </div>

                    @foreach($survey->questions as $q)
                        <div class="q-row">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-2">
                                <p class="q-title mb-0">{{ $q->question_text }}</p>
                                <span class="domain-pill">{{ strtoupper(str_replace('_', '-', $q->cqc_domain)) }}</span>
                            </div>

                            @error('q_' . $q->id)
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div class="likert">
                                @php
                                    $labels = [
                                        0 => 'Not applicable',
                                        1 => 'Strongly disagree',
                                        2 => 'Disagree',
                                        3 => 'Neutral',
                                        4 => 'Agree',
                                        5 => 'Strongly agree',
                                    ];
                                    $oldVal = (int) old('q_' . $q->id, 0);
                                @endphp
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="score-{{ $i }}">
                                        <input type="radio" name="q_{{ $q->id }}" value="{{ $i }}" {{ $oldVal === $i ? 'checked' : '' }} required>
                                        <span class="opt">
                                            <div class="num">{{ $i }}</div>
                                            <div class="txt">{{ $labels[$i] }}</div>
                                        </span>
                                    </label>
                                @endfor
                                <label class="score-0">
                                    <input type="radio" name="q_{{ $q->id }}" value="0" {{ $oldVal === 0 ? 'checked' : '' }} required>
                                    <span class="opt">
                                        <div class="num">N/A</div>
                                        <div class="txt">{{ $labels[0] }}</div>
                                    </span>
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <div class="q-row">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <p class="q-title mb-0">Any additional comments? (optional)</p>
                            <span class="domain-pill">COMMENTS</span>
                        </div>
                        <div class="text-muted small mb-2">If you’d like, you can add anything else about your experience.</div>
                        @error('additional_comments')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        <textarea name="additional_comments" rows="4" class="form-control" maxlength="2000"
                                  placeholder="Write your comments here...">{{ old('additional_comments') }}</textarea>
                        <div class="text-muted small mt-1">Max 2000 characters.</div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primaryx">
                            Submit feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center text-muted small mt-3">
            Powered by {{ config('app.name') }}
        </div>
    </div>
</body>
</html>


