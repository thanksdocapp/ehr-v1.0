@extends('admin.layouts.app')

@section('title', 'Send Test Feedback Email')

@section('content')
    @include('admin.shared.modern-ui')

    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="modern-page-title">
                        <i class="fas fa-paper-plane"></i>
                        Send Test Feedback Email
                    </div>
                    <p class="modern-page-subtitle">
                        Send the patient feedback form link to a test email address (admin testing).
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.patient-feedback.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-vial me-2"></i>Test Setup</h5>
            <p class="modern-card-subtitle mb-0">
                Choose a <strong>completed</strong> appointment. The system will generate a feedback survey linked to that appointment and email the link to your test address.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.patient-feedback.test-email.send') }}" class="mt-3">
            @csrf

            <div class="row g-3">
                <div class="col-md-7">
                    <label class="form-label fw-semibold">Appointment</label>
                    <select name="appointment_id" class="form-select @error('appointment_id') is-invalid @enderror" required>
                        <option value="" disabled {{ old('appointment_id') ? '' : 'selected' }}>Select appointment…</option>
                        @foreach($appointments as $a)
                            @php
                                $patientName = $a->patient?->full_name
                                    ?? trim(($a->patient->first_name ?? '') . ' ' . ($a->patient->last_name ?? ''))
                                    ?: 'Patient';
                                $doctorName = $a->doctor?->name ?? 'Doctor';
                                $date = $a->appointment_date?->format('d M Y') ?? '';
                            @endphp
                            <option value="{{ $a->id }}" {{ (string) old('appointment_id') === (string) $a->id ? 'selected' : '' }}>
                                #{{ $a->id }} · {{ $date }} · {{ strtoupper($a->status) }} · {{ $patientName }} · {{ $doctorName }}
                            </option>
                        @endforeach
                    </select>
                    @error('appointment_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Tip: pick a “COMPLETED” appointment.</div>
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-semibold">Test email address</label>
                    <input type="email" name="test_email" class="form-control @error('test_email') is-invalid @enderror"
                           value="{{ old('test_email') }}" placeholder="you@example.com" required>
                    @error('test_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">We’ll send the feedback email template to this address.</div>
                </div>
            </div>

            <div class="alert alert-warning mt-3 mb-0">
                <i class="fas fa-triangle-exclamation me-2"></i>
                This is a <strong>test tool</strong>. The emailed link is a real feedback link tied to the selected appointment.
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Send test email
                </button>
            </div>
        </form>
    </div>
@endsection


