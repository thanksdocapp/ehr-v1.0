@extends('layouts.public-booking')

@section('title', 'Review & Confirm')
@section('container-width', '700px')

@section('content')
    <div class="booking-header">
        <h1>Review & Confirm</h1>
        <p>Please review your appointment details before confirming</p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="progress-steps">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">3</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <form id="confirm-form" method="POST" action="{{ route('public.booking.confirm') }}">
        @csrf
        <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        <input type="hidden" name="appointment_date" value="{{ $appointment_date }}">
        <input type="hidden" name="appointment_time" value="{{ $appointment_time }}">
        <input type="hidden" name="first_name" value="{{ $patient_data['first_name'] }}">
        <input type="hidden" name="last_name" value="{{ $patient_data['last_name'] }}">
        <input type="hidden" name="email" value="{{ $patient_data['email'] }}">
        <input type="hidden" name="phone" value="{{ $patient_data['phone'] }}">
        <input type="hidden" name="date_of_birth" value="{{ $patient_data['date_of_birth'] ?? '' }}">
        <input type="hidden" name="gender" value="{{ $patient_data['gender'] ?? '' }}">
        <input type="hidden" name="consultation_type" value="{{ $patient_data['consultation_type'] ?? 'in_person' }}">
        @if(isset($patient_data['department_id']))
        <input type="hidden" name="department_id" value="{{ $patient_data['department_id'] }}">
        @endif
        @if(isset($patient_data['notes']))
        <input type="hidden" name="notes" value="{{ $patient_data['notes'] }}">
        @endif

        <div class="review-card">
            <div class="review-card-header">
                <h3><i class="fas fa-calendar-check me-2"></i>Appointment Summary</h3>
            </div>
            <div class="review-row">
                <span class="review-label">Service</span>
                <span class="review-value">{{ $service->name }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Duration</span>
                <span class="review-value">{{ $service->default_duration_minutes ?? 60 }} minutes</span>
            </div>
            <div class="review-row">
                <span class="review-label">Doctor</span>
                <span class="review-value">{{ $doctor->full_name }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Consultation Type</span>
                <span class="review-value">
                    @php $pbCt = $patient_data['consultation_type'] ?? 'in_person'; @endphp
                    @if($pbCt === 'online')
                        <i class="fas fa-video me-1"></i>Online (Video)
                    @elseif($pbCt === 'telephone')
                        <i class="fas fa-phone me-1"></i>Telephone
                    @else
                        <i class="fas fa-hospital me-1"></i>In Person
                    @endif
                </span>
            </div>
            <div class="review-row">
                <span class="review-label">Date</span>
                <span class="review-value">{{ \Carbon\Carbon::parse($appointment_date)->format('l, j F Y') }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Time</span>
                <span class="review-value">{{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Price</span>
                <span class="review-price">
                    @if($price)
                    £{{ number_format($price, 2) }}
                    @else
                    On request
                    @endif
                </span>
            </div>
        </div>

        <div class="review-card">
            <div class="review-card-header">
                <h3><i class="fas fa-user me-2"></i>Your Information</h3>
            </div>
            <div class="review-row">
                <span class="review-label">Name</span>
                <span class="review-value">{{ $patient_data['first_name'] }} {{ $patient_data['last_name'] }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Email</span>
                <span class="review-value">{{ $patient_data['email'] }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Phone</span>
                <span class="review-value">{{ $patient_data['phone'] }}</span>
            </div>
            @if(isset($patient_data['date_of_birth']))
            <div class="review-row">
                <span class="review-label">Date of Birth</span>
                <span class="review-value">{{ \Carbon\Carbon::parse($patient_data['date_of_birth'])->format('d/m/Y') }}</span>
            </div>
            @endif
            @if(isset($patient_data['gender']))
            <div class="review-row">
                <span class="review-label">Gender</span>
                <span class="review-value">{{ ucfirst($patient_data['gender']) }}</span>
            </div>
            @endif
            @if(isset($patient_data['notes']) && $patient_data['notes'])
            <div class="review-row">
                <span class="review-label">Reason for coming in</span>
                <span class="review-value">{{ $patient_data['notes'] }}</span>
            </div>
            @endif
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" onclick="window.history.back()" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <button type="submit" class="btn btn-success btn-lg" id="confirm-btn">
                <i class="fas fa-check me-2"></i>Confirm Appointment
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('confirm-form');
        const confirmBtn = document.getElementById('confirm-btn');

        if (!form || !confirmBtn) {
            console.error('Form or confirm button not found');
            return;
        }

        let isSubmitting = false;

        // Handle form submission
        form.addEventListener('submit', function(e) {
            // Prevent double submission
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }

            isSubmitting = true;

            // Disable button and show loading state
            if (confirmBtn && !confirmBtn.disabled) {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
            }

            // Allow form to submit normally
            return true;
        });

        // Handle button click - just ensure form submits
        confirmBtn.addEventListener('click', function(e) {
            // Log for debugging
            console.log('Confirm button clicked');

            // If form is already submitting, prevent click
            if (isSubmitting) {
                e.preventDefault();
                return false;
            }

            // Trigger form submission
            if (form) {
                // Validate CSRF token is present
                const csrfToken = form.querySelector('input[name="_token"]');
                if (!csrfToken || !csrfToken.value) {
                    console.error('CSRF token missing');
                    e.preventDefault();
                    alert('Security token missing. Please refresh the page and try again.');
                    return false;
                }

                // Let the form submit normally
                return true;
            }
        });
    });
</script>

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
