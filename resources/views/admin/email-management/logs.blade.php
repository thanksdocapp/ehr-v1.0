@extends('admin.layouts.app')

@section('title', 'Email Logs')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Email Logs</h1>
            <p class="mb-0 text-muted">View detailed email delivery logs and history</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary" id="export-logs">
                <i class="fas fa-download"></i> Export
            </button>
            <a href="{{ route('admin.email-management.index') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Overview
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" id="filter-form">
                <div class="col-md-3">
                    <label for="status-filter" class="form-label">Status</label>
                    <select class="form-select" id="status-filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="sent">Sent</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending</option>
                        <option value="queued">Queued</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type-filter" class="form-label">Type</label>
                    <select class="form-select" id="type-filter" name="type">
                        <option value="">All Types</option>
                        <option value="patient_welcome">Patient Welcome</option>
                        <option value="appointment_confirmation">Appointment Confirmation</option>
                        <option value="appointment_reminder">Appointment Reminder</option>
                        <option value="test_results">Test Results</option>
                        <option value="prescription_ready">Prescription Ready</option>
                        <option value="payment_reminder">Payment Reminder</option>
                        <option value="staff_notification">Staff Notification</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date-from" class="form-label">From Date</label>
                    <input type="text" class="form-control" id="date-from" name="date_from"
                           placeholder="dd-mm-yyyy" 
                           pattern="\d{2}-\d{2}-\d{4}" 
                           maxlength="10">
                    <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                </div>
                <div class="col-md-2">
                    <label for="date-to" class="form-label">To Date</label>
                    <input type="text" class="form-control" id="date-to" name="date_to"
                           placeholder="dd-mm-yyyy" 
                           pattern="\d{2}-\d{2}-\d{4}" 
                           maxlength="10">
                    <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="w-100">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Logs Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Email Logs</h6>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary" id="refresh-logs">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="bulk-resend"><i class="fas fa-redo text-info"></i> Resend Failed</a></li>
                        <li><a class="dropdown-item" href="#" id="bulk-delete"><i class="fas fa-trash text-danger"></i> Delete Selected</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="clear-old-logs"><i class="fas fa-broom text-warning"></i> Clear Old Logs</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="email-logs-table">
                    <thead class="table-light">
                        <tr>
                            <th width="30">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th>Date & Time</th>
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted small" id="pagination-info">
                        Showing 1-25 of 247 entries
                    </span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="pagination-controls">
                        <!-- Pagination will be generated dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Email Detail Modal -->
<div class="modal fade" id="emailDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="email-detail-content">
                <!-- Email details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="resend-email">
                    <i class="fas fa-paper-plane"></i> Resend Email
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Resend Confirmation Modal -->
<div class="modal fade" id="resendConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Resend</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to resend this email?</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    The email will be added to the queue and sent using the current email template.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-resend">
                    <i class="fas fa-paper-plane"></i> Yes, Resend
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentPage = 1;
    let currentEmailId = null;
    
    // Load initial logs
    loadEmailLogs();
    
    // Filter form submission
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        loadEmailLogs();
    });
    
    // Refresh logs
    $('#refresh-logs').on('click', function() {
        const btn = $(this);
        btn.find('i').addClass('fa-spin');
        loadEmailLogs();
        setTimeout(() => {
            btn.find('i').removeClass('fa-spin');
        }, 1000);
    });
    
    // Select all checkbox
    $('#select-all').on('change', function() {
        $('.email-checkbox').prop('checked', this.checked);
    });
    
    // Individual checkbox change
    $(document).on('change', '.email-checkbox', function() {
        const totalCheckboxes = $('.email-checkbox').length;
        const checkedCheckboxes = $('.email-checkbox:checked').length;
        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // View email details
    $(document).on('click', '.view-email', function() {
        const emailId = $(this).data('email-id');
        loadEmailDetails(emailId);
    });
    
    // Individual resend
    $(document).on('click', '.resend-email', function() {
        currentEmailId = $(this).data('email-id');
        $('#resendConfirmModal').modal('show');
    });
    
    // Confirm resend
    $('#confirm-resend').on('click', function() {
        if (currentEmailId) {
            resendEmail(currentEmailId);
        }
    });
    
    // Bulk resend failed emails
    $('#bulk-resend').on('click', function() {
        const selectedEmails = getSelectedEmails();
        if (selectedEmails.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select emails to resend.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        Swal.fire({
            title: 'Resend Selected Emails?',
            text: `This will resend ${selectedEmails.length} selected email(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Resend',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkResendEmails(selectedEmails);
            }
        });
    });
    
    // Bulk delete
    $('#bulk-delete').on('click', function() {
        const selectedEmails = getSelectedEmails();
        if (selectedEmails.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Selection',
                text: 'Please select emails to delete.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        Swal.fire({
            title: 'Delete Selected Emails?',
            text: `This will permanently delete ${selectedEmails.length} email log(s). This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkDeleteEmails(selectedEmails);
            }
        });
    });
    
    // Export logs
    $('#export-logs').on('click', function() {
        const filters = $('#filter-form').serialize();
        window.open(`/admin/email-management/logs/export?${filters}`, '_blank');
    });
    
    // Clear old logs
    $('#clear-old-logs').on('click', function() {
        Swal.fire({
            title: 'Clear Old Logs?',
            text: 'This will delete email logs older than 30 days. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Clear',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                clearOldLogs();
            }
        });
    });
});

