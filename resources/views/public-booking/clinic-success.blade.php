@extends('layouts.public-booking')

@section('title', 'Booking Successful')
@section('container-width', '700px')

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
    <div class="review-row"><span class="review-label">Status</span><span class="review-value"><span class="badge bg-info">Awaiting Doctor</span></span></div>
    <div class="review-row"><span class="review-label">Clinic</span><span class="review-value">{{ $request->department->name }}</span></div>
    <div class="review-row"><span class="review-label">Service</span><span class="review-value">{{ $request->service->name ?? 'Consultation' }}</span></div>
    <div class="review-row"><span class="review-label">Date</span><span class="review-value">{{ $request->appointment_date->format('l, j F Y') }}</span></div>
    <div class="review-row"><span class="review-label">Time</span><span class="review-value">{{ $request->appointment_time instanceof \DateTimeInterface ? $request->appointment_time->format('g:i A') : $request->appointment_time }}</span></div>

    <p class="text-muted small mt-4 mb-0">Check your email (<strong>{{ $patientEmail }}</strong>) for updates. If you have any questions, please contact the clinic directly.</p>
</div>

<div class="text-center mt-4">
    <small class="text-muted">Powered by {{ getAppName() }}</small>
</div>
@endsection
