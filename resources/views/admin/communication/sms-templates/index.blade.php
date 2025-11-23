@extends('admin.layouts.app')

@section('title', 'SMS Templates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">SMS Templates</li>
@endsection

@section('content')
<div class="page-title">
    <h1>SMS Templates</h1>
    <p class="page-subtitle">Manage your SMS templates for automated text communications</p>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">SMS Templates</h3>
                <a href="{{ contextRoute('sms-templates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Template
                </a>
            </div>
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Message Preview</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>SMS Count</th>
                                    <th>Last Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <strong>{{ $template->formatted_name }}</strong>
                                        @if($template->description)
                                            <br><small class="text-muted">{{ $template->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="sms-preview">
                                            {{ $template->message_preview }}
                                        </div>
                                        <small class="text-muted">
                                            {{ strlen($template->message) }} chars
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($template->category) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $template->status_badge_class }}">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $template->sms_count ?? 1 }} SMS
                                        </span>
                                    </td>
                                    <td>
                                        @if($template->last_used_at)
                                            {{ $template->last_used_at->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ contextRoute('sms-templates.show', $template) }}" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ contextRoute('sms-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ contextRoute('sms-templates.duplicate', $template) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                                        title="Duplicate">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteSmsTemplate({{ $template->id }}); return false;" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $templates->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-sms fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No SMS templates found</h5>
                        <p class="text-muted">Create your first SMS template to get started</p>
                        <a href="{{ contextRoute('sms-templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Template
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-primary">
                <i class="fas fa-sms"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->total() }}</h3>
                <p>Total Templates</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->where('status', 'active')->count() }}</h3>
                <p>Active Templates</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-warning">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->where('status', 'draft')->count() }}</h3>
                <p>Draft Templates</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-info">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->whereNotNull('last_used_at')->count() }}</h3>
                <p>Used Templates</p>
            </div>
        </div>
    </div>
</div>

<!-- SMS Preview Modal -->
<div class="modal fade" id="smsPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="smsPreviewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    .table th {
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .sms-preview {
        max-width: 200px;
        font-size: 0.9rem;
        line-height: 1.4;
        color: #374151;
        background: #f8f9fa;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1.5rem;
    }
    
    .stats-content h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        color: #374151;
    }
    
    .stats-content p {
        margin: 0;
        color: #6b7280;
        font-weight: 500;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-outline-info, .btn-outline-warning, .btn-outline-secondary, .btn-outline-danger, .btn-outline-primary {
        border-radius: 6px;
        border-width: 1px;
        transition: all 0.3s ease;
    }
    
    .btn-outline-info:hover, .btn-outline-warning:hover, 
    .btn-outline-secondary:hover, .btn-outline-danger:hover, .btn-outline-primary:hover {
        transform: translateY(-1px);
    }
</style>
@endpush

@push('scripts')
<script>
function previewSmsTemplate(templateId) {
    const modal = new bootstrap.Modal(document.getElementById('smsPreviewModal'));
    const content = document.getElementById('smsPreviewContent');
    
    // Show loading
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading preview...</p></div>';
    modal.show();
    
    // Make AJAX request to get preview
    fetch(`/admin/sms-templates/${templateId}/preview`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <div class="sms-preview-box">
                                <div class="sms-header">
                                    <strong>From:</strong> ${data.preview.sender_id || 'HOSPITAL'}
                                </div>
                                <div class="sms-content">
                                    ${data.preview.message}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="sms-stats">
                                <div class="stat-item">
                                    <strong>Character Count:</strong> ${data.preview.character_count}
                                </div>
                                <div class="stat-item">
                                    <strong>SMS Count:</strong> ${data.preview.sms_count}
                                </div>
                                <div class="stat-item">
                                    <strong>Sender ID:</strong> ${data.preview.sender_id || 'HOSPITAL'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                content.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
            }
        })
        .catch(error => {
            content.innerHTML = `<div class="alert alert-danger">Error loading preview: ${error.message}</div>`;
        });
}

function deleteSmsTemplate(templateId) {
    ModalSystem.confirm({
        title: 'Delete SMS Template',
        message: 'Are you sure you want to delete this SMS template?',
        confirmText: 'Delete',
        confirmClass: 'btn-danger',
        icon: 'fas fa-exclamation-triangle',
        onConfirm: function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/sms-templates/${templateId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            form.appendChild(csrfToken);
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<style>
.sms-preview-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.sms-header {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.sms-content {
    font-size: 1rem;
    line-height: 1.5;
    color: #212529;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.sms-stats {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
}

.stat-item {
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.stat-item:last-child {
    margin-bottom: 0;
}
</style>
@endpush
