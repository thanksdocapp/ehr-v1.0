<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Already Completed - {{ config('app.name') }}</title>
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
        .info-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .info-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .info-icon i {
            font-size: 50px;
            color: white;
        }
        h1 {
            color: #17a2b8;
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
    </style>
</head>
<body>
    <div class="info-card">
        <div class="info-icon">
            <i class="fas fa-check-double"></i>
        </div>
        <h1>Already Completed</h1>
        <p>This form has already been submitted.</p>

        @if($formRequest->completed_at)
        <div class="details">
            <p><strong>Form:</strong> {{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Form') }}</p>
            <p><strong>Completed:</strong> {{ $formRequest->completed_at->format('F d, Y \a\t H:i') }}</p>
        </div>
        @endif

        <p class="mt-4 text-muted">If you need to make changes, please contact the healthcare provider.</p>
    </div>
</body>
</html>
