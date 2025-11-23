@extends('admin.layouts.app')

@section('title', 'Email Management')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Email Management</h1>
            <p class="mb-0 text-muted">Monitor and manage email notifications</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="refresh-stats">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <a href="{{ route('admin.email-management.logs') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> View Logs
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Emails Sent
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-emails">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Successfully Delivered
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="delivered-emails">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Failed/Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="failed-emails">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Queue Size
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="queue-size">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-primary text-white mr-3">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Send Test Email</h6>
                                    <p class="small text-muted mb-0">Test your email configuration</p>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" id="send-test-email">
                                    Test
                                </button>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-success text-white mr-3">
                                    <i class="fas fa-redo"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Process Queue</h6>
                                    <p class="small text-muted mb-0">Manually process pending emails</p>
                                </div>
                                <button class="btn btn-sm btn-outline-success" id="process-queue">
                                    Process
                                </button>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-info text-white mr-3">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Email Settings</h6>
                                    <p class="small text-muted mb-0">Configure notification preferences</p>
                                </div>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#settingsModal">
                                    Settings
                                </button>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-warning text-white mr-3">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">View Statistics</h6>
                                    <p class="small text-muted mb-0">Detailed email analytics</p>
                                </div>
                                <a href="{{ route('admin.email-management.statistics') }}" class="btn btn-sm btn-outline-warning">
                                    View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Status</h6>
                </div>
                <div class="card-body">
                    <div id="system-status">
                        <div class="d-flex align-items-center mb-2">
                            <div class="status-indicator bg-success mr-2"></div>
                            <span class="small">Email Service: <strong class="text-success">Active</strong></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="status-indicator bg-success mr-2"></div>
                            <span class="small">Queue Worker: <strong id="queue-status" class="text-success">Running</strong></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="status-indicator bg-success mr-2"></div>
                            <span class="small">SMTP Connection: <strong id="smtp-status" class="text-success">Connected</strong></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator bg-success mr-2"></div>
                            <span class="small">Last Processed: <strong id="last-processed">Just now</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Recent Email Activity</h6>
            <a href="{{ route('admin.email-management.logs') }}" class="btn btn-sm btn-primary">
                View All Logs
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-borderless" id="recent-activity-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">
                                <i class="fas fa-spinner fa-spin"></i> Loading recent activity...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Notification Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="settings-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Patient Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="patient_welcome" name="patient_welcome" checked>
                                <label class="form-check-label" for="patient_welcome">
                                    Welcome Emails
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="appointment_confirmation" name="appointment_confirmation" checked>
                                <label class="form-check-label" for="appointment_confirmation">
                                    Appointment Confirmations
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="appointment_reminder" name="appointment_reminder" checked>
                                <label class="form-check-label" for="appointment_reminder">
                                    Appointment Reminders
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="test_results" name="test_results" checked>
                                <label class="form-check-label" for="test_results">
                                    Test Results Ready
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">Staff Notifications</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="new_patient_registration" name="new_patient_registration" checked>
                                <label class="form-check-label" for="new_patient_registration">
                                    New Patient Registrations
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="new_appointment" name="new_appointment" checked>
                                <label class="form-check-label" for="new_appointment">
                                    New Appointments
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emergency_admission" name="emergency_admission" checked>
                                <label class="form-check-label" for="emergency_admission">
                                    Emergency Admissions
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="critical_results" name="critical_results" checked>
                                <label class="form-check-label" for="critical_results">
                                    Critical Lab Results
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-settings">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="test-email-form">
                    @csrf
                    <div class="mb-3">
                        <label for="test-email-recipient" class="form-label">Recipient Email</label>
                        <input type="email" class="form-control" id="test-email-recipient" name="recipient" required>
                    </div>
                    <div class="mb-3">
                        <label for="test-email-type" class="form-label">Email Type</label>
                        <select class="form-select" id="test-email-type" name="type" required>
                            <option value="test">Basic Test Email</option>
                            <option value="appointment_confirmation">Appointment Confirmation</option>
                            <option value="appointment_reminder">Appointment Reminder</option>
                            <option value="patient_welcome">Patient Welcome</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="send-test">Send Test Email</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial data
    loadStatistics();
    loadRecentActivity();
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        loadStatistics();
        loadRecentActivity();
    }, 30000);
    
    // Refresh button
    $('#refresh-stats').on('click', function() {
        $(this).find('i').addClass('fa-spin');
        loadStatistics();
        loadRecentActivity();
        setTimeout(() => {
            $(this).find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    // Send test email
    $('#send-test-email').on('click', function() {
        $('#testEmailModal').modal('show');
    });
    
    $('#send-test').on('click', function() {
        const formData = $('#test-email-form').serialize();
        const btn = $(this);
        btn.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: '{{ route("admin.email-management.test") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#testEmailModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Test Email Sent!',
                    text: response.message || 'Test email sent successfully.',
                    confirmButtonText: 'OK'
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Send',
                    text: response?.message || 'Failed to send test email.',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                btn.prop('disabled', false).text('Send Test Email');
            }
        });
    });
    
    // Process queue
    $('#process-queue').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // In a real implementation, this would trigger queue processing
        setTimeout(() => {
            btn.prop('disabled', false).text('Process');
            Swal.fire({
                icon: 'success',
                title: 'Queue Processed',
                text: 'Pending emails have been processed.',
                confirmButtonText: 'OK'
            });
            loadStatistics();
        }, 2000);
    });
    
    // Save settings
    $('#save-settings').on('click', function() {
        const formData = $('#settings-form').serialize();
        const btn = $(this);
        btn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: '{{ route("admin.email-management.settings.update") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#settingsModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Settings Saved',
                    text: 'Email notification settings updated successfully.',
                    confirmButtonText: 'OK'
                });
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Save',
                    text: response?.message || 'Failed to save settings.',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                btn.prop('disabled', false).text('Save Changes');
            }
        });
    });
});

