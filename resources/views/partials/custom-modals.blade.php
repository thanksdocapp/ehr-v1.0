{{-- Custom Alert Modal --}}
<div class="modal fade" id="customAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center w-100">
                    <div class="modal-icon me-3" id="alertModalIcon">
                        <i class="fas fa-info-circle text-primary"></i>
                    </div>
                    <h5 class="modal-title flex-grow-1" id="alertModalTitle">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0" id="alertModalMessage">This is a notification message.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="alertModalOkBtn">
                    <i class="fas fa-check me-2"></i>OK
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Custom Confirmation Modal --}}
<div class="modal fade" id="customConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center w-100">
                    <div class="modal-icon me-3" id="confirmModalIcon">
                        <i class="fas fa-question-circle text-warning"></i>
                    </div>
                    <h5 class="modal-title flex-grow-1" id="confirmModalTitle">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0" id="confirmModalMessage">Are you sure you want to perform this action?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="confirmModalCancelBtn">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmModalConfirmBtn">
                    <i class="fas fa-check me-2"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Custom Toast Container --}}
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <!-- Toasts will be dynamically added here -->
</div>

<style>
/* Custom Modal Styles */
.modal-content {
    border-radius: 16px;
    overflow: hidden;
}

.modal-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(var(--bs-primary-rgb), 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.modal-icon.success {
    background: rgba(var(--bs-success-rgb), 0.1);
}

.modal-icon.success i {
    color: var(--bs-success) !important;
}

.modal-icon.danger {
    background: rgba(var(--bs-danger-rgb), 0.1);
}

.modal-icon.danger i {
    color: var(--bs-danger) !important;
}

.modal-icon.warning {
    background: rgba(var(--bs-warning-rgb), 0.1);
}

.modal-icon.warning i {
    color: var(--bs-warning) !important;
}

.modal-icon.info {
    background: rgba(var(--bs-info-rgb), 0.1);
}

.modal-icon.info i {
    color: var(--bs-info) !important;
}

/* Custom Toast Styles */
.custom-toast {
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    backdrop-filter: blur(10px);
    margin-bottom: 12px;
    overflow: hidden;
}

.custom-toast .toast-header {
    border-bottom: none;
    padding: 16px 20px 8px;
    background: transparent;
}

.custom-toast .toast-body {
    padding: 8px 20px 16px;
    font-size: 14px;
    line-height: 1.5;
}

.custom-toast.toast-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    border-left: 4px solid var(--bs-success);
}

.custom-toast.toast-error {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    border-left: 4px solid var(--bs-danger);
}

.custom-toast.toast-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-left: 4px solid var(--bs-warning);
}

.custom-toast.toast-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    border-left: 4px solid var(--bs-info);
}

.toast-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    margin-right: 8px;
}

.toast-success .toast-icon {
    background: var(--bs-success);
    color: white;
}

.toast-error .toast-icon {
    background: var(--bs-danger);
    color: white;
}

.toast-warning .toast-icon {
    background: var(--bs-warning);
    color: white;
}

.toast-info .toast-icon {
    background: var(--bs-info);
    color: white;
}

/* Animation for modals */
.modal.fade .modal-dialog {
    transform: translate(0, -50px) scale(0.9);
    transition: all 0.3s ease;
}

.modal.show .modal-dialog {
    transform: translate(0, 0) scale(1);
}

