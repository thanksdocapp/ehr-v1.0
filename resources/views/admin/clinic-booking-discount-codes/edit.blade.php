@extends('admin.layouts.app')

@section('title', 'Edit clinic booking code')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('admin.departments.clinic-booking-discount-codes.index', $department) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><strong>Edit code — {{ $department->name }}</strong></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.departments.clinic-booking-discount-codes.update', [$department, $clinicBookingDiscountCode]) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', $clinicBookingDiscountCode->code) }}" maxlength="64" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select" required>
                            <option value="percent" @selected(old('discount_type', $clinicBookingDiscountCode->discount_type) === 'percent')>Percent off</option>
                            <option value="fixed" @selected(old('discount_type', $clinicBookingDiscountCode->discount_type) === 'fixed')>Fixed (£)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" name="discount_value" class="form-control" step="0.01" min="0" value="{{ old('discount_value', $clinicBookingDiscountCode->discount_value) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Limit to one booking service</label>
                        <select name="booking_service_id" class="form-select">
                            <option value="">— All services this clinic offers (public booking) —</option>
                            @foreach($services as $svc)
                            <option value="{{ $svc->id }}" @selected(old('booking_service_id', $clinicBookingDiscountCode->booking_service_id) == $svc->id)>{{ $svc->name }}</option>
                            @endforeach
                        </select>
                        @php
                            $curSid = $clinicBookingDiscountCode->booking_service_id;
                            $curInList = $curSid && $services->contains('id', (int) $curSid);
                        @endphp
                        @if($curSid && !$curInList)
                        <div class="form-text text-warning">Current service is not in this clinic’s public list. Clear to “all services” or pick a listed service.</div>
                        @elseif($services->isEmpty())
                        <div class="form-text text-warning">No doctor-linked services for this clinic. Save may require clearing service scope.</div>
                        @else
                        <div class="form-text">Matches the same service name across doctors at this clinic.</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Maximum uses</label>
                        <input type="number" name="max_uses" class="form-control" min="1" value="{{ old('max_uses', $clinicBookingDiscountCode->max_uses) }}" placeholder="Unlimited" autocomplete="off">
                        <div class="form-text">Cannot be lower than {{ $clinicBookingDiscountCode->uses_count }} (already used).</div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $clinicBookingDiscountCode->is_active))>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid from</label>
                        <input type="date" name="valid_from" class="form-control" value="{{ old('valid_from', optional($clinicBookingDiscountCode->valid_from)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid until</label>
                        <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', optional($clinicBookingDiscountCode->valid_until)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-0">Uses recorded: <strong>{{ $clinicBookingDiscountCode->uses_count }}</strong></p>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
