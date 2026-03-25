@extends('layouts.public-booking')

@section('title', 'Review & Confirm')

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
            <div class="step-label">Service &amp; time</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Date of birth</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Your details</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">4</div>
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
        {{-- Multiline patient reason must not use value="..." (breaks newlines / long text). --}}
        <textarea name="notes" class="d-none" tabindex="-1" aria-hidden="true" autocomplete="off">{{ $patient_data['notes'] ?? '' }}</textarea>
        <input type="hidden" name="address" value="{{ $patient_data['address'] ?? '' }}">
        <input type="hidden" name="address_line_2" value="{{ $patient_data['address_line_2'] ?? '' }}">
        <input type="hidden" name="city" value="{{ $patient_data['city'] ?? '' }}">
        <input type="hidden" name="state" value="{{ $patient_data['state'] ?? '' }}">
        <input type="hidden" name="postal_code" value="{{ $patient_data['postal_code'] ?? '' }}">
        <input type="hidden" name="country" value="{{ $patient_data['country'] ?? 'United Kingdom' }}">
        <input type="hidden" name="guardian_name" value="{{ $patient_data['guardian_name'] ?? '' }}">
        <input type="hidden" name="guardian_phone" value="{{ $patient_data['guardian_phone'] ?? '' }}">

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
                    <span id="pb-list-line">£{{ number_format($price, 2) }}</span>
                    <span id="pb-discount-wrap" class="d-none text-success small d-block mt-1">Discount: −£<span id="pb-discount-amt">0.00</span></span>
                    <span id="pb-due-wrap" class="d-none fw-bold d-block mt-1">You pay: £<span id="pb-due-amt">0.00</span></span>
                    @else
                    On request
                    @endif
                </span>
            </div>
            @if($price && (float) $price > 0)
            <div class="review-row align-items-start">
                <span class="review-label pt-1">Discount code</span>
                <span class="review-value">
                    <div class="input-group input-group-sm" style="max-width: 22rem;">
                        <input type="text" name="discount_code" id="discount_code" class="form-control @error('discount_code') is-invalid @enderror" value="{{ old('discount_code') }}" maxlength="64" placeholder="Optional" autocomplete="off">
                        <button type="button" class="btn btn-outline-primary px-3" id="discount-apply-btn">Apply</button>
                    </div>
                    <div id="discount-apply-feedback" class="small mt-1" role="status" aria-live="polite"></div>
                    @error('discount_code')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted d-block mt-1">If your doctor gave you a code, enter it here and tap Apply to see your price. It will be applied when you confirm.</small>
                </span>
            </div>
            @endif
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
            @php
                $pbReviewMinor = false;
                if (!empty($patient_data['date_of_birth'] ?? null)) {
                    try {
                        $pbReviewMinor = \Carbon\Carbon::parse($patient_data['date_of_birth'])->age < 18;
                    } catch (\Exception $e) {
                        $pbReviewMinor = false;
                    }
                }
            @endphp
            @if($pbReviewMinor)
            <div class="review-row">
                <span class="review-label">Guardian / parent name</span>
                <span class="review-value">{{ $patient_data['guardian_name'] ?? '—' }}</span>
            </div>
            <div class="review-row">
                <span class="review-label">Guardian / parent phone</span>
                <span class="review-value">{{ $patient_data['guardian_phone'] ?? '—' }}</span>
            </div>
            @endif
            @php
                $pbAddr1 = $patient_data['address'] ?? '';
                $pbAddr2 = $patient_data['address_line_2'] ?? '';
                $pbAddrBlock = trim($pbAddr1 . ($pbAddr2 !== '' ? "\n".$pbAddr2 : ''));
            @endphp
            @if($pbAddrBlock !== '')
            <div class="review-row">
                <span class="review-label">Address</span>
                <span class="review-value">{!! nl2br(e($pbAddrBlock)) !!}</span>
            </div>
            @endif
            @if(!empty($patient_data['city']))
            <div class="review-row">
                <span class="review-label">Town / city</span>
                <span class="review-value">{{ $patient_data['city'] }}</span>
            </div>
            @endif
            @if(!empty($patient_data['state']))
            <div class="review-row">
                <span class="review-label">County</span>
                <span class="review-value">{{ $patient_data['state'] }}</span>
            </div>
            @endif
            @if(!empty($patient_data['postal_code']))
            <div class="review-row">
                <span class="review-label">Postcode</span>
                <span class="review-value">{{ $patient_data['postal_code'] }}</span>
            </div>
            @endif
            @if(!empty($patient_data['country']))
            <div class="review-row">
                <span class="review-label">Country</span>
                <span class="review-value">{{ $patient_data['country'] }}</span>
            </div>
            @endif
            <div class="review-row">
                <span class="review-label">Reason for booking</span>
                <span class="review-value">{{ $patient_data['notes'] ?? '—' }}</span>
            </div>
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

        const discountInput = document.getElementById('discount_code');
        const discountApplyBtn = document.getElementById('discount-apply-btn');
        const discountFeedback = document.getElementById('discount-apply-feedback');
        const listLine = document.getElementById('pb-list-line');
        const discountWrap = document.getElementById('pb-discount-wrap');
        const dueWrap = document.getElementById('pb-due-wrap');
        const discountAmtEl = document.getElementById('pb-discount-amt');
        const dueAmtEl = document.getElementById('pb-due-amt');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');

        function clearDiscountFeedback() {
            if (!discountFeedback) {
                return;
            }
            discountFeedback.textContent = '';
            discountFeedback.classList.remove('text-success', 'text-danger');
        }

        function resetDiscountPreview() {
            if (discountWrap) {
                discountWrap.classList.add('d-none');
            }
            if (dueWrap) {
                dueWrap.classList.add('d-none');
            }
            clearDiscountFeedback();
        }

        if (discountInput && discountApplyBtn && listLine && form) {
            const previewUrl = @json(route('public.booking.preview-doctor-discount'));

            discountInput.addEventListener('input', resetDiscountPreview);

            discountApplyBtn.addEventListener('click', function() {
                const code = (discountInput.value || '').trim();
                if (!code) {
                    if (discountFeedback) {
                        discountFeedback.textContent = 'Enter a discount code.';
                        discountFeedback.classList.add('text-danger');
                    }
                    return;
                }

                discountApplyBtn.disabled = true;
                fetch(previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        doctor_id: form.querySelector('input[name="doctor_id"]')?.value,
                        service_id: form.querySelector('input[name="service_id"]')?.value,
                        discount_code: code,
                    }),
                })
                    .then(function(response) {
                        return response.json().then(function(data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function(res) {
                        discountApplyBtn.disabled = false;
                        resetDiscountPreview();
                        if (res.data && res.data.ok) {
                            const d = res.data;
                            if (discountAmtEl) {
                                discountAmtEl.textContent = Number(d.discount_amount).toFixed(2);
                            }
                            if (dueAmtEl) {
                                dueAmtEl.textContent = Number(d.amount_due).toFixed(2);
                            }
                            if (discountWrap && Number(d.discount_amount) > 0) {
                                discountWrap.classList.remove('d-none');
                            }
                            if (dueWrap) {
                                dueWrap.classList.remove('d-none');
                            }
                            if (discountFeedback) {
                                discountFeedback.textContent = 'Code applied.';
                                discountFeedback.classList.add('text-success');
                            }
                        } else if (discountFeedback) {
                            discountFeedback.textContent = (res.data && res.data.message)
                                ? res.data.message
                                : 'Could not apply code.';
                            discountFeedback.classList.add('text-danger');
                        }
                    })
                    .catch(function() {
                        discountApplyBtn.disabled = false;
                        if (discountFeedback) {
                            discountFeedback.textContent = 'Something went wrong. Please try again.';
                            discountFeedback.classList.add('text-danger');
                        }
                    });
            });
        }
    });
</script>

    <!-- Footer -->
    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection
