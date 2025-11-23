@extends('admin.layouts.app')

@section('title', 'Alert Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Patient Alerts</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Patient Alert Settings</h1>
        <p class="page-subtitle text-muted">Configure patient alert logging and notification settings</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['total_alerts'] ?? 0) }}</div>
                <div class="stat-label">Total Alerts</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['active_alerts'] ?? 0) }}</div>
                <div class="stat-label">Active Alerts</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['critical_alerts'] ?? 0) }}</div>
                <div class="stat-label">Critical Alerts</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['alert_activities'] ?? 0) }}</div>
                <div class="stat-label">Logged Activities</div>
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
    
    <form action="{{ route('admin.settings.alerts.update') }}" method="POST" id="alertsForm">
        @csrf

        <!-- Alert Logging Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2"></i>Alert Logging Configuration</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_alert_logging" 
                                   name="enable_alert_logging" value="1" 
                                   {{ ($settings['enable_alert_logging'] ?? '1') == '1' ? 'checked' : '' }}
                                   onchange="toggleLoggingOptions(this.checked)">
                            <label class="form-check-label" for="enable_alert_logging">
                                <strong>Enable Alert Logging</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Log all patient alert activities to the audit trail
                            </small>
                        </div>
                    </div>
                </div>

                <div id="loggingOptions" style="{{ ($settings['enable_alert_logging'] ?? '1') == '1' ? '' : 'display: none;' }}">
                    <hr>
                    <h6 class="mb-3"><i class="fas fa-list-check me-2"></i>Log Specific Actions</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="log_alert_creation" 
                                       name="log_alert_creation" value="1" 
                                       {{ ($settings['log_alert_creation'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="log_alert_creation">
                                    Log Alert Creation
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="log_alert_update" 
                                       name="log_alert_update" value="1" 
                                       {{ ($settings['log_alert_update'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="log_alert_update">
                                    Log Alert Updates
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="log_alert_deletion" 
                                       name="log_alert_deletion" value="1" 
                                       {{ ($settings['log_alert_deletion'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="log_alert_deletion">
                                    Log Alert Deletion
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="log_alert_activation" 
                                       name="log_alert_activation" value="1" 
                                       {{ ($settings['log_alert_activation'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="log_alert_activation">
                                    Log Alert Activation
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="log_alert_deactivation" 
                                       name="log_alert_deactivation" value="1" 
                                       {{ ($settings['log_alert_deactivation'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="log_alert_deactivation">
                                    Log Alert Deactivation
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3"><i class="fas fa-filter me-2"></i>Log by Severity Level</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="log_severity_critical" 
                                       name="log_severity_levels[]" value="critical" 
                                       {{ in_array('critical', explode(',', $settings['log_severity_levels'] ?? 'critical,high')) ? 'checked' : '' }}>
                                <label class="form-check-label text-danger" for="log_severity_critical">
                                    <strong>Critical</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="log_severity_high" 
                                       name="log_severity_levels[]" value="high" 
                                       {{ in_array('high', explode(',', $settings['log_severity_levels'] ?? 'critical,high')) ? 'checked' : '' }}>
                                <label class="form-check-label text-warning" for="log_severity_high">
                                    <strong>High</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="log_severity_medium" 
                                       name="log_severity_levels[]" value="medium" 
                                       {{ in_array('medium', explode(',', $settings['log_severity_levels'] ?? '')) ? 'checked' : '' }}>
                                <label class="form-check-label text-info" for="log_severity_medium">
                                    <strong>Medium</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="log_severity_low" 
                                       name="log_severity_levels[]" value="low" 
                                       {{ in_array('low', explode(',', $settings['log_severity_levels'] ?? '')) ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" for="log_severity_low">
                                    <strong>Low</strong>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="log_severity_info" 
                                       name="log_severity_levels[]" value="info" 
                                       {{ in_array('info', explode(',', $settings['log_severity_levels'] ?? '')) ? 'checked' : '' }}>
                                <label class="form-check-label text-muted" for="log_severity_info">
                                    <strong>Info</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alert_retention_days" class="form-label">Alert Log Retention (Days)</label>
                            <input type="number" class="form-control" id="alert_retention_days" 
                                   name="alert_retention_days" 
                                   value="{{ $settings['alert_retention_days'] ?? '365' }}" 
                                   min="1" max="3650">
                            <small class="form-text text-muted">
                                How long to keep alert activity logs (1-3650 days)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Expiry Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Alert Expiry & Notifications</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_expire_alerts" 
                                   name="auto_expire_alerts" value="1" 
                                   {{ ($settings['auto_expire_alerts'] ?? '0') == '1' ? 'checked' : '' }}
                                   onchange="toggleExpiryOptions(this.checked)">
                            <label class="form-check-label" for="auto_expire_alerts">
                                <strong>Auto-Expire Alerts</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Automatically deactivate alerts when they reach their expiry date
                            </small>
                        </div>
                    </div>
                </div>

                <div id="expiryOptions" style="{{ ($settings['auto_expire_alerts'] ?? '0') == '1' ? '' : 'display: none;' }}">
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="alert_expiry_notification" 
                                       name="alert_expiry_notification" value="1" 
                                       {{ ($settings['alert_expiry_notification'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert_expiry_notification">
                                    Send Expiry Notifications
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expiry_notification_days" class="form-label">Notification Days Before Expiry</label>
                            <input type="number" class="form-control" id="expiry_notification_days" 
                                   name="expiry_notification_days" 
                                   value="{{ $settings['expiry_notification_days'] ?? '7' }}" 
                                   min="1" max="30">
                            <small class="form-text text-muted">
                                Days before expiry to send notification (1-30 days)
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Email Notifications -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-envelope me-2"></i>Email Notifications</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="email_on_critical_alert" 
                                   name="email_on_critical_alert" value="1" 
                                   {{ ($settings['email_on_critical_alert'] ?? '0') == '1' ? 'checked' : '' }}
                                   onchange="toggleEmailOptions(this.checked)">
                            <label class="form-check-label" for="email_on_critical_alert">
                                <strong>Email on Critical Alerts</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Send email notifications when critical alerts are created
                            </small>
                        </div>
                    </div>
                </div>

                <div id="emailOptions" style="{{ ($settings['email_on_critical_alert'] ?? '0') == '1' ? '' : 'display: none;' }}">
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="email_recipients" class="form-label">Email Recipients</label>
                            <input type="text" class="form-control" id="email_recipients" 
                                   name="email_recipients" 
                                   value="{{ $settings['email_recipients'] ?? '' }}" 
                                   placeholder="email1@example.com, email2@example.com">
                            <small class="form-text text-muted">
                                Comma-separated list of email addresses to notify
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Alert Summary Reports</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="alert_summary_enabled" 
                                   name="alert_summary_enabled" value="1" 
                                   {{ ($settings['alert_summary_enabled'] ?? '0') == '1' ? 'checked' : '' }}
                                   onchange="toggleSummaryOptions(this.checked)">
                            <label class="form-check-label" for="alert_summary_enabled">
                                <strong>Enable Alert Summary Reports</strong>
                            </label>
                            <small class="form-text text-muted d-block">
                                Generate and send periodic summary reports of patient alerts
                            </small>
                        </div>
                    </div>
                </div>

                <div id="summaryOptions" style="{{ ($settings['alert_summary_enabled'] ?? '0') == '1' ? '' : 'display: none;' }}">
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alert_summary_frequency" class="form-label">Summary Frequency</label>
                            <select class="form-select" id="alert_summary_frequency" name="alert_summary_frequency">
                                <option value="daily" {{ ($settings['alert_summary_frequency'] ?? 'weekly') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($settings['alert_summary_frequency'] ?? 'weekly') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($settings['alert_summary_frequency'] ?? 'weekly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                            <small class="form-text text-muted">
                                How often to generate alert summary reports
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Settings
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Alert Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    function toggleLoggingOptions(enabled) {
        document.getElementById('loggingOptions').style.display = enabled ? 'block' : 'none';
    }

    function toggleExpiryOptions(enabled) {
        document.getElementById('expiryOptions').style.display = enabled ? 'block' : 'none';
    }

    function toggleEmailOptions(enabled) {
        document.getElementById('emailOptions').style.display = enabled ? 'block' : 'none';
    }

    function toggleSummaryOptions(enabled) {
        document.getElementById('summaryOptions').style.display = enabled ? 'block' : 'none';
    }
</script>
@endpush

