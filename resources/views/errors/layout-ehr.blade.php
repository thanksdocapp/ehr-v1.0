<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ getAppName() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ route('theme.css') }}">
    <style>
        :root {
            --ehr-error-primary: var(--primary-color, #2563eb);
            --ehr-error-surface: #ffffff;
            --ehr-error-page-bg: #f1f5f9;
            --ehr-error-muted: #64748b;
        }
        .ehr-error-page {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh;
            color: #1e293b;
            background: var(--ehr-error-page-bg);
            position: relative;
            overflow-x: hidden;
        }
        .ehr-error-page::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(1200px 600px at 10% -10%, color-mix(in srgb, var(--ehr-error-primary) 12%, transparent), transparent 55%),
                radial-gradient(900px 500px at 100% 20%, color-mix(in srgb, var(--ehr-error-primary) 8%, transparent), transparent 50%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            z-index: 0;
            pointer-events: none;
        }
        .ehr-error-page-inner {
            position: relative;
            z-index: 1;
        }
        .ehr-error-card {
            background: var(--ehr-error-surface);
            border-radius: 1rem;
            box-shadow:
                0 1px 2px rgba(15, 23, 42, 0.06),
                0 24px 48px -12px rgba(15, 23, 42, 0.12);
            border: 1px solid rgba(148, 163, 184, 0.2);
            overflow: hidden;
        }
        .ehr-error-card__accent {
            height: 4px;
            width: 100%;
            background: linear-gradient(90deg, var(--accent, var(--ehr-error-primary)), color-mix(in srgb, var(--accent, var(--ehr-error-primary)) 65%, #94a3b8));
        }
        .ehr-error-icon-wrap {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            background: color-mix(in srgb, var(--accent, var(--ehr-error-primary)) 12%, white);
            color: var(--accent, var(--ehr-error-primary));
            margin: 0 auto 1.25rem;
        }
        .ehr-error-heading {
            font-weight: 700;
            font-size: 1.375rem;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        .ehr-error-body {
            color: var(--ehr-error-muted);
            font-size: 0.95rem;
            line-height: 1.65;
            max-width: 32rem;
            margin-left: auto;
            margin-right: auto;
        }
        .ehr-error-actions .btn {
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.5rem 1.25rem;
        }
        .ehr-error-ref {
            font-size: 0.75rem;
            color: #94a3b8;
            letter-spacing: 0.02em;
        }
        .ehr-error-footer {
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="ehr-error-page">
    <div class="ehr-error-page-inner">
        <main class="container py-5" role="main" aria-labelledby="ehr-error-title">
            <div class="row justify-content-center align-items-center min-vh-100 py-4">
                <div class="col-12 col-md-10 col-lg-7 col-xl-6">
                    @yield('content')
                    <p class="ehr-error-footer text-center mt-4 mb-0">
                        {{ getAppName() }} · {{ date('Y') }}
                    </p>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
