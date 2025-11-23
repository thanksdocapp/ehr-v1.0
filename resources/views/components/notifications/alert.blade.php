@props([
    'type' => 'info', 
    'title' => '', 
    'message' => '', 
    'dismissible' => true,
    'icon' => '',
    'actionUrl' => '',
    'actionText' => 'View Details'
])

@php
    $iconClasses = [
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'danger' => 'fas fa-times-circle',
        'info' => 'fas fa-info-circle',
        'primary' => 'fas fa-bell',
        'appointment' => 'fas fa-calendar-check',
        'prescription' => 'fas fa-file-prescription',
        'lab_result' => 'fas fa-vial',
        'billing' => 'fas fa-file-invoice-dollar',
        'medical_record' => 'fas fa-file-medical',
    ];
    
    $alertIcon = $icon ?: ($iconClasses[$type] ?? 'fas fa-info-circle');
@endphp

<div class="alert alert-{{ $type }} alert-dismissible fade show hospital-notification-alert" role="alert">
    <div class="d-flex align-items-start">
        <div class="notification-alert-icon me-3">
            <i class="{{ $alertIcon }}"></i>
        </div>
        
        <div class="flex-grow-1">
            @if($title)
                <h6 class="alert-heading mb-1">{{ $title }}</h6>
            @endif
            
            <div class="notification-alert-message">
                {{ $message }}
            </div>
            
            @if($actionUrl)
                <div class="mt-2">
                    <a href="{{ $actionUrl }}" class="btn btn-sm btn-outline-{{ $type }}">
                        <i class="fas fa-arrow-right me-1"></i>{{ $actionText }}
                    </a>
                </div>
            @endif
        </div>
        
        @if($dismissible)
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
</div>

<style>
.hospital-notification-alert {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.notification-alert-icon {
    font-size: 1.5rem;
    opacity: 0.8;
    margin-top: 0.1rem;
}

.alert-heading {
    font-weight: 600;
    color: inherit;
}

.notification-alert-message {
    line-height: 1.5;
}

.hospital-notification-alert .btn-outline-success {
    color: #198754;
    border-color: #198754;
}

.hospital-notification-alert .btn-outline-warning {
    color: #fd7e14;
    border-color: #fd7e14;
}

.hospital-notification-alert .btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.hospital-notification-alert .btn-outline-info {
    color: #0dcaf0;
    border-color: #0dcaf0;
}

.hospital-notification-alert .btn-outline-primary {
    color: #0d6efd;
    border-color: #0d6efd;
}
</style>
