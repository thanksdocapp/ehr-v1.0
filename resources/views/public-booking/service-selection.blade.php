@extends('layouts.public-booking')

@section('title', 'Book Appointment')

@section('content')
    <!-- Header -->
    <div class="booking-header">
        <h1>Book Your Appointment</h1>
        <p>Select a service to continue</p>
    </div>

    <!-- Progress Steps -->
    <div class="progress-steps">
        <div class="step active">
            <div class="step-circle">1</div>
            <div class="step-label">Service</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">2</div>
            <div class="step-label">Date & Time</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">3</div>
            <div class="step-label">Your Details</div>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <div class="step-circle">4</div>
            <div class="step-label">Confirm</div>
        </div>
    </div>

    <!-- Clinic/Doctor Info -->
    @if(isset($department))
    <div class="info-card">
        <h3>{{ $department->name }}</h3>
        @if($department->description)
        <p>{{ $department->description }}</p>
        @endif
    </div>
    @elseif(isset($doctor))
    <div class="info-card">
        <h3>{{ $doctor->full_name }}</h3>
        <p>{{ $doctor->specialization }}</p>
    </div>
    @endif

    <!-- Services Selection -->
    <form id="service-form" method="POST" action="{{ route('public.booking.select-datetime') }}">
        @csrf
        @if(isset($doctor))
        <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        @endif
        @if(isset($department))
        <input type="hidden" name="department_id" value="{{ $department->id }}">
        @endif

        @if($services->isEmpty())
        <div class="empty-state">
            <i class="fas fa-calendar-times"></i>
            <p>No services available at this time.</p>
        </div>
        @else
        <div class="services-grid">
            @foreach($services as $service)
            <label class="service-card">
                <input type="radio" name="service_id" value="{{ $service->id }}" class="service-radio" required>
                <div class="service-card-header">
                    <h4>{{ $service->name }}</h4>
                    @if($service->description)
                    <p>{{ Str::limit($service->description, 100) }}</p>
                    @endif
                </div>
                <div class="service-card-footer">
                    <span class="service-duration">{{ $service->default_duration_minutes ?? 60 }} min</span>
                    <span class="service-price">
                        @if($service->default_price)
                        £{{ number_format($service->default_price, 2) }}
                        @else
                        On request
                        @endif
                    </span>
                </div>
                @if($service->tags && is_array($service->tags) && count($service->tags) > 0)
                <div class="service-tags">
                    @foreach(array_slice($service->tags, 0, 3) as $tag)
                    <span class="service-tag">{{ $tag }}</span>
                    @endforeach
                </div>
                @endif
            </label>
            @endforeach
        </div>
        @endif

        <div class="text-center">
            <button type="submit" class="btn btn-primary btn-lg" id="continue-btn" disabled>
                Continue <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceCards = document.querySelectorAll('.service-card');
        const continueBtn = document.getElementById('continue-btn');
        const form = document.getElementById('service-form');

        serviceCards.forEach(card => {
            card.addEventListener('click', function() {
                // Remove selected class from all cards
                serviceCards.forEach(c => c.classList.remove('selected'));
                // Add selected class to clicked card
                this.classList.add('selected');
                // Check the radio button
                const radio = this.querySelector('.service-radio');
                if (radio) {
                    radio.checked = true;
                    continueBtn.disabled = false;
                }
            });
        });

        form.addEventListener('submit', function(e) {
            const selectedService = document.querySelector('input[name="service_id"]:checked');
            if (!selectedService) {
                e.preventDefault();
                alert('Please select a service to continue.');
                return false;
            }
        });
    });
</script>
@endsection
