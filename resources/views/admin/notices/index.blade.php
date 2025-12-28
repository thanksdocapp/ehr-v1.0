@extends('admin.layouts.app')

@section('title', 'Notices Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Notices</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Notices Management</h4>
            <small class="text-muted">Create and manage notices visible to all users on their dashboards</small>
        </div>
        <a href="{{ route('admin.notices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create Notice
        </a>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Notices Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">All Notices</h5>
        </div>
        <div class="doctor-card-body">
            @if($notices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Target Roles</th>
                                <th>Status</th>
                                <th>Schedule</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notices as $notice)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $notice->title }}</div>
                                    <small class="text-muted">{{ Str::limit($notice->message, 60) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $notice->type }}">{{ ucfirst($notice->type) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $notice->priority_color }}">{{ ucfirst($notice->priority) }}</span>
                                </td>
                                <td>
                                    @if($notice->target_roles && count($notice->target_roles) > 0)
                                        @foreach($notice->target_roles as $role)
                                            <span class="badge bg-secondary me-1">{{ ucfirst($role) }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-info">All Users</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notice->isCurrentlyActive())
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notice->starts_at || $notice->expires_at)
                                        <small>
                                            @if($notice->starts_at)
                                                <div>Starts: {{ $notice->starts_at->format('M d, Y') }}</div>
                                            @endif
                                            @if($notice->expires_at)
                                                <div>Expires: {{ $notice->expires_at->format('M d, Y') }}</div>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">No schedule</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $notice->created_at->format('M d, Y') }}</small>
                                    @if($notice->creator)
                                        <br><small class="text-muted">by {{ $notice->creator->name }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('admin.notices.show', $notice) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.notices.edit', $notice) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.notices.toggle-status', $notice) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-{{ $notice->is_active ? 'secondary' : 'success' }}"
                                                    title="{{ $notice->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $notice->is_active ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.notices.destroy', $notice) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this notice?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $notices->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No notices found. Create your first notice to get started.</p>
                    <a href="{{ route('admin.notices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Notice
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

