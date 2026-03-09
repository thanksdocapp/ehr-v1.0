@include('partials.page-header', [
    'pageTitle' => 'Booking Request Received - ' . ($site_settings['hospital_name'] ?? getAppName()),
    'pageDescription' => 'Your booking request has been received',
    'heroTitle' => 'Booking Request Received!',
    'heroSubtitle' => 'A doctor from the clinic will confirm your appointment shortly',
    'showBreadcrumbs' => false
])

<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <div class="success-icon mb-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h2 class="mb-3">Request Submitted Successfully!</h2>
                            <p class="text-muted">We've received your booking request. A doctor from <strong>{{ $request->department->name }}</strong> will review and accept it. You will receive a confirmation email once a doctor has accepted your booking.</p>
                        </div>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body text-start">
                                <h5 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Request Details</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Request Number</small>
                                        <div><strong>{{ $request->request_number }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Status</small>
                                        <div><span class="badge bg-info">Awaiting Doctor</span></div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Clinic</small>
                                        <div><strong>{{ $request->department->name }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Service</small>
                                        <div><strong>{{ $request->service->name ?? 'Consultation' }}</strong></div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Date</small>
                                        <div><strong>{{ $request->appointment_date->format('l, j F Y') }}</strong></div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <small class="text-muted">Time</small>
                                        <div><strong>{{ $request->appointment_time instanceof \DateTimeInterface ? $request->appointment_time->format('g:i A') : $request->appointment_time }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small">Check your email (<strong>{{ $patientEmail }}</strong>) for updates. If you have any questions, please contact the clinic directly.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
