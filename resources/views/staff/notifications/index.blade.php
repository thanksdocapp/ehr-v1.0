@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Manage your notifications')

@section('content')
    <div class="card">
        <div class="doctor-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="doctor-doctor-card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Your Notifications
                </h5>
                <button class="btn btn-sm btn-primary" id="mark-all-read-btn">
                    <i class="fas fa-check-double me-1"></i>Mark All as Read
                </button>
            </div>
        </div>
        <div class="doctor-card-body">
            @if($paginator->count() > 0)
                <div class="notification-list">
                    @foreach($paginator as $notification)
                        <div class="notification-card {{ !$notification->is_read ? 'unread' : '' }}" data-notification-id="{{ $notification->notification_id ?? $notification->id }}">
                            <div class="d-flex align-items-start">
                                <div class="notification-icon me-3">
                                    <i class="{{ $notification->icon }} text-{{ $notification->type_color }}"></i>
                                </div>
                                <div class="notification-content flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="notification-title mb-1">{{ $notification->title }}</h6>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="notification-message mb-1">{{ $notification->message }}</p>
                                    <div class="notification-actions">
                                        @if($notification->url)
                                            <a href="{{ $notification->url }}" class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-external-link-alt me-1"></i>View
                                            </a>
                                        @endif
                                        @if(!$notification->is_system && !$notification->is_read)
                                            <button class="btn btn-sm btn-outline-success mark-as-read" data-id="{{ $notification->notification_id ?? $notification->id }}">
                                                <i class="fas fa-check me-1"></i>Mark as Read
                                            </button>
                                        @endif
                                        @if(!$notification->is_system)
                                            <button class="btn btn-sm btn-outline-danger delete-notification" data-id="{{ $notification->notification_id ?? $notification->id }}">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <hr class="my-3">
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">You have no notifications yet.</p>
                </div>
            @endif
        </div>
        @if($paginator->hasPages())
            <div class="card-footer">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
<style>
.notification-card {
    padding: 1rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
}

.notification-card.unread {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
}

.notification-card:hover {
    background-color: #f1f3f5;
}

.notification-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0, 123, 255, 0.1);
    border-radius: 50%;
    font-size: 1.2rem;
}

.notification-title {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.notification-message {
    font-size: 0.9rem;
    color: #6c757d;
    line-height: 1.4;
}

.notification-actions {
    margin-top: 0.5rem;
}

.notification-actions .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Mark all as read
        $('#mark-all-read-btn').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true);
            
            $.post("{{ route('staff.notifications.markAllAsRead') }}", function(data) {
                if (data.success) {
                    if (typeof showNotification === 'function') {
                        showNotification('All notifications marked as read!', 'success');
                    } else {
                        alert('All notifications marked as read!');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('Error marking notifications as read', 'error');
                    } else {
                        alert('Error marking notifications as read');
                    }
                    btn.prop('disabled', false);
                }
            }).fail(function() {
                if (typeof showNotification === 'function') {
                    showNotification('Error marking notifications as read', 'error');
                } else {
                    alert('Error marking notifications as read');
                }
                btn.prop('disabled', false);
            });
        });

        // Mark individual notification as read
        $(document).on('click', '.mark-as-read', function(e) {
            e.preventDefault();
            const btn = $(this);
            const notificationId = btn.data('id');
            
            console.log('Marking notification as read:', notificationId);
            btn.prop('disabled', true);
            
            $.post("{{ route('staff.notifications.markAsRead') }}", {
                id: notificationId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function(data) {
                console.log('Response:', data);
                if (data.success) {
                    const notificationCard = $('[data-notification-id="' + notificationId + '"]');
                    notificationCard.removeClass('unread');
                    btn.hide(); // Hide the mark as read button
                    
                    if (typeof showNotification === 'function') {
                        showNotification('Notification marked as read', 'success');
                    } else {
                        console.log('Notification marked as read');
                    }
                } else {
                    if (typeof showNotification === 'function') {
                        showNotification('Error marking notification as read', 'error');
                    } else {
                        alert('Error marking notification as read');
                    }
                    btn.prop('disabled', false);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                if (typeof showNotification === 'function') {
                    showNotification('Error marking notification as read', 'error');
                } else {
                    alert('Error marking notification as read: ' + error);
                }
                btn.prop('disabled', false);
            });
        });

        // Delete notification
        $(document).on('click', '.delete-notification', function(e) {
            e.preventDefault();
            const btn = $(this);
            const notificationId = btn.data('id');
            
            if (confirm('Are you sure you want to delete this notification?')) {
                btn.prop('disabled', true);
                
                $.ajax({
                    url: "{{ url('staff/notifications') }}/" + notificationId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (result.success) {
                            $('.notification-card[data-notification-id="' + notificationId + '"]').fadeOut();
                            if (typeof showNotification === 'function') {
                                showNotification('Notification deleted successfully', 'success');
                            } else {
                                console.log('Notification deleted successfully');
                            }
                        } else {
                            if (typeof showNotification === 'function') {
                                showNotification('Error deleting notification', 'error');
                            } else {
                                alert('Error deleting notification');
                            }
                            btn.prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Delete Error:', xhr.responseText);
                        if (typeof showNotification === 'function') {
                            showNotification('Error deleting notification', 'error');
                        } else {
                            alert('Error deleting notification: ' + error);
                        }
                        btn.prop('disabled', false);
                    }
                });
            }
        });
    });
</script>
@endpush
