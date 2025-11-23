@php
use Illuminate\Support\Facades\Storage;
@endphp
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" 
     @if($site_settings['navbar_background_image'] ?? false)
         style="background-image: url('{{ Storage::disk('public')->url($site_settings['navbar_background_image']) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
     @endif>
    @if($site_settings['navbar_background_image'] ?? false)
        <div class="navbar-overlay"></div>
    @endif
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            @if($site_settings['site_logo'] ?? false)
                <img src="{{ asset($site_settings['site_logo']) }}" alt="{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}" class="me-2" height="50">
            @else
                <img src="{{ asset('assets/images/hospital-logo.svg') }}" alt="{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}" class="me-2" height="50">
            @endif
            @if(Request::is('/'))
                {{-- Show website name only on homepage --}}
                <span class="fw-bold text-primary">{{ $site_settings['hospital_name'] ?? 'ThanksDoc EHR' }}</span>
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                </li>
                {{-- Website content pages removed - keeping patient booking only --}}
                {{-- <li class="nav-item">
                    <a class="nav-link {{ Request::is('about') ? 'active' : '' }}" href="{{ route('about') }}">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('services*') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('departments*') ? 'active' : '' }}" href="{{ route('departments') }}">Departments</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('doctors*') ? 'active' : '' }}" href="{{ route('doctors') }}">Doctors</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                </li> --}}
                
                <!-- Patient Portal Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ Request::is('patient*') ? 'active' : '' }}" href="#" id="patientPortalDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-injured me-1"></i>Patient Portal
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="patientPortalDropdown">
                        @auth('patient')
                            <li><a class="dropdown-item" href="{{ route('patient.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('patient.appointments.index') }}">
                                <i class="fas fa-calendar-check me-2"></i>My Appointments
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('patient.medical-records.index') }}">
                                <i class="fas fa-file-medical me-2"></i>Medical Records
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('patient.billing.index') }}">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Billing
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('patient.profile') }}">
                                <i class="fas fa-user-edit me-2"></i>Profile
                            </a></li>
                            <li>
                                <form method="POST" action="{{ route('patient.logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('patient.login') }}">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Portal
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('patient.register') }}">
                                <i class="fas fa-user-plus me-2"></i>Register as Patient
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><small class="dropdown-item-text text-muted px-3">
                                Access your appointments, <br>medical records, and billing
                            </small></li>
                        @endauth
                    </ul>
                </li>
                
                <li class="nav-item ms-2">
                    <a class="btn btn-primary rounded-pill px-4" href="{{ route('appointments.create') }}">
                        <i class="fas fa-calendar-plus me-2"></i>Book Appointment
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
