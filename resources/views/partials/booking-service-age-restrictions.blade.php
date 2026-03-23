{{--
  Booking service age rules: mutually exclusive checkboxes (hidden 0 + checkbox 1 for reliable old() input).
  @var \App\Models\BookingService|null $bookingService
--}}
@php
    $underDefault = isset($bookingService) && $bookingService->isUnder18OnlyService() ? '1' : '0';
    $adultDefault = isset($bookingService) && $bookingService->isAdultsOnlyService() ? '1' : '0';
    $underChecked = (string) old('under_18_only', $underDefault) === '1';
    $adultChecked = (string) old('adults_only', $adultDefault) === '1';
@endphp
<div class="mb-3">
    <span class="form-label fw-semibold d-block">Patient age (online booking)</span>
    <small class="text-muted d-block mb-2">Optional. Leave both unchecked to allow any age.</small>

    @error('age_restriction')
    <div class="alert alert-danger py-2 small">{{ $message }}</div>
    @enderror

    <div class="form-check mb-2">
        <input type="hidden" name="under_18_only" value="0">
        <input type="checkbox" class="form-check-input @error('age_restriction') is-invalid @enderror" id="under_18_only" name="under_18_only" value="1" {{ $underChecked ? 'checked' : '' }}>
        <label class="form-check-label" for="under_18_only">Under-18 only (patients under 18)</label>
    </div>
    <div class="form-check">
        <input type="hidden" name="adults_only" value="0">
        <input type="checkbox" class="form-check-input @error('age_restriction') is-invalid @enderror" id="adults_only" name="adults_only" value="1" {{ $adultChecked ? 'checked' : '' }}>
        <label class="form-check-label" for="adults_only">Adults only (18 and over)</label>
    </div>
</div>
<script>
(function () {
    var u = document.getElementById('under_18_only');
    var a = document.getElementById('adults_only');
    if (!u || !a) return;
    u.addEventListener('change', function () { if (u.checked) a.checked = false; });
    a.addEventListener('change', function () { if (a.checked) u.checked = false; });
})();
</script>
