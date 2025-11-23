@extends('admin.layouts.app')

@section('title', 'View Notification')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('notifications.index') }}">Notifications</a></li>
    <li class="breadcrumb-item active">View Notification</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bell me-2 text-primary"></i>
                                {{ $notification->title }}
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                @if($notification->action_url)
                                    <a href="{{ $notification->action_url }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>
                                        View Details
                                    </a>
                                @endif
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteNotification({{ $notification->id }})">
                                    <i class="fas fa-trash me-1"></i>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small text-muted d-block">Type</label>
                                <span class="badge bg-{{ $notification->type_color }}-subtle text-{{ $notification->type_color }}">
                                    <i class="{{ $notification->type_icon }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $notification->type)) }}
                                </span>
                            </div>
                            <div class="mb-3">
                                <label class="small text-muted d-block">Status</label>
                                @if($notification->is_read)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fas fa-check me-1"></i>Read
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="fas fa-circle me-1"></i>Unread
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="small text-muted d-block">Created</label>
                                <div>{{ $notification->created_at->format('M j, Y g:i A') }}</div>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </div>
                            @if($notification->read_at)
                            <div class="mb-3">
                                <label class="small text-muted d-block">Read At</label>
                                <div>{{ $notification->read_at->format('M j, Y g:i A') }}</div>
                                <small class="text-muted">{{ $notification->read_at->diffForHumans() }}</small>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted d-block">Message</label>
                        <div class="p-3 bg-light rounded">
                            {{ $notification->message }}
                        </div>
                    </div>

                    @if($notification->data)
                    <div>
                        <label class="small text-muted d-block">Additional Data</label>
                        <div class="p-3 bg-light rounded">
                            <pre class="mb-0"><code>{{ json_encode(json_decode($notification->data), JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        $.ajax({
            url: "{{ contextRoute('notifications.index') }}/" + notificationId,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(result) {
                if (result.success) {
                    window.location.href = "{{ contextRoute('notifications.index') }}";
                }
            }
        });
    }
}
</script>
@endpush
