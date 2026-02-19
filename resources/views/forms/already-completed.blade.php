<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Already Completed - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        :root {
            --info-color: #0dcaf0;
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
        .info-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .info-icon {
            width: 80px;
            height: 80px;
            background: var(--info-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .info-icon i {
            font-size: 40px;
            color: white;
        }
        h1 {
            color: #0aa2c0;
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
        .details {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 20px;
            margin-top: 24px;
            text-align: left;
        }
        .details p {
            margin: 8px 0;
            font-size: 0.9rem;
            color: #495057;
        }
        .details strong {
            color: #212529;
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
    <div class="info-card">
        @if(config('app.logo'))
        <div class="logo-section">
            <img src="{{ asset('storage/' . config('app.logo')) }}" alt="{{ config('app.name') }}">
        </div>
        @endif

        <div class="info-icon">
            <i class="fas fa-check-double"></i>
        </div>
        <h1>Already Completed</h1>
        <p class="subtitle">This form has already been submitted.</p>

        @if($formRequest->completed_at)
        <div class="details">
            <p><strong><i class="fas fa-file-alt me-2 text-muted"></i>Form:</strong> {{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Form') }}</p>
            <p><strong><i class="fas fa-calendar-check me-2 text-muted"></i>Completed:</strong> {{ \Carbon\Carbon::parse($formRequest->completed_at)->format('j F Y \a\t H:i') }}</p>
        </div>
        @endif

        <div class="contact-notice">
            <i class="fas fa-info-circle me-1"></i>
            If you need to make changes, please contact the healthcare provider.
        </div>
    </div>
</body>
</html>
