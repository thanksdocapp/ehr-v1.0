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
        @if(($flow ?? '') !== 'non_consultation' && !empty($appointment_date ?? null))
        <div class="summary-row">
            <span class="summary-label">Date &amp; time</span>
            <span class="summary-value">
                {{ formatDateUkLongWeekday($appointment_date) }} at {{ formatTime($appointment_time, 'g:i A') }}
            </span>
        </div>
        @endif
    </div>

    @php
        $pbDobMin = \Carbon\Carbon::now()->subYears(150)->format('Y-m-d');
        $pbDobMax = \Carbon\Carbon::now()->format('Y-m-d');
        $slotDobYmd = old('date_of_birth') ? parseDateInput(old('date_of_birth')) : '';
    @endphp
    <div class="form-card">
        <form method="POST" action="{{ route('public.booking.store-slot-dob') }}" id="slot-dob-form">
            @csrf
            <div class="mb-3">
                <label for="slot_booking_date_of_birth" class="form-label">Date of birth <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control uk-date uk-date-dob @error('date_of_birth') is-invalid @enderror"
                       id="slot_booking_date_of_birth"
                       name="date_of_birth"
                       required
                       data-uk-date="true"
                       data-min-date="{{ $pbDobMin }}"
                       data-max-date="{{ $pbDobMax }}"
                       value="{{ $slotDobYmd ? formatDateUkSlash($slotDobYmd) : '' }}"
                       autocomplete="bday"
                       inputmode="numeric">
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
                @elseif($flow === 'non_consultation' && $doctor)
                    <a href="{{ isset($department) && $department ? route('public.booking.clinic', ['slug' => $department->slug]) : route('public.booking.doctor', ['slug' => $doctor->slug]) }}" class="btn btn-outline-secondary btn-lg">
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
