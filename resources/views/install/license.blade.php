@extends('install.layout')

@section('title', 'License Verification - ' . $productInfo['name'])

@section('content')
<div class="install-step" data-step="license">
    <div class="step-header text-center">
        <div class="step-icon">
            <i class="fas fa-key text-warning"></i>
        </div>
        <h2>License Verification</h2>
        <p class="step-description">Please verify your CodeCanyon purchase to continue</p>
    </div>

    <div class="license-info">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i>How to find your purchase code?</h5>
            <ol class="mb-0">
                <li>Log in to your <strong>CodeCanyon account</strong></li>
                <li>Go to <strong>Downloads</strong> section</li>
                <li>Find "{{ $productInfo['name'] }}" and click <strong>Download</strong></li>
                <li>Select <strong>License certificate & purchase code</strong></li>
                <li>Copy the <strong>Item Purchase Code</strong></li>
            </ol>
        </div>

        <div class="license-benefits">
            <h5><i class="fas fa-star text-warning me-2"></i>Your License Benefits</h5>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Lifetime Updates</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 6 Months Premium Support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Commercial Usage Rights</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Documentation Access</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Bug Fixes & Security Updates</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Feature Enhancements</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="license-form">
        <form id="licenseForm" class="needs-validation" novalidate>
            @csrf
            <div class="mb-4">
                <label for="purchase_code" class="form-label">
                    <i class="fas fa-key me-2"></i>Purchase Code <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control form-control-lg" 
                       id="purchase_code" 
                       name="purchase_code" 
                       placeholder="e.g., 12345678-1234-1234-1234-123456789012"
                       pattern="[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}"
                       required>
                <div class="invalid-feedback">
                    Please enter a valid Envato purchase code.
                </div>
                <small class="form-text text-muted">
                    This is the unique code you received when purchasing from CodeCanyon
                </small>
            </div>

            <div class="mb-4">
                <label for="envato_username" class="form-label">
                    <i class="fas fa-user me-2"></i>Envato Username <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control form-control-lg" 
                       id="envato_username" 
                       name="envato_username" 
                       placeholder="Your Envato/CodeCanyon username"
                       required>
                <div class="invalid-feedback">
                    Please enter your Envato username.
                </div>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="agree_terms" required>
                    <label class="form-check-label" for="agree_terms">
                        I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    </label>
                    <div class="invalid-feedback">
                        You must agree to the terms and conditions.
                    </div>
                </div>
            </div>

            <div class="license-actions">
                <button type="submit" class="btn btn-primary btn-lg" id="verifyBtn">
                    <i class="fas fa-shield-check me-2"></i>
                    Verify License
                </button>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-lock me-1"></i>
                        Your information is encrypted and secure
                    </small>
                </div>
            </div>
        </form>
    </div>

    <div class="license-help">
        <div class="help-section">
            <h6><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
            <div class="help-links">
                <a href="#" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-book me-1"></i>
                    How to find purchase code
                </a>
                <a href="#" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-envelope me-1"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('licenseForm');
    const verifyBtn = document.getElementById('verifyBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading state
        verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying License...';
        verifyBtn.disabled = true;
        
        // Collect form data
        const formData = new FormData(form);
        
        // Send verification request
        fetch('{{ route("install.process", "license") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showAlert('success', data.message);
                
                // Redirect to next step
                setTimeout(() => {
                    window.location.href = '{{ route("install.step", "requirements") }}';
                }, 1500);
            } else {
                // Show error message
                showAlert('error', data.message);
                resetButton();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An error occurred during verification. Please try again.');
            resetButton();
        });
    });
    
    function resetButton() {
        verifyBtn.innerHTML = '<i class="fas fa-shield-check me-2"></i>Verify License';
        verifyBtn.disabled = false;
    }
    
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert before form
        const form = document.getElementById('licenseForm');
        form.parentNode.insertBefore(alertDiv, form);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Format purchase code input
    const purchaseCodeInput = document.getElementById('purchase_code');
    purchaseCodeInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^a-f0-9]/gi, '').toLowerCase();
        let formatted = value.replace(/(.{8})(.{4})(.{4})(.{4})(.{12})/, '$1-$2-$3-$4-$5');
        e.target.value = formatted;
    });
});
</script>
@endsection

@push('styles')
<style>
.license-info {
    margin-bottom: 2rem;
}

.license-benefits {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-top: 1rem;
}

.license-form {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    border: 1px solid #e9ecef;
    margin-bottom: 2rem;
}

.license-actions {
    text-align: center;
}

.license-help {
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.help-links {
    margin-top: 0.5rem;
}

.step-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

.was-validated .form-control:valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.44 1.44L7.88 4 7 3.06l-2.2 2.2-.94-.94z'/%3e%3c/svg%3e");
}
</style>
@endpush
