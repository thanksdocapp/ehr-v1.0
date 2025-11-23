<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Registration - {{ getAppName() }}</title>
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
            padding: 40px 0;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--gold-color) 100%);
            color: white;
            padding: 40px 50px;
            text-align: center;
        }

        .register-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .register-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .register-form-section {
            padding: 50px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gold-color);
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 12px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--gold-color);
            box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
        }

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(233, 69, 96, 0.3);
            color: white;
        }

        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .login-link a {
            color: var(--gold-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .required-field {
            color: var(--gold-color);
        }

        @media (max-width: 768px) {
            .register-container {
                margin: 20px;
            }

            .register-header,
            .register-form-section {
                padding: 30px;
            }

            .register-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <!-- Header Section -->
            <div class="register-header">
                <h1><i class="fas fa-user-plus me-3"></i>Patient Registration</h1>
                <p>Join our healthcare community and take control of your health journey</p>
            </div>

            <!-- Form Section -->
            <div class="register-form-section">
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

                <form method="POST" action="{{ route('patient.register') }}">
                    @csrf
                    
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-user me-2"></i>Personal Information</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="{{ old('first_name') }}" 
                                           placeholder="First Name" 
                                           required>
                                    <label for="first_name">First Name <span class="required-field">*</span></label>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" 
                                           name="last_name" 
                                           value="{{ old('last_name') }}" 
                                           placeholder="Last Name" 
                                           required>
                                    <label for="last_name">Last Name <span class="required-field">*</span></label>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" 
                                           name="date_of_birth" 
                                           value="{{ old('date_of_birth') }}" 
                                           required>
                                    <label for="date_of_birth">Date of Birth <span class="required-field">*</span></label>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select @error('gender') is-invalid @enderror" 
                                            id="gender" 
                                            name="gender" 
                                            required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    <label for="gender">Gender <span class="required-field">*</span></label>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-address-book me-2"></i>Contact Information</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           placeholder="name@example.com" 
                                           required>
                                    <label for="email">Email Address <span class="required-field">*</span></label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone') }}" 
                                           placeholder="Phone Number" 
                                           required>
                                    <label for="phone">Phone Number <span class="required-field">*</span></label>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Information -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-shield-alt me-2"></i>Security Information</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Password" 
                                           required>
                                    <label for="password">Password <span class="required-field">*</span></label>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Confirm Password" 
                                           required>
                                    <label for="password_confirmation">Confirm Password <span class="required-field">*</span></label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-section">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a href="#" class="text-decoration-none">Privacy Policy</a> <span class="required-field">*</span>
                            </label>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                            <label class="form-check-label" for="newsletter">
                                I would like to receive health tips and appointment reminders via email
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register">
                        <i class="fas fa-user-plus me-2"></i>Create My Account
                    </button>
                </form>

                <div class="login-link">
                    <p>Already have an account? <a href="{{ route('patient.login') }}">Sign in here</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
