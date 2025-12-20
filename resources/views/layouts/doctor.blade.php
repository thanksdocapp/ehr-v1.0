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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Dynamic Theme CSS - Uses admin appearance settings -->
    <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ time() }}">

    <!-- Custom Doctor Styles -->
    <style>
        :root {
            /* Medical Professional Color Scheme - Uses dynamic theme colors */
            --doctor-primary: var(--primary-color, #0d6efd);
            --doctor-primary-dark: var(--button-hover-primary, #0a58ca);
            --doctor-secondary: var(--secondary-color, #6c757d);
            --doctor-success: var(--success-color, #198754);
            --doctor-warning: var(--warning-color, #ffc107);
            --doctor-danger: var(--danger-color, #dc3545);
            --doctor-info: var(--info-color, #0dcaf0);
            --doctor-light: #f8f9fa;
            --doctor-dark: #212529;

            /* Medical Theme Colors - Uses dynamic colors */
            --medical-blue: var(--primary-color, #1e88e5);
            --medical-green: var(--success-color, #43a047);
            --medical-teal: var(--info-color, #00acc1);
            --medical-red: var(--danger-color, #e53935);
            --medical-orange: var(--warning-color, #fb8c00);

            /* UI Colors */
            --bg-primary: var(--background-color, #ffffff);
            --bg-secondary: #f8f9fa;
            --bg-sidebar: #ffffff;
            --text-primary: var(--text-color, #212529);
            --text-secondary: var(--secondary-color, #6c757d);
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

        /* ==================== MODERN BUTTON STYLES ==================== */

        /* Base Button Styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--doctor-primary), 0.25);
        }

        .btn:active {
            transform: scale(0.97);
        }

        .btn i, .btn .fa, .btn .fas, .btn .far, .btn .fab {
            font-size: 0.9em;
        }

        /* Primary Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--doctor-primary) 0%, var(--doctor-primary-dark) 100%);
            color: #ffffff !important;
            border-color: var(--doctor-primary);
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--doctor-primary-dark) 0%, var(--doctor-primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.35);
            color: #ffffff !important;
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25);
        }

        /* Success Button */
        .btn-success {
            background: linear-gradient(135deg, var(--doctor-success) 0%, #157347 100%);
            color: #ffffff !important;
            border-color: var(--doctor-success);
            box-shadow: 0 2px 8px rgba(25, 135, 84, 0.25);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #157347 0%, var(--doctor-success) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.35);
            color: #ffffff !important;
        }

        /* Danger Button */
        .btn-danger {
            background: linear-gradient(135deg, var(--doctor-danger) 0%, #b02a37 100%);
            color: #ffffff !important;
            border-color: var(--doctor-danger);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.25);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #b02a37 0%, var(--doctor-danger) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.35);
            color: #ffffff !important;
        }

        /* Warning Button */
        .btn-warning {
            background: linear-gradient(135deg, var(--doctor-warning) 0%, #e0a800 100%);
            color: #212529 !important;
            border-color: var(--doctor-warning);
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.25);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #e0a800 0%, var(--doctor-warning) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.35);
            color: #212529 !important;
        }

        /* Info Button */
        .btn-info {
            background: linear-gradient(135deg, var(--doctor-info) 0%, #0aa2c0 100%);
            color: #ffffff !important;
            border-color: var(--doctor-info);
            box-shadow: 0 2px 8px rgba(13, 202, 240, 0.25);
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0aa2c0 0%, var(--doctor-info) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 202, 240, 0.35);
            color: #ffffff !important;
        }

        /* Secondary Button */
        .btn-secondary {
            background: linear-gradient(135deg, var(--doctor-secondary) 0%, #5a6268 100%);
            color: #ffffff !important;
            border-color: var(--doctor-secondary);
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.25);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, var(--doctor-secondary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.35);
            color: #ffffff !important;
        }

        /* Light Button */
        .btn-light {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #212529 !important;
            border-color: #dee2e6;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .btn-light:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            color: #212529 !important;
        }

        /* Dark Button */
        .btn-dark {
            background: linear-gradient(135deg, #343a40 0%, #1a1e21 100%);
            color: #ffffff !important;
            border-color: #343a40;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .btn-dark:hover {
            background: linear-gradient(135deg, #1a1e21 0%, #343a40 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
            color: #ffffff !important;
        }

        /* Outline Buttons */
        .btn-outline-primary {
            background: transparent;
            color: var(--doctor-primary) !important;
            border: 2px solid var(--doctor-primary);
        }

        .btn-outline-primary:hover {
            background: var(--doctor-primary);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.35);
        }

        .btn-outline-success {
            background: transparent;
            color: var(--doctor-success) !important;
            border: 2px solid var(--doctor-success);
        }

        .btn-outline-success:hover {
            background: var(--doctor-success);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.35);
        }

        .btn-outline-danger {
            background: transparent;
            color: var(--doctor-danger) !important;
            border: 2px solid var(--doctor-danger);
        }

        .btn-outline-danger:hover {
            background: var(--doctor-danger);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.35);
        }

        .btn-outline-warning {
            background: transparent;
            color: #997404 !important;
            border: 2px solid var(--doctor-warning);
        }

        .btn-outline-warning:hover {
            background: var(--doctor-warning);
            color: #212529 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.35);
        }

        .btn-outline-info {
            background: transparent;
            color: var(--doctor-info) !important;
            border: 2px solid var(--doctor-info);
        }

        .btn-outline-info:hover {
            background: var(--doctor-info);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 202, 240, 0.35);
        }

        .btn-outline-secondary {
            background: transparent;
            color: var(--doctor-secondary) !important;
            border: 2px solid var(--doctor-secondary);
        }

        .btn-outline-secondary:hover {
            background: var(--doctor-secondary);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.35);
        }

        .btn-outline-light {
            background: transparent;
            color: #6c757d !important;
            border: 2px solid #dee2e6;
        }

        .btn-outline-light:hover {
            background: #f8f9fa;
            color: #212529 !important;
            transform: translateY(-2px);
        }

        .btn-outline-dark {
            background: transparent;
            color: #343a40 !important;
            border: 2px solid #343a40;
        }

        .btn-outline-dark:hover {
            background: #343a40;
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
        }

        /* Button Sizes */
        .btn-lg {
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
            border-radius: 12px;
        }

        .btn-sm {
            padding: 0.375rem 0.875rem;
            font-size: 0.8125rem;
            border-radius: 8px;
        }

        .btn-xs {
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            border-radius: 6px;
        }

        /* Icon Only Button */
        .btn-icon {
            width: 40px;
            height: 40px;
            padding: 0;
            border-radius: 10px;
        }

        .btn-icon.btn-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
        }

        .btn-icon.btn-lg {
            width: 48px;
            height: 48px;
            border-radius: 12px;
        }

        /* Button Group */
        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-radius: 10px 0 0 10px;
        }

        .btn-group .btn:last-child {
            border-radius: 0 10px 10px 0;
        }

        .btn-group .btn:not(:last-child) {
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Disabled State */
        .btn:disabled,
        .btn.disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Loading State */
        .btn-loading {
            pointer-events: none;
            opacity: 0.75;
        }

        .btn-loading::after {
            content: '';
            width: 1em;
            height: 1em;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: btn-spinner 0.75s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes btn-spinner {
            to { transform: rotate(360deg); }
        }

        /* Link Style Button */
        .btn-link {
            background: transparent !important;
            border: none !important;
            color: var(--doctor-primary) !important;
            box-shadow: none !important;
            padding: 0.5rem 0.75rem;
            text-decoration: none;
        }

        .btn-link:hover {
            color: var(--doctor-primary-dark) !important;
            text-decoration: underline;
            transform: none;
            box-shadow: none !important;
        }

        /* Doctor Custom Primary Button */
        .btn-doctor-primary {
            background: linear-gradient(135deg, var(--doctor-primary) 0%, var(--doctor-primary-dark) 100%);
            color: #ffffff !important;
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-doctor-primary:hover {
            background: linear-gradient(135deg, var(--doctor-primary-dark) 0%, var(--doctor-primary) 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
            color: #ffffff !important;
        }

        .btn-doctor-primary:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        /* Gradient Buttons */
        .btn-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-gradient-primary:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
            color: #ffffff !important;
        }

        .btn-gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
        }

        .btn-gradient-success:hover {
            background: linear-gradient(135deg, #38ef7d 0%, #11998e 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.5);
            color: #ffffff !important;
        }

        .btn-gradient-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 15px rgba(235, 51, 73, 0.4);
        }

        .btn-gradient-danger:hover {
            background: linear-gradient(135deg, #f45c43 0%, #eb3349 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(235, 51, 73, 0.5);
            color: #ffffff !important;
        }

        .btn-gradient-info {
            background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
            color: #ffffff !important;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 114, 255, 0.4);
        }

        .btn-gradient-info:hover {
            background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 114, 255, 0.5);
            color: #ffffff !important;
        }

        /* ==================== END MODERN BUTTON STYLES ==================== */

        /* ==================== MODERN SEARCH & FILTER STYLES ==================== */

        /* Quick Search Input */
        .doctor-header-search {
            position: relative;
        }

        .doctor-header-search .form-control {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            padding: 0.625rem 1rem 0.625rem 2.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            width: 280px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .doctor-header-search .form-control::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        .doctor-header-search .form-control:focus {
            background: #ffffff;
            border-color: var(--doctor-primary);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1), 0 4px 12px rgba(0, 0, 0, 0.08);
            outline: none;
            width: 320px;
        }

        .doctor-header-search .fa-search {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.875rem;
            transition: color 0.3s ease;
            pointer-events: none;
            z-index: 2;
        }

        .doctor-header-search .form-control:focus + .fa-search,
        .doctor-header-search:focus-within .fa-search {
            color: var(--doctor-primary);
        }

        /* Search Results Dropdown */
        #quickPatientSearchResults {
            background: #ffffff;
            border: none !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
            margin-top: 8px;
            overflow: hidden;
            animation: searchDropdown 0.2s ease-out;
        }

        @keyframes searchDropdown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #quickPatientSearchResults a {
            border-radius: 10px;
            margin: 4px;
            transition: all 0.2s ease;
        }

        #quickPatientSearchResults a:hover {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            transform: translateX(4px);
        }

        /* DataTables Modern Styling */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1.5rem;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            color: #64748b;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_length select {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 2rem 0.5rem 1rem;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: var(--doctor-primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.625rem 1rem 0.625rem 2.5rem;
            font-weight: 500;
            color: #334155;
            min-width: 250px;
            transition: all 0.3s ease;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 0.75rem center;
        }

        .dataTables_wrapper .dataTables_filter input::placeholder {
            color: #94a3b8;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--doctor-primary);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1), 0 4px 12px rgba(0, 0, 0, 0.08);
            outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230d6efd' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
        }

        /* DataTables Info and Pagination */
        .dataTables_wrapper .dataTables_info {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            padding-top: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0 !important;
            border-radius: 10px !important;
            padding: 0.5rem 0.875rem !important;
            margin: 0 0.25rem !important;
            font-weight: 600;
            color: #64748b !important;
            transition: all 0.25s ease;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
            border-color: var(--doctor-primary) !important;
            color: var(--doctor-primary) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--doctor-primary) 0%, var(--doctor-primary-dark) 100%) !important;
            border-color: var(--doctor-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, var(--doctor-primary-dark) 0%, var(--doctor-primary) 100%) !important;
            transform: translateY(-2px);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Modern Filter Card */
        .filter-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .filter-card-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .filter-card-header h6 {
            margin: 0;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-card-header h6 i {
            color: var(--doctor-primary);
        }

        .filter-card-body {
            padding: 1.5rem;
        }

        /* Filter Form Elements */
        .filter-group {
            margin-bottom: 1rem;
        }

        .filter-group:last-child {
            margin-bottom: 0;
        }

        .filter-label {
            display: block;
            font-weight: 600;
            color: #475569;
            font-size: 0.8125rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #334155;
            transition: all 0.3s ease;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: var(--doctor-primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
            outline: none;
        }

        .filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
            cursor: pointer;
        }

        /* Filter Buttons */
        .filter-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .btn-filter {
            flex: 1;
            padding: 0.625rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.25s ease;
        }

        .btn-filter-apply {
            background: linear-gradient(135deg, var(--doctor-primary) 0%, var(--doctor-primary-dark) 100%);
            color: #ffffff;
            border: none;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25);
        }

        .btn-filter-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.35);
        }

        .btn-filter-reset {
            background: transparent;
            color: #64748b;
            border: 2px solid #e2e8f0;
        }

        .btn-filter-reset:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #475569;
        }

        /* Quick Filter Pills */
        .filter-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.875rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .filter-pill:hover {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-color: var(--doctor-primary);
            color: var(--doctor-primary);
        }

        .filter-pill.active {
            background: linear-gradient(135deg, var(--doctor-primary) 0%, var(--doctor-primary-dark) 100%);
            border-color: var(--doctor-primary);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.25);
        }

        .filter-pill i {
            font-size: 0.75rem;
        }

        /* Search with Icon */
        .search-input-group {
            position: relative;
        }

        .search-input-group .form-control {
            padding-left: 2.75rem;
        }

        .search-input-group .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            z-index: 2;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .search-input-group .form-control:focus + .search-icon {
            color: var(--doctor-primary);
        }

        /* Date Range Picker */
        .date-range-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .date-range-group .filter-input {
            flex: 1;
        }

        .date-range-separator {
            color: #94a3b8;
            font-weight: 500;
        }

        /* Status Badge Filters */
        .status-filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .status-filter-btn {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8125rem;
            font-weight: 600;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .status-filter-btn.all {
            background: #f1f5f9;
            color: #64748b;
            border-color: #e2e8f0;
        }

        .status-filter-btn.all:hover,
        .status-filter-btn.all.active {
            background: #1e293b;
            color: #ffffff;
            border-color: #1e293b;
        }

        .status-filter-btn.confirmed {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .status-filter-btn.confirmed:hover,
        .status-filter-btn.confirmed.active {
            background: #22c55e;
            color: #ffffff;
            border-color: #22c55e;
        }

        .status-filter-btn.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border-color: rgba(245, 158, 11, 0.3);
        }

        .status-filter-btn.pending:hover,
        .status-filter-btn.pending.active {
            background: #f59e0b;
            color: #ffffff;
            border-color: #f59e0b;
        }

        .status-filter-btn.cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .status-filter-btn.cancelled:hover,
        .status-filter-btn.cancelled.active {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }

        /* Advanced Search Toggle */
        .advanced-search-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: transparent;
            border: none;
            color: var(--doctor-primary);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .advanced-search-toggle:hover {
            color: var(--doctor-primary-dark);
        }

        .advanced-search-toggle i {
            transition: transform 0.3s ease;
        }

        .advanced-search-toggle.active i {
            transform: rotate(180deg);
        }

        /* Collapsible Filter Section */
        .filter-collapse {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .filter-collapse.show {
            max-height: 500px;
        }

        /* ==================== END SEARCH & FILTER STYLES ==================== */

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
<body>
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
                    $isDropdown = false;
                    
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
                        case 'my-documents':
                            $route = null; // Dropdown, no direct route
                            $isActive = request()->routeIs('staff.generated-documents.*') || 
                                       request()->routeIs('staff.templates.*');
                            $isDropdown = true;
                            break;
                        case 'form-requests':
                        case 'form-submissions':
                            $route = route('staff.form-requests.index');
                            $isActive = request()->routeIs('staff.form-requests.*');
                            break;
                        case 'alerts':
                            $route = route('staff.alerts.index');
                            $isActive = request()->routeIs('staff.alerts.*');
                            break;
                        case 'billing':
                            $route = route('staff.billing.index');
                            $isActive = request()->routeIs('staff.billing.*');
                            break;
                        case 'feedback':
                            $route = route('staff.feedback.index');
                            $isActive = request()->routeIs('staff.feedback.*');
                            break;
                        case 'doctor-services':
                        case 'services':
                            $route = route('staff.doctor-services.index');
                            $isActive = request()->routeIs('staff.doctor-services.*');
                            break;
                        case 'schedule':
                        case 'availability':
                            $route = route('staff.schedule.index');
                            $isActive = request()->routeIs('staff.schedule.*');
                            break;
                        default:
                            // Try to use route if provided, otherwise skip
                            if (isset($item['route'])) {
                                $route = $item['route'];
                            }
                    }
                @endphp
                @if($isDropdown && $menuKey === 'my-documents')
                    @php
                        $isLetterTemplatesVisible = \App\Models\RoleMenuVisibility::isVisible($userRole, 'staff', 'letter-templates');
                        $isFormTemplatesVisible = \App\Models\RoleMenuVisibility::isVisible($userRole, 'staff', 'form-templates');
                    @endphp
                    <div class="doctor-nav-item">
                        <div class="dropdown">
                            <a href="#" 
                               class="doctor-nav-link dropdown-toggle {{ $isActive ? 'active' : '' }}"
                               data-bs-toggle="dropdown"
                               role="button"
                               aria-expanded="false"
                               title="{{ $label }}">
                                <i class="{{ $icon }} doctor-nav-icon" aria-hidden="true"></i>
                                <span class="doctor-nav-text">{{ $label }}</span>
                                <i class="fas fa-chevron-down ms-auto" style="font-size: 0.75rem;"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('staff.generated-documents.*') ? 'active' : '' }}" 
                                       href="{{ route('staff.generated-documents.index') }}">
                                        <i class="fas fa-file-pdf me-2"></i>My Documents
                                    </a>
                                </li>
                                @if($isLetterTemplatesVisible)
                                <li>
                                    <a class="dropdown-item {{ (request()->routeIs('staff.templates.*') && request('type') === 'letter') ? 'active' : '' }}" 
                                       href="{{ route('staff.templates.index', ['type' => 'letter']) }}">
                                        <i class="fas fa-envelope me-2"></i>Letter Templates
                                    </a>
                                </li>
                                @endif
                                @if($isFormTemplatesVisible)
                                <li>
                                    <a class="dropdown-item {{ (request()->routeIs('staff.templates.*') && request('type') === 'form') ? 'active' : '' }}" 
                                       href="{{ route('staff.templates.index', ['type' => 'form']) }}">
                                        <i class="fas fa-file-alt me-2"></i>Form Templates
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @elseif($route !== '#')
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
            <div class="doctor-nav-title">
                Quick Links
                <i class="fas fa-grip-vertical ms-2" style="font-size: 0.8rem; opacity: 0.5;" title="Drag to reorder"></i>
            </div>
            <div id="quickLinksSortable">
            @foreach($customMenuItems as $customItem)
            <div class="doctor-nav-item draggable-item" data-id="{{ $customItem->id }}" style="cursor: move;">
                <i class="fas fa-grip-vertical drag-handle" style="opacity: 0.3; margin-right: 8px; cursor: grab;"></i>
                <a href="{{ $customItem->url }}" 
                   target="{{ $customItem->target }}"
                   class="doctor-nav-link"
                   rel="noopener noreferrer"
                   style="flex: 1;">
                    <i class="{{ $customItem->icon ?? 'fas fa-external-link-alt' }} doctor-nav-icon"></i>
                    <span class="doctor-nav-text">{{ $customItem->label }}</span>
                    @if($customItem->target === '_blank')
                        <i class="fas fa-external-link-alt" style="font-size: 0.7rem; opacity: 0.7;"></i>
                    @endif
                </a>
            </div>
            @endforeach
            </div>
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
                        <span id="doctorNotificationCount" class="badge rounded-pill bg-danger position-absolute" style="display: none; top: -2px; right: -2px; font-size: 0.6rem; min-width: 16px; height: 16px; line-height: 16px; padding: 0 4px;">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="width: 380px; max-height: 450px; overflow-y: auto; border-radius: 16px; margin-top: 0.5rem;">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-bell me-2"></i>Notifications</h6>
                            <a href="javascript:void(0)" class="text-muted small" onclick="markAllNotificationsRead()">Mark all read</a>
                        </div>
                        <div id="doctorNotificationList" class="p-2">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mb-0 small text-muted">Loading notifications...</p>
                            </div>
                        </div>
                        <div class="p-2 border-top text-center">
                            <a href="{{ route('staff.notifications.index') }}" class="text-primary small fw-semibold">
                                <i class="fas fa-eye me-1"></i>View All Notifications
                            </a>
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
                            <div class="doctor-user-role">
                                @php
                                    $user = auth()->user();
                                    // Always use the role field from the database, not specialization
                                    $role = $user->role ?? 'staff';
                                    // Map common roles to display names (including variations)
                                    $roleMap = [
                                        'doctor' => 'Doctor',
                                        'nurse' => 'Nurse',
                                        'receptionist' => 'Receptionist',
                                        'pharmacist' => 'Pharmacist',
                                        'technician' => 'Technician',
                                        'admin' => 'Administrator',
                                        'staff' => 'Staff',
                                        'medical director' => 'Doctor', // Map Medical Director to Doctor
                                        'medical_director' => 'Doctor',
                                    ];
                                    // Get display name from map, or format the role value
                                    $roleDisplay = $roleMap[strtolower($role)] ?? ucwords(str_replace(['_', '-'], ' ', strtolower($role)));
                                @endphp
                                {{ $roleDisplay }}
                            </div>
                        </div>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: 16px; margin-top: 0.5rem;">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="{{ route('staff.schedule.index') }}"><i class="fas fa-calendar-alt me-2"></i>My Schedule</a></li>
                        <li><a class="dropdown-item" href="{{ route('staff.doctor-services.index') }}"><i class="fas fa-briefcase-medical me-2"></i>Services</a></li>
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
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
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

            // Load notifications for doctor
            loadDoctorNotifications();

            // Refresh notifications every 30 seconds
            setInterval(loadDoctorNotifications, 30000);
            
            // Initialize Quick Links Sortable
            const quickLinksContainer = document.getElementById('quickLinksSortable');
            if (quickLinksContainer && typeof Sortable !== 'undefined') {
                new Sortable(quickLinksContainer, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        // Get the new order
                        const items = quickLinksContainer.querySelectorAll('.draggable-item');
                        const order = [];
                        items.forEach(function(item, index) {
                            order.push({
                                id: item.getAttribute('data-id'),
                                order: index + 1
                            });
                        });
                        
                        // Save the new order via AJAX
                        fetch('{{ route("staff.custom-menu-items.reorder") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show subtle success feedback
                                const feedback = document.createElement('div');
                                feedback.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 24px; border-radius: 8px; z-index: 9999; animation: fadeInOut 2s;';
                                feedback.innerHTML = '<i class="fas fa-check me-2"></i>Quick Links order saved';
                                document.body.appendChild(feedback);
                                setTimeout(() => feedback.remove(), 2000);
                            }
                        })
                        .catch(error => {
                            console.error('Error saving order:', error);
                        });
                    }
                });
            }
        });
    </script>

    <!-- Doctor Notification Functions -->
    <script>
    function loadDoctorNotifications() {
        fetch('{{ route("staff.notifications.api.staff") }}')
            .then(response => response.json())
            .then(data => {
                updateDoctorNotificationBadge(data.total_count);
                updateDoctorNotificationList(data.notifications);
            })
            .catch(error => {
                console.error('Failed to load notifications:', error);
            });
    }

    function updateDoctorNotificationBadge(count) {
        const badge = document.getElementById('doctorNotificationCount');
        if (!badge) return;

        if (count > 0) {
            const displayCount = count > 99 ? '99+' : count;
            badge.textContent = displayCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function updateDoctorNotificationList(notifications) {
        const container = document.getElementById('doctorNotificationList');
        if (!container) return;

        if (!notifications || notifications.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                    <p class="mb-0 small text-muted">No new notifications</p>
                </div>
            `;
            return;
        }

        let html = '';
        notifications.forEach(notification => {
            const iconClass = notification.icon || 'fas fa-bell';
            const typeColor = notification.type === 'warning' ? '#ffc107' :
                            notification.type === 'info' ? '#17a2b8' :
                            notification.type === 'success' ? '#28a745' : '#007bff';

            html += `
                <a href="${notification.url || '#'}" class="d-flex align-items-start p-2 rounded text-decoration-none notification-item mb-1" style="background: rgba(0,0,0,0.02);">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: ${typeColor}15;">
                            <i class="${iconClass}" style="color: ${typeColor};"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="fw-semibold text-dark small">${notification.title}</div>
                        <div class="text-muted small text-truncate">${notification.message}</div>
                        <div class="text-muted small mt-1">
                            <i class="fas fa-clock me-1"></i>${notification.created_at}
                            ${notification.count ? `<span class="badge bg-${notification.type === 'warning' ? 'warning text-dark' : notification.type === 'success' ? 'success' : 'primary'} ms-2">${notification.count}</span>` : ''}
                        </div>
                    </div>
                </a>
            `;
        });

        container.innerHTML = html;
    }

    function markAllNotificationsRead() {
        fetch('{{ route("staff.notifications.markAllAsRead") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadDoctorNotifications();
            }
        })
        .catch(error => {
            console.error('Failed to mark notifications as read:', error);
        });
    }
    </script>

    <style>
    .sortable-ghost {
        opacity: 0.4;
    }
    .sortable-chosen {
        background: rgba(0, 123, 255, 0.1);
    }
    .draggable-item {
        display: flex;
        align-items: center;
    }
    .drag-handle:hover {
        opacity: 0.7 !important;
    }
    @keyframes fadeInOut {
        0%, 100% { opacity: 0; }
        10%, 90% { opacity: 1; }
    }
    .notification-item:hover {
        background: rgba(0,0,0,0.05) !important;
    }
    </style>
</body>
</html>

