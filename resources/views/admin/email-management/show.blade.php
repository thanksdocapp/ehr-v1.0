@extends('admin.layouts.app')

@section('title', 'Email Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.email-management.index') }}">Email Management</a></li>
    <li class="breadcrumb-item active">Email Details</li>
@endsection

@push('styles')
<style>
.detail-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.detail-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px 15px 0 0;
    position: relative;
}

.detail-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    border-radius: 15px 15px 0 0;
}

.detail-body {
    padding: 2.5rem;
}

.info-section {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.info-section h5 {
    color: #5a5c69;
    margin-bottom: 1.5rem;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.info-section h5 i {
    margin-right: 0.75rem;
    width: 24px;
    text-align: center;
}

.info-table {
    margin: 0;
}

.info-table td {
    border: none;
    padding: 0.75rem 0;
    vertical-align: top;
}

.info-table td:first-child {
    font-weight: 600;
    color: #5a5c69;
    width: 150px;
}

.info-table td:last-child {
    color: #858796;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-flex;
    align-items: center;
}

.status-badge i {
    margin-right: 0.5rem;
}

.status-sent {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
}

.status-failed {
    background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
    color: white;
}

.status-pending {
    background: linear-gradient(135deg, #f6c23e 0%, #f39c12 100%);
    color: white;
}

.status-queued {
    background: linear-gradient(135deg, #36b9cc 0%, #3498db 100%);
    color: white;
}

.email-content {
    background: white;
    border: 2px solid #e3e6f0;
    border-radius: 12px;
    padding: 2rem;
    margin-top: 1.5rem;
    max-height: 400px;
    overflow-y: auto;
}

.email-headers {
    background: #2d3748;
    color: #cbd5e0;
    border-radius: 8px;
    padding: 1.5rem;
    font-family: 'Monaco', 'Consolas', monospace;
    font-size: 0.875rem;
    line-height: 1.6;
    max-height: 300px;
    overflow-y: auto;
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e3e6f0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -2.25rem;
    top: 1.5rem;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #667eea;
    border: 3px solid white;
    box-shadow: 0 0 0 2px #667eea;
}

.timeline-time {
    color: #858796;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.timeline-title {
    color: #5a5c69;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.timeline-description {
    color: #858796;
    font-size: 0.875rem;
}

.action-buttons {
    background: #f8f9fc;
    border-radius: 12px;
    padding: 2rem;
    border: 1px solid #e3e6f0;
}

.btn-custom {
    padding: 0.75rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-resend {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-resend:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
    color: white;
}

.btn-delete {
    background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(231, 74, 59, 0.3);
}

.btn-delete:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(231, 74, 59, 0.4);
    color: white;
}

.btn-back {
    background: linear-gradient(135deg, #858796 0%, #60616f 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(133, 135, 150, 0.3);
}

.btn-back:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(133, 135, 150, 0.4);
    color: white;
}

.error-details {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    border: 1px solid #feb2b2;
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1rem;
}

.error-details h6 {
    color: #c53030;
    margin-bottom: 1rem;
    font-weight: 600;
}

.error-details pre {
    background: #fff;
    border: 1px solid #feb2b2;
    border-radius: 8px;
    padding: 1rem;
    font-size: 0.875rem;
    color: #742a2a;
    margin: 0;
    white-space: pre-wrap;
    word-break: break-word;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Details</h1>
        <p class="page-subtitle text-muted">Detailed information about this email</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="detail-card">
                <div class="detail-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0"><i class="fas fa-info-circle me-2"></i>Email Information</h3>
                            <p class="mb-0 opacity-75">Email ID: #{{ $emailLog->id }}</p>
                        </div>
                        <div class="col-auto">
                            <span class="status-badge status-{{ $emailLog->status }}">
                                <i class="fas fa-{{ $emailLog->status == 'sent' ? 'check-circle' : ($emailLog->status == 'failed' ? 'exclamation-circle' : ($emailLog->status == 'pending' ? 'clock' : 'hourglass-half')) }}"></i>
                                {{ ucfirst($emailLog->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="detail-body">
                    <div class="info-section">
                        <h5><i class="fas fa-user text-primary"></i>Recipient Information</h5>
                        <table class="table info-table">
                            <tr>
                                <td>Recipient Name:</td>
                                <td>{{ $emailLog->recipient_name ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <td>Email Address:</td>
                                <td>
                                    <a href="mailto:{{ $emailLog->recipient_email }}" class="text-decoration-none">
                                        {{ $emailLog->recipient_email }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>Subject:</td>
                                <td><strong>{{ $emailLog->subject }}</strong></td>
                            </tr>
                            <tr>
                                <td>Template:</td>
                                <td>
                                    @if($emailLog->emailTemplate)
                                        <span class="badge bg-secondary">{{ $emailLog->emailTemplate->name }}</span>
                                    @else
                                        <span class="text-muted">No template</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="info-section">
                        <h5><i class="fas fa-clock text-success"></i>Delivery Information</h5>
                        <table class="table info-table">
                            <tr>
                                <td>Created:</td>
                                <td>{{ formatDateTime($emailLog->created_at) }}</td>
                            </tr>
                            <tr>
                                <td>Sent:</td>
                                <td>
                                    @if($emailLog->sent_at)
                                        {{ formatDateTime($emailLog->sent_at) }}
                                    @else
                                        <span class="text-muted">Not sent yet</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Processing Time:</td>
                                <td>
                                    @if($emailLog->sent_at && $emailLog->created_at)
                                        {{ $emailLog->created_at->diffInSeconds($emailLog->sent_at) }} seconds
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Attempts:</td>
                                <td>
                                    <span class="badge bg-info">{{ $emailLog->attempts ?? 1 }}</span>
                                    @if(($emailLog->attempts ?? 1) > 1)
                                        <small class="text-muted ms-1">(Multiple attempts)</small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    @if($emailLog->status === 'failed' && $emailLog->error_message)
                    <div class="error-details">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Error Details</h6>
                        <pre>{{ $emailLog->error_message }}</pre>
                    </div>
                    @endif

                    @if($emailLog->content)
                    <div class="info-section">
                        <h5><i class="fas fa-file-alt text-info"></i>Email Content</h5>
                        <div class="email-content">
                            {!! $emailLog->content !!}
                        </div>
                    </div>
                    @endif

                    @if($emailLog->headers)
                    <div class="info-section">
                        <h5><i class="fas fa-code text-warning"></i>Email Headers</h5>
                        <div class="email-headers">
                            @php
                                $headers = is_array($emailLog->headers) ? $emailLog->headers : json_decode($emailLog->headers, true);
                            @endphp
                            @if($headers)
                                @foreach($headers as $header => $value)
{{ $header }}: {{ $value }}
                                @endforeach
                            @else
No headers available
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="detail-card mb-4">
                <div class="detail-header">
                    <h4 class="mb-0"><i class="fas fa-history me-2"></i>Email Timeline</h4>
                </div>
                <div class="detail-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-time">{{ formatDateTime($emailLog->created_at) }}</div>
                            <div class="timeline-title">Email Created</div>
                            <div class="timeline-description">Email was queued for sending</div>
                        </div>

                        @if($emailLog->status === 'sent' && $emailLog->sent_at)
                        <div class="timeline-item">
                            <div class="timeline-time">{{ formatDateTime($emailLog->sent_at) }}</div>
                            <div class="timeline-title">Email Sent</div>
                            <div class="timeline-description">Successfully delivered to {{ $emailLog->recipient_email }}</div>
                        </div>
                        @elseif($emailLog->status === 'failed')
                        <div class="timeline-item">
                            <div class="timeline-time">{{ formatDateTime($emailLog->updated_at) }}</div>
                            <div class="timeline-title">Delivery Failed</div>
                            <div class="timeline-description">Email delivery failed after {{ $emailLog->attempts ?? 1 }} attempt(s)</div>
                        </div>
                        @elseif($emailLog->status === 'pending')
                        <div class="timeline-item">
                            <div class="timeline-time">{{ formatDateTime(now()) }}</div>
                            <div class="timeline-title">Pending</div>
                            <div class="timeline-description">Email is waiting in queue for processing</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="action-buttons">
                <h5 class="mb-3"><i class="fas fa-tools me-2"></i>Actions</h5>
                
                @if(in_array($emailLog->status, ['failed', 'pending']))
                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-custom btn-resend" onclick="resendEmail({{ $emailLog->id }})">
                        <i class="fas fa-paper-plane me-2"></i>Resend Email
                    </button>
                </div>
                @endif

                <div class="d-grid gap-2 mb-3">
                    <button class="btn btn-custom btn-delete" onclick="deleteEmail({{ $emailLog->id }})">
                        <i class="fas fa-trash me-2"></i>Delete Log
                    </button>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('admin.email-management.index') }}" class="btn btn-custom btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Back to Logs
                    </a>
                </div>

                <hr class="my-4">

                <h6 class="text-muted mb-3">Quick Links</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.email-config') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-cog me-1"></i>Email Settings
                    </a>
                    <a href="{{ route('admin.email-management.statistics') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-chart-bar me-1"></i>View Statistics
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resendEmail(emailId) {
    Swal.fire({
        title: 'Resend Email?',
        text: 'This will add the email back to the queue for sending.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Resend',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#1cc88a'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Resending Email...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admin/email-management/${emailId}/resend`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Queued!',
                            text: response.message || 'The email has been added to the queue and will be sent shortly.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Resend',
                            text: response.message || 'Failed to resend email. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response?.message || 'Failed to resend email. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

function deleteEmail(emailId) {
    Swal.fire({
        title: 'Delete Email Log?',
        text: 'This will permanently delete this email log. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#e74a3b'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting Email Log...',
                text: 'Please wait while we process your request.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admin/email-management/${emailId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Log Deleted!',
                            text: response.message || 'The email log has been deleted successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route("admin.email-management.index") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Delete',
                            text: response.message || 'Failed to delete email log. Please try again.',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response?.message || 'Failed to delete email log. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}
</script>
@endpush
