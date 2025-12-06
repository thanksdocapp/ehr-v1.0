@extends('admin.layouts.app')

@section('title', 'Edit Document - ' . $document->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->full_name }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.documents.index', $patient) }}">Letters & Forms</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header with Status-based Color -->
    @php
        $headerColors = [
            'draft' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
            'final' => 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
            'void' => 'linear-gradient(135deg, #636363 0%, #a2a2a2 100%)'
        ];
        $headerColor = $headerColors[$document->status] ?? $headerColors['draft'];
    @endphp
    <div class="card border-0 shadow-sm mb-4" style="background: {{ $headerColor }};">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-white">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-white bg-opacity-25 text-white me-2">
                            {{ ucfirst($document->status) }}
                        </span>
                        <span class="badge bg-white bg-opacity-25 text-white">
                            {{ ucfirst($document->type) }}
                        </span>
                    </div>
                    <h1 class="h3 mb-1 fw-bold">
                        <i class="fas fa-edit me-2"></i>{{ Str::limit($document->title, 40) }}
                    </h1>
                    <p class="mb-0 opacity-75">
                        Patient: <strong>{{ $patient->full_name }}</strong>
                    </p>
                </div>
                <a href="{{ route('admin.patients.documents.show', [$patient, $document]) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Document
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$document->isDraft())
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="fas fa-lock fa-2x me-3"></i>
            <div>
                <h5 class="alert-heading mb-1">Document Locked</h5>
                <p class="mb-0">This document is <strong>{{ $document->status }}</strong> and cannot be edited. Only draft documents can be modified.</p>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.patients.documents.update', [$patient, $document]) }}" method="POST" id="documentForm">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Document Information Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Document Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="title" class="form-label fw-semibold">Document Title</label>
                                <input type="text"
                                       class="form-control form-control-lg @error('title') is-invalid @enderror"
                                       id="title"
                                       name="title"
                                       value="{{ old('title', $document->title) }}"
                                       {{ !$document->isDraft() ? 'readonly' : '' }}>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Document Type</label>
                                <div class="fw-bold">
                                    <span class="badge bg-{{ $document->type === 'letter' ? 'primary' : 'success' }} fs-6">
                                        <i class="fas fa-{{ $document->type === 'letter' ? 'envelope' : 'clipboard-list' }} me-1"></i>
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Template</label>
                                <div class="fw-bold">{{ $document->template ? $document->template->name : 'No template' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Fields Section (for Forms) -->
                @if($document->type === 'form')
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <h5 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-clipboard-list me-2"></i>Form Fields
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(!$document->isDraft())
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Form fields are read-only because this document is {{ $document->status }}.
                                </div>
                            @endif

                            <div id="formFieldsLoading" class="text-center py-5" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-muted">Loading form fields...</p>
                            </div>
                            <div id="formFieldsContainer">
                                @php
                                    $formData = $document->form_data ?? [];
                                    $schema = [];
                                    if ($document->template) {
                                        $schema = app(\App\Services\TemplateRenderer::class)->buildFormSchema($document->template);
                                    }
                                @endphp

                                @if(count($schema) > 0)
                                    @foreach($schema as $sectionIndex => $section)
                                        <div class="form-section">
                                            <h6 class="form-section-title">
                                                <i class="fas fa-layer-group me-2"></i>{{ $section['title'] ?? 'Section ' . ($sectionIndex + 1) }}
                                            </h6>
                                            @if(!empty($section['description']))
                                                <p class="text-muted small mb-3">{{ $section['description'] }}</p>
                                            @endif
                                            <div class="row">
                                                @foreach($section['fields'] ?? [] as $field)
                                                    @include('admin.patients.documents.partials.form-field', [
                                                        'field' => $field,
                                                        'formData' => $formData,
                                                        'readonly' => !$document->isDraft()
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @elseif(is_array($formData) && count($formData) > 0)
                                    {{-- Fallback for documents without schema but with form data --}}
                                    <div class="form-section">
                                        <h6 class="form-section-title">
                                            <i class="fas fa-layer-group me-2"></i>Form Data
                                        </h6>
                                        <div class="row">
                                            @foreach($formData as $fieldName => $value)
                                                <div class="col-md-6 form-field-group">
                                                    <label class="form-label">{{ ucwords(str_replace('_', ' ', $fieldName)) }}</label>
                                                    @if(is_bool($value) || $value === '0' || $value === '1')
                                                        <div class="form-control-plaintext">
                                                            <span class="badge bg-{{ $value ? 'success' : 'secondary' }}">
                                                                {{ $value ? 'Yes' : 'No' }}
                                                            </span>
                                                        </div>
                                                    @elseif(is_array($value))
                                                        <div class="form-control-plaintext">
                                                            @foreach($value as $v)
                                                                <span class="badge bg-secondary me-1">{{ $v }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <input type="text"
                                                               class="form-control"
                                                               name="form_data[{{ $fieldName }}]"
                                                               value="{{ $value }}"
                                                               {{ !$document->isDraft() ? 'readonly' : '' }}>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No form data available. The template may not have form fields defined.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Letter Content Section -->
                @if($document->type === 'letter')
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-envelope me-2"></i>Letter Content
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($document->isDraft())
                                <div id="letterContentEditor" style="min-height: 400px; background: #fff; border: 1px solid #dee2e6; border-radius: 8px;">
                                    {!! old('content', $document->content) !!}
                                </div>
                                <textarea class="form-control @error('content') is-invalid @enderror"
                                          id="content"
                                          name="content"
                                          style="display: none;">{{ old('content', $document->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Use the rich text editor above to format your letter content.</small>

                                <hr class="my-4">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <i class="fas fa-plus-circle me-1 text-primary"></i>
                                        Re-render with Extra Placeholders
                                    </label>
                                    <p class="text-muted small mb-3">Add custom values to fill placeholders, then save to re-render the letter.</p>
                                    <div id="extraPlaceholdersContainer">
                                        @if(old('extra_placeholders'))
                                            @foreach(old('extra_placeholders') as $index => $placeholder)
                                                <div class="placeholder-row d-flex gap-2 align-items-center">
                                                    <div class="flex-grow-1">
                                                        <input type="text"
                                                               class="form-control form-control-sm"
                                                               name="extra_placeholders[{{ $index }}][name]"
                                                               value="{{ $placeholder['name'] ?? '' }}"
                                                               placeholder="Placeholder name">
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <input type="text"
                                                               class="form-control form-control-sm"
                                                               name="extra_placeholders[{{ $index }}][value]"
                                                               value="{{ $placeholder['value'] ?? '' }}"
                                                               placeholder="Value">
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-placeholder">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addPlaceholderBtn">
                                        <i class="fas fa-plus me-1"></i>Add Placeholder
                                    </button>
                                </div>
                            @else
                                <div class="letter-content-preview p-4" style="background: #fff; border: 1px solid #dee2e6; border-radius: 8px;">
                                    {!! $document->content !!}
                                </div>
                                <p class="text-muted mt-2 small">
                                    <i class="fas fa-lock me-1"></i>Content is read-only for {{ $document->status }} documents.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-bolt me-2 text-warning"></i>Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($document->isDraft())
                                <button type="submit" class="btn btn-lg text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            @endif
                            <a href="{{ route('admin.patients.documents.show', [$patient, $document]) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Document
                            </a>
                            @can('finalise', $document)
                            @if($document->isDraft())
                                <hr class="my-2">
                                <form action="{{ route('admin.patients.documents.finalise', [$patient, $document]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to finalise this document? This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check-circle me-2"></i>Finalise Document
                                    </button>
                                </form>
                            @endif
                            @endcan
                        </div>
                    </div>

                    <!-- Document Status -->
                    <div class="card-footer bg-light border-0">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-info-circle me-1 text-info"></i>Document Details
                        </h6>
                        <div class="mb-2">
                            <small class="text-muted">Status</small>
                            <div>
                                @php
                                    $statusColors = [
                                        'draft' => 'warning',
                                        'final' => 'success',
                                        'void' => 'secondary'
                                    ];
                                    $statusIcons = [
                                        'draft' => 'edit',
                                        'final' => 'check-circle',
                                        'void' => 'ban'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                                    <i class="fas fa-{{ $statusIcons[$document->status] ?? 'file' }} me-1"></i>
                                    {{ ucfirst($document->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Created</small>
                            <div class="fw-bold small">{{ $document->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        @if($document->updated_at != $document->created_at)
                        <div class="mb-0">
                            <small class="text-muted">Last Updated</small>
                            <div class="small">{{ $document->updated_at->diffForHumans() }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .form-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .form-section-title {
        font-weight: 600;
        color: #495057;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    .form-field-group {
        margin-bottom: 1rem;
    }
    .form-field-group label {
        font-weight: 500;
        color: #495057;
    }
    .form-field-group .required-indicator {
        color: #dc3545;
        margin-left: 2px;
    }
    .checkbox-group-container,
    .radio-group-container {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
    }
    .checkbox-group-container .form-check,
    .radio-group-container .form-check {
        padding: 8px 0 8px 2rem;
        border-bottom: 1px solid #f1f1f1;
    }
    .checkbox-group-container .form-check:last-child,
    .radio-group-container .form-check:last-child {
        border-bottom: none;
    }
    .info-text-field {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px;
        border-radius: 0 8px 8px 0;
        font-style: italic;
        color: #666;
    }
    .placeholder-row {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .letter-content-preview {
        max-height: 500px;
        overflow-y: auto;
    }
    #letterContentEditor .ql-editor {
        min-height: 350px;
    }
</style>
@endpush

@push('scripts')
<!-- Quill Rich Text Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
let quillEditor = null;

$(document).ready(function() {
    // Initialize Quill editor for letter content
    @if($document->type === 'letter' && $document->isDraft())
    const contentEditor = document.getElementById('letterContentEditor');
    if (contentEditor) {
        quillEditor = new Quill('#letterContentEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Set initial content
        const initialContent = $('#content').val();
        if (initialContent) {
            quillEditor.root.innerHTML = initialContent;
        }

        // Update hidden textarea on content change
        quillEditor.on('text-change', function() {
            $('#content').val(quillEditor.root.innerHTML);
        });
    }
    @endif

    // Add placeholder field
    let placeholderIndex = {{ old('extra_placeholders') ? count(old('extra_placeholders')) : 0 }};
    $('#addPlaceholderBtn').on('click', function() {
        const placeholderHtml = `
            <div class="placeholder-row d-flex gap-2 align-items-center">
                <div class="flex-grow-1">
                    <input type="text"
                           class="form-control form-control-sm"
                           name="extra_placeholders[${placeholderIndex}][name]"
                           placeholder="Placeholder name">
                </div>
                <div class="flex-grow-1">
                    <input type="text"
                           class="form-control form-control-sm"
                           name="extra_placeholders[${placeholderIndex}][value]"
                           placeholder="Value">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-placeholder">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        $('#extraPlaceholdersContainer').append(placeholderHtml);
        placeholderIndex++;
    });

    // Remove placeholder
    $(document).on('click', '.remove-placeholder', function() {
        $(this).closest('.placeholder-row').remove();
    });

    // Form submission - ensure Quill content is saved
    $('#documentForm').on('submit', function(e) {
        if (quillEditor) {
            $('#content').val(quillEditor.root.innerHTML);
        }
    });
});
</script>
@endpush
