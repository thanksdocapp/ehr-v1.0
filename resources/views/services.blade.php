@php
use Illuminate\Support\Facades\Storage;
@endphp

@include('partials.page-header', [
    'pageTitle' => 'Services - ' . ($site_settings['hospital_name'] ?? 'ThankDoc EHR'),
    'pageDescription' => 'Comprehensive medical services with advanced technology and expert healthcare professionals.',
    'heroTitle' => 'Our Medical Services',
    'heroSubtitle' => 'Comprehensive healthcare services delivered with advanced technology, expert medical professionals, and compassionate care.',
    'showBreadcrumbs' => true,
    'currentPage' => 'Services'
])

    <!-- Services Filter Section -->
    <section class="py-4 bg-light border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3 mb-md-0">Filter by Department:</h5>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-primary btn-sm filter-btn active" data-filter="all">All Services</button>
                        @foreach($departments as $department)
                            <button class="btn btn-outline-primary btn-sm filter-btn" data-filter="dept-{{ $department->id }}">
                                {{ $department->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4" id="services-container">
                @forelse($services as $index => $service)
                    <div class="col-lg-6 col-xl-4 service-item" data-department="dept-{{ $service->department_id ?? 'none' }}" data-aos="fade-up" data-aos-duration="600" data-aos-delay="{{ $index * 100 }}">
                        <div class="service-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="service-image">
                                @if($service->image)
                                    <img src="{{ Storage::disk('public')->url($service->image) }}" alt="{{ $service->name }}" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
                                @else
                                    @php
                                        $serviceName = strtolower($service->name);
                                        $serviceColor = 'bg-success';
                                        $serviceIcon = $service->icon ?? 'fas fa-heartbeat';
                                        
                                        // Set colors and icons based on service type
                                        if (str_contains($serviceName, 'emergency') || str_contains($serviceName, 'trauma')) {
                                            $serviceColor = 'bg-danger';
                                            $serviceIcon = 'fas fa-ambulance';
                                        } elseif (str_contains($serviceName, 'surgery') || str_contains($serviceName, 'surgical')) {
                                            $serviceColor = 'bg-info';
                                            $serviceIcon = 'fas fa-procedures';
                                        } elseif (str_contains($serviceName, 'diagnostic') || str_contains($serviceName, 'imaging') || str_contains($serviceName, 'radiology')) {
                                            $serviceColor = 'bg-warning';
                                            $serviceIcon = 'fas fa-x-ray';
                                        } elseif (str_contains($serviceName, 'cardio') || str_contains($serviceName, 'heart')) {
                                            $serviceColor = 'bg-danger';
                                            $serviceIcon = 'fas fa-heartbeat';
                                        } elseif (str_contains($serviceName, 'neuro') || str_contains($serviceName, 'brain')) {
                                            $serviceColor = 'bg-primary';
                                            $serviceIcon = 'fas fa-brain';
                                        } elseif (str_contains($serviceName, 'orthopedic') || str_contains($serviceName, 'bone')) {
                                            $serviceColor = 'bg-secondary';
                                            $serviceIcon = 'fas fa-bone';
                                        } elseif (str_contains($serviceName, 'pediatric') || str_contains($serviceName, 'child')) {
                                            $serviceColor = 'bg-info';
                                            $serviceIcon = 'fas fa-child';
                                        } elseif (str_contains($serviceName, 'maternity') || str_contains($serviceName, 'obstetric')) {
                                            $serviceColor = 'bg-pink';
                                            $serviceIcon = 'fas fa-baby';
                                        } elseif (str_contains($serviceName, 'dental') || str_contains($serviceName, 'oral')) {
                                            $serviceColor = 'bg-info';
                                            $serviceIcon = 'fas fa-tooth';
                                        } elseif (str_contains($serviceName, 'eye') || str_contains($serviceName, 'ophthalm')) {
                                            $serviceColor = 'bg-warning';
                                            $serviceIcon = 'fas fa-eye';
                                        } elseif (str_contains($serviceName, 'mental') || str_contains($serviceName, 'psychiatric')) {
                                            $serviceColor = 'bg-purple';
                                            $serviceIcon = 'fas fa-head-side-virus';
                                        } elseif (str_contains($serviceName, 'laboratory') || str_contains($serviceName, 'lab')) {
                                            $serviceColor = 'bg-success';
                                            $serviceIcon = 'fas fa-flask';
                                        } elseif (str_contains($serviceName, 'pharmacy') || str_contains($serviceName, 'medication')) {
                                            $serviceColor = 'bg-success';
                                            $serviceIcon = 'fas fa-pills';
                                        } elseif (str_contains($serviceName, 'physical') || str_contains($serviceName, 'therapy')) {
                                            $serviceColor = 'bg-info';
                                            $serviceIcon = 'fas fa-dumbbell';
                                        } elseif (str_contains($serviceName, 'dermatology') || str_contains($serviceName, 'skin')) {
                                            $serviceColor = 'bg-warning';
                                            $serviceIcon = 'fas fa-hand-paper';
                                        } elseif (str_contains($serviceName, 'urology') || str_contains($serviceName, 'kidney')) {
                                            $serviceColor = 'bg-primary';
                                            $serviceIcon = 'fas fa-kidneys';
                                        }
                                    @endphp
                                    <div class="{{ $serviceColor }} d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                        <i class="{{ $serviceIcon }} fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    @if($service->icon)
                                        <i class="{{ $service->icon }} text-success fs-3 me-3"></i>
                                    @endif
                                    <h4 class="fw-bold mb-0">{{ $service->name }}</h4>
                                </div>
                                <p class="text-muted mb-3">{{ $service->description }}</p>
                                
                                @if($service->features)
                                    <div class="mb-3">
                                        <h6 class="fw-semibold mb-2">Key Features:</h6>
                                        <ul class="list-unstyled">
                                            @foreach(explode(',', $service->features) as $feature)
                                                <li class="mb-1">
                                                    <i class="fas fa-check text-success me-2"></i>{{ trim($feature) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('services.show', $service->id) }}" class="btn btn-success">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    @if($service->department)
                                        <small class="text-muted">
                                            <i class="fas fa-building me-1"></i>{{ $service->department->name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Default services when none exist -->
                    <div class="col-lg-6 col-xl-4 service-item" data-aos="fade-up" data-aos-duration="600">
                        <div class="service-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="bg-success d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                <i class="fas fa-heartbeat fs-1"></i>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-heartbeat text-success fs-3 me-3"></i>
                                    <h4 class="fw-bold mb-0">Cardiology</h4>
                                </div>
                                <p class="text-muted mb-3">Advanced cardiac care with state-of-the-art equipment and experienced cardiologists.</p>
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Key Features:</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>24/7 Emergency Care</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Advanced Diagnostics</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Surgical Procedures</li>
                                    </ul>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-success">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>Cardiology Dept.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4 service-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                        <div class="service-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="bg-primary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                <i class="fas fa-brain fs-1"></i>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-brain text-primary fs-3 me-3"></i>
                                    <h4 class="fw-bold mb-0">Neurology</h4>
                                </div>
                                <p class="text-muted mb-3">Expert neurological care for complex brain and nervous system disorders.</p>
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Key Features:</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Brain Surgery</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Stroke Treatment</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Epilepsy Care</li>
                                    </ul>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-primary">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>Neurology Dept.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4 service-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                        <div class="service-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="bg-info d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                <i class="fas fa-procedures fs-1"></i>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-procedures text-info fs-3 me-3"></i>
                                    <h4 class="fw-bold mb-0">General Surgery</h4>
                                </div>
                                <p class="text-muted mb-3">Comprehensive surgical services with minimally invasive techniques and advanced technology.</p>
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Key Features:</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Minimally Invasive Surgery</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Robotic Surgery</li>
                                        <li class="mb-1"><i class="fas fa-check text-success me-2"></i>Day Surgery</li>
                                    </ul>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-info">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-building me-1"></i>Surgery Dept.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-success text-white">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-8">
                    <h2 class="h1 mb-3">Ready to Schedule Your Appointment?</h2>
                    <p class="lead mb-0">Our medical services are designed to provide you with the best possible care. Book your appointment today.</p>
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

        // Services Filter
        const filterButtons = document.querySelectorAll('.filter-btn');
        const serviceItems = document.querySelectorAll('.service-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter services
                serviceItems.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-department') === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
