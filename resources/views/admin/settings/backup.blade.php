@extends('admin.layouts.app')

@section('title', 'Backup Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Backup</li>
@endsection

@push('styles')
@include('admin.shared.styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-database me-2 text-primary"></i>Backup Settings</h1>
        <p class="page-subtitle text-muted">Manage database backups and restore operations</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value">{{ number_format($backupStats['total_backups']) }}</div>
                <div class="stat-label">Total Backups</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-hdd"></i>
                </div>
                <div class="stat-value">{{ $backupStats['storage_used'] }}</div>
                <div class="stat-label">Storage Used</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ $backupStats['last_backup'] }}</div>
                <div class="stat-label">Last Backup</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-value">{{ $backupStats['next_backup'] }}</div>
                <div class="stat-label">Next Backup</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <form action="{{ contextRoute('settings.backup.update') }}" method="POST" id="backupForm">
        @csrf
        @method('PUT')
        
        <!-- Automatic Backup Settings -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Automatic Backup</h4>
                <small class="opacity-75">Configure automated backup scheduling</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="auto_backup" name="auto_backup" value="1" 
                                    {{ old('auto_backup', $settings['auto_backup'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_backup">
                                    Enable Auto Backup
                                </label>
                                <div class="form-text">Automatically create database backups</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="backup_frequency" class="form-label">Backup Frequency</label>
                                <select class="form-control" id="backup_frequency" name="backup_frequency">
                                    <option value="hourly" {{ old('backup_frequency', $settings['backup_frequency'] ?? 'daily') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                    <option value="daily" {{ old('backup_frequency', $settings['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('backup_frequency', $settings['backup_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('backup_frequency', $settings['backup_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                                <div class="form-text">How often backups should be created</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="backup_time" class="form-label">Backup Time</label>
                                <input type="time" class="form-control" id="backup_time" name="backup_time" 
                                    value="{{ old('backup_time', $settings['backup_time'] ?? '03:00') }}">
                                <div class="form-text">Time when automatic backups should run</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="max_backups" class="form-label">Maximum Backups to Keep</label>
                                <input type="number" class="form-control" id="max_backups" name="max_backups" 
                                    value="{{ old('max_backups', $settings['max_backups'] ?? 30) }}" min="1" max="365">
                                <div class="form-text">Older backups will be automatically deleted</div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <!-- Storage Configuration -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-hdd me-2"></i>Storage Configuration</h4>
                <small class="opacity-75">Configure backup storage location and options</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="backup_storage" class="form-label">Storage Location</label>
                                <select class="form-control" id="backup_storage" name="backup_storage">
                                    <option value="local" {{ old('backup_storage', $settings['backup_storage'] ?? 'local') == 'local' ? 'selected' : '' }}>Local Storage</option>
                                    <option value="s3" {{ old('backup_storage', $settings['backup_storage'] ?? 'local') == 's3' ? 'selected' : '' }}>Amazon S3</option>
                                    <option value="dropbox" {{ old('backup_storage', $settings['backup_storage'] ?? 'local') == 'dropbox' ? 'selected' : '' }}>Dropbox</option>
                                    <option value="google" {{ old('backup_storage', $settings['backup_storage'] ?? 'local') == 'google' ? 'selected' : '' }}>Google Drive</option>
                                </select>
                                <div class="form-text">Where backup files will be stored</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="backup_path" class="form-label">Backup Path</label>
                                <input type="text" class="form-control" id="backup_path" name="backup_path" 
                                    value="{{ old('backup_path', $settings['backup_path'] ?? 'storage/app/backups') }}">
                                <div class="form-text">Directory where backups will be stored</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="backup_compression" name="backup_compression" value="1" 
                                    {{ old('backup_compression', $settings['backup_compression'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="backup_compression">
                                    Enable Compression
                                </label>
                                <div class="form-text">Compress backup files to save space</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="backup_encryption" name="backup_encryption" value="1" 
                                    {{ old('backup_encryption', $settings['backup_encryption'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="backup_encryption">
                                    Enable Encryption
                                </label>
                                <div class="form-text">Encrypt backup files for security</div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-bell me-2"></i>Notification Settings</h4>
                <small class="opacity-75">Configure backup notification preferences</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="backup_notifications" name="backup_notifications" value="1" 
                                    {{ old('backup_notifications', $settings['backup_notifications'] ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="backup_notifications">
                                    Enable Email Notifications
                                </label>
                                <div class="form-text">Send backup status notifications via email</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="notification_email" class="form-label">Notification Email</label>
                                <input type="email" class="form-control" id="notification_email" name="notification_email" 
                                    value="{{ old('notification_email', $settings['notification_email'] ?? config('mail.from.address')) }}">
                                <div class="form-text">Email address to receive notifications</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Notification Events</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify_success" name="notify_events[]" value="success" 
                                                {{ in_array('success', old('notify_events', explode(',', $settings['notify_events'] ?? 'success,failure'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_success">
                                                Successful backups
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify_failure" name="notify_events[]" value="failure" 
                                                {{ in_array('failure', old('notify_events', explode(',', $settings['notify_events'] ?? 'success,failure'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_failure">
                                                Failed backups
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify_cleanup" name="notify_events[]" value="cleanup" 
                                                {{ in_array('cleanup', old('notify_events', explode(',', $settings['notify_events'] ?? 'success,failure'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_cleanup">
                                                Cleanup operations
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="notify_restore" name="notify_events[]" value="restore" 
                                                {{ in_array('restore', old('notify_events', explode(',', $settings['notify_events'] ?? 'success,failure'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notify_restore">
                                                Restore operations
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-section text-center">
            <button type="submit" class="btn btn-primary btn-lg me-3">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
            <button type="button" class="btn btn-secondary btn-lg me-3" onclick="location.reload()">
                <i class="fas fa-undo me-2"></i>Reset
            </button>
            <a href="{{ contextRoute('settings.index') }}" class="btn btn-info btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Back to Settings
            </a>
        </div>
    </form>

    <!-- Backup Actions -->
    <div class="form-section">
        <div class="form-section-header">
            <h4 class="mb-0"><i class="fas fa-tools me-2"></i>Backup Operations</h4>
            <small class="opacity-75">Manual backup and restore operations</small>
        </div>
        <div class="form-section-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <button type="button" class="btn btn-success btn-lg w-100" onclick="createBackup()">
                        <i class="fas fa-plus me-2"></i>
                        Create Backup Now
                    </button>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="button" class="btn btn-warning btn-lg w-100" onclick="showRestoreModal()">
                        <i class="fas fa-upload me-2"></i>
                        Restore Backup
                    </button>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="button" class="btn btn-info btn-lg w-100" onclick="downloadLatestBackup()">
                        <i class="fas fa-download me-2"></i>
                        Download Latest
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Database Safety Statistics -->
    @if(isset($hasData) && $hasData)
    <div class="form-section">
        <div class="form-section-header">
            <h4 class="mb-0"><i class="fas fa-shield-alt me-2 text-warning"></i>Database Safety Information</h4>
            <small class="opacity-75">Current database statistics for safety monitoring</small>
        </div>
        <div class="form-section-body">
            @if(isset($databaseStats) && !empty($databaseStats))
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Current Database Status:</strong> The following data exists in your database:
            </div>
            <div class="row">
                @foreach($databaseStats as $label => $count)
                <div class="col-md-3 mb-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="mb-1">{{ number_format($count) }}</h5>
                            <small class="text-muted">{{ $label }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Safety Reminder:</strong> Always create a backup before running destructive operations like <code>migrate:fresh</code>. 
                Use <code>php artisan migrate:safe-fresh --backup</code> for safe migrations.
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Backups -->
    <div class="form-section">
        <div class="form-section-header">
            <h4 class="mb-0"><i class="fas fa-history me-2"></i>Recent Backups</h4>
            <small class="opacity-75">View and manage existing backup files</small>
        </div>
        <div class="form-section-body">
            @if(isset($backupFiles) && !empty($backupFiles))
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Compressed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backupFiles as $backup)
                            <tr>
                                <td>
                                    <div>{{ $backup['created_at'] }}</div>
                                    <small class="text-muted">{{ $backup['created_at_human'] }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $backup['filename'] }}</span>
                                </td>
                                <td>{{ $backup['size_formatted'] }}</td>
                                <td>
                                    @if($backup['compressed'])
                                        <span class="badge bg-success"><i class="fas fa-compress"></i> Yes</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-file"></i> No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.settings.backup.download', ['id' => $backup['filename']]) }}" 
                                           class="btn btn-outline-primary" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" 
                                                onclick="deleteBackup('{{ $backup['filename'] }}')" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif(isset($recentBackups) && $recentBackups->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBackups as $backup)
                            <tr>
                                <td>
                                    <div>{{ formatDate($backup['created_at']) }}</div>
                                    <small class="text-muted">{{ $backup['created_at']->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $backup['filename'] }}</span>
                                </td>
                                <td>{{ $backup['size'] }}</td>
                                <td>
                                    <span class="badge {{ $backup['type'] === 'Manual' ? 'bg-secondary' : 'bg-primary' }}">
                                        {{ $backup['type'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $backup['status'] === 'Completed' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $backup['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="downloadBackup('{{ $backup['id'] }}')" title="Download">
                                            <i class="fas fa-download"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="deleteBackup('{{ $backup['id'] }}')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-database text-muted mb-3" style="font-size: 48px;"></i>
                    <h6 class="text-muted">No backups found</h6>
                    <p class="text-muted mb-0">Create your first backup using the "Create Backup Now" button above.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Application Footer -->
    @if(shouldShowPoweredBy())
    <div class="text-center mt-5 py-4" style="border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="fas fa-hospital" style="color: #e94560;"></i>
            <span>{!! getPoweredByText() !!}</span>
        </div>
        <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
            {{ getCopyrightText() }}
        </div>
    </div>
    @endif
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreModalLabel">Restore Backup</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="restoreForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="backupFile" class="form-label">Select Backup File</label>
                        <input type="file" class="form-control" id="backupFile" name="backup_file" accept=".sql,.zip,.gz" required>
                        <small class="form-text text-muted">Supported formats: .sql, .zip, .gz</small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Restoring a backup will overwrite all current data. This action cannot be undone.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmRestore()">
                    <i class="fas fa-upload me-2"></i>
                    Restore Backup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Fix Backup Configuration form overflow */
.backup-config-form {
    max-width: 100% !important;
    overflow-x: hidden !important;
}

.backup-config-form .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
    max-width: 100% !important;
}

.backup-config-form .col-12,
.backup-config-form .col-md-3,
.backup-config-form .col-md-4,
.backup-config-form .col-md-6 {
    padding-left: 15px !important;
    padding-right: 15px !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.backup-config-form .form-control,
.backup-config-form .form-select {
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.backup-config-form textarea {
    max-width: 100% !important;
    resize: vertical !important;
}

.backup-config-form .d-flex {
    flex-wrap: wrap !important;
    max-width: 100% !important;
}

.backup-config-form .btn {
    margin-bottom: 10px !important;
}

/* Ensure admin-card contains its content */
.admin-card {
    overflow: hidden !important;
}

.admin-card .card-body {
    overflow-x: hidden !important;
    padding: 20px !important;
}
</style>
@endpush

@push('scripts')
<script>
    // Toggle backup frequency based on auto backup
    document.getElementById('auto_backup').addEventListener('change', function() {
        const fields = ['backup_frequency', 'backup_time', 'max_backups'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.disabled = !this.checked;
                element.closest('.form-group').style.opacity = this.checked ? '1' : '0.5';
            }
        });
    });

    // Toggle notification email based on notifications
    document.getElementById('backup_notifications').addEventListener('change', function() {
        const emailField = document.getElementById('notification_email');
        const eventsContainer = document.querySelector('[name="notify_events[]"]').closest('.form-group');
        
        if (emailField) {
            emailField.disabled = !this.checked;
            emailField.closest('.form-group').style.opacity = this.checked ? '1' : '0.5';
        }
        
        if (eventsContainer) {
            eventsContainer.style.opacity = this.checked ? '1' : '0.5';
            const checkboxes = eventsContainer.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => cb.disabled = !this.checked);
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('auto_backup').dispatchEvent(new Event('change'));
        document.getElementById('backup_notifications').dispatchEvent(new Event('change'));
    });

    async function createBackup() {
        const compress = confirm('Would you like to compress the backup? (Recommended - saves disk space)');
        
        const confirmed = await confirmAction('Create a new backup of the database?', {
            title: 'Create Backup',
            confirmText: 'Create Backup',
            icon: 'fas fa-plus',
            confirmClass: 'btn-success'
        });
        
        if (confirmed) {
            const formData = new FormData();
            if (compress) {
                formData.append('compress', '1');
            }
            formData.append('keep', '7');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            ajaxWithModal({
                url: '{{ route('admin.settings.create-backup') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                loadingMessage: 'Creating backup... This may take a moment.',
                successMessage: 'Backup created successfully!',
                errorMessage: 'Failed to create backup. Please try again.',
                onSuccess: function(response) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            });
        }
    }

    function showRestoreModal() {
        const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
        modal.show();
    }

    async function confirmRestore() {
        const fileInput = document.getElementById('backupFile');
        if (!fileInput.files[0]) {
            alert('Please select a backup file to restore.');
            return;
        }

        const confirmed = await confirmAction('This will overwrite all current data. Are you sure?', {
            title: 'Restore Backup',
            confirmText: 'Restore',
            icon: 'fas fa-exclamation-triangle',
            confirmClass: 'btn-danger'
        });
        
        if (confirmed) {
            const formData = new FormData();
            formData.append('backup_file', fileInput.files[0]);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            ajaxWithModal({
                url: '/admin/settings/restore-backup',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                loadingMessage: 'Restoring backup...',
                successMessage: 'Backup restored successfully!',
                errorMessage: 'Failed to restore backup. Please try again.',
                onSuccess: function(response) {
                    document.getElementById('restoreModal').style.display = 'none';
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            });
        }
    }

    async function downloadLatestBackup() {
        showLoading('Preparing download...');
        window.open('/admin/settings/backup/latest/download', '_blank');
        
        setTimeout(() => {
            document.querySelectorAll('#loadingModal .modal').forEach(modal => {
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) instance.hide();
            });
        }, 2000);
    }

    async function downloadBackup(filename) {
        showLoading('Preparing download...');
        const encodedFilename = encodeURIComponent(filename);
        window.open(`/admin/settings/backup/${encodedFilename}/download`, '_blank');
        
        setTimeout(() => {
            document.querySelectorAll('#loadingModal .modal').forEach(modal => {
                const instance = bootstrap.Modal.getInstance(modal);
                if (instance) instance.hide();
            });
        }, 2000);
    }

    async function deleteBackup(filename) {
        const confirmed = await confirmAction('Delete this backup? This action cannot be undone.', {
            title: 'Delete Backup',
            confirmText: 'Delete',
            icon: 'fas fa-trash',
            confirmClass: 'btn-danger'
        });
        
        if (confirmed) {
            // Encode filename for URL
            const encodedFilename = encodeURIComponent(filename);
            
            ajaxWithModal({
                url: `/admin/settings/backup/${encodedFilename}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                loadingMessage: 'Deleting backup...',
                successMessage: 'Backup deleted successfully!',
                errorMessage: 'Failed to delete backup. Please try again.',
                onSuccess: function(response) {
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            });
        }
    }
</script>
@endpush
