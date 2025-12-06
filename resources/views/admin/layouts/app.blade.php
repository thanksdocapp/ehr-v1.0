@php
use Illuminate\Support\Facades\Storage;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - {{ getAppName() }}</title>
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- AOS Animation Library -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Dynamic Theme CSS - Uses admin appearance settings -->
    <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ time() }}">

    <style>
        :root {
            /* Admin layout specific variables - inherits from dynamic theme CSS */
            --gold-color: #e94560;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --sidebar-width: 280px;
            --header-height: 70px;
            --gradient-card: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-bg);
            color: var(--dark-text);
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--gradient-bg);
            z-index: 1050;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            direction: ltr;
            transform: none;
            -webkit-transform: none;
        }

        .admin-sidebar.collapsed {
            width: 80px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-logo {
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-logo .logo-img {
            height: 35px;
            width: auto;
            max-width: 120px;
            object-fit: contain;
        }
        
        .sidebar-logo i {
            color: var(--gold-color);
        }

        .sidebar-menu {
            padding: 20px 0;
            height: calc(100vh - 120px);
            overflow-y: auto;
            position: relative;
            z-index: 10000;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }

        .menu-item {
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        
        .menu-item .dropdown {
            position: relative;
            z-index: 1;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            pointer-events: auto;
            cursor: pointer;
        }

        .menu-link:hover,
        .menu-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .menu-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gold-color);
        }

        .menu-icon {
            width: 20px;
            margin-right: 15px;
            text-align: center;
            font-size: 16px;
        }

        .menu-text {
            font-weight: 500;
            font-size: 14px;
        }

        .menu-dropdown {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            margin: 5px 15px;
        }

        .dropdown-toggle {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .dropdown-toggle::after {
            margin-left: auto;
            transition: transform 0.3s ease;
            border: none;
            content: "\f078";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            border: 0;
        }

        .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .sidebar-menu .dropdown-menu {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            padding: 5px 0;
            min-width: 200px;
            margin-top: 5px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            position: static !important;
            transform: none !important;
            width: 100%;
            float: none;
            inset: auto !important;
        }

        .sidebar-menu .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            padding: 8px 50px;
            font-size: 13px;
            border-radius: 4px;
            margin: 2px 10px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
            pointer-events: auto;
            cursor: pointer;
            display: block;
        }

        .sidebar-menu .dropdown-item:hover,
        .sidebar-menu .dropdown-item:focus {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        /* Main Content - FIXED FOR SIDEBAR OVERLAP */
        .admin-main {
            margin-left: var(--sidebar-width) !important;
            transition: all 0.3s ease;
            min-height: 100vh;
            position: relative;
            z-index: 1;
            width: calc(100% - var(--sidebar-width)) !important;
            box-sizing: border-box !important;
            background: var(--light-bg) !important;
            overflow-x: hidden !important;
        }

        .admin-main.expanded {
            margin-left: 80px !important;
            width: calc(100% - 80px) !important;
        }

        /* Content Area */
        .admin-content {
            padding: 30px;
        }

        /* Header */
        .admin-header {
            background: white;
            height: var(--header-height);
            padding: 0 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 18px;
            color: var(--dark-text);
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: var(--light-bg);
            transform: scale(1.1);
        }

        .breadcrumb {
            background: none;
            margin: 0;
            padding: 0;
            font-size: 14px;
        }

        .breadcrumb-item a {
            color: var(--gold-color);
            text-decoration: none;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: auto;
        }

        .header-search {
            position: relative;
        }

        .header-search input {
            border: 1px solid #e9ecef;
            border-radius: 25px;
            padding: 8px 40px 8px 20px;
            background: var(--light-bg);
            width: 250px;
            font-size: 14px;
        }

        .header-search .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .header-notifications {
            position: relative;
        }

        .notification-btn {
            background: none;
            border: none;
            color: var(--dark-text);
            font-size: 18px;
            padding: 10px;
            border-radius: 50%;
            transition: all 0.3s ease;
            position: relative;
        }

        .notification-btn:hover {
            background: var(--light-bg);
            transform: scale(1.1);
        }

        .notification-badge-count {
            font-size: 0.65rem;
            min-width: 18px;
            height: 18px;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            z-index: 10;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            animation: pulse-badge 2s infinite;
        }

        .notification-badge-count.show {
            display: flex !important;
        }

        @keyframes pulse-badge {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px;
            border-radius: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .admin-profile:hover {
            background: var(--light-bg);
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--gold-color);
        }

        .profile-info {
            text-align: left;
        }

        .profile-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--dark-text);
        }

        .profile-role {
            font-size: 12px;
            color: #6c757d;
        }

        /* Content Area */
        .admin-content {
            padding: 30px;
        }

        .page-title {
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .page-subtitle {
            color: #6c757d;
            font-size: 16px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.04);
            min-height: auto;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .stat-card:hover {
            text-decoration: none;
            color: inherit;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-bg);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            text-decoration: none;
            color: inherit;
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-icon.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-icon.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.danger { background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%); }
        .stat-icon.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0;
        }

        .stat-change {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .stat-change.positive {
            color: #059669;
            background: rgba(5, 150, 105, 0.1);
        }

        .stat-change.negative {
            color: #dc2626;
            background: rgba(220, 38, 38, 0.1);
        }

        .stat-change i {
            font-size: 10px;
        }

        /* Cards */
        .admin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            background: var(--light-bg);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: var(--dark-text);
        }

        .card-body {
            padding: 25px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            * {
                -webkit-transform: none !important;
                -webkit-backface-visibility: visible !important;
            }
            
            html, body {
                overflow-x: hidden !important;
                max-width: 100vw !important;
                width: 100vw !important;
            }
            
            :root {
                --sidebar-width: 0px;
            }
            
            .admin-sidebar {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                left: -280px !important;
                right: auto !important;
                width: 280px !important;
                height: 100vh !important;
                z-index: 9999 !important;
                transform: none !important;
                transition: left 0.3s ease !important;
                -webkit-transform: none !important;
                direction: ltr !important;
            }
            
            .admin-sidebar.show {
                left: 0 !important;
                box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3) !important;
            }
            
            .admin-main {
                margin-left: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                overflow-x: hidden !important;
            }
            
            .admin-main.expanded {
                margin-left: 0 !important;
                width: 100vw !important;
            }
            
            .admin-content {
                padding: 15px !important;
                max-width: 100% !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
                width: 100% !important;
            }
            
            .stats-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 15px !important;
                width: 100% !important;
            }
            
            .modern-stats-grid {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 15px !important;
                width: 100% !important;
                margin-bottom: 25px !important;
            }
            
            .stat-card,
            .modern-stat-card {
                min-height: 120px !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }
            
            .stat-value,
            .stat-number {
                font-size: 22px !important;
            }
            
            .header-search {
                display: none !important;
            }
            
            .profile-info {
                display: none !important;
            }
            
            .admin-header {
                padding: 0 15px !important;
            }
            
            .page-title h1 {
                font-size: 22px !important;
            }
            
            .page-subtitle {
                font-size: 14px !important;
            }
            
            .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            
            .col-lg-8, .col-lg-4, .col-lg-6 {
                padding-left: 0 !important;
                padding-right: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        
        /* Mobile Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9998;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        /* When sidebar is visible on mobile, shift overlay so it doesn't cover the sidebar */
        @media (max-width: 992px) {
            .mobile-overlay.with-sidebar {
                left: 280px; /* matches mobile sidebar width */
            }
        }
        
        .mobile-overlay.show {
            display: block;
            opacity: 1;
        }
        
        @media (min-width: 993px) {
            .mobile-overlay {
                display: none !important;
            }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Modern Widget Styles - FORCE DISPLAY */
        .modern-stats-grid {
            display: grid !important;
            grid-template-columns: 1fr !important; /* Mobile-first: single column */
            gap: 18px !important;
            margin-bottom: 25px !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            height: auto !important;
        }

        /* Responsive columns for stats grid */
        @media (min-width: 576px) {
            .modern-stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
        @media (min-width: 992px) {
            .modern-stats-grid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
        @media (min-width: 1200px) {
            .modern-stats-grid {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }

        .modern-stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 20px !important;
            padding: 1rem !important;
            position: relative !important;
            overflow: hidden !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            text-decoration: none !important;
            color: inherit !important;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15) !important;
            min-height: auto !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            height: auto !important;
        }

        .modern-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(233, 69, 96, 0.05) 0%, rgba(26, 26, 46, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .modern-stat-card:hover {
            transform: none !important;
            box-shadow: none !important;
            text-decoration: none !important;
            color: inherit !important;
        }

        .modern-stat-card:hover::before {
            opacity: 1;
        }

        .stat-card-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(233, 69, 96, 0.1) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modern-stat-card:hover .stat-card-bg {
            opacity: 1;
        }

        .stat-card-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .stat-icon-modern {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            font-size: 1.25rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            opacity: 0.8;
        }

        .icon-pulse {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            border-radius: 15px;
            transform: translate(-50%, -50%);
            animation: pulse 2s infinite;
            opacity: 0.3;
        }

        @keyframes pulse {
            0% {
                transform: translate(-50%, -50%) scale(1);
                opacity: 0.3;
            }
            50% {
                transform: translate(-50%, -50%) scale(1.1);
                opacity: 0.1;
            }
            100% {
                transform: translate(-50%, -50%) scale(1.2);
                opacity: 0;
            }
        }

        .stat-icon-modern.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon-modern.primary .icon-pulse {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-icon-modern.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon-modern.success .icon-pulse {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stat-icon-modern.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-icon-modern.info .icon-pulse {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-icon-modern.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-icon-modern.warning .icon-pulse {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .stat-trend.positive {
            color: #059669;
            background: rgba(5, 150, 105, 0.1);
            border-color: rgba(5, 150, 105, 0.2);
        }

        .stat-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-text);
            line-height: 1.2;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, #1a1a2e 0%, #e94560 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-title {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-text);
            margin-bottom: 2px;
        }

        .stat-subtitle {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 400;
        }

        .stat-footer {
            margin-top: 15px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            transition: width 1s ease-in-out;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .progress-fill.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .progress-fill.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .progress-fill.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        /* Pending Actions Grid */
        .pending-actions-grid {
            grid-column: 3 !important;
            grid-row: 1 / 3 !important;
            display: grid !important;
            gap: 12px !important;
            grid-template-rows: repeat(4, 75px) !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            height: 320px !important;
            max-height: 320px !important;
            align-content: start !important;
            overflow: visible !important;
            position: relative !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .compact-stat-card {
            background: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 15px !important;
            padding: 20px !important;
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            text-decoration: none !important;
            color: inherit !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 20px rgba(31, 38, 135, 0.1) !important;
            position: relative !important;
            overflow: hidden !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: 100% !important;
            height: auto !important;
            min-height: 80px !important;
        }

        .compact-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }

        .compact-stat-card:hover {
            transform: none !important;
            box-shadow: none !important;
            text-decoration: none !important;
            color: inherit !important;
        }

        .compact-stat-card:hover::before {
            width: 8px;
        }

        .compact-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
            flex-shrink: 0;
        }

        .compact-icon.danger {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
        }

        .compact-icon.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .compact-icon.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .compact-icon.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .compact-content {
            flex: 1;
        }

        .compact-number {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark-text);
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .compact-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }

        .compact-status {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 12px;
        }

        .compact-status.urgent {
            background: rgba(252, 70, 107, 0.1);
            color: #fc466b;
        }

        .compact-status.normal {
            background: rgba(17, 153, 142, 0.1);
            color: #11998e;
        }

        .active-count {
            font-size: 11px;
            font-weight: 600;
        }

        /* Responsive Design for Modern Widgets */
        @media (max-width: 1200px) {
            .modern-stats-grid {
                grid-template-columns: repeat(2, 1fr);
                grid-template-rows: repeat(3, auto);
            }

            .pending-actions-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                grid-template-rows: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 768px) {
            .modern-stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .pending-actions-grid {
                grid-column: 1;
                grid-row: auto;
                grid-template-columns: 1fr;
                grid-template-rows: repeat(4, auto);
            }

            .modern-stat-card {
                min-height: 150px;
                padding: 20px;
            }

            .stat-number {
                font-size: 28px;
            }

            .stat-icon-modern {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }

            .compact-stat-card {
                padding: 15px;
            }

            .compact-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .compact-number {
                font-size: 18px;
            }
        }

        @media (max-width: 576px) {
            .stat-header {
                flex-direction: column;
                gap: 10px;
            }

            .stat-trend {
                align-self: flex-start;
            }
        }

        /* Enhanced Dropdown Styling for Tables */
        .table .btn-group {
            position: relative;
        }

        .table .dropdown-toggle {
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table .dropdown-toggle:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .table .dropdown-toggle::after {
            transition: transform 0.3s ease;
        }

        .table .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .table .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            padding: 0.75rem 0;
            margin-top: 0.5rem;
            min-width: 200px;
            transform: translateY(-10px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1050;
        }

        .table .dropdown-menu.show {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }

        .table .dropdown-item {
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: #495057;
            border-radius: 8px;
            margin: 0 0.5rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .table .dropdown-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s;
        }

        .table .dropdown-item:hover::before {
            left: 100%;
        }

        .table .dropdown-item:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #212529;
            transform: translateX(4px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table .dropdown-item:active {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            transform: translateX(2px);
        }

        .table .dropdown-item i {
            width: 16px;
            margin-right: 0.75rem;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .table .dropdown-item:hover i {
            transform: scale(1.1);
        }

        /* Color-coded dropdown items */
        .table .dropdown-item.text-success:hover {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .table .dropdown-item.text-warning:hover {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
        }

        .table .dropdown-item.text-danger:hover {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }

        .table .dropdown-item.text-info:hover {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }

        .table .dropdown-divider {
            margin: 0.5rem 0.5rem;
            border-top: 1px solid #e9ecef;
            opacity: 0.6;
        }

        /* Button group enhancements */
        .table .btn-group .btn {
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .table .btn-group .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.3s ease, height 0.3s ease;
        }

        .table .btn-group .btn:hover::before {
            width: 120%;
            height: 120%;
        }

        .table .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
        }

        .table .btn-outline-primary:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .table .btn-outline-secondary:hover {
            background: #6c757d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        /* Mobile responsive dropdown */
        @media (max-width: 768px) {
            .table .dropdown-menu {
                position: fixed !important;
                top: auto !important;
                left: 50% !important;
                transform: translateX(-50%) translateY(-10px) !important;
                bottom: 100px;
                min-width: 280px;
                max-width: 90vw;
            }

            .table .dropdown-menu.show {
                transform: translateX(-50%) translateY(0) !important;
            }

            .table .dropdown-item {
                padding: 1rem 1.25rem;
                font-size: 1rem;
                margin: 0 0.75rem;
            }

            .table .dropdown-item i {
                margin-right: 1rem;
            }
        }

        /* Dropdown backdrop for mobile */
        .dropdown-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .dropdown-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Animation keyframes */
        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes dropdownSlideOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
        }

        .table .dropdown-menu.animating-in {
            animation: dropdownSlideIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .table .dropdown-menu.animating-out {
            animation: dropdownSlideOut 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Focus states for accessibility */
        .table .dropdown-toggle:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
        }

        .table .dropdown-item:focus {
            outline: none;
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            color: #212529;
        }

        /* Loading state for dropdown items */
        .table .dropdown-item.loading {
            pointer-events: none;
            opacity: 0.6;
            position: relative;
        }

        .table .dropdown-item.loading::after {
            content: '';
            position: absolute;
            right: 1.25rem;
            top: 50%;
            width: 12px;
            height: 12px;
            border: 2px solid #6c757d;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            transform: translateY(-50%);
        }

        @keyframes spin {
            to {
                transform: translateY(-50%) rotate(360deg);
            }
        }

        /* ULTIMATE MODAL FIX - NUCLEAR APPROACH */
        
        /* Reset all modal z-indexes to extreme values */
        .modal {
            z-index: 999999 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overflow: hidden !important;
            outline: 0 !important;
        }
        
        .modal.fade {
            z-index: 999999 !important;
            opacity: 0 !important;
            transition: opacity 0.15s linear !important;
        }
        
        .modal.show {
            z-index: 999999 !important;
            opacity: 1 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .modal-backdrop {
            z-index: 999998 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            pointer-events: none !important; /* Don't block clicks - allow clicks through backdrop */
        }
        
        .modal-backdrop.fade {
            opacity: 0 !important;
            pointer-events: none !important;
        }
        
        .modal-backdrop.show {
            opacity: 0.5 !important;
            pointer-events: none !important; /* Don't block clicks even when shown */
        }
        
        .modal-dialog {
            z-index: 1000000 !important;
            position: relative !important;
            width: auto !important;
            margin: 0.5rem !important;
            pointer-events: none !important;
        }
        
        .modal-content {
            z-index: 1000001 !important;
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            pointer-events: auto !important;
            background-color: #fff !important;
            background-clip: padding-box !important;
            border: 1px solid rgba(0, 0, 0, 0.2) !important;
            border-radius: 15px !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            outline: 0 !important;
        }
        
        .modal-header {
            z-index: 1000002 !important;
            position: relative !important;
            background: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
            border-radius: 15px 15px 0 0 !important;
            padding: 1rem !important;
        }
        
        .modal-body {
            z-index: 1000002 !important;
            position: relative !important;
            background: #ffffff !important;
            padding: 1.5rem !important;
            flex: 1 1 auto !important;
        }
        
        .modal-footer {
            z-index: 1000002 !important;
            position: relative !important;
            background: #f8f9fa !important;
            border-top: 1px solid #dee2e6 !important;
            border-radius: 0 0 15px 15px !important;
            padding: 1rem !important;
        }
        
        /* Force specific modal dialogs to be centered */
        .modal.show .modal-dialog {
            transform: none !important;
            margin: auto !important;
        }
        
        /* Override any conflicting styles */
        .modal * {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Ensure form elements in modals are visible */
        .modal .form-control,
        .modal .form-select,
        .modal .form-check-input,
        .modal .btn {
            opacity: 1 !important;
            visibility: visible !important;
            background: white !important;
            color: #212529 !important;
        }
        
        /* Make sure text is visible */
        .modal .modal-title,
        .modal .modal-body,
        .modal .modal-footer,
        .modal label,
        .modal p,
        .modal span {
            color: #212529 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Ensure buttons work */
        .modal .btn {
            position: relative !important;
            z-index: 1000003 !important;
            pointer-events: auto !important;
        }
        
        /* Remove any transforms that might hide content */
        .modal-dialog {
            transform: none !important;
        }
        
        .modal.fade .modal-dialog {
            transform: translateY(-50px) !important;
            transition: transform 0.3s ease-out !important;
        }
        
        .modal.show .modal-dialog {
            transform: none !important;
        }
        
        /* Ensure admin sidebar doesn't interfere */
        .admin-sidebar {
            z-index: 1050 !important;
        }
        
        .admin-main {
            z-index: 1 !important;
        }
        
        .admin-header {
            z-index: 999 !important;
        }
        
        /* Force dropdown menus to stay below modals */
        .dropdown-menu {
            z-index: 1055 !important;
        }
        
        /* Admin Notification Bell Styles */
        .admin-notification-btn {
            background: rgba(108, 117, 125, 0.1);
            border: 1px solid rgba(108, 117, 125, 0.2);
            transition: all 0.3s ease;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-notification-btn:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .admin-notification-btn:hover i {
            color: white !important;
        }

        .admin-notification-badge {
            font-size: 0.6rem;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: admin-pulse 2s infinite;
        }

        @keyframes admin-pulse {
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

        /* Notification Dropdown Styling */
        .notification-dropdown {
            width: 350px !important;
            max-height: 400px !important;
            overflow-y: auto !important;
            z-index: 1060 !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12) !important;
            border-radius: 12px !important;
            border: none !important;
            backdrop-filter: blur(10px) !important;
            background: rgba(255, 255, 255, 0.95) !important;
            padding: 0 !important;
            margin-top: 6px !important;
        }
        
        .notification-dropdown .dropdown-header {
            padding: 12px 18px !important;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            color: white !important;
            font-weight: 600 !important;
            border-radius: 12px 12px 0 0 !important;
            margin: 0 !important;
            border: none !important;
        }
        
        .notification-dropdown .dropdown-header h6 {
            margin: 0 !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            color: white !important;
        }
        
        .notification-list {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .notification-list .notification-item {
            padding: 12px 18px !important;
            border-radius: 0 !important;
            margin: 0 !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
            display: flex !important;
            align-items: flex-start !important;
            color: inherit !important;
            gap: 12px !important;
        }
        
        .notification-list .notification-item:hover {
            background: linear-gradient(90deg, rgba(26, 26, 46, 0.02) 0%, rgba(26, 26, 46, 0.05) 100%) !important;
            text-decoration: none !important;
            transform: translateX(2px) !important;
            color: inherit !important;
        }
        
        .notification-list .notification-item:last-child {
            border-bottom: none !important;
        }
        
        .notification-icon-wrapper {
            flex-shrink: 0 !important;
            margin-right: 0 !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 8px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: rgba(0, 123, 255, 0.1) !important;
        }
        
        .notification-content {
            flex: 1 !important;
            min-width: 0 !important;
        }
        
        .notification-title {
            font-size: 13px !important;
            line-height: 1.3 !important;
            margin-bottom: 3px !important;
            font-weight: 600 !important;
            color: #2c3e50 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
        
        .notification-message {
            font-size: 12px !important;
            line-height: 1.3 !important;
            margin-bottom: 4px !important;
            color: #6c757d !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
        }
        
        .notification-time {
            font-size: 10px !important;
            color: #95a5a6 !important;
            font-weight: 500 !important;
        }
        
        .notification-badge-wrapper {
            flex-shrink: 0 !important;
            margin-left: 8px !important;
            align-self: flex-start !important;
        }
        
        .notification-badge-wrapper .badge {
            font-size: 10px !important;
            font-weight: 600 !important;
            min-width: 20px !important;
            height: 20px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 10px !important;
        }
        
        .dropdown-footer {
            padding: 12px 20px !important;
            background: #f8f9fa !important;
            border-radius: 0 0 15px 15px !important;
            border-top: 1px solid rgba(0, 0, 0, 0.05) !important;
            margin: 0 !important;
        }
        
        .dropdown-footer .dropdown-item {
            padding: 8px 0 !important;
            margin: 0 !important;
            text-align: center !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            color: var(--primary-color) !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }
        
        .dropdown-footer .dropdown-item:hover {
            background: rgba(26, 26, 46, 0.1) !important;
            color: var(--primary-color) !important;
            text-decoration: none !important;
        }
        
        /* Empty state styling */
        .notification-list .text-center {
            padding: 40px 20px !important;
        }
        
        .notification-list .text-center i {
            font-size: 3rem !important;
            opacity: 0.3 !important;
            margin-bottom: 12px !important;
            color: #95a5a6 !important;
        }
        
        .notification-list .text-center p {
            color: #95a5a6 !important;
            font-size: 14px !important;
            margin: 0 !important;
        }
        
        /* Loading state */
        .notification-list .spinner-border-sm {
            width: 1.5rem !important;
            height: 1.5rem !important;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .notification-dropdown {
                width: 340px !important;
                max-width: 95vw !important;
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) !important;
            }
        }
        
        @media (max-width: 480px) {
            .notification-dropdown {
                width: 300px !important;
            }
            
            .notification-list .notification-item {
                padding: 12px 16px !important;
            }
            
            .notification-title {
                font-size: 13px !important;
            }
            
            .notification-message {
                font-size: 12px !important;
            }
        }
        
        /* Notification badge in header */
        .notification-badge {
            font-size: 10px !important;
            min-width: 18px !important;
            height: 18px !important;
            animation: pulse-notification 2s infinite !important;
        }
        
        @keyframes pulse-notification {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 4px rgba(220, 53, 69, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }
        
        /* Fix for any nested modals or dropdowns within modals */
        .modal .dropdown-menu {
            z-index: 10010 !important;
        }
        
        /* Ensure modal appears on top of any overlay or backdrop */
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .modal.show .modal-dialog {
            margin: auto !important;
        }

        .confirmation-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            animation: pulse-warning 2s infinite;
        }

        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            animation: pulse-success 2s infinite;
        }

        .notification-icon.error {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            animation: pulse-error 2s infinite;
        }

        .notification-icon.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            animation: pulse-warning 2s infinite;
        }

        @keyframes pulse-success {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(17, 153, 142, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(17, 153, 142, 0); }
        }

        @keyframes pulse-error {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(252, 70, 107, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(252, 70, 107, 0); }
        }

        @keyframes pulse-warning {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(240, 147, 251, 0.7); }
            50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(240, 147, 251, 0); }
        }

        .modal-title {
            font-weight: 600;
            color: var(--dark-text);
        }

        .modal-footer .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .modal-footer .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 3px;
        }

        /* Modal Animation */
        .modal.fade .modal-dialog {
            transition: transform 0.4s ease-out;
            transform: translate(0, -50px) scale(0.9);
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0) scale(1);
        }
    </style>
    
    @stack('styles')
    
    <!-- Modern UI System -->
    @include('admin.shared.modern-ui')
</head>
<body>
    <!-- Sidebar -->
    <div class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-logo">
                @if(isset($site_settings) && ($site_settings['site_logo_dark'] ?? false))
                    <img src="{{ asset($site_settings['site_logo_dark']) }}" alt="{{ getAppName() }}" class="logo-img">
                @elseif(isset($site_settings) && ($site_settings['site_logo'] ?? false))
                    <img src="{{ asset($site_settings['site_logo']) }}" alt="{{ getAppName() }}" class="logo-img">
                @else
                    <i class="fas fa-wave-square"></i>
                    <span class="logo-text">{{ getAppName() }} Admin</span>
                @endif
            </a>
        </div>

        <div class="sidebar-menu">
            @php
                // Get ordered and visible menu items for current user
                $menuItems = getSidebarMenuItems('admin');
                $isAdmin = auth()->user()->is_admin ?? false;
            @endphp
            
            {{-- Render ordered menu items dynamically --}}
            @foreach($menuItems as $item)
                @include('partials.admin-menu-item', ['item' => $item])
            @endforeach

            {{-- Always visible items at the bottom (not part of role-based ordering) --}}
            <!-- System Information -->
            <div class="menu-item">
                <a href="{{ route('admin.settings.system-info') }}" class="menu-link {{ request()->routeIs('admin.settings.system-info') ? 'active' : '' }}">
                    <i class="menu-icon fas fa-info-circle"></i>
                    <span class="menu-text">System Information</span>
                </a>
            </div>

            <!-- Developer Tools -->
            <div class="menu-item">
                <div class="dropdown">
                    <a href="#" class="menu-link dropdown-toggle {{ request()->routeIs('admin.tools.*') ? 'active' : '' }}" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                        <i class="menu-icon fas fa-tools"></i>
                        <span class="menu-text">Developer Tools</span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item {{ request()->routeIs('admin.tools.email-template-seeder') ? 'active' : '' }}" href="{{ route('admin.tools.email-template-seeder') }}">Email Template Seeder</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="admin-main" id="adminMain">
        <!-- Header -->
        <header class="admin-header">
            <div class="header-left">
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            </div>

            <div class="header-right">
                <div class="header-search">
                    <input type="text" placeholder="Search..." class="form-control">
                    <i class="fas fa-search search-icon"></i>
                </div>

                {{-- Professional Notification Bell --}}
                <div class="header-notifications position-relative me-3">
                    <button class="notification-btn btn border-0 p-2 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fs-5 text-secondary"></i>
                        <span id="adminNotificationCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge-count" style="display: none; font-size: 0.65rem; min-width: 18px; height: 18px; line-height: 18px; padding: 0 6px;">
                            0
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown shadow-lg border-0">
                        <div class="dropdown-header">
                            <h6 class="mb-0"><i class="fas fa-bell me-2"></i>Notifications</h6>
                        </div>
                        <div id="adminNotificationList" class="notification-list">
                            <div class="text-center py-4">
                                <div class="spinner-border spinner-border-sm mb-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mb-0 small text-muted">Loading notifications...</p>
                            </div>
                        </div>
                        <div class="dropdown-footer">
                            <a class="dropdown-item text-center py-2" href="{{ route('admin.notifications.index') }}">
                                <i class="fas fa-eye me-2"></i>View All Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <div class="admin-profile" data-bs-toggle="dropdown">
                        @if(auth()->user() && auth()->user()->avatar)
                            <img src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}" alt="Admin" class="profile-avatar">
                        @else
                            <img src="{{ auth()->user() ? auth()->user()->avatar_url : asset('assets/images/default-avatar.png') }}" alt="Admin" class="profile-avatar">
                        @endif
                        <div class="profile-info">
                            <div class="profile-name">{{ auth()->user() ? auth()->user()->name : 'Admin' }}</div>
                            <div class="profile-role">{{ auth()->user() ? ucfirst(auth()->user()->role ?? 'Administrator') : 'Administrator' }}</div>
                        </div>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="admin-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

{{-- Context-aware routing helpers are now globally available via RouteHelper.php --}}
@php
    // Set route prefix variable for backward compatibility
    $routePrefix = getContextPrefix();
@endphp

@yield('content')
        </main>
    </div>

    <!-- Confirmation Modals -->
    <div id="confirmationModal" class="modal fade" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center">
                        <div id="confirmIcon" class="confirmation-icon me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p id="confirmationMessage" class="mb-0">Are you sure you want to perform this action?</p>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" id="confirmButton" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Notification Modal -->
    <div id="notificationModal" class="modal fade" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center w-100 justify-content-center">
                        <div id="notificationIcon" class="notification-icon me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h5 class="modal-title" id="notificationModalLabel">Success</h5>
                    </div>
                </div>
                <div class="modal-body text-center pt-2">
                    <p id="notificationMessage" class="mb-0">Operation completed successfully!</p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-check me-2"></i>OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-4">
                    <div class="loading-spinner mb-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <p id="loadingMessage" class="mb-0">Processing...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Sidebar toggle for mobile and desktop
            $('#sidebarToggle').on('click', function() {
                if (window.innerWidth <= 992) {
                    // Mobile: show/hide sidebar with overlay
                    const isShowing = $('#adminSidebar').hasClass('show');
                    
                    if (isShowing) {
                        // Close sidebar
                        $('#adminSidebar').removeClass('show');
                        $('.mobile-overlay').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        // Open sidebar - create overlay behind sidebar
                        if ($('.mobile-overlay').length === 0) {
                            $('<div class="mobile-overlay with-sidebar"></div>').appendTo('body').hide().fadeIn(300);
                        } else {
                            $('.mobile-overlay').addClass('with-sidebar');
                        }
                        setTimeout(() => {
                            $('#adminSidebar').addClass('show');
                        }, 50);
                    }
                } else {
                    // Desktop: collapse/expand sidebar
                    $('#adminSidebar').toggleClass('collapsed');
                    $('#adminMain').toggleClass('expanded');
                }
            });
            
            // Close sidebar when clicking overlay
            $(document).on('click', '.mobile-overlay', function() {
                $('#adminSidebar').removeClass('show');
                $(this).fadeOut(300, function() {
                    $(this).remove();
                });
            });
            
            // Close sidebar when clicking a link on mobile (after small delay to allow navigation)
            if (window.innerWidth <= 992) {
                $('.sidebar-menu .menu-link:not(.dropdown-toggle), .sidebar-menu .dropdown-item').off('click.mobile').on('click.mobile', function(e) {
                    // Don't interfere with dropdown toggles
                    if ($(this).hasClass('dropdown-toggle')) {
                        return;
                    }
                    // Ensure navigation occurs even if other handlers run
                    const href = this.getAttribute('href');
                    if (href && href !== '#') {
                        setTimeout(() => { window.location.href = href; }, 10);
                    }
                    // Defer sidebar close slightly so the browser can follow the link
                    setTimeout(function() {
                        $('#adminSidebar').removeClass('show');
                        $('.mobile-overlay').fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 200);
                });
            }

            // Initialize Bootstrap dropdowns for sidebar
            // Explicitly initialize all dropdown toggles in sidebar
            document.querySelectorAll('.sidebar-menu .dropdown-toggle').forEach(function(toggle) {
                // Initialize Bootstrap dropdown if not already initialized
                if (!bootstrap.Dropdown.getInstance(toggle)) {
                    new bootstrap.Dropdown(toggle, {
                        boundary: 'viewport',
                        popperConfig: {
                            modifiers: [
                                {
                                    name: 'preventOverflow',
                                    options: {
                                        boundary: document.querySelector('.admin-sidebar')
                                    }
                                }
                            ]
                        }
                    });
                }
            });

            // Close other sidebar dropdowns when one opens
            document.addEventListener('show.bs.dropdown', function(e) {
                const currentDropdown = e.target.closest('.sidebar-menu .dropdown');
                if (currentDropdown) {
                    document.querySelectorAll('.sidebar-menu .dropdown').forEach(function(dropdown) {
                        if (dropdown !== currentDropdown) {
                            const toggle = dropdown.querySelector('.dropdown-toggle');
                            if (toggle) {
                                const instance = bootstrap.Dropdown.getInstance(toggle);
                                if (instance) {
                                    instance.hide();
                                }
                            }
                        }
                    });
                }
            });

            // Auto-hide alerts
            $('.alert').delay(30000).fadeOut();

            // Initialize DataTables
            if ($('.data-table').length > 0) {
                $('.data-table').DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: {
                        search: "Search:",
                        lengthMenu: "Show _MENU_ entries",
                    }
                });
            }

            // Smooth scrolling
            $('a[href^="#"]').on('click', function(event) {
                var target = $(this.getAttribute('href'));
                if (target.length) {
                    event.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });
        });

        // Real-time notifications (you can implement WebSocket here)
        function updateNotifications() {
            // Fetch notifications via AJAX
            // Update notification badge and dropdown
        }

        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            easing: 'ease-in-out-back',
            once: true,
            offset: 50
        });

        // Enhanced Dropdown Functionality - Light touch to work with Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading state to dropdown items when clicked
            document.addEventListener('click', function(e) {
                if (e.target.closest('.table .dropdown-item') && e.target.closest('.table .dropdown-item').getAttribute('onclick')) {
                    const item = e.target.closest('.table .dropdown-item');
                    item.classList.add('loading');
                    setTimeout(() => item.classList.remove('loading'), 2000);
                }
            });
        });
    </script>

    <!-- Global Date Input Handler for Search Filters -->
    <script>
    $(document).ready(function() {
        // Date input mask for dd-mm-yyyy format in all search/filter forms
        $(document).on('input', 'input[pattern="\\d{2}-\\d{2}-\\d{4}"]', function() {
            let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
            if (value.length >= 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 9);
            }
            $(this).val(value);
        });

        // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission (for search forms)
        $(document).on('submit', 'form', function() {
            $(this).find('input[pattern="\\d{2}-\\d{2}-\\d{4}"]').each(function() {
                const dateStr = $(this).val();
                if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                    const parts = dateStr.split('-');
                    const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
                    $(this).val(yyyyMmDd);
                }
            });
        });
    });
    </script>

    @stack('scripts')
    
    <!-- Beautiful Modal System -->
    <script>
        // Global Modal System
        window.ModalSystem = {
            // Beautiful confirmation modal
            confirm: function(options = {}) {
                const defaults = {
                    title: 'Confirm Action',
                    message: 'Are you sure you want to perform this action?',
                    confirmText: 'Confirm',
                    cancelText: 'Cancel',
                    icon: 'fas fa-exclamation-triangle',
                    confirmClass: 'btn-primary',
                    onConfirm: null,
                    onCancel: null
                };
                
                const config = Object.assign(defaults, options);
                
                return new Promise((resolve, reject) => {
                    const modal = document.getElementById('confirmationModal');
                    const title = modal.querySelector('#confirmationModalLabel');
                    const message = modal.querySelector('#confirmationMessage');
                    const icon = modal.querySelector('#confirmIcon i');
                    const confirmBtn = modal.querySelector('#confirmButton');
                    
                    // Set content
                    title.textContent = config.title;
                    message.textContent = config.message;
                    icon.className = config.icon;
                    confirmBtn.innerHTML = `<i class="fas fa-check me-2"></i>${config.confirmText}`;
                    confirmBtn.className = `btn ${config.confirmClass}`;
                    
                    // Handle confirm button
                    const handleConfirm = () => {
                        if (config.onConfirm) config.onConfirm();
                        bootstrap.Modal.getInstance(modal).hide();
                        resolve(true);
                        cleanup();
                    };
                    
                    // Handle cancel
                    const handleCancel = () => {
                        if (config.onCancel) config.onCancel();
                        bootstrap.Modal.getInstance(modal).hide();
                        resolve(false);
                        cleanup();
                    };
                    
                    // Cleanup function
                    const cleanup = () => {
                        confirmBtn.removeEventListener('click', handleConfirm);
                        modal.removeEventListener('hidden.bs.modal', handleCancel);
                    };
                    
                    // Add event listeners
                    confirmBtn.addEventListener('click', handleConfirm);
                    modal.addEventListener('hidden.bs.modal', handleCancel, { once: true });
                    
                    // Show modal
                    new bootstrap.Modal(modal).show();
                });
            },
            
            // Beautiful notification modal
            notify: function(options = {}) {
                const defaults = {
                    title: 'Notification',
                    message: 'Operation completed successfully!',
                    type: 'success', // success, error, warning, info
                    duration: 3000,
                    onClose: null
                };
                
                const config = Object.assign(defaults, options);
                
                const modal = document.getElementById('notificationModal');
                const title = modal.querySelector('#notificationModalLabel');
                const message = modal.querySelector('#notificationMessage');
                const icon = modal.querySelector('#notificationIcon i');
                const iconDiv = modal.querySelector('#notificationIcon');
                
                // Configure based on type
                const typeConfig = {
                    success: {
                        icon: 'fas fa-check-circle',
                        title: config.title || 'Success',
                        class: ''
                    },
                    error: {
                        icon: 'fas fa-times-circle',
                        title: config.title || 'Error',
                        class: 'error'
                    },
                    warning: {
                        icon: 'fas fa-exclamation-triangle',
                        title: config.title || 'Warning',
                        class: 'warning'
                    },
                    info: {
                        icon: 'fas fa-info-circle',
                        title: config.title || 'Information',
                        class: 'info'
                    }
                };
                
                const currentType = typeConfig[config.type] || typeConfig.success;
                
                // Set content
                title.textContent = currentType.title;
                message.textContent = config.message;
                icon.className = currentType.icon;
                iconDiv.className = `notification-icon me-3 ${currentType.class}`;
                
                // Show modal
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                
                // Auto hide if duration is set
                if (config.duration > 0) {
                    setTimeout(() => {
                        modalInstance.hide();
                        if (config.onClose) config.onClose();
                    }, config.duration);
                }
            },
            
            // Loading modal
            loading: function(message = 'Processing...') {
                const modal = document.getElementById('loadingModal');
                const messageEl = modal.querySelector('#loadingMessage');
                messageEl.textContent = message;
                
                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
                
                return {
                    hide: () => modalInstance.hide(),
                    updateMessage: (newMessage) => {
                        messageEl.textContent = newMessage;
                    }
                };
            }
        };
        
        // Global helper functions that replace alert() and confirm()
        window.showConfirm = function(message, onConfirm, options = {}) {
            return ModalSystem.confirm({
                message: message,
                onConfirm: onConfirm,
                ...options
            });
        };
        
        window.showNotification = function(message, type = 'success', title = null) {
            return ModalSystem.notify({
                message: message,
                type: type,
                title: title
            });
        };
        
        window.showLoading = function(message = 'Processing...') {
            return ModalSystem.loading(message);
        };
        
        // Enhanced confirm function that returns promise
        window.confirmAction = async function(message, options = {}) {
            return await ModalSystem.confirm({
                message: message,
                ...options
            });
        };
        
        // Override browser's alert and confirm functions
        window.originalAlert = window.alert;
        window.originalConfirm = window.confirm;
        
        window.alert = function(message) {
            showNotification(message, 'info', 'Alert');
        };
        
        window.confirm = function(message) {
            return confirmAction(message);
        };
        
        // Form confirmation helper
        window.confirmFormSubmission = function(form, message = 'Are you sure you want to submit this form?') {
            confirmAction(message).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        };
        
        // AJAX helper with modal integration
        window.ajaxWithModal = function(options = {}) {
            const defaults = {
                method: 'POST',
                loadingMessage: 'Processing...',
                successMessage: 'Operation completed successfully!',
                errorMessage: 'An error occurred. Please try again.',
                showLoading: true,
                showSuccess: true,
                showError: true,
                onSuccess: null,
                onError: null
            };
            
            const config = Object.assign(defaults, options);
            let loader = null;
            
            if (config.showLoading) {
                loader = showLoading(config.loadingMessage);
            }
            
            return $.ajax({
                url: config.url,
                method: config.method,
                data: config.data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/json'
                },
                dataType: 'json',
                success: function(response) {
                    if (loader) loader.hide();
                    
                    if (config.showSuccess) {
                        showNotification(config.successMessage, 'success');
                    }
                    
                    if (config.onSuccess) {
                        config.onSuccess(response);
                    }
                },
                error: function(xhr, status, error) {
                    if (loader) loader.hide();
                    
                    let errorMessage = config.errorMessage;
                    
                    // Try to get error message from response
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                        } catch (e) {
                            // Use default error message
                        }
                    }
                    
                    if (config.showError) {
                        showNotification(errorMessage, 'error');
                    }
                    
                    if (config.onError) {
                        config.onError(xhr, status, error);
                    }
                }
            });
        };
        
        // EMERGENCY MODAL FIX - FORCE INITIALIZATION
        document.addEventListener('DOMContentLoaded', function() {
            // Force remove any conflicting modal CSS
            const style = document.createElement('style');
            style.textContent = `
                .modal { display: none !important; }
                .modal.show { display: flex !important; }
                .modal * { visibility: visible !important; opacity: 1 !important; }
                .modal-backdrop { opacity: 0.5 !important; }
            `;
            document.head.appendChild(style);
            
            // Force modal functionality
            const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
            modalTriggers.forEach(function(trigger) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-bs-target');
                    const modal = document.querySelector(targetId);
                    
                    if (modal) {
                        // Force show modal
                        modal.style.display = 'flex';
                        modal.style.alignItems = 'center';
                        modal.style.justifyContent = 'center';
                        modal.classList.add('show');
                        
                        // Create backdrop
                        const backdrop = document.createElement('div');
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.style.zIndex = '999998';
                        document.body.appendChild(backdrop);
                        
                        // Add close functionality
                        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
                        closeButtons.forEach(function(btn) {
                            btn.addEventListener('click', function() {
                                modal.style.display = 'none';
                                modal.classList.remove('show');
                                backdrop.remove();
                            });
                        });
                        
                        // Close on backdrop click
                        backdrop.addEventListener('click', function() {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            backdrop.remove();
                        });
                    }
                });
            });
        });
        
        // Document ready functions
        $(document).ready(function() {
            // Auto-convert existing onclick confirmations
            $('[onclick*="confirm("]').each(function() {
                const element = this;
                const originalOnclick = element.getAttribute('onclick');
                
                // Extract confirm message
                const confirmMatch = originalOnclick.match(/confirm\(['"](.*?)['"]/); 
                const confirmMessage = confirmMatch ? confirmMatch[1] : 'Are you sure?';
                
                // Extract the action after confirm
                const actionMatch = originalOnclick.match(/confirm\([^)]+\)\s*&&\s*(.+)/);
                if (actionMatch) {
                    const action = actionMatch[1].trim();
                    
                    // SECURITY: Instead of using eval(), store the original onclick
                    // and restore it temporarily, or parse for specific patterns
                    // Whitelist of allowed function calls (safer than eval)
                    const allowedFunctions = [
                        'deletePatient', 'deleteDoctor', 'deleteUser', 'deleteAppointment',
                        'deleteDepartment', 'deleteRecord', 'deletePrescription',
                        'confirmAppointment', 'confirmDelete', 'confirmRestore',
                        'confirmPayment', 'confirmBlockIP', 'toggleStatus',
                        'viewDoctors', 'bulkConfirm', 'previewTemplate'
                    ];
                    
                    // Check if action matches whitelist pattern (function call with optional parameters)
                    const functionMatch = action.match(/^(\w+)\(([^)]*)\)/);
                    
                    if (functionMatch && allowedFunctions.includes(functionMatch[1])) {
                        // Store action in data attribute instead of using eval
                        element.setAttribute('data-action', action);
                        
                        // Remove original onclick
                        element.removeAttribute('onclick');
                        
                        // Add new click handler
                        element.addEventListener('click', function(e) {
                            e.preventDefault();
                            confirmAction(confirmMessage).then(confirmed => {
                                if (confirmed) {
                                    // Execute the function from whitelist
                                    try {
                                        const fnMatch = element.getAttribute('data-action').match(/^(\w+)\(([^)]*)\)/);
                                        if (fnMatch && allowedFunctions.includes(fnMatch[1])) {
                                            // Parse parameters safely
                                            const params = fnMatch[2].split(',').map(p => p.trim().replace(/['"]/g, ''));
                                            // Execute function if it exists
                                            if (typeof window[fnMatch[1]] === 'function') {
                                                window[fnMatch[1]].apply(null, params);
                                            }
                                        }
                                    } catch (error) {
                                        console.error('Error executing action:', error);
                                    }
                                }
                            });
                        });
                    } else {
                        // If not in whitelist, log warning but keep original behavior
                        console.warn('Unsafe onclick handler detected, keeping original:', action);
                    }
                }
            });
            
            // Enhanced form submission with confirmation
            $('form[data-confirm]').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                const message = form.getAttribute('data-confirm');
                
                confirmAction(message).then(confirmed => {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
            
            // Load admin notifications on page load
            console.log('🚀 Document ready - initializing admin notifications');
            loadAdminNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadAdminNotifications, 30000);
        });
        
        // Admin notification functions
        function loadAdminNotifications() {
            const url = '{{ route("admin.notifications.admin") }}';
            
            $.get(url)
                .done(function(response) {
                    console.log('✅ Notifications loaded:', response);
                    updateAdminNotificationBadge(response.total_count);
                    updateAdminNotificationList(response.notifications);
                })
                .fail(function(xhr, status, error) {
                    console.error('❌ Failed to load admin notifications:', error);
                });
        }
        
        function updateAdminNotificationBadge(count) {
            const badge = $('#adminNotificationCount');
            console.log('🏷️ Badge update - Count:', count, 'Element:', badge.length);

            if (count > 0) {
                const displayCount = count > 99 ? '99+' : count;
                badge.text(displayCount)
                     .addClass('show')
                     .css('display', 'flex');
                console.log('📍 Badge shown with count:', displayCount);
            } else {
                badge.removeClass('show')
                     .css('display', 'none');
                console.log('🔸 Badge hidden (count is 0)');
            }
        }
        
        function updateAdminNotificationList(notifications) {
            const container = $('#adminNotificationList');
            
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
                
                const badgeClass = notification.type === 'warning' ? 'warning' : 
                                 notification.type === 'info' ? 'info' : 
                                 notification.type === 'success' ? 'success' : 'primary';
                
                html += `
                    <a class="notification-item" href="${notification.url || '#'}">
                        <div class="notification-icon-wrapper">
                            <i class="${iconClass} ${colorClass}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">
                                ${notification.title}
                            </div>
                            <div class="notification-message">
                                ${notification.message}
                            </div>
                            <div class="notification-time">
                                <i class="fas fa-clock me-1"></i>${notification.created_at}
                            </div>
                        </div>
                        <div class="notification-badge-wrapper">
                            <span class="badge bg-${badgeClass}">
                                ${notification.count}
                            </span>
                        </div>
                    </a>
                `;
            });
            
            container.html(html);
        }
    </script>
    
    {{-- Include Custom Modals --}}
    @include('partials.custom-modals')
    
    {{-- Include Admin Confirmation Modal --}}
    @include('admin.partials.confirmation-modal')
    
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

