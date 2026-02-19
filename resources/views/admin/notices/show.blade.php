@extends('admin.layouts.app')

@section('title', 'Notice Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notices.index') }}">System Notices</a></li>
    <li class="breadcrumb-item active">Notice Details</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1 class="modern-page-title">
                        <i class="fas fa-bullhorn"></i>
                        Notice Details
                    </h1>
                    <p class="modern-page-subtitle">View complete notice information</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-info">
                        <i class="fas fa-edit me-2"></i>Edit Notice
                    </a>
                    <a href="{{ route('admin.notices.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Notices
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Notice Content -->
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <h6 class="modern-card-title">
                        <i class="fas fa-file-alt"></i>
                        Notice Content
                    </h6>
                </div>
                <div class="modern-card-body">
                    <div class="modern-form-group">
                        <label class="modern-form-label">
                            <i class="fas fa-heading me-1"></i>Title
                        </label>
                        <div class="fw-bold fs-5">{{ $notice->title }}</div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">
                            <i class="fas fa-align-left me-1"></i>Message
                        </label>
                        <div class="p-3 bg-light rounded border">
                            {!! nl2br(e($notice->message)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notice Settings -->
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <h6 class="modern-card-title">
                        <i class="fas fa-cog"></i>
                        Notice Settings
                    </h6>
                </div>
                <div class="modern-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    <i class="fas fa-tag me-1"></i>Type
                                </label>
                                <div>
                                    <span class="badge bg-{{ $notice->type }} fs-6">{{ ucfirst($notice->type) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    <i class="fas fa-exclamation-circle me-1"></i>Priority
                                </label>
                                <div>
                                    <span class="badge bg-{{ $notice->priority_color }} fs-6">{{ ucfirst($notice->priority) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modern-form-group">
                        <label class="modern-form-label">
                            <i class="fas fa-users me-1"></i>Target Roles
                        </label>
                        <div>
                            @if($notice->target_roles && count($notice->target_roles) > 0)
                                @foreach($notice->target_roles as $role)
                                    <span class="badge bg-secondary me-1 mb-1">{{ ucfirst($role) }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-info">All Users</span>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status
                                </label>
                                <div>
                                    @if($notice->isCurrentlyActive())
                                        <span class="badge bg-success">Currently Active</span>
                                    @else
                                        <span class="badge bg-secondary">Currently Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    <i class="fas fa-power-off me-1"></i>Active Flag
                                </label>
                                <div>
                                    <span class="badge bg-{{ $notice->is_active ? 'success' : 'secondary' }}">
                                        {{ $notice->is_active ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule -->
            @if($notice->starts_at || $notice->expires_at)
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <h6 class="modern-card-title">
                        <i class="fas fa-calendar-alt"></i>
                        Schedule
                    </h6>
                </div>
                <div class="modern-card-body">
                    <div class="row">
                        @if($notice->starts_at)
                        <div class="col-md-6">
                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    <i class="fas fa-play-circle me-1"></i>Start Date & Time
                                </label>
                                <div class="fw-bold">{{ formatDateTimeUk($notice->starts_at) }}</div>
                            </div>
                        </div>
                        @endif
                        @if($notice->expires_at)
                        <div class="col-md-6">
                            <div class="modern-form-group">
                                <label class="modern-form-label">
                                    <i class="fas fa-stop-circle me-1"></i>Expiry Date & Time
                                </label>
                                <div class="fw-bold">{{ formatDateTimeUk($notice->expires_at) }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Notice Information -->
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <h6 class="modern-card-title">
                        <i class="fas fa-info-circle"></i>
                        Notice Information
                    </h6>
                </div>
                <div class="modern-card-body">
                    <div class="mb-3">
                        <label class="modern-form-label">
                            <i class="fas fa-calendar me-1"></i>Created
                        </label>
                        <div class="fw-bold">{{ formatDateTimeUk($notice->created_at) }}</div>
                        @if($notice->creator)
                            <small class="text-muted">by {{ $notice->creator->name }}</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="modern-form-label">
                            <i class="fas fa-clock me-1"></i>Last Updated
                        </label>
                        <div class="fw-bold">{{ formatDateTimeUk($notice->updated_at) }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <h6 class="modern-card-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h6>
                </div>
                <div class="modern-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Edit Notice
                        </a>
                        <form action="{{ route('admin.notices.toggle-status', $notice) }}" 
                              method="POST" 
                              class="d-inline">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-{{ $notice->is_active ? 'secondary' : 'success' }} w-100">
                                <i class="fas fa-{{ $notice->is_active ? 'eye-slash' : 'eye' }} me-2"></i>
                                {{ $notice->is_active ? 'Deactivate' : 'Activate' }} Notice
                            </button>
                        </form>
                        <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notice Preview -->
            <div class="modern-card fade-in-up">
                <div class="modern-card-header">
                    <h6 class="modern-card-title">
                        <i class="fas fa-eye"></i>
                        Preview
                    </h6>
                </div>
                <div class="modern-card-body">
                    <div class="alert alert-{{ $notice->type }} mb-0">
                        <h6 class="alert-heading mb-2">
                            <i class="fas {{ $notice->type_icon }} me-2"></i>{{ $notice->title }}
                        </h6>
                        <p class="mb-0">{{ Str::limit($notice->message, 100) }}</p>
                    </div>
                    <small class="text-muted">This is how the notice will appear to users</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
