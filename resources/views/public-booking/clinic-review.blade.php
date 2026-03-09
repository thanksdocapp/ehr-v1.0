@extends('layouts.public-booking')

@section('title', 'Review & Confirm')
@section('container-width', '700px')

@section('content')
    <div class="booking-header">
        <h1>Review & Confirm</h1>
        <p>Please review your booking.</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form id="confirm-form" method="POST" action="{{ route('public.booking.clinic-confirm') }}">
        @csrf
        <input type="hidden" name="department_id" value="{{ $department->id }}">
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        <input type="hidden" name="appointment_date" value="{{ $appointment_date }}">
        <input type="hidden" name="appointment_time" value="{{ $appointment_time }}">
        <input type="hidden" name="first_name" value="{{ $patient_data['first_name'] }}">
        <input type="hidden" name="last_name" value="{{ $patient_data['last_name'] }}">
        <input type="hidden" name="email" value="{{ $patient_data['email'] }}">
        <input type="hidden" name="phone" value="{{ $patient_data['phone'] }}">
        <input type="hidden" name="date_of_birth" value="{{ $patient_data['date_of_birth'] ?? '' }}">
        <input type="hidden" name="gender" value="{{ $patient_data['gender'] ?? '' }}">
        <input type="hidden" name="consultation_type" value="{{ $patient_data['consultation_type'] ?? 'in_person' }}">
        @if(isset($patient_data['notes']))
        <input type="hidden" name="notes" value="{{ $patient_data['notes'] }}">
        @endif

        <div class="review-card">
            <div class="review-card-header"><h3><i class="fas fa-calendar-check me-2"></i>Booking Summary</h3></div>
            <div class="review-row"><span class="review-label">Clinic</span><span class="review-value">{{ $department->name }}</span></div>
            <div class="review-row"><span class="review-label">Service</span><span class="review-value">{{ $service->name }}</span></div>
            <div class="review-row"><span class="review-label">Consultation Type</span><span class="review-value">
                @php $ct = $patient_data['consultation_type'] ?? 'in_person'; $ct = in_array($ct, ['phone', 'telephone']) ? 'telephone' : $ct; @endphp
                @if($ct === 'online')
                    <i class="fas fa-video me-1"></i>Online (Video)
                @elseif($ct === 'telephone')
                    <i class="fas fa-phone me-1"></i>Telephone
                @else
                    <i class="fas fa-hospital me-1"></i>In Person
                @endif
            </span></div>
            <div class="review-row"><span class="review-label">Date</span><span class="review-value">{{ \Carbon\Carbon::parse($appointment_date)->format('l, j F Y') }}</span></div>
            <div class="review-row"><span class="review-label">Time</span><span class="review-value">{{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}</span></div>
            <div class="review-row"><span class="review-label">Price</span><span class="review-price">£{{ number_format($price ?? 0, 2) }}</span></div>
        </div>

        <div class="review-card">
            <div class="review-card-header"><h3><i class="fas fa-user me-2"></i>Your Information</h3></div>
            <div class="review-row"><span class="review-label">Name</span><span class="review-value">{{ ($patient_data['first_name'] ?? '') . ' ' . ($patient_data['last_name'] ?? '') }}</span></div>
            <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $patient_data['email'] ?? '' }}</span></div>
            <div class="review-row"><span class="review-label">Phone</span><span class="review-value">{{ $patient_data['phone'] ?? '' }}</span></div>
        </div>

        <div class="text-center mt-4">
            <button type="button" onclick="window.history.back()" class="btn btn-outline-secondary btn-lg me-2"><i class="fas fa-arrow-left me-2"></i>Back</button>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-credit-card me-2"></i>Proceed to Payment</button>
        </div>
    </form>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
