<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'ThanksDoc EHR' }}</title>
    <meta name="description" content="{{ $pageDescription ?? 'ThanksDoc EHR - Leading Healthcare Excellence' }}">
    
    <!-- Favicon -->
    @if($site_settings['site_favicon'] ?? false)
        <link rel="icon" type="image/x-icon" href="{{ asset($site_settings['site_favicon']) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    
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
    
    <!-- Standard Footer Hover Effects -->
    <style>
        .hover-primary:hover {
            color: #0d6efd !important;
            transition: color 0.3s ease;
        }
        .social-links a:hover {
            transform: translateY(-2px);
            transition: transform 0.3s ease;
        }
        
        /* Topbar Styling */
        .topbar {
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .topbar .social-link {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .topbar .social-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: white;
        }
        
        .topbar a {
            transition: all 0.3s ease;
        }
        
        .topbar a:hover {
            color: #fff !important;
            text-decoration: none;
        }
        
        /* Fixed navbar adjustment for topbar */
        .navbar.fixed-top {
            top: 0;
        }
        
        .detail-hero {
            margin-top: 60px; /* Adjust for topbar + navbar */
        }
        
        /* Navigation improvements for simplified menu */
        .navbar-nav {
            align-items: center;
        }
        
        .navbar-nav .nav-item {
            margin: 0 0.5rem;
        }
        
        .navbar-nav .nav-link {
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: #0d6efd !important;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .navbar-brand img {
            max-height: 50px;
            width: auto;
        }
        
        /* Book Appointment button styling */
        .navbar-nav .btn-primary {
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
        }
        
        .navbar-nav .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .topbar .me-4 {
                margin-right: 1rem !important;
                margin-bottom: 0.5rem;
            }
            
            .topbar .social-links {
                justify-content: center;
                margin-top: 0.5rem;
            }
            
            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
            }
            
            .navbar-nav .nav-item {
                margin: 0.25rem 0;
            }
            
            .navbar-nav .btn-primary {
                margin-top: 0.5rem;
            }
            
            .navbar-brand {
                font-size: 1.25rem;
            }
            
            .navbar-brand img {
                max-height: 40px;
            }
        }
        
        @media (max-width: 576px) {
            .navbar-nav .btn-primary {
                width: 100%;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Topbar -->
    <div class="topbar bg-primary text-white py-2">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center flex-wrap">
                        @if($site_settings['contact_address'] ?? false)
                            <div class="me-4 mb-1">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <span class="small">{!! $site_settings['contact_address'] !!}</span>
                            </div>
                        @endif
                        @if($site_settings['contact_phone'] ?? false)
                            <div class="me-4 mb-1">
                                <i class="fas fa-phone me-2"></i>
                                <a href="tel:{{ $site_settings['contact_phone'] }}" class="text-white text-decoration-none small">{{ $site_settings['contact_phone'] }}</a>
                            </div>
                        @endif
                        @if($site_settings['contact_email'] ?? false)
                            <div class="me-4 mb-1">
                                <i class="fas fa-envelope me-2"></i>
                                <a href="mailto:{{ $site_settings['contact_email'] }}" class="text-white text-decoration-none small">{{ $site_settings['contact_email'] }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-md-end">
                        <div class="social-links d-flex align-items-center">
                            @if(($site_settings['social_facebook'] ?? false) && ($site_settings['social_facebook_enabled'] ?? '1') == '1')
                                <a href="{{ $site_settings['social_facebook'] }}" target="_blank" class="text-white me-3 social-link">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            @endif
                            @if(($site_settings['social_twitter'] ?? false) && ($site_settings['social_twitter_enabled'] ?? '1') == '1')
                                <a href="{{ $site_settings['social_twitter'] }}" target="_blank" class="text-white me-3 social-link">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif
                            @if(($site_settings['social_instagram'] ?? false) && ($site_settings['social_instagram_enabled'] ?? '1') == '1')
                                <a href="{{ $site_settings['social_instagram'] }}" target="_blank" class="text-white me-3 social-link">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            @endif
                            @if(($site_settings['social_linkedin'] ?? false) && ($site_settings['social_linkedin_enabled'] ?? '1') == '1')
                                <a href="{{ $site_settings['social_linkedin'] }}" target="_blank" class="text-white me-3 social-link">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            @endif
                            @if(($site_settings['social_youtube'] ?? false) && ($site_settings['social_youtube_enabled'] ?? '1') == '1')
                                <a href="{{ $site_settings['social_youtube'] }}" target="_blank" class="text-white me-3 social-link">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            @endif
                            @if(($site_settings['social_whatsapp'] ?? false) && ($site_settings['social_whatsapp_enabled'] ?? '1') == '1')
                                <a href="https://wa.me/{{ $site_settings['social_whatsapp'] }}" target="_blank" class="text-white me-3 social-link">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                @if($site_settings['site_logo'] ?? false)
                    <img src="{{ asset($site_settings['site_logo']) }}" alt="{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}" height="50">
                @else
                    <img src="{{ asset('assets/images/hospital-logo.svg') }}" alt="{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}" height="50">
                @endif
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    {{-- Website content pages removed - keeping patient booking only --}}
                    {{-- <li class="nav-item">
                        <a class="nav-link {{ request()->is('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('services*') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('departments*') ? 'active' : '' }}" href="{{ route('departments') }}">Departments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('doctors*') ? 'active' : '' }}" href="{{ route('doctors') }}">Doctors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('faq') ? 'active' : '' }}" href="{{ route('faq') }}">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                    </li> --}}
                    <li class="nav-item ms-3">
                        <a class="btn btn-primary rounded-pill px-4" href="{{ route('appointments.create') }}">
                            <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Hero Section -->
    <section class="detail-hero text-white text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <h1 class="display-4 fw-bold mb-3">{{ $heroTitle ?? 'ThanksDoc EHR' }}</h1>
                    @if(isset($heroSubtitle))
                        <p class="lead mb-4">{{ $heroSubtitle }}</p>
                    @endif
                    @if(isset($showBreadcrumbs) && $showBreadcrumbs)
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center">
                                <li class="breadcrumb-item">
                                    <a href="{{ url('/') }}" class="text-white-50">Home</a>
                                </li>
                                @if(isset($breadcrumbs) && is_array($breadcrumbs))
                                    @foreach($breadcrumbs as $breadcrumb)
                                        @if($loop->last)
                                            <li class="breadcrumb-item active text-white" aria-current="page">
                                                {{ $breadcrumb['text'] }}
                                            </li>
                                        @else
                                            <li class="breadcrumb-item">
                                                <a href="{{ $breadcrumb['url'] }}" class="text-white-50">{{ $breadcrumb['text'] }}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                @else
                                    <li class="breadcrumb-item active text-white" aria-current="page">
                                        {{ $currentPage ?? $heroTitle }}
                                    </li>
                                @endif
                            </ol>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    </section>
