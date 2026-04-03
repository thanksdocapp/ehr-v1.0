@extends('admin.layouts.app')

@section('title', 'Edit doctor booking code')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><strong>Edit code — {{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }}</strong></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.doctors.booking-discount-codes.update', [$doctor, $doctorBookingDiscountCode]) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', $doctorBookingDiscountCode->code) }}" maxlength="64" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select" required>
                            <option value="percent" @selected(old('discount_type', $doctorBookingDiscountCode->discount_type) === 'percent')>Percent off</option>
                            <option value="fixed" @selected(old('discount_type', $doctorBookingDiscountCode->discount_type) === 'fixed')>Fixed (£)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" name="discount_value" class="form-control" step="0.01" min="0" value="{{ old('discount_value', $doctorBookingDiscountCode->discount_value) }}" required>
                    </div>
                    <div class="col-md-6">
                        @php
                            $selectedSvcIds = \App\Models\DoctorBookingDiscountCode::normalizeServiceIdList(
                                old('booking_service_ids', $doctorBookingDiscountCode->selectedBookingServiceIdsForForm())
                            );
                        @endphp
                        <label class="form-label">Limit to specific booking services</label>
                        <select name="booking_service_ids[]" class="form-select" multiple size="{{ min(8, max(3, $services->count() ?: 3)) }}">
                            @foreach($services as $svc)
                            <option value="{{ $svc->id }}" @selected(in_array((int) $svc->id, $selectedSvcIds, true))>{{ $svc->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold <kbd>Ctrl</kbd> (Windows) or <kbd>⌘</kbd> (Mac) to select multiple. Leave none selected for <strong>every</strong> service.</div>
                        @php
                            $missingSelected = collect($selectedSvcIds)->contains(fn ($id) => !$services->contains('id', (int) $id));
                        @endphp
                        @if($missingSelected)
                        <div class="form-text text-warning">Some selected services are not in the list (inactive or disabled for this doctor). Adjust the selection or clear all for “all services”.</div>
                        @elseif($services->isEmpty())
                        <div class="form-text text-warning">No active services are enabled for this doctor. Service-scoped codes may be invalid until pricing is configured.</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Maximum uses</label>
                        <input type="number" name="max_uses" class="form-control" min="1" value="{{ old('max_uses', $doctorBookingDiscountCode->max_uses) }}" placeholder="Unlimited" autocomplete="off">
                        <div class="form-text">Cannot be lower than {{ $doctorBookingDiscountCode->uses_count }} (already used).</div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $doctorBookingDiscountCode->is_active))>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid from</label>
                        <input type="text" name="valid_from" class="form-control uk-date" data-uk-date="true" autocomplete="off" placeholder="dd/mm/yyyy" value="{{ formUkDateOldOrModel(old('valid_from'), $doctorBookingDiscountCode->valid_from) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid until</label>
                        <input type="text" name="valid_until" class="form-control uk-date" data-uk-date="true" autocomplete="off" placeholder="dd/mm/yyyy" value="{{ formUkDateOldOrModel(old('valid_until'), $doctorBookingDiscountCode->valid_until) }}">
                    </div>
                    <div class="col-12">
                        <p class="text-muted small mb-0">Uses recorded: <strong>{{ $doctorBookingDiscountCode->uses_count }}</strong></p>
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
