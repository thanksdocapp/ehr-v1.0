@extends('admin.layouts.app')

@section('title', 'Audit Trail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Advanced Reports</a></li>
    <li class="breadcrumb-item active">Audit Trail</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
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
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="modern-page-title"><i class="fas fa-history"></i>Audit Trail</h1>
                    <p class="modern-page-subtitle">Track logins, record changes, and security-related events</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.advanced-reports.audit-trail.export') }}?{{ http_build_query(request()->all()) }}" class="btn-modern btn-modern-outline">
                        <i class="fas fa-download"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-list"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['total_logs'] ?? 0) }}</div>
                        <div class="stat-label">Total Logs</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['today_logs'] ?? 0) }}</div>
                        <div class="stat-label">Today</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['unique_users'] ?? 0) }}</div>
                        <div class="stat-label">Unique Users</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-shield-alt"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['high_risk_7d'] ?? 0) }}</div>
                        <div class="stat-label">High Risk (7 days)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card mb-4">
        <div class="modern-card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Tip: Use filters + search to trace who changed a record, when it happened, and which module/record was affected.
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-danger">
                        <i class="fas fa-ban me-1"></i>{{ number_format($stats['failed_logins_today'] ?? 0) }} failed logins today
                    </span>
                    <a href="{{ route('admin.advanced-reports.audit-trail') }}" class="btn-modern btn-modern-outline btn-modern-sm">
                        <i class="fas fa-rotate-right"></i>Reset
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-filter"></i>Filters</h5>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ route('admin.advanced-reports.audit-trail') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="modern-form-label">Event Type</label>
                <select name="event_type" class="modern-form-select">
                    <option value="">All Events</option>
                    @foreach($eventTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('event_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="modern-form-label">Severity</label>
                <select name="severity" class="modern-form-select">
                    <option value="">All Severities</option>
                    @foreach(($severities ?? []) as $sev)
                        <option value="{{ $sev }}" {{ request('severity') == $sev ? 'selected' : '' }}>
                            {{ ucfirst($sev) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="modern-form-label">User</label>
                <select name="user_id" class="modern-form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="modern-form-label">Module</label>
                <select name="model_type" class="modern-form-select">
                    <option value="">All Modules</option>
                    @foreach(($modelTypes ?? []) as $type => $label)
                        <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn-modern btn-modern-primary w-100">
                    <i class="fas fa-filter"></i>Apply
                </button>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-2">
                <label class="modern-form-label">From Date</label>
                <input type="text" name="date_from" id="date_from" class="modern-form-control"
                       value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                       placeholder="dd-mm-yyyy"
                       pattern="\d{2}-\d{2}-\d{4}"
                       maxlength="10">
                <small class="form-help-text">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-2">
                <label class="modern-form-label">To Date</label>
                <input type="text" name="date_to" id="date_to" class="modern-form-control"
                       value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                       placeholder="dd-mm-yyyy"
                       pattern="\d{2}-\d{2}-\d{4}"
                       maxlength="10">
                <small class="form-help-text">Format: dd-mm-yyyy</small>
            </div>
            <div class="col-md-5">
                <label class="modern-form-label">Search</label>
                <input type="text" name="search" class="modern-form-control" placeholder="Description, user/email, module, record ID, IP..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end justify-content-end gap-2">
                <a href="{{ route('admin.advanced-reports.audit-trail') }}" class="btn-modern btn-modern-outline">
                    <i class="fas fa-rotate-right"></i>Reset
                </a>
            </div>
        </div>
    </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-list"></i>Audit Logs</h5>
        </div>
        <div class="modern-card-body">
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

        <div class="modern-table-wrapper">
        <table class="modern-table audit-table">
            <thead>
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
                           class="btn-modern btn-modern-outline btn-modern-sm" 
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
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #f1f5f9;">
            <div class="text-muted small">
                Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() ?? 0 }} logs
            </div>
            {{ $logs->links() }}
        </div>
    </div>
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

