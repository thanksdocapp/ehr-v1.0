{{-- Patient date of birth before service selection (session: public_booking_dob) --}}
<div class="booking-header">
    <h1>Book Your Appointment</h1>
    <p>{{ $dobIntro ?? 'Enter the patient\'s date of birth first. You will then see services available for this age.' }}</p>
</div>

<div class="progress-steps">
    <div class="step active">
        <div class="step-circle">1</div>
        <div class="step-label">Date of birth</div>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <div class="step-circle">2</div>
        <div class="step-label">Service</div>
    </div>
    <div class="step-line"></div>
    <div class="step">
        <div class="step-circle">3</div>
        <div class="step-label">Your details</div>
    </div>
</div>

<div class="form-card">
    <form method="POST" action="{{ route('public.booking.store-dob') }}" id="public-booking-dob-form">
        @csrf
        <div class="mb-3">
            <label for="public_booking_date_of_birth" class="form-label">Date of birth <span class="text-danger">*</span></label>
            <input type="text"
                   class="form-control uk-date uk-date-dob @error('date_of_birth') is-invalid @enderror"
                   id="public_booking_date_of_birth"
                   name="date_of_birth"
                   data-uk-date="true"
                   required
                   value="{{ old('date_of_birth') }}"
                   placeholder="dd/mm/yyyy"
                   autocomplete="off">
            <small class="form-text text-muted">Format: dd/mm/yyyy</small>
            @error('date_of_birth')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary btn-lg">
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('public_booking_date_of_birth');
    if (el && typeof window.initUkDatePicker === 'function') {
        window.initUkDatePicker(el, { maxDate: 'today' });
    }
});
</script>
