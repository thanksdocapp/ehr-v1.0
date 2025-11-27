<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Portal') - {{ getAppName() }}</title>
    
    <!-- Modern Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --secondary: #64748b;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --light: #f8fafc;
            --dark: #0f172a;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: #334155;
            font-size: 14px;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0; bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.04);
            z-index: 3000; /* ensure above everything */
            overflow-y: auto;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: left;
        }

        /* Custom Sidebar Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f8f9fc;
            border-radius: 10px;
            margin: 10px 0;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .sidebar::-webkit-scrollbar-thumb:active {
            background: #64748b;
        }

        /* Notification Bell Styles */
        .notification-btn {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: rgba(79, 70, 229, 0.1) !important;
            transform: scale(1.05);
        }

        .notification-dropdown {
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: none;
            animation: slideInDown 0.3s ease;
        }

        .notification-item {
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 2px;
        }

        .notification-item:hover {
            background: rgba(79, 70, 229, 0.05);
            transform: translateX(3px);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Firefox Scrollbar */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8f9fc;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 2px solid #e2e8f0;
            text-align: center;
            background: #f8f9fc;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: #1a202c;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .logo i {
            font-size: 24px;
            color: white;
        }

        .brand-text {
            color: #1a202c;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .brand-subtitle {
            color: #4a5568;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Navigation */
        .nav-section {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
        }

        .nav-title {
            color: #6c757d;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
            padding-left: 0.5rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.25rem;
            color: #2d3748;
            text-decoration: none;
            border-radius: 16px;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #f8f9fc;
            color: #1a202c;
            transform: translateX(8px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .nav-link.active::before {
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
        
        .nav-link i {
            color: #1a202c;
        }

        .nav-icon {
            width: 24px;
            text-align: center;
            margin-right: 1rem;
            font-size: 1.125rem;
        }

        .nav-text {
            flex: 1;
            font-size: 0.9rem;
        }

        .nav-badge {
            background: #e2e8f0;
            color: #1a202c;
            font-size: 0.65rem;
            padding: 0.15rem 0.45rem;
            border-radius: 12px;
            font-weight: 500;
            text-transform: capitalize;
            letter-spacing: 0.01em;
            line-height: 1.1;
            opacity: 0.95;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        
        .nav-link:hover .nav-badge {
            opacity: 1;
            transform: scale(1.05);
        }
        
        /* Ensure custom menu links are clickable */
        .nav-link.custom-menu-link {
            cursor: pointer !important;
            pointer-events: auto !important;
            position: relative;
            z-index: 10;
        }
        
        .nav-link.custom-menu-link:hover {
            cursor: pointer !important;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--light);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header {
            height: var(--header-height);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 1200; /* below sidebar (3000) but above content */
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            padding-top: env(safe-area-inset-top, 0px);
        }

        .header-left h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Ensure header layout keeps hamburger visible */
        .header-left {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 1;
            min-width: 0; /* allow text to truncate */
        }
        .header-left > .d-flex {
            flex: 1;
            min-width: 0; /* allow title to shrink */
        }

        .header-subtitle {
            color: var(--secondary);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .notification-btn {
            position: relative;
            background: var(--light);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .notification-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.3);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--danger);
            color: white;
            font-size: 0.75rem;
            padding: 0.125rem 0.375rem;
            border-radius: 10px;
            font-weight: 600;
        }

        .user-menu {
            display: flex;
            align-items: center;
            background: white;
            padding: 0.5rem;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-menu:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--info));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 0.75rem;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.875rem;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--secondary);
            line-height: 1.2;
        }

        /* Content Area */
        .content-wrapper {
            padding: 2rem;
        }

        /* Cards */
        .card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: none;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.5rem 2rem 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }

        .card-body {
            padding: 2rem;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--info));
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--secondary);
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
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

        .stat-card-enhanced .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .stat-card-enhanced .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.4;
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0;
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

            .stat-card-enhanced .stat-number {
                font-size: 1.5rem;
            }

            .stat-card-enhanced .stat-label {
                font-size: 0.8rem;
            }
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #16a34a);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info), #0891b2);
            color: white;
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
        }

        .form-check-label {
            color: #2d3748 !important;
            font-weight: 500 !important;
            cursor: pointer !important;
            line-height: 1.5 !important;
            flex: 1 !important;
            margin: 0 !important;
        }

        .form-check-label i {
            color: #1a202c !important;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            html, body { overflow-x: hidden !important; }
            .sidebar {
                left: -280px; /* hide off-canvas */
                right: auto;
                width: 280px; /* fixed width for mobile */
                position: fixed;
                top: 0; bottom: 0; /* ensure full height */
                -webkit-overflow-scrolling: touch;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.show {
                left: 0 !important; /* force fully visible */
                padding-top: 0; /* overlay above header */
            }
        }

        @media (max-width: 768px) {
            .header {
                padding: 0 1rem;
            }
            
            .content-wrapper {
                padding: 1rem;
            }
            
            .header-left h1 {
                font-size: 1.5rem;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-up {
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        /* Mobile menu toggle button */
        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 10px;
            min-width: 40px;
            min-height: 40px;
            z-index: 2001; /* above header content */
            margin-left: calc(env(safe-area-inset-left, 0px) + 8px);
            flex: 0 0 auto; /* do not shrink */
        }
        .sidebar-toggle:focus { outline: none; }
        @media (min-width: 1025px) {
            .sidebar-toggle { display: none !important; }
        }
        
        /* Mobile overlay for sidebar */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1500; /* below sidebar (2000) */
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .mobile-overlay.show { display: block; opacity: 1; }
        @media (max-width: 1024px) {
            .mobile-overlay.with-sidebar { left: 280px; } /* don't cover the sidebar */
        }
        
        /* Prevent header text overlapping on small screens */
        @media (max-width: 768px) {
            .header-subtitle { display: none !important; }
            .header {
                height: var(--header-height);
                padding-right: 0.875rem;
                padding-left: calc(env(safe-area-inset-left, 0px) + 0.875rem);
                padding-top: calc(env(safe-area-inset-top, 0px) + 2px);
            }
            .header-left h1 {
                font-size: 1.25rem !important;
                white-space: nowrap;
            }
            .header-right { gap: 0.5rem; }
            .user-menu { padding: 0.25rem 0.5rem; }
            .user-info { display: none !IMPORTANT; } /* hide name/role to prevent header overflow */
            .sidebar-toggle { margin-left: calc(env(safe-area-inset-left, 0px) + 8px); }
        }
        
        @media (max-width: 420px) {
            .header-left h1 { font-size: 1.1rem !important; }
        }
        
        /* Ensure modals are always above sidebar and clickable */
        /* Remove backdrop blocking - make it non-interactive */
        .modal-backdrop {
            z-index: 3001 !important; /* Above sidebar (3000) */
            pointer-events: none !important; /* Don't block clicks - allow clicks through backdrop */
        }
        
        .modal-backdrop.fade {
            pointer-events: none !important;
        }
        
        .modal-backdrop.show {
            pointer-events: none !important;
        }
        
        .modal {
            z-index: 3002 !important; /* Above backdrop (3001) */
            pointer-events: none !important; /* Allow clicks through modal container */
        }
        
        .modal-dialog {
            pointer-events: auto !important; /* Enable clicks on modal dialog */
            z-index: 3003 !important;
        }
        
        .modal-content {
            pointer-events: auto !important; /* Enable clicks on modal content */
            z-index: 3004 !important;
        }
        
        .modal-footer,
        .modal-header,
        .modal-body {
            pointer-events: auto !important; /* Ensure all modal sections are clickable */
        }
        
        .modal-footer .btn,
        .modal-header .btn,
        .modal-body .btn {
            pointer-events: auto !important; /* Ensure buttons are clickable */
            position: relative;
            z-index: 3005 !important;
        }
        
        /* Ensure mobile overlay doesn't interfere with modals */
        .mobile-overlay {
            z-index: 1500 !important; /* Below modals */
        }
        
        /* When modal is open, hide mobile overlay if present */
        body.modal-open .mobile-overlay {
            display: none !important;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #e2e8f0;
        }

        body.dark-mode .main-content {
            background: #0f172a;
            color: #e2e8f0;
        }

        body.dark-mode .header {
            background: rgba(30, 41, 59, 0.95);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .header-left h1 {
            color: #f1f5f9;
        }

        body.dark-mode .header-subtitle {
            color: #94a3b8;
        }

        body.dark-mode .content-wrapper {
            background: #0f172a;
            color: #e2e8f0;
        }

        body.dark-mode .card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        body.dark-mode .card-header {
            background: #334155;
            border-bottom-color: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }

        body.dark-mode .card-body {
            color: #e2e8f0;
        }

        body.dark-mode .table {
            color: #e2e8f0;
        }

        body.dark-mode .table thead th {
            border-color: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }

        body.dark-mode .table tbody td {
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.05);
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
            border-color: var(--primary);
            color: #e2e8f0;
        }

        body.dark-mode .form-control::placeholder {
            color: #64748b;
        }

        body.dark-mode .btn-outline-secondary {
            border-color: rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
        }

        body.dark-mode .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #f1f5f9;
        }

        body.dark-mode .text-muted {
            color: #94a3b8 !important;
        }

        body.dark-mode .alert {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
        }

        body.dark-mode .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        body.dark-mode .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        body.dark-mode .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fcd34d;
        }

        body.dark-mode .alert-info {
            background: rgba(6, 182, 212, 0.1);
            border-color: rgba(6, 182, 212, 0.3);
            color: #67e8f9;
        }

        body.dark-mode .user-menu {
            background: #1e293b;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
        }

        body.dark-mode .notification-btn {
            background: #1e293b;
            color: #e2e8f0;
        }

        body.dark-mode .notification-btn:hover {
            background: var(--primary);
            color: white;
        }

        body.dark-mode .dropdown-menu {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .dropdown-item {
            color: #e2e8f0;
        }

        body.dark-mode .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #f1f5f9;
        }

        body.dark-mode .stat-card {
            background: #1e293b;
            border-color: rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .badge {
            color: #f1f5f9;
        }

        body.dark-mode .text-secondary {
            color: #94a3b8 !important;
        }

        body.dark-mode #darkModeToggle {
            border-color: rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
        }

        body.dark-mode #darkModeToggle:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
            color: #fbbf24;
        }

        /* Dark mode transitions */
        body.dark-mode * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
    
    @stack('styles')
