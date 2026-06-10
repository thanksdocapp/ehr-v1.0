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
    padding: 1.5rem 1.75rem;
    border-radius: 15px 15px 0 0;
    position: relative;
    overflow: hidden;
}

.detail-header .detail-header-inner {
    position: relative;
    z-index: 1;
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
    pointer-events: none;
    z-index: 0;
}

.detail-body {
    padding: 1.5rem;
}

@media (min-width: 992px) {
    .detail-body {
        padding: 2rem;
    }
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
    background: #fff;
    border: 2px solid #e3e6f0;
    border-radius: 12px;
    margin-top: 1rem;
    overflow: hidden;
    max-width: 100%;
}

.email-preview-frame {
    display: block;
    width: 100%;
    min-height: 280px;
    max-height: 560px;
    border: 0;
    background: #fff;
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

    <div class="row g-4">
        <div class="col-lg-8 order-lg-1">
            <div class="detail-card">
                <div class="detail-header">
                    <div class="detail-header-inner row align-items-center g-2">
                        <div class="col">
                            <h3 class="mb-1 h4"><i class="fas fa-info-circle me-2"></i>Email Information</h3>
                            <p class="mb-0 opacity-75 small">Email ID: #{{ $emailLog->id }}</p>
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
                                    @if($emailLog->template)
                                        <span class="badge bg-secondary">{{ $emailLog->template->name }}</span>
                                    @elseif($emailLog->email_template_id)
                                        <span class="text-muted">Template ID: {{ $emailLog->email_template_id }} (not found)</span>
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
                                    @php $attempts = (int) data_get($emailLog->metadata, 'attempts', 1); @endphp
                                    <span class="badge bg-info">{{ $attempts }}</span>
                                    @if($attempts > 1)
                                        <small class="text-muted ms-1">(Multiple attempts)</small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    @php
                        $meta = is_array($emailLog->metadata) ? $emailLog->metadata : [];
                        $relatedInvoiceId = $emailLog->invoice_id ?? ($meta['invoice_id'] ?? null);
                        $relatedPaymentId = $emailLog->payment_id ?? ($meta['payment_id'] ?? null);
                    @endphp
                    @if($relatedInvoiceId || $relatedPaymentId)
                    <div class="info-section">
                        <h5><i class="fas fa-link text-warning"></i>Related records</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @if($relatedPaymentId)
                                <a href="{{ route('admin.booking-payments.index', ['from' => '', 'to' => '']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-credit-card me-1"></i>Booking payments
                                </a>
                            @endif
                            @if($relatedInvoiceId)
                                <span class="badge bg-light text-dark border align-self-center">Invoice #{{ $relatedInvoiceId }}</span>
                            @endif
                            @if($relatedPaymentId)
                                <span class="badge bg-light text-dark border align-self-center">Payment #{{ $relatedPaymentId }}</span>
                            @endif
                        </div>
                        <p class="small text-muted mb-0 mt-2">Open <strong>Booking payments</strong> and clear filters to find this checkout (search by patient email or amount).</p>
                    </div>
                    @endif

                    @if($emailLog->status === 'failed' && $emailLog->error_message)
                    <div class="error-details">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Error Details</h6>
                        <pre>{{ $emailLog->error_message }}</pre>
                    </div>
                    @endif

                    <div class="info-section">
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <h5 class="mb-0"><i class="fas fa-file-alt text-info"></i>Email Content</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="copyEmailHtmlBtn">
                                    <i class="fas fa-copy me-1"></i>Copy HTML
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="copyEmailTextBtn">
                                    <i class="fas fa-copy me-1"></i>Copy Text
                                </button>
                            </div>
                        </div>

                        @php
                            $emailHtml = (string) ($emailLog->body ?? '');
                            // Basic safety for admin log preview: strip script tags so we don't execute arbitrary JS
                            $emailHtmlSafe = preg_replace('/<script\\b[^>]*>(.*?)<\\/script>/is', '', $emailHtml) ?? $emailHtml;
                            $emailText = trim(preg_replace('/\\s+/', ' ', strip_tags($emailHtml)));
                        @endphp

                        <ul class="nav nav-tabs mt-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabRendered" type="button" role="tab">
                                    Rendered
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHtml" type="button" role="tab">
                                    HTML Source
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMeta" type="button" role="tab">
                                    Variables / Metadata
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="tabRendered" role="tabpanel">
                                <div class="email-content mt-3">
                                    @if(trim($emailHtmlSafe) === '')
                                        <div class="text-muted p-4">No email body stored for this log.</div>
                                    @else
                                        <iframe
                                            id="emailPreviewFrame"
                                            class="email-preview-frame"
                                            title="Rendered email preview"
                                            sandbox=""
                                        ></iframe>
                                    @endif
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabHtml" role="tabpanel">
                                <div class="email-headers mt-3" style="background:#111827;color:#e5e7eb;">
{{ $emailHtml }}
                                </div>
                            </div>

                            <div class="tab-pane fade" id="tabMeta" role="tabpanel">
                                <div class="email-headers mt-3">
@php
    $vars = $emailLog->variables ?? null;
    $meta = $emailLog->metadata ?? null;
    $varsJson = is_string($vars) ? $vars : json_encode($vars, JSON_PRETTY_PRINT);
    $metaJson = is_string($meta) ? $meta : json_encode($meta, JSON_PRETTY_PRINT);
@endphp
Variables:
{{ $varsJson ?: 'null' }}

Metadata:
{{ $metaJson ?: 'null' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 order-lg-2">
            <!-- Timeline -->
            <div class="detail-card mb-4">
                <div class="detail-header">
                    <div class="detail-header-inner">
                        <h4 class="mb-0 h5"><i class="fas fa-history me-2"></i>Email Timeline</h4>
                    </div>
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
                    <a href="{{ route('admin.email-management.logs') }}" class="btn btn-custom btn-back">
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
document.addEventListener('DOMContentLoaded', function () {
    const html = @json((string)($emailLog->body ?? ''));
    const text = @json(trim(preg_replace('/\\s+/', ' ', strip_tags((string)($emailLog->body ?? '')))));

    const previewFrame = document.getElementById('emailPreviewFrame');
    if (previewFrame && html) {
        previewFrame.srcdoc = html;
        previewFrame.addEventListener('load', function () {
            try {
                const doc = previewFrame.contentDocument;
                if (!doc) {
                    return;
                }
                const height = Math.max(
                    doc.documentElement?.scrollHeight || 0,
                    doc.body?.scrollHeight || 0,
                    280
                );
                previewFrame.style.height = Math.min(height + 24, 560) + 'px';
            } catch (e) {
                previewFrame.style.height = '400px';
            }
        });
    }

    function copyToClipboard(value) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(value);
        }
        const ta = document.createElement('textarea');
        ta.value = value;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        ta.remove();
        return Promise.resolve();
    }

    const copyHtmlBtn = document.getElementById('copyEmailHtmlBtn');
    if (copyHtmlBtn) {
        copyHtmlBtn.addEventListener('click', function () {
            copyToClipboard(html).then(() => {
                Swal.fire({ icon: 'success', title: 'Copied', text: 'Email HTML copied to clipboard.', timer: 1500, showConfirmButton: false });
            });
        });
    }

    const copyTextBtn = document.getElementById('copyEmailTextBtn');
    if (copyTextBtn) {
        copyTextBtn.addEventListener('click', function () {
            copyToClipboard(text).then(() => {
                Swal.fire({ icon: 'success', title: 'Copied', text: 'Email text copied to clipboard.', timer: 1500, showConfirmButton: false });
            });
        });
    }
});

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
