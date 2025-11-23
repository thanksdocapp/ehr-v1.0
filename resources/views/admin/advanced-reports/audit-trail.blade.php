@extends('admin.layouts.app')

@section('title', 'Audit Trail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Advanced Reports</a></li>
    <li class="breadcrumb-item active">Audit Trail</li>
@endsection

@push('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .stats-card h6 {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 10px;
    }
    .stats-card .stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1a1a2e;
    }
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .log-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        margin: 0 0.125rem;
        font-size: 0.875rem;
        color: #1cc88a;
        border-color: #e3e6f0;
    }
    
    .pagination .page-link:hover {
        color: #1cc88a;
        background-color: #f8f9fc;
        border-color: #1cc88a;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #1cc88a;
        border-color: #1cc88a;
        color: white;
    }
    
    .pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
        color: #6c757d;
    }
    
    /* Hide Previous/Next icon buttons */
    .pagination .page-item:first-child,
    .pagination .page-item:last-child {
        display: none !important;
    }
    
    /* Hide pagination arrow SVG icons - multiple selectors for different Laravel versions */
    .pagination .page-link svg,
    .pagination svg,
    nav[aria-label="Pagination Navigation"] svg {
        display: none !important;
    }
    
    /* Hide aria-hidden elements that contain arrows */
    .pagination [aria-hidden="true"],
    .pagination .page-link span:first-child:not(:only-child) {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="page-title mb-4">
    <h1><i class="fas fa-history me-2"></i>Audit Trail</h1>
    <p class="page-subtitle">Track user login and CRUD activities</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h6><i class="fas fa-list me-2"></i>Total Logs</h6>
            <div class="stat-value text-primary">{{ number_format($stats['total_logs']) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h6><i class="fas fa-calendar-day me-2"></i>Today's Logs</h6>
            <div class="stat-value text-success">{{ number_format($stats['today_logs']) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h6><i class="fas fa-users me-2"></i>Unique Users</h6>
            <div class="stat-value text-info">{{ number_format($stats['unique_users']) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h6><i class="fas fa-sign-in-alt me-2"></i>Logins Today</h6>
            <div class="stat-value text-warning">{{ number_format($stats['login_count']) }}</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.advanced-reports.audit-trail') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Event Type</label>
                <select name="event_type" class="form-select">
                    <option value="">All Events</option>
                    @foreach($eventTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('event_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="text" name="date_from" id="date_from" class="form-control" 
                       value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                       placeholder="dd-mm-yyyy" 
                       pattern="\d{2}-\d{2}-\d{4}" 
                       maxlength="10">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="text" name="date_to" id="date_to" class="form-control" 
                       value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                       placeholder="dd-mm-yyyy" 
                       pattern="\d{2}-\d{2}-\d{4}" 
                       maxlength="10">
                <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search description, user, IP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('admin.advanced-reports.audit-trail') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-2"></i>Reset
                </a>
                <a href="{{ route('admin.advanced-reports.audit-trail.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success">
                    <i class="fas fa-download me-2"></i>Export CSV
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Audit Logs Table -->
<div class="log-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>User</th>
                    <th>Event</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <small>{{ formatDate($log->created_at) }}</small><br>
                        <small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <strong>{{ $log->user_name ?? 'System' }}</strong><br>
                        @if($log->user)
                            <small class="text-muted">{{ $log->user->email }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $log->event_badge_color }}">
                            <i class="fas fa-{{ $log->event_icon }} me-1"></i>
                            {{ $log->formatted_event_type }}
                        </span>
                        @if($log->short_model_name)
                            <br><small class="text-muted">{{ $log->short_model_name }}</small>
                        @endif
                    </td>
                    <td>{{ $log->description }}</td>
                    <td><code>{{ $log->ip_address }}</code></td>
                    <td>
                        <a href="{{ route('admin.advanced-reports.audit-trail.show', $log->id) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No audit logs found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $logs->links() }}
</div>

@endsection

