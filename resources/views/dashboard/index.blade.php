<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Dashboard - {{ $primaryRole->display_name }} | Hospital Management System</title>
    
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom styles -->
    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --success: #10b981;
            --info: #06b6d4;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }
        
        * {
            transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            background-attachment: fixed;
        }
        
        body[data-theme="dark"] {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
        }
        
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            min-height: calc(100vh - 76px);
            border-radius: 20px 20px 0 0;
            margin-top: 20px;
            box-shadow: var(--shadow-xl);
            position: relative;
            z-index: 1;
        }
        
        body[data-theme="dark"] .main-content {
            background: rgba(30, 41, 59, 0.95);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: none;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-card.primary::before { background: linear-gradient(90deg, var(--primary), var(--primary-dark)); }
        .stat-card.success::before { background: linear-gradient(90deg, var(--success), #059669); }
        .stat-card.info::before { background: linear-gradient(90deg, var(--info), #0891b2); }
        .stat-card.warning::before { background: linear-gradient(90deg, var(--warning), #d97706); }
        .stat-card.danger::before { background: linear-gradient(90deg, var(--danger), #dc2626); }
        
        body[data-theme="dark"] .stat-card {
            background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
            color: #f8fafc;
        }
        
        .stat-icon {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 1.5rem;
        }
        
        .stat-number {
            font-size: 2.25rem;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .stat-change {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .stat-change.positive {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }
        
        .stat-change.negative {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
        }
        
        .text-primary { color: var(--primary) !important; }
        .text-success { color: var(--success) !important; }
        .text-info { color: var(--info) !important; }
        .text-warning { color: var(--warning) !important; }
        .text-danger { color: var(--danger) !important; }
        
        .font-weight-bold { font-weight: 700 !important; }
        .text-xs { font-size: 0.75rem; }
        
        .shadow {
            box-shadow: var(--shadow-lg) !important;
        }
        
        .enhanced-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .enhanced-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }
        
        body[data-theme="dark"] .enhanced-card {
            background: var(--gray-800);
            color: #f8fafc;
        }
        
        .icon-circle {
            height: 3rem;
            width: 3rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }
        
        .activity-timeline {
            position: relative;
            padding: 1rem 0;
        }
        
        .timeline-item {
            position: relative;
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-left: 3rem;
        }
        
        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0.5rem;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: white;
            z-index: 2;
        }
        
        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 0.875rem;
            top: 2rem;
            bottom: -2rem;
            width: 2px;
            background: linear-gradient(to bottom, var(--primary), transparent);
            z-index: 1;
        }
        
        .timeline-content {
            flex: 1;
            background: var(--gray-50);
            padding: 1rem;
            border-radius: 12px;
            margin-left: 1rem;
        }
        
        body[data-theme="dark"] .timeline-content {
            background: var(--gray-700);
        }
        
        /* Ensure navbar and dropdowns are above everything */
        body {
            position: relative;
        }
        
        .navbar-fixed-top {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1050;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: white !important;
        }
        
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            z-index: 1055 !important;
            position: absolute !important;
        }
        
        .navbar .dropdown {
            position: relative;
        }
        
        /* Right-side profile dropdown positioning */
        .navbar-nav .nav-item.dropdown:last-child .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            left: auto;
            z-index: 1055;
        }
        
        /* Left-side navigation dropdowns positioning */
        .navbar-nav .nav-item.dropdown:not(:last-child) .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: auto;
            z-index: 1055;
            min-width: 200px;
        }
        
        .quick-action-btn {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
            color: white;
        }
        
        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .weather-widget {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
            border-radius: 16px;
            padding: 1.5rem;
        }
        
        .theme-toggle {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 1000;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-top: 10px;
                border-radius: 16px 16px 0 0;
            }
            
            .stat-card {
                margin-bottom: 1rem;
            }
            
            .theme-toggle {
                right: 15px;
                top: 80px;
            }
        }
        
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <button class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon" id="themeIcon"></i>
    </button>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-hospital-symbol me-2"></i>
                Hospital Management
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    @if(in_array($primaryRole->name, ['admin', 'super_admin']))
                        <!-- Admin Quick Links -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-users me-1"></i>
                                Management
                            </a>
                            <ul class="dropdown-menu">
                                @if($user->hasPermission('patients.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.patients.index') }}"><i class="fas fa-user-injured me-2"></i>Patients</a></li>
                                @endif
                                @if($user->hasPermission('appointments.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.appointments.index') }}"><i class="fas fa-calendar-alt me-2"></i>Appointments</a></li>
                                @endif
                                @if($user->hasPermission('users.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.doctors.index') }}"><i class="fas fa-user-md me-2"></i>Doctors</a></li>
                                @endif
                                @if($user->hasPermission('users.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.users.index') }}"><i class="fas fa-users me-2"></i>Users</a></li>
                                @endif
                            </ul>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-file-medical me-1"></i>
                                Medical
                            </a>
                            <ul class="dropdown-menu">
                                @if($user->hasPermission('medical_records.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.medical-records.index') }}"><i class="fas fa-file-medical me-2"></i>Medical Records</a></li>
                                @endif
                                @if($user->hasPermission('prescriptions.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.prescriptions.index') }}"><i class="fas fa-prescription-bottle me-2"></i>Prescriptions</a></li>
                                @endif
                                @if($user->hasPermission('lab_reports.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.lab-reports.index') }}"><i class="fas fa-flask me-2"></i>Lab Reports</a></li>
                                @endif
                            </ul>
                        </li>
                        
                        @if($user->hasPermission('settings.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.index') }}">
                                <i class="fas fa-cog me-1"></i>
                                Settings
                            </a>
                        </li>
                        @endif
                        
                    @elseif($primaryRole->name === 'doctor')
                        <!-- Doctor Quick Links -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.appointments.index') }}">
                                <i class="fas fa-calendar-check me-1"></i>
                                My Appointments
                            </a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-file-medical me-1"></i>
                                Patient Care
                            </a>
                            <ul class="dropdown-menu">
                                @if($user->hasPermission('medical_records.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.medical-records.index') }}"><i class="fas fa-file-medical me-2"></i>Medical Records</a></li>
                                @endif
                                @if($user->hasPermission('prescriptions.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.prescriptions.index') }}"><i class="fas fa-prescription-bottle me-2"></i>Prescriptions</a></li>
                                @endif
                                @if($user->hasPermission('lab_reports.view'))
                                    <li><a class="dropdown-item" href="{{ route('staff.lab-reports.index') }}"><i class="fas fa-flask me-2"></i>Lab Reports</a></li>
                                @endif
                            </ul>
                        </li>
                        
                    @elseif($primaryRole->name === 'nurse')
                        <!-- Nurse Quick Links -->
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.patients.index') }}">
                                <i class="fas fa-user-injured me-1"></i>
                                Patient Care
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.prescriptions.index') }}">
                                <i class="fas fa-pills me-1"></i>
                                Medications
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.medical-records.index') }}">
                                <i class="fas fa-heartbeat me-1"></i>
                                Vital Signs
                            </a>
                        </li>
                        
                    @elseif($primaryRole->name === 'receptionist')
                        <!-- Receptionist Quick Links -->
                        @if($user->hasPermission('appointments.create'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.appointments.create') }}">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Book Appointment
                            </a>
                        </li>
                        @endif
                        
                        @if($user->hasPermission('patients.create'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.patients.create') }}">
                                <i class="fas fa-user-plus me-1"></i>
                                Register Patient
                            </a>
                        </li>
                        @endif
                        
                        @if($user->hasPermission('patients.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.patients.index') }}">
                                <i class="fas fa-search me-1"></i>
                                Patient Search
                            </a>
                        </li>
                        @endif
                        
                    @elseif($primaryRole->name === 'pharmacist')
                        <!-- Pharmacist Quick Links -->
                        @if($user->hasPermission('prescriptions.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.prescriptions.index') }}">
                                <i class="fas fa-prescription me-1"></i>
                                Prescriptions
                            </a>
                        </li>
                        @endif
                        
                        @if($user->hasPermission('prescriptions.update'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.prescriptions.index') }}">
                                <i class="fas fa-boxes me-1"></i>
                                Inventory
                            </a>
                        </li>
                        @endif
                        
                        @if($user->hasPermission('reports.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.prescriptions.index') }}">
                                <i class="fas fa-chart-bar me-1"></i>
                                Reports
                            </a>
                        </li>
                        @endif
                        
                    @elseif($primaryRole->name === 'technician')
                        <!-- Technician Quick Links -->
                        @if($user->hasPermission('lab_reports.view'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.lab-reports.index') }}">
                                <i class="fas fa-list-ul me-1"></i>
                                Lab Queue
                            </a>
                        </li>
                        @endif
                        
                        @if($user->hasPermission('lab_reports.create'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.lab-reports.create') }}">
                                <i class="fas fa-file-medical-alt me-1"></i>
                                Test Results
                            </a>
                        </li>
                        @endif
                        
                        @if($user->hasPermission('lab_reports.update'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('staff.lab-reports.index') }}">
                                <i class="fas fa-tools me-1"></i>
                                Equipment
                            </a>
                        </li>
                        @endif
                    @endif
                    
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="#">
                            <i class="fas fa-bell me-1"></i>
                            Notifications
                            <span class="notification-badge">{{ $dashboardData['notifications_count'] ?? 0 }}</span>
                        </a>
                    </li>
                </ul>
                
                <!-- Real-time Clock -->
                <div class="navbar-text me-3 d-none d-md-block">
                    <i class="fas fa-clock me-1"></i>
                    <span id="realTimeClock"></span>
                </div>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ $user->name }}
                            <span class="badge bg-light text-dark ms-1">{{ $primaryRole->display_name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('change-password') }}"><i class="fas fa-key me-2"></i>Change Password</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
                        <div class="animate__animated animate__fadeInLeft">
                            <h1 class="display-6 fw-bold mb-2" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                Welcome back, {{ $user->name }}!
                            </h1>
                            <p class="lead mb-0 text-muted">
                                <i class="fas fa-calendar-day me-2"></i>
                                {{ now()->format('l, F j, Y') }}
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-3 mt-3 mt-md-0">
                            <div class="weather-widget animate__animated animate__fadeInRight">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-sun fa-2x me-3"></i>
                                    <div>
                                        <div class="fw-bold">24°C</div>
                                        <small>Sunny</small>
                                    </div>
                                </div>
                            </div>
                            <span class="badge bg-gradient fs-6 px-3 py-2" style="background: linear-gradient(135deg, var(--primary), var(--secondary)) !important;">
                                {{ $primaryRole->display_name }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert" style="border-radius: 12px; border: none; box-shadow: var(--shadow-md);">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInDown" role="alert" style="border-radius: 12px; border: none; box-shadow: var(--shadow-md);">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

    <!-- Role-specific Dashboard Content -->
    
    @if(in_array($primaryRole->name, ['admin', 'super_admin']))
        @include('dashboard.partials.admin-dashboard', ['data' => $dashboardData])
    @elseif($primaryRole->name === 'doctor')
        @include('dashboard.partials.doctor-dashboard', ['data' => $dashboardData])
    @elseif($primaryRole->name === 'nurse')
        @include('dashboard.partials.nurse-dashboard', ['data' => $dashboardData])
    @elseif($primaryRole->name === 'receptionist')
        @include('dashboard.partials.receptionist-dashboard', ['data' => $dashboardData])
    @elseif($primaryRole->name === 'pharmacist')
        @include('dashboard.partials.pharmacist-dashboard', ['data' => $dashboardData])
    @elseif($primaryRole->name === 'technician')
        @include('dashboard.partials.technician-dashboard', ['data' => $dashboardData])
    @else
        @include('dashboard.partials.default-dashboard', ['data' => $dashboardData])
    @endif

    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom scripts -->
    <script>
        // Theme Management
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('themeIcon');
            const currentTheme = body.getAttribute('data-theme');
            
            if (currentTheme === 'dark') {
                body.removeAttribute('data-theme');
                themeIcon.className = 'fas fa-moon';
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                themeIcon.className = 'fas fa-sun';
                localStorage.setItem('theme', 'dark');
            }
        }
        
        // Load saved theme
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const themeIcon = document.getElementById('themeIcon');
            
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                themeIcon.className = 'fas fa-sun';
            }
        });
        
        // Real-time clock
        function updateClock() {
            const now = new Date();
            const clock = document.getElementById('realTimeClock');
            if (clock) {
                clock.textContent = now.toLocaleTimeString();
            }
        }
        
        setInterval(updateClock, 1000);
        updateClock();
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Smooth scroll to sections
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
        
        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Loading states
        function showLoading(element) {
            element.innerHTML = '<div class="loading-skeleton" style="height: 20px; border-radius: 4px;"></div>';
        }
        
        // Dashboard initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Enhanced Dashboard loaded successfully');
            
            // Add entrance animations to cards
            const cards = document.querySelectorAll('.stat-card, .enhanced-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animate__animated', 'animate__fadeInUp');
                }, index * 100);
            });
            
            // Simulate real-time data updates
            setInterval(() => {
                // Update notification count randomly
                const notificationBadge = document.querySelector('.notification-badge');
                if (notificationBadge) {
                    const currentCount = parseInt(notificationBadge.textContent) || 0;
                    if (Math.random() > 0.7) { // 30% chance
                        notificationBadge.textContent = currentCount + 1;
                        notificationBadge.classList.add('animate__animated', 'animate__pulse');
                        setTimeout(() => {
                            notificationBadge.classList.remove('animate__animated', 'animate__pulse');
                        }, 1000);
                    }
                }
            }, 30000); // Every 30 seconds
        });
    </script>
</body>
</html>
