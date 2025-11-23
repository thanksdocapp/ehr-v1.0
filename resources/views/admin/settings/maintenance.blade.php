@extends('admin.layouts.app')

@section('title', 'Maintenance Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Maintenance</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-tools me-2 text-primary"></i>Maintenance Settings</h1>
        <p class="page-subtitle text-muted">Configure maintenance mode and system updates</p>
    </div>

    <!-- Current Status Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon {{ ($settings['maintenance_mode'] ?? false) ? 'warning' : 'success' }}">
                    <i class="fas {{ ($settings['maintenance_mode'] ?? false) ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                </div>
                <div class="stat-value">{{ ($settings['maintenance_mode'] ?? false) ? 'Maintenance' : 'Active' }}</div>
                <div class="stat-label">Current Mode</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value">{{ now()->format('H:i') }}</div>
                <div class="stat-label">Current Time</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="stat-value">{{ ($settings['auto_update'] ?? false) ? 'On' : 'Off' }}</div>
                <div class="stat-label">Auto Updates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-broom"></i>
                </div>
                <div class="stat-value">{{ ($settings['auto_cleanup'] ?? true) ? 'On' : 'Off' }}</div>
                <div class="stat-label">Auto Cleanup</div>
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
    
    <form action="{{ contextRoute('settings.maintenance.update') }}" method="POST" id="maintenanceForm">
        @csrf
        @method('PUT')
        
        <!-- Maintenance Mode Settings -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-power-off me-2"></i>Maintenance Mode</h4>
                <small class="opacity-75">Configure maintenance mode and access controls</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" 
                                    {{ old('maintenance_mode', $settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="maintenance_mode">
                                    Enable Maintenance Mode
                                </label>
                                <div class="form-text">Put the site in maintenance mode (only administrators can access)</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="allowed_ips" class="form-label">Allowed IP Addresses</label>
                                <textarea class="form-control" id="allowed_ips" name="allowed_ips" rows="3" 
                                    placeholder="Enter IP addresses or ranges, one per line&#10;Example:&#10;192.168.1.100&#10;10.0.0.0/24">{{ old('allowed_ips', $settings['allowed_ips'] ?? '') }}</textarea>
                                <div class="form-text">IP addresses that can access the site during maintenance (one per line)</div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Message -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-comment-alt me-2"></i>Maintenance Message</h4>
                <small class="opacity-75">Configure messages displayed to users during maintenance</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_title" class="form-label">Maintenance Title</label>
                                <input type="text" class="form-control" id="maintenance_title" name="maintenance_title" 
                                    value="{{ old('maintenance_title', $settings['maintenance_title'] ?? 'Site Under Maintenance') }}" 
                                    placeholder="Site Under Maintenance">
                                <div class="form-text">Title displayed during maintenance mode</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_retry_after" class="form-label">Retry After (minutes)</label>
                                <input type="number" class="form-control" id="maintenance_retry_after" name="maintenance_retry_after" 
                                    value="{{ old('maintenance_retry_after', $settings['maintenance_retry_after'] ?? 60) }}" 
                                    placeholder="60" min="1">
                                <div class="form-text">How long browsers should wait before retrying</div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="maintenance_message" class="form-label">Maintenance Message</label>
                                <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="4" 
                                    placeholder="We are currently performing scheduled maintenance. Please check back soon.">{{ old('maintenance_message', $settings['maintenance_message'] ?? 'We are currently performing scheduled maintenance. Please check back soon.') }}</textarea>
                                <div class="form-text">Message displayed to users during maintenance mode</div>
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <!-- Auto-Update Settings -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Auto-Update Settings</h4>
                <small class="opacity-75">Configure automatic system updates</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="auto_update" name="auto_update" value="1" 
                                    {{ old('auto_update', $settings['auto_update'] ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_update">
                                    Enable Auto Updates
                                </label>
                                <div class="form-text">Automatically install security updates</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="update_check_frequency" class="form-label">Update Check Frequency</label>
                                <select class="form-control" id="update_check_frequency" name="update_check_frequency">
                                    <option value="daily" {{ old('update_check_frequency', $settings['update_check_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ old('update_check_frequency', $settings['update_check_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('update_check_frequency', $settings['update_check_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="never" {{ old('update_check_frequency', $settings['update_check_frequency'] ?? 'daily') == 'never' ? 'selected' : '' }}>Never</option>
                                </select>
                                <div class="form-text">How often to check for available updates</div>
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
            </div>
        </div>
    </div>

    <!-- ThanksDoc EHR Footer -->
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
@endsection

@push('styles')
@include('admin.shared.styles')
<style>
/* Fix Maintenance Configuration form overflow */
.maintenance-config-form {
    max-width: 100% !important;
    overflow-x: hidden !important;
}

.maintenance-config-form .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
    max-width: 100% !important;
}

.maintenance-config-form .col-12,
.maintenance-config-form .col-md-3,
.maintenance-config-form .col-md-4,
.maintenance-config-form .col-md-6 {
    padding-left: 15px !important;
    padding-right: 15px !important;
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.maintenance-config-form .form-control,
.maintenance-config-form .form-select {
    max-width: 100% !important;
    box-sizing: border-box !important;
}

.maintenance-config-form textarea {
    max-width: 100% !important;
    resize: vertical !important;
}

.maintenance-config-form .d-flex {
    flex-wrap: wrap !important;
    max-width: 100% !important;
}

.maintenance-config-form .btn {
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
// Form submission with loading state
document.getElementById('maintenanceForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i> Saving...';
    
    // Re-enable button after form submission
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }, 3000);
});

// Toggle update frequency based on auto update
document.getElementById('auto_update').addEventListener('change', function() {
    const updateFreq = document.getElementById('update_check_frequency');
    if (updateFreq) {
        // Don't disable the field, just change its appearance
        // updateFreq.disabled = !this.checked;
        updateFreq.closest('.mb-3').style.opacity = this.checked ? '1' : '0.5';
        
        // If auto_update is disabled, set update_check_frequency to 'never'
        if (!this.checked) {
            updateFreq.value = 'never';
        } else if (updateFreq.value === 'never') {
            updateFreq.value = 'daily';
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Trigger the change event to set initial state
    const autoUpdateCheckbox = document.getElementById('auto_update');
    if (autoUpdateCheckbox) {
        autoUpdateCheckbox.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
