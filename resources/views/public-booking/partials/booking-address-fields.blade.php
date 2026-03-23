{{-- Patient home address (Ideal Postcodes + manual); maps to patients.address, city, state, postal_code, country --}}
<div class="mb-3 pt-2 border-top">
    <p class="text-uppercase small fw-semibold text-muted mb-3"><i class="fas fa-map-marker-alt me-1"></i>Your address</p>
    <div class="mb-3">
        <label for="ideal_postcodes_finder" class="form-label">Find address</label>
        <input type="text"
               id="ideal_postcodes_finder"
               class="form-control"
               placeholder="Start typing postcode or address…"
               autocomplete="off">
        <small class="text-muted" id="ideal_postcodes_notice" style="display:none;"></small>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="address" class="form-label">Address line 1 <span class="text-danger">*</span></label>
            <input type="text" name="address" id="address"
                   class="form-control @error('address') is-invalid @enderror"
                   value="{{ old('address') }}" placeholder="House number and street name" required>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6 mb-3">
            <label for="address_line_2" class="form-label">Address line 2 <span class="text-muted">(optional)</span></label>
            <input type="text" name="address_line_2" id="address_line_2"
                   class="form-control @error('address_line_2') is-invalid @enderror"
                   value="{{ old('address_line_2') }}" placeholder="Apartment, suite, unit, etc.">
            @error('address_line_2')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="city" class="form-label">Town / city <span class="text-danger">*</span></label>
            <input type="text" name="city" id="city"
                   class="form-control @error('city') is-invalid @enderror"
                   value="{{ old('city') }}" placeholder="Town or city" required>
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="state" class="form-label">County <span class="text-muted">(optional)</span></label>
            <input type="text" name="state" id="state"
                   class="form-control @error('state') is-invalid @enderror"
                   value="{{ old('state') }}" placeholder="County">
            @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4 mb-3">
            <label for="postal_code" class="form-label">Postcode <span class="text-danger">*</span></label>
            <input type="text" name="postal_code" id="postal_code"
                   class="form-control @error('postal_code') is-invalid @enderror"
                   value="{{ old('postal_code') }}" placeholder="e.g. SW1A 1AA" required style="text-transform: uppercase;">
            @error('postal_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>
    <input type="hidden" name="country" id="country" value="{{ old('country', 'United Kingdom') }}">
</div>
