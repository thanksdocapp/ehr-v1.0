@include('partials.page-header', [
    'pageTitle' => 'FAQ - ' . ($site_settings['hospital_name'] ?? 'ThankDoc EHR'),
    'pageDescription' => 'Find answers to frequently asked questions about our hospital services and healthcare.',
    'heroTitle' => 'Frequently Asked Questions', 
    'heroSubtitle' => 'Find answers to common questions about our hospital services, appointments, and healthcare policies.',
    'showBreadcrumbs' => true,
    'currentPage' => 'FAQ'
])

<!-- FAQ Categories -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3" data-aos="fade-right">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">FAQ Categories</h5>
                        <ul class="list-unstyled">
                            <li><a href="#general" class="text-decoration-none"><i class="fas fa-chevron-right me-2"></i>General Questions</a></li>
                            <li><a href="#appointments" class="text-decoration-none"><i class="fas fa-chevron-right me-2"></i>Appointments</a></li>
                            <li><a href="#services" class="text-decoration-none"><i class="fas fa-chevron-right me-2"></i>Medical Services</a></li>
                            <li><a href="#emergency" class="text-decoration-none"><i class="fas fa-chevron-right me-2"></i>Emergency Care</a></li>
                            <li><a href="#billing" class="text-decoration-none"><i class="fas fa-chevron-right me-2"></i>Insurance & Billing</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Need More Help?</h5>
                        <p class="card-text">Can't find the answer you're looking for?</p>
                        <a href="{{ route('contact') }}" class="btn btn-primary">Contact Support</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9" data-aos="fade-left">
                <!-- General Questions -->
                <div id="general" class="mb-5">
                    <h3 class="mb-4">General Questions</h3>
                    <div class="accordion" id="generalAccordion">
                        @if(isset($faqs) && $faqs->where('category', 'general')->count() > 0)
                            @foreach($faqs->where('category', 'general') as $index => $faq)
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#general{{ $index }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="general{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                                    <div class="accordion-body">
                                        {{ $faq->answer }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#general1">
                                        What services does {{ $site_settings['hospital_name'] ?? 'ThankDoc EHR' }} provide?
                                    </button>
                                </h2>
                                <div id="general1" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                                    <div class="accordion-body">
                                        We provide comprehensive healthcare services including emergency care, specialized medical treatments, surgical procedures, diagnostic services, and preventive care. Our state-of-the-art facility is equipped with the latest medical technology and staffed by experienced healthcare professionals.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#general2">
                                        What are your visiting hours?
                                    </button>
                                </h2>
                                <div id="general2" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                                    <div class="accordion-body">
                                        General visiting hours are from 8:00 AM to 8:00 PM daily. ICU visiting hours are from 10:00 AM to 12:00 PM and 4:00 PM to 6:00 PM. Please check with the specific department as visiting hours may vary by unit.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#general3">
                                        Do you have parking facilities?
                                    </button>
                                </h2>
                                <div id="general3" class="accordion-collapse collapse" data-bs-parent="#generalAccordion">
                                    <div class="accordion-body">
                                        Yes, we have a multi-level parking garage with spaces for patients, visitors, and staff. Parking validation is available for patients and their families. Emergency department patients receive free parking for the first 4 hours.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Appointments Questions -->
                <div id="appointments" class="mb-5">
                    <h3 class="mb-4">Appointments</h3>
                    <div class="accordion" id="appointmentsAccordion">
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#appointments1">
                                    How do I book an appointment?
                                </button>
                            </h2>
                            <div id="appointments1" class="accordion-collapse collapse" data-bs-parent="#appointmentsAccordion">
                                <div class="accordion-body">
                                    You can book an appointment online through our website, call our appointment line, or visit the hospital in person. Online booking is available 24/7 and allows you to choose your preferred doctor, date, and time slot.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#appointments2">
                                    Can I cancel or reschedule my appointment?
                                </button>
                            </h2>
                            <div id="appointments2" class="accordion-collapse collapse" data-bs-parent="#appointmentsAccordion">
                                <div class="accordion-body">
                                    Yes, you can cancel or reschedule your appointment up to 24 hours before the scheduled time. Please call our appointment line or use our online portal. Late cancellations may incur a fee.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item border-0 shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#appointments3">
                                    What should I bring to my appointment?
                                </button>
                            </h2>
                            <div id="appointments3" class="accordion-collapse collapse" data-bs-parent="#appointmentsAccordion">
                                <div class="accordion-body">
                                    Please bring a valid ID, insurance card, list of current medications, and any relevant medical records. If this is your first visit, arrive 15 minutes early to complete registration forms.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Services Questions -->
                <div id="services" class="mb-5">
                    <h3 class="mb-4">Medical Services</h3>
                    <div class="accordion" id="servicesAccordion">
                        @if(isset($faqs) && $faqs->where('category', 'services')->count() > 0)
                            @foreach($faqs->where('category', 'services') as $index => $faq)
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#services{{ $index }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="services{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                                    <div class="accordion-body">
                                        {{ $faq->answer }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#services1">
                                        What medical services do you offer?
                                    </button>
                                </h2>
                                <div id="services1" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                                    <div class="accordion-body">
                                        We offer comprehensive medical services including Emergency Care, Internal Medicine, Cardiology, Orthopedics, Pediatrics, Radiology, Laboratory Services, and Surgical Procedures.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#services2">
                                        Are diagnostic services available?
                                    </button>
                                </h2>
                                <div id="services2" class="accordion-collapse collapse" data-bs-parent="#servicesAccordion">
                                    <div class="accordion-body">
                                        Yes, we offer comprehensive diagnostic services including X-rays, CT scans, MRI, ultrasound, blood tests, and specialized cardiac testing. Most results are available within 24-48 hours.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Emergency Care Questions -->
                <div id="emergency" class="mb-5">
                    <h3 class="mb-4">Emergency Care</h3>
                    <div class="accordion" id="emergencyAccordion">
                        @if(isset($faqs) && $faqs->where('category', 'emergency')->count() > 0)
                            @foreach($faqs->where('category', 'emergency') as $index => $faq)
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#emergency{{ $index }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="emergency{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#emergencyAccordion">
                                    <div class="accordion-body">
                                        {{ $faq->answer }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#emergency1">
                                        When should I go to the emergency room?
                                    </button>
                                </h2>
                                <div id="emergency1" class="accordion-collapse collapse" data-bs-parent="#emergencyAccordion">
                                    <div class="accordion-body">
                                        Visit our emergency room for life-threatening conditions such as chest pain, difficulty breathing, severe injuries, stroke symptoms, or any condition requiring immediate medical attention.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#emergency2">
                                        What should I bring to the emergency room?
                                    </button>
                                </h2>
                                <div id="emergency2" class="accordion-collapse collapse" data-bs-parent="#emergencyAccordion">
                                    <div class="accordion-body">
                                        Please bring a valid ID, insurance card, list of current medications, and any relevant medical records. If possible, have a family member or friend accompany you.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Insurance & Billing Questions -->
                <div id="billing" class="mb-5">
                    <h3 class="mb-4">Insurance & Billing</h3>
                    <div class="accordion" id="billingAccordion">
                        @if(isset($faqs) && $faqs->where('category', 'billing')->count() > 0)
                            @foreach($faqs->where('category', 'billing') as $index => $faq)
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billing{{ $index }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="billing{{ $index }}" class="accordion-collapse collapse" data-bs-parent="#billingAccordion">
                                    <div class="accordion-body">
                                        {{ $faq->answer }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billing1">
                                        What insurance plans do you accept?
                                    </button>
                                </h2>
                                <div id="billing1" class="accordion-collapse collapse" data-bs-parent="#billingAccordion">
                                    <div class="accordion-body">
                                        We accept most major insurance plans including Blue Cross Blue Shield, Aetna, Cigna, UnitedHealth, and Medicare. Please verify your coverage with our billing department before your visit.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#billing2">
                                        How do I pay my medical bills?
                                    </button>
                                </h2>
                                <div id="billing2" class="accordion-collapse collapse" data-bs-parent="#billingAccordion">
                                    <div class="accordion-body">
                                        You can pay your bills online through our patient portal, by phone, by mail, or in person at our billing office. We accept cash, check, and major credit cards.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Support Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center" data-aos="fade-up">
            <div class="col-lg-8">
                <h2 class="h1 mb-3">Still Have Questions?</h2>
                <p class="lead mb-0">Our expert support team is available 24/7 to help you with any questions or concerns.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="{{ route('contact') }}" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
                <a href="tel:+15551234567" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-phone me-2"></i>Call Now
                </a>
            </div>
        </div>
    </div>
</section>

@include('partials.footer')

<!-- Bootstrap 5.3 JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/js/hospital-frontend.js') }}"></script>
    
    <script>
        // Initialize AOS Animation
        AOS.init({
            duration: 1000,
            easing: 'ease-in-out',
            once: true
        });
    </script>
</body>
</html>
