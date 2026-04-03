@extends('admin.layouts.app')

@section('title', 'New doctor booking code')

@section('content')
<div class="container-fluid">
    <div class="mb-3">
        <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white"><strong>New code — {{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }}</strong></div>
        <div class="card-body">
            @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.doctors.booking-discount-codes.store', $doctor) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code') }}" maxlength="64" required autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select" required>
                            <option value="percent" @selected(old('discount_type') === 'percent')>Percent off</option>
                            <option value="fixed" @selected(old('discount_type', 'fixed') === 'fixed')>Fixed (£)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" name="discount_value" class="form-control" step="0.01" min="0" value="{{ old('discount_value') }}" required>
                    </div>
                    <div class="col-md-6">
                        @php $selectedSvcIds = \App\Models\DoctorBookingDiscountCode::normalizeServiceIdList(old('booking_service_ids')); @endphp
                        <label class="form-label">Limit to specific booking services</label>
                        <select name="booking_service_ids[]" class="form-select" multiple size="{{ min(8, max(3, $services->count() ?: 3)) }}">
                            @foreach($services as $svc)
                            <option value="{{ $svc->id }}" @selected(in_array((int) $svc->id, $selectedSvcIds, true))>{{ $svc->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold <kbd>Ctrl</kbd> (Windows) or <kbd>⌘</kbd> (Mac) to select multiple. Leave none selected so the code applies to <strong>every</strong> service this doctor offers.</div>
                        @if($services->isEmpty())
                        <div class="form-text text-warning">No active services are enabled for this doctor in admin pricing. Codes will only work as “all services” until services are configured.</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Maximum uses</label>
                        <input type="number" name="max_uses" class="form-control" min="1" value="{{ old('max_uses') }}" placeholder="Unlimited" autocomplete="off">
                        <div class="form-text">Optional. Leave empty for unlimited redemptions.</div>
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
                        <input type="text" name="valid_from" class="form-control uk-date" data-uk-date="true" autocomplete="off" placeholder="dd/mm/yyyy" value="{{ formUkDateOldOrModel(old('valid_from'), null) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Valid until</label>
                        <input type="text" name="valid_until" class="form-control uk-date" data-uk-date="true" autocomplete="off" placeholder="dd/mm/yyyy" value="{{ formUkDateOldOrModel(old('valid_until'), null) }}">
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
