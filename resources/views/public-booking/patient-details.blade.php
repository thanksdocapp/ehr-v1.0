@extends('layouts.public-booking')

@section('title', 'Your Details')
@section('container-width', '700px')

@section('content')
    <div class="booking-header">
        <h1>Your Details</h1>
        <p>Please provide your contact information</p>
    </div>

    <div class="progress-steps">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Date & Time</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">3</div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-row">
            <span class="summary-label">Service</span>
            <span class="summary-value">{{ $service->name }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Date & Time</span>
            <span class="summary-value">
                {{ \Carbon\Carbon::parse($appointment_date)->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}
            </span>
        </div>
    </div>

    <form id="patient-details-form" method="POST" action="{{ route('public.booking.review') }}">
        @csrf
        <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        <input type="hidden" name="appointment_date" value="{{ $appointment_date }}">
        <input type="hidden" name="appointment_time" value="{{ $appointment_time }}">
        @if(isset($department_id))
        <input type="hidden" name="department_id" value="{{ $department_id }}">
        @endif

        <div class="form-card">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" required value="{{ old('first_name') }}">
                    @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" required value="{{ old('last_name') }}">
                    @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" required value="{{ old('email') }}">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" required value="{{ old('phone') }}">
                @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" required value="{{ old('date_of_birth') }}" max="{{ date('Y-m-d') }}">
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Consultation Type <span class="text-danger">*</span></label>
                <div class="d-flex gap-3">
                    <div class="form-check form-check-inline consultation-option" style="flex: 1;">
                        <input class="form-check-input @error('consultation_type') is-invalid @enderror" type="radio" id="consultation_in_person" name="consultation_type" value="in_person" {{ old('consultation_type', 'in_person') === 'in_person' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="consultation_in_person" style="cursor: pointer; width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; text-align: center; transition: all 0.2s;">
                            <i class="fas fa-hospital me-2"></i>In-Person Consultation
                        </label>
                    </div>
                    <div class="form-check form-check-inline consultation-option" style="flex: 1;">
                        <input class="form-check-input @error('consultation_type') is-invalid @enderror" type="radio" id="consultation_online" name="consultation_type" value="online" {{ old('consultation_type') === 'online' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="consultation_online" style="cursor: pointer; width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; text-align: center; transition: all 0.2s;">
                            <i class="fas fa-video me-2"></i>Online Consultation
                        </label>
                    </div>
                </div>
                @error('consultation_type')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="notes" class="form-label">Additional Notes (Optional)</label>
                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information you'd like to share...">{{ old('notes') }}</textarea>
            </div>

            <!-- GP Consent & Details -->
            <div class="mb-3">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox"
                           id="consent_share_with_gp" name="consent_share_with_gp" value="1"
                           {{ old('consent_share_with_gp') ? 'checked' : '' }}
                           onchange="handleGpConsentChange(this)">
                    <label class="form-check-label" for="consent_share_with_gp">
                        <strong>I consent for you to share information with my GP.</strong>
                    </label>
                </div>
                <small class="text-muted d-block mb-3">By checking this box, you authorize the hospital to share your medical information with your GP.</small>

                <div id="gp_details_group" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gp_name" class="form-label">GP Name <span class="text-danger">*</span></label>
                            <input type="text" name="gp_name" id="gp_name"
                                   class="form-control @error('gp_name') is-invalid @enderror"
                                   value="{{ old('gp_name') }}" placeholder="Enter GP full name">
                            @error('gp_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gp_email" class="form-label">GP Email <span class="text-danger">*</span></label>
                            <input type="email" name="gp_email" id="gp_email"
                                   class="form-control @error('gp_email') is-invalid @enderror"
                                   value="{{ old('gp_email') }}" placeholder="gp@example.com">
                            @error('gp_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gp_phone" class="form-label">GP Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="gp_phone" id="gp_phone"
                                   class="form-control @error('gp_phone') is-invalid @enderror"
                                   value="{{ old('gp_phone') }}" placeholder="+44 123 456 7890">
                            @error('gp_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gp_address" class="form-label">GP Address <span class="text-danger">*</span></label>
                            <input type="text" name="gp_address" id="gp_address"
                                   class="form-control @error('gp_address') is-invalid @enderror"
                                   value="{{ old('gp_address') }}" placeholder="GP Practice Address">
                            @error('gp_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-check">
                <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" id="consent" name="consent" value="1" required>
                <label class="form-check-label" for="consent">
                    I consent to the processing of my personal data for appointment purposes <span class="text-danger">*</span>
                </label>
                @error('consent')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" onclick="window.history.back()" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <button type="submit" class="btn btn-primary btn-lg">
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    function handleGpConsentChange(checkbox) {
        var gpGroup = document.getElementById('gp_details_group');
        var gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];

        if (!gpGroup) return;

        var isChecked = checkbox && (checkbox.checked || checkbox.getAttribute('checked') !== null);

        if (isChecked) {
            gpGroup.style.display = 'block';
            gpFields.forEach(function(fieldId) {
                var field = document.getElementById(fieldId);
                if (field) {
                    field.required = true;
                }
            });
        } else {
            gpGroup.style.display = 'none';
            gpFields.forEach(function(fieldId) {
                var field = document.getElementById(fieldId);
                if (field) {
                    field.required = false;
                    field.value = '';
                }
            });
        }
    }

    // Initialize on page load
    (function() {
        var checkbox = document.getElementById('consent_share_with_gp');
        if (checkbox) {
            setTimeout(function() {
                handleGpConsentChange(checkbox);
            }, 100);
            checkbox.addEventListener('change', function() {
                handleGpConsentChange(this);
            });
        }
    })();
</script>
@endsection
