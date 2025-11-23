<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@php
        use Illuminate\Support\Facades\Storage;
        $siteSettings = \App\Models\SiteSetting::getSettings();
        $hospitalName = $siteSettings['hospital_name'] ?? config('app.name', 'Hospital Management');
        $favicon = $siteSettings['site_favicon'] ?? 'favicon.ico';
    @endphp
    <title>@yield('title', 'Patient Portal') - {{ $hospitalName }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset($favicon) }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --gold-color: #e94560;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: #333;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 10px;
        }

        .sidebar-title {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-left-color: var(--gold-color);
        }

        .nav-link i {
            width: 20px;
            margin-right: 12px;
            text-align: center;
        }

        .user-profile {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }

        .user-info {
            display: flex;
            align-items: center;
            color: white;
            margin-bottom: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-id {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid #e9ecef;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
        }

        .content-area {
            padding: 30px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .card-title {
            margin: 0;
            font-weight: 600;
        }

        /* Stat Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Colors */
        .bg-primary-gradient {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .bg-success-gradient {
            background: linear-gradient(135deg, var(--success-color) 0%, #20c997 100%);
        }

        .bg-warning-gradient {
            background: linear-gradient(135deg, var(--warning-color) 0%, #fd7e14 100%);
        }

        .bg-danger-gradient {
            background: linear-gradient(135deg, var(--danger-color) 0%, #e83e8c 100%);
        }

        .bg-info-gradient {
            background: linear-gradient(135deg, var(--info-color) 0%, #6f42c1 100%);
        }

        /* Buttons */
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gold-color) 0%, #c73650 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(233, 69, 96, 0.4);
        }

        /* Tables */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 20px 15px;
            }

            .mobile-menu-btn {
                display: block !important;
            }
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        /* Logout Button */
        .logout-btn {
            background: rgba(233, 69, 96, 0.2);
            color: var(--gold-color);
            border: 1px solid rgba(233, 69, 96, 0.3);
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            background: var(--gold-color);
            color: white;
        }

        .logout-btn i {
            margin-right: 5px;
        }

        /* Notification Bell Styles */
        .notification-bell-container {
            position: relative;
        }

        .notification-btn {
            background: rgba(108, 117, 125, 0.1);
            border: 1px solid rgba(108, 117, 125, 0.2);
            transition: all 0.3s ease;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .notification-btn:hover i {
            color: white !important;
        }

        .notification-badge {
            font-size: 0.6rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.1);
            }
            100% {
                transform: translate(-50%, -50%) scale(1);
            }
        }

        .notification-dropdown {
            min-width: 320px;
            max-height: 400px;
            overflow-y: auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            margin-top: 8px;
        }

        .notification-dropdown .dropdown-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
            padding: 12px 16px;
        }

        .notification-dropdown .dropdown-item {
            padding: 12px 16px;
            border-radius: 8px;
            margin: 2px 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .notification-dropdown .dropdown-item:hover {
            background: rgba(26, 26, 46, 0.1);
            transform: translateX(4px);
        }

        .notification-dropdown .dropdown-item-text {
            border-radius: 8px;
            margin: 2px 8px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            @php
                $hospitalName = getAppName();
                $siteLogo = \App\Models\SiteSetting::get('site_logo_dark') ?: \App\Models\SiteSetting::get('site_logo');
            @endphp
            
            @if($siteLogo)
                <img src="{{ asset($siteLogo) }}" alt="{{ $hospitalName }}" class="sidebar-logo">
            @else
                <div class="sidebar-title">{{ $hospitalName }}</div>
            @endif
        </div>

        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="{{ route('patient.dashboard') }}" class="nav-link {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patient.profile') }}" class="nav-link {{ request()->routeIs('patient.profile*') ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    My Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patient.appointments.index') }}" class="nav-link {{ request()->routeIs('patient.appointments*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patient.medical-records.index') }}" class="nav-link {{ request()->routeIs('patient.medical-records*') ? 'active' : '' }}">
                    <i class="fas fa-file-medical"></i>
                    Medical Records
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patient.lab-reports.index') }}" class="nav-link {{ request()->routeIs('patient.lab-reports*') ? 'active' : '' }}">
                    <i class="fas fa-flask"></i>
                    Lab Reports
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patient.prescriptions.index') }}" class="nav-link {{ request()->routeIs('patient.prescriptions*') ? 'active' : '' }}">
                    <i class="fas fa-prescription-bottle"></i>
                    Prescriptions
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('patient.billing.index') }}" class="nav-link {{ request()->routeIs('patient.billing*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Billing & Payments
                </a>
            </li>
        </ul>

        <div class="user-profile">
            <div class="user-info">
                <img src="{{ Auth::guard('patient')->user()->photo_url }}" alt="Profile" class="user-avatar">
                <div>
                    <div class="user-name">{{ Auth::guard('patient')->user()->full_name }}</div>
                    <div class="user-id">ID: {{ Auth::guard('patient')->user()->patient_id }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('patient.logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-btn w-100">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="top-navbar">
            <div class="page-header">
                <div>
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                    @if(isset($breadcrumbs))
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                @foreach($breadcrumbs as $breadcrumb)
                                    @if($loop->last)
                                        <li class="breadcrumb-item active">{{ $breadcrumb['name'] }}</li>
                                    @else
                                        <li class="breadcrumb-item">
                                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                </div>
                <div class="header-right-section d-flex align-items-center">
                    <div class="header-info d-flex align-items-center me-3">
                        <span class="text-muted me-3">Welcome back, {{ Auth::guard('patient')->user()->first_name }}!</span>
                        <span class="badge bg-primary">{{ now()->format('M d, Y') }}</span>
                    </div>
                    {{-- Patient Notification Bell - Positioned at far right --}}
                    <div class="notification-bell-container position-relative">
                        <button class="patient-notification-btn notification-btn btn p-2 rounded-circle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fs-5 text-secondary"></i>
                            <span id="patientNotificationCount" class="notification-badge position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;">
                                0
                                <span class="visually-hidden">unread notifications</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow" style="width: 380px; max-height: 450px; overflow-y: auto;">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <div id="patientNotificationList">
                                <li>
                                    <div class="dropdown-item-text text-center py-3 text-muted">
                                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                                        Loading notifications...
                                    </div>
                                </li>
                            </div>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="{{ route('patient.notifications.index') }}">View All Notifications</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Content -->
            @yield('content')
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile menu toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('show');
        }

        // Auto-hide alerts after 30 seconds (except for payment-related alerts)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.payment-alert)');
                alerts.forEach(alert => {
                    // Only auto-hide success and info alerts, not errors or warnings
                    if (alert.classList.contains('alert-success') || alert.classList.contains('alert-info')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        if (bsAlert) {
                            bsAlert.close();
                        }
                    }
                });
            }, 30000); // Increased to 30 seconds
        });

        // CSRF Token setup
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.csrfToken = token.content;
        }
        
        // Setup CSRF token for AJAX requests
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
        
        // Patient notification functions
        function loadPatientNotifications() {
            if (typeof $ === 'undefined') {
                console.error('jQuery not loaded');
                return;
            }
            
            $.get('{{ route("patient.api.notifications") }}')
                .done(function(response) {
                    updatePatientNotificationBadge(response.total_count || 0);
                    updatePatientNotificationList(response.notifications || []);
                })
                .fail(function() {
                    console.error('Failed to load patient notifications');
                    // Set empty state on failure
                    updatePatientNotificationBadge(0);
                    updatePatientNotificationList([]);
                });
        }
        
        function updatePatientNotificationBadge(count) {
            const badge = $('#patientNotificationCount');
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).show();
            } else {
                badge.hide();
            }
        }
        
        function updatePatientNotificationList(notifications) {
            const container = $('#patientNotificationList');
            
            if (notifications.length === 0) {
                container.html(`
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>
                        <p class="mb-0">No new notifications</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            notifications.forEach(function(notification) {
                const iconClass = notification.icon || 'fas fa-bell';
                const colorClass = notification.type === 'warning' ? 'text-warning' : 
                                 notification.type === 'info' ? 'text-info' : 
                                 notification.type === 'success' ? 'text-success' : 'text-primary';
                
                html += `
                    <li>
                        <a class="dropdown-item notification-item p-3" 
                           href="${notification.url || '#'}" 
                           style="white-space: normal; text-decoration: none; border-bottom: 1px solid #f0f0f0;">
                            <div class="d-flex align-items-start">
                                <div class="notification-icon me-3 mt-1" style="min-width: 24px;">
                                    <i class="${iconClass} ${colorClass} fs-5"></i>
                                </div>
                                <div class="notification-content flex-grow-1" style="min-width: 0;">
                                    <div class="notification-title fw-semibold text-dark mb-1" style="font-size: 14px; line-height: 1.3;">
                                        ${notification.title || 'Notification'}
                                    </div>
                                    <div class="notification-message text-muted mb-2" style="font-size: 13px; line-height: 1.4; word-wrap: break-word;">
                                        ${notification.message || ''}
                                    </div>
                                    <div class="notification-time text-muted" style="font-size: 11px;">
                                        <i class="fas fa-clock me-1"></i>${notification.created_at || ''}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                `;
            });
            
            container.html(html);
        }
        
        // Load notifications when page loads (after a short delay to ensure all scripts are loaded)
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                loadPatientNotifications();
                
                // Refresh notifications every 30 seconds
                setInterval(loadPatientNotifications, 30000);
            }, 1000);
        });
    </script>
    
    <!-- jQuery CDN (if not already loaded) -->
    <script>
        if (typeof $ === 'undefined') {
            const jqueryScript = document.createElement('script');
            jqueryScript.src = 'https://code.jquery.com/jquery-3.7.0.min.js';
            jqueryScript.onload = function() {
                // Setup AJAX after jQuery loads
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                // Load notifications
                loadPatientNotifications();
                setInterval(loadPatientNotifications, 30000);
            };
            document.head.appendChild(jqueryScript);
        }
    </script>

    @stack('scripts')
</body>
</html>
