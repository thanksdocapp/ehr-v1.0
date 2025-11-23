@props(['notifications' => collect(), 'unreadCount' => 0, 'viewAllUrl' => '#'])

<div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-lg">
    <!-- Header -->
    <div class="dropdown-item noti-title">
        <h5 class="m-0">
            <span class="float-right">
                <a href="javascript:void(0)" class="text-dark" id="mark-all-read">
                    <small>Mark All Read</small>
                </a>
            </span>
            <i class="fas fa-bell me-1"></i> Notifications
            @if($unreadCount > 0)
                <span class="badge badge-danger ms-1">{{ $unreadCount }}</span>
            @endif
        </h5>
    </div>

    <div class="slimscroll" style="max-height: 300px;">
        @forelse($notifications as $notification)
            <a href="{{ $notification->action_url ?? 'javascript:void(0);' }}" 
               class="dropdown-item notify-item {{ $notification->is_read ? '' : 'unread-notification' }}" 
               data-notification-id="{{ $notification->id }}">
                
                <div class="notify-icon bg-{{ $notification->type_color }}">
                    <i class="{{ $notification->type_icon }}"></i>
                </div>
                
                <div class="notify-details">
                    <div class="notify-title">{{ $notification->title }}</div>
                    <p class="notify-desc mb-1">{{ Str::limit($notification->message, 60) }}</p>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    @if(!$notification->is_read)
                        <span class="badge badge-primary badge-sm ms-2">New</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="dropdown-item text-center text-muted py-3">
                <i class="fas fa-bell-slash fa-2x mb-2"></i>
                <p class="mb-0">No notifications yet</p>
            </div>
        @endforelse
    </div>

    <!-- View All -->
    @if($notifications->count() > 0)
        <a href="{{ $viewAllUrl }}" class="dropdown-item text-center text-primary notify-item notify-all">
            View all notifications
            <i class="fas fa-arrow-right ms-1"></i>
        </a>
    @endif
</div>

<style>
.notify-item {
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
}

.notify-item:hover {
    background-color: #f8f9fa;
    text-decoration: none;
}

.unread-notification {
    background-color: #f8f9ff;
    border-left: 3px solid #007bff;
}

.notify-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin-right: 12px;
    font-size: 16px;
    float: left;
}

.notify-details {
    margin-left: 55px;
}

.notify-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.notify-desc {
    color: #666;
    line-height: 1.4;
}

.noti-title {
    border-bottom: 1px solid #eee;
    padding: 15px 20px 10px;
    background-color: #fafafa;
}

.notify-all {
    border-top: 1px solid #eee;
    font-weight: 600;
    padding: 15px 20px;
}
</style>
