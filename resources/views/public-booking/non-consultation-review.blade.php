@extends('layouts.public-booking')

@section('title', 'Review & Confirm')

@section('content')
    <div class="booking-header">
        <h1>Review & Confirm</h1>
        <p>Please review your booking for <strong>{{ $service->name }}</strong></p>
    </div>

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <form id="nc-confirm-form" method="POST" action="{{ publicBookingNonConsultationUrl('confirm') }}">
        @csrf
        <div class="review-card">
            <div class="review-card-header"><h3>Booking summary</h3></div>
            <div class="review-row"><span class="review-label">Service</span><span class="review-value">{{ $service->name }}</span></div>
            <div class="review-row"><span class="review-label">Clinician</span><span class="review-value">{{ $doctor->full_name }}</span></div>
            <div class="review-row">
                <span class="review-label">Price</span>
                <span class="review-price">
                    @if($price)
                    <span id="pb-list-line">£{{ number_format($price, 2) }}</span>
                    <span id="pb-discount-wrap" class="d-none text-success small d-block mt-1">Discount: −£<span id="pb-discount-amt">0.00</span></span>
                    <span id="pb-due-wrap" class="d-none fw-bold d-block mt-1">You pay: £<span id="pb-due-amt">0.00</span></span>
                    @else
                    Free
                    @endif
                </span>
            </div>
            @if($price && (float) $price > 0)
            <div class="review-row align-items-start">
                <span class="review-label pt-1">Discount code</span>
                <span class="review-value">
                    <div class="input-group input-group-sm" style="max-width: 22rem;">
                        <input type="text" name="discount_code" id="discount_code" class="form-control" maxlength="64" placeholder="Optional" autocomplete="off">
                        <button type="button" class="btn btn-outline-primary px-3" id="discount-apply-btn">Apply</button>
                    </div>
                    <div id="discount-apply-feedback" class="small mt-1"></div>
                </span>
            </div>
            @endif
        </div>

        <div class="review-card">
            <div class="review-card-header"><h3>Your information</h3></div>
            <div class="review-row"><span class="review-label">Name</span><span class="review-value">{{ $patient_data['first_name'] }} {{ $patient_data['last_name'] }}</span></div>
            <div class="review-row"><span class="review-label">Email</span><span class="review-value">{{ $patient_data['email'] }}</span></div>
            <div class="review-row"><span class="review-label">Phone</span><span class="review-value">{{ $patient_data['phone'] }}</span></div>
            @if(!empty($patient_data['date_of_birth']))
            <div class="review-row"><span class="review-label">Date of birth</span><span class="review-value">{{ formatDateUkSlash($patient_data['date_of_birth']) }}</span></div>
            @endif
            <div class="review-row"><span class="review-label">Details</span><span class="review-value">{{ $patient_data['notes'] ?? '—' }}</span></div>
        </div>

        <p class="text-muted small">After you confirm, @if($price && (float) $price > 0) you will complete payment, then @endif you will receive an email. <strong>{{ $doctor->full_name }}</strong> will contact you regarding <strong>{{ $service->name }}</strong>.</p>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ publicBookingNonConsultationUrl('patient-details') }}" class="btn btn-outline-secondary btn-lg">Back</a>
            <button type="submit" class="btn btn-success btn-lg" id="confirm-btn">Confirm booking</button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewUrl = @json($is_clinic_flow ? publicBookingNonConsultationUrl('preview-clinic-discount') : publicBookingNonConsultationUrl('preview-doctor-discount'));
    const discountInput = document.getElementById('discount_code');
    const discountApplyBtn = document.getElementById('discount-apply-btn');
    const discountFeedback = document.getElementById('discount-apply-feedback');
    const discountWrap = document.getElementById('pb-discount-wrap');
    const dueWrap = document.getElementById('pb-due-wrap');
    const discountAmtEl = document.getElementById('pb-discount-amt');
    const dueAmtEl = document.getElementById('pb-due-amt');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!discountInput || !discountApplyBtn) return;
    discountApplyBtn.addEventListener('click', function() {
        const code = (discountInput.value || '').trim();
        if (!code) return;
        discountApplyBtn.disabled = true;
        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ discount_code: code }),
        })
        .then(r => r.json())
        .then(data => {
            discountApplyBtn.disabled = false;
            if (!data.ok) {
                discountFeedback.textContent = data.message || 'Invalid code';
                discountFeedback.className = 'small mt-1 text-danger';
                return;
            }
            discountFeedback.textContent = 'Discount applied';
            discountFeedback.className = 'small mt-1 text-success';
            if (discountWrap && data.discount_amount > 0) {
                discountWrap.classList.remove('d-none');
                discountAmtEl.textContent = parseFloat(data.discount_amount).toFixed(2);
            }
            if (dueWrap) {
                dueWrap.classList.remove('d-none');
                dueAmtEl.textContent = parseFloat(data.payable_fee).toFixed(2);
            }
        })
        .catch(function() {
            discountApplyBtn.disabled = false;
            discountFeedback.textContent = 'Could not verify code';
            discountFeedback.className = 'small mt-1 text-danger';
        });
    });
});
</script>
@endsection
