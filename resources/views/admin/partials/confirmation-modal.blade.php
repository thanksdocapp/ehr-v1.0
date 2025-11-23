<!-- Universal Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center w-100">
                    <div class="modal-icon me-3" id="modalIcon">
                        <i class="fas fa-question-circle text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0" id="confirmationModalLabel">Confirm Action</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body pt-2">
                <div class="confirmation-content">
                    <p class="mb-0 text-muted" id="confirmationMessage">Are you sure you want to perform this action?</p>
                    <div class="mt-3" id="confirmationDetails" style="display: none;">
                        <div class="alert alert-warning border-0 bg-light">
                            <small id="confirmationDetailsText"></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary px-4" id="confirmAction">
                    <i class="fas fa-check me-2"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal-content {
    border-radius: 15px !important;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
}

.modal-body {
    padding: 1.5rem;
    background: white;
}

.modal-footer {
    background: white;
    padding: 1.5rem;
}

.modal-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 193, 7, 0.1);
}

.modal-icon.danger {
    background: rgba(220, 53, 69, 0.1);
}

.modal-icon.success {
    background: rgba(40, 167, 69, 0.1);
}

.modal-icon.info {
    background: rgba(23, 162, 184, 0.1);
}

.btn-primary {
    background: linear-gradient(45deg, #1a1a2e, #16213e);
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(26, 26, 46, 0.3);
}

.btn-outline-secondary {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
    transform: translateY(-2px);
}

.confirmation-modal-backdrop {
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

/* Animation for modal entrance */
.modal.fade .modal-dialog {
    transform: translate(0, -50px) scale(0.9);
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: translate(0, 0) scale(1);
}

/* Pulse animation for dangerous actions */
@keyframes pulse-danger {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}

.btn-danger-pulse {
    animation: pulse-danger 2s infinite;
}
</style>

<script>
/**
 * Universal Confirmation Modal Handler
 * Replaces browser's default confirm() with beautiful custom modal
 */
class ConfirmationModal {
    constructor() {
        this.modal = document.getElementById('confirmationModal');
        this.modalInstance = new bootstrap.Modal(this.modal);
        this.confirmButton = document.getElementById('confirmAction');
        this.modalIcon = document.getElementById('modalIcon');
        this.modalTitle = document.getElementById('confirmationModalLabel');
        this.modalMessage = document.getElementById('confirmationMessage');
        this.modalDetails = document.getElementById('confirmationDetails');
        this.modalDetailsText = document.getElementById('confirmationDetailsText');
        
        this.currentCallback = null;
        
        // Bind confirm button click
        this.confirmButton.addEventListener('click', () => {
            if (this.currentCallback) {
                this.currentCallback();
            }
            this.hide();
        });
    }
    
    /**
     * Show confirmation modal
     * @param {Object} options - Configuration options
     */
    show(options = {}) {
        const config = {
            title: 'Confirm Action',
            message: 'Are you sure you want to perform this action?',
            details: null,
            type: 'warning', // warning, danger, success, info
            confirmText: 'Confirm',
            confirmClass: 'btn-primary',
            onConfirm: null,
            ...options
        };
        
        // Set title
        this.modalTitle.textContent = config.title;
        
        // Set message
        this.modalMessage.textContent = config.message;
        
        // Set details if provided
        if (config.details) {
            this.modalDetailsText.textContent = config.details;
            this.modalDetails.style.display = 'block';
        } else {
            this.modalDetails.style.display = 'none';
        }
        
        // Set icon and styling based on type
        this.setModalType(config.type);
        
        // Set confirm button text and class
        this.confirmButton.innerHTML = `<i class="fas fa-check me-2"></i>${config.confirmText}`;
        this.confirmButton.className = `btn px-4 ${config.confirmClass}`;
        
        // Add pulse animation for dangerous actions
        if (config.type === 'danger') {
            this.confirmButton.classList.add('btn-danger-pulse');
        } else {
            this.confirmButton.classList.remove('btn-danger-pulse');
        }
        
        // Set callback
        this.currentCallback = config.onConfirm;
        
        // Show modal
        this.modalInstance.show();
    }
    
    /**
     * Hide modal
     */
    hide() {
        this.modalInstance.hide();
        this.currentCallback = null;
    }
    
    /**
     * Set modal type and styling
     * @param {string} type - Modal type (warning, danger, success, info)
     */
    setModalType(type) {
        // Reset icon classes
        this.modalIcon.className = 'modal-icon me-3';
        
        switch (type) {
            case 'danger':
                this.modalIcon.classList.add('danger');
                this.modalIcon.innerHTML = '<i class="fas fa-exclamation-triangle text-danger" style="font-size: 2rem;"></i>';
                break;
            case 'success':
                this.modalIcon.classList.add('success');
                this.modalIcon.innerHTML = '<i class="fas fa-check-circle text-success" style="font-size: 2rem;"></i>';
                break;
            case 'info':
                this.modalIcon.classList.add('info');
                this.modalIcon.innerHTML = '<i class="fas fa-info-circle text-info" style="font-size: 2rem;"></i>';
                break;
            default: // warning
                this.modalIcon.innerHTML = '<i class="fas fa-question-circle text-warning" style="font-size: 2rem;"></i>';
        }
    }
}

// Initialize global confirmation modal
let confirmModal;

document.addEventListener('DOMContentLoaded', function() {
    confirmModal = new ConfirmationModal();
});

/**
 * Global function to show confirmation modal
 * Replaces the standard confirm() function
 */
function showConfirmation(options) {
    return new Promise((resolve) => {
        if (typeof options === 'string') {
            options = { message: options };
        }
        
        confirmModal.show({
            ...options,
            onConfirm: () => resolve(true)
        });
        
        // Handle modal close/cancel as rejection
        const modal = document.getElementById('confirmationModal');
        const handleCancel = () => {
            resolve(false);
            modal.removeEventListener('hidden.bs.modal', handleCancel);
        };
        modal.addEventListener('hidden.bs.modal', handleCancel);
    });
}

/**
 * Predefined confirmation types for common actions
 */
window.confirmations = {
    delete: (itemName = 'item') => showConfirmation({
        title: 'Delete Confirmation',
        message: `Are you sure you want to delete this ${itemName}?`,
        details: 'This action cannot be undone.',
        type: 'danger',
        confirmText: 'Delete',
        confirmClass: 'btn-danger'
    }),
    
    toggleStatus: (currentStatus = 'status') => showConfirmation({
        title: 'Change Status',
        message: `Are you sure you want to toggle this user's status?`,
        details: `The user's status will be changed from ${currentStatus}.`,
        type: 'warning',
        confirmText: 'Change Status',
        confirmClass: 'btn-warning'
    }),
    
    loginAsUser: () => showConfirmation({
        title: 'Login as User',
        message: 'Are you sure you want to login as this user?',
        details: 'This will log you out from the admin panel.',
        type: 'info',
        confirmText: 'Login as User',
        confirmClass: 'btn-info'
    }),
    
    bulkAction: (action, count) => showConfirmation({
        title: `Bulk ${action.charAt(0).toUpperCase() + action.slice(1)}`,
        message: `Are you sure you want to ${action} ${count} selected item(s)?`,
        details: action === 'delete' ? 'This action cannot be undone.' : null,
        type: action === 'delete' ? 'danger' : 'warning',
        confirmText: `${action.charAt(0).toUpperCase() + action.slice(1)}`,
        confirmClass: action === 'delete' ? 'btn-danger' : 'btn-warning'
    })
};
</script>
