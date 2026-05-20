@extends('layouts.public-booking')

@section('title', 'Your Details')

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
            <div class="step-label">Date of birth</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">3</div>
            <div class="step-label">Your details</div>
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
            <span class="summary-label">Clinician</span>
            <span class="summary-value">{{ $doctor->full_name }}</span>
        </div>
        @if(!empty($bookingDobYmd ?? null) && !$errors->has('date_of_birth'))
        <div class="summary-row">
            <span class="summary-label">Date of birth</span>
            <span class="summary-value">{{ formatDateUkLongWeekday($bookingDobYmd) }}</span>
        </div>
        @endif
    </div>

    @php
        $pbSessionDobYmd = !empty($bookingDobYmd ?? null) ? \Carbon\Carbon::parse($bookingDobYmd)->format('Y-m-d') : '';
    @endphp
    <form id="patient-details-form" method="POST" action="{{ route('public.booking.non-consultation.review.post') }}" data-session-dob-ymd="{{ $pbSessionDobYmd }}">
        @csrf
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

            @include('public-booking.partials.booking-address-fields')

            @php
                $bookingDobSessionYmd = !empty($bookingDobYmd ?? null) ? \Carbon\Carbon::parse($bookingDobYmd)->format('Y-m-d') : null;
                $showDobPicker = !$bookingDobSessionYmd || $errors->has('date_of_birth');
                $dobHiddenYmd = $bookingDobSessionYmd;
                if (old('date_of_birth')) {
                    $parsedDob = parseDateInput(old('date_of_birth'));
                    if ($parsedDob) {
                        $dobHiddenYmd = $parsedDob;
                    }
                }
                $dobInputYmd = $dobHiddenYmd;
                if (old('date_of_birth')) {
                    $p = parseDateInput(old('date_of_birth'));
                    if ($p) {
                        $dobInputYmd = $p;
                    }
                }
                $pbDobMin = \Carbon\Carbon::now()->subYears(150)->format('Y-m-d');
                $pbDobMax = \Carbon\Carbon::now()->format('Y-m-d');
            @endphp
            @if($bookingDobSessionYmd && !$showDobPicker)
                <input type="hidden" name="date_of_birth" value="{{ $dobHiddenYmd }}">
            @endif
            <div class="row">
                @if($showDobPicker)
                <div class="col-md-6 mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control uk-date uk-date-dob @error('date_of_birth') is-invalid @enderror"
                           id="date_of_birth"
                           name="date_of_birth"
                           required
                           data-uk-date="true"
                           data-min-date="{{ $pbDobMin }}"
                           data-max-date="{{ $pbDobMax }}"
                           value="{{ $dobInputYmd ? formatDateUkSlash($dobInputYmd) : '' }}"
                           autocomplete="bday"
                           inputmode="numeric">
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @endif

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

            @include('public-booking.partials.booking-guardian-fields')

            <input type="hidden" name="consultation_type" value="{{ old('consultation_type', $consultation_type ?? 'in_person') }}">

            <div class="mb-3">
                <label for="notes" class="form-label">Additional information <span class="text-danger">*</span></label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" required placeholder="e.g. I think I have a chest infection">{{ old('notes') }}</textarea>
                @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Shared with your clinician from your booking (before your visit). On this device, a draft is kept for 60 seconds if you refresh the page.</small>
            </div>

            <div class="form-check">
                <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" id="consent" name="consent" value="1" required>
                <label class="form-check-label" for="consent">
                    <span class="text-danger">*</span> <strong>I consent to the processing of my personal data. I understand that this service is not suitable for medical emergencies.</strong>
                </label>
                @error('consent')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="@if($department){{ route('public.booking.clinic', ['slug' => $department->slug]) }}@else{{ route('public.booking.doctor', ['slug' => $doctor->slug]) }}@endif" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <button type="submit" class="btn btn-primary btn-lg">
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('scripts')
@include('public-booking.partials.booking-guardian-toggle-script')
@include('public-booking.partials.ideal-postcodes-public-booking-script')
@endsection
