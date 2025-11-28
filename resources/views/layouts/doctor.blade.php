<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor Portal') - {{ getAppName() }}</title>
    
    <!-- Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom Doctor Styles -->
    <style>
        :root {
            /* Medical Professional Color Scheme */
            --doctor-primary: #0d6efd;
            --doctor-primary-dark: #0a58ca;
            --doctor-secondary: #6c757d;
            --doctor-success: #198754;
            --doctor-warning: #ffc107;
            --doctor-danger: #dc3545;
            --doctor-info: #0dcaf0;
            --doctor-light: #f8f9fa;
            --doctor-dark: #212529;
            
            /* Medical Theme Colors */
            --medical-blue: #1e88e5;
            --medical-green: #43a047;
            --medical-teal: #00acc1;
            --medical-red: #e53935;
            --medical-orange: #fb8c00;
            
            /* UI Colors */
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-sidebar: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #e2e8f0;
            
            /* Layout */
            --sidebar-width: 280px;
            --header-height: 75px;
            --sidebar-collapsed-width: 80px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Modern Sidebar */
        .doctor-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.04);
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .doctor-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .doctor-sidebar::-webkit-scrollbar-track {
            background: #f8f9fc;
        }

        .doctor-sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .doctor-sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Sidebar Header */
        .doctor-sidebar-header {
            padding: 1.5rem;
            border-bottom: 2px solid var(--border-color);
            background: #f8f9fc;
        }

        .doctor-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-primary);
            text-decoration: none;
        }

        .doctor-logo-icon {
            width: 45px;
            height: 45px;
            background: #1a202c;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .doctor-logo-text {
            flex: 1;
        }

        .doctor-logo-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
            line-height: 1.2;
        }

        .doctor-logo-subtitle {
            font-size: 0.75rem;
            color: #4a5568;
            margin: 0;
        }

        /* Navigation */
        .doctor-nav-section {
            padding: 1.5rem 0;
        }

        .doctor-nav-title {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            padding: 0 1.5rem;
            margin-bottom: 0.75rem;
        }

        .doctor-nav-item {
            margin: 0.25rem 0;
            padding: 0 1rem;
        }

        .doctor-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #2d3748;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }

        .doctor-nav-link:hover {
            background: #f8f9fc;
            color: #1a202c;
            transform: translateX(4px);
        }

        .doctor-nav-link.active {
            background: #f8f9fc;
            color: #1a202c;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .doctor-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: #1a202c;
            border-radius: 0 4px 4px 0;
        }
        
        .doctor-nav-link i {
            color: #1a202c !important;
            display: inline-block !important;
            font-style: normal !important;
            font-variant: normal !important;
            text-rendering: auto !important;
            line-height: 1 !important;
        }

        .doctor-nav-icon {
            width: 24px !important;
            min-width: 24px !important;
            text-align: center !important;
            font-size: 1.1rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        /* Ensure Font Awesome icons are visible and properly styled */
        .doctor-nav-link i.fas,
        .doctor-nav-link i.far,
        .doctor-nav-link i.fab,
        .doctor-nav-link i.fal,
        .doctor-nav-link i.fad,
        .doctor-nav-link i[class*="fa-"] {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important;
            font-weight: 900 !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }

        .doctor-nav-text {
            flex: 1;
            font-size: 0.9rem;
        }

        .doctor-nav-badge {
            background: #e2e8f0;
            color: #1a202c;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Main Content Area */
        .doctor-main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: #f5f7fa;
            transition: margin-left 0.3s ease;
        }

        /* Modern Header */
        .doctor-header {
            height: var(--header-height);
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .doctor-header-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
        }

        .doctor-header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .doctor-header-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .doctor-header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Header Actions */
        .doctor-header-action {
            position: relative;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .doctor-header-action:hover {
            background: var(--doctor-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .doctor-header-action .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--doctor-danger);
            color: white;
            font-size: 0.65rem;
            padding: 0.2rem 0.4rem;
            border-radius: 10px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* User Menu */
        .doctor-user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--bg-secondary);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .doctor-user-menu:hover {
            background: white;
            border-color: var(--border-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .doctor-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--doctor-primary), var(--medical-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .doctor-user-info {
            flex: 1;
            min-width: 0;
        }

        .doctor-user-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .doctor-user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Content Wrapper */
        .doctor-content-wrapper {
            padding: 2rem;
        }

        /* Modern Cards */
        .doctor-card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .doctor-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .doctor-card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }

        .doctor-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .doctor-card-body {
            padding: 1.5rem;
        }

        /* Modern Checkboxes - High Specificity for Production */
        .form-check {
            display: flex !important;
            align-items: flex-start !important;
            gap: 0.75rem !important;
            margin-bottom: 1rem !important;
        }

        input[type="checkbox"].form-check-input,
        .form-check-input[type="checkbox"] {
            width: 20px !important;
            height: 20px !important;
            margin-top: 0.25rem !important;
            margin-left: 0 !important;
            border: 2px solid #cbd5e1 !important;
            border-radius: 6px !important;
            background-color: white !important;
            background-image: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            flex-shrink: 0 !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            position: relative !important;
            float: none !important;
            pointer-events: auto !important;
            z-index: 10 !important;
            opacity: 1 !important;
        }

        input[type="checkbox"].form-check-input:hover,
        .form-check-input[type="checkbox"]:hover {
            border-color: #94a3b8 !important;
            background-color: #f8f9fc !important;
            background-image: none !important;
        }

        input[type="checkbox"].form-check-input:focus,
        .form-check-input[type="checkbox"]:focus {
            outline: none !important;
            border-color: #1a202c !important;
            box-shadow: 0 0 0 4px rgba(26, 32, 44, 0.1) !important;
            background-image: none !important;
        }

        input[type="checkbox"].form-check-input:checked,
        .form-check-input[type="checkbox"]:checked {
            background-color: #1a202c !important;
            border-color: #1a202c !important;
            background-image: none !important;
        }

        input[type="checkbox"].form-check-input:checked::after,
        .form-check-input[type="checkbox"]:checked::after {
            content: '' !important;
            position: absolute !important;
            left: 50% !important;
            top: 50% !important;
            transform: translate(-50%, -50%) rotate(45deg) !important;
            width: 5px !important;
            height: 10px !important;
            border: solid white !important;
            border-width: 0 2px 2px 0 !important;
            display: block !important;
            pointer-events: none !important;
            z-index: 1 !important;
        }

        .form-check-label {
            color: #2d3748 !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            line-height: 1.5 !important;
            flex: 1 !important;
            margin: 0 !important;
            user-select: none !important;
            pointer-events: auto !important;
        }

        .form-check-label i {
            color: #1a202c !important;
        }
        
        /* Ensure form-check container doesn't block clicks */
        .form-check {
            pointer-events: auto !important;
        }

        /* Stats Cards */
        .doctor-stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            height: 100%;
        }

        .doctor-stat-card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transform: translateY(-4px);
        }

        .doctor-stat-card.primary {
            border-left: 4px solid var(--doctor-primary);
        }

        .doctor-stat-card.success {
            border-left: 4px solid var(--doctor-success);
        }

        .doctor-stat-card.warning {
            border-left: 4px solid var(--doctor-warning);
        }

        .doctor-stat-card.info {
            border-left: 4px solid var(--doctor-info);
        }

        .doctor-stat-card.danger {
            border-left: 4px solid var(--doctor-danger);
        }

        .doctor-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0.5rem 0;
        }

        .doctor-stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Enhanced Stat Cards - Same as Admin */
        .stat-card-enhanced {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .stat-card-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.3), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card-enhanced:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: rgba(102, 126, 234, 0.2);
        }

        .stat-card-enhanced:hover::before {
            opacity: 1;
        }

        .stat-card-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            background: #000000 !important;
        }

        .stat-icon-wrapper i {
            color: #ffffff;
            font-size: 1.5rem;
        }

        .stat-card-enhanced:hover .stat-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.4;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .stat-card-enhanced {
                padding: 1rem;
            }

            .stat-icon-wrapper {
                width: 48px;
                height: 48px;
            }

            .stat-icon-wrapper i {
                font-size: 1.25rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.8rem;
            }
        }

        .doctor-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Quick Action Buttons */
        .doctor-quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            height: 100%;
        }

        .doctor-quick-action:hover {
            background: var(--doctor-primary);
            color: white;
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.3);
        }

        .doctor-quick-action-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .doctor-quick-action-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .doctor-quick-action-subtitle {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Modern Buttons */
        .btn-doctor-primary {
            background: var(--doctor-primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-doctor-primary:hover {
            background: var(--doctor-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            color: white;
        }

        /* Sidebar Toggle for Mobile */
        .doctor-sidebar-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--bg-secondary);
            border: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .doctor-sidebar-toggle:hover {
            background: var(--doctor-primary);
            color: white;
        }

        /* Mobile Overlay */
        .doctor-mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .doctor-mobile-overlay.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .doctor-sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .doctor-sidebar.show {
                transform: translateX(0);
            }

            .doctor-main-content {
                margin-left: 0;
            }

            .doctor-sidebar-toggle {
                display: flex;
            }

            .doctor-header {
                padding: 0 1rem;
            }

            .doctor-header-title {
                font-size: 1.25rem;
            }

            .doctor-header-subtitle {
                display: none;
            }

            .doctor-content-wrapper {
                padding: 1rem;
            }

            .doctor-user-info {
                display: none !important;
            }

            .doctor-header-search {
                display: none;
            }

            .doctor-header-right {
                gap: 0.5rem;
            }

            .doctor-header-action {
                width: 38px;
                height: 38px;
            }
        }

        @media (max-width: 768px) {
            .doctor-header {
                height: 60px;
                padding: 0 0.75rem;
            }

            .doctor-header-title {
                font-size: 1.1rem;
            }

            .doctor-content-wrapper {
                padding: 0.75rem;
            }

            .doctor-stat-card {
                padding: 1rem;
            }

            .doctor-stat-number {
                font-size: 1.5rem;
            }

            .doctor-card-header {
                padding: 1rem;
            }

            .doctor-card-body {
                padding: 1rem;
            }

            .doctor-quick-action {
                padding: 1rem;
            }

            .doctor-quick-action-icon {
                font-size: 1.5rem;
            }

            .doctor-quick-action-title {
                font-size: 0.8rem;
            }

            .doctor-quick-action-subtitle {
                font-size: 0.7rem;
            }

            /* Stack stats in single column on mobile */
            .row.g-4 > [class*="col-"] {
                margin-bottom: 1rem;
            }

            /* Make tables scrollable on mobile */
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }

            /* Adjust dropdown menus for mobile */
            .dropdown-menu {
                max-width: calc(100vw - 2rem);
                left: auto !important;
                right: 0 !important;
            }
        }

        @media (max-width: 576px) {
            .doctor-header-title {
                font-size: 1rem;
            }

            .doctor-content-wrapper {
                padding: 0.5rem;
            }

            .doctor-stat-card {
                padding: 0.75rem;
            }

            .doctor-stat-number {
                font-size: 1.25rem;
            }

            .doctor-stat-label {
                font-size: 0.75rem;
            }

            .doctor-card-header {
                padding: 0.75rem;
            }

            .doctor-card-body {
                padding: 0.75rem;
            }

            .doctor-card-title {
                font-size: 0.95rem;
            }

            .doctor-quick-action {
                padding: 0.75rem;
            }

            .doctor-quick-action-icon {
                font-size: 1.25rem;
                margin-bottom: 0.5rem;
            }

            .doctor-quick-action-title {
                font-size: 0.75rem;
            }

            .doctor-quick-action-subtitle {
                font-size: 0.65rem;
            }

            /* Hide less important columns in tables on mobile */
            .table th:nth-child(n+4),
            .table td:nth-child(n+4) {
                display: none;
            }

            /* Make buttons smaller on mobile */
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: #0f172a;
            color: #e2e8f0;
        }

        body.dark-mode .doctor-main-content {
            background: #0f172a;
        }

        body.dark-mode .doctor-header {
            background: #1e293b;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .doctor-header-title {
            color: #f1f5f9;
        }

        body.dark-mode .doctor-header-subtitle {
            color: #94a3b8;
        }

        body.dark-mode .doctor-content-wrapper {
            background: #0f172a;
        }

        body.dark-mode .doctor-card,
        body.dark-mode .doctor-stat-card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .doctor-card-header {
            background: #334155;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .doctor-card-title {
            color: #f1f5f9;
        }

        body.dark-mode .doctor-stat-card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .doctor-stat-number {
            color: #f1f5f9;
        }

        body.dark-mode .doctor-stat-label {
            color: #94a3b8;
        }

        body.dark-mode .doctor-quick-action {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        body.dark-mode .doctor-header-action {
            background: #334155;
            color: #e2e8f0;
        }

        body.dark-mode .doctor-user-menu {
            background: #334155;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .doctor-user-name {
            color: #f1f5f9;
        }

        body.dark-mode .doctor-user-role {
            color: #94a3b8;
        }

        body.dark-mode .table {
            color: #e2e8f0;
        }

        body.dark-mode .table thead th {
            background: #334155;
            border-color: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
        }

        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus {
            background: #1e293b;
            border-color: var(--doctor-primary);
            color: #e2e8f0;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Smooth Transitions */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
    
    @stack('styles')
</head>
<body class="{{ auth()->check() && auth()->user()->role === 'doctor' && auth()->user()->dark_mode ? 'dark-mode' : '' }}">
    <!-- Doctor Sidebar -->
    <aside class="doctor-sidebar" id="doctorSidebar">
        <div class="doctor-sidebar-header">
            <a href="{{ route('staff.dashboard') }}" class="doctor-logo">
                <div class="doctor-logo-icon">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="doctor-logo-text">
                    <div class="doctor-logo-title">{{ getAppName() }}</div>
                    <div class="doctor-logo-subtitle">Doctor Portal</div>
                </div>
            </a>
        </div>

        @php
            // Get ordered and visible menu items for doctor
            $menuItems = getSidebarMenuItems('staff');
            $mainMenuItems = array_filter($menuItems, function($item) {
                if (isset($item['is_custom']) && $item['is_custom']) {
                    return false;
                }
                if (isset($item['menu_key']) && str_starts_with($item['menu_key'], 'custom-')) {
                    return false;
                }
                return true;
            });
            $mainMenuItems = array_values($mainMenuItems);
            
            // Get current route for active state
            $currentRoute = request()->route()->getName();
        @endphp

        <nav class="doctor-nav-section">
            <div class="doctor-nav-title">Main Menu</div>
            @foreach($mainMenuItems as $item)
                @php
                    $menuKey = $item['menu_key'] ?? '';
                    $label = $item['label'] ?? '';
                    $icon = $item['icon'] ?? 'fa-circle';
                    
                    // Ensure icon has Font Awesome prefix (fas, far, fab, etc.)
                    if (!empty($icon) && !preg_match('/^(fas|far|fab|fal|fad|fa)\s/', $icon)) {
                        // If icon doesn't start with a prefix, add 'fas' as default
                        $icon = 'fas ' . $icon;
                    }
                    
                    // Map menu_key to route
                    $route = '#';
                    $isActive = false;
                    
                    switch($menuKey) {
                        case 'dashboard':
                            $route = route('staff.dashboard');
                            $isActive = request()->routeIs('staff.dashboard');
                            break;
                        case 'patients':
                            $route = route('staff.patients.index');
                            $isActive = request()->routeIs('staff.patients.*');
                            break;
                        case 'appointments':
                            $route = route('staff.appointments.index');
                            $isActive = request()->routeIs('staff.appointments.*');
                            break;
                        case 'medical-records':
                            $route = route('staff.medical-records.index');
                            $isActive = request()->routeIs('staff.medical-records.*');
                            break;
                        case 'prescriptions':
                            $route = route('staff.prescriptions.index');
                            $isActive = request()->routeIs('staff.prescriptions.*');
                            break;
                        case 'lab-reports':
                            $route = route('staff.lab-reports.index');
                            $isActive = request()->routeIs('staff.lab-reports.*');
                            break;
                        case 'document-templates':
                            $route = route('staff.document-templates.index');
                            $isActive = request()->routeIs('staff.document-templates.*') || request()->routeIs('staff.patients.documents.*');
                            break;
                        case 'alerts':
                            $route = route('staff.alerts.index');
                            $isActive = request()->routeIs('staff.alerts.*');
                            break;
                        case 'billing':
                            $route = route('staff.billing.index');
                            $isActive = request()->routeIs('staff.billing.*');
                            break;
                        default:
                            // Try to use route if provided, otherwise skip
                            if (isset($item['route'])) {
                                $route = $item['route'];
                            }
                    }
                @endphp
                @if($route !== '#')
                <div class="doctor-nav-item">
                    <a href="{{ $route }}" 
                       class="doctor-nav-link {{ $isActive ? 'active' : '' }}"
                       title="{{ $label }}">
                        <i class="{{ $icon }} doctor-nav-icon" aria-hidden="true"></i>
                        <span class="doctor-nav-text">{{ $label }}</span>
                        @if(isset($item['badge']) && $item['badge'])
                            <span class="doctor-nav-badge">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                </div>
                @endif
            @endforeach
        </nav>

        <!-- Custom Menu Items -->
        @php
            $userRole = auth()->user()->role ?? 'doctor';
            $customMenuItems = \App\Models\CustomMenuItem::getActiveForMenuTypeAndRole('staff', $userRole);
        @endphp
        @if($customMenuItems->count() > 0)
        <nav class="doctor-nav-section">
            <div class="doctor-nav-title">Quick Links</div>
            @foreach($customMenuItems as $customItem)
            <div class="doctor-nav-item">
                <a href="{{ $customItem->url }}" 
                   target="{{ $customItem->target }}"
                   class="doctor-nav-link"
                   rel="noopener noreferrer">
                    <i class="{{ $customItem->icon ?? 'fas fa-external-link-alt' }} doctor-nav-icon"></i>
                    <span class="doctor-nav-text">{{ $customItem->label }}</span>
                    @if($customItem->target === '_blank')
                        <i class="fas fa-external-link-alt" style="font-size: 0.7rem; opacity: 0.7;"></i>
                    @endif
                </a>
            </div>
            @endforeach
        </nav>
        @endif
    </aside>

    <!-- Mobile Overlay -->
    <div class="doctor-mobile-overlay" id="doctorMobileOverlay"></div>

    <!-- Main Content -->
    <div class="doctor-main-content">
        <!-- Header -->
        <header class="doctor-header">
            <div class="doctor-header-left">
                <button class="doctor-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div>
                    <h1 class="doctor-header-title">@yield('page-title', 'Dashboard')</h1>
                    <p class="doctor-header-subtitle">@yield('page-subtitle', 'Welcome back, Dr. ' . (auth()->user()->name ?? ''))</p>
                </div>
            </div>
            <div class="doctor-header-right">
                <!-- Quick Patient Search -->
                <div class="doctor-header-search position-relative me-3">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           id="quickPatientSearch" 
                           placeholder="Quick search patient..." 
                           style="width: 250px; border-radius: 20px; padding-left: 40px; border: 1px solid rgba(0,0,0,0.1);"
                           autocomplete="off">
                    <i class="fas fa-search position-absolute" 
                       style="left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; pointer-events: none;"></i>
                    <div id="quickPatientSearchResults" 
                         class="position-absolute bg-white shadow-lg border rounded" 
                         style="top: 100%; left: 0; right: 0; max-height: 400px; overflow-y: auto; display: none; z-index: 1000; margin-top: 5px; border-radius: 12px;">
                        <!-- Search results will appear here -->
                    </div>
                </div>
                
                {{-- Dark Mode Toggle - Disabled --}}

                <!-- Notifications -->
                <div class="position-relative">
                    <button class="doctor-header-action" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span id="doctorNotificationCount" class="badge d-none">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 380px; max-height: 450px; overflow-y: auto; border-radius: 16px; margin-top: 0.5rem;">
                        <div class="p-3 border-bottom">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-bell me-2"></i>Notifications</h6>
                        </div>
                        <div id="doctorNotificationList" class="p-2">
                            <div class="text-center py-4">
                                <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mb-0 small text-muted">No new notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <div class="doctor-user-menu" data-bs-toggle="dropdown">
                        <div class="doctor-user-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'D', 0, 1)) }}
                        </div>
                        <div class="doctor-user-info d-none d-md-block">
                            <div class="doctor-user-name">{{ auth()->user()->name ?? 'Doctor' }}</div>
                            <div class="doctor-user-role">Medical Doctor</div>
                        </div>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 16px; margin-top: 0.5rem;">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('change-password') }}"><i class="fas fa-key me-2"></i>Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="doctor-content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none;">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none;">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @stack('scripts')
    
    <script>
        // Quick Patient Search
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('quickPatientSearch');
            const searchResults = document.getElementById('quickPatientSearchResults');
            let searchTimeout;
            
            if (searchInput && searchResults) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length < 2) {
                        searchResults.style.display = 'none';
                        return;
                    }
                    
                    searchTimeout = setTimeout(function() {
                        fetch('{{ route("staff.api.patients.search") }}?q=' + encodeURIComponent(query), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // Handle both array format and object format
                            const patients = Array.isArray(data) ? data : (data.patients || []);
                            
                            if (patients.length > 0) {
                                let html = '<div class="p-2">';
                                patients.slice(0, 8).forEach(function(patient) {
                                    const initials = (patient.first_name ? patient.first_name.charAt(0) : '') + (patient.last_name ? patient.last_name.charAt(0) : '') || 'P';
                                    html += `
                                        <a href="{{ route('staff.patients.show', '') }}/${patient.id}" 
                                           class="d-flex align-items-center p-2 rounded text-decoration-none text-dark"
                                           style="transition: background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                                            <div class="doctor-user-avatar me-3" style="width: 36px; height: 36px; font-size: 0.875rem;">
                                                ${initials.toUpperCase()}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">${patient.first_name || ''} ${patient.last_name || ''}</div>
                                                <small class="text-muted">#${String(patient.id).padStart(4, '0')} • ${patient.phone || 'No phone'}</small>
                                            </div>
                                            <i class="fas fa-chevron-right text-muted ms-2"></i>
                                        </a>
                                    `;
                                });
                                html += '</div>';
                                searchResults.innerHTML = html;
                                searchResults.style.display = 'block';
                            } else {
                                searchResults.innerHTML = '<div class="p-3 text-center text-muted"><i class="fas fa-search me-2"></i>No patients found</div>';
                                searchResults.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Patient search error:', error);
                            searchResults.innerHTML = '<div class="p-3 text-center text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Search failed. Please try again.</div>';
                            searchResults.style.display = 'block';
                        });
                    }, 300);
                });
                
                // Hide results when clicking outside
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });
                
                // Keyboard shortcut: Ctrl/Cmd + K for patient search
                document.addEventListener('keydown', function(e) {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                        e.preventDefault();
                        searchInput.focus();
                    }
                });
            }
        });
        
        // Sidebar Toggle for Mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('doctorSidebar');
            const overlay = document.getElementById('doctorMobileOverlay');
            
            function toggleSidebar() {
                const isOpen = sidebar.classList.contains('show');
                sidebar.classList.toggle('show');
                if (overlay) {
                    overlay.classList.toggle('show', !isOpen);
                }
                // Prevent body scroll when sidebar is open
                document.body.style.overflow = !isOpen ? 'hidden' : '';
            }
            
            function closeSidebar() {
                sidebar.classList.remove('show');
                if (overlay) {
                    overlay.classList.remove('show');
                }
                document.body.style.overflow = '';
            }
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleSidebar();
                });
                
                if (overlay) {
                    overlay.addEventListener('click', closeSidebar);
                }
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(event) {
                    if (window.innerWidth <= 992) {
                        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                            closeSidebar();
                        }
                    }
                });

                // Close sidebar on window resize if it becomes desktop
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 992) {
                        closeSidebar();
                    }
                });
            }

            // Dark Mode Toggle - Disabled

            // Load notifications (if you have a notification system)
            // You can integrate your notification loading logic here
        });
    </script>
</body>
</html>

