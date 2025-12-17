<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback already submitted - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        body { background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif; }
        .wrap { max-width: 700px; margin: 0 auto; padding: 24px; }
        .cardx { background: #fff; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 28px; }
    </style>
</head>
<body>
    <div class="wrap py-5">
        <div class="cardx text-center">
            <div class="mb-3">
                <i class="fas fa-info-circle text-primary" style="font-size: 2.5rem;"></i>
            </div>
            <h1 class="h4 fw-bold mb-2">Feedback already submitted</h1>
            <p class="text-muted mb-0">Thanks — we’ve already received your response for this consultation.</p>
        </div>
        <div class="text-center text-muted small mt-3">
            {{ config('app.name') }}
        </div>
    </div>
</body>
</html>


