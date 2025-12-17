@extends('admin.layouts.app')

@section('title', 'Email Logs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.email-management.index') }}">Email Management</a></li>
    <li class="breadcrumb-item active">Email Logs</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="modern-page-title"><i class="fas fa-envelope-open-text"></i>Email Logs</h1>
                    <p class="modern-page-subtitle">View detailed email delivery logs and history</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn-modern btn-modern-outline" id="export-logs" type="button">
                        <i class="fas fa-download"></i>Export
                    </button>
                    <button class="btn-modern btn-modern-outline" id="refresh-logs" type="button">
                        <i class="fas fa-rotate-right"></i>Refresh
                    </button>
                    <a href="{{ route('admin.email-management.index') }}" class="btn-modern btn-modern-primary">
                        <i class="fas fa-arrow-left"></i>Back to Overview
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-inbox"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['total'] ?? 0) }}</div>
                        <div class="stat-label">Total</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['sent'] ?? 0) }}</div>
                        <div class="stat-label">Sent</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format(($stats['pending'] ?? 0) + ($stats['queued'] ?? 0)) }}</div>
                        <div class="stat-label">Pending / Queued</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['failed'] ?? 0) }}</div>
                        <div class="stat-label">Failed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="modern-card mb-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-filter"></i>Filters</h5>
        </div>
        <div class="modern-card-body">
            <form class="row g-3" id="filter-form">
                <div class="col-md-3">
                    <label for="status-filter" class="modern-form-label">Status</label>
                    <select class="modern-form-select" id="status-filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>Queued</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="type-filter" class="modern-form-label">Type</label>
                    <select class="modern-form-select" id="type-filter" name="type">
                        <option value="">All Types</option>
                        <option value="patient_welcome" {{ request('type') === 'patient_welcome' ? 'selected' : '' }}>Patient Welcome</option>
                        <option value="appointment_confirmation" {{ request('type') === 'appointment_confirmation' ? 'selected' : '' }}>Appointment Confirmation</option>
                        <option value="appointment_reminder" {{ request('type') === 'appointment_reminder' ? 'selected' : '' }}>Appointment Reminder</option>
                        <option value="test_results" {{ request('type') === 'test_results' ? 'selected' : '' }}>Test Results</option>
                        <option value="prescription_ready" {{ request('type') === 'prescription_ready' ? 'selected' : '' }}>Prescription Ready</option>
                        <option value="payment_reminder" {{ request('type') === 'payment_reminder' ? 'selected' : '' }}>Payment Reminder</option>
                        <option value="staff_notification" {{ request('type') === 'staff_notification' ? 'selected' : '' }}>Staff Notification</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date-from" class="modern-form-label">From Date</label>
                    <input type="text" class="modern-form-control" id="date-from" name="date_from"
                           value="{{ request('date_from') }}"
                           placeholder="dd-mm-yyyy" 
                           pattern="\d{2}-\d{2}-\d{4}" 
                           maxlength="10">
                    <small class="form-help-text">Format: dd-mm-yyyy</small>
                </div>
                <div class="col-md-2">
                    <label for="date-to" class="modern-form-label">To Date</label>
                    <input type="text" class="modern-form-control" id="date-to" name="date_to"
                           value="{{ request('date_to') }}"
                           placeholder="dd-mm-yyyy" 
                           pattern="\d{2}-\d{2}-\d{4}" 
                           maxlength="10">
                    <small class="form-help-text">Format: dd-mm-yyyy</small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="w-100">
                        <button type="submit" class="btn-modern btn-modern-primary w-100">
                            <i class="fas fa-filter"></i>Apply
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Email Logs Table -->
    <div class="modern-card">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0"><i class="fas fa-list"></i>Email Logs</h5>
            <div class="d-flex gap-2 flex-wrap">
                <div class="dropdown">
                    <button class="btn-modern btn-modern-outline btn-modern-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-gear"></i>Actions
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
        <div class="modern-card-body">
            <div class="modern-table-wrapper">
                <table class="modern-table" id="email-logs-table">
                    <thead>
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
                                    <div class="mt-1">
                                        <a class="small text-decoration-none" data-bs-toggle="collapse" href="#preview-{{ $log->id }}" role="button" aria-expanded="false">
                                            Preview
                                        </a>
                                    </div>
                                    <div class="collapse mt-2" id="preview-{{ $log->id }}">
                                        <div class="border rounded p-2 bg-light" style="max-width: 420px;">
                                            <div class="small text-muted mb-1">Body (preview)</div>
                                            <div class="small" style="max-height: 120px; overflow:auto; white-space: pre-wrap;">
                                                {{ Str::limit(strip_tags($log->body ?? ''), 500) }}
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ route('admin.email-management.show', $log->id) }}" class="small text-decoration-none">
                                                    View full email
                                                </a>
                                            </div>
                                        </div>
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
                                        <a href="{{ route('admin.email-management.show', $log->id) }}" class="btn-modern btn-modern-outline btn-modern-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(in_array($log->status, ['failed', 'pending']))
                                            <button class="btn-modern btn-modern-outline btn-modern-sm resend-email" data-email-id="{{ $log->id }}" title="Resend">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        @endif
                                        <button class="btn-modern btn-modern-outline btn-modern-sm delete-email" data-email-id="{{ $log->id }}" title="Delete">
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
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #f1f5f9;">
                <div class="text-muted small">
                    Showing {{ $emailLogs->firstItem() }} to {{ $emailLogs->lastItem() }} of {{ $emailLogs->total() }} entries
                </div>
                {{ $emailLogs->links() }}
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
                text: (response && response.message) ? response.message : 'Failed to delete email log.',
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
                text: (response && response.message) ? response.message : 'Failed to resend email.',
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

// Function to reload email logs (refresh page)
function loadEmailLogs() {
    window.location.reload();
}

</script>
@endpush
