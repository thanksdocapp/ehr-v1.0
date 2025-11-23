@extends('admin.layouts.app')

@section('title', 'View Email Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('email-templates.index') }}">Email Templates</a></li>
    <li class="breadcrumb-item active">{{ $emailTemplate->formatted_name }}</li>
@endsection

@section('content')
<div class="page-title">
    <h1>{{ $emailTemplate->formatted_name }}</h1>
    <p class="page-subtitle">View email template details and preview</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Template Preview</h3>
                <div>
                    <button type="button" class="btn btn-info btn-sm me-2" id="togglePreviewBtn">
                        <i class="fas fa-eye me-1"></i>Show with Sample Data
                    </button>
                    <a href="{{ contextRoute('email-templates.edit', $emailTemplate) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-2"></i>Edit Template
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Raw Template View -->
                <div id="rawPreview" class="email-preview">
                    <div class="email-header">
                        <div class="email-meta">
                            <strong>From:</strong> {{ $emailTemplate->sender_name ?: 'System Default' }} 
                            &lt;{{ $emailTemplate->sender_email ?: 'system@hospital.com' }}&gt;
                        </div>
                        <div class="email-meta">
                            <strong>Subject:</strong> {{ $emailTemplate->subject }}
                        </div>
                    </div>
                    <div class="email-divider"></div>
                    <div class="email-body">
                        {!! nl2br(e($emailTemplate->body)) !!}
                    </div>
                </div>
                
                <!-- Sample Data Preview -->
                <div id="samplePreview" class="email-preview" style="display: none;">
                    <div class="email-header">
                        <div class="email-meta">
                            <strong>From:</strong> <span id="previewSenderName">{{ $emailTemplate->sender_name ?: 'ThanksDoc EHR' }}</span> 
                            &lt;<span id="previewSenderEmail">{{ $emailTemplate->sender_email ?: 'noreply@hospital.com' }}</span>&gt;
                        </div>
                        <div class="email-meta">
                            <strong>Subject:</strong> <span id="previewSubject">{{ $emailTemplate->subject }}</span>
                        </div>
                    </div>
                    <div class="email-divider"></div>
                    <div class="email-body" id="previewBody">
                        <!-- Sample content will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">Raw Template Code</h3>
            </div>
            <div class="card-body">
                <pre class="template-code"><code>{{ $emailTemplate->body }}</code></pre>
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
                    <span>{{ $emailTemplate->name }}</span>
                </div>
                <div class="info-item">
                    <strong>Category:</strong>
                    <span class="badge bg-secondary">{{ ucfirst($emailTemplate->category) }}</span>
                </div>
                <div class="info-item">
                    <strong>Status:</strong>
                    <span class="badge {{ $emailTemplate->status_badge_class }}">
                        {{ ucfirst($emailTemplate->status) }}
                    </span>
                </div>
                <div class="info-item">
                    <strong>Created:</strong>
                    <span>{{ formatDateTime($emailTemplate->created_at) }}</span>
                </div>
                <div class="info-item">
                    <strong>Last Modified:</strong>
                    <span>{{ formatDateTime($emailTemplate->updated_at) }}</span>
                </div>
                <div class="info-item">
                    <strong>Last Used:</strong>
                    <span>{{ $emailTemplate->last_used_at ? $emailTemplate->last_used_at->diffForHumans() : 'Never' }}</span>
                </div>
                @if($emailTemplate->description)
                <div class="info-item">
                    <strong>Description:</strong>
                    <span>{{ $emailTemplate->description }}</span>
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
                    <a href="{{ contextRoute('email-templates.edit', $emailTemplate) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit Template
                    </a>
                    
                    <form action="{{ contextRoute('email-templates.duplicate', $emailTemplate) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fas fa-copy me-2"></i>Duplicate Template
                        </button>
                    </form>
                    
                    <form action="{{ contextRoute('email-templates.destroy', $emailTemplate) }}" 
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
                    $body = $emailTemplate->body;
                    if (preg_match_all('/\{\{([^}]+)\}\}/', $body, $matches)) {
                        $variables = array_unique($matches[1]);
                    }
                @endphp
                
                @if(count($variables) > 0)
                    <div class="variable-list">
                        @foreach($variables as $variable)
                            <div class="variable-item">
                                <code>@{{ trim($variable) }}</code>
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
    <a href="{{ contextRoute('email-templates.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Templates
    </a>
    
    <div>
        <a href="{{ contextRoute('email-templates.edit', $emailTemplate) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit Template
        </a>
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
    
    .email-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .email-header {
        margin-bottom: 1rem;
    }
    
    .email-meta {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
    
    .email-divider {
        border-top: 1px solid #dee2e6;
        margin: 1rem 0;
    }
    
    .email-body {
        background: white;
        padding: 1.5rem;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        font-family: Arial, sans-serif;
        line-height: 1.6;
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
    
    .btn-secondary, .btn-warning, .btn-danger {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-secondary:hover, .btn-warning:hover, .btn-danger:hover {
        transform: translateY(-1px);
    }
    
    .d-grid .btn {
        padding: 12px 24px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePreviewBtn');
    const rawPreview = document.getElementById('rawPreview');
    const samplePreview = document.getElementById('samplePreview');
    const previewSubject = document.getElementById('previewSubject');
    const previewBody = document.getElementById('previewBody');
    
    let showingSampleData = false;
    
    toggleBtn.addEventListener('click', function() {
        if (showingSampleData) {
            // Switch to raw view
            rawPreview.style.display = 'block';
            samplePreview.style.display = 'none';
            toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Show with Sample Data';
            showingSampleData = false;
        } else {
            // Switch to sample data view
            const originalSubject = @json($emailTemplate->subject);
            const originalBody = @json($emailTemplate->body);
            
            // Sample data for preview
            const sampleData = {
                'patient_name': 'John Doe',
                'patient_id': 'P001',
                'doctor_name': 'Dr. Sarah Johnson',
                'doctor_phone': '+1 (555) 123-4567',
                'appointment_date': 'February 15, 2025',
                'appointment_time': '10:30 AM',
                'department': 'Cardiology',
                'hospital_name': 'ThanksDoc EHR',
                'hospital_phone': '+1 (555) 987-6543',
                'hospital_address': '123 Healthcare Street, City, State 12345',
                'site_url': window.location.origin,
                'date': new Date().toLocaleDateString(),
                'time': new Date().toLocaleTimeString()
            };
            
            let previewSubjectText = originalSubject;
            let previewBodyText = originalBody;
            
            // Replace variables with sample data
            Object.keys(sampleData).forEach(key => {
                const regex = new RegExp('{{' + key + '}}', 'g');
                previewSubjectText = previewSubjectText.replace(regex, sampleData[key]);
                previewBodyText = previewBodyText.replace(regex, sampleData[key]);
            });
            
            // Update preview content
            previewSubject.textContent = previewSubjectText;
            previewBody.innerHTML = previewBodyText.replace(/\n/g, '<br>');
            
            // Show sample preview
            rawPreview.style.display = 'none';
            samplePreview.style.display = 'block';
            toggleBtn.innerHTML = '<i class="fas fa-code me-1"></i>Show Raw Template';
            showingSampleData = true;
        }
    });
});
</script>
@endpush
