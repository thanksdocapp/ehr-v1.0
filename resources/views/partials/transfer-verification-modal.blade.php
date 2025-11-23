<!-- Transfer Code Verification Modal -->
<div class="modal fade" id="transferVerificationModal" tabindex="-1" aria-labelledby="transferVerificationModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <div class="d-flex align-items-center w-100">
                    <div class="verification-icon me-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="transferVerificationModalLabel">Security Verification Required</h5>
                        <small class="opacity-75" id="verificationCodeType">Transfer Code Verification</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <div class="verification-content text-center">
                    <!-- Security Icon -->
                    <div class="security-badge mb-4">
                        <div class="security-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="security-pulse"></div>
                    </div>
                    
                    <!-- Verification Message -->
                    <div class="verification-message mb-4">
                        <h6 class="text-dark mb-3" id="verificationTitle">Transfer Security Code Required</h6>
                        <p class="text-muted mb-0" id="verificationMessage">
                            The transfer code is required to enable you to continue with this transaction. 
                            Please contact our online customer care representative with the live chat or 
                            send us an email: they will help you with the appropriate code for this transaction.
                        </p>
                    </div>
                    
                    <!-- Code Input Form -->
                    <form id="transferCodeForm" class="verification-form">
                        <div class="mb-4">
                            <label for="transferCode" class="form-label fw-bold text-dark">Enter Verification Code</label>
                            <div class="code-input-group">
                                <input type="text" 
                                       class="form-control form-control-lg text-center" 
                                       id="transferCode" 
                                       name="transfer_code" 
                                       placeholder="••••••" 
                                       maxlength="6" 
                                       pattern="[0-9]{6}"
                                       required
                                       autocomplete="off">
                                <div class="input-overlay">
                                    <i class="fas fa-key"></i>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="transferCodeError"></div>
                            <small class="form-text text-muted">Enter the 6-digit security code provided by our support team</small>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="contact-info">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="contact-method">
                                        <div class="contact-icon">
                                            <i class="fas fa-comments"></i>
                                        </div>
                                        <div class="contact-details">
                                            <small class="text-muted d-block">Live Chat</small>
                                            <strong class="text-primary">Chat Support</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="contact-method">
                                        <div class="contact-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="contact-details">
                                            <small class="text-muted d-block">Email Us</small>
                                            <strong class="text-primary">support@globaltrustfinance.com</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel Transfer
                </button>
                <button type="button" class="btn btn-primary px-4" id="verifyCodeBtn">
                    <span class="btn-text">
                        <i class="fas fa-shield-alt me-2"></i>Verify & Continue
                    </span>
                    <span class="btn-loading d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Verifying...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Transfer Verification Modal Styles */
.modal-content {
    border-radius: 20px !important;
    overflow: hidden;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
}

.verification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.security-badge {
    position: relative;
    display: inline-block;
}

.security-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    position: relative;
    z-index: 2;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.security-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: translate(-50%, -50%);
    animation: pulse-security 2s infinite;
    opacity: 0.3;
    z-index: 1;
}

@keyframes pulse-security {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.3;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 0.1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1.4);
        opacity: 0;
    }
}

.verification-message {
    max-width: 400px;
    margin: 0 auto;
}

.verification-form {
    max-width: 300px;
    margin: 0 auto;
}

.code-input-group {
    position: relative;
}

.code-input-group .form-control {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-family: 'Courier New', monospace;
    font-size: 20px;
    font-weight: bold;
    letter-spacing: 8px;
    padding: 15px 20px;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.code-input-group .form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    background: white;
}

.code-input-group .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.input-overlay {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
    transition: all 0.3s ease;
}

.code-input-group .form-control:focus + .input-overlay {
    color: #667eea;
    transform: translateY(-50%) scale(1.1);
}

.contact-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 20px;
    margin-top: 20px;
}

.contact-method {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: 10px;
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
}

.contact-method:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.contact-details {
    flex: 1;
    text-align: left;
}

.contact-details strong {
    font-size: 12px;
    line-height: 1.2;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-outline-secondary {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
}

.btn-loading {
    display: none;
}

.btn-primary:disabled .btn-text {
    display: none;
}

.btn-primary:disabled .btn-loading {
    display: inline-block;
}

/* Responsive Design */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1rem;
    }
    
    .verification-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
    
    .security-icon {
        width: 60px;
        height: 60px;
        font-size: 24px;
    }
    
    .security-pulse {
        width: 60px;
        height: 60px;
    }
    
    .code-input-group .form-control {
        font-size: 18px;
        letter-spacing: 6px;
        padding: 12px 15px;
    }
    
    .contact-method {
        flex-direction: column;
        text-align: center;
        gap: 8px;
    }
    
    .contact-details {
        text-align: center;
    }
    
    .contact-details strong {
        font-size: 11px;
    }
}

/* Input Animation */
.code-input-group .form-control:focus {
    animation: code-input-focus 0.3s ease;
}

@keyframes code-input-focus {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.02);
    }
    100% {
        transform: scale(1);
    }
}

/* Success State */
.code-input-group .form-control.is-valid {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    background: #f8fff9;
}

.code-input-group .form-control.is-valid + .input-overlay {
    color: #28a745;
}

.code-input-group .form-control.is-valid + .input-overlay i::before {
    content: "\f058"; /* fa-check-circle */
}
</style>

<script>
// Transfer Code Verification Modal Handler
class TransferVerificationModal {
    constructor() {
        this.modal = document.getElementById('transferVerificationModal');
        this.modalInstance = new bootstrap.Modal(this.modal);
        this.codeInput = document.getElementById('transferCode');
        this.verifyBtn = document.getElementById('verifyCodeBtn');
        this.form = document.getElementById('transferCodeForm');
        this.currentCodeType = null;
        this.verificationCallback = null;
        
        this.init();
    }
    
