@extends(auth()->check() && auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Change Password')
@section('page-title', 'Change Password')
@section('page-subtitle', 'Update your account password')

@section('content')
<div class="fade-in-up">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <!-- Alert Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none;">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none;">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Change Password Form -->
                    <form method="POST" action="{{ route('change-password') }}" id="changePasswordForm">
                        @csrf
                        
                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="current_password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2"></i>Current Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg @error('current_password') is-invalid @enderror" 
                                   id="current_password" 
                                   name="current_password" 
                                   required 
                                   autofocus
                                   placeholder="Enter your current password"
                                   style="border-radius: 12px;">
                            @error('current_password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-key me-2"></i>New Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required
                                   minlength="8"
                                   placeholder="Enter your new password"
                                   style="border-radius: 12px;">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle me-1"></i>Password must be at least 8 characters long.
                            </small>
                        </div>

                        <!-- Confirm New Password -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                <i class="fas fa-key me-2"></i>Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required
                                   minlength="8"
                                   placeholder="Confirm your new password"
                                   style="border-radius: 12px;">
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Strength Indicator -->
                        <div class="mb-4">
                            <div class="progress" style="height: 6px; border-radius: 10px; display: none;" id="passwordStrength">
                                <div class="progress-bar" role="progressbar" style="width: 0%; transition: all 0.3s ease;"></div>
                            </div>
                            <small class="text-muted" id="passwordStrengthText"></small>
                        </div>

                        <!-- Submit and Cancel Buttons -->
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-doctor-primary btn-lg" style="border-radius: 12px; padding: 0.875rem;">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                            <a href="{{ Auth::check() ? (Auth::user()->is_admin ? route('admin.dashboard') : route('staff.dashboard')) : route('dashboard') }}" 
                               class="btn btn-outline-secondary btn-lg" style="border-radius: 12px; padding: 0.875rem;">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>

                        <!-- Security Info -->
                        <div class="text-center mt-4 pt-4 border-top">
                            <p class="text-muted mb-0 small">
                                <i class="fas fa-shield-alt me-1"></i>
                                Keep your password secure and don't share it with others
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    // Password strength checker
    if (passwordInput && strengthBar && strengthText) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length === 0) {
                strengthBar.style.display = 'none';
                strengthText.textContent = '';
                return;
            }
            
            strengthBar.style.display = 'block';
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    text = 'Very Weak';
                    color = '#dc3545';
                    strengthBar.querySelector('.progress-bar').style.width = '20%';
                    break;
                case 2:
                    text = 'Weak';
                    color = '#fd7e14';
                    strengthBar.querySelector('.progress-bar').style.width = '40%';
                    break;
                case 3:
                    text = 'Fair';
                    color = '#ffc107';
                    strengthBar.querySelector('.progress-bar').style.width = '60%';
                    break;
                case 4:
                    text = 'Good';
                    color = '#20c997';
                    strengthBar.querySelector('.progress-bar').style.width = '80%';
                    break;
                case 5:
                    text = 'Strong';
                    color = '#198754';
                    strengthBar.querySelector('.progress-bar').style.width = '100%';
                    break;
            }
            
            strengthBar.querySelector('.progress-bar').style.backgroundColor = color;
            strengthText.textContent = 'Password Strength: ' + text;
            strengthText.style.color = color;
        });
    }
    
    // Password matching validation
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
        
        passwordInput.addEventListener('input', function() {
            const confirmPassword = confirmPasswordInput.value;
            if (confirmPassword) {
                confirmPasswordInput.dispatchEvent(new Event('input'));
            }
        });
    }
});
</script>
@endpush
