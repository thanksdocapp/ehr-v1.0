@php
use Illuminate\Support\Facades\Storage;
@endphp

@include('partials.page-header', [
    'pageTitle' => 'About Us - ' . ($site_settings['site_title'] ?? 'ThanksDoc EHR'),
    'pageDescription' => 'Learn more about ThanksDoc EHR - our mission, vision, and commitment to providing exceptional healthcare services.',
    'heroTitle' => $site_settings['about_hero_title'] ?? 'About ThanksDoc EHR',
    'heroSubtitle' => $site_settings['about_hero_subtitle'] ?? 'Excellence in Healthcare Since 2010',
    'showBreadcrumbs' => true,
    'currentPage' => 'About'
])

    <!-- About Content -->
    <section class="py-5">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <div class="about-image">
                        @php
                            $aboutImage = $site_settings['about_image'] ?? 'assets/images/about-hospital.jpg';
                            $aboutImageUrl = str_starts_with($aboutImage, 'http') ? $aboutImage : 
                                             (str_starts_with($aboutImage, 'assets/') ? asset($aboutImage) : Storage::disk('public')->url($aboutImage));
                        @endphp
                        <img src="{{ $aboutImageUrl }}" alt="{{ $site_settings['about_image_alt'] ?? 'Hospital Building' }}" class="img-fluid rounded-3 shadow">
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000">
                    <div class="about-content ps-lg-5">
                        <div class="section-badge mb-3">
                            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">Our Mission</span>
                        </div>
                        <h2 class="display-5 fw-bold mb-4">{{ $site_settings['about_main_title'] ?? 'Leading Healthcare Excellence' }}</h2>
                        <p class="lead text-muted mb-4">
                            {{ $site_settings['about_main_description'] ?? 'ThanksDoc EHR has been at the forefront of medical innovation, providing exceptional healthcare services to our community for over a decade.' }}
                        </p>
                        <p class="text-muted mb-4">
                            {{ $site_settings['about_main_content'] ?? 'We are committed to delivering comprehensive, compassionate, and cutting-edge healthcare services. Our team of highly skilled medical professionals works tirelessly to ensure that every patient receives the best possible care in a comfortable and supportive environment.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="row g-4 mb-5">
                @foreach ($about_stats as $stat)
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-duration="600">
                        <div class="stat-card text-center p-4 rounded-3 shadow h-100" style="background: {{ $stat->color }}; color: white;">
                            <div class="stat-icon mb-3">
                                <i class="{{ $stat->icon }} fs-1"></i>
                            </div>
                            <h3 class="fw-bold mb-2">{{ $stat->prefix }}{{ $stat->value }}{{ $stat->suffix }}</h3>
                            <p class="mb-0">{{ $stat->title }}</p>
                            @if ($stat->subtitle)
                                <small class="opacity-75">{{ $stat->subtitle }}</small>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Our Values -->
            <div class="row mb-5">
                <div class="col-12" data-aos="fade-up">
                    <div class="text-center mb-5">
                        <div class="section-badge">
                            <span class="badge bg-primary-soft text-primary px-3 py-2 rounded-pill">Our Values</span>
                        </div>
                        <h2 class="display-5 fw-bold mt-3 mb-4">What We Stand For</h2>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-lg-4" data-aos="fade-up" data-aos-duration="600">
                    <div class="value-card text-center p-4 bg-white border rounded-3 shadow-sm h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-heart text-primary fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Compassionate Care</h4>
                        <p class="text-muted">We treat every patient with empathy, respect, and dignity, ensuring they feel valued and supported throughout their healthcare journey.</p>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                    <div class="value-card text-center p-4 bg-white border rounded-3 shadow-sm h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-award text-primary fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Excellence</h4>
                        <p class="text-muted">We strive for the highest standards in medical care, continuously improving our services and embracing innovative treatment methods.</p>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                    <div class="value-card text-center p-4 bg-white border rounded-3 shadow-sm h-100">
                        <div class="value-icon mb-3">
                            <i class="fas fa-shield-alt text-primary fs-1"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Safety First</h4>
                        <p class="text-muted">Patient safety is our top priority. We maintain the highest safety standards and protocols in all our medical procedures and treatments.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8" data-aos="fade-right">
                    <h2 class="display-6 fw-bold mb-3">Ready to Experience Quality Healthcare?</h2>
                    <p class="lead mb-0">Schedule your appointment today and let our expert team take care of your health needs.</p>
                </div>
                <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                    <a href="{{ route('appointments.create') }}" class="btn btn-light btn-lg rounded-pill px-4">
                        <i class="fas fa-calendar-check me-2"></i>Book Appointment
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
    </script>
</body>
</html>
