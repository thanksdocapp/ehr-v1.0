@props(['notifications'])

<div class="notification-list">
    @forelse($notifications as $notification)
        <x-notifications.card :notification="$notification" />
    @empty
        <div class="text-center py-5">
            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
            <p class="text-muted">You have no notifications yet.</p>
        </div>
    @endforelse
</div>

<style>
.notification-list {
    display: grid;
    gap: 1rem;
}
</style>
