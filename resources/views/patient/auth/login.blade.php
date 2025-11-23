<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Login - {{ getAppName() }}</title>
    <link rel="icon" type="image/x-icon" href="{{ getFavicon() }}">
    
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
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--accent-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            min-height: 600px;
        }

        .login-form-section {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-brand h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .login-brand p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--gold-color);
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--gold-color) 0%, #c73650 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(233, 69, 96, 0.3);
            color: white;
        }

        .login-info-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--gold-color) 100%);
            color: white;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .info-icon {
            font-size: 4rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .info-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .info-text {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .info-features {
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .feature-item i {
            margin-right: 15px;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
        }

        .register-link a {
            color: var(--gold-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                margin: 20px;
                max-width: 500px;
            }

            .login-info-section {
                order: -1;
                padding: 40px 30px;
            }

            .login-form-section {
                padding: 40px 30px;
            }

            .info-features {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Login Form Section -->
        <div class="login-form-section">
            <div class="login-brand">
                <h1><i class="fas fa-user-injured text-primary me-2"></i>Patient Portal</h1>
                <p>Access your medical records and appointments</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('patient.login') }}">
                @csrf
                
                <div class="form-floating">
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="name@example.com" 
                           required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-floating">
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Password" 
                           required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="register-link">
                <p>Don't have an account? <a href="{{ route('patient.register') }}">Register here</a></p>
                <p><a href="{{ route('patient.password.request') }}">Forgot your password?</a></p>
            </div>
        </div>

        <!-- Info Section -->
        <div class="login-info-section">
            <div class="info-icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <h2 class="info-title">Welcome to Your Health Portal</h2>
            <p class="info-text">
                Access your complete medical information, manage appointments, and stay connected with your healthcare providers.
            </p>
            
            <div class="info-features">
                <div class="feature-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Book and manage appointments</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-file-medical"></i>
                    <span>View medical records and test results</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-bell"></i>
                    <span>Receive appointment reminders</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure and confidential access</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