/* Button hover effects */
.modal-footer .btn {
    border-radius: 8px;
    padding: 10px 24px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.modal-footer .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>

<script>
// Custom Alert Function
function customAlert(message, type = 'info', title = null) {
    const modal = new bootstrap.Modal(document.getElementById('customAlertModal'));
    const iconElement = document.getElementById('alertModalIcon');
    const titleElement = document.getElementById('alertModalTitle');
    const messageElement = document.getElementById('alertModalMessage');
    const okBtn = document.getElementById('alertModalOkBtn');

    // Set icon and colors based on type
    iconElement.className = '';
    iconElement.classList.add('modal-icon', 'me-3', type);
    
    let icon, defaultTitle, btnClass;
    switch(type) {
        case 'success':
            icon = 'fas fa-check-circle';
            defaultTitle = 'Success';
            btnClass = 'btn-success';
            break;
        case 'error':
        case 'danger':
            icon = 'fas fa-exclamation-circle';
            defaultTitle = 'Error';
            btnClass = 'btn-danger';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            defaultTitle = 'Warning';
            btnClass = 'btn-warning';
            break;
        default:
            icon = 'fas fa-info-circle';
            defaultTitle = 'Information';
            btnClass = 'btn-primary';
    }

    iconElement.innerHTML = `<i class="${icon}"></i>`;
    titleElement.textContent = title || defaultTitle;
    messageElement.textContent = message;
    
    okBtn.className = `btn ${btnClass}`;
    
    modal.show();
    
    return new Promise((resolve) => {
        const handleClose = () => {
            modal.hide();
            resolve(true);
            okBtn.removeEventListener('click', handleClose);
            document.getElementById('customAlertModal').removeEventListener('hidden.bs.modal', handleClose);
        };
        
        okBtn.addEventListener('click', handleClose);
        document.getElementById('customAlertModal').addEventListener('hidden.bs.modal', handleClose);
    });
}

// Custom Confirm Function
function customConfirm(message, type = 'warning', title = null, confirmText = 'Confirm', cancelText = 'Cancel') {
    const modal = new bootstrap.Modal(document.getElementById('customConfirmModal'));
    const iconElement = document.getElementById('confirmModalIcon');
    const titleElement = document.getElementById('confirmModalTitle');
    const messageElement = document.getElementById('confirmModalMessage');
    const confirmBtn = document.getElementById('confirmModalConfirmBtn');
    const cancelBtn = document.getElementById('confirmModalCancelBtn');

    // Set icon and colors based on type
    iconElement.className = '';
    iconElement.classList.add('modal-icon', 'me-3', type);
    
    let icon, defaultTitle, btnClass;
    switch(type) {
        case 'danger':
        case 'delete':
            icon = 'fas fa-trash-alt';
            defaultTitle = 'Delete Confirmation';
            btnClass = 'btn-danger';
            break;
        case 'warning':
            icon = 'fas fa-exclamation-triangle';
            defaultTitle = 'Warning';
            btnClass = 'btn-warning';
            break;
        case 'success':
            icon = 'fas fa-check-circle';
            defaultTitle = 'Confirm Action';
            btnClass = 'btn-success';
            break;
        default:
            icon = 'fas fa-question-circle';
            defaultTitle = 'Confirm Action';
            btnClass = 'btn-primary';
    }

    iconElement.innerHTML = `<i class="${icon}"></i>`;
    titleElement.textContent = title || defaultTitle;
    messageElement.textContent = message;
    confirmBtn.textContent = confirmText;
    cancelBtn.textContent = cancelText;
    
    confirmBtn.className = `btn ${btnClass}`;
    
    modal.show();
    
    return new Promise((resolve) => {
        const handleConfirm = () => {
            modal.hide();
            resolve(true);
            cleanup();
        };
        
        const handleCancel = () => {
            modal.hide();
            resolve(false);
            cleanup();
        };
        
        const cleanup = () => {
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', handleCancel);
            document.getElementById('customConfirmModal').removeEventListener('hidden.bs.modal', handleCancel);
        };
        
        confirmBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', handleCancel);
        document.getElementById('customConfirmModal').addEventListener('hidden.bs.modal', handleCancel);
    });
}

// Custom Toast Function
function showToast(message, type = 'info', title = null, duration = 5000) {
    const toastContainer = document.querySelector('.toast-container');
    const toastId = 'toast-' + Date.now();
    
    let icon, defaultTitle;
    switch(type) {
        case 'success':
            icon = 'fas fa-check';
            defaultTitle = 'Success';
            break;
        case 'error':
        case 'danger':
            icon = 'fas fa-times';
            defaultTitle = 'Error';
            break;
        case 'warning':
            icon = 'fas fa-exclamation';
            defaultTitle = 'Warning';
            break;
        default:
            icon = 'fas fa-info';
            defaultTitle = 'Info';
    }

    const toastHTML = `
        <div class="toast custom-toast toast-${type}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <div class="toast-icon">
                    <i class="${icon}"></i>
                </div>
                <strong class="me-auto">${title || defaultTitle}</strong>
                <small class="text-muted">now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        delay: duration
    });
    
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
    
    return toast;
}

// Override browser's alert and confirm functions
window.alert = function(message) {
    return customAlert(message, 'info');
};

window.confirm = function(message) {
    return customConfirm(message, 'warning');
};

// Global function to replace showAlert function
window.showAlert = function(type, message, title = null) {
    return showToast(message, type, title);
};

// Global function for notifications (alias for showToast)
window.showNotification = function(message, type = 'info', title = null) {
    return showToast(message, type, title);
};
</script>
