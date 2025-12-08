<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submitted - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        h1 {
            color: #28a745;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            font-size: 1.1rem;
        }
        .details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
            text-align: left;
        }
        .details p {
            margin: 5px 0;
            font-size: 0.95rem;
        }
        .details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h1>Thank You!</h1>
        <p>Your form has been submitted successfully.</p>
        <p class="text-muted">The healthcare provider has been notified and will review your submission.</p>

        <div class="details">
            <p><strong>Form:</strong> {{ $formRequest->template->name }}</p>
            <p><strong>Submitted:</strong> {{ $formRequest->completed_at->format('F d, Y \a\t H:i') }}</p>
            <p><strong>Reference:</strong> #{{ $formRequest->id }}</p>
        </div>

        <p class="mt-4 text-muted small">
            You may close this window now.
        </p>
    </div>
</body>
</html>
