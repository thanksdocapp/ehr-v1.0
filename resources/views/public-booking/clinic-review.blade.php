@extends('layouts.public-booking')

@section('title', 'Review & Confirm')

@section('content')
    <div class="booking-header">
        <h1>Review & Confirm</h1>
        <p>Please review your booking.</p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="clinic-review-errors">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div id="clinic-review-live-error" class="alert alert-danger d-none" role="alert"></div>

    <form id="confirm-form" method="POST" action="{{ route('public.booking.clinic-confirm') }}">
        @csrf
        <input type="hidden" name="department_id" value="{{ $department->id }}">
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
            <div class="review-card-header"><h3><i class="fas fa-calendar-check me-2"></i>Booking Summary</h3></div>
            <div class="review-row"><span class="review-label">Clinic</span><span class="review-value">{{ $department->name }}</span></div>
            <div class="review-row"><span class="review-label">Service</span><span class="review-value">{{ $service->name }}</span></div>
            <div class="review-row"><span class="review-label">Consultation Type</span><span class="review-value">
                @php $ct = $patient_data['consultation_type'] ?? 'in_person'; $ct = in_array($ct, ['phone', 'telephone']) ? 'telephone' : $ct; @endphp
                @if($ct === 'online')
                    <i class="fas fa-video me-1"></i>Online (Video)
                @elseif($ct === 'telephone')
                    <i class="fas fa-phone me-1"></i>Telephone
                @else
                    <i class="fas fa-hospital me-1"></i>In Person
                @endif
            </span></div>
            <div class="review-row"><span class="review-label">Date</span><span class="review-value">{{ formatDateUkLongWeekday($appointment_date) }}</span></div>
            <div class="review-row"><span class="review-label">Time</span><span class="review-value">{{ formatTime($appointment_time, 'g:i A') }}</span></div>
            <div class="review-row"><span class="review-label">Price</span><span class="review-price">
                @if(isset($price) && (float) $price > 0)
                <span id="cl-list-line">£{{ number_format($price, 2) }}</span>
                <span id="cl-discount-wrap" class="d-none text-success small d-block mt-1">Discount: −£<span id="cl-discount-amt">0.00</span></span>
                <span id="cl-due-wrap" class="d-none fw-bold d-block mt-1">You pay: £<span id="cl-due-amt">0.00</span></span>
                @else
                £{{ number_format($price ?? 0, 2) }}
                @endif
            </span></div>
            @if(isset($price) && (float) $price > 0)
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
                    <small class="text-muted d-block mt-1">Enter your code and tap Apply to see your price before payment.</small>
                </span>
            </div>
            @endif
        </div>

        <div class="review-card">
            <div class="review-card-header"><h3><i class="fas fa-user me-2"></i>Your Information</h3></div>
            <div class="review-row"><span class="review-label">Name</span><span class="review-value">{{ ($patient_data['first_name'] ?? '') . ' ' . ($patient_data['last_name'] ?? '') }}</span></div>
            <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $patient_data['email'] ?? '' }}</span></div>
            <div class="review-row"><span class="review-label">Phone</span><span class="review-value">{{ $patient_data['phone'] ?? '' }}</span></div>
            @php
                $clAddr1 = $patient_data['address'] ?? '';
                $clAddr2 = $patient_data['address_line_2'] ?? '';
                $clAddrBlock = trim($clAddr1 . ($clAddr2 !== '' ? "\n".$clAddr2 : ''));
            @endphp
            @if($clAddrBlock !== '')
            <div class="review-row"><span class="review-label">Address</span><span class="review-value">{!! nl2br(e($clAddrBlock)) !!}</span></div>
            @endif
            @if(!empty($patient_data['city']))
            <div class="review-row"><span class="review-label">Town / city</span><span class="review-value">{{ $patient_data['city'] }}</span></div>
            @endif
            @if(!empty($patient_data['state']))
            <div class="review-row"><span class="review-label">County</span><span class="review-value">{{ $patient_data['state'] }}</span></div>
            @endif
            @if(!empty($patient_data['postal_code']))
            <div class="review-row"><span class="review-label">Postcode</span><span class="review-value">{{ $patient_data['postal_code'] }}</span></div>
            @endif
            @if(!empty($patient_data['country']))
            <div class="review-row"><span class="review-label">Country</span><span class="review-value">{{ $patient_data['country'] }}</span></div>
            @endif
            @php
                $clReviewMinor = false;
                if (!empty($patient_data['date_of_birth'] ?? null)) {
                    try {
                        $clReviewMinor = \Carbon\Carbon::parse($patient_data['date_of_birth'])->age < 18;
                    } catch (\Exception $e) {
                        $clReviewMinor = false;
                    }
                }
            @endphp
            @if($clReviewMinor)
            <div class="review-row"><span class="review-label">Guardian / parent name</span><span class="review-value">{{ $patient_data['guardian_name'] ?? '—' }}</span></div>
            <div class="review-row"><span class="review-label">Guardian / parent phone</span><span class="review-value">{{ $patient_data['guardian_phone'] ?? '—' }}</span></div>
            @endif
            <div class="review-row"><span class="review-label">Reason for booking</span><span class="review-value">{{ $patient_data['notes'] ?? '—' }}</span></div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('public.booking.clinic-patient-details.show') }}" class="btn btn-outline-secondary btn-lg me-2"><i class="fas fa-arrow-left me-2"></i>Back</a>
            @php $clinicIsFree = !isset($price) || (float) $price <= 0; @endphp
            <button type="submit" class="btn btn-primary btn-lg" id="confirm-btn">
                @if($clinicIsFree)
                <i class="fas fa-check me-2"></i>Confirm booking
                @else
                <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                @endif
            </button>
        </div>
    </form>

    <div class="text-center mt-5 mb-3">
        <small class="text-muted">Powered by ThanksDoc</small>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('confirm-form');
        const confirmBtn = document.getElementById('confirm-btn');
        const discountInput = document.getElementById('discount_code');
        const discountApplyBtn = document.getElementById('discount-apply-btn');
        const discountFeedback = document.getElementById('discount-apply-feedback');
        const listLine = document.getElementById('cl-list-line');
        const discountWrap = document.getElementById('cl-discount-wrap');
        const dueWrap = document.getElementById('cl-due-wrap');
        const discountAmtEl = document.getElementById('cl-discount-amt');
        const dueAmtEl = document.getElementById('cl-due-amt');
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const liveError = document.getElementById('clinic-review-live-error');
        const pbEmbed = @json(request()->boolean('embed') || session('embed', false));
        const defaultConfirmHtml = confirmBtn ? confirmBtn.innerHTML : '';

        let isSubmitting = false;

        function showLiveError(message) {
            if (!liveError) {
                alert(message);
                return;
            }
            liveError.textContent = message;
            liveError.classList.remove('d-none');
            liveError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function hideLiveError() {
            if (liveError) {
                liveError.textContent = '';
                liveError.classList.add('d-none');
            }
        }

        function navigateAfterBooking(url) {
            if (!url) {
                showLiveError('Booking completed but no redirect URL was returned. Please refresh and check your email.');
                return;
            }
            try {
                if (pbEmbed && window.top && window.top !== window.self) {
                    window.top.location.href = url;
                    return;
                }
            } catch (e) {
                // Fall through to same-frame navigation.
            }
            window.location.href = url;
        }

        function resetConfirmButton() {
            isSubmitting = false;
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = defaultConfirmHtml;
            }
        }

        if (form && confirmBtn) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (isSubmitting) {
                    return false;
                }

                const csrfToken = form.querySelector('input[name="_token"]');
                if (!csrfToken || !csrfToken.value) {
                    showLiveError('Security token missing. Please refresh the page and try again.');
                    return false;
                }

                hideLiveError();
                isSubmitting = true;
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then(function(response) {
                        return response.json()
                            .catch(function() {
                                return { error: response.status === 419
                                    ? 'Your session expired. Please refresh the page and start again.'
                                    : 'Something went wrong. Please try again.' };
                            })
                            .then(function(data) {
                                return { ok: response.ok, status: response.status, data: data };
                            });
                    })
                    .then(function(res) {
                        if (res.ok && res.data && res.data.redirect) {
                            navigateAfterBooking(res.data.redirect);
                            return;
                        }

                        let message = (res.data && res.data.error)
                            ? res.data.error
                            : 'Failed to submit booking request. Please try again.';

                        if (res.data && res.data.errors) {
                            const firstKey = Object.keys(res.data.errors)[0];
                            if (firstKey && res.data.errors[firstKey] && res.data.errors[firstKey][0]) {
                                message = res.data.errors[firstKey][0];
                            }
                        }

                        showLiveError(message);
                        resetConfirmButton();
                    })
                    .catch(function() {
                        showLiveError('Network error. Please check your connection and try again.');
                        resetConfirmButton();
                    });

                return false;
            });
        }

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
            const previewUrl = @json(route('public.booking.preview-clinic-discount'));

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
                        department_id: form.querySelector('input[name="department_id"]')?.value,
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
@endsection
