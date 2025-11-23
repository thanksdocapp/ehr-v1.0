@props(['notification'])

<div class="card notification-card {{ $notification->is_read ? '' : 'unread' }}" data-notification-id="{{ $notification->id }}">
    <div class="card-body">
        <div class="d-flex align-items-start">
            <div class="notification-icon me-3">
                <div class="icon-circle bg-{{ $notification->type_color }}">
                    <i class="{{ $notification->type_icon }}"></i>
                </div>
            </div>
            
            <div class="notification-content flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="notification-title mb-0">{{ $notification->title }}</h6>
                    <div class="notification-actions">
                        @if(!$notification->is_read)
                            <span class="badge bg-primary me-2">New</span>
                        @endif
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                @if(!$notification->is_read)
                                    <li><button class="dropdown-item mark-as-read" type="button" data-id="{{ $notification->id }}">
                                        <i class="fas fa-check me-2"></i>Mark as Read
                                    </button></li>
                                @endif
                                @if($notification->action_url)
                                    <li><a class="dropdown-item" href="{{ $notification->action_url }}">
                                        <i class="fas fa-external-link-alt me-2"></i>View Details
                                    </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item text-danger delete-notification" type="button" data-id="{{ $notification->id }}">
                                    <i class="fas fa-trash me-2"></i>Delete
                                </button></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <p class="notification-message text-muted mb-2">{{ $notification->message }}</p>
                
                <div class="notification-meta d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        {{ $notification->created_at->diffForHumans() }}
                    </small>
                    
                    @if($notification->category)
                        <span class="badge bg-light text-dark">{{ ucfirst($notification->category) }}</span>
                    @endif
                </div>
                
                @if($notification->action_url)
                    <div class="mt-2">
                        <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary notification-action-btn">
                            <i class="fas fa-arrow-right me-1"></i>Take Action
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.notification-card {
    margin-bottom: 1rem;
    border: 1px solid #e3e6f0;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.notification-card:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transform: translateY(-2px);
}

.notification-card.unread {
    border-left: 4px solid #007bff;
    background-color: #f8f9ff;
}

.notification-icon .icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.notification-title {
    color: #333;
    font-weight: 600;
}

.notification-message {
    line-height: 1.5;
}

.notification-actions .dropdown-toggle {
    border: none;
    background: none;
    color: #6c757d;
}

.notification-actions .dropdown-toggle:focus {
    box-shadow: none;
}

.notification-card.unread .notification-title {
    color: #007bff;
}

/* Make dropdown buttons look like dropdown items */
.dropdown-item[type="button"] {
    background: none;
    border: none;
    text-align: left;
    width: 100%;
    padding: 0.25rem 1rem;
    font-size: 0.875rem;
    color: var(--bs-dropdown-link-color);
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
}

.dropdown-item[type="button"]:hover,
.dropdown-item[type="button"]:focus {
    color: var(--bs-dropdown-link-hover-color);
    background-color: var(--bs-dropdown-link-hover-bg);
}

.dropdown-item[type="button"]:active {
    color: var(--bs-dropdown-link-active-color);
    background-color: var(--bs-dropdown-link-active-bg);
}

.dropdown-item[type="button"].text-danger:hover {
    color: #fff;
    background-color: #dc3545;
}
</style>
