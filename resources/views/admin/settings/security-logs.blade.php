@extends('admin.layouts.app')

@section('title', 'Security Logs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.security') }}">Security</a></li>
    <li class="breadcrumb-item active">Security Logs</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Title -->
    <div class="page-title">
        <h1>Security Logs</h1>
        <p class="page-subtitle">Monitor and review security-related activities</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['total_events'] ?? 0) }}</div>
                <div class="stat-label">Total Events</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['high_severity'] ?? 0) }}</div>
                <div class="stat-label">High Severity</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['failed_logins'] ?? 0) }}</div>
                <div class="stat-label">Failed Logins</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-value">{{ number_format($statistics['suspicious_activities'] ?? 0) }}</div>
                <div class="stat-label">Suspicious Activities</div>
            </div>
        </div>
    </div>

    <!-- Security Events Table -->
    <div class="admin-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title">Security Events</h5>
                <p class="card-title-desc">Monitor and review security-related activities</p>
            </div>
            <div>
                <div class="d-flex flex-wrap gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-1"></i> Filter by Severity
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="filterBySeverity('all')">All Severities</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterBySeverity('high')">High</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterBySeverity('medium')">Medium</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterBySeverity('warning')">Warning</a></li>
                            <li><a class="dropdown-item" href="#" onclick="filterBySeverity('info')">Info</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="exportLogs()">
                        <i class="fas fa-download me-1"></i> Export Logs
                    </button>
                    <button type="button" class="btn btn-danger" onclick="clearLogs()">
                        <i class="fas fa-trash me-1"></i> Clear Logs
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="securityLogsTable">
                    <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Event</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>Severity</th>
                                    <th>Details</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr data-severity="{{ $log['severity'] }}">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">{{ formatDate($log['created_at']) }}</span>
                                            <small class="text-muted">{{ $log['created_at']->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @switch($log['event'])
                                                @case('Failed Login Attempt')
                                                    <i class="mdi mdi-account-alert text-warning me-2"></i>
                                                    @break
                                                @case('Password Changed')
                                                    <i class="mdi mdi-key text-info me-2"></i>
                                                    @break
                                                @case('Suspicious Login')
                                                    <i class="mdi mdi-security text-danger me-2"></i>
                                                    @break
                                                @case('Account Locked')
                                                    <i class="mdi mdi-lock text-warning me-2"></i>
                                                    @break
                                                @case('2FA Enabled')
                                                    <i class="mdi mdi-two-factor-authentication text-success me-2"></i>
                                                    @break
                                                @default
                                                    <i class="mdi mdi-information text-primary me-2"></i>
                                            @endswitch
                                            <span>{{ $log['event'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar-xs">
                                                    <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                        {{ strtoupper(substr($log['user_email'], 0, 1)) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $log['user_email'] }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $log['ip_address'] }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $log['location'] }}</span>
                                    </td>
                                    <td>
                                        @switch($log['severity'])
                                            @case('high')
                                                <span class="badge bg-danger">High</span>
                                                @break
                                            @case('medium')
                                                <span class="badge bg-warning">Medium</span>
                                                @break
                                            @case('warning')
                                                <span class="badge bg-warning">Warning</span>
                                                @break
                                            @case('info')
                                                <span class="badge bg-info">Info</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($log['severity']) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="text-truncate" style="max-width: 200px; display: inline-block;" title="{{ $log['details'] }}">
                                            {{ $log['details'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="mdi mdi-dots-horizontal"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="viewLogDetails({{ $log['id'] }})">
                                                    <i class="mdi mdi-eye me-2"></i>View Details
                                                </a></li>
                                                <li><a class="dropdown-item" href="#" onclick="blockIP('{{ $log['ip_address'] }}')">
                                                    <i class="mdi mdi-block-helper me-2"></i>Block IP
                                                </a></li>
                                                @if($log['severity'] === 'high')
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="escalateIncident({{ $log['id'] }})">
                                                    <i class="mdi mdi-alert-circle me-2"></i>Escalate Incident
                                                </a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="mdi mdi-shield-check-outline display-4 text-muted mb-3"></i>
                                        <p class="mb-0">No security events found</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($logs->count() > 0)
                    <div class="row mt-4">
                        <div class="col-sm-6">
                            <div class="dataTables_info">
                                Showing {{ $logs->count() }} entries
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="dataTables_paginate paging_simple_numbers float-end">
                                <ul class="pagination pagination-rounded mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#">Previous</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">Security Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="logDetailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="exportSingleLog()">Export Event</button>
            </div>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div class="modal fade" id="blockIPModal" tabindex="-1" aria-labelledby="blockIPModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockIPModalLabel">Block IP Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to block the IP address <strong id="ipToBlock"></strong>?</p>
                <div class="mb-3">
                    <label for="blockReason" class="form-label">Reason for blocking</label>
                    <textarea class="form-control" id="blockReason" rows="3" placeholder="Enter reason for blocking this IP address"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmBlockIP()">Block IP Address</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentIPToBlock = '';

function filterBySeverity(severity) {
    const rows = document.querySelectorAll('#securityLogsTable tbody tr[data-severity]');
    
    rows.forEach(row => {
        if (severity === 'all' || row.getAttribute('data-severity') === severity) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function viewLogDetails(logId) {
    // Sample log details - in real implementation, this would fetch from server
    const logDetails = {
        1: {
            event: 'Failed Login Attempt',
            user_email: 'john@example.com',
            ip_address: '192.168.1.100',
            user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            location: 'New York, US',
            severity: 'warning',
            created_at: '2024-01-15 14:30:25',
            details: 'Multiple failed login attempts detected',
            additional_info: 'User attempted to login 5 times within 2 minutes using incorrect passwords.'
        }
    };
    
    const log = logDetails[logId] || logDetails[1]; // Fallback to first log
    
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Event Information</h6>
                <table class="table table-borderless table-sm">
                    <tr><td><strong>Event Type:</strong></td><td>${log.event}</td></tr>
                    <tr><td><strong>Severity:</strong></td><td><span class="badge bg-warning">${log.severity}</span></td></tr>
                    <tr><td><strong>Date/Time:</strong></td><td>${log.created_at}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>User Information</h6>
                <table class="table table-borderless table-sm">
                    <tr><td><strong>Email:</strong></td><td>${log.user_email}</td></tr>
                    <tr><td><strong>IP Address:</strong></td><td><code>${log.ip_address}</code></td></tr>
                    <tr><td><strong>Location:</strong></td><td>${log.location}</td></tr>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h6>Technical Details</h6>
                <p><strong>User Agent:</strong><br><code>${log.user_agent}</code></p>
                <p><strong>Event Details:</strong><br>${log.details}</p>
                <p><strong>Additional Information:</strong><br>${log.additional_info}</p>
            </div>
        </div>
    `;
    
    document.getElementById('logDetailsContent').innerHTML = content;
    const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
    modal.show();
}

function blockIP(ipAddress) {
    currentIPToBlock = ipAddress;
    document.getElementById('ipToBlock').textContent = ipAddress;
    const modal = new bootstrap.Modal(document.getElementById('blockIPModal'));
    modal.show();
}

function confirmBlockIP() {
    const reason = document.getElementById('blockReason').value;
    
    if (!reason.trim()) {
        alert('Please provide a reason for blocking this IP address.');
        return;
    }
    
    // In real implementation, this would make an API call
    fetch('/admin/api/security/block-ip', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            ip_address: currentIPToBlock,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('IP address blocked successfully!');
            bootstrap.Modal.getInstance(document.getElementById('blockIPModal')).hide();
        } else {
            alert('Failed to block IP address: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to block IP address');
    });
}

function escalateIncident(logId) {
    if (confirm('Are you sure you want to escalate this security incident? This will notify the security team.')) {
        // In real implementation, this would make an API call
        fetch('/admin/api/security/escalate-incident', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                log_id: logId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Incident escalated successfully! Security team has been notified.');
            } else {
                alert('Failed to escalate incident: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to escalate incident');
        });
    }
}

function exportLogs() {
    // In real implementation, this would trigger a download
    window.open('/admin/api/security/export-logs', '_blank');
}

function exportSingleLog() {
    // Export the currently viewed log
    alert('Single log export functionality would be implemented here.');
}

function clearLogs() {
    if (confirm('Are you sure you want to clear all security logs? This action cannot be undone.')) {
        fetch('/admin/api/security/clear-logs', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Security logs cleared successfully!');
                location.reload();
            } else {
                alert('Failed to clear logs: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to clear logs');
        });
    }
}
</script>
@endsection
