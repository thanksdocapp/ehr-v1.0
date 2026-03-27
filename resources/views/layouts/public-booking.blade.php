<!DOCTYPE html>
@php $embed = $embed ?? request()->boolean('embed') || session('embed', false); @endphp
<html lang="en-GB">
<head>
    @include('partials.gtm-head')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" id="booking-viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ $site_settings['hospital_name'] ?? getAppName() }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Dynamic Theme CSS - Uses admin appearance settings -->
    <link rel="stylesheet" href="{{ route('theme.css') }}?v={{ time() }}">

    <style>
        :root {
            /* Use admin theme colors with fallbacks */
            --booking-primary: var(--primary-color, #2563eb);
            --booking-primary-hover: var(--button-hover-primary, #1d4ed8);
            --booking-success: var(--success-color, #10b981);
            --booking-success-hover: var(--button-hover-success, #059669);
            --booking-secondary: var(--secondary-color, #6c757d);
            --booking-secondary-hover: var(--button-hover-secondary, #5a6268);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #1a202c;
            line-height: 1.6;
        }
        /* Touch scroll in iframe (iOS): momentum scrolling and allow vertical pan */
        html { -webkit-overflow-scrolling: touch; }
        body { overflow-y: auto; -webkit-overflow-scrolling: touch; touch-action: pan-y; }

        .booking-container {
            max-width: @yield('container-width', '1200px');
            margin: 0 auto;
            padding: 1rem 0.75rem;
        }
        @media (min-width: 768px) {
            .booking-container { padding: 2rem 1rem; }
        }

        .booking-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .booking-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .booking-header p {
            font-size: 1rem;
            color: #718096;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 3rem;
            gap: 1rem;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 0 0 auto;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #718096;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }

        .step.active .step-circle {
            background-color: var(--booking-primary);
            color: #ffffff;
        }

        .step.completed .step-circle {
            background-color: var(--booking-success);
            color: #ffffff;
        }

        .step-label {
            font-size: 0.75rem;
            color: #718096;
            font-weight: 500;
        }

        .step.active .step-label {
            color: var(--booking-primary);
            font-weight: 600;
        }

        .step-line {
            width: 60px;
            height: 2px;
            background-color: #e2e8f0;
            margin: 0 0.5rem;
            margin-top: -25px;
        }

        .step-line.completed {
            background-color: var(--booking-success);
        }

        /* Cards */
        .info-card, .summary-card, .form-card, .review-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-card, .review-card {
            padding: 2rem;
        }

        .info-card h3, .summary-card h4, .review-card-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .info-card p, .summary-card p {
            color: #718096;
            margin: 0;
        }

        /* Selection Cards (Services, Doctors) */
        .services-grid, .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .service-card, .doctor-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .service-card:hover, .doctor-card:hover {
            border-color: var(--booking-primary);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }

        .service-card.selected, .doctor-card.selected {
            border-color: var(--booking-primary);
            background-color: color-mix(in srgb, var(--booking-primary) 10%, white);
        }

        .service-radio, .doctor-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        /* Time Slots */
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .time-slot-btn {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            color: #1a202c;
        }

        .time-slot-btn:hover {
            border-color: var(--booking-primary);
            background-color: color-mix(in srgb, var(--booking-primary) 10%, white);
        }

        .time-slot-btn.selected {
            border-color: var(--booking-primary);
            background-color: var(--booking-primary);
            color: #ffffff;
        }

        .time-slot-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.2s;
            min-height: 44px;
        }
        select.form-control { cursor: pointer; }

        .form-control:focus {
            border-color: var(--booking-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--booking-primary) 10%, transparent);
            outline: none;
        }

        /* Native DOB on booking: mobile-friendly picker, avoid tiny text on iOS */
        input.form-control[type="date"].public-booking-dob-native {
            min-height: 48px;
            font-size: 1rem;
        }

        .form-control.is-invalid {
            border-color: #dc2626;
        }

        .invalid-feedback {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Consultation Type Radio Styling */
        .consultation-option .form-check-input:checked + .form-check-label {
            border-color: var(--booking-primary) !important;
            background-color: color-mix(in srgb, var(--booking-primary) 10%, white);
            color: var(--booking-primary);
            font-weight: 600;
        }

        .consultation-option .form-check-input:focus + .form-check-label {
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--booking-primary) 10%, transparent);
        }

        /* Buttons - touch-friendly min height */
        .btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary {
            background-color: var(--booking-primary) !important;
            border-color: var(--booking-primary) !important;
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--booking-primary-hover) !important;
            border-color: var(--booking-primary-hover) !important;
        }

        .btn-primary:disabled {
            background-color: #cbd5e1 !important;
            border-color: #cbd5e1 !important;
            cursor: not-allowed;
        }

        .btn-success {
            background-color: var(--booking-success) !important;
            border-color: var(--booking-success) !important;
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-success:hover {
            background-color: var(--booking-success-hover) !important;
            border-color: var(--booking-success-hover) !important;
        }

        .btn-success:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-outline-secondary {
            border-color: #e2e8f0;
            color: #4a5568;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
        }
        .btn-lg { padding: 0.875rem 1.5rem; min-height: 48px; }
        @media (max-width: 767px) {
            .btn-lg { width: 100%; justify-content: center; }
            .d-flex.justify-content-between { flex-direction: column; gap: 0.75rem; }
            .d-flex.justify-content-between .btn { width: 100%; }
        }

        /* Review Section */
        .review-card-header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .review-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f7fafc;
        }

        .review-row:last-child {
            border-bottom: none;
        }

        .review-label {
            font-size: 0.875rem;
            color: #718096;
        }

        .review-value {
            font-weight: 600;
            color: #1a202c;
            text-align: right;
        }

        .review-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--booking-primary);
        }

        /* Summary Rows */
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .summary-label {
            font-size: 0.875rem;
            color: #718096;
        }

        .summary-value {
            font-weight: 600;
            color: #1a202c;
        }

        /* Service Card Details */
        .service-card-header h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }

        .service-card-header p {
            font-size: 0.875rem;
            color: #718096;
            margin: 0;
        }

        .service-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }

        .service-duration {
            font-size: 0.875rem;
            color: #718096;
        }

        .service-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a202c;
        }

        .service-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .service-tag {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            background-color: #f7fafc;
            color: #4a5568;
            border-radius: 6px;
            font-weight: 500;
        }

        /* Doctor Card Details */
        .doctor-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .doctor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #718096;
        }

        .doctor-details h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }

        .doctor-details p {
            font-size: 0.875rem;
            color: #718096;
            margin: 0;
        }

        /* Loading & Empty States */
        .loading-spinner {
            text-align: center;
            padding: 2rem;
            color: #718096;
        }

        .empty-state, .empty-message {
            text-align: center;
            padding: 3rem 1rem;
            color: #718096;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }

        .empty-message {
            padding: 2rem;
            background: #f7fafc;
            border-radius: 8px;
        }

        /* Responsive - mobile first */
        @media (max-width: 767px) {
            .booking-header { margin-bottom: 2rem; }
            .booking-header h1 { font-size: 1.35rem; }
            .booking-header p { font-size: 0.9rem; }
            .progress-steps { flex-wrap: wrap; justify-content: center; gap: 0.5rem; margin-bottom: 2rem; }
            .step-line { width: 24px; margin-top: -22px; }
            .step-circle { width: 36px; height: 36px; font-size: 0.8rem; }
            .step-label { font-size: 0.65rem; max-width: 60px; text-align: center; }
            .info-card, .summary-card, .form-card, .review-card { padding: 1rem; margin-bottom: 1.5rem; }
            .form-card, .review-card { padding: 1.25rem; }
            .services-grid, .doctors-grid { grid-template-columns: 1fr; gap: 1rem; }
            .service-card, .doctor-card { padding: 1.25rem; min-height: 44px; }
            .time-slots-grid { grid-template-columns: repeat(2, 1fr); gap: 0.5rem; }
            .time-slot-btn { padding: 0.875rem 0.5rem; min-height: 44px; font-size: 0.9rem; }
            .summary-row, .review-row { flex-wrap: wrap; gap: 0.25rem; }
            .review-value { text-align: left; }
        }
        @media (min-width: 768px) {
            .time-slots-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
        }

        /* Embed mode: compact when shown in iframe (e.g. WordPress) */
        body.embed-mode .booking-container { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        body.embed-mode .booking-header { margin-bottom: 1.5rem; }
        body.embed-mode .booking-header h1 { font-size: 1.5rem; }
        /* Desktop layout only when iframe is wide (script adds .embed-desktop); mobile keeps fluid layout */
        body.embed-mode.embed-desktop .booking-container { min-width: 900px; }

        @yield('styles')
    </style>
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body class="{{ $embed ? 'embed-mode' : '' }}">
    @include('partials.gtm-body')
    @if($embed)
    <script>
    (function() {
        var vp = document.getElementById('booking-viewport');
        if (vp && window.innerWidth >= 768) {
            vp.setAttribute('content', 'width=1100, initial-scale=1.0');
            document.body.classList.add('embed-desktop');
        }
    })();
    </script>
    @endif
    <div class="booking-container" style="max-width: @yield('container-width', '1200px')">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    @yield('scripts')
    <!-- Flatpickr Initialization - Load after all scripts -->
    <script src="{{ asset('js/flatpickr-init.js') }}?v={{ time() }}"></script>
</body>
</html>
