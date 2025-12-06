@extends('layouts.public-booking')

@section('title', 'Select Doctor')

@section('content')
    <div class="booking-header">
        <h1>Select a Doctor</h1>
        <p>Choose your preferred doctor</p>
    </div>

    <div class="progress-steps">
        <div class="step completed">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line completed"></div>
        <div class="step active">
            <div class="step-circle">2</div>
            <div class="step-label">Doctor</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Date & Time</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">5</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <div class="info-card">
        <h3>{{ $department->name }}</h3>
        <p>Service: {{ $service->name }}</p>
    </div>

    <form id="doctor-form" method="POST" action="{{ route('public.booking.select-datetime') }}">
        @csrf
        <input type="hidden" name="service_id" value="{{ $service->id }}">
        <input type="hidden" name="department_id" value="{{ $department->id }}">

        @if($doctors->isEmpty())
        <div class="text-center py-5">
            <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
            <p class="text-muted">No doctors available for this service.</p>
        </div>
        @else
        <div class="doctors-grid">
            @foreach($doctors as $doctor)
            <label class="doctor-card">
                <input type="radio" name="doctor_id" value="{{ $doctor->id }}" class="doctor-radio" required>
                <div class="doctor-info">
                    <div class="doctor-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="doctor-details">
                        <h4>{{ $doctor->full_name }}</h4>
                        <p>{{ $doctor->specialization }}</p>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
        @endif

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('public.booking.clinic', $department->slug) }}" class="btn btn-outline-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="continue-btn" disabled>
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const doctorCards = document.querySelectorAll('.doctor-card');
        const continueBtn = document.getElementById('continue-btn');

        doctorCards.forEach(card => {
            card.addEventListener('click', function() {
                doctorCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const radio = this.querySelector('.doctor-radio');
                if (radio) {
                    radio.checked = true;
                    continueBtn.disabled = false;
                }
            });
        });
    });
</script>
@endsection
