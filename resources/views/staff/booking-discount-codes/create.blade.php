@extends('layouts.doctor')

@section('title', 'New discount code')
@section('page-title', 'New booking discount code')

@section('content')
<div class="fade-in-up">
    <div class="mb-3">
        <a href="{{ route('staff.booking-discount-codes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            <form method="POST" action="{{ route('staff.booking-discount-codes.store') }}">
                @csrf
                <p class="text-muted small">These codes work only on your <strong>personal</strong> booking URL (<code>/book/…</code> with your name), not on clinic or department booking pages. Clinic pages use separate codes set by admin.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code') }}" maxlength="64" required autocomplete="off" placeholder="e.g. SPRING25">
                        <div class="form-text">Stored in capitals; patients may type any case.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select" required>
                            <option value="percent" @selected(old('discount_type') === 'percent')>Percent off</option>
                            <option value="fixed" @selected(old('discount_type', 'fixed') === 'fixed')>Fixed amount (£)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" name="discount_value" class="form-control" step="0.01" min="0" value="{{ old('discount_value') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Limit to one service</label>
                        <select name="booking_service_id" class="form-select">
                            <option value="">— All services on my doctor booking link —</option>
                            @foreach($services as $svc)
                            <option value="{{ $svc->id }}" @selected(old('booking_service_id') == $svc->id)>{{ $svc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Maximum uses</label>
                        <input type="number" name="max_uses" class="form-control" min="1" value="{{ old('max_uses') }}" placeholder="Unlimited" autocomplete="off">
                        <div class="form-text">Optional. Leave empty so the code never expires by count.</div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked((string) old('is_active', '1') !== '0')>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid from</label>
                        <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid until</label>
                        <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create code</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
