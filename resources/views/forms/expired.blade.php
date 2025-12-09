<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Expired - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        :root {
            --warning-color: #ffc107;
            --border-color: #dee2e6;
            --bg-light: #f8f9fa;
        }
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .error-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .error-icon {
            width: 80px;
            height: 80px;
            background: var(--warning-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .error-icon i {
            font-size: 40px;
            color: #212529;
        }
        h1 {
            color: #997404;
            margin-bottom: 12px;
            font-size: 1.75rem;
            font-weight: 600;
        }
        .subtitle {
            color: #212529;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }
        .description {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .contact-notice {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            color: #6c757d;
            font-size: 0.85rem;
        }
        .logo-section {
            margin-bottom: 20px;
        }
        .logo-section img {
            max-height: 50px;
            max-width: 180px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        @if(config('app.logo'))
        <div class="logo-section">
            <img src="{{ asset('storage/' . config('app.logo')) }}" alt="{{ config('app.name') }}">
        </div>
        @endif

        <div class="error-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h1>Form Expired</h1>
        <p class="subtitle">This form link has expired and is no longer available.</p>
        <p class="description">The submission deadline for this form has passed.</p>

        <div class="contact-notice">
            <i class="fas fa-info-circle me-1"></i>
            Please contact the healthcare provider if you still need to complete this form.
        </div>
    </div>
</body>
</html>
