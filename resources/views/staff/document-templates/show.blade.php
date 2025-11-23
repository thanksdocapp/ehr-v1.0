@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', $documentTemplate->name . ' - Document Template')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">{{ ucfirst(auth()->user()->role) }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.document-templates.index') }}">Document Templates</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($documentTemplate->name, 30) }}</li>
@endsection

@push('styles')
<style>
    .template-preview {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 30px;
        min-height: 400px;
        font-family: 'Times New Roman', serif;
        line-height: 1.8;
    }
    
    .preview-header {
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        padding: 15px;
        border-bottom: 2px solid #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt me-2 text-primary"></i>{{ $documentTemplate->name }}
            </h1>
            <p class="text-muted mb-0">
                <span class="badge bg-{{ $documentTemplate->type === 'letter' ? 'primary' : 'info' }}">
                    {{ ucfirst($documentTemplate->type) }}
                </span>
                @if($documentTemplate->is_active)
                    <span class="badge bg-success ms-2">Active</span>
                @else
                    <span class="badge bg-secondary ms-2">Inactive</span>
                @endif
            </p>
        </div>
        <div class="btn-group">
                @can('update', $documentTemplate)
                    <a href="{{ route('staff.document-templates.edit', $documentTemplate) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                @endcan
                <button type="button" class="btn btn-info" id="previewWithPatientBtn">
                    <i class="fas fa-eye me-2"></i>Preview with Sample Patient
                </button>
                <a href="{{ route('staff.document-templates.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Template Details -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Template Details
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Template Name</label>
                            <div class="fw-bold">{{ $documentTemplate->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Slug</label>
                            <div><code>{{ $documentTemplate->slug }}</code></div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Type</label>
                            <div>
                                <span class="badge bg-{{ $documentTemplate->type === 'letter' ? 'primary' : 'info' }}">
                                    {{ ucfirst($documentTemplate->type) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @if($documentTemplate->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Created By</label>
                            <div class="fw-bold">
                                @if($documentTemplate->creator)
                                    {{ $documentTemplate->creator->name }}
                                    <small class="text-muted">({{ ucfirst($documentTemplate->creator->role) }})</small>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Created At</label>
                            <div class="fw-bold">{{ $documentTemplate->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $documentTemplate->created_at->format('h:i A') }}</small>
                        </div>
                    </div>
                    
                    @if($documentTemplate->updated_at != $documentTemplate->created_at)
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Last Updated</label>
                            <div class="fw-bold">{{ $documentTemplate->updated_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $documentTemplate->updated_at->diffForHumans() }}</small>
                        </div>
                        @if($documentTemplate->updater)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Updated By</label>
                            <div class="fw-bold">
                                {{ $documentTemplate->updater->name }}
                                <small class="text-muted">({{ ucfirst($documentTemplate->updater->role) }})</small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Template Preview -->
            <div class="doctor-card mb-4">
                <div class="preview-header">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>Template Preview
                    </h5>
                </div>
                <div class="template-preview" id="templatePreview">
                    @if($documentTemplate->type === 'letter')
                        @php
                            try {
                                $samplePatient = \App\Models\Patient::first();
                                if ($samplePatient) {
                                    $renderer = app(\App\Services\TemplateRenderer::class);
                                    $branding = [];
                                    if (function_exists('getLogo')) {
                                        $branding['clinic_logo'] = getLogo('light');
                                    }
                                    $previewHtml = $renderer->renderLetter(
                                        $documentTemplate,
                                        $samplePatient,
                                        auth()->user(),
                                        [],
                                        $branding
                                    );
                                } else {
                                    $previewHtml = '<p class="text-muted">No sample patient available for preview. Please create a patient first.</p>';
                                }
                            } catch (\Exception $e) {
                                $previewHtml = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Preview error: ' . $e->getMessage() . '</div>';
                            }
                        @endphp
                        {!! $previewHtml ?? '<p class="text-muted">Preview unavailable</p>' !!}
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Form Template:</strong> This is a form template. Preview will show the form structure when creating a document.
                        </div>
                        @if($documentTemplate->schema)
                            <h6 class="mb-3">Form Structure:</h6>
                            @foreach($documentTemplate->schema as $section)
                                <div class="mb-4">
                                    <h6>{{ $section['title'] ?? 'Section' }}</h6>
                                    @if(!empty($section['description']))
                                        <p class="text-muted">{{ $section['description'] }}</p>
                                    @endif
                                    <ul>
                                        @foreach($section['fields'] ?? [] as $field)
                                            <li>
                                                <strong>{{ $field['label'] ?? $field['name'] ?? 'Field' }}</strong>
                                                ({{ $field['type'] }})
                                                @if($field['required'] ?? false)
                                                    <span class="badge bg-danger">Required</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            @if($documentTemplate->patientDocuments && $documentTemplate->patientDocuments->count() > 0)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Usage Statistics
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="h4 text-primary mb-1">{{ $documentTemplate->patientDocuments->count() }}</div>
                            <div class="text-muted">Total Documents</div>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 text-success mb-1">{{ $documentTemplate->patientDocuments->where('status', 'final')->count() }}</div>
                            <div class="text-muted">Finalised</div>
                        </div>
                        <div class="col-md-4">
                            <div class="h4 text-warning mb-1">{{ $documentTemplate->patientDocuments->where('status', 'draft')->count() }}</div>
                            <div class="text-muted">Drafts</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        @can('update', $documentTemplate)
                            <a href="{{ route('staff.document-templates.edit', $documentTemplate) }}" class="btn btn-warning w-100">
                                <i class="fas fa-edit me-2"></i>Edit Template
                            </a>
                        @endcan
                        <button type="button" class="btn btn-info w-100" id="previewBtn">
                            <i class="fas fa-eye me-2"></i>Preview Template
                        </button>
                        <button type="button" class="btn btn-primary w-100" id="previewWithSampleBtn">
                            <i class="fas fa-user me-2"></i>Preview with Sample Patient
                        </button>
                        @can('deactivate', $documentTemplate)
                        @if($documentTemplate->is_active)
                            <form action="{{ route('staff.document-templates.deactivate', $documentTemplate) }}" 
                                  method="POST" 
                                  class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to deactivate this template?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-toggle-on me-2"></i>Deactivate
                                </button>
                            </form>
                        @endif
                        @endcan
                        <a href="{{ route('staff.document-templates.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-list me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Template Information -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Template Info</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Render Mode</label>
                        <div>
                            <span class="badge bg-secondary">{{ ucfirst($documentTemplate->render_mode ?? 'builder') }}</span>
                        </div>
                    </div>
                    @if($documentTemplate->builder_config && is_array($documentTemplate->builder_config))
                    <div class="mb-3">
                        <label class="form-label text-muted">Blocks</label>
                        <div class="fw-bold">{{ count($documentTemplate->builder_config) }}</div>
                        <small class="text-muted">blocks in template</small>
                    </div>
                    @endif
                    @if($documentTemplate->patientDocuments)
                    <div class="mb-0">
                        <label class="form-label text-muted">In Use</label>
                        <div class="fw-bold text-primary">{{ $documentTemplate->patientDocuments->count() }}</div>
                        <small class="text-muted">documents created</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Template Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalPreviewContent" class="template-preview">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Preview with sample patient
    $('#previewWithPatientBtn, #previewWithSampleBtn').on('click', function() {
        const modal = $('#previewModal');
        const previewContent = $('#modalPreviewContent');
        
        modal.modal('show');
        previewContent.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Generating preview with sample patient data...</p>
            </div>
        `);
        
        $.ajax({
            url: '{{ route("staff.document-templates.show", $documentTemplate) }}',
            method: 'GET',
            data: {
                preview: true
            },
            success: function(response) {
                if (response.success) {
                    previewContent.html(`
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Preview using sample patient: <strong>${response.patient.name}</strong> (${response.patient.id})
                        </div>
                        ${response.html}
                    `);
                } else {
                    previewContent.html(`
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${response.error || 'Preview unavailable'}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || 'Failed to load preview';
                previewContent.html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${error}
                    </div>
                `);
            }
        });
    });
    
    // Simple preview (current view)
    $('#previewBtn').on('click', function() {
        $('html, body').animate({
            scrollTop: $('#templatePreview').offset().top - 100
        }, 500);
    });
});
</script>
@endpush

