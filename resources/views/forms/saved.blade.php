<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress Saved - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --border-color: #dee2e6;
            --bg-light: #f8f9fa;
        }
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header {
            background: #0d6efd;
            padding: 30px;
            text-align: center;
        }
        .form-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
        }
        .form-header .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .form-header .icon-circle i {
            font-size: 36px;
            color: white;
        }
        .form-body {
            padding: 30px;
            text-align: center;
        }
        .form-body p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        .btn-continue {
            background: var(--primary-color);
            border: none;
            padding: 12px 32px;
            font-size: 1rem;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.15s ease-in-out;
        }
        .btn-continue:hover {
            background: #0b5ed7;
            color: white;
        }
        .clinic-branding {
            text-align: center;
            padding: 16px;
            border-top: 1px solid var(--border-color);
            color: #6c757d;
            font-size: 0.85rem;
            background: var(--bg-light);
        }
        .logo-section {
            text-align: center;
            padding: 20px 0;
        }
        .logo-section img {
            max-height: 60px;
            max-width: 200px;
        }
        .info-box {
            background: #e7f1ff;
            border: 1px solid #b6d4fe;
            border-radius: 6px;
            padding: 16px;
            margin: 20px 0;
            text-align: left;
        }
        .info-box i {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="form-container py-5">
        <div class="form-card">
            @if(config('app.logo'))
            <div class="logo-section">
                <img src="{{ asset('storage/' . config('app.logo')) }}" alt="{{ config('app.name') }}">
            </div>
            @endif

            <div class="form-header">
                <div class="icon-circle">
                    <i class="fas fa-save"></i>
                </div>
                <h1>Progress Saved</h1>
            </div>

            <div class="form-body">
                <p class="lead">Your progress has been saved successfully.</p>
                <p>You can return to this form at any time using the same link to continue where you left off.</p>

                <div class="info-box">
                    <p class="mb-2"><i class="fas fa-info-circle me-2"></i><strong>Important:</strong></p>
                    <ul class="mb-0 ps-4">
                        <li>Your form is not yet submitted</li>
                        <li>Keep your link safe to return later</li>
                        @if($formRequest->expires_at)
                        <li>Complete by {{ $formRequest->expires_at->format('F d, Y') }}</li>
                        @endif
                    </ul>
                </div>

                <a href="{{ route('forms.fill', $formRequest->token) }}" class="btn-continue">
                    <i class="fas fa-arrow-left me-2"></i>Return to Form
                </a>
            </div>

            <div class="clinic-branding">
                <p class="mb-0">Powered by {{ config('app.name') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
