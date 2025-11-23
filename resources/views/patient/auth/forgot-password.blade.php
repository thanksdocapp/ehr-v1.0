<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Patient Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <i class="fas fa-user-injured fa-3x text-primary mb-3"></i>
                            <h3 class="card-title text-primary">Reset Password</h3>
                            <p class="text-muted">Enter your email to receive a password reset link</p>
                        </div>

                        <!-- Success Message -->
                        @if(session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Error Messages -->
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Reset Password Form -->
                        <form method="POST" action="{{ route('patient.password.email') }}">
                            @csrf
                            
                            <!-- Email -->
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email') }}" 
                                           required 
                                           autofocus
                                           placeholder="Enter your registered email">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Send Reset Link
                                </button>
                            </div>
                        </form>

                        <!-- Back to Login -->
                        <div class="text-center">
                            <hr>
                            <p class="mb-0">
                                Remember your password? 
                                <a href="{{ route('patient.login') }}" class="text-primary">
                                    <i class="fas fa-sign-in-alt me-1"></i>
                                    Back to Login
                                </a>
                            </p>
                        </div>

                        <!-- Footer Info -->
                        <div class="text-center mt-4">
                            <p class="text-muted mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    For patient portal access only
                                </small>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Need Help?</h6>
                        <small class="text-muted">
                            If you don't receive the reset email within a few minutes, please:
                            <ul class="mt-2 mb-0">
                                <li>Check your spam/junk folder</li>
                                <li>Ensure you're using the correct email address</li>
                                <li>Contact our support team for assistance</li>
                            </ul>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
