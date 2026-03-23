{{-- Shown when patient DOB is under 18: parent/guardian contact only (no ID upload at booking). --}}
@php
    $guardianVisibleOnLoad = false;
    if (!empty($bookingDobYmd ?? null)) {
        try {
            $guardianVisibleOnLoad = \Carbon\Carbon::parse($bookingDobYmd)->age < 18;
        } catch (\Exception $e) {
            $guardianVisibleOnLoad = false;
        }
    }
@endphp
<div id="public-booking-guardian-wrap"
     class="mb-3 p-3 border rounded"
     style="display: {{ $guardianVisibleOnLoad ? 'block' : 'none' }}; background: #f8fafc;">
    <p class="small fw-semibold text-muted mb-2"><i class="fas fa-user-shield me-1"></i>Parent / guardian (patient under 18)</p>
    <p class="small text-muted mb-3">We need the parent or guardian&rsquo;s name and phone. You do not need to upload ID here; your clinician may ask for it later.</p>
    <div class="row">
        <div class="col-md-6 mb-3 mb-md-0">
            <label for="guardian_name" class="form-label">Guardian / parent name <span class="text-danger public-guardian-req" @if(!$guardianVisibleOnLoad) style="display:none" @endif>*</span></label>
            <input type="text"
                   class="form-control @error('guardian_name') is-invalid @enderror"
                   name="guardian_name"
                   id="guardian_name"
                   value="{{ old('guardian_name') }}"
                   autocomplete="name">
            @error('guardian_name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="guardian_phone" class="form-label">Guardian / parent phone <span class="text-danger public-guardian-req" @if(!$guardianVisibleOnLoad) style="display:none" @endif>*</span></label>
            <input type="tel"
                   class="form-control @error('guardian_phone') is-invalid @enderror"
                   name="guardian_phone"
                   id="guardian_phone"
                   value="{{ old('guardian_phone') }}"
                   autocomplete="tel">
            @error('guardian_phone')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
