@extends('patient.layouts.app')

@section('title', 'My Notifications')
@section('page-title', 'My Notifications')

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Your Health Notifications
                </h5>
@php
    $hasUnreadNotifications = $notifications->where('is_read', false)->count() > 0;
@endphp
                @if($hasUnreadNotifications)
                    <button class="btn btn-sm btn-primary" id="mark-all-read-btn">
                        <i class="fas fa-check-double me-1"></i>Mark All as Read
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <x-notifications.list :notifications="$notifications" />
            @else
                <div class="text-center py-5">
                    <div class="stat-icon bg-info-gradient mb-3 mx-auto" style="width: 80px; height: 80px;">
                        <i class="fas fa-bell-slash fa-2x"></i>
                    </div>
                    <h5>No Notifications Yet</h5>
                    <p class="text-muted">You'll receive notifications about your appointments, lab results, prescriptions, and more.</p>
                </div>
            @endif
        </div>
        @if($notifications->hasPages())
            <div class="card-footer">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-danger text-white">
                    <div class="d-flex align-items-center">
                        <div class="modal-icon me-3">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0" id="deleteConfirmModalLabel">Delete Notification</h5>
                            <small class="opacity-75">This action cannot be undone</small>
                        </div>
                    </div>
                </div>
                <div class="modal-body px-4 py-4">
                    <div class="text-center mb-4">
                        <div class="delete-icon mb-3">
                            <i class="fas fa-trash-alt fa-3x text-danger opacity-75"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">Are you sure you want to delete this notification?</h6>
                        <p class="text-muted mb-0">This notification will be permanently removed and cannot be recovered.</p>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-2"></i>Delete Notification
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* Beautiful Modal Styles */
#deleteConfirmModal .modal-content {
    border-radius: 20px;
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

#deleteConfirmModal .modal-header {
    border-radius: 20px 20px 0 0;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    padding: 1.5rem;
}

#deleteConfirmModal .modal-icon {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: iconPulse 2s infinite;
}

@keyframes iconPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

#deleteConfirmModal .delete-icon {
    animation: trashShake 0.8s ease-in-out infinite;
}

@keyframes trashShake {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-2deg); }
    75% { transform: rotate(2deg); }
}

#deleteConfirmModal .btn {
    border-radius: 12px;
    font-weight: 600;
    padding: 0.75rem 2rem;
    transition: all 0.3s ease;
    border: none;
}

#deleteConfirmModal .btn-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

#deleteConfirmModal .btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

#deleteConfirmModal .btn-secondary {
    background: #6c757d;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.2);
}

#deleteConfirmModal .btn-secondary:hover {
    transform: translateY(-2px);
    background: #5a6268;
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3);
}

/* Deleting Animation */
.notification-card.deleting {
    animation: deleteSlideOut 0.5s ease-out forwards;
    pointer-events: none;
}

@keyframes deleteSlideOut {
    0% {
        transform: translateX(0);
        opacity: 1;
    }
    100% {
        transform: translateX(50px);
        opacity: 0;
    }
}

/* Modal Backdrop */
#deleteConfirmModal .modal-backdrop {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
}

/* Success/Error Alerts Animation */
.alert {
    animation: alertSlideIn 0.3s ease-out;
    border: none;
    border-radius: 12px;
}

