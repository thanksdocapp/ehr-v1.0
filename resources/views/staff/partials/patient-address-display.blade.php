@if($patient->address || $patient->city || $patient->state || $patient->country || $patient->postal_code)
<div class="mb-4 pt-3 border-top">
    <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i>Address</p>
    <div class="row">
        @if($patient->full_address || $patient->address)
        <div class="col-12">
            <label class="form-label text-muted small mb-0">Street / address</label>
            <div class="fw-bold">{{ $patient->full_address ?: $patient->address }}</div>
        </div>
        @endif
        @if($patient->city || $patient->state || $patient->country || $patient->postal_code)
        <div class="col-md-6">
            @if($patient->city)
            <label class="form-label text-muted small mb-0">City</label>
            <div class="fw-bold">{{ $patient->city }}</div>
            @endif
            @if($patient->state)
            <label class="form-label text-muted small mb-0">County</label>
            <div class="fw-bold">{{ $patient->state }}</div>
            @endif
        </div>
        <div class="col-md-6">
            @if($patient->country)
            <label class="form-label text-muted small mb-0">Country</label>
            <div class="fw-bold">{{ $patient->country }}</div>
            @endif
            @if($patient->postal_code)
            <label class="form-label text-muted small mb-0">Postcode</label>
            <div class="fw-bold">{{ $patient->postal_code }}</div>
            @endif
        </div>
        @endif
    </div>
</div>
@endif
