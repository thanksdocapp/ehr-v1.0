@extends('admin.layouts.app')

@section('title', 'Create Notice')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notices.index') }}">Notices</a></li>
    <li class="breadcrumb-item active">Create Notice</li>
@endsection

@section('content')
<div class="fade-in">
    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-bullhorn me-2"></i>Create New Notice
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <form action="{{ route('admin.notices.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" 
                                      name="message" 
                                      rows="5" 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This message will be displayed to all users on their dashboards.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>Danger</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority" 
                                        required>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target Roles</label>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="target_all" 
                                       name="target_all"
                                       {{ !old('target_roles') ? 'checked' : '' }}
                                       onchange="toggleTargetRoles()">
                                <label class="form-check-label" for="target_all">
                                    All Users (leave unchecked to select specific roles)
                                </label>
                            </div>
                            <div id="target_roles_container" style="display: {{ old('target_roles') ? 'block' : 'none' }}; margin-top: 10px;">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="target_doctor" 
                                           name="target_roles[]" 
                                           value="doctor"
                                           {{ old('target_roles') && in_array('doctor', old('target_roles')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="target_doctor">Doctors</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="target_nurse" 
                                           name="target_roles[]" 
                                           value="nurse"
                                           {{ old('target_roles') && in_array('nurse', old('target_roles')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="target_nurse">Nurses</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="target_receptionist" 
                                           name="target_roles[]" 
                                           value="receptionist"
                                           {{ old('target_roles') && in_array('receptionist', old('target_roles')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="target_receptionist">Receptionists</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="target_staff" 
                                           name="target_roles[]" 
                                           value="staff"
                                           {{ old('target_roles') && in_array('staff', old('target_roles')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="target_staff">Staff</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="starts_at" class="form-label">Start Date (Optional)</label>
                                <input type="datetime-local" 
                                       class="form-control @error('starts_at') is-invalid @enderror" 
                                       id="starts_at" 
                                       name="starts_at" 
                                       value="{{ old('starts_at') }}">
                                @error('starts_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Notice will be visible from this date</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="expires_at" class="form-label">Expiry Date (Optional)</label>
                                <input type="datetime-local" 
                                       class="form-control @error('expires_at') is-invalid @enderror" 
                                       id="expires_at" 
                                       name="expires_at" 
                                       value="{{ old('expires_at') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Notice will be hidden after this date</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Notice will be visible immediately)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Notice
                            </button>
                            <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
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

