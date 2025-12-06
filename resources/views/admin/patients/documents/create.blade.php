@extends('admin.layouts.app')

@section('title', 'Create Document - ' . $patient->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->full_name }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.documents.index', $patient) }}">Letters & Forms</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header with Gradient -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-white">
                    <h1 class="h3 mb-1 fw-bold">
                        <i class="fas fa-file-plus me-2"></i>Create Document
                    </h1>
                    <p class="mb-0 opacity-75">
                        Patient: <strong>{{ $patient->full_name }}</strong>
                        <span class="badge bg-white bg-opacity-25 ms-2">{{ $patient->patient_id ?? 'N/A' }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Documents
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

    <form action="{{ route('admin.patients.documents.store', $patient) }}" method="POST" id="documentForm">
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Patient Information Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-user me-2 text-primary"></i>Patient Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Patient Name</label>
                                <div class="fw-bold">{{ $patient->full_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Patient ID</label>
                                <div class="fw-bold">{{ $patient->patient_id ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Date of Birth</label>
                                <div>
                                    {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'N/A' }}
                                    @if($patient->date_of_birth)
                                        <span class="badge bg-secondary ms-1">{{ $patient->age }} years</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Email</label>
                                <div>{{ $patient->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Selection Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h5 class="mb-0 fw-semibold">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Document Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="template_id" class="form-label">
                                    Select Template <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('template_id') is-invalid @enderror"
                                        id="template_id" name="template_id" required>
                                    <option value="">-- Choose a template --</option>
                                    @foreach($templates->groupBy('type') as $type => $typeTemplates)
                                        <optgroup label="{{ ucfirst($type) }}s">
                                            @foreach($typeTemplates as $tmpl)
                                                <option value="{{ $tmpl->id }}"
                                                        data-type="{{ $tmpl->type }}"
                                                        {{ old('template_id', $template->id ?? '') == $tmpl->id ? 'selected' : '' }}>
                                                    {{ $tmpl->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('template_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="type" class="form-label">
                                    Document Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg @error('type') is-invalid @enderror"
                                        id="type" name="type" required>
                                    <option value="">Select type</option>
                                    <option value="letter" {{ old('type', $template->type ?? '') == 'letter' ? 'selected' : '' }}>
                                        <i class="fas fa-envelope"></i> Letter
                                    </option>
                                    <option value="form" {{ old('type', $template->type ?? '') == 'form' ? 'selected' : '' }}>
                                        <i class="fas fa-clipboard-list"></i> Form
                                    </option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
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
                                <small class="text-muted">Optional - defaults to template name if left empty</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Fields Section (for Forms) -->
                <div id="formDataSection" class="card border-0 shadow-sm mb-4" style="display: none;">
                    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <h5 class="mb-0 fw-semibold text-white">
                            <i class="fas fa-clipboard-list me-2"></i>Fill Form Fields
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="formFieldsLoading" class="text-center py-5" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 text-muted">Loading form fields...</p>
                        </div>
                        <div id="formFieldsContainer">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Select a form template to load the form fields.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Letter Preview Section -->
                <div id="letterPreviewSection" class="card border-0 shadow-sm mb-4" style="display: none;">
                    <div class="card-header border-0 py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="mb-0 fw-semibold text-white">
                            <i class="fas fa-envelope me-2"></i>Letter Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Letter Document:</strong> This letter will be automatically generated from the template with patient and doctor information.
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-plus-circle me-1 text-primary"></i>
                                Extra Placeholders (optional)
                            </label>
                            <p class="text-muted small mb-3">Add custom values to fill placeholders in the template that aren't automatically filled.</p>
                            <div id="extraPlaceholdersContainer"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addPlaceholderBtn">
                                <i class="fas fa-plus me-1"></i>Add Placeholder
                            </button>
                        </div>
                    </div>
                </div>
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
                            <button type="submit" class="btn btn-lg text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <i class="fas fa-save me-2"></i>Create Document
                            </button>
                            <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </div>

                    <!-- Template Info -->
                    <div class="card-footer bg-light border-0">
                        <div id="templateInfo" style="display: none;">
                            <h6 class="fw-semibold mb-2">
                                <i class="fas fa-info-circle me-1 text-info"></i>Template Info
                            </h6>
                            <div id="templateInfoContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const templateSelect = $('#template_id');
    const typeSelect = $('#type');
    const formDataSection = $('#formDataSection');
    const letterPreviewSection = $('#letterPreviewSection');
    const formFieldsContainer = $('#formFieldsContainer');
    const formFieldsLoading = $('#formFieldsLoading');
    const extraPlaceholdersContainer = $('#extraPlaceholdersContainer');
    const templateInfo = $('#templateInfo');
    const templateInfoContent = $('#templateInfoContent');

    // Template selection handler
    templateSelect.on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const templateType = selectedOption.data('type');
        const templateId = $(this).val();

        if (templateType) {
            typeSelect.val(templateType).trigger('change');
        }

        if (templateId && templateType === 'form') {
            loadTemplateSchema(templateId);
        }

        // Update template info
        if (templateId) {
            templateInfo.show();
            templateInfoContent.html(`
                <p class="mb-1"><small class="text-muted">Template:</small><br><strong>${selectedOption.text()}</strong></p>
                <p class="mb-0"><small class="text-muted">Type:</small><br><span class="badge bg-${templateType === 'letter' ? 'primary' : 'success'}">${templateType.charAt(0).toUpperCase() + templateType.slice(1)}</span></p>
            `);
        } else {
            templateInfo.hide();
        }
    });

    // Type selection handler
    typeSelect.on('change', function() {
        const type = $(this).val();
        if (type === 'form') {
            formDataSection.slideDown();
            letterPreviewSection.slideUp();
        } else if (type === 'letter') {
            formDataSection.slideUp();
            letterPreviewSection.slideDown();
        } else {
            formDataSection.slideUp();
            letterPreviewSection.slideUp();
        }
    });

    // Load template schema via AJAX
    function loadTemplateSchema(templateId) {
        formFieldsLoading.show();
        formFieldsContainer.html('');

        $.ajax({
            url: `/admin/document-templates/${templateId}/schema`,
            method: 'GET',
            success: function(response) {
                formFieldsLoading.hide();
                if (response.success && response.schema && response.schema.length > 0) {
                    renderFormFields(response.schema);
                } else {
                    formFieldsContainer.html(`
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This template doesn't have any form fields defined. You can create the document and add content later.
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                formFieldsLoading.hide();
                formFieldsContainer.html(`
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Failed to load form fields. Please try again.
                    </div>
                `);
            }
        });
    }

    // Render form fields dynamically
    function renderFormFields(schema) {
        let html = '';

        schema.forEach((section, sectionIndex) => {
            html += `
                <div class="form-section">
                    <h6 class="form-section-title">
                        <i class="fas fa-layer-group me-2"></i>${section.title || 'Section ' + (sectionIndex + 1)}
                    </h6>
                    ${section.description ? `<p class="text-muted small mb-3">${section.description}</p>` : ''}
                    <div class="row">
            `;

            (section.fields || []).forEach(field => {
                html += renderField(field);
            });

            html += `
                    </div>
                </div>
            `;
        });

        formFieldsContainer.html(html);
    }

    // Render individual field
    function renderField(field) {
        const required = field.required ? '<span class="required-indicator">*</span>' : '';
        const requiredAttr = field.required ? 'required' : '';
        const fieldName = `form_data[${field.name}]`;

        let fieldHtml = '';

        switch (field.type) {
            case 'text':
                fieldHtml = `
                    <div class="col-md-6 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <input type="text" class="form-control" name="${fieldName}" ${requiredAttr}>
                    </div>
                `;
                break;

            case 'textarea':
                fieldHtml = `
                    <div class="col-12 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <textarea class="form-control" name="${fieldName}" rows="${field.rows || 4}" ${requiredAttr}></textarea>
                    </div>
                `;
                break;

            case 'number':
                fieldHtml = `
                    <div class="col-md-4 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <input type="number" class="form-control" name="${fieldName}"
                               ${field.min !== null ? `min="${field.min}"` : ''}
                               ${field.max !== null ? `max="${field.max}"` : ''}
                               step="${field.step || 1}" ${requiredAttr}>
                    </div>
                `;
                break;

            case 'date':
                fieldHtml = `
                    <div class="col-md-4 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <input type="date" class="form-control" name="${fieldName}"
                               ${field.min ? `min="${field.min}"` : ''}
                               ${field.max ? `max="${field.max}"` : ''} ${requiredAttr}>
                    </div>
                `;
                break;

            case 'select':
                let options = '<option value="">-- Select --</option>';
                (field.options || []).forEach(opt => {
                    const value = typeof opt === 'object' ? opt.value : opt;
                    const label = typeof opt === 'object' ? opt.label : opt;
                    options += `<option value="${value}">${label}</option>`;
                });
                fieldHtml = `
                    <div class="col-md-6 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <select class="form-select" name="${fieldName}" ${requiredAttr}>
                            ${options}
                        </select>
                    </div>
                `;
                break;

            case 'checkbox':
                fieldHtml = `
                    <div class="col-md-6 form-field-group">
                        <div class="form-check mt-4">
                            <input type="hidden" name="${fieldName}" value="0">
                            <input type="checkbox" class="form-check-input" id="field_${field.name}" name="${fieldName}" value="1">
                            <label class="form-check-label" for="field_${field.name}">${field.label}${required}</label>
                        </div>
                    </div>
                `;
                break;

            case 'checkbox_group':
                let checkboxes = '';
                (field.options || []).forEach((opt, idx) => {
                    const value = typeof opt === 'object' ? opt.value : opt;
                    const label = typeof opt === 'object' ? opt.label : opt;
                    checkboxes += `
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="field_${field.name}_${idx}" name="${fieldName}[]" value="${value}">
                            <label class="form-check-label" for="field_${field.name}_${idx}">${label}</label>
                        </div>
                    `;
                });
                fieldHtml = `
                    <div class="col-12 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <div class="checkbox-group-container">
                            ${checkboxes}
                        </div>
                    </div>
                `;
                break;

            case 'radio_group':
                let radios = '';
                (field.options || []).forEach((opt, idx) => {
                    const value = typeof opt === 'object' ? opt.value : opt;
                    const label = typeof opt === 'object' ? opt.label : opt;
                    radios += `
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="field_${field.name}_${idx}" name="${fieldName}" value="${value}" ${requiredAttr}>
                            <label class="form-check-label" for="field_${field.name}_${idx}">${label}</label>
                        </div>
                    `;
                });
                fieldHtml = `
                    <div class="col-12 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <div class="radio-group-container">
                            ${radios}
                        </div>
                    </div>
                `;
                break;

            case 'info_text':
                fieldHtml = `
                    <div class="col-12 form-field-group">
                        <div class="info-text-field">
                            <i class="fas fa-info-circle me-2"></i>${field.text || field.label}
                        </div>
                    </div>
                `;
                break;

            default:
                fieldHtml = `
                    <div class="col-md-6 form-field-group">
                        <label class="form-label">${field.label}${required}</label>
                        <input type="text" class="form-control" name="${fieldName}" ${requiredAttr}>
                    </div>
                `;
        }

        return fieldHtml;
    }

    // Extra placeholders management
    let placeholderIndex = 0;
    $('#addPlaceholderBtn').on('click', function() {
        const placeholderHtml = `
            <div class="placeholder-row d-flex gap-2 align-items-center">
                <div class="flex-grow-1">
                    <input type="text" class="form-control form-control-sm" name="extra_placeholders[${placeholderIndex}][name]" placeholder="Placeholder name (e.g., custom_field)">
                </div>
                <div class="flex-grow-1">
                    <input type="text" class="form-control form-control-sm" name="extra_placeholders[${placeholderIndex}][value]" placeholder="Value">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-placeholder">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        extraPlaceholdersContainer.append(placeholderHtml);
        placeholderIndex++;
    });

    $(document).on('click', '.remove-placeholder', function() {
        $(this).closest('.placeholder-row').remove();
    });

    // Trigger initial state if template is pre-selected
    if (templateSelect.val()) {
        templateSelect.trigger('change');
    }
});
</script>
@endpush
