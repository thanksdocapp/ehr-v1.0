<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Site Under Maintenance' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --gold-color: #e94560;
            --gradient-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-bg);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .maintenance-container {
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .maintenance-icon {
            font-size: 120px;
            color: var(--gold-color);
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .maintenance-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }

        .maintenance-message {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 40px;
            color: rgba(255, 255, 255, 0.9);
        }

        .maintenance-details {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }

        .countdown-item {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            padding: 15px;
            min-width: 80px;
        }

        .countdown-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gold-color);
        }

        .countdown-label {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .social-links {
            margin-top: 40px;
        }

        .social-links a {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.5rem;
            margin: 0 15px;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: var(--gold-color);
            transform: translateY(-2px);
        }

        .progress-bar-container {
            margin: 30px 0;
        }

        .progress {
            height: 8px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--gold-color), #ff6b88);
            border-radius: 10px;
            animation: progress 3s ease-in-out infinite;
        }

        @keyframes progress {
            0%, 100% { width: 30%; }
            50% { width: 70%; }
        }

        .admin-login-hint {
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border-left: 4px solid var(--gold-color);
        }

        .admin-login-hint a {
            color: var(--gold-color);
            text-decoration: none;
            font-weight: 600;
        }

        .admin-login-hint a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .maintenance-title {
                font-size: 2rem;
            }
            
            .maintenance-icon {
                font-size: 80px;
            }
            
            .countdown {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .countdown-item {
                min-width: 60px;
                padding: 10px;
            }
            
            .countdown-number {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1 class="maintenance-title">{{ $title ?? 'Site Under Maintenance' }}</h1>
        
        <div class="maintenance-details">
            <p class="maintenance-message">
                {{ $message ?? 'We are currently performing scheduled maintenance to improve your experience. Please check back soon.' }}
            </p>
            
            <div class="progress-bar-container">
                <div class="progress">
                    <div class="progress-bar" role="progressbar"></div>
                </div>
                <small class="text-muted mt-2 d-block">Maintenance in progress...</small>
            </div>
            
            <div class="countdown" id="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="hours">--</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes">{{ $retryAfter ?? 60 }}</div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds">00</div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>
            
            <p class="mb-0">
                <i class="fas fa-clock me-2"></i>
                Expected completion: <strong id="estimated-time">{{ now()->addMinutes($retryAfter ?? 60)->format('M d, Y H:i') }}</strong>
            </p>
        </div>
        
        <div class="admin-login-hint">
            <i class="fas fa-user-shield me-2"></i>
            Are you an administrator? 
            <a href="{{ route('admin.login') }}">Login to Admin Panel</a>
        </div>
        
        <div class="social-links">
            <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
            <a href="#" title="Email"><i class="fas fa-envelope"></i></a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                © {{ date('Y') }} {{ config('app.name', 'Global Trust Finance') }}. All rights reserved.
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh countdown and page refresh
        let retryAfterMinutes = {{ $retryAfter ?? 60 }};
        let totalSeconds = retryAfterMinutes * 60;
        
        function updateCountdown() {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            
            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            
            if (totalSeconds <= 0) {
                // Refresh the page when countdown reaches zero
                location.reload();
            }
            
            totalSeconds--;
        }
        
        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Auto-refresh page every 5 minutes to check if maintenance is over
        setInterval(() => {
            location.reload();
        }, 5 * 60 * 1000);
    </script>
</body>
</html>
