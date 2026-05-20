@extends('layouts.public-booking')

@section('title', 'Booking Confirmed')

@section('content')
<div class="booking-header mb-4">
    <h1>Booking confirmed</h1>
    <p>Thank you. We have sent a confirmation email to <strong>{{ $order->patient->email }}</strong>.</p>
</div>

<div class="review-card">
    <div class="text-center mb-3"><i class="fas fa-check-circle fa-3x text-success"></i></div>
    <div class="review-row"><span class="review-label">Reference</span><span class="review-value">{{ $order->order_number }}</span></div>
    <div class="review-row"><span class="review-label">Service</span><span class="review-value">{{ $order->service->name }}</span></div>
    <div class="review-row"><span class="review-label">Clinician</span><span class="review-value">{{ $order->doctor->full_name }}</span></div>
    @if($order->fee > 0)
    <div class="review-row"><span class="review-label">Amount paid</span><span class="review-value">£{{ number_format($order->fee, 2) }}</span></div>
    @endif
    <p class="text-muted small mt-4 mb-0">
        <strong>{{ $order->doctor->full_name }}</strong> will contact you regarding <strong>{{ $order->service->name }}</strong>.
        No appointment time has been scheduled.
    </p>
</div>
@endsection
