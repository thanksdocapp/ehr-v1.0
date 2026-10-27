@extends('layouts.public-booking')

@php
    $pbEmbed = request()->boolean('embed') || session('embed', false);
    $newEhrUrl = \App\Models\Setting::get('migration_mode_new_url', 'https://my.thanksdoc.co.uk');
@endphp

@section('title', 'Booking Has Moved')
@section('container-width', '560px')

@section('content')
    <div class="booking-header">
        <h1><i class="fas fa-exchange-alt text-warning me-2"></i>This Booking Page Has Moved</h1>
        <p class="text-muted mb-0">This doctor's booking system has been upgraded to a new platform.</p>
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

    <div class="text-center mt-4">
        <p class="text-muted mb-3">If your doctor has shared a new booking link, please use that instead.</p>
    </div>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
