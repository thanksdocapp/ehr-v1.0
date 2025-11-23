@php
use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Details - {{ $department->name }}</title>
    <meta name="description" content="Details of {{ $department->name }} department at ThankDoc EHR.">
    <!-- Bootstrap 5.3 CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome 6.4 -->
    <link href="{{ asset('assets/css/all.min.css') }}" rel="stylesheet">
    <!-- Font Awesome CDN Fallback -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/hospital-frontend.css') }}" rel="stylesheet">
    <!-- Dynamic Theme CSS (loads after static CSS to override variables) -->
    <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ time() }}">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Dynamic Theme CSS -->
    @if($theme_settings && $theme_settings->custom_css)
        <style>
            {{ $theme_settings->custom_css }}
        </style>
    @endif
</head>
<body>
        @include('partials.navbar')

    <!-- Hero Section -->
    <section class="detail-hero text-white text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-4 fw-bold mb-3">{{ $department->name }}</h1>
                    <p class="lead mb-4">Comprehensive medical care with specialized expertise</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('departments') }}" class="text-white-50">Departments</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">{{ $department->name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Department Details Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8" data-aos="fade-right">
                    <div class="info-card">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                                <i class="fas fa-building text-primary fs-1"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold mb-1">{{ $department->name }}</h2>
                                <p class="text-muted mb-0">Medical Department</p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5 class="fw-semibold mb-3">Department Overview</h5>
                            <p class="text-muted">{{ $department->description }}</p>
                        </div>
                        
                        @if($related_services && $related_services->count() > 0)
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">Medical Services Offered</h5>
                                <div class="row g-3">
                                    @foreach($related_services as $service)
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2">
                                                        <i class="fas fa-check text-primary"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <p class="mb-0 fw-medium">{{ $service->name }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <div class="bg-light rounded p-4">
                            <h6 class="fw-semibold mb-3">Why Choose This Department?</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-star text-warning me-2"></i>Expert medical specialists</li>
                                <li class="mb-2"><i class="fas fa-star text-warning me-2"></i>Advanced medical equipment</li>
                                <li class="mb-2"><i class="fas fa-star text-warning me-2"></i>Comprehensive treatment options</li>
                                <li class="mb-0"><i class="fas fa-star text-warning me-2"></i>Patient-centered care approach</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4" data-aos="fade-left">
                    <div class="glass-card p-4 mb-4">
                        <div class="detail-image mb-4">
                            @if($department->image)
                                <img src="{{ Storage::disk('public')->url($department->image) }}" alt="{{ $department->name }}" class="img-fluid rounded-3" style="height: 300px; object-fit: cover; width: 100%;">
                            @else
                                <div class="bg-gradient bg-primary d-flex align-items-center justify-content-center text-white rounded-3" style="height: 300px;">
                                    <i class="fas fa-building fs-1"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-center">
                            <h5 class="fw-bold mb-3">Visit This Department</h5>
                            <p class="text-muted mb-4">Schedule your appointment with our specialized medical team</p>
                            <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-lg w-100 rounded-pill">
                                <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                            </a>
                        </div>
                    </div>
                    
                    <!-- Department Stats -->
                    <div class="profile-stats">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-user-md text-primary fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">{{ $related_doctors->count() }}+</h6>
                                    <small class="text-muted">Specialists</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-heartbeat text-success fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">{{ $related_services->count() }}+</h6>
                                    <small class="text-muted">Services</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-clock text-info fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">24/7</h6>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-award text-warning fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">Top</h6>
                                    <small class="text-muted">Rated</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Doctors Section -->
    @if($related_doctors && $related_doctors->count() > 0)
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-6 fw-bold mb-4">Meet Our Specialists</h2>
                <p class="lead text-muted">Expert medical professionals in {{ $department->name }}</p>
            </div>
            
            <div class="row g-4">
                @foreach($related_doctors as $index => $doctor)
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                        <div class="detail-card bg-white h-100">
                            <div class="detail-image" style="height: 250px;">
                                @if($doctor->photo)
                                    <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->full_name }}" class="img-fluid">
                                @else
                                    <div class="bg-info d-flex align-items-center justify-content-center text-white h-100">
                                        <i class="fas fa-user-md fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h5 class="fw-bold mb-2">{{ $doctor->full_name }}</h5>
                                <p class="text-info mb-3">{{ $doctor->specialization }}</p>
                                @if($doctor->experience_years)
                                    <p class="text-muted mb-3">
                                        <i class="fas fa-clock me-2"></i>{{ $doctor->experience_years }} years experience
                                    </p>
                                @endif
                                <a href="{{ route('doctors.show', $doctor->id) }}" class="btn btn-outline-primary rounded-pill">
                                    View Profile <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-8">
                    <h2 class="h1 mb-3">Ready to Experience {{ $department->name }}?</h2>
                    <p class="lead mb-0">Our specialized medical team is ready to provide you with exceptional care. Schedule your appointment today.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('appointments.create') }}" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-calendar-plus me-2"></i>Book Now
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

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