    init() {
        // Bind form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.verifyCode();
        });
        
        // Bind verify button click
        this.verifyBtn.addEventListener('click', () => {
            this.verifyCode();
        });
        
        // Auto-format code input
        this.codeInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Only digits
            if (value.length > 6) value = value.substring(0, 6);
            e.target.value = value;
            
            // Clear validation states
            this.clearValidation();
            
            // Auto-submit when 6 digits entered
            if (value.length === 6) {
                setTimeout(() => this.verifyCode(), 500);
            }
        });
        
        // Clear form when modal is hidden
        this.modal.addEventListener('hidden.bs.modal', () => {
            this.resetForm();
        });
    }
    
    /**
     * Show verification modal
     * @param {Object} options - Configuration options
     */
    show(options = {}) {
        const config = {
            codeType: 'TRANSFER',
            title: 'Transfer Security Code Required',
            message: 'The transfer code is required to enable you to continue with this transaction. Please contact our online customer care representative with the live chat or send us an email: they will help you with the appropriate code for this transaction.',
            onVerify: null,
            ...options
        };
        
        // Set current code type
        this.currentCodeType = config.codeType;
        this.verificationCallback = config.onVerify;
        
        // Update modal content
        document.getElementById('verificationCodeType').textContent = `${config.codeType} Code Verification`;
        document.getElementById('verificationTitle').textContent = config.title;
        document.getElementById('verificationMessage').textContent = config.message;
        
        // Reset form
        this.resetForm();
        
        // Show modal
        this.modalInstance.show();
        
        // Focus on input after modal is shown
        setTimeout(() => {
            this.codeInput.focus();
        }, 300);
    }
    
    /**
     * Verify the entered code
     */
    async verifyCode() {
        const code = this.codeInput.value.trim();
        
        if (!this.validateCode(code)) {
            return;
        }
        
        // Show loading state
        this.setLoading(true);
        
        try {
            const response = await fetch('/api/transfer-codes/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    code: code
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Show success state
                this.showSuccess();
                
                // Call verification callback
                if (this.verificationCallback) {
                    setTimeout(() => {
                        this.verificationCallback(true, result);
                        this.hide();
                    }, 1000);
                } else {
                    setTimeout(() => this.hide(), 1500);
                }
            } else {
                this.showError(result.message || 'Invalid verification code. Please try again.');
            }
        } catch (error) {
            console.error('Verification error:', error);
            this.showError('Network error. Please check your connection and try again.');
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Validate the entered code
     */
    validateCode(code) {
        if (!code) {
            this.showError('Please enter the verification code.');
            return false;
        }
        
        if (code.length !== 6) {
            this.showError('Verification code must be 6 digits.');
            return false;
        }
        
        if (!/^\d{6}$/.test(code)) {
            this.showError('Verification code must contain only numbers.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Show loading state
     */
    setLoading(loading) {
        this.verifyBtn.disabled = loading;
        if (loading) {
            this.verifyBtn.querySelector('.btn-text').classList.add('d-none');
            this.verifyBtn.querySelector('.btn-loading').classList.remove('d-none');
        } else {
            this.verifyBtn.querySelector('.btn-text').classList.remove('d-none');
            this.verifyBtn.querySelector('.btn-loading').classList.add('d-none');
        }
    }
    
    /**
     * Show success state
     */
    showSuccess() {
        this.codeInput.classList.remove('is-invalid');
        this.codeInput.classList.add('is-valid');
        document.getElementById('transferCodeError').textContent = '';
        
        // Update button to show success
        this.verifyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Verified!';
        this.verifyBtn.classList.remove('btn-primary');
        this.verifyBtn.classList.add('btn-success');
    }
    
    /**
     * Show error state
     */
    showError(message) {
        this.codeInput.classList.remove('is-valid');
        this.codeInput.classList.add('is-invalid');
        document.getElementById('transferCodeError').textContent = message;
        
        // Shake animation
        this.codeInput.style.animation = 'shake 0.5s ease-in-out';
        setTimeout(() => {
            this.codeInput.style.animation = '';
        }, 500);
    }
    
    /**
     * Clear validation states
     */
    clearValidation() {
        this.codeInput.classList.remove('is-invalid', 'is-valid');
        document.getElementById('transferCodeError').textContent = '';
    }
    
    /**
     * Reset form to initial state
     */
    resetForm() {
        this.codeInput.value = '';
        this.clearValidation();
        this.setLoading(false);
        
        // Reset button
        this.verifyBtn.innerHTML = '<span class="btn-text"><i class="fas fa-shield-alt me-2"></i>Verify & Continue</span><span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Verifying...</span>';
        this.verifyBtn.classList.remove('btn-success');
        this.verifyBtn.classList.add('btn-primary');
        this.verifyBtn.disabled = false;
    }
    
    /**
     * Hide modal
     */
    hide() {
        this.modalInstance.hide();
    }
}

// Shake animation for error state
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}
`;
document.head.appendChild(shakeStyle);

// Initialize modal when DOM is ready
let transferVerificationModal;
document.addEventListener('DOMContentLoaded', function() {
    transferVerificationModal = new TransferVerificationModal();
});

/**
 * Global function to show transfer verification modal
 * Usage: showTransferVerification({ codeType: 'COT', onVerify: callback })
 */
function showTransferVerification(options = {}) {
    if (transferVerificationModal) {
        transferVerificationModal.show(options);
    }
}
</script>
