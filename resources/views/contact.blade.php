@include('partials.page-header', [
    'pageTitle' => 'Contact Us - ' . ($site_settings['hospital_name'] ?? 'ThanksDoc EHR'),
    'pageDescription' => 'Contact ' . ($site_settings['hospital_name'] ?? 'ThanksDoc EHR') . ' for appointments, emergencies, and general inquiries. We\'re here to help you 24/7.',
    'heroTitle' => $site_settings['contact_hero_title'] ?? 'Contact Us',
    'heroSubtitle' => $site_settings['contact_hero_subtitle'] ?? 'Get in touch with our medical team. We\'re here to help you with all your healthcare needs, 24/7.',
    'showBreadcrumbs' => true,
    'currentPage' => 'Contact'
])

    <!-- Contact Information -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-phone text-primary fs-2"></i>
                            </div>
                            <h5>Emergency Hotline</h5>
                            <p class="text-muted mb-3">Available 24/7 for emergencies</p>
                            <p class="fw-bold text-danger">{{ $site_settings['contact_emergency_phone'] ?? '+1 (555) 911-0000' }}</p>
                            <small class="text-muted">General: {{ $site_settings['contact_general_phone'] ?? '+1 (555) 123-4567' }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-envelope text-primary fs-2"></i>
                            </div>
                            <h5>Email Support</h5>
                            <p class="text-muted mb-3">Response within 2-4 hours</p>
                            <p class="fw-bold">{{ $site_settings['contact_email'] ?? 'info@newwaveshospital.com' }}</p>
                            <small class="text-muted">Appointments: {{ $site_settings['contact_appointments_email'] ?? 'appointments@newwaveshospital.com' }}</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 border-0 shadow-sm text-center">
                        <div class="card-body p-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-map-marker-alt text-primary fs-2"></i>
                            </div>
                            <h5>Hospital Address</h5>
                            <p class="text-muted mb-3">Visit us anytime</p>
                            <p class="fw-bold">{!! $site_settings['contact_address'] ?? '123 Medical Center Drive<br>Healthcare City, HC 12345' !!}</p>
                            <small class="text-muted">24/7 Emergency Services Available</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<!-- Contact Form & Map -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <h2 class="section-title text-start">{{ $site_settings['contact_form_title'] ?? 'Send Us a Message' }}</h2>
                <p class="lead mb-4">{{ $site_settings['contact_form_subtitle'] ?? 'Have a question or need assistance? Fill out the form below and our team will get back to you as soon as possible.' }}</p>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('contact.store') }}" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" required>
                            <div class="invalid-feedback">
                                Please provide your first name.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" required>
                            <div class="invalid-feedback">
                                Please provide your last name.
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone">
                        </div>
                        <div class="col-12">
                            <label for="subject" class="form-label">Subject</label>
                            <select class="form-select" id="subject" required>
                                <option value="">Choose a subject...</option>
                                <option value="appointment">Appointment Request</option>
                                <option value="general">General Inquiry</option>
                                <option value="emergency">Emergency Information</option>
                                <option value="services">Medical Services</option>
                                <option value="billing">Billing & Insurance</option>
                                <option value="feedback">Feedback & Complaints</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a subject.
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="5" required placeholder="Please describe your inquiry in detail..."></textarea>
                            <div class="invalid-feedback">
                                Please provide your message.
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="consent" required>
                                <label class="form-check-label" for="consent">
                                    I agree to the <a href="#" class="text-primary">Privacy Policy</a> and <a href="#" class="text-primary">Terms of Service</a>
                                </label>
                                <div class="invalid-feedback">
                                    You must agree to our terms and privacy policy.
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="col-lg-6" data-aos="fade-left">
                <h3 class="mb-4">Find Our Hospital</h3>
                <!-- Embedded Map -->
                <div class="ratio ratio-4x3 mb-4">
                    @if(isset($site_settings['contact_map_embed_url']) && !empty($site_settings['contact_map_embed_url']))
                        <iframe 
                            src="{{ $site_settings['contact_map_embed_url'] }}" 
                            class="rounded shadow" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    @else
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3024.355731049394!2d-74.01084558461596!3d40.70813797923035!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c25a316fd4bb07%3A0x6e9cc2a13dac3bda!2sHealthcare%20District%2C%20Medical%20Center%2C%20USA!5e0!3m2!1sen!2sus!4v1642162729845!5m2!1sen!2sus" 
                            class="rounded shadow" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    @endif
                </div>
                
                <!-- Hospital Hours -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Hospital Hours</h5>
                        <div class="row g-2">
                            <div class="col-6">
                                <strong>Emergency Services:</strong>
                            </div>
                            <div class="col-6 text-success">
                                {{ $site_settings['contact_emergency_hours'] ?? '24/7 Available' }}
                            </div>
                            <div class="col-6">
                                <strong>Outpatient Clinic:</strong>
                            </div>
                            <div class="col-6">
                                {{ $site_settings['contact_outpatient_hours'] ?? '6:00 AM - 10:00 PM' }}
                            </div>
                            <div class="col-6">
                                <strong>Visitor Hours:</strong>
                            </div>
                            <div class="col-6">
                                {{ $site_settings['contact_visitor_hours'] ?? '8:00 AM - 8:00 PM' }}
                            </div>
                            <div class="col-6">
                                <strong>Pharmacy:</strong>
                            </div>
                            <div class="col-6">
                                {{ $site_settings['contact_pharmacy_hours'] ?? '24/7 Available' }}
                            </div>
                            <div class="col-12 mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Emergency services and urgent care available 24/7
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Regional Offices -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Global Offices</h2>
            <p class="lead">We have offices worldwide to serve you better</p>
        </div>
        
        <div class="row g-4">
            @php
                // Define global offices data
                $global_offices = [
                    1 => [
                        'name' => $site_settings['global_office_1_name'] ?? 'New York (HQ)',
                        'address' => $site_settings['global_office_1_address'] ?? "123 Financial District\nNew York, NY 10005",
                        'phone' => $site_settings['global_office_1_phone'] ?? '+1 (555) 123-4567'
                    ],
                    2 => [
                        'name' => $site_settings['global_office_2_name'] ?? 'London',
                        'address' => $site_settings['global_office_2_address'] ?? "456 Canary Wharf\nLondon E14 5AB, UK",
                        'phone' => $site_settings['global_office_2_phone'] ?? '+44 20 7946 0958'
                    ],
                    3 => [
                        'name' => $site_settings['global_office_3_name'] ?? 'Singapore',
                        'address' => $site_settings['global_office_3_address'] ?? "789 Marina Bay\nSingapore 018956",
                        'phone' => $site_settings['global_office_3_phone'] ?? '+65 6789 0123'
                    ],
                    4 => [
                        'name' => $site_settings['global_office_4_name'] ?? 'Dubai',
                        'address' => $site_settings['global_office_4_address'] ?? "321 DIFC\nDubai, UAE",
                        'phone' => $site_settings['global_office_4_phone'] ?? '+971 4 123 4567'
                    ]
                ];
                
                $delay_counter = 100;
            @endphp
            
            @foreach($global_offices as $office_id => $office)
                @if(!empty(trim($office['name'])))
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ $delay_counter }}">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas fa-building text-primary"></i>
                                </div>
                                <h6>{{ $office['name'] }}</h6>
                                @if(!empty(trim($office['address'])))
                                    <p class="small text-muted mb-2">{!! nl2br(e($office['address'])) !!}</p>
                                @endif
                                @if(!empty(trim($office['phone'])))
                                    <p class="small text-muted">{{ $office['phone'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @php $delay_counter += 100; @endphp
                @endif
            @endforeach
        </div>
    </div>
</section>

<!-- FAQ Quick Links -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center" data-aos="fade-up">
            <div class="col-lg-8">
                <h3 class="mb-3">Looking for Quick Answers?</h3>
                <p class="lead mb-0">Check out our comprehensive FAQ section for instant answers to common questions about our services, security, and account management.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('faq') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-question-circle me-2"></i>Visit FAQ
                </a>
            </div>
        </div>
    </div>
</section>

    @include('partials.footer')

    <!-- Scripts -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Back to Top Button
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });

        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            event.preventDefault();
                            // Show success message
                            alert('Thank you for your message! We will get back to you within 24 hours.');
                            form.reset();
                            form.classList.remove('was-validated');
                            return false;
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>
