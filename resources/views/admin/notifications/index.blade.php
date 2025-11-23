@extends('admin.layouts.app')

@section('title', 'All Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('content')
{{-- Error Display --}}
@if(isset($error))
    <div class="alert alert-danger">
        <strong>Error:</strong> {{ $error }}
    </div>
@endif

@php
    $notificationCount = $notifications ? ($notifications->total() ?? $notifications->count()) : 0;
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2 text-primary"></i>
                    All Notifications
                    <span class="badge bg-secondary ms-2">{{ $notificationCount }}</span>
                </h5>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" id="mark-all-read-btn">
                        <i class="fas fa-check-double me-1"></i>
                        Mark All as Read
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.location.reload()">
                        <i class="fas fa-sync me-1"></i>
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        <tr class="{{ !$notification->is_read ? 'bg-light' : '' }}" data-notification-id="{{ $notification->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $typeColor = $notification->type_color ?? 'secondary';
                                        $typeIcon = $notification->type_icon ?? 'fas fa-bell';
                                    @endphp
                                    <span class="badge bg-{{ $typeColor }} text-white">
                                        <i class="{{ $typeIcon }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $notification->type ?? 'notification')) }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ $notification->title ?? 'No Title' }}</td>
                            <td>{{ Str::limit($notification->message ?? 'No Message', 80) }}</td>
                            <td>
                                @if($notification->is_read)
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-check me-1"></i>Read
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-circle me-1"></i>Unread
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-muted">
                                    {{ $notification->created_at->diffForHumans() }}
                                    <br>
                                    {{ $notification->created_at->format('M j, Y g:i A') }}
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    @php
                                        // Check if this is a real UserNotification (has user_id) or system notification
                                        $isUserNotification = isset($notification->user_id);
                                        $isSystemNotification = in_array($notification->id ?? '', ['pending_appointments', 'new_patients', 'pending_bills', 'new_lab_reports']);
                                    @endphp
                                    
                                    @if($isUserNotification && !$notification->is_read)
                                        <button class="btn btn-sm btn-outline-primary mark-read-btn" data-id="{{ $notification->id }}" title="Mark as read">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                    
                                    @if($notification->action_url)
                                        <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-info" title="View details">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    @endif
                                    
                                    @if($isUserNotification)
                                        <button class="btn btn-sm btn-outline-danger delete-notification" data-id="{{ $notification->id }}" title="Delete notification">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <span class="btn btn-sm btn-outline-secondary disabled" title="System notifications cannot be deleted">
                                            <i class="fas fa-info-circle"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div>
                                    <i class="fas fa-bell-slash text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5 class="mt-3 text-muted">No Notifications</h5>
                                    <p class="text-muted">You don't have any notifications yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-white">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mark all as read
    $('#mark-all-read-btn').on('click', function() {
        if (!confirm('Are you sure you want to mark all user notifications as read? System notifications will remain visible.')) {
            return;
        }
        
        const button = $(this);
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Marking as read...');
        
        $.ajax({
            url: "{{ contextRoute('notifications.mark-all-read') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    // Show success message
                    if (data.updated_count > 0) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert('No user notifications found to mark as read. System notifications cannot be marked as read.');
                        button.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i>Mark All as Read');
                    }
                } else {
                    alert('Failed to mark notifications as read');
                    button.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i>Mark All as Read');
                }
            },
            error: function() {
                alert('Error occurred while marking notifications as read');
                button.prop('disabled', false).html('<i class="fas fa-check-double me-1"></i>Mark All as Read');
            }
        });
    });

    // Mark single notification as read
    $('.mark-read-btn').on('click', function() {
        const notificationId = $(this).data('id');
        $.post("{{ contextRoute('notifications.mark-read', '') }}/" + notificationId, function(data) {
            if (data.success) {
                window.location.reload();
            }
        });
    });

    // Delete notification
    $('.delete-notification').on('click', function() {
        const notificationId = $(this).data('id');
        if (confirm('Are you sure you want to delete this notification?')) {
            $.ajax({
                url: "{{ contextRoute('notifications.index') }}/" + notificationId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(result) {
                    if (result.success) {
                        $(`tr[data-notification-id=${notificationId}]`).fadeOut(function() {
                            $(this).remove();
                            if ($('tbody tr').length === 0) {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        }
    });
});
</script>
@endpush