function loadEmailLogs() {
    const filters = $('#filter-form').serialize();
    const tbody = $('#email-logs-table tbody');
    
    // Show loading
    tbody.html(`
        <tr>
            <td colspan="8" class="text-center py-4">
                <i class="fas fa-spinner fa-spin"></i> Loading email logs...
            </td>
        </tr>
    `);
    
    // Mock data - in real implementation, this would be an AJAX call
    setTimeout(() => {
        const mockLogs = [
            {
                id: 1,
                datetime: '2024-01-15 14:30:25',
                recipient: 'john.doe@email.com',
                subject: 'Appointment Confirmation - January 16, 2024',
                type: 'appointment_confirmation',
                status: 'sent',
                attempts: 1,
                error: null
            },
            {
                id: 2,
                datetime: '2024-01-15 14:25:12',
                recipient: 'jane.smith@email.com',
                subject: 'Welcome to ThanksDoc EHR',
                type: 'patient_welcome',
                status: 'sent',
                attempts: 1,
                error: null
            },
            {
                id: 3,
                datetime: '2024-01-15 14:20:45',
                recipient: 'bob.wilson@email.com',
                subject: 'Appointment Reminder - Tomorrow at 2:00 PM',
                type: 'appointment_reminder',
                status: 'failed',
                attempts: 3,
                error: 'SMTP Error: Connection timeout'
            },
            {
                id: 4,
                datetime: '2024-01-15 14:15:33',
                recipient: 'admin@hospital.com',
                subject: 'New Patient Registration Alert',
                type: 'staff_notification',
                status: 'sent',
                attempts: 1,
                error: null
            },
            {
                id: 5,
                datetime: '2024-01-15 14:10:18',
                recipient: 'patient@example.com',
                subject: 'Your Test Results Are Ready',
                type: 'test_results',
                status: 'pending',
                attempts: 0,
                error: null
            }
        ];
        
        let html = '';
        mockLogs.forEach(log => {
            const statusClass = {
                'sent': 'success',
                'failed': 'danger',
                'pending': 'warning',
                'queued': 'info'
            }[log.status] || 'secondary';
            
            const statusIcon = {
                'sent': 'check-circle',
                'failed': 'exclamation-circle',
                'pending': 'clock',
                'queued': 'hourglass-half'
            }[log.status] || 'question-circle';
            
            const typeLabels = {
                'patient_welcome': 'Welcome',
                'appointment_confirmation': 'Appointment',
                'appointment_reminder': 'Reminder',
                'test_results': 'Test Results',
                'prescription_ready': 'Prescription',
                'payment_reminder': 'Payment',
                'staff_notification': 'Staff Alert'
            };
            
            html += `
                <tr>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input email-checkbox" type="checkbox" value="${log.id}">
                        </div>
                    </td>
                    <td>
                        <small class="text-muted d-block">${log.datetime}</small>
                    </td>
                    <td>${log.recipient}</td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="${log.subject}">
                            ${log.subject}
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary">${typeLabels[log.type] || log.type}</span>
                    </td>
                    <td>
                        <span class="badge bg-${statusClass}">
                            <i class="fas fa-${statusIcon}"></i> ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                        </span>
                        ${log.error ? `<br><small class="text-danger">${log.error}</small>` : ''}
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">${log.attempts}</span>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-primary view-email" data-email-id="${log.id}" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${log.status === 'failed' || log.status === 'pending' ? `
                                <button class="btn btn-sm btn-outline-success resend-email" data-email-id="${log.id}" title="Resend">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            ` : ''}
                            <button class="btn btn-sm btn-outline-danger delete-email" data-email-id="${log.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.html(html);
        updatePaginationInfo();
    }, 500);
}

function loadEmailDetails(emailId) {
    $('#email-detail-content').html(`
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin"></i> Loading email details...
        </div>
    `);
    
    $('#emailDetailModal').modal('show');
    
    // Mock data - in real implementation, this would be an AJAX call
    setTimeout(() => {
        const mockDetail = {
            id: emailId,
            datetime: '2024-01-15 14:30:25',
            recipient: 'john.doe@email.com',
            subject: 'Appointment Confirmation - January 16, 2024',
            type: 'appointment_confirmation',
            status: 'sent',
            attempts: 1,
            error: null,
            content: `
                <h4>Appointment Confirmation</h4>
                <p>Dear John Doe,</p>
                <p>This is to confirm your appointment scheduled for:</p>
                <ul>
                    <li><strong>Date:</strong> January 16, 2024</li>
                    <li><strong>Time:</strong> 2:00 PM</li>
                    <li><strong>Doctor:</strong> Dr. Sarah Johnson</li>
                    <li><strong>Department:</strong> Cardiology</li>
                </ul>
                <p>Please arrive 15 minutes before your appointment time.</p>
                <p>Best regards,<br>ThanksDoc EHR</p>
            `,
            headers: {
                'From': 'noreply@hospital.com',
                'To': 'john.doe@email.com',
                'Subject': 'Appointment Confirmation - January 16, 2024',
                'Date': 'Mon, 15 Jan 2024 14:30:25 +0000',
                'Message-ID': '<1642261825.12345@hospital.com>'
            }
        };
        
        const statusClass = {
            'sent': 'success',
            'failed': 'danger',
            'pending': 'warning',
            'queued': 'info'
        }[mockDetail.status] || 'secondary';
        
        $('#email-detail-content').html(`
            <div class="row">
                <div class="col-md-6">
                    <h6>Email Information</h6>
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>${mockDetail.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Date & Time:</strong></td>
                            <td>${mockDetail.datetime}</td>
                        </tr>
                        <tr>
                            <td><strong>Recipient:</strong></td>
                            <td>${mockDetail.recipient}</td>
                        </tr>
                        <tr>
                            <td><strong>Subject:</strong></td>
                            <td>${mockDetail.subject}</td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td><span class="badge bg-secondary">${mockDetail.type}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="badge bg-${statusClass}">${mockDetail.status}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Attempts:</strong></td>
                            <td>${mockDetail.attempts}</td>
                        </tr>
                        ${mockDetail.error ? `
                        <tr>
                            <td><strong>Error:</strong></td>
                            <td><span class="text-danger">${mockDetail.error}</span></td>
                        </tr>
                        ` : ''}
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Email Headers</h6>
                    <pre class="bg-light p-2 rounded" style="font-size: 12px; max-height: 200px; overflow-y: auto;">
${Object.entries(mockDetail.headers).map(([key, value]) => `${key}: ${value}`).join('\n')}
                    </pre>
                </div>
            </div>
            <div class="mt-3">
                <h6>Email Content</h6>
                <div class="border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                    ${mockDetail.content}
                </div>
            </div>
        `);
        
        currentEmailId = emailId;
    }, 300);
}

function resendEmail(emailId) {
    const btn = $('#confirm-resend');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Resending...');
    
    // Mock AJAX call
    setTimeout(() => {
        $('#resendConfirmModal').modal('hide');
        Swal.fire({
            icon: 'success',
            title: 'Email Queued',
            text: 'The email has been added to the queue and will be sent shortly.',
            confirmButtonText: 'OK'
        }).then(() => {
            loadEmailLogs(); // Refresh the logs
        });
        
        btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Yes, Resend');
    }, 1500);
}

function getSelectedEmails() {
    const selected = [];
    $('.email-checkbox:checked').each(function() {
        selected.push($(this).val());
    });
    return selected;
}

function bulkResendEmails(emailIds) {
    Swal.fire({
        title: 'Resending Emails...',
        text: 'Please wait while we process your request.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Emails Queued',
            text: `${emailIds.length} email(s) have been added to the queue and will be sent shortly.`,
            confirmButtonText: 'OK'
        }).then(() => {
            loadEmailLogs(); // Refresh the logs
        });
    }, 2000);
}

function bulkDeleteEmails(emailIds) {
    Swal.fire({
        title: 'Deleting Emails...',
        text: 'Please wait while we process your request.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Emails Deleted',
            text: `${emailIds.length} email log(s) have been deleted successfully.`,
            confirmButtonText: 'OK'
        }).then(() => {
            loadEmailLogs(); // Refresh the logs
        });
    }, 1500);
}

function clearOldLogs() {
    Swal.fire({
        title: 'Clearing Old Logs...',
        text: 'Please wait while we clean up old email logs.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'success',
            title: 'Old Logs Cleared',
            text: 'Email logs older than 30 days have been deleted successfully.',
            confirmButtonText: 'OK'
        }).then(() => {
            loadEmailLogs(); // Refresh the logs
        });
    }, 2000);
}

function updatePaginationInfo() {
    $('#pagination-info').text('Showing 1-5 of 247 entries');
    
    $('#pagination-controls').html(`
        <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1">Previous</a>
        </li>
        <li class="page-item active">
            <a class="page-link" href="#">1</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="#">2</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="#">3</a>
        </li>
        <li class="page-item">
            <a class="page-link" href="#">Next</a>
        </li>
    `);
}
</script>
@endpush
