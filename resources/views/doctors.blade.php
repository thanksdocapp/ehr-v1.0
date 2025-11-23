@include('partials.page-header', [
    'pageTitle' => 'Our Doctors - ' . ($site_settings['hospital_name'] ?? 'ThanksDoc EPR'),
    'pageDescription' => 'Meet our expert medical team of qualified doctors and healthcare professionals.',
    'heroTitle' => 'Meet Our Expert Doctors', 
    'heroSubtitle' => 'Our team of highly qualified medical professionals is dedicated to providing exceptional healthcare with compassion and expertise.',
    'showBreadcrumbs' => true,
    'currentPage' => 'Doctors'
])

    <!-- Doctors Filter Section -->
    <section class="py-4 bg-light border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-3 mb-md-0">Filter by Department:</h5>
                </div>
                <div class="col-md-6">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-info btn-sm filter-btn active" data-filter="all">All Doctors</button>
                        @foreach($departments as $department)
                            <button class="btn btn-outline-info btn-sm filter-btn" data-filter="dept-{{ $department->id }}">
                                {{ $department->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Doctors Section -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4" id="doctors-container">
                @forelse($doctors as $index => $doctor)
                    <div class="col-lg-6 col-xl-4 doctor-item" data-department="dept-{{ $doctor->department_id ?? 'none' }}" data-aos="fade-up" data-aos-duration="600" data-aos-delay="{{ $index * 100 }}">
                        <div class="doctor-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="doctor-image position-relative">
                            @if($doctor->photo)
                                <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->first_name }} {{ $doctor->last_name }}" class="img-fluid w-100" style="height: 280px; object-fit: cover;">
                                @else
                                    @php
                                        $docName = strtolower($doctor->specialization ?? 'doctor');
                                        $docColor = 'bg-info';
                                        $docIcon = 'fas fa-user-md';
                                        
                                        // Set colors based on specialization
                                        if (str_contains($docName, 'cardio')) {
                                            $docColor = 'bg-danger';
                                            $docIcon = 'fas fa-heartbeat';
                                        } elseif (str_contains($docName, 'neuro')) {
                                            $docColor = 'bg-primary';
                                            $docIcon = 'fas fa-brain';
                                        } elseif (str_contains($docName, 'surgery')) {
                                            $docColor = 'bg-success';
                                            $docIcon = 'fas fa-procedures';
                                        } elseif (str_contains($docName, 'pediatric')) {
                                            $docColor = 'bg-warning';
                                            $docIcon = 'fas fa-child';
                                        } elseif (str_contains($docName, 'orthopedic')) {
                                            $docColor = 'bg-secondary';
                                            $docIcon = 'fas fa-bone';
                                        }
                                    @endphp
                                    <div class="{{ $docColor }} d-flex align-items-center justify-content-center text-white" style="height: 280px;">
                                        <i class="{{ $docIcon }} fs-1"></i>
                                    </div>
                                @endif
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-info">{{ $doctor->specialization }}</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="fw-bold mb-2">{{ $doctor->first_name }} {{ $doctor->last_name }}</h4>
                                <p class="text-info mb-3 fw-semibold">{{ $doctor->specialization }}</p>
                                @if($doctor->department)
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-building me-2"></i>{{ $doctor->department->name }}
                                    </p>
                                @endif
                                @if($doctor->bio)
                                    <p class="text-muted mb-3">{{ Str::limit($doctor->bio, 100) }}</p>
                                @endif
                                
                                @if($doctor->qualification)
                                    <div class="mb-3">
                                        <h6 class="fw-semibold mb-2">Qualifications:</h6>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach(explode(',', $doctor->qualification) as $qualification)
                                                <span class="badge bg-light text-dark">{{ trim($qualification) }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('doctors.show', $doctor->id) }}" class="btn btn-info">
                                        View Profile <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    @if($doctor->experience_years)
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $doctor->experience_years }} years exp.
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Default doctors when none exist -->
                    <div class="col-lg-6 col-xl-4 doctor-item" data-aos="fade-up" data-aos-duration="600">
                        <div class="doctor-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="doctor-image position-relative">
                                <div class="bg-info d-flex align-items-center justify-content-center text-white" style="height: 280px;">
                                    <i class="fas fa-user-md fs-1"></i>
                                </div>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-info">Cardiologist</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="fw-bold mb-2">Dr. Sarah Johnson</h4>
                                <p class="text-info mb-3 fw-semibold">Cardiologist</p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-building me-2"></i>Cardiology Department
                                </p>
                                <p class="text-muted mb-3">Specialist in cardiac care with over 15 years of experience in treating heart conditions.</p>
                                
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Qualifications:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-light text-dark">MD</span>
                                        <span class="badge bg-light text-dark">FACC</span>
                                        <span class="badge bg-light text-dark">FSCAI</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-info">
                                        View Profile <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>15 years exp.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4 doctor-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="100">
                        <div class="doctor-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="doctor-image position-relative">
                                <div class="bg-primary d-flex align-items-center justify-content-center text-white" style="height: 280px;">
                                    <i class="fas fa-user-md fs-1"></i>
                                </div>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-primary">Neurologist</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="fw-bold mb-2">Dr. Michael Chen</h4>
                                <p class="text-primary mb-3 fw-semibold">Neurologist</p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-building me-2"></i>Neurology Department
                                </p>
                                <p class="text-muted mb-3">Expert in neurological disorders with specialized training in brain and spinal cord conditions.</p>
                                
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Qualifications:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-light text-dark">MD</span>
                                        <span class="badge bg-light text-dark">PhD</span>
                                        <span class="badge bg-light text-dark">FAAN</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-primary">
                                        View Profile <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>12 years exp.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-xl-4 doctor-item" data-aos="fade-up" data-aos-duration="600" data-aos-delay="200">
                        <div class="doctor-card bg-white rounded-3 shadow-sm h-100 overflow-hidden">
                            <div class="doctor-image position-relative">
                                <div class="bg-success d-flex align-items-center justify-content-center text-white" style="height: 280px;">
                                    <i class="fas fa-user-md fs-1"></i>
                                </div>
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-success">Surgeon</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h4 class="fw-bold mb-2">Dr. Emily Rodriguez</h4>
                                <p class="text-success mb-3 fw-semibold">General Surgeon</p>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-building me-2"></i>Surgery Department
                                </p>
                                <p class="text-muted mb-3">Skilled in minimally invasive surgical techniques with expertise in complex procedures.</p>
                                
                                <div class="mb-3">
                                    <h6 class="fw-semibold mb-2">Qualifications:</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span class="badge bg-light text-dark">MD</span>
                                        <span class="badge bg-light text-dark">FACS</span>
                                        <span class="badge bg-light text-dark">FICS</span>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="#" class="btn btn-success">
                                        View Profile <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>10 years exp.
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
    <section class="py-5 bg-info text-white">
        <div class="container">
            <div class="row align-items-center" data-aos="fade-up">
                <div class="col-lg-8">
                    <h2 class="h1 mb-3">Schedule an Appointment with Our Doctors</h2>
                    <p class="lead mb-0">Our experienced medical team is ready to provide you with personalized healthcare. Book your consultation today.</p>
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

        // Doctors Filter
        const filterButtons = document.querySelectorAll('.filter-btn');
        const doctorItems = document.querySelectorAll('.doctor-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter doctors
                doctorItems.forEach(item => {
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
