@extends('admin.layouts.app')

@section('title', 'Audit Log Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Advanced Reports</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.audit-trail') }}">Audit Trail</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
    .audit-diff-table th { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.03em; color: #5a5c69; }
    .audit-diff-table td { vertical-align: top; font-size: 0.95rem; }
    .audit-diff-cell {
        max-width: 420px;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.45;
    }
    .audit-diff-before { background: rgba(239, 68, 68, 0.06); border-left: 3px solid rgba(239, 68, 68, 0.45) !important; }
    .audit-diff-after { background: rgba(34, 197, 94, 0.08); border-left: 3px solid rgba(34, 197, 94, 0.5) !important; }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="modern-page-title"><i class="fas fa-info-circle"></i>Audit Log Details</h1>
                    <p class="modern-page-subtitle">Detailed view of this audit log entry</p>
                </div>
                <div>
                    <a href="{{ route('admin.advanced-reports.audit-trail') }}" class="btn-modern btn-modern-outline">
                        <i class="fas fa-arrow-left"></i>Back to Audit Trail
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Log Details -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-clipboard-list"></i>Activity Information</h5>
        </div>
        <div class="modern-card-body">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Basic Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">ID</th>
                            <td>{{ $auditLog->id }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>
                                @if($auditLog->user)
                                    {{ $auditLog->user->name }}
                                    <br>
                                    <small class="text-muted">{{ $auditLog->user->email }}</small>
                                @else
                                    System
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Action</th>
                            <td>
                                @php
                                $iconMap = ['create' => 'plus', 'update' => 'edit', 'delete' => 'trash', 'view' => 'eye', 'login' => 'sign-in-alt', 'logout' => 'sign-out-alt', 'pre_consultation_verified' => 'clipboard-check'];
                                $icon = $iconMap[$auditLog->action] ?? 'circle';
                                $sevBg = $auditLog->severity_badge ?? 'bg-secondary';
                                $sevTextClass = in_array($sevBg, ['bg-warning', 'bg-light', 'bg-info']) ? 'text-dark' : 'text-white';
                                @endphp
                                <span class="badge {{ $sevBg }} {{ $sevTextClass }} px-3 py-2">
                                    <i class="fas fa-{{ $icon }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Severity</th>
                            <td>
                                <span class="badge {{ $sevBg }} {{ $sevTextClass }} px-3 py-2">
                                    <i class="{{ $auditLog->severity_icon }}"></i>
                                    {{ ucfirst($auditLog->severity) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Timestamp</th>
                            <td>{{ $auditLog->created_at->format('d-m-Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Technical Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Technical Information</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">IP Address</th>
                            <td>{{ $auditLog->ip_address ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Session ID</th>
                            <td><code>{{ $auditLog->session_id ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th>User Agent</th>
                            <td><small>{{ $auditLog->user_agent ?? 'N/A' }}</small></td>
                        </tr>
                        @if($auditLog->model_type)
                        <tr>
                            <th>Model Type</th>
                            <td>
                                @php
                                    $parts = explode('\\', $auditLog->model_type);
                                    $modelName = end($parts);
                                @endphp
                                <code>{{ $modelName }}</code>
                            </td>
                        </tr>
                        @endif
                        @if($auditLog->model_id)
                        <tr>
                            <th>Model ID</th>
                            <td>{{ $auditLog->model_id }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Description -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="mb-3">Description</h5>
                    <div class="modern-card" style="padding: 1.25rem;">
                        <div class="text-muted small mb-1">Message</div>
                        <div>{{ $auditLog->description }}</div>
                    </div>
                </div>
            </div>

            <!-- Changes (if available) -->
            @if($auditLog->old_values || $auditLog->new_values)
            <div class="row mt-4">
                <div class="col-12">
                    @include('partials.audit-change-diff', ['oldValues' => $auditLog->old_values, 'newValues' => $auditLog->new_values])
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Related Activities -->
    @if($auditLog->user_id)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-user-clock"></i>Recent Activities by Same User</h5>
        </div>
        <div class="modern-card-body">
            @php
                $recentActivities = \App\Models\UserActivity::where('user_id', $auditLog->user_id)
                    ->where('id', '!=', $auditLog->id)
                    ->latest()
                    ->limit(5)
                    ->get();
            @endphp

            @if($recentActivities->count() > 0)
                <div class="modern-table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivities as $activity)
                            <tr>
                                <td>{{ $activity->created_at->format('d-m-Y H:i:s') }}</td>
                                <td>
                                    @php
                                        $abg = $activity->severity_badge ?? 'bg-secondary';
                                        $atxt = in_array($abg, ['bg-warning', 'bg-light', 'bg-info']) ? 'text-dark' : 'text-white';
                                    @endphp
                                    <span class="badge {{ $abg }} {{ $atxt }}">
                                        {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($activity->description, 60) }}</td>
                                <td>
                                    <a href="{{ route('admin.advanced-reports.audit-trail.show', $activity->id) }}" class="btn-modern btn-modern-outline btn-modern-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">No other recent activities found for this user.</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
