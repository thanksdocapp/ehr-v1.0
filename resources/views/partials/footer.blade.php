<!-- Footer -->
<footer class="bg-dark text-white py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <div class="footer-brand mb-4">
                <a class="navbar-brand d-flex align-items-center text-white" href="{{ url('/') }}">
                        @if(isset($site_settings) && ($site_settings['site_logo_dark'] ?? false))
                            <img src="{{ asset($site_settings['site_logo_dark']) }}" alt="{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}" height="50">
                        @elseif(isset($site_settings) && ($site_settings['site_logo'] ?? false))
                            <img src="{{ asset($site_settings['site_logo']) }}" alt="{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}" height="50">
                        @else
                            <div style="color: white; font-weight: 600; font-size: 1.5rem;">{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}</div>
                        @endif
                    </a>
                </div>
                <p class="text-light mb-4">
                    {{ $site_settings['footer_description'] ?? 'Leading healthcare excellence with compassionate care, advanced technology, and expert medical professionals.' }}
                </p>
                <div class="social-links">
                    @if(($site_settings['social_facebook'] ?? false) && ($site_settings['social_facebook_enabled'] ?? '1') == '1')
                        <a href="{{ $site_settings['social_facebook'] }}" class="text-white me-3 fs-5" target="_blank"><i class="fab fa-facebook"></i></a>
                    @endif
                    @if(($site_settings['social_twitter'] ?? false) && ($site_settings['social_twitter_enabled'] ?? '1') == '1')
                        <a href="{{ $site_settings['social_twitter'] }}" class="text-white me-3 fs-5" target="_blank"><i class="fab fa-twitter"></i></a>
                    @endif
                    @if(($site_settings['social_instagram'] ?? false) && ($site_settings['social_instagram_enabled'] ?? '1') == '1')
                        <a href="{{ $site_settings['social_instagram'] }}" class="text-white me-3 fs-5" target="_blank"><i class="fab fa-instagram"></i></a>
                    @endif
                    @if(($site_settings['social_linkedin'] ?? false) && ($site_settings['social_linkedin_enabled'] ?? '1') == '1')
                        <a href="{{ $site_settings['social_linkedin'] }}" class="text-white me-3 fs-5" target="_blank"><i class="fab fa-linkedin"></i></a>
                    @endif
                    @if(($site_settings['social_youtube'] ?? false) && ($site_settings['social_youtube_enabled'] ?? '1') == '1')
                        <a href="{{ $site_settings['social_youtube'] }}" class="text-white me-3 fs-5" target="_blank"><i class="fab fa-youtube"></i></a>
                    @endif
                    @if(($site_settings['social_whatsapp'] ?? false) && ($site_settings['social_whatsapp_enabled'] ?? '1') == '1')
                        <a href="https://wa.me/{{ $site_settings['social_whatsapp'] }}" class="text-white me-3 fs-5" target="_blank"><i class="fab fa-whatsapp"></i></a>
                    @endif
                </div>
            </div>
            
            <div class="col-lg-2">
                <h5 class="fw-bold mb-4 text-white">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ url('/') }}" class="text-light text-decoration-none hover-primary">Home</a></li>
                    {{-- Website content pages removed - keeping patient booking only --}}
                    {{-- <li class="mb-2"><a href="{{ route('about') }}" class="text-light text-decoration-none hover-primary">About</a></li>
                    <li class="mb-2"><a href="{{ route('services') }}" class="text-light text-decoration-none hover-primary">Services</a></li>
                    <li class="mb-2"><a href="{{ route('departments') }}" class="text-light text-decoration-none hover-primary">Departments</a></li>
                    <li class="mb-2"><a href="{{ route('doctors') }}" class="text-light text-decoration-none hover-primary">Doctors</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}" class="text-light text-decoration-none hover-primary">Contact</a></li> --}}
                    <li class="mb-2"><a href="{{ route('appointments.create') }}" class="text-light text-decoration-none hover-primary">Book Appointment</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2">
                <h5 class="fw-bold mb-4 text-white">Patient Portal</h5>
                <ul class="list-unstyled">
                    @auth('patient')
                        <li class="mb-2"><a href="{{ route('patient.dashboard') }}" class="text-light text-decoration-none hover-primary"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                        <li class="mb-2"><a href="{{ route('patient.appointments.index') }}" class="text-light text-decoration-none hover-primary"><i class="fas fa-calendar-check me-1"></i>Appointments</a></li>
                        <li class="mb-2"><a href="{{ route('patient.medical-records.index') }}" class="text-light text-decoration-none hover-primary"><i class="fas fa-file-medical me-1"></i>Records</a></li>
                        <li class="mb-2"><a href="{{ route('patient.billing.index') }}" class="text-light text-decoration-none hover-primary"><i class="fas fa-file-invoice-dollar me-1"></i>Billing</a></li>
                        <li class="mb-2"><a href="{{ route('patient.profile') }}" class="text-light text-decoration-none hover-primary"><i class="fas fa-user-cog me-1"></i>Profile</a></li>
                    @else
                        <li class="mb-3">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('patient.login') }}" class="btn btn-outline-light btn-sm rounded-pill">
                                    <i class="fas fa-sign-in-alt me-1"></i>Patient Login
                                </a>
                                <a href="{{ route('patient.register') }}" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="fas fa-user-plus me-1"></i>Register Now
                                </a>
                            </div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted d-block mb-1">Secure Access:</small>
                            <small class="text-light">• View appointments &amp; records</small><br>
                            <small class="text-light">• Manage billing &amp; payments</small><br>
                            <small class="text-light">• 24/7 online access</small>
                        </li>
                    @endauth
                </ul>
            </div>
            
            {{-- Services section removed - website content pages disabled --}}
            {{-- <div class="col-lg-2">
                <h5 class="fw-bold mb-4 text-white">Services</h5>
                <ul class="list-unstyled">
                    {{-- Services links removed - website content pages removed --}}
                    {{-- <li class="mb-2"><a href="{{ route('services') }}" class="text-light text-decoration-none hover-primary">Emergency Care</a></li>
                    <li class="mb-2"><a href="{{ route('services') }}" class="text-light text-decoration-none hover-primary">Cardiology</a></li>
                    <li class="mb-2"><a href="{{ route('services') }}" class="text-light text-decoration-none hover-primary">Neurology</a></li>
                    <li class="mb-2"><a href="{{ route('services') }}" class="text-light text-decoration-none hover-primary">Pediatrics</a></li>
                    <li class="mb-2"><a href="{{ route('services') }}" class="text-light text-decoration-none hover-primary">Surgery</a></li> --}}
                </ul>
            </div> --}}
            
            <div class="col-lg-3">
                <h5 class="fw-bold mb-4 text-white">Contact Info</h5>
                <ul class="list-unstyled">
                    @if($site_settings['contact_address'] ?? false)
                        <li class="mb-2 text-light">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            {!! $site_settings['contact_address'] !!}
                        </li>
                    @endif
                    @if($site_settings['contact_phone'] ?? false)
                        <li class="mb-2 text-light">
                            <i class="fas fa-phone me-2"></i>
                            <a href="tel:{{ $site_settings['contact_phone'] }}" class="text-light text-decoration-none hover-primary">{{ $site_settings['contact_phone'] }}</a>
                        </li>
                    @endif
                    @if($site_settings['contact_email'] ?? false)
                        <li class="mb-2 text-light">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:{{ $site_settings['contact_email'] }}" class="text-light text-decoration-none hover-primary">{{ $site_settings['contact_email'] }}</a>
                        </li>
                    @endif
                    @if($site_settings['footer_working_hours'] ?? false)
                        <li class="mb-2 text-light">
                            <i class="fas fa-clock me-2"></i>
                            {{ $site_settings['footer_working_hours'] }}
                        </li>
                    @endif
                    @if($site_settings['contact_emergency_phone'] ?? false)
                        <li class="mb-2 text-light">
                            <i class="fas fa-ambulance me-2"></i>
                            Emergency: <a href="tel:{{ $site_settings['contact_emergency_phone'] }}" class="text-warning text-decoration-none fw-bold">{{ $site_settings['contact_emergency_phone'] }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-light mb-0">
                    &copy; {{ date('Y') }} {{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}. {{ $site_settings['footer_copyright'] ?? 'All rights reserved.' }}
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="#" class="text-light text-decoration-none me-3 hover-primary">Privacy Policy</a>
                <a href="#" class="text-light text-decoration-none hover-primary">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow" style="display: none;">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
.hover-primary:hover {
    color: #007bff !important;
    transition: color 0.3s ease;
}

.social-links a:hover {
    color: #007bff !important;
    transition: color 0.3s ease;
}

.footer-brand:hover {
    opacity: 0.9;
    transition: opacity 0.3s ease;
}
</style>
