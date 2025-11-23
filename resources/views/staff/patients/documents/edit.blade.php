@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Document - ' . $document->title)

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('staff.patients.index') }}">Patients</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('staff.patients.show', $patient) }}">{{ $patient->full_name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('staff.patients.documents.index', $patient) }}">Letters & Forms</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('staff.patients.documents.show', [$patient, $document]) }}">{{ Str::limit($document->title, 20) }}</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-900">
                        <i class="fas fa-edit me-2 text-primary"></i>Edit Document
                    </h1>
                    <p class="text-muted mb-0">{{ $document->title }} - Patient: <strong>{{ $patient->full_name }}</strong></p>
                </div>
                <a href="{{ route('staff.patients.documents.show', [$patient, $document]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Cancel
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
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> This document is {{ $document->status }} and cannot be edited. Only draft documents can be modified.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('staff.patients.documents.update', [$patient, $document]) }}" method="POST" id="documentForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Document Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-file-alt me-2"></i>Document Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $document->title) }}"
                                   {{ !$document->isDraft() ? 'readonly' : '' }}>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Document Type:</strong> {{ ucfirst($document->type) }}
                            <br><strong>Template:</strong> {{ $document->template ? $document->template->name : 'No template' }}
                        </div>

                        <!-- For Forms: Form Data Input -->
                        @if($document->type === 'form')
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Form Document:</strong> Edit the form data below.
                            </div>
                            <div id="formFieldsContainer">
                                @if($document->form_data && is_array($document->form_data))
                                    @foreach($document->form_data as $field => $value)
                                    <div class="mb-3">
                                        <label class="form-label">{{ ucwords(str_replace('_', ' ', $field)) }}</label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="form_data[{{ $field }}]" 
                                               value="{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}"
                                               {{ !$document->isDraft() ? 'readonly' : '' }}>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Form data is not available. The form schema should be loaded from the template.
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- For Letters: Content Editor -->
                        @if($document->type === 'letter')
                            <div class="mb-3">
                                <label for="content" class="form-label">Letter Content</label>
                                <div id="letterContentEditor" style="min-height: 400px;">
                                    {!! old('content', $document->content) !!}
                                </div>
                                <textarea class="form-control @error('content') is-invalid @enderror" 
                                          id="content" 
                                          name="content" 
                                          style="display: none;"
                                          {{ !$document->isDraft() ? 'readonly' : '' }}>{{ old('content', $document->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Use the rich text editor above to format your letter content.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Extra Placeholders (optional)</label>
                                <div id="extraPlaceholdersContainer">
                                    @if(old('extra_placeholders'))
                                        @foreach(old('extra_placeholders') as $index => $placeholder)
                                        <div class="row mb-2 placeholder-row">
                                            <div class="col-5">
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="extra_placeholders[{{ $index }}][name]" 
                                                       value="{{ $placeholder['name'] ?? '' }}"
                                                       placeholder="Placeholder name">
                                            </div>
                                            <div class="col-6">
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       name="extra_placeholders[{{ $index }}][value]" 
                                                       value="{{ $placeholder['value'] ?? '' }}"
                                                       placeholder="Value">
                                            </div>
                                            <div class="col-1">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-placeholder">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                @if($document->isDraft())
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addPlaceholderBtn">
                                    <i class="fas fa-plus me-1"></i>Add Placeholder
                                </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
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
                            @if($document->isDraft())
                                <button type="submit" class="btn btn-doctor-primary w-100">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            @endif
                            <a href="{{ route('staff.patients.documents.show', [$patient, $document]) }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                            @can('finalise', $document)
                            @if($document->isDraft())
                                <form action="{{ route('staff.patients.documents.finalise', [$patient, $document]) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to finalise this document? This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-check me-2"></i>Finalise Document
                                    </button>
                                </form>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>

                <!-- Document Status -->
                <div class="doctor-card">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Document Status</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Status</label>
                            <div>
                                @php
                                    $statusColors = [
                                        'draft' => 'warning',
                                        'final' => 'success',
                                        'void' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$document->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ ucfirst($document->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Created</label>
                            <div class="fw-bold">{{ $document->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                        </div>
                        @if($document->updated_at != $document->created_at)
                        <div class="mb-0">
                            <label class="form-label text-muted">Last Updated</label>
                            <div class="fw-bold">{{ $document->updated_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $document->updated_at->diffForHumans() }}</small>
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
@endpush

@push('scripts')
<!-- Quill Rich Text Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
            <div class="row mb-2 placeholder-row">
                <div class="col-5">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="extra_placeholders[${placeholderIndex}][name]" 
                           placeholder="Placeholder name">
                </div>
                <div class="col-6">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           name="extra_placeholders[${placeholderIndex}][value]" 
                           placeholder="Value">
                </div>
                <div class="col-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-placeholder">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
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

