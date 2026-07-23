<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="0;url={{ $url }}">
    <title>{{ $title ?? 'Redirecting' }}</title>
    <style>
        body {
            font-family: Inter, system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f5f7fa;
            color: #1a202c;
        }
        .wrap { text-align: center; padding: 2rem; }
        .spinner {
            width: 2rem;
            height: 2rem;
            border: 0.2rem solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="spinner" aria-hidden="true"></div>
        <p>Booking confirmed. Redirecting you now…</p>
        <p><a href="{{ $url }}" target="_top" rel="noopener noreferrer">Continue if you are not redirected</a></p>
    </div>
    <script>
        (function () {
            var url = @json($url);
            try {
                if (window.top && window.top !== window.self) {
                    window.top.location.replace(url);
                    return;
                }
            } catch (e) {
                // Cross-origin parent — fall back to link / meta refresh.
            }
            window.location.replace(url);
        })();
    </script>
</body>
</html>
