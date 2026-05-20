<div class="mb-3">
    <div class="form-check form-switch">
        <input class="form-check-input"
               type="checkbox"
               id="is_non_consultation"
               name="is_non_consultation"
               value="1"
               {{ old('is_non_consultation', $bookingService->is_non_consultation ?? false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_non_consultation">
            Non-consultation service (no appointment slot)
        </label>
    </div>
    <small class="text-muted d-block">Patients book without choosing a date or time. They pay (or skip if free), then you contact them.</small>
</div>

<div id="consultation-type-wrap" class="mb-3">
    <label for="consultation_type" class="form-label fw-semibold">Consultation Type <span class="text-danger consultation-type-required">*</span></label>
    <select class="form-select @error('consultation_type') is-invalid @enderror"
            id="consultation_type"
            name="consultation_type"
            required>
        <option value="in_person" {{ old('consultation_type', $defaultConsultationType ?? 'in_person') == 'in_person' ? 'selected' : '' }}>In Person</option>
        <option value="online" {{ old('consultation_type', $defaultConsultationType ?? '') == 'online' ? 'selected' : '' }}>Online (Video)</option>
        <option value="telephone" {{ old('consultation_type', $defaultConsultationType ?? '') == 'telephone' ? 'selected' : '' }}>Telephone</option>
    </select>
    @error('consultation_type')
    <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="text-muted">How patients book this service when it is a consultation.</small>
</div>

<script>
(function() {
    function syncNonConsultationToggle() {
        var toggle = document.getElementById('is_non_consultation');
        var wrap = document.getElementById('consultation-type-wrap');
        var select = document.getElementById('consultation_type');
        if (!toggle || !wrap) {
            return;
        }
        var hide = toggle.checked;
        wrap.style.display = hide ? 'none' : '';
        if (select) {
            if (hide) {
                select.removeAttribute('required');
            } else {
                select.setAttribute('required', 'required');
            }
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('is_non_consultation');
        if (toggle) {
            toggle.addEventListener('change', syncNonConsultationToggle);
            syncNonConsultationToggle();
        }
    });
})();
</script>