</head>
<body class="{{ auth()->check() && auth()->user()->role === 'doctor' && auth()->user()->dark_mode ? 'dark-mode' : '' }}">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                @if(isset($site_settings) && ($site_settings['site_logo_dark'] ?? false))
                    <img src="{{ asset($site_settings['site_logo_dark']) }}" alt="{{ getAppName() }}" style="max-width: 100%; max-height: 50px; object-fit: contain;">
                @elseif(isset($site_settings) && ($site_settings['site_logo'] ?? false))
                    <img src="{{ asset($site_settings['site_logo']) }}" alt="{{ getAppName() }}" style="max-width: 100%; max-height: 50px; object-fit: contain;">
                @else
                    <div style="color: #1a202c; font-weight: 600; font-size: 1.1rem;">{{ getAppName() }}</div>
                @endif
            </div>
            <div class="brand-subtitle">Staff Portal</div>
        </div>
        
        @php
            // Check if 2FA forced setup is active
            $isForced2FASetup = isset($isForced2FASetup) ? $isForced2FASetup : false;
            if (!$isForced2FASetup && auth()->check()) {
                // Check if 2FA is required but not enabled
                $user = auth()->user();
                $twoFactorService = app(\App\Services\TwoFactorAuthService::class);
                $isForced2FASetup = $twoFactorService->isRequired($user) && !$twoFactorService->requiresTwoFactor($user);
            }
        @endphp
        
        @if(!$isForced2FASetup)
        @php
            // Get ordered and visible menu items for current user
            $menuItems = getSidebarMenuItems('staff');
            $isAdmin = auth()->user()->is_admin ?? false;
            
            // Filter out custom menu items from Main Menu (they go in Quick Links section)
            // Custom items have 'is_custom' flag or start with 'custom-'
            $mainMenuItems = array_filter($menuItems, function($item) {
                // Skip if it's marked as custom
                if (isset($item['is_custom']) && $item['is_custom']) {
                    return false;
                }
                // Skip if menu_key starts with 'custom-'
                if (isset($item['menu_key']) && str_starts_with($item['menu_key'], 'custom-')) {
                    return false;
                }
                return true;
            });
            // Re-index array after filtering
            $mainMenuItems = array_values($mainMenuItems);
        @endphp
        
        {{-- Render ordered menu items dynamically --}}
        <div class="nav-section">
            <div class="nav-title">Main Menu</div>
            @foreach($mainMenuItems as $item)
                @include('partials.staff-menu-item', ['item' => $item])
            @endforeach
        </div>

        {{-- Custom Menu Items (Links) - Respects Role-Based Visibility --}}
        @php
            $userRole = auth()->user()->role ?? 'staff';
            $customMenuItems = \App\Models\CustomMenuItem::getActiveForMenuTypeAndRole('staff', $userRole);
        @endphp
        @if($customMenuItems->count() > 0)
        <div class="nav-section">
            <div class="nav-title">Quick Links</div>
            @foreach($customMenuItems as $customItem)
            <div class="nav-item">
                <a href="{{ $customItem->url }}" 
                   target="{{ $customItem->target }}" 
                   class="nav-link custom-menu-link"
                   rel="noopener noreferrer"
                   style="cursor: pointer; pointer-events: auto !important; z-index: 10;">
                    <i class="nav-icon fas {{ $customItem->icon ?? 'fa-external-link-alt' }}"></i>
                    <span class="nav-text">{{ $customItem->label }}</span>
                    @if($customItem->target === '_blank')
                        <i class="fas fa-external-link-alt ms-auto" style="font-size: 0.75rem; opacity: 0.7;"></i>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        @endif
        @else
        {{-- Show locked navigation message when 2FA setup is forced --}}
        <div class="nav-section">
            <div class="alert alert-warning m-3" role="alert" style="background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.3);">
                <i class="fas fa-lock me-2"></i>
                <small><strong>Navigation Locked</strong><br>Complete 2FA setup to access the system.</small>
            </div>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="sidebar-toggle btn btn-light d-inline-flex d-lg-none me-2" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center">
                    <h1 class="mb-0">@yield('page-title', 'Staff Dashboard')</h1>
                </div>
                <div class="header-subtitle">@yield('page-subtitle', 'Welcome to the staff portal')</div>
            </div>
            <div class="header-right">
                {{-- Dark Mode Toggle (Doctor Role Only) --}}
                @if(auth()->user()->role === 'doctor')
                <div class="dark-mode-toggle me-3">
                    <button class="btn btn-outline-secondary btn-sm" id="darkModeToggle" 
                            title="{{ auth()->user()->dark_mode ? 'Disable Dark Mode' : 'Enable Dark Mode' }}">
                        <i class="fas {{ auth()->user()->dark_mode ? 'fa-sun' : 'fa-moon' }}"></i>
                    </button>
                </div>
                @endif
                
                {{-- Staff Notification Bell --}}
                <div class="header-notifications position-relative me-3">
                    <button class="notification-btn btn border-0 p-2 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fs-5 text-secondary"></i>
                        <span id="staffNotificationCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem; min-width: 18px; height: 18px;">
                            0
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg border-0" style="width: 380px; max-height: 450px; overflow-y: auto;">
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h6>
                            <small class="text-muted">Staff Portal</small>
                        </div>
                        <div id="staffNotificationList" class="p-2">
                            <div class="text-center py-4">
                                <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mb-0 small text-muted">Loading notifications...</p>
                            </div>
                        </div>
                        <div class="border-top p-2">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('staff.notifications.index') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>View All
                                </a>
                                <button class="btn btn-sm btn-outline-secondary" onclick="markAllStaffNotificationsAsRead()">
                                    <i class="fas fa-check-double me-1"></i>Mark All Read
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="user-menu" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name ?? 'Staff User' }}</div>
                        <div class="user-role">{{ ucfirst(auth()->user()->role ?? 'Staff') }}</div>
                    </div>
                    <i class="fas fa-chevron-down ms-2"></i>
                </div>
                
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(!(isset($isForced2FASetup) && $isForced2FASetup))
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('change-password') }}"><i class="fas fa-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    @endif
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

        <!-- Content -->
        <div class="content-wrapper">
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
            
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Auto-hide alerts after 30 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 30000);
        
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const isOpen = sidebar.classList.contains('show');
                    if (isOpen) {
                        sidebar.classList.remove('show');
                        const ov = document.querySelector('.mobile-overlay');
                        if (ov) { ov.classList.remove('show'); setTimeout(()=>ov.remove(), 300); }
                    } else {
                        sidebar.classList.add('show');
                        let overlay = document.querySelector('.mobile-overlay');
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'mobile-overlay show with-sidebar';
                            overlay.addEventListener('click', function(){
                                sidebar.classList.remove('show');
                                this.classList.remove('show');
                                setTimeout(()=>this.remove(), 300);
                            });
                            document.body.appendChild(overlay);
                        } else {
                            overlay.classList.add('show');
                            overlay.classList.add('with-sidebar');
                        }
                    }
                });
            }
            
            // Handle custom menu links separately - allow normal link behavior
            $(document).on('click', '.sidebar .nav-link.custom-menu-link', function(e) {
                // Allow normal link behavior - don't interfere
                // Links will open based on their target attribute
                const href = $(this).attr('href');
                if (href && (href.startsWith('http://') || href.startsWith('https://') || href.startsWith('mailto:') || href.startsWith('tel:'))) {
                    // External link - allow normal navigation, no need to prevent default
                    // Only close sidebar on mobile after allowing navigation
                    if (window.innerWidth <= 1024) {
                        setTimeout(()=>{
                            document.querySelector('.sidebar')?.classList.remove('show');
                            const ov = document.querySelector('.mobile-overlay');
                            if (ov) { ov.classList.remove('show'); setTimeout(()=>ov.remove(), 300); }
                        }, 300);
                    }
                    return true; // Allow default link behavior
                }
                // For any other links, also allow normal behavior
                if (window.innerWidth <= 1024) {
                    setTimeout(()=>{
                        document.querySelector('.sidebar')?.classList.remove('show');
                        const ov = document.querySelector('.mobile-overlay');
                        if (ov) { ov.classList.remove('show'); setTimeout(()=>ov.remove(), 300); }
                    }, 300);
                }
                return true; // Allow default link behavior
            });
            
            // Close sidebar after tapping a nav link on mobile (for non-custom menu items)
            if (window.innerWidth <= 1024) {
                $(document).on('click', '.sidebar .nav-link:not(.custom-menu-link)', function(e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        setTimeout(()=>{ window.location.href = href; }, 10);
                    }
                    setTimeout(()=>{
                        document.querySelector('.sidebar')?.classList.remove('show');
                        const ov = document.querySelector('.mobile-overlay');
                        if (ov) { ov.classList.remove('show'); setTimeout(()=>ov.remove(), 300); }
                    }, 200);
                });
            }
            
            // Load staff notifications on page load
            console.log('🚀 Document ready - initializing staff notifications');
            loadStaffNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadStaffNotifications, 30000);
        });
        
        // Staff notification functions
        function loadStaffNotifications() {
            const url = '{{ route("staff.notifications.api.staff") }}';
            
            $.get(url)
                .done(function(response) {
                    console.log('✅ Staff notifications loaded:', response);
                    updateStaffNotificationBadge(response.total_count);
                    updateStaffNotificationList(response.notifications);
                })
                .fail(function(xhr, status, error) {
                    console.error('❌ Failed to load staff notifications:', error);
                    updateStaffNotificationList([]);
                });
        }
        
        function updateStaffNotificationBadge(count) {
            const badge = $('#staffNotificationCount');
            
            if (count > 0) {
                badge.text(count > 99 ? '99+' : count).removeClass('d-none').show();
            } else {
                badge.addClass('d-none').hide();
            }
        }
        
        function updateStaffNotificationList(notifications) {
            const container = $('#staffNotificationList');
            
            if (notifications.length === 0) {
                container.html(`
                    <div class="text-center py-4">
                        <i class="fas fa-bell-slash text-muted mb-2" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mb-0 small text-muted">No pending notifications</p>
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
                    <div class="notification-item p-3 border-bottom" style="cursor: pointer;" onclick="handleStaffNotificationClick('${notification.url}', '${notification.notification_id || ''}')">
                        <div class="d-flex align-items-start">
                            <div class="notification-icon me-3 mt-1">
                                <i class="${iconClass} ${colorClass}" style="font-size: 1.1rem;"></i>
                            </div>
                            <div class="notification-content flex-grow-1">
                                <div class="notification-title fw-semibold text-dark mb-1" style="font-size: 13px;">
                                    ${notification.title}
                                    ${notification.count > 1 ? `<span class="badge bg-secondary ms-1">${notification.count}</span>` : ''}
                                </div>
                                <div class="notification-message text-muted mb-1" style="font-size: 12px;">
                                    ${notification.message}
                                </div>
                                <div class="notification-time text-muted" style="font-size: 11px;">
                                    <i class="fas fa-clock me-1"></i>${notification.created_at}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.html(html);
        }
        
        function handleStaffNotificationClick(url, notificationId) {
            // Mark notification as read if it has an ID
            if (notificationId && notificationId.startsWith('user_')) {
                const actualId = notificationId.replace('user_', '');
                markStaffNotificationAsRead(actualId);
            }
            
            // Navigate to the URL
            if (url && url !== '#') {
                window.location.href = url;
            }
        }
        
        function markStaffNotificationAsRead(notificationId) {
            $.post('{{ route("staff.notifications.markAsRead") }}', {
                _token: '{{ csrf_token() }}',
                id: notificationId
            }).done(function(response) {
                if (response.success) {
                    console.log('✅ Staff notification marked as read');
                    loadStaffNotifications(); // Refresh the list
                }
            }).fail(function(xhr, status, error) {
                console.error('❌ Failed to mark staff notification as read:', error);
            });
        }
        
        function markAllStaffNotificationsAsRead() {
            $.post('{{ route("staff.notifications.markAllAsRead") }}', {
                _token: '{{ csrf_token() }}'
            }).done(function(response) {
                if (response.success) {
                    console.log('✅ All staff notifications marked as read');
                    loadStaffNotifications(); // Refresh the list
                    
                    // Show success message
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            }).fail(function(xhr, status, error) {
                console.error('❌ Failed to mark all staff notifications as read:', error);
                
                // Show error message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to mark notifications as read. Please try again.'
                    });
                }
            });
        }
    </script>
    
    @stack('scripts')
    
    <!-- Dark Mode Toggle (Doctor Role Only) -->
    @if(auth()->check() && auth()->user()->role === 'doctor')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function() {
                    fetch('{{ route("staff.toggle-dark-mode") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Toggle dark mode class on body
                            document.body.classList.toggle('dark-mode', data.dark_mode);
                            
                            // Update button icon and title
                            const icon = darkModeToggle.querySelector('i');
                            if (data.dark_mode) {
                                icon.className = 'fas fa-sun';
                                darkModeToggle.title = 'Disable Dark Mode';
                            } else {
                                icon.className = 'fas fa-moon';
                                darkModeToggle.title = 'Enable Dark Mode';
                            }
                            
                            // Optional: Show a brief notification
                            if (window.Swal) {
                                window.Swal.fire({
                                    icon: 'success',
                                    title: data.message,
                                    timer: 1500,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error toggling dark mode:', error);
                    });
                });
            }
        });
    </script>
    @endif
    
    <!-- Global fix: Remove modal backdrop click blocking and persistent backdrops -->
    <script>
    (function() {
        // Function to remove all persistent modal backdrops
        function removePersistentBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                backdrop.remove();
            });
            // Also remove from body class
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
        
        // Function to disable backdrop click blocking
        function disableBackdropClicks() {
            // Find all modal backdrops and disable pointer events, then remove them
            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                backdrop.style.pointerEvents = 'none';
                // Remove backdrop after a short delay if modal is not shown
                setTimeout(function() {
                    if (!document.querySelector('.modal.show')) {
                        backdrop.remove();
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                    }
                }, 100);
            });
        }
        
        // Remove persistent backdrops immediately on load
        removePersistentBackdrops();
        disableBackdropClicks();
        
        // Watch for dynamically created backdrops
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && node.classList.contains('modal-backdrop')) {
                            node.style.pointerEvents = 'none';
                            // Remove backdrop if no modal is shown
                            if (!document.querySelector('.modal.show')) {
                                setTimeout(function() {
                                    node.remove();
                                    document.body.classList.remove('modal-open');
                                    document.body.style.overflow = '';
                                    document.body.style.paddingRight = '';
                                }, 100);
                            }
                        }
                        // Also check children
                        const backdrops = node.querySelectorAll && node.querySelectorAll('.modal-backdrop');
                        if (backdrops) {
                            backdrops.forEach(function(backdrop) {
                                backdrop.style.pointerEvents = 'none';
                            });
                        }
                    }
                });
            });
            disableBackdropClicks();
        });
        
        // Observe document body for changes
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Remove backdrops when modals are hidden (Bootstrap event)
        document.addEventListener('hidden.bs.modal', function() {
            removePersistentBackdrops();
        });
        
        // Also run when modals are shown (Bootstrap event)
        document.addEventListener('shown.bs.modal', function() {
            disableBackdropClicks();
        });
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            removePersistentBackdrops();
        });
        
        // Run on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                removePersistentBackdrops();
                disableBackdropClicks();
            });
        } else {
            removePersistentBackdrops();
            disableBackdropClicks();
        }
        
        // Periodic cleanup check
        setInterval(function() {
            if (!document.querySelector('.modal.show')) {
                removePersistentBackdrops();
            }
        }, 500);
    })();
    </script>
</body>
</html>
