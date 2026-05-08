@extends('layouts.public-booking')

@section('title', 'Booking Successful')

@section('content')
<div class="booking-header mb-4">
    <h1>Booking Successful!</h1>
    <p>You will receive a confirmation email for your appointment shortly, please check the details and follow the instructions.</p>
</div>

<div class="review-card">
    <div class="mb-4">
        <div class="text-center mb-3">
            <i class="fas fa-check-circle fa-3x text-success"></i>
        </div>
    </div>

    <div class="review-card-header">
        <h3><i class="fas fa-calendar-alt me-2"></i>Booking Details</h3>
    </div>
    <div class="review-row"><span class="review-label">Booking Number</span><span class="review-value">{{ $request->request_number }}</span></div>
    <div class="review-row"><span class="review-label">Status</span><span class="review-value">
        @if($request->status === 'accepted' && $request->appointment)
            <span class="badge bg-success">Confirmed</span>
        @else
            <span class="badge bg-info">Awaiting Doctor</span>
        @endif
    </span></div>
    @if($request->status === 'accepted' && $request->appointment)
    <div class="review-row"><span class="review-label">Appointment Number</span><span class="review-value">{{ $request->appointment->appointment_number }}</span></div>
    <div class="review-row"><span class="review-label">Doctor</span><span class="review-value">{{ $request->doctor?->full_name ?? $request->appointment->doctor?->full_name ?? '—' }}</span></div>
    @endif
    <div class="review-row"><span class="review-label">Clinic</span><span class="review-value">{{ $request->department->name }}</span></div>
    <div class="review-row"><span class="review-label">Service</span><span class="review-value">{{ $request->service->name ?? 'Consultation' }}</span></div>
    <div class="review-row"><span class="review-label">Date</span><span class="review-value">{{ formatDateUkLongWeekday($request->appointment_date) }}</span></div>
    <div class="review-row"><span class="review-label">Time</span><span class="review-value">{{ formatTime($request->appointment_time, 'g:i A') }}</span></div>

    @if(!empty($calendarLinks['google_url']) && !empty($calendarLinks['ics_url']))
    <div class="mt-4">
        <div class="mb-2 text-muted small">Add this time to your calendar</div>
        <div class="d-flex flex-column flex-md-row gap-2">
            <a href="{{ $calendarLinks['google_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm">
                <i class="fab fa-google me-1"></i>Google Calendar
            </a>
            <a href="{{ $calendarLinks['ics_url'] }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-download me-1"></i>Apple / Outlook (.ics)
            </a>
        </div>
    </div>
    @endif

    <p class="text-muted small mt-4 mb-0">Check your email (<strong>{{ $patientEmail }}</strong>) for updates. If you have any questions, please contact the clinic directly.</p>
</div>

<div class="text-center mt-4">
    <small class="text-muted">Powered by {{ getAppName() }}</small>
</div>
@endsection
