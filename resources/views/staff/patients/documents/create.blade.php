@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create Document - ' . $patient->full_name)

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
                            <li class="breadcrumb-item active">Create</li>
                        </ol>
                    </nav>
                    <h1 class="h3 mb-0 text-gray-900">
                        <i class="fas fa-file-plus me-2 text-primary"></i>Create Document
                    </h1>
                    <p class="text-muted mb-0">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id ?? 'N/A' }})</p>
                </div>
                <a href="{{ route('staff.patients.documents.index', $patient) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
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

    <form action="{{ route('staff.patients.documents.store', $patient) }}" method="POST" id="documentForm">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Patient Name</label>
                                <div class="form-control-plaintext fw-bold">{{ $patient->full_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Patient ID</label>
                                <div class="form-control-plaintext">{{ $patient->patient_id ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <div class="form-control-plaintext">
                                    {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}
                                    @if($patient->date_of_birth)
                                        <small class="text-muted">({{ $patient->age }} years old)</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="form-control-plaintext">
                                    {{ $patient->email ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-file-alt me-2"></i>Document Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="template_id" class="form-label">Template <span class="text-danger">*</span></label>
                            <select class="form-control @error('template_id') is-invalid @enderror" 
                                    id="template_id" name="template_id" required>
                                <option value="">Select a template</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" 
                                            data-type="{{ $tmpl->type }}"
                                            {{ old('template_id', $template->id ?? '') == $tmpl->id ? 'selected' : '' }}>
                                        {{ $tmpl->name }} ({{ ucfirst($tmpl->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choose a letter or form template to create the document</small>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" 
                                    id="type" name="type" required>
                                <option value="">Select type</option>
                                <option value="letter" {{ old('type', $template->type ?? '') == 'letter' ? 'selected' : '' }}>Letter</option>
                                <option value="form" {{ old('type', $template->type ?? '') == 'form' ? 'selected' : '' }}>Form</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Document Title</label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Leave blank to use template name">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">If left blank, the template name will be used</small>
                        </div>

                        <!-- For Forms: Form Data Input -->
                        <div id="formDataSection" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Form Document:</strong> Fill in the form fields below. The form schema will be loaded from the selected template.
                            </div>
                            <div id="formFieldsContainer">
                                <!-- Form fields will be dynamically loaded here based on template schema -->
                            </div>
                        </div>

                        <!-- For Letters: Content Preview (read-only, auto-generated) -->
                        <div id="letterPreviewSection" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Letter Document:</strong> This letter will be automatically generated from the template with patient information.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Extra Placeholders (optional)</label>
                                <div id="extraPlaceholdersContainer">
                                    <!-- Placeholder fields will be dynamically added here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addPlaceholderBtn">
                                    <i class="fas fa-plus me-1"></i>Add Placeholder
                                </button>
                            </div>
                        </div>
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
                            <button type="submit" class="btn btn-doctor-primary w-100">
                                <i class="fas fa-save me-2"></i>Create Document
                            </button>
                            <a href="{{ route('staff.patients.documents.index', $patient) }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Document Preview -->
                <div class="doctor-card mb-4" id="previewCard" style="display: none;">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-eye me-2"></i>Preview</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div id="documentPreview" class="border p-3 rounded" style="min-height: 200px; max-height: 400px; overflow-y: auto;">
                            <p class="text-muted text-center">Select a template to preview</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const templateSelect = $('#template_id');
    const typeSelect = $('#type');
    const formDataSection = $('#formDataSection');
    const letterPreviewSection = $('#letterPreviewSection');
    const formFieldsContainer = $('#formFieldsContainer');
    const extraPlaceholdersContainer = $('#extraPlaceholdersContainer');
    const previewCard = $('#previewCard');
    const documentPreview = $('#documentPreview');
    
    // Sync type with template
    templateSelect.on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const templateType = selectedOption.data('type');
        if (templateType) {
            typeSelect.val(templateType).trigger('change');
        }
    });
    
    // Handle type change
    typeSelect.on('change', function() {
        const type = $(this).val();
        if (type === 'form') {
            formDataSection.show();
            letterPreviewSection.hide();
            loadFormSchema();
        } else if (type === 'letter') {
            formDataSection.hide();
            letterPreviewSection.show();
            loadLetterPreview();
        } else {
            formDataSection.hide();
            letterPreviewSection.hide();
            previewCard.hide();
        }
    });
    
    // Load form schema
    function loadFormSchema() {
        const templateId = templateSelect.val();
        if (!templateId) {
            formFieldsContainer.html('<p class="text-muted">Please select a template</p>');
            return;
        }
        
        // For now, show a simple message. Full implementation would fetch schema via AJAX
        formFieldsContainer.html(`
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Form fields will be dynamically loaded based on the selected template schema.
                <br><small>This feature requires the template builder to be fully implemented.</small>
            </div>
        `);
    }
    
    // Load letter preview
    function loadLetterPreview() {
        const templateId = templateSelect.val();
        if (!templateId) {
            documentPreview.html('<p class="text-muted text-center">Select a template to preview</p>');
            previewCard.hide();
            return;
        }
        
        previewCard.show();
        documentPreview.html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading preview...</span>
                </div>
                <p class="text-muted mt-2">Generating preview...</p>
            </div>
        `);
        
        // In a full implementation, this would make an AJAX call to preview the letter
        setTimeout(function() {
            documentPreview.html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Letter preview will be generated from the template with patient information.
                    <br><small>The full letter content will be automatically generated when you create the document.</small>
                </div>
            `);
        }, 500);
    }
    
    // Add placeholder field
    let placeholderIndex = 0;
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
        extraPlaceholdersContainer.append(placeholderHtml);
        placeholderIndex++;
    });
    
    // Remove placeholder
    $(document).on('click', '.remove-placeholder', function() {
        $(this).closest('.placeholder-row').remove();
    });
    
    // Initialize if template is pre-selected
    if (templateSelect.val()) {
        typeSelect.trigger('change');
    }
});
</script>
@endpush

