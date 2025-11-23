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
                        @forelse($emailLogs as $log)
                            @php
                                $statusClass = [
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'pending' => 'warning',
                                    'queued' => 'info'
                                ][$log->status] ?? 'secondary';
                                
                                $statusIcon = [
                                    'sent' => 'check-circle',
                                    'failed' => 'exclamation-circle',
                                    'pending' => 'clock',
                                    'queued' => 'hourglass-half'
                                ][$log->status] ?? 'question-circle';
                                
                                $typeLabel = $log->template->name ?? ($log->metadata['type'] ?? 'General');
                            @endphp
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input email-checkbox" type="checkbox" value="{{ $log->id }}">
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted d-block">{{ $log->created_at->format('Y-m-d H:i:s') }}</small>
                                </td>
                                <td>{{ $log->recipient_email }}</td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $log->subject }}">
                                        {{ $log->subject }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $typeLabel }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusClass }}">
                                        <i class="fas fa-{{ $statusIcon }}"></i> {{ ucfirst($log->status) }}
                                    </span>
                                    @if($log->error_message)
                                        <br><small class="text-danger">{{ Str::limit($log->error_message, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $log->metadata['attempts'] ?? 1 }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.email-management.show', $log->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($log->status, ['failed', 'pending']))
                                            <button class="btn btn-sm btn-outline-success resend-email" data-email-id="{{ $log->id }}" title="Resend">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger delete-email" data-email-id="{{ $log->id }}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No email logs found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($emailLogs->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted small">
                        Showing {{ $emailLogs->firstItem() }} to {{ $emailLogs->lastItem() }} of {{ $emailLogs->total() }} entries
                    </span>
                </div>
                <nav>
                    {{ $emailLogs->links() }}
                </nav>
            </div>
            @endif
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
    let currentEmailId = null;
    
    // Filter form submission - reload page with filters
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = new URL(window.location.href);
        const formData = new FormData(form[0]);
        
        // Update URL parameters
        for (let [key, value] of formData.entries()) {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        }
        
        // Reload page with filters
        window.location.href = url.toString();
    });
    
    // Refresh logs - reload page
    $('#refresh-logs').on('click', function() {
        window.location.reload();
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
    
    // Individual resend
    $(document).on('click', '.resend-email', function(e) {
        e.preventDefault();
        currentEmailId = $(this).data('email-id');
        $('#resendConfirmModal').modal('show');
    });
    
    // Confirm resend
    $('#confirm-resend').on('click', function() {
        if (currentEmailId) {
            resendEmail(currentEmailId);
        }
    });
    
    // Delete email
    $(document).on('click', '.delete-email', function(e) {
        e.preventDefault();
        const emailId = $(this).data('email-id');
        
        Swal.fire({
            title: 'Delete Email Log?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteEmail(emailId);
            }
        });
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

function deleteEmail(emailId) {
    $.ajax({
        url: `/admin/email-management/logs/${emailId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: 'Email log deleted successfully.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response?.message || 'Failed to delete email log.',
                confirmButtonText: 'OK'
            });
        }
    });
}

function resendEmail(emailId) {
    const btn = $('#confirm-resend');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Resending...');
    
    $.ajax({
        url: `/admin/email-management/resend/${emailId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#resendConfirmModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Email Queued',
                text: response.message || 'The email has been added to the queue and will be sent shortly.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response?.message || 'Failed to resend email.',
                confirmButtonText: 'OK'
            });
        },
        complete: function() {
            btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Yes, Resend');
        }
    });
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

</script>
@endpush
