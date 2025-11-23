<nav x-data="{ open: false }" class="navbar navbar-expand-lg {{ request()->is('/') ? 'position-fixed w-100' : 'bg-white border-bottom' }}" style="{{ request()->is('/') ? 'top: 0; z-index: 1060;' : '' }}">
    <!-- Primary Navigation Menu -->
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center" style="min-height: 64px;">
            <div class="d-flex align-items-center">
                <!-- Logo -->
                <div class="navbar-brand me-4">
                    <a href="{{ Auth::check() ? route('dashboard') : route('homepage') }}" class="d-flex align-items-center text-decoration-none">
                        <div class="d-flex align-items-center">
                            <x-application-logo class="d-block" style="height: 40px; width: 40px; fill: {{ request()->is('/') ? 'white' : '#374151' }};" />
                            <span class="ms-2 fw-bold fs-5" style="color: {{ request()->is('/') ? 'white' : '#374151' }}">ThanksDoc</span>
                        </div>
                    </a>
                </div>

            </div>
            
            <!-- Navigation Toggle for Mobile -->
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}" style="color: {{ request()->is('/') ? 'white' : '#374151' }}">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    @endauth
                </ul>
                
                <!-- Right Side Navigation -->
                <ul class="navbar-nav">
                    @auth
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: {{ request()->is('/') ? 'white' : '#374151' }}">
                            <i class="fas fa-user-circle me-2"></i>
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>{{ __('Profile') }}</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>{{ __('Log Out') }}
                                    </a>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <!-- Guest Links -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}" style="color: {{ request()->is('/') ? 'white' : '#374151' }}">
                            <i class="fas fa-sign-in-alt me-1"></i>{{ __('Login') }}
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn {{ request()->is('/') ? 'btn-warning' : 'btn-primary' }} btn-sm" href="{{ route('register') }}">
                            <i class="fas fa-user-plus me-1"></i>{{ __('Register') }}
                        </a>
                    </li>
                    @endauth
                </ul>
            </div>

        </div>
    </div>
</nav>