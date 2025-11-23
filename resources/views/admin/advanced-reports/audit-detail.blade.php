@extends('admin.layouts.app')

@section('title', 'Audit Log Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Advanced Reports</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.audit-trail') }}">Audit Trail</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<!-- Page Header -->
<div class="page-title mb-4">
    <h1><i class="fas fa-info-circle me-2"></i>Audit Log Details</h1>
    <p class="page-subtitle">Detailed view of audit log entry</p>
</div>

<div class="mb-3">
    <a href="{{ route('admin.advanced-reports.audit-trail') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Audit Trail
    </a>
</div>

    <!-- Audit Log Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Activity Information</h6>
        </div>
        <div class="card-body">
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
                                @endphp
                                <span class="badge bg-{{ $auditLog->severity_badge }} px-3 py-2">
                                    <i class="fas fa-{{ $icon }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $auditLog->action)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Severity</th>
                            <td>
                                <span class="badge {{ $auditLog->severity_badge }} px-3 py-2">
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ $auditLog->description }}
                    </div>
                </div>
            </div>

            <!-- Changes (if available) -->
            @if($auditLog->old_values || $auditLog->new_values)
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="mb-3">Changes</h5>
                    <div class="row">
                        @if($auditLog->old_values)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <i class="fas fa-minus-circle"></i> Old Values
                                </div>
                                <div class="card-body">
                                    <pre class="mb-0">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($auditLog->new_values)
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <i class="fas fa-plus-circle"></i> New Values
                                </div>
                                <div class="card-body">
                                    <pre class="mb-0">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Related Activities -->
    @if($auditLog->user_id)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Activities by Same User</h6>
        </div>
        <div class="card-body">
            @php
                $recentActivities = \App\Models\UserActivity::where('user_id', $auditLog->user_id)
                    ->where('id', '!=', $auditLog->id)
                    ->latest()
                    ->limit(5)
                    ->get();
            @endphp

            @if($recentActivities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
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
                                    <span class="badge badge-sm {{ $activity->severity_badge }}">
                                        {{ ucfirst($activity->action) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($activity->description, 60) }}</td>
                                <td>
                                    <a href="{{ route('admin.advanced-reports.audit-trail.show', $activity->id) }}" class="btn btn-sm btn-info">
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
@endsection
