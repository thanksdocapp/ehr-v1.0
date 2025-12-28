@extends('admin.layouts.app')

@section('title', 'View Notice')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notices.index') }}">Notices</a></li>
    <li class="breadcrumb-item active">View Notice</li>
@endsection

@section('content')
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-bullhorn me-2"></i>Notice Details
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Title</label>
                        <div class="fw-bold fs-5">{{ $notice->title }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Message</label>
                        <div class="p-3 bg-light rounded border">
                            {!! nl2br(e($notice->message)) !!}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Type</label>
                            <div>
                                <span class="badge bg-{{ $notice->type }} fs-6">{{ ucfirst($notice->type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Priority</label>
                            <div>
                                <span class="badge bg-{{ $notice->priority_color }} fs-6">{{ ucfirst($notice->priority) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Target Roles</label>
                        <div>
                            @if($notice->target_roles && count($notice->target_roles) > 0)
                                @foreach($notice->target_roles as $role)
                                    <span class="badge bg-secondary me-1">{{ ucfirst($role) }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-info">All Users</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @if($notice->isCurrentlyActive())
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Active Flag</label>
                            <div>
                                <span class="badge bg-{{ $notice->is_active ? 'success' : 'secondary' }}">
                                    {{ $notice->is_active ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if($notice->starts_at || $notice->expires_at)
                    <div class="row mb-3">
                        @if($notice->starts_at)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Start Date</label>
                            <div class="fw-bold">{{ $notice->starts_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                        @if($notice->expires_at)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Expiry Date</label>
                            <div class="fw-bold">{{ $notice->expires_at->format('M d, Y H:i') }}</div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Created</label>
                            <div>
                                <div class="fw-bold">{{ $notice->created_at->format('M d, Y H:i') }}</div>
                                @if($notice->creator)
                                    <small class="text-muted">by {{ $notice->creator->name }}</small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Last Updated</label>
                            <div class="fw-bold">{{ $notice->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Notice
                        </a>
                        <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