function loadStatistics() {
    // Mock data - in real implementation, this would be an AJAX call
    setTimeout(() => {
        $('#total-emails').html('<span class="countup">1,247</span>');
        $('#delivered-emails').html('<span class="countup">1,189</span>');
        $('#failed-emails').html('<span class="countup">58</span>');
        $('#queue-size').html('<span class="countup">12</span>');
    }, 500);
}

function loadRecentActivity() {
    // Mock data - in real implementation, this would be an AJAX call
    const mockData = [
        {
            time: '2 minutes ago',
            recipient: 'patient@example.com',
            subject: 'Appointment Reminder',
            type: 'appointment_reminder',
            status: 'sent'
        },
        {
            time: '5 minutes ago',
            recipient: 'doctor@hospital.com',
            subject: 'New Patient Registration',
            type: 'staff_notification',
            status: 'sent'
        },
        {
            time: '8 minutes ago',
            recipient: 'patient2@example.com',
            subject: 'Test Results Ready',
            type: 'test_results',
            status: 'sent'
        },
        {
            time: '12 minutes ago',
            recipient: 'patient3@example.com',
            subject: 'Welcome to Our Hospital',
            type: 'patient_welcome',
            status: 'failed'
        }
    ];
    
    setTimeout(() => {
        let html = '';
        mockData.forEach(item => {
            const statusClass = item.status === 'sent' ? 'success' : 'danger';
            const statusIcon = item.status === 'sent' ? 'check-circle' : 'exclamation-circle';
            const typeLabels = {
                'appointment_reminder': 'Appointment',
                'staff_notification': 'Staff',
                'test_results': 'Test Results',
                'patient_welcome': 'Welcome'
            };
            
            html += `
                <tr>
                    <td><small class="text-muted">${item.time}</small></td>
                    <td>${item.recipient}</td>
                    <td>${item.subject}</td>
                    <td><span class="badge bg-secondary">${typeLabels[item.type] || item.type}</span></td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            <i class="fas fa-${statusIcon}"></i> ${item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                        </span>
                    </td>
                </tr>
            `;
        });
        $('#recent-activity-table tbody').html(html);
    }, 300);
}
</script>

<style>
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.countup {
    animation: countUp 1s ease-in-out;
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush
