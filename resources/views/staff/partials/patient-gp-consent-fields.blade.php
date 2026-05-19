{{-- GP consent and details — used on staff patient create/edit --}}
<input type="hidden" name="gp_section" value="1">
<div class="doctor-card mb-4">
    <div class="doctor-card-header">
        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2 text-primary"></i>GP Consent & Details</h5>
    </div>
    <div class="doctor-card-body">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox"
                   id="consent_share_with_gp" name="consent_share_with_gp" value="1"
                   {{ old('consent_share_with_gp', $patient->consent_share_with_gp ?? false) ? 'checked' : '' }}
                   onchange="handleGpConsentChange(this)">
            <label class="form-check-label" for="consent_share_with_gp" onclick="setTimeout(function(){handleGpConsentChange(document.getElementById('consent_share_with_gp'));}, 10);">
                <strong>I consent for you to share information with my GP.</strong>
            </label>
        </div>
        <small class="text-muted d-block mb-3">By checking this box, the patient authorizes the clinic to share medical information with their GP.</small>
        <small class="text-muted d-block mb-3">
            Any response from the GP should be sent only to the clinic at <strong>{{ config('hospital.gp_reply_to_email', 'gpsurgeryresponses@thanksdoc.co.uk') }}</strong>.
        </small>

        <script>
        function handleGpConsentChange(checkbox) {
            var gpGroup = document.getElementById('gp_details_group');
            var gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];

            if (!gpGroup) return;

            var isChecked = checkbox && (checkbox.checked || checkbox.getAttribute('checked') !== null);

            if (isChecked) {
                gpGroup.style.display = 'block';
                gpGroup.style.visibility = 'visible';
                gpGroup.style.opacity = '1';
                gpGroup.removeAttribute('style');
                gpGroup.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');

                gpFields.forEach(function(fieldId) {
                    var field = document.getElementById(fieldId);
                    if (field) {
                        field.required = true;
                        field.setAttribute('required', 'required');
                    }
                });
            } else {
                gpGroup.style.display = 'none';
                gpGroup.style.visibility = 'hidden';
                gpGroup.removeAttribute('style');
                gpGroup.setAttribute('style', 'display: none !important;');

                gpFields.forEach(function(fieldId) {
                    var field = document.getElementById(fieldId);
                    if (field) {
                        field.required = false;
                        field.removeAttribute('required');
                        field.value = '';
                    }
                });
            }
        }

        (function() {
            var checkbox = document.getElementById('consent_share_with_gp');
            if (checkbox) {
                setTimeout(function() {
                    handleGpConsentChange(checkbox);
                }, 100);
                checkbox.addEventListener('change', function() {
                    handleGpConsentChange(this);
                });
                checkbox.addEventListener('click', function() {
                    setTimeout(function() {
                        handleGpConsentChange(checkbox);
                    }, 10);
                });
            }
        })();
        </script>

        <div id="gp_details_group" style="display: {{ old('consent_share_with_gp', $patient->consent_share_with_gp ?? false) ? 'block' : 'none' }};">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="gp_name" class="form-label fw-semibold">GP Name <span class="text-danger">*</span></label>
                    <input type="text" name="gp_name" id="gp_name"
                           class="form-control @error('gp_name') is-invalid @enderror"
                           value="{{ old('gp_name', $patient->gp_name ?? '') }}"
                           placeholder="Enter GP full name"
                           @if(old('consent_share_with_gp', $patient->consent_share_with_gp ?? false)) required @endif>
                    @error('gp_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gp_email" class="form-label fw-semibold">GP Email <span class="text-danger">*</span></label>
                    <input type="email" name="gp_email" id="gp_email"
                           class="form-control @error('gp_email') is-invalid @enderror"
                           value="{{ old('gp_email', $patient->gp_email ?? '') }}"
                           placeholder="gp@example.com"
                           @if(old('consent_share_with_gp', $patient->consent_share_with_gp ?? false)) required @endif>
                    @error('gp_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gp_phone" class="form-label fw-semibold">GP Phone <span class="text-danger">*</span></label>
                    <input type="tel" name="gp_phone" id="gp_phone"
                           class="form-control @error('gp_phone') is-invalid @enderror"
                           value="{{ old('gp_phone', $patient->gp_phone ?? '') }}"
                           placeholder="+44 ..."
                           @if(old('consent_share_with_gp', $patient->consent_share_with_gp ?? false)) required @endif>
                    @error('gp_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gp_address" class="form-label fw-semibold">GP Address <span class="text-danger">*</span></label>
                    <textarea name="gp_address" id="gp_address" rows="2"
                              class="form-control @error('gp_address') is-invalid @enderror"
                              placeholder="Enter GP clinic address"
                              @if(old('consent_share_with_gp', $patient->consent_share_with_gp ?? false)) required @endif>{{ old('gp_address', $patient->gp_address ?? '') }}</textarea>
                    @error('gp_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>
