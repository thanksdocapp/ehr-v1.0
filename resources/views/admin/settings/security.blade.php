@extends('admin.layouts.app')

@section('title', 'Security Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Security</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1 class="mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Security Settings</h1>
        <p class="page-subtitle text-muted">Configure security policies and authentication settings</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['active_users_today'] ?? 0) }}</div>
                <div class="stat-label">Active Users Today</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['failed_logins'] ?? 0) }}</div>
                <div class="stat-label">Failed Login Attempts</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['two_fa_users'] ?? 0) }}</div>
                <div class="stat-label">2FA Enabled Users</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value">{{ $statistics['security_score'] ?? 85 }}%</div>
                <div class="stat-label">Security Score</div>
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
    
    <form action="{{ contextRoute('settings.security.update') }}" method="POST" id="securityForm">
        @csrf
        @method('PUT')

        <!-- Authentication Settings -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-key me-2"></i>Authentication Settings</h4>
                <small class="opacity-75">Configure login and session security settings</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="login_attempts" class="form-label">Max Login Attempts</label>
                            <input type="number" class="form-control" id="login_attempts" name="login_attempts" 
                                   value="{{ $settings['login_attempts'] ?? 5 }}" min="1" max="20">
                            <div class="form-text">Maximum failed login attempts before account lockout</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="lockout_duration" class="form-label">Lockout Duration (minutes)</label>
                            <input type="number" class="form-control" id="lockout_duration" name="lockout_duration" 
                                   value="{{ $settings['lockout_duration'] ?? 15 }}" min="1" max="1440">
                            <div class="form-text">Duration to lock account after max attempts reached</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                   value="{{ $settings['session_timeout'] ?? 120 }}" min="5" max="480">
                            <div class="form-text">Auto logout users after inactivity</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password_expiry" class="form-label">Password Expiry (days)</label>
                            <input type="number" class="form-control" id="password_expiry" name="password_expiry" 
                                   value="{{ $settings['password_expiry'] ?? 90 }}" min="0" max="365">
                            <div class="form-text">Force password change after specified days (0 = never)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Policy -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Password Policy</h4>
                <small class="opacity-75">Configure password requirements and complexity</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="min_password_length" class="form-label">Minimum Password Length</label>
                            <input type="number" class="form-control" id="min_password_length" name="min_password_length" 
                                   value="{{ $settings['min_password_length'] ?? 8 }}" min="6" max="50">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password_history" class="form-label">Password History</label>
                            <input type="number" class="form-control" id="password_history" name="password_history" 
                                   value="{{ $settings['password_history'] ?? 3 }}" min="0" max="10">
                            <div class="form-text">Prevent reusing last N passwords</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_uppercase" name="require_uppercase" 
                                           {{ ($settings['require_uppercase'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_uppercase">
                                        Require Uppercase
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_lowercase" name="require_lowercase" 
                                           {{ ($settings['require_lowercase'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_lowercase">
                                        Require Lowercase
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_numbers" name="require_numbers" 
                                           {{ ($settings['require_numbers'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_numbers">
                                        Require Numbers
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_special_chars" name="require_special_chars" 
                                           {{ ($settings['require_special_chars'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="require_special_chars">
                                        Require Special Characters
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two-Factor Authentication -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-mobile-alt me-2"></i>Two-Factor Authentication</h4>
                <small class="opacity-75">Configure 2FA requirements for users</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="force_2fa" name="force_2fa" 
                                   {{ ($settings['force_2fa'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="force_2fa">
                                Force 2FA for All Users
                            </label>
                            <div class="form-text">Require all users to enable two-factor authentication</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="force_admin_2fa" name="force_admin_2fa" 
                                   {{ ($settings['force_admin_2fa'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="force_admin_2fa">
                                Force 2FA for Admin Users
                            </label>
                            <div class="form-text">Require all admin users to enable two-factor authentication</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IP Restrictions -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-network-wired me-2"></i>IP Restrictions</h4>
                <small class="opacity-75">Configure IP address access controls</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    
                    <div class="col-md-6">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_ip_whitelist" name="enable_ip_whitelist" 
                                   {{ ($settings['enable_ip_whitelist'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_ip_whitelist">
                                Enable IP Whitelist
                            </label>
                            <div class="form-text">Only allow access from whitelisted IP addresses</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="mb-3">
                            <label for="allowed_ips" class="form-label">Allowed IP Addresses</label>
                            <textarea class="form-control" id="allowed_ips" name="allowed_ips" rows="3" 
                                      placeholder="Enter IP addresses or ranges, one per line&#10;Example:&#10;192.168.1.100&#10;10.0.0.0/24">{{ $settings['allowed_ips'] ?? '' }}</textarea>
                            <div class="form-text">Enter IP addresses or CIDR ranges, one per line</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Features -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Features</h4>
                <small class="opacity-75">Configure additional security features</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_captcha" name="enable_captcha" 
                                   {{ ($settings['enable_captcha'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_captcha">
                                Enable CAPTCHA
                            </label>
                            <div class="form-text">Show CAPTCHA on login forms</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_login_notifications" name="enable_login_notifications" 
                                   {{ ($settings['enable_login_notifications'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_login_notifications">
                                Login Notifications
                            </label>
                            <div class="form-text">Send email on successful logins</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="enable_device_tracking" name="enable_device_tracking" 
                                   {{ ($settings['enable_device_tracking'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="enable_device_tracking">
                                Device Tracking
                            </label>
                            <div class="form-text">Track and alert on new devices</div>
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

    <!-- Recent Active Users -->
    <div class="form-section">
        <div class="form-section-header">
            <h4 class="mb-0"><i class="fas fa-users me-2"></i>Recent Active Users</h4>
            <small class="opacity-75">Hospital staff who logged in within the last 24 hours</small>
        </div>
        <div class="form-section-body">
            @if($activeUsers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeUsers as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $avatarColors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger'];
                                            $colorClass = $avatarColors[($user->id ?? 0) % count($avatarColors)];
                                            $initials = strtoupper(substr($user->name ?? 'U', 0, 1) . substr($user->email ?? 'ser', 0, 1));
                                        @endphp
                                        <div class="rounded-circle {{ $colorClass }} text-white d-flex align-items-center justify-content-center me-2" 
                                             style="width: 32px; height: 32px; font-size: 12px; font-weight: bold;">
                                            {{ $initials }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->name ?? 'Unknown User' }}</div>
                                            <small class="text-muted">{{ $user->email ?? 'No email' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($user->role ?? 'staff') }}</span>
                                </td>
                                <td>
                                    @php
                                        $loginTime = $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at) : null;
                                    @endphp
                                    @if($loginTime)
                                        <div>{{ formatDate($loginTime) }}</div>
                                        <small class="text-muted">{{ $loginTime->format('h:i A') }}</small>
                                    @else
                                        <span class="text-muted">No login recorded</span>
                                    @endif
                                </td>
                                <td>
                                    @if($loginTime && $loginTime->isToday())
                                        <span class="badge bg-success">Active Today</span>
                                    @elseif($loginTime && $loginTime->isYesterday())
                                        <span class="badge bg-warning">Yesterday</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary view-user-btn" 
                                                data-user-id="{{ $user->id }}" 
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                            <button class="btn btn-outline-warning view-sessions-btn" 
                                                    data-user-id="{{ $user->id }}" 
                                                    title="View Sessions">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users text-muted mb-3" style="font-size: 48px;"></i>
                    <h6 class="text-muted">No recent active users</h6>
                    <p class="text-muted mb-0">Users will appear here when they log in to the system.</p>
                </div>
            @endif
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

<!-- Test Security Configuration Modal -->
<div class="modal fade" id="testSecurityModal" tabindex="-1" aria-labelledby="testSecurityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testSecurityModalLabel">Test Security Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="securityTestResult">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Testing...</span>
                        </div>
                        <p class="mt-2">Testing security configuration...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('admin.shared.styles')
<style>
/* Ensure Consistent Layout and Style */
.form-section {
    max-width: 100%;
    overflow-x: auto;
}

.table {
    width: 100%;
    table-layout: fixed;
}

.table th, .table td {
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.table .btn-group .btn {
    padding: 2px 6px;
    font-size: 12px;
}

.btn {
    pointer-events: auto;
    cursor: pointer;
}
</style>
@endpush

@section('scripts')
<script>
function testSecurityConfig() {
    console.log('testSecurityConfig() called');
    const modal = new bootstrap.Modal(document.getElementById('testSecurityModal'));
    modal.show();
    
    // Reset modal content
    document.getElementById('securityTestResult').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Testing...</span>
            </div>
            <p class="mt-2">Testing security configuration...</p>
        </div>
    `;
    
    // Get current form values
    const formData = {
        login_attempts: $('#login_attempts').val(),
        lockout_duration: $('#lockout_duration').val(),
        session_timeout: $('#session_timeout').val(),
        password_expiry: $('#password_expiry').val(),
        min_password_length: $('#min_password_length').val(),
        password_history: $('#password_history').val(),
        require_uppercase: $('#require_uppercase').is(':checked'),
        require_lowercase: $('#require_lowercase').is(':checked'),
        require_numbers: $('#require_numbers').is(':checked'),
        require_special_chars: $('#require_special_chars').is(':checked'),
        force_2fa: $('#force_2fa').is(':checked'),
        force_admin_2fa: $('#force_admin_2fa').is(':checked'),
        enable_ip_whitelist: $('#enable_ip_whitelist').is(':checked'),
        allowed_ips: $('#allowed_ips').val(),
        enable_captcha: $('#enable_captcha').is(':checked'),
        enable_login_notifications: $('#enable_login_notifications').is(':checked'),
        enable_device_tracking: $('#enable_device_tracking').is(':checked')
    };
    
    // Send AJAX request to test security configuration
    $.ajax({
        url: '/admin/api/test-security',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(formData),
        success: function(response) {
            let resultHtml = `
                <div class="alert alert-success" role="alert">
                    <h6 class="alert-heading"><i class="mdi mdi-check-circle me-2"></i>Security Test Completed</h6>
                    <hr>
                    <ul class="mb-0">
            `;
            
            if (response.results) {
                for (const [key, result] of Object.entries(response.results)) {
                    const icon = result.status === 'passed' ? '✅' : result.status === 'enabled' ? '🔒' : result.status === 'configured' ? '⚙️' : '💡';
                    resultHtml += `<li>${icon} ${result.message}: <strong>${result.status.charAt(0).toUpperCase() + result.status.slice(1)}</strong></li>`;
                }
            }
            
            resultHtml += `
                    </ul>
                </div>
            `;
            
            document.getElementById('securityTestResult').innerHTML = resultHtml;
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Failed to test security configuration.';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            document.getElementById('securityTestResult').innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h6 class="alert-heading"><i class="mdi mdi-alert-circle me-2"></i>Security Test Failed</h6>
                    <hr>
                    <p class="mb-0">${errorMessage}</p>
                </div>
            `;
        }
    });
}

function viewSecurityLogs() {
    console.log('viewSecurityLogs() called');
    // Redirect to security logs page
    window.location.href = '/admin/settings/security/logs';
}

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this session?')) {
        // AJAX call to terminate session
        fetch(`/admin/api/sessions/${sessionId}/terminate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to terminate session: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to terminate session');
        });
    }
}

function viewUserDetails(userId) {
    console.log('viewUserDetails() called for user:', userId);
    
    if (!userId) {
        console.error('No user ID provided');
        alert('Error: No user ID provided');
        return;
    }
    
    try {
        const userUrl = `/admin/users/${userId}`;
        console.log('Redirecting to:', userUrl);
        
        // Try different methods to ensure it works
        if (window.location) {
            window.location.href = userUrl;
        } else {
            document.location.href = userUrl;
        }
    } catch (error) {
        console.error('Error redirecting to user details:', error);
        alert('Error navigating to user details: ' + error.message);
    }
}

function viewUserSessions(userId) {
    console.log('viewUserSessions() called for user:', userId);
    
    if (!userId) {
        console.error('No user ID provided for sessions');
        alert('Error: No user ID provided');
        return;
    }
    
    // In a real implementation, this would show user's active sessions
    alert('User Sessions for ID: ' + userId + '\n\nThis would show detailed information about the user\'s sessions including:\n- Active sessions\n- Login history\n- Device information\n- Geographic locations\n- Session durations');
}

function viewSessionDetails(sessionId) {
    // In a real implementation, this would fetch session details from the server
    alert('Session Details for: ' + sessionId + '\n\nThis would show detailed information about the session including:\n- User agent details\n- Login history\n- Geographic location\n- Device fingerprint\n- Activity timeline');
}

function terminateAllSessions() {
    if (confirm('Are you sure you want to terminate ALL active sessions? This will log out all users except yourself.')) {
        // AJAX call to terminate all sessions
        fetch('/admin/api/sessions/terminate-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('All sessions terminated successfully!');
                location.reload();
            } else {
                alert('Failed to terminate sessions: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to terminate sessions');
        });
    }
}

// Form submission with loading state
document.getElementById('securityForm').addEventListener('submit', function(e) {
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

// Ensure buttons work with alternative event binding
$(document).ready(function() {
    // Alternative event handlers for buttons in case onclick doesn't work
    $('button:contains("Test Configuration")').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Test Configuration button clicked via jQuery');
        testSecurityConfig();
    });
    
    $('button:contains("View Security Logs")').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('View Security Logs button clicked via jQuery');
        viewSecurityLogs();
    });
    
    // Add event handlers for view user buttons using data attributes
    $('.view-user-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const userId = $(this).data('user-id');
        console.log('View user button clicked, user ID:', userId);
        
        if (userId) {
            viewUserDetails(userId);
        } else {
            console.error('No user ID found in data attribute');
            alert('Error: No user ID found');
        }
    });
    
    // Add event handlers for view sessions buttons using data attributes
    $('.view-sessions-btn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const userId = $(this).data('user-id');
        console.log('View sessions button clicked, user ID:', userId);
        
        if (userId) {
            viewUserSessions(userId);
        } else {
            console.error('No user ID found in data attribute');
            alert('Error: No user ID found');
        }
    });
    
    // Debug: Check if buttons exist
    console.log('Test Config button exists:', $('button:contains("Test Configuration")').length > 0);
    console.log('View Logs button exists:', $('button:contains("View Security Logs")').length > 0);
    console.log('View User buttons exist:', $('.btn-outline-primary').length);
    console.log('View Sessions buttons exist:', $('.btn-outline-warning').length);
});
</script>
@endsection
