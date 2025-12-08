<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Expired - {{ config('app.name') }}</title>
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
        .error-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
        }
        .error-icon i {
            font-size: 50px;
            color: white;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-clock"></i>
        </div>
        <h1>Form Expired</h1>
        <p>This form link has expired and is no longer available for submission.</p>
        <p class="text-muted">Please contact the healthcare provider if you still need to complete this form.</p>
    </div>
</body>
</html>
