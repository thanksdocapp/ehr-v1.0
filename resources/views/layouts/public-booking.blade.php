<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .booking-container {
            max-width: @yield('container-width', '1200px');
            margin: 0 auto;
            padding: 2rem 1rem;
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
        }

        .form-control:focus {
            border-color: var(--booking-primary);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--booking-primary) 10%, transparent);
            outline: none;
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

        /* Buttons */
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

        /* Responsive */
        @media (max-width: 768px) {
            .booking-header h1 {
                font-size: 1.5rem;
            }

            .services-grid, .doctors-grid {
                grid-template-columns: 1fr;
            }

            .time-slots-grid {
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            }

            .step-line {
                width: 30px;
            }
        }

        @yield('styles')
    </style>
</head>
<body>
    <div class="booking-container" style="max-width: @yield('container-width', '1200px')">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
