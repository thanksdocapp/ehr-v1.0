@extends('admin.layouts.app')

@section('title', 'Patient Feedback Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Patient Feedback</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('content')
<div class="fade-in">
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="modern-page-title"><i class="fas fa-comment-dots"></i>Patient Feedback</h1>
                    <p class="modern-page-subtitle">Control when feedback forms are automatically sent after a completed consultation</p>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="btn-modern btn-modern-outline">
                    <i class="fas fa-arrow-left"></i>Back to Settings
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($delayMinutes) }}</div>
                        <div class="stat-label">Delay (minutes)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="modern-card">
                <div class="modern-card-body">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        This delay applies to <strong>future</strong> completed consultations. Range: <strong>1 minute</strong> to <strong>3 days</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        // Suggest a friendly default in the form
        $formUnit = old('delay_unit', $delayMinutes % 1440 === 0 ? 'days' : ($delayMinutes % 60 === 0 ? 'hours' : 'minutes'));
        $formValue = (int) old('delay_value', $formUnit === 'days' ? max(1, (int) round($delayMinutes / 1440)) : ($formUnit === 'hours' ? max(1, (int) round($delayMinutes / 60)) : max(1, $delayMinutes)));
    @endphp

    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-sliders-h"></i>Automatic Send Delay</h5>
        </div>
        <div class="modern-card-body">
            <form method="POST" action="{{ route('admin.settings.patient-feedback.update') }}">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="modern-form-label">Delay</label>
                        <input type="number"
                               name="delay_value"
                               class="modern-form-control @error('delay_value') is-invalid @enderror"
                               min="1"
                               value="{{ $formValue }}"
                               required>
                        @error('delay_value')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="modern-form-label">Unit</label>
                        <select name="delay_unit" class="modern-form-select @error('delay_unit') is-invalid @enderror" required>
                            <option value="minutes" {{ $formUnit === 'minutes' ? 'selected' : '' }}>Minutes</option>
                            <option value="hours" {{ $formUnit === 'hours' ? 'selected' : '' }}>Hours</option>
                            <option value="days" {{ $formUnit === 'days' ? 'selected' : '' }}>Days</option>
                        </select>
                        @error('delay_unit')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn-modern btn-modern-primary w-100">
                            <i class="fas fa-save"></i>Save
                        </button>
                    </div>
                </div>

                <div class="mt-3 text-muted small">
                    <strong>Examples:</strong> 1 minute (test), 30 minutes, 2 hours, 2 days, 3 days.
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


