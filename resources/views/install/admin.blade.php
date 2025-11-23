@extends('install.layout')

@section('title', 'Admin Setup - Hospital Management System Installation')

@section('content')
<div class="text-center mb-4">
    <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
    <h2 class="step-title">Administrator Account</h2>
    <p class="text-muted">
        Create your administrator account to manage the system
    </p>
</div>

<form id="adminForm" action="{{ route('install.process', 'admin') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="admin_name" class="form-label">
                    <i class="fas fa-user me-2"></i>
                    Full Name
                </label>
                <input type="text" 
                       class="form-control" 
                       id="admin_name" 
                       name="admin_name" 
                       value="System Administrator" 
                       required>
                <small class="text-muted">Your full name for the admin account</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="admin_username" class="form-label">
                    <i class="fas fa-at me-2"></i>
                    Username
                </label>
                <input type="text" 
                       class="form-control" 
                       id="admin_username" 
                       name="admin_username" 
                       value="admin" 
                       required>
                <small class="text-muted">Username for logging into the admin panel</small>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="admin_email" class="form-label">
                    <i class="fas fa-envelope me-2"></i>
                    Email Address
                </label>
                <input type="email" 
                       class="form-control" 
                       id="admin_email" 
                       name="admin_email" 
                       placeholder="admin@example.com" 
                       required>
                <small class="text-muted">Valid email address for admin notifications</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="admin_phone" class="form-label">
                    <i class="fas fa-phone me-2"></i>
                    Phone Number
                </label>
                <input type="tel" 
                       class="form-control" 
                       id="admin_phone" 
                       name="admin_phone" 
                       placeholder="+1234567890">
                <small class="text-muted">Optional: Phone number for admin account</small>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="admin_password" class="form-label">
                    <i class="fas fa-lock me-2"></i>
                    Password
                </label>
                <div class="password-input-wrapper">
                    <input type="password" 
                           class="form-control" 
                           id="admin_password" 
                           name="admin_password" 
                           minlength="8" 
                           required>
                    <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword('admin_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength mt-2">
                    <div class="strength-meter">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <small class="text-muted" id="strengthText">Password strength: Weak</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="admin_password_confirmation" class="form-label">
                    <i class="fas fa-lock me-2"></i>
                    Confirm Password
                </label>
                <div class="password-input-wrapper">
                    <input type="password" 
                           class="form-control" 
                           id="admin_password_confirmation" 
                           name="admin_password_confirmation" 
                           minlength="8" 
                           required>
                    <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword('admin_password_confirmation')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="password-match mt-2">
                    <small class="text-muted" id="matchText"></small>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="mt-4">
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Admin Account Requirements:</strong>
        <ul class="mt-2 mb-0">
            <li>Password must be at least 8 characters long</li>
            <li>Include uppercase and lowercase letters</li>
            <li>Include at least one number</li>
            <li>Include at least one special character</li>
        </ul>
    </div>
    
    <div class="alert alert-warning">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Security Notice:</strong> 
        <ul class="mt-2 mb-0">
            <li>This account will have full administrative access</li>
            <li>Use a strong, unique password</li>
            <li>Store your credentials in a secure location</li>
            <li>You can change these details later from the admin panel</li>
        </ul>
    </div>
</div>

<div class="admin-privileges mt-4">
    <h6>
        <i class="fas fa-crown me-2"></i>
        Administrator Privileges
    </h6>
    <div class="row">
        <div class="col-md-6">
            <ul class="list-unstyled text-muted small">
                <li><i class="fas fa-check text-success me-2"></i> Full system management</li>
                <li><i class="fas fa-check text-success me-2"></i> User account management</li>
                    <li><i class="fas fa-check text-success me-2"></i> Medical operations control</li>
                <li><i class="fas fa-check text-success me-2"></i> System settings configuration</li>
            </ul>
        </div>
        <div class="col-md-6">
            <ul class="list-unstyled text-muted small">
                    <li><i class="fas fa-check text-success me-2"></i> GDPR & medical compliance settings</li>
                <li><i class="fas fa-check text-success me-2"></i> Audit logs and reporting</li>
                <li><i class="fas fa-check text-success me-2"></i> Email & SMS management</li>
                    <li><i class="fas fa-check text-success me-2"></i> Patient portal content management</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-info-circle me-1"></i>
        Step 6 of 7 - Administrator Account Setup
    </small>
</div>
<div>
    <a href="{{ route('install.step', 'database') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-2"></i>
        Back
    </a>
    <button type="button" onclick="submitAdmin()" class="btn btn-primary" id="adminSubmitBtn">
        <i class="fas fa-user-plus me-2"></i>
        Create Administrator
    </button>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    
    function checkPasswordStrength(password) {
        let strength = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 8) strength += 1;
        else feedback.push('At least 8 characters');
        
        // Uppercase check
        if (/[A-Z]/.test(password)) strength += 1;
        else feedback.push('Uppercase letter');
        
        // Lowercase check
        if (/[a-z]/.test(password)) strength += 1;
        else feedback.push('Lowercase letter');
        
        // Number check
        if (/\d/.test(password)) strength += 1;
        else feedback.push('Number');
        
        // Special character check
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
        else feedback.push('Special character');
        
        return { strength, feedback };
    }
    
    function updatePasswordStrength() {
        const password = document.getElementById('admin_password').value;
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        const { strength, feedback } = checkPasswordStrength(password);
        
        // Update strength bar
        const percentage = (strength / 5) * 100;
        strengthBar.style.width = percentage + '%';
        
        // Update colors and text
        if (strength === 0) {
            strengthBar.className = 'strength-bar bg-danger';
            strengthText.textContent = 'Password strength: Very Weak';
            strengthText.className = 'text-danger small';
        } else if (strength <= 2) {
            strengthBar.className = 'strength-bar bg-danger';
            strengthText.textContent = 'Password strength: Weak';
            strengthText.className = 'text-danger small';
        } else if (strength <= 3) {
            strengthBar.className = 'strength-bar bg-warning';
            strengthText.textContent = 'Password strength: Fair';
            strengthText.className = 'text-warning small';
        } else if (strength <= 4) {
            strengthBar.className = 'strength-bar bg-info';
            strengthText.textContent = 'Password strength: Good';
            strengthText.className = 'text-info small';
        } else {
            strengthBar.className = 'strength-bar bg-success';
            strengthText.textContent = 'Password strength: Strong';
            strengthText.className = 'text-success small';
        }
        
        if (feedback.length > 0 && password.length > 0) {
            strengthText.textContent += ' (Missing: ' + feedback.join(', ') + ')';
        }
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('admin_password').value;
        const confirmation = document.getElementById('admin_password_confirmation').value;
        const matchText = document.getElementById('matchText');
        
        if (confirmation.length === 0) {
            matchText.textContent = '';
            return;
        }
        
        if (password === confirmation) {
            matchText.textContent = 'Passwords match';
            matchText.className = 'text-success small';
        } else {
            matchText.textContent = 'Passwords do not match';
            matchText.className = 'text-danger small';
        }
    }
    
    function validateForm() {
        const name = document.getElementById('admin_name').value.trim();
        const username = document.getElementById('admin_username').value.trim();
        const email = document.getElementById('admin_email').value.trim();
        const password = document.getElementById('admin_password').value;
        const confirmation = document.getElementById('admin_password_confirmation').value;
        
        if (!name || !username || !email || !password || !confirmation) {
            showAlert('warning', 'Please fill in all required fields.');
            return false;
        }
        
        if (password !== confirmation) {
            showAlert('warning', 'Passwords do not match.');
            return false;
        }
        
        const { strength } = checkPasswordStrength(password);
        if (strength < 3) {
            showAlert('warning', 'Please use a stronger password. Include uppercase, lowercase, numbers, and special characters.');
            return false;
        }
        
        // Email validation
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            showAlert('warning', 'Please enter a valid email address.');
            return false;
        }
        
        return true;
    }
    
    function submitAdmin() {
        if (!validateForm()) {
            return;
        }
        
        const form = document.getElementById('adminForm');
        
        // Show loading
        showLoading();
        document.querySelector('#loadingOverlay .loading-content h5').textContent = 'Creating Administrator...';
        document.querySelector('#loadingOverlay .loading-content p').textContent = 'Setting up your admin account and finalizing installation.';
        
        submitForm(form, function(data) {
            clearAlerts();
            
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(function() {
                    window.location.href = "{{ route('install.step', 'final') }}";
                }, 2000);
            } else {
                showAlert('danger', data.message);
            }
        });
    }
    
    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const passwordField = document.getElementById('admin_password');
        const confirmationField = document.getElementById('admin_password_confirmation');
        
        passwordField.addEventListener('input', function() {
            updatePasswordStrength();
            if (confirmationField.value.length > 0) {
                checkPasswordMatch();
            }
        });
        
        confirmationField.addEventListener('input', checkPasswordMatch);
        
        // Auto-fill email based on domain
        const emailField = document.getElementById('admin_email');
        if (!emailField.value) {
            const hostname = window.location.hostname;
            if (hostname && hostname !== 'localhost' && !hostname.match(/^\d+\.\d+\.\d+\.\d+$/)) {
                emailField.value = `admin@${hostname}`;
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    .password-input-wrapper {
        position: relative;
        display: flex;
    }
    
    .password-input-wrapper .form-control {
        padding-right: 45px;
    }
    
    .password-toggle {
        position: absolute;
        right: 0;
        top: 0;
        height: 100%;
        width: 40px;
        border-left: none;
        z-index: 3;
    }
    
    .strength-meter {
        width: 100%;
        height: 6px;
        background-color: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .strength-bar {
        height: 100%;
        width: 0%;
        transition: width 0.3s ease, background-color 0.3s ease;
        border-radius: 3px;
    }
    
    .admin-privileges {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        background: #f8fafc;
    }
    
    .password-match, .password-strength {
        min-height: 20px;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .list-unstyled li {
        margin-bottom: 0.25rem;
    }
    
    .alert ul {
        margin-bottom: 0;
        padding-left: 1.25rem;
    }
    
    .alert li {
        margin-bottom: 0.25rem;
    }
</style>
@endpush
