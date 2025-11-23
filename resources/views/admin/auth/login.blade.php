<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --gold-color: #e94560;
            --text-light: #f8f9fa;
            --gradient-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--gradient-bg);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .admin-login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 20px;
        }

        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(233, 69, 96, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 120px;
            height: 120px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 80px;
            height: 80px;
            top: 70%;
            right: 20%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.7;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 1;
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            padding: 0;
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 2;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            background: var(--gradient-bg);
            color: var(--text-light);
            padding: 40px 30px 30px;
            text-align: center;
            position: relative;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .admin-icon {
            width: 80px;
            height: 80px;
            background: var(--gold-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(233, 69, 96, 0.3);
            position: relative;
            z-index: 1;
        }

        .admin-icon i {
            font-size: 36px;
            color: white;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--gold-color);
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
            background: white;
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            z-index: 3;
        }

        .form-control.has-icon {
            padding-left: 50px;
        }

        .btn-admin {
            background: var(--gradient-bg);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(26, 26, 46, 0.3);
            color: white;
        }

        .btn-admin:active {
            transform: translateY(0);
        }

        .btn-admin .spinner-border {
            width: 1rem;
            height: 1rem;
            margin-right: 8px;
        }

        .remember-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .form-check-input:checked {
            background-color: var(--gold-color);
            border-color: var(--gold-color);
        }

        .form-check-label {
            color: #666;
            font-size: 14px;
        }

        .forgot-link {
            color: var(--gold-color);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .forgot-link:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        .alert-success {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.2);
        }

        .footer-text {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .admin-login-container {
                padding: 10px;
            }
            
            .login-card {
                max-width: 100%;
                margin: 10px;
            }
            
            .login-header {
                padding: 30px 20px 25px;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .admin-icon {
                width: 70px;
                height: 70px;
            }
            
            .admin-icon i {
                font-size: 32px;
            }
            
            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="admin-login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="admin-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h2>Admin Portal</h2>
                <p>Secure access to administrative dashboard</p>
            </div>

            <div class="login-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div id="login-alert" class="alert alert-danger d-none">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="login-error-message"></span>
                </div>

                <form id="loginForm" method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="login" class="form-label">
                            <i class="fas fa-user me-2"></i>Email or Username
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" 
                                   class="form-control has-icon @error('login') is-invalid @enderror" 
                                   id="login" 
                                   name="login" 
                                   value="{{ old('login') }}" 
                                   placeholder="Enter your email or username"
                                   required 
                                   autocomplete="username">
                        </div>
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control has-icon @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required 
                                   autocomplete="current-password">
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="remember-section">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-admin" id="loginBtn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Sign In
                        </span>
                        <span class="btn-loading d-none">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Signing in...
                        </span>
                    </button>
                </form>

                <div class="footer-text">
                    <i class="fas fa-shield-alt me-2"></i>
                    Protected by advanced security measures
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Login form submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $btn = $('#loginBtn');
                const $btnText = $btn.find('.btn-text');
                const $btnLoading = $btn.find('.btn-loading');
                const $alert = $('#login-alert');
                
                // Show loading state
                $btn.prop('disabled', true);
                $btnText.addClass('d-none');
                $btnLoading.removeClass('d-none');
                $alert.addClass('d-none');
                
                // Submit form
                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Show success message and redirect
                            $alert.removeClass('alert-danger').addClass('alert-success');
                            $('#login-error-message').text(response.message);
                            $alert.removeClass('d-none');
                            
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            // Handle failed login (wrong credentials)
                            $alert.removeClass('alert-success').addClass('alert-danger');
                            $('#login-error-message').text(response.message || 'Invalid credentials. Please try again.');
                            $alert.removeClass('d-none');
                            
                            // Reset button state
                            $btn.prop('disabled', false);
                            $btnText.removeClass('d-none');
                            $btnLoading.addClass('d-none');
                            
                            // Auto-hide error message after 5 seconds
                            setTimeout(function() {
                                $alert.fadeOut();
                            }, 5000);
                        }
                    },
                    error: function(xhr) {
                        // Show error message
                        let errorMessage = 'An error occurred. Please try again.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors[0] || errorMessage;
                        }
                        
                        $alert.removeClass('alert-success').addClass('alert-danger');
                        $('#login-error-message').text(errorMessage);
                        $alert.removeClass('d-none');
                        
                        // Reset button state
                        $btn.prop('disabled', false);
                        $btnText.removeClass('d-none');
                        $btnLoading.addClass('d-none');
                    }
                });
            });

            // Input focus animations
            $('.form-control').on('focus', function() {
                $(this).parent().addClass('focused');
            }).on('blur', function() {
                if (!$(this).val()) {
                    $(this).parent().removeClass('focused');
                }
            });

            // Auto-hide alerts after 5 seconds
            $('.alert:not(#login-alert)').delay(30000).fadeOut();
        });
    </script>
</body>
</html>

