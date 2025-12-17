@extends('admin.layouts.app')

@section('title', 'Edit Feedback Question')

@section('content')
    @include('admin.shared.modern-ui')

    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="modern-page-title">
                        <i class="fas fa-edit"></i>
                        Edit Feedback Question
                    </div>
                    <p class="modern-page-subtitle">Update question text, CQC domain, order, and enabled status.</p>
                </div>
                <a href="{{ route('admin.patient-feedback.questions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.patient-feedback.questions.update', $question) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Question text</label>
                <textarea name="question_text" rows="3" class="form-control @error('question_text') is-invalid @enderror" required>{{ old('question_text', $question->question_text) }}</textarea>
                @error('question_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">CQC domain</label>
                    @php $val = old('cqc_domain', $question->cqc_domain); @endphp
                    <select name="cqc_domain" class="form-select @error('cqc_domain') is-invalid @enderror" required>
                        <option value="safe" {{ $val === 'safe' ? 'selected' : '' }}>Safe</option>
                        <option value="effective" {{ $val === 'effective' ? 'selected' : '' }}>Effective</option>
                        <option value="caring" {{ $val === 'caring' ? 'selected' : '' }}>Caring</option>
                        <option value="responsive" {{ $val === 'responsive' ? 'selected' : '' }}>Responsive</option>
                        <option value="well_led" {{ $val === 'well_led' ? 'selected' : '' }}>Well-led</option>
                    </select>
                    @error('cqc_domain') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sort order</label>
                    <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
                           value="{{ old('sort_order', $question->sort_order) }}" min="0" max="10000" required>
                    @error('sort_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_enabled" value="1" id="is_enabled"
                               {{ old('is_enabled', $question->is_enabled ? '1' : '') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_enabled">Enabled</label>
                        <div class="form-text">Max 10 enabled questions.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.patient-feedback.questions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save changes
                </button>
            </div>
        </form>
    </div>
@endsection


