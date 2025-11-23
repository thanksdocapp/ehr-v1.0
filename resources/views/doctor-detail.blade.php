@php
use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr. {{ $doctor->full_name }} - Doctor Profile</title>
    <meta name="description" content="Meet Dr. {{ $doctor->full_name }}, {{ $doctor->specialization }} at ThankDoc EHR.">
    
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
                    <h1 class="display-4 fw-bold mb-3">Dr. {{ $doctor->full_name }}</h1>
                    <p class="lead mb-4">{{ $doctor->specialization }} - Expert Medical Professional</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('doctors') }}" class="text-white-50">Doctors</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">{{ $doctor->full_name }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctor Profile Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-4" data-aos="fade-right">
                    <div class="glass-card p-4 mb-4">
                        <div class="detail-image mb-4">
                            @if($doctor->photo)
                                <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->full_name }}" class="img-fluid rounded-3" style="height: 350px; object-fit: cover; width: 100%;">
                            @else
                                <div class="bg-gradient bg-info d-flex align-items-center justify-content-center text-white rounded-3" style="height: 350px;">
                                    <i class="fas fa-user-md fs-1"></i>
                                </div>
                            @endif
                        </div>
                        
                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-2">{{ $doctor->full_name }}</h3>
                            <p class="text-info mb-3 fw-semibold">{{ $doctor->specialization }}</p>
                            
                            @if($doctor->department)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-building me-2"></i>{{ $doctor->department->name }}
                                </p>
                            @endif
                            
                            @if($doctor->experience_years)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-clock me-2"></i>{{ $doctor->experience_years }} years experience
                                </p>
                            @endif
                            
                            @if($doctor->email)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-envelope me-2"></i>{{ $doctor->email }}
                                </p>
                            @endif
                            
                            @if($doctor->phone)
                                <p class="text-muted mb-2">
                                    <i class="fas fa-phone me-2"></i>{{ $doctor->phone }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="text-center">
                            <a href="{{ route('appointments.create') }}" class="btn btn-info btn-lg w-100 rounded-pill">
                                <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                            </a>
                        </div>
                    </div>
                    
                    <!-- Doctor Stats -->
                    <div class="profile-stats">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-users text-primary fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">1000+</h6>
                                    <small class="text-muted">Patients Treated</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-award text-warning fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">5 Star</h6>
                                    <small class="text-muted">Rating</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-certificate text-success fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">Board</h6>
                                    <small class="text-muted">Certified</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-badge">
                                    <i class="fas fa-stethoscope text-info fs-4 mb-2"></i>
                                    <h6 class="fw-bold mb-1">Expert</h6>
                                    <small class="text-muted">Specialist</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8" data-aos="fade-left">
                    <div class="info-card">
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-3">
                                <i class="fas fa-user-md text-info fs-1"></i>
                            </div>
                            <div>
                                <h2 class="fw-bold mb-1">Dr. {{ $doctor->full_name }}</h2>
                                <p class="text-muted mb-0">Expert Medical Professional</p>
                            </div>
                        </div>
                        
                        @if($doctor->bio)
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">Professional Biography</h5>
                                <p class="text-muted">{{ $doctor->bio }}</p>
                            </div>
                        @endif
                        
                        @if($doctor->qualification)
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">Qualifications & Certifications</h5>
                                <div class="row g-3">
                                    @php
                                        $qualifications = is_array($doctor->qualification) ? $doctor->qualification : explode(',', $doctor->qualification);
                                    @endphp
                                    @foreach($qualifications as $qualification)
                                        @if(trim($qualification))
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-info bg-opacity-10 rounded-circle p-2">
                                                            <i class="fas fa-graduation-cap text-info"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="mb-0 fw-medium">{{ trim($qualification) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($doctor->specialties)
                            <div class="mb-4">
                                <h5 class="fw-semibold mb-3">Medical Specializations</h5>
                                <div class="row g-3">
                                    @php
                                        $specialties = is_array($doctor->specialties) ? $doctor->specialties : explode(',', $doctor->specialties);
                                    @endphp
                                    @foreach($specialties as $specialty)
                                        @if(trim($specialty))
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                                            <i class="fas fa-check text-success"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <p class="mb-0 fw-medium">{{ trim($specialty) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        <div class="row g-4 mb-4">
                            @if($doctor->languages)
                                <div class="col-md-6">
                                    <h5 class="fw-semibold mb-3">Languages Spoken</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        @php
                                            $languages = is_array($doctor->languages) ? $doctor->languages : explode(',', $doctor->languages);
                                        @endphp
                                        @foreach($languages as $language)
                                            @if(trim($language))
                                                <span class="badge bg-light text-dark border fw-normal">{{ trim($language) }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            @if($doctor->consultation_fee)
                                <div class="col-md-6">
                                    <h5 class="fw-semibold mb-3">Consultation Fee</h5>
                                    <p class="text-info fs-4 fw-bold mb-0">${{ number_format($doctor->consultation_fee, 2) }}</p>
                                </div>
                            @endif
                        </div>
                        
                        @if($doctor->availability)
                            <div class="bg-light rounded p-4">
                                <h6 class="fw-semibold mb-3">Availability Schedule</h6>
                                <p class="text-muted mb-0">{{ $doctor->availability }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Services Section -->
    @if($related_services && $related_services->count() > 0)
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-6 fw-bold mb-4">Related Services</h2>
                <p class="lead text-muted">Services available in {{ $doctor->department->name ?? 'this department' }}</p>
            </div>
            
            <div class="row g-4">
                @foreach($related_services as $index => $service)
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                        <div class="detail-card bg-white h-100">
                            <div class="detail-image" style="height: 200px;">
                                @if($service->image)
                                    <img src="{{ Storage::disk('public')->url($service->image) }}" alt="{{ $service->name }}" class="img-fluid">
                                @else
                                    <div class="bg-info d-flex align-items-center justify-content-center text-white h-100">
                                        @if($service->icon)
                                            <i class="{{ $service->icon }} fs-1"></i>
                                        @else
                                            <i class="fas fa-heartbeat fs-1"></i>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <h5 class="fw-bold mb-3">{{ $service->name }}</h5>
                                <p class="text-muted mb-3">{{ Str::limit($service->description, 100) }}</p>
                                <a href="{{ route('services.show', $service->id) }}" class="btn btn-outline-info rounded-pill">
                                    Learn More <i class="fas fa-arrow-right ms-1"></i>
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
    <section class="py-5 bg-info text-white">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-8">
                    <h2 class="h1 mb-3">Ready to Schedule Your Appointment?</h2>
                    <p class="lead mb-0">Dr. {{ $doctor->full_name }} is ready to provide you with expert medical care. Book your appointment today.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('appointments.create') }}" class="btn btn-light btn-lg me-3">
                        <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                    </a>
                    <a href="{{ route('contact') }}" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-phone me-2"></i>Contact Us
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
