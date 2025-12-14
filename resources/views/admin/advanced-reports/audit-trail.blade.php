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

    .audit-cell-meta {
        color: #6c757d;
        font-size: 0.78rem;
        line-height: 1.2;
    }

    .audit-mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.78rem;
    }

    .audit-badge {
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .audit-desc {
        max-width: 720px;
        white-space: normal;
        overflow-wrap: anywhere;
        word-break: break-word;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }

    .audit-desc.is-expanded {
        display: block;
        -webkit-line-clamp: unset;
        overflow: visible;
    }

    .audit-desc-toggle {
        font-size: 0.8rem;
        text-decoration: none;
    }

    .audit-table td {
        vertical-align: top;
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
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

<!-- Security / Quality Signals -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h6><i class="fas fa-shield-alt me-2"></i>High Risk (7 days)</h6>
            <div class="stat-value text-danger">{{ number_format($stats['high_risk_7d'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h6><i class="fas fa-ban me-2"></i>Failed Logins (Today)</h6>
            <div class="stat-value text-danger">{{ number_format($stats['failed_logins_today'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card">
            <h6><i class="fas fa-info-circle me-2"></i>Tip</h6>
            <div class="audit-cell-meta" style="font-size: 0.9rem;">
                Use filters + search to trace who changed a record, when it happened, and which module/record was affected.
            </div>
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
            <div class="col-md-2">
                <label class="form-label">Severity</label>
                <select name="severity" class="form-select">
                    <option value="">All Severities</option>
                    @foreach(($severities ?? []) as $sev)
                        <option value="{{ $sev }}" {{ request('severity') == $sev ? 'selected' : '' }}>
                            {{ ucfirst($sev) }}
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
                <label class="form-label">Module</label>
                <select name="model_type" class="form-select">
                    <option value="">All Modules</option>
                    @foreach(($modelTypes ?? []) as $type => $label)
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </div>
        <div class="row g-3 mt-1">
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
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Description, user/email, module, record ID, IP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end justify-content-end gap-2">
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
        @php
            $actionBadge = [
                'login' => 'success',
                'logout' => 'secondary',
                'create' => 'primary',
                'update' => 'warning',
                'delete' => 'danger',
                'view' => 'info',
                'download' => 'info',
                'export' => 'info',
                'import' => 'info',
                'failed_login' => 'danger',
                'password_change' => 'warning',
                'pre_consultation_verified' => 'success',
            ];

            $actionIcon = [
                'login' => 'sign-in-alt',
                'logout' => 'sign-out-alt',
                'create' => 'plus-circle',
                'update' => 'edit',
                'delete' => 'trash',
                'view' => 'eye',
                'download' => 'download',
                'export' => 'file-export',
                'import' => 'file-import',
                'failed_login' => 'ban',
                'password_change' => 'key',
                'pre_consultation_verified' => 'clipboard-check',
            ];

            $targetRouteMap = [
                'App\\Models\\Patient' => 'admin.patients.show',
                'App\\Models\\Appointment' => 'admin.appointments.show',
                'App\\Models\\MedicalRecord' => 'admin.medical-records.show',
                'App\\Models\\Prescription' => 'admin.prescriptions.show',
                'App\\Models\\Billing' => 'admin.billing.show',
                'App\\Models\\LabReport' => 'admin.lab-reports.show',
                'App\\Models\\GeneratedDocument' => 'admin.generated-documents.show',
                'App\\Models\\User' => 'admin.users.show',
            ];
        @endphp

        <table class="table table-hover mb-0 audit-table">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 160px;">Time</th>
                    <th style="min-width: 220px;">Actor</th>
                    <th style="min-width: 220px;">Action</th>
                    <th style="min-width: 220px;">Target</th>
                    <th>Description</th>
                    <th style="min-width: 210px;">Source</th>
                    <th style="width: 72px;">View</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ formatDate($log->created_at) }}</div>
                        <div class="audit-cell-meta">
                            {{ $log->created_at->format('H:i') }} · {{ $log->created_at->diffForHumans() }}
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $log->user->name ?? 'System' }}</div>
                        <div class="audit-cell-meta">
                            @if($log->user)
                                {{ $log->user->email }}
                                · {{ ucfirst($log->user->role ?? 'staff') }}
                            @else
                                System event
                            @endif
                        </div>
                    </td>
                    <td>
                        @php
                            $action = $log->action ?? 'event';
                            $actionLabel = \Illuminate\Support\Str::of((string) $action)->replace('_', ' ')->title();
                            $actionColor = $actionBadge[$action] ?? 'secondary';
                            $icon = $actionIcon[$action] ?? 'circle';

                            $sev = $log->severity ?? null;
                            $sevLabel = $sev ? ucfirst($sev) : 'N/A';
                            $sevBgClass = $log->severity_badge ?? 'bg-secondary';
                            $sevTextClass = in_array($sevBgClass, ['bg-warning', 'bg-light', 'bg-info']) ? 'text-dark' : 'text-white';
                        @endphp

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-{{ $actionColor }} audit-badge">
                                <i class="fas fa-{{ $icon }} me-1"></i>{{ $actionLabel }}
                            </span>
                            @if($sev)
                                <span class="badge {{ $sevBgClass }} {{ $sevTextClass }} audit-badge" title="Severity">
                                    <i class="{{ $log->severity_icon }} me-1"></i>{{ $sevLabel }}
                                </span>
                            @endif
                        </div>
                        @if(!empty($log->session_id))
                            <div class="audit-cell-meta mt-1">
                                Session: <span class="audit-mono">{{ \Illuminate\Support\Str::limit($log->session_id, 14, '…') }}</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @php
                            $modelType = $log->model_type;
                            $modelId = $log->model_id;
                            $short = null;
                            if ($modelType) {
                                $parts = explode('\\', $modelType);
                                $short = end($parts);
                            }
                            $routeName = ($modelType && isset($targetRouteMap[$modelType])) ? $targetRouteMap[$modelType] : null;
                            $canLink = $routeName && $modelId && \Illuminate\Support\Facades\Route::has($routeName);
                        @endphp

                        @if($short || $modelId)
                            <div class="fw-semibold">
                                @if($canLink)
                                    <a href="{{ route($routeName, $modelId) }}" class="text-decoration-none">
                                        {{ $short ?? 'Record' }} #{{ $modelId }}
                                    </a>
                                @else
                                    {{ $short ?? 'Record' }} @if($modelId)#{{ $modelId }}@endif
                                @endif
                            </div>
                            <div class="audit-cell-meta">{{ $modelType ?? 'N/A' }}</div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $desc = (string) ($log->description ?? '');
                            $descTooLong = \Illuminate\Support\Str::length($desc) > 140;
                        @endphp

                        <div id="desc-{{ $log->id }}" class="audit-desc" title="{{ $desc }}">{{ $desc }}</div>
                        @if($descTooLong)
                            <div class="mt-1">
                                <a href="#" class="audit-desc-toggle" data-target="desc-{{ $log->id }}">More</a>
                            </div>
                        @endif
                        @php
                            $changedKeys = [];
                            $old = is_array($log->old_values ?? null) ? $log->old_values : [];
                            $new = is_array($log->new_values ?? null) ? $log->new_values : [];
                            if (!empty($old) || !empty($new)) {
                                $changedKeys = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
                            }
                        @endphp
                        @if(!empty($changedKeys))
                            <div class="audit-cell-meta mt-1">
                                Changed: {{ \Illuminate\Support\Str::limit(implode(', ', array_slice($changedKeys, 0, 6)), 80, '…') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="audit-mono">
                            {{ $log->ip_address ?? '—' }}
                        </div>
                        @if(!empty($log->user_agent))
                            <div class="audit-cell-meta" title="{{ $log->user_agent }}">
                                {{ \Illuminate\Support\Str::limit($log->user_agent, 36, '…') }}
                            </div>
                        @endif
                    </td>
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
                    <td colspan="7" class="text-center py-4">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.audit-desc-toggle[data-target]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var targetId = link.getAttribute('data-target');
            if (!targetId) return;
            var el = document.getElementById(targetId);
            if (!el) return;

            var expanded = el.classList.toggle('is-expanded');
            link.textContent = expanded ? 'Less' : 'More';
        });
    });
});
</script>
@endpush

