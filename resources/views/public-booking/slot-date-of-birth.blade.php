@extends('layouts.public-booking')

@section('title', 'Date of Birth')

@section('content')
    <div class="booking-header">
        <h1>Date of birth</h1>
        <p>We need this to confirm the service is suitable and to complete your booking.</p>
    </div>

    <div class="progress-steps">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">2</div>
            <div class="step-label">Date of birth</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Your details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <div class="summary-card">
        @if($flow === 'clinic' && $department)
            <div class="summary-row">
                <span class="summary-label">Clinic</span>
                <span class="summary-value">{{ $department->name }}</span>
            </div>
        @elseif($flow === 'doctor' && $doctor)
            <div class="summary-row">
                <span class="summary-label">Doctor</span>
                <span class="summary-value">{{ $doctor->full_name }}</span>
            </div>
        @endif
        <div class="summary-row">
            <span class="summary-label">Service</span>
            <span class="summary-value">{{ $service->name }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Date &amp; time</span>
            <span class="summary-value">
                {{ \Carbon\Carbon::parse($appointment_date)->format('l, j F Y') }} at {{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}
            </span>
        </div>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('public.booking.store-slot-dob') }}" id="slot-dob-form">
            @csrf
            <div class="mb-3">
                <label for="slot_booking_date_of_birth" class="form-label">Date of birth <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control uk-date uk-date-dob @error('date_of_birth') is-invalid @enderror"
                       id="slot_booking_date_of_birth"
                       name="date_of_birth"
                       data-uk-date="true"
                       required
                       value="{{ old('date_of_birth') }}"
                       placeholder="dd/mm/yyyy"
                       autocomplete="off">
                <small class="form-text text-muted">Format: dd/mm/yyyy</small>
                @error('date_of_birth')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            @if(session('error'))
                <div class="alert alert-danger border-0">{{ session('error') }}</div>
            @endif
            <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
                @if($flow === 'clinic' && $department)
                    <a href="{{ route('public.booking.clinic', ['slug' => $department->slug]) }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                @elseif($flow === 'doctor' && $doctor)
                    <a href="{{ route('public.booking.doctor', ['slug' => $doctor->slug]) }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                @else
                    <span></span>
                @endif
                <button type="submit" class="btn btn-primary btn-lg">
                    Continue <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('scripts')
<script>
(function() {
    function initSlotDobPicker() {
        var el = document.getElementById('slot_booking_date_of_birth');
        if (!el || el.hasAttribute('data-flatpickr-initialized') || typeof flatpickr === 'undefined') return;
        try {
            flatpickr(el, {
                dateFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: true,
                maxDate: 'today',
                minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 150)),
                locale: { firstDayOfWeek: 1 },
            });
            el.setAttribute('data-flatpickr-initialized', 'true');
        } catch (e) { console.error('Slot DOB Flatpickr init error:', e); }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { setTimeout(initSlotDobPicker, 150); });
    } else {
        setTimeout(initSlotDobPicker, 150);
    }
})();
</script>
@endsection