@keyframes alertSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
    // Ensure jQuery is loaded
    function initializeNotifications() {
        console.log('Initializing patient notifications...');
        
        // Mark all as read functionality
        $(document).off('click', '#mark-all-read-btn').on('click', '#mark-all-read-btn', function(e) {
            e.preventDefault();
            console.log('Mark all as read button clicked');
            
            const button = $(this);
            const originalHtml = button.html();
            
            // Disable button and show loading
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Processing...');
            
            // Make AJAX request
            $.ajax({
                url: "{{ route('patient.notifications.markAllAsRead') }}",
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 10000, // 10 second timeout
                success: function(data) {
                    console.log('Mark all as read response:', data);
                    
                    if (data && data.success) {
                        // Show success message
                        const alert = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>${data.message || 'All notifications marked as read successfully!'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                        $('.card-body').prepend(alert);
                        
                        // Remove unread styling
                        $('.notification-card.unread').removeClass('unread');
                        $('.badge.bg-primary, .badge:contains("New")').remove();
                        
                        // Hide the button with animation
                        button.fadeOut(300, function() {
                            button.remove();
                        });
                        
                        // Update notification count in header if exists
                        if ($('.notification-count, #patientNotificationCount').length) {
                            $('.notification-count, #patientNotificationCount').text('0').hide();
                        }
                        
                        // Auto-hide success message after 5 seconds
                        setTimeout(function() {
                            $('.alert-success').fadeOut();
                        }, 30000);
                    } else {
                        console.error('Server returned success=false:', data);
                        alert('Failed to mark notifications as read. Server error.');
                        button.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    
                    let errorMessage = 'Error marking notifications as read. Please try again.';
                    if (xhr.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page and try again.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'You do not have permission to perform this action.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please contact support if this continues.';
                    }
                    
                    alert(errorMessage);
                    button.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Mark individual notification as read (using event delegation)
        $(document).on('click', '.mark-as-read', function(e) {
            e.preventDefault();
            const notificationId = $(this).data('id');
            const notificationCard = $('.notification-card[data-notification-id=' + notificationId + ']');
            
            $.ajax({
                url: "{{ route('patient.notifications.markAsRead') }}",
                type: 'POST',
                data: {
                    id: notificationId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        notificationCard.removeClass('unread');
                        notificationCard.find('.badge.bg-primary').remove();
                        notificationCard.find('.mark-as-read').closest('li').remove();
                        
                        // Update notification count
                        const unreadCount = $('.notification-card.unread').length;
                        if (unreadCount === 0) {
                            $('#mark-all-read-btn').fadeOut();
                        }
                    } else {
                        alert('Failed to mark notification as read. Please try again.');
                    }
                },
                error: function(xhr) {
                    console.error('Error marking notification as read:', xhr);
                    alert('Error marking notification as read. Please try again.');
                }
            });
        });

        // Store current notification data for modal
        let currentNotificationId = null;
        let currentDeleteButton = null;
        
        // Delete notification with confirmation (using event delegation)
        $(document).on('click', '.delete-notification', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Delete button clicked - MODAL VERSION');
            
            const button = $(this);
            const notificationId = button.attr('data-id') || button.data('id');
            
            if (!notificationId) {
                console.error('No notification ID found on button!');
                alert('Error: Could not find notification ID');
                return false;
            }
            
            // Store current data for modal confirmation
            currentNotificationId = notificationId;
            currentDeleteButton = button;
            
            // Show beautiful confirmation modal
            const deleteModal = new bootstrap.Modal('#deleteConfirmModal');
            deleteModal.show();
            
            return false;
        });
        
        // Handle modal confirmation
        $(document).on('click', '#confirmDeleteBtn', function(e) {
            e.preventDefault();
            
            if (!currentNotificationId || !currentDeleteButton) {
                console.error('No current notification data found!');
                return;
            }
            
            console.log('Modal confirmed deletion for ID:', currentNotificationId);
            
            const notificationCard = $(`.notification-card[data-notification-id="${currentNotificationId}"]`);
            const button = currentDeleteButton;
            const originalText = button.html();
            
            // Close modal
            const deleteModal = bootstrap.Modal.getInstance('#deleteConfirmModal');
            deleteModal.hide();
            
            // Show loading state on button
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Deleting...');
            
            // Make delete request
            $.ajax({
                url: `/patient/notifications/${currentNotificationId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                success: function(response) {
                    console.log('Delete success response:', response);
                    if (response && response.success) {
                        // Show success animation
                        notificationCard.addClass('deleting');
                        
                        setTimeout(() => {
                            notificationCard.fadeOut(400, function() {
                                $(this).remove();
                                
                                // Check if page should be reloaded
                                if ($('.notification-card').length === 0) {
                                    location.reload();
                                } else {
                                    // Update unread count
                                    const unreadCount = $('.notification-card.unread').length;
                                    if (unreadCount === 0) {
                                        $('#mark-all-read-btn').fadeOut();
                                    }
                                }
                            });
                        }, 500);
                        
                        // Show success message
                        const successAlert = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>Notification deleted successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        $('.card-body').prepend(successAlert);
                        
                        // Auto-hide success message
                        setTimeout(() => {
                            $('.alert-success').fadeOut();
                        }, 3000);
                    } else {
                        button.prop('disabled', false).html(originalText);
                        alert('Failed to delete notification. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete AJAX error:', {
                        xhr: xhr,
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    let message = 'Error deleting notification. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    
                    button.prop('disabled', false).html(originalText);
                    
                    // Show error alert
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.card-body').prepend(errorAlert);
                    
                    // Auto-hide error message
                    setTimeout(() => {
                        $('.alert-danger').fadeOut();
                    }, 5000);
                }
            });
            
            // Reset current data
            currentNotificationId = null;
            currentDeleteButton = null;
        });
        
        // Reset modal data when closed
        $('#deleteConfirmModal').on('hidden.bs.modal', function() {
            currentNotificationId = null;
            currentDeleteButton = null;
        });
        
        // Handle action buttons in notifications
        $(document).on('click', '.notification-action-btn', function(e) {
            const notificationId = $(this).closest('.notification-card').data('notification-id');
            if (notificationId) {
                // Mark as read when action is clicked
                $.post("{{ route('patient.notifications.markAsRead') }}", {
                    id: notificationId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                });
            }
        });
    }
    
    // Function to wait for jQuery to load
    function waitForJQuery(callback, maxAttempts = 50, attempt = 1) {
        if (typeof $ !== 'undefined') {
            console.log('jQuery found, initializing notifications...');
            callback();
        } else if (attempt < maxAttempts) {
            console.log('Waiting for jQuery... attempt', attempt);
            setTimeout(function() {
                waitForJQuery(callback, maxAttempts, attempt + 1);
            }, 100);
        } else {
            console.error('jQuery not loaded after', maxAttempts, 'attempts');
            // Fallback: load jQuery manually
            loadJQueryManually(callback);
        }
    }
    
    // Function to manually load jQuery
    function loadJQueryManually(callback) {
        console.log('Loading jQuery manually...');
        const script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
        script.onload = function() {
            console.log('jQuery loaded successfully');
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            callback();
        };
        script.onerror = function() {
            console.error('Failed to load jQuery from CDN');
        };
        document.head.appendChild(script);
    }
    
    // Initialize notifications when everything is ready
    function initializeWhenReady() {
        console.log('Initializing notifications regardless of mark-all-read button...');
        initializeNotifications();
        
        // Test delete button exists
        const deleteButtons = $('.delete-notification');
        console.log('Found', deleteButtons.length, 'delete buttons');
        if (deleteButtons.length > 0) {
            console.log('First delete button data-id:', deleteButtons.first().attr('data-id'));
        }
    }
    
    // Start the initialization process
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            waitForJQuery(initializeWhenReady);
        });
    } else {
        waitForJQuery(initializeWhenReady);
    }
</script>
@endpush
