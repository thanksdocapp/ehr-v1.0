@extends('layouts.public-booking')

@php
    $pbEmbed = request()->boolean('embed') || session('embed', false);
    $newEhrUrl = \App\Models\Setting::get('migration_mode_new_url', 'https://my.thanksdoc.co.uk');
    $contactNumber = $doctor->migration_contact_whatsapp ?? null;
    $doctorName = $doctor ? ($doctor->title ? $doctor->title . ' ' : '') . $doctor->first_name . ' ' . $doctor->last_name : null;
    $waLink = $contactNumber ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $contactNumber) : null;
@endphp

@section('title', 'Booking Has Moved')
@section('container-width', '560px')

@section('content')
    <div class="booking-header">
        <h1><i class="fas fa-exchange-alt text-warning me-2"></i>This Booking Page Has Moved</h1>
        @if($doctorName)
            <p class="text-muted mb-0">{{ $doctorName }}'s booking system has been upgraded to a new platform.</p>
        @else
            <p class="text-muted mb-0">This doctor's booking system has been upgraded to a new platform.</p>
        @endif
    </div>

    <div class="alert alert-warning" role="alert" style="border-radius: 12px; border: none; background: #fef3c7; color: #92400e;">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Online booking is no longer available on this system.</strong>
    </div>

    <div class="info-card text-start" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px;">
        <h3 class="h6 mb-3" style="font-weight: 600;">
            <i class="fas fa-question-circle text-primary me-2"></i>What should I do?
        </h3>
        <ul class="mb-0" style="list-style: none; padding: 0;">
            <li style="margin-bottom: 12px; padding-left: 24px; position: relative;">
                <i class="fas fa-check-circle text-success" style="position: absolute; left: 0; top: 3px;"></i>
                <strong>Ask your doctor</strong> for their new booking link on the updated system.
            </li>
            <li style="padding-left: 24px; position: relative;">
                <i class="fas fa-check-circle text-success" style="position: absolute; left: 0; top: 3px;"></i>
                All services, availability and pricing remain the same on the new platform.
            </li>
        </ul>
    </div>

    @if($contactNumber)
    <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px; margin-top: 20px; text-align: center;">
        <h3 class="h6 mb-3" style="font-weight: 600; color: #065f46;">
            <i class="fab fa-whatsapp me-2" style="color: #25d366;"></i>Get in touch
        </h3>
        <p style="color: #047857; margin-bottom: 16px; font-size: 14px;">
            Contact {{ $doctorName ?? 'the clinic' }} for your new booking link:
        </p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
               class="btn" style="background: #25d366; color: white; font-weight: 600; border-radius: 10px; padding: 10px 24px; border: none;">
                <i class="fab fa-whatsapp me-2"></i>WhatsApp {{ $contactNumber }}
            </a>
            <a href="tel:{{ $contactNumber }}"
               class="btn btn-outline-secondary" style="border-radius: 10px; padding: 10px 24px;">
                <i class="fas fa-phone me-2"></i>Call {{ $contactNumber }}
            </a>
        </div>
    </div>
    @endif

    <div class="text-center mt-4">
        <p class="text-muted mb-3">If your doctor has shared a new booking link, please use that instead.</p>
    </div>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
