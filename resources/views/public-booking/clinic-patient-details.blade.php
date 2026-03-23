@extends('layouts.public-booking')

@section('title', 'Your Details')
@section('container-width', '700px')

@section('content')
    <div class="booking-header">
        <h1>Your Details</h1>
        <p>Please provide your contact information</p>
    </div>

    <div class="progress-steps">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">2</div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-row">
            <span class="summary-label">Clinic</span>
            <span class="summary-value">{{ $department->name }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Service</span>
            <span class="summary-value">{{ $service->name }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Date & Time</span>
            <span class="summary-value">
                {{ \Carbon\Carbon::parse($appointment_date)->format('l, j F Y') }} at {{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}
            </span>
        </div>
        @if(!empty($bookingDobYmd ?? null) && !$errors->has('date_of_birth'))
        <div class="summary-row">
            <span class="summary-label">Date of birth</span>
            <span class="summary-value">{{ \Carbon\Carbon::parse($bookingDobYmd)->format('l, j F Y') }}</span>
        </div>
        @endif
    </div>

    <form id="patient-details-form" method="POST" action="{{ route('public.booking.clinic-review') }}">
        @csrf
        <input type="hidden" name="department_id" value="{{ $department->id }}">
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        <input type="hidden" name="appointment_date" value="{{ $appointment_date }}">
        <input type="hidden" name="appointment_time" value="{{ $appointment_time }}">
        <input type="hidden" name="consultation_type" value="{{ $consultation_type ?? 'in_person' }}">

        <div class="form-card">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" required value="{{ old('first_name') }}">
                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" required value="{{ old('last_name') }}">
                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" required value="{{ old('email') }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" required value="{{ old('phone') }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @include('public-booking.partials.booking-address-fields')

            @php
                $bookingDobSessionYmd = !empty($bookingDobYmd ?? null) ? \Carbon\Carbon::parse($bookingDobYmd)->format('Y-m-d') : null;
                $showDobPicker = !$bookingDobSessionYmd || $errors->has('date_of_birth');
                $dobHiddenYmd = $bookingDobSessionYmd;
                if (old('date_of_birth')) {
                    $parsedDob = parseDateInput(old('date_of_birth'));
                    if ($parsedDob) {
                        $dobHiddenYmd = $parsedDob;
                    }
                }
                $dobFieldVal = old('date_of_birth');
                if ($dobFieldVal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dobFieldVal)) {
                    $dobFieldVal = \Carbon\Carbon::parse($dobFieldVal)->format('d/m/Y');
                }
                if (!$dobFieldVal && $bookingDobSessionYmd) {
                    $dobFieldVal = \Carbon\Carbon::parse($bookingDobSessionYmd)->format('d/m/Y');
                }
            @endphp
            @if($bookingDobSessionYmd && !$showDobPicker)
                <input type="hidden" name="date_of_birth" value="{{ $dobHiddenYmd }}">
            @endif
            <div class="row">
                @if($showDobPicker)
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="text" class="form-control uk-date uk-date-dob @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" data-uk-date="true" required value="{{ $dobFieldVal }}" placeholder="dd/mm/yyyy" autocomplete="off">
                    <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @endif
                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Reason for booking <span class="text-muted">(optional)</span></label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="e.g. I think I have a chest infection">{{ old('notes') }}</textarea>
                <small class="form-text text-muted">Shared with clinic staff from your booking (before your visit).</small>
            </div>
            <div class="form-check">
                <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" id="consent" name="consent" value="1" required>
                <label class="form-check-label" for="consent"><span class="text-danger">*</span> <strong>I consent to the processing of my personal data. I understand that this service is not suitable for medical emergencies.</strong></label>
                @error('consent')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" onclick="window.history.back()" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Back</button>
            <button type="submit" class="btn btn-primary btn-lg">Continue <i class="fas fa-arrow-right ms-2"></i></button>
        </div>
    </form>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('scripts')
<script>
(function() {
    function initDobPicker() {
        var el = document.getElementById('date_of_birth');
        if (!el || el.type === 'hidden' || el.hasAttribute('data-flatpickr-initialized') || typeof flatpickr === 'undefined') return;
        try {
            flatpickr(el, { dateFormat: 'd/m/Y', allowInput: true, maxDate: 'today' });
            el.setAttribute('data-flatpickr-initialized', 'true');
        } catch (e) {}
    }
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', function() { setTimeout(initDobPicker, 150); }) : setTimeout(initDobPicker, 150);
})();
</script>
@include('public-booking.partials.ideal-postcodes-public-booking-script')
@endsection
