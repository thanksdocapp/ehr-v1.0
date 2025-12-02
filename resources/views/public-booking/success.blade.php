@include('partials.page-header', [
    'pageTitle' => 'Booking Confirmed - ' . ($site_settings['hospital_name'] ?? getAppName()),
    'pageDescription' => 'Your appointment has been successfully booked',
    'heroTitle' => 'Booking Confirmed!',
    'heroSubtitle' => 'Your appointment has been successfully scheduled',
    'showBreadcrumbs' => false
])

<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Success Card -->
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <div class="success-icon mb-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h2 class="mb-3">Appointment Booked Successfully!</h2>
                            <p class="text-muted">We've sent a confirmation email to <strong>{{ $appointment->patient->email }}</strong></p>
                        </div>

                        <!-- Appointment Details -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body text-start">
                                <h5 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Appointment Details</h5>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Appointment Number</small>
                                        <div><strong>{{ $appointment->appointment_number }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Status</small>
                                        <div>
                                            <span class="badge bg-warning text-dark">{{ ucfirst($appointment->status) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Service</small>
                                        <div><strong>{{ $appointment->service->name ?? 'Consultation' }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Doctor</small>
                                        <div><strong>{{ $appointment->doctor->full_name }}</strong></div>
                                        <small class="text-muted">{{ $appointment->doctor->specialization }}</small>
                                    </div>
                                </div>

                                <hr>

                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Date</small>
                                        <div><strong>{{ $appointment->appointment_date->format('l, F j, Y') }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Time</small>
                                        <div><strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</strong></div>
                                    </div>
                                </div>

                                @if($appointment->fee)
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <small class="text-muted">Fee</small>
                                        <div><strong class="h5">£{{ number_format($appointment->fee, 2) }}</strong></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Patient Information -->
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body text-start">
                                <h5 class="mb-3"><i class="fas fa-user me-2"></i>Patient Information</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Name</small>
                                        <div><strong>{{ $appointment->patient->full_name }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Email</small>
                                        <div>{{ $appointment->patient->email }}</div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Phone</small>
                                        <div>{{ $appointment->patient->phone }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="alert alert-info border-0 mb-4">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Important Information</h6>
                            <ul class="mb-0 text-start">
                                <li>Please arrive 10 minutes before your appointment time</li>
                                <li>Bring a valid ID and any relevant medical documents</li>
                                <li>You will receive a reminder email 24 hours before your appointment</li>
                                <li>If you need to cancel or reschedule, please contact us at least 24 hours in advance</li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex flex-column flex-md-row gap-3 justify-content-center">
                            <a href="{{ route('public.booking.doctor', $appointment->doctor->slug) }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-calendar-plus me-2"></i>Book Another Appointment
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-print me-2"></i>Print Confirmation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.success-icon {
    font-size: 80px;
    color: #198754;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.card {
    border-radius: 12px;
}

@media print {
    .btn {
        display: none;
    }
}
</style>

@include('partials.footer')

