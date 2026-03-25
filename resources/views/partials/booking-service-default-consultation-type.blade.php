@php
    $consultationSelected = old(
        'default_consultation_type',
        isset($bookingService) && $bookingService
            ? ($bookingService->default_consultation_type ?? 'in_person')
            : 'in_person'
    );
@endphp
<div class="mb-3">
    <label for="default_consultation_type" class="form-label fw-semibold">Default consultation type</label>
    <select name="default_consultation_type"
            id="default_consultation_type"
            class="form-select @error('default_consultation_type') is-invalid @enderror">
        <option value="in_person" @selected($consultationSelected === 'in_person')>In person</option>
        <option value="online" @selected($consultationSelected === 'online')>Online (video)</option>
        <option value="telephone" @selected($consultationSelected === 'telephone')>Telephone</option>
    </select>
    @error('default_consultation_type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Used for public booking. Doctors with a custom assignment can override this.</small>
</div>
