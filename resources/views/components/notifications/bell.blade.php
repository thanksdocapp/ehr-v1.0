@props(['unreadCount' => 0, 'notifications' => collect(), 'viewAllUrl' => '#'])

<div class="dropdown notification-bell">
    <button class="btn btn-link text-dark p-2" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
        <div class="position-relative">
            <i class="fas fa-bell fs-5"></i>
            @if($unreadCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    <span class="visually-hidden">unread notifications</span>
                </span>
            @endif
        </div>
    </button>

    <!-- Notification Dropdown -->
    <x-notifications.dropdown 
        :notifications="$notifications" 
        :unreadCount="$unreadCount" 
        :viewAllUrl="$viewAllUrl" 
    />
</div>

<style>
.notification-bell .btn:focus {
    box-shadow: none;
}

.notification-badge {
    font-size: 0.65rem;
    padding: 0.25em 0.4em;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        transform: translate(-50%, -50%) scale(1.1);
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
    }
}

.notification-bell .dropdown-menu {
    width: 350px;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.notification-bell .dropdown-toggle::after {
    display: none;
}
</style>
