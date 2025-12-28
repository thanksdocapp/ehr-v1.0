@extends('admin.layouts.app')

@section('title', 'Create New Notice')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notices.index') }}">System Notices</a></li>
    <li class="breadcrumb-item active">Create Notice</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
<style>
    .role-checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .role-checkbox-item {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .role-checkbox-item:hover {
        background-color: #f8f9fc;
        border-color: #667eea;
    }

    .role-checkbox-item input[type="checkbox"] {
        margin-right: 0.5rem;
    }

    .form-help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>
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
                        Create New System Notice
                    </h1>
                    <p class="modern-page-subtitle">Post a notice that will be visible to users on their dashboards</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.notices.index') }}" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Notices
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.notices.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Main Form Column -->
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
                                <i class="fas fa-heading me-1"></i>Title <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control modern-form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   placeholder="e.g., System Maintenance Scheduled"
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-help-text">A clear, concise title for the notice</div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">
                                <i class="fas fa-align-left me-1"></i>Message <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control modern-form-control modern-form-textarea @error('message') is-invalid @enderror"
                                      id="message"
                                      name="message"
                                      rows="6"
                                      placeholder="Enter the notice message that will be displayed to users..."
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-help-text">This message will be displayed to all targeted users on their dashboards</div>
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
                                        <i class="fas fa-tag me-1"></i>Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select modern-form-select @error('type') is-invalid @enderror"
                                            id="type"
                                            name="type"
                                            required>
                                        <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info (Blue)</option>
                                        <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning (Yellow)</option>
                                        <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success (Green)</option>
                                        <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">Visual style of the notice</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-exclamation-circle me-1"></i>Priority <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select modern-form-select @error('priority') is-invalid @enderror"
                                            id="priority"
                                            name="priority"
                                            required>
                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">Priority level affects display order</div>
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label">
                                <i class="fas fa-users me-1"></i>Target Audience
                            </label>
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="target_all"
                                       name="target_all"
                                       {{ !old('target_roles') ? 'checked' : '' }}
                                       onchange="toggleTargetRoles()">
                                <label class="form-check-label" for="target_all">
                                    <strong>All Users</strong> (leave checked to show to everyone)
                                </label>
                            </div>
                            <div id="target_roles_container" style="display: {{ old('target_roles') ? 'block' : 'none' }};">
                                <div class="role-checkbox-group">
                                    @foreach($roles as $roleKey => $roleLabel)
                                        <div class="role-checkbox-item">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="target_{{ $roleKey }}"
                                                   name="target_roles[]"
                                                   value="{{ $roleKey }}"
                                                   {{ old('target_roles') && in_array($roleKey, old('target_roles')) ? 'checked' : '' }}>
                                            <label class="form-check-label mb-0" for="target_{{ $roleKey }}">
                                                {{ $roleLabel }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-help-text">Select specific roles to target, or leave "All Users" checked</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-calendar-alt"></i>
                            Schedule (Optional)
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-play-circle me-1"></i>Start Date & Time
                                    </label>
                                    <input type="datetime-local"
                                           class="form-control modern-form-control @error('starts_at') is-invalid @enderror"
                                           id="starts_at"
                                           name="starts_at"
                                           value="{{ old('starts_at') }}">
                                    @error('starts_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">Notice will be visible from this date (leave blank for immediate)</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label class="modern-form-label">
                                        <i class="fas fa-stop-circle me-1"></i>Expiry Date & Time
                                    </label>
                                    <input type="datetime-local"
                                           class="form-control modern-form-control @error('expires_at') is-invalid @enderror"
                                           id="expires_at"
                                           name="expires_at"
                                           value="{{ old('expires_at') }}">
                                    @error('expires_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-help-text">Notice will be hidden after this date (leave blank for no expiry)</div>
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Active</strong> - Notice will be visible immediately (if within schedule)
                                </label>
                            </div>
                            <div class="form-help-text">Uncheck to create the notice in inactive state</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Notice
                                </button>
                                <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Info -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-info-circle"></i>
                            Notice Information
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div class="mb-3">
                            <h6 class="text-muted mb-2"><i class="fas fa-lightbulb me-2"></i>Best Practices</h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Keep titles concise and clear</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Use appropriate priority levels</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Set expiry dates for time-sensitive notices</li>
                                <li class="mb-0"><i class="fas fa-check text-success me-2"></i>Target specific roles when needed</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Notice Types Guide -->
                <div class="modern-card fade-in-up">
                    <div class="modern-card-header">
                        <h6 class="modern-card-title">
                            <i class="fas fa-palette"></i>
                            Notice Types
                        </h6>
                    </div>
                    <div class="modern-card-body">
                        <div class="mb-2">
                            <span class="badge bg-info me-2">Info</span>
                            <small class="text-muted">General information</small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-warning me-2">Warning</span>
                            <small class="text-muted">Important alerts</small>
                        </div>
                        <div class="mb-2">
                            <span class="badge bg-success me-2">Success</span>
                            <small class="text-muted">Positive updates</small>
                        </div>
                        <div class="mb-0">
                            <span class="badge bg-danger me-2">Danger</span>
                            <small class="text-muted">Critical issues</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleTargetRoles() {
    const targetAll = document.getElementById('target_all');
    const container = document.getElementById('target_roles_container');
    
    if (targetAll.checked) {
        container.style.display = 'none';
        // Uncheck all role checkboxes
        document.querySelectorAll('#target_roles_container input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
    } else {
        container.style.display = 'block';
    }
}
</script>
@endsection
