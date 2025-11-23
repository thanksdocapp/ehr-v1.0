
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking | ThanksDoc EHR</title>
    <meta name="description" content="Schedule an appointment with ThanksDoc EHR's expert doctors. Our online appointment system is quick and easy to use.">

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

</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-heartbeat text-primary me-2 fs-4"></i>
                <span class="fw-bold text-primary">ThanksDoc EHR</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Appointment Booking Section -->
    <section id="appointment" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col text-center" data-aos="fade-up">
                    <h1 class="display-5 fw-bold">Book Your Appointment</h1>
                    <p class="lead text-muted">Simple and secure online scheduling</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('appointments.store') }}" data-aos="fade-up" data-aos-delay="200">
                        @csrf
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="select_doctor" class="form-label">Select Doctor</label>
                            <select class="form-select" id="select_doctor" name="doctor_id" required>
                                <option value="">Choose...</option>
                                <!-- Assume doctors list will be populated dynamically -->
                                <option value="1">Dr. Sarah Johnson - Cardiology</option>
                                <option value="2">Dr. Michael Chen - Neurology</option>
                                <option value="3">Dr. Emily Rodriguez - Pediatrics</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_date" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" id="appointment_date" name="appointment_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="appointment_time" class="form-label">Preferred Time</label>
                            <input type="time" class="form-control" id="appointment_time" name="appointment_time" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-4">Submit Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="footer-brand mb-3">
                        <a class="navbar-brand d-flex align-items-center text-white" href="{{ url('/') }}">
                            <i class="fas fa-heartbeat text-primary me-2 fs-4"></i>
                            <span class="fw-bold">ThanksDoc EHR</span>
                        </a>
                    </div>
                    <p class="text-light mb-3">Leading healthcare excellence with compassionate care, advanced technology, and expert medical professionals.</p>
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
                <div class="col-lg-6 text-lg-end">
                    <p class="text-light mb-0">&copy; {{ date('Y') }} ThanksDoc EHR. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>


