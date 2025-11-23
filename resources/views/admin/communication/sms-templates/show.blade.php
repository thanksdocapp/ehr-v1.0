@extends('admin.layouts.app')

@section('title', 'View SMS Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('sms-templates.index') }}">SMS Templates</a></li>
    <li class="breadcrumb-item active">{{ $smsTemplate->formatted_name }}</li>
@endsection

@section('content')
<div class="page-title">
    <h1>{{ $smsTemplate->formatted_name }}</h1>
    <p class="page-subtitle">View SMS template details and preview</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">SMS Preview</h3>
                <div>
                    <button class="btn btn-info btn-sm me-2" onclick="previewSmsTemplate()">
                        <i class="fas fa-eye me-2"></i>Preview with Data
                    </button>
                    <a href="{{ contextRoute('sms-templates.edit', $smsTemplate) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-2"></i>Edit Template
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="sms-preview">
                    <div class="sms-header">
                        <div class="sms-meta">
                            <strong>From:</strong> {{ $smsTemplate->sender_id ?: 'HOSPITAL' }}
                        </div>
                        <div class="sms-meta">
                            <strong>Message Length:</strong> {{ strlen($smsTemplate->message) }} characters
                        </div>
                        <div class="sms-meta">
                            <strong>SMS Count:</strong> 
                            @php
                                $length = strlen($smsTemplate->message);
                                $smsCount = $length <= 160 ? 1 : ceil($length / 153);
                            @endphp
                            {{ $smsCount }} SMS
                        </div>
                    </div>
                    <div class="sms-divider"></div>
                    <div class="sms-body">
                        {{ $smsTemplate->message }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Raw Template Message</h3>
            </div>
            <div class="card-body">
                <pre class="template-code"><code>{{ $smsTemplate->message }}</code></pre>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Template Information</h3>
            </div>
            <div class="card-body">
                <div class="info-item">
                    <strong>Name:</strong>
                    <span>{{ $smsTemplate->name }}</span>
                </div>
                <div class="info-item">
                    <strong>Category:</strong>
                    <span class="badge bg-secondary">{{ ucfirst($smsTemplate->category) }}</span>
                </div>
                <div class="info-item">
                    <strong>Status:</strong>
                    <span class="badge {{ $smsTemplate->status_badge_class }}">
                        {{ ucfirst($smsTemplate->status) }}
                    </span>
                </div>
                <div class="info-item">
                    <strong>Sender ID:</strong>
                    <span>{{ $smsTemplate->sender_id ?: 'HOSPITAL' }}</span>
                </div>
                <div class="info-item">
                    <strong>Created:</strong>
                    <span>{{ formatDateTime($smsTemplate->created_at) }}</span>
                </div>
                <div class="info-item">
                    <strong>Last Modified:</strong>
                    <span>{{ formatDateTime($smsTemplate->updated_at) }}</span>
                </div>
                <div class="info-item">
                    <strong>Last Used:</strong>
                    <span>{{ $smsTemplate->last_used_at ? $smsTemplate->last_used_at->diffForHumans() : 'Never' }}</span>
                </div>
                @if($smsTemplate->description)
                <div class="info-item">
                    <strong>Description:</strong>
                    <span>{{ $smsTemplate->description }}</span>
                </div>
                @endif
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Template Actions</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('sms-templates.edit', $smsTemplate) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Template
                    </a>
                    
                    <form action="{{ contextRoute('sms-templates.duplicate', $smsTemplate) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fas fa-copy me-2"></i>Duplicate Template
                        </button>
                    </form>
                    
                    <form action="{{ contextRoute('sms-templates.destroy', $smsTemplate) }}" 
                          method="POST" onsubmit="return confirm('Are you sure you want to delete this template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Variables in Template</h3>
            </div>
            <div class="card-body">
                @php
                    $variables = [];
                    preg_match_all('/\{\{([^}]+)\}\}/', $smsTemplate->message, $matches);
                    $variables = array_unique($matches[1]);
                @endphp
                
                @if(count($variables) > 0)
                    <div class="variable-list">
                        @foreach($variables as $variable)
                            <div class="variable-item">
                                <code>{!!'{{'!!}{{ trim($variable) }}{!!'}}'!!}</code>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No variables found in this template.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ contextRoute('sms-templates.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Templates
    </a>
    
    <div>
        <a href="{{ contextRoute('sms-templates.edit', $smsTemplate) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit Template
        </a>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="smsPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Template Preview with Sample Data</h5>
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
    
    .sms-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .sms-header {
        margin-bottom: 1rem;
    }
    
    .sms-meta {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .sms-divider {
        border-top: 1px solid #dee2e6;
        margin: 1rem 0;
    }
    
    .sms-body {
        background: white;
        padding: 1.5rem;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        font-family: Arial, sans-serif;
        line-height: 1.6;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .template-code {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        font-size: 0.875rem;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .template-code code {
        background: none;
        padding: 0;
        color: #495057;
        font-family: 'Courier New', monospace;
        white-space: pre-wrap;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.75rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #f1f3f4;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .info-item strong {
        min-width: 100px;
        flex-shrink: 0;
    }
    
    .info-item span {
        text-align: right;
        word-break: break-word;
    }
    
    .variable-item {
        margin-bottom: 0.5rem;
        padding: 0.25rem 0.5rem;
        background: #f8f9fa;
        border-radius: 4px;
        border: 1px solid #e9ecef;
    }
    
    .variable-item:last-child {
        margin-bottom: 0;
    }
    
    .variable-item code {
        background: none;
        padding: 0;
        font-size: 0.875rem;
        color: #e83e8c;
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
    
    .btn-secondary, .btn-warning, .btn-danger, .btn-info {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover, .btn-warning:hover, .btn-danger:hover, .btn-info:hover {
        transform: translateY(-1px);
    }
    
    .d-grid .btn {
        padding: 12px 24px;
    }
    
    .sms-preview-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .sms-preview-header {
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

@push('scripts')
<script>
function previewSmsTemplate() {
    const modal = new bootstrap.Modal(document.getElementById('smsPreviewModal'));
    const content = document.getElementById('smsPreviewContent');
    
    // Show loading
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading preview...</p></div>';
    modal.show();
    
    // Make AJAX request to get preview
    fetch(`/admin/sms-templates/{{ $smsTemplate->id }}/preview`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <div class="sms-preview-box">
                                <div class="sms-preview-header">
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
</script>
@endpush
