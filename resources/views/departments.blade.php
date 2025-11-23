@php
use Illuminate\Support\Facades\Storage;
@endphp

@include('partials.page-header', [
    'pageTitle' => 'Departments - ' . ($site_settings['hospital_name'] ?? 'ThankDoc EHR'),
    'pageDescription' => 'Explore our comprehensive medical departments with specialized care and expert medical professionals.',
    'heroTitle' => 'Our Medical Departments',
    'heroSubtitle' => 'Comprehensive healthcare across specialized departments with expert medical professionals and state-of-the-art facilities.',
    'showBreadcrumbs' => true,
    'currentPage' => 'Departments'
])

    <!-- Departments Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                @forelse($departments as $index => $department)
                    <div class="col-lg-6 col-xl-4" data-aos="fade-up" data-aos-duration="600" data-aos-delay="{{ $index * 100 }}">
                        <div class="department-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="department-image">
                                @if($department->image)
                                    <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}" alt="{{ $department->name }}" class="img-fluid w-100" style="height: 200px; object-fit: cover;">
                                @else
                                    <div class="bg-primary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                        <i class="{{ $department->icon ?? 'fas fa-hospital' }} fs-1"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    @if($department->icon)
                                        <i class="{{ $department->icon }} text-primary fs-3 me-3"></i>
                                    @endif
                                    <h4 class="fw-bold mb-0">{{ $department->name }}</h4>
                                </div>
                                <p class="text-muted mb-3">{{ $department->description }}</p>
                                
                                @if($department->services && is_array($department->services) && count($department->services) > 0)
                                    <div class="mb-3">
                                        <h6 class="fw-semibold mb-2">Services:</h6>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(array_slice($department->services, 0, 3) as $service)
                                                <span class="badge bg-primary-soft text-primary">{{ $service }}</span>
                                            @endforeach
                                            @if(count($department->services) > 3)
                                                <span class="badge bg-light text-muted">+{{ count($department->services) - 3 }} more</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('departments.show', $department->id) }}" class="btn btn-primary">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    @if(isset($department->doctors_count) && $department->doctors_count > 0)
                                        <small class="text-muted">
                                            <i class="fas fa-user-md me-1"></i>{{ $department->doctors_count }} Doctor{{ $department->doctors_count > 1 ? 's' : '' }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Default departments when none exist -->
                    <div class="col-lg-6 col-xl-4" data-aos="fade-up" data-aos-duration="600">
                        <div class="department-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="bg-primary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                <i class="fas fa-ambulance fs-1"></i>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-ambulance text-primary fs-3 me-3"></i>
                                    <h4 class="fw-bold mb-0">Emergency Department</h4>
                                </div>
                                <p class="text-muted mb-3">24/7 emergency care with immediate medical attention for critical conditions.</p>
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Services:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-primary-soft text-primary">Trauma Care</span>
                                        <span class="badge bg-primary-soft text-primary">Cardiac Emergency</span>
                                        <span class="badge bg-primary-soft text-primary">Stroke Care</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-primary">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-user-md me-1"></i>5 Doctors
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                        <div class="department-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
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
                                    <h6 class="fw-semibold mb-2">Services:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-success-soft text-success">ECG</span>
                                        <span class="badge bg-success-soft text-success">Echocardiography</span>
                                        <span class="badge bg-success-soft text-success">Cardiac Surgery</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-success">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-user-md me-1"></i>3 Doctors
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                        <div class="department-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="bg-info d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                <i class="fas fa-brain fs-1"></i>
                            </div>
                            <div class="p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-brain text-info fs-3 me-3"></i>
                                    <h4 class="fw-bold mb-0">Neurology</h4>
                                </div>
                                <p class="text-muted mb-3">Expert neurological care for complex brain and nervous system disorders.</p>
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Services:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-info-soft text-info">Brain Surgery</span>
                                        <span class="badge bg-info-soft text-info">Stroke Treatment</span>
                                        <span class="badge bg-info-soft text-info">Epilepsy Care</span>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-info">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-user-md me-1"></i>2 Doctors
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
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-8">
                    <h2 class="h1 mb-3">Need Medical Assistance?</h2>
                    <p class="lead mb-0">Our expert medical team is ready to provide you with the best healthcare services across all departments.</p>
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
