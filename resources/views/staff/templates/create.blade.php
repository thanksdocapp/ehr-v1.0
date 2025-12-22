@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Create Template')
@section('page-title', 'Create Template')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('staff.templates.index') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Templates
            </a>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">
                <i class="fas fa-plus-circle me-2 text-primary"></i>Create New {{ ucfirst($type ?? 'Letter') }} Template
            </h1>
        </div>
    </div>

    <form action="{{ route('staff.templates.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-lg-8">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">Template Details</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @php 
                            $selectedType = old('type', $type ?? 'letter'); 
                            $isForm = $selectedType === 'form';
                        @endphp
                        @if($selectedType && in_array($selectedType, ['letter', 'form']))
                            <input type="hidden" name="type" value="{{ $selectedType }}">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <div class="form-control bg-light">
                                    <i class="fas fa-{{ $selectedType === 'letter' ? 'envelope' : 'clipboard-list' }} me-2"></i>
                                    <strong>{{ ucfirst($selectedType) }}</strong>
                                </div>
                            </div>
                        @else
                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="letter" {{ $selectedType == 'letter' ? 'selected' : '' }}>Letter</option>
                                <option value="form" {{ $selectedType == 'form' ? 'selected' : '' }}>Form</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif

                        @if($isForm)
                            <!-- Formeo Form Builder for Forms -->
                            <div class="mb-3">
                                <label class="form-label">Form Design <span class="text-danger">*</span></label>
                                <div id="formeo-builder" style="min-height: 500px; border: 1px solid #dee2e6; border-radius: 4px;"></div>
                                <!-- Hidden input for Formeo schema -->
                                <input type="hidden" id="formeo_schema" name="formeo_schema" value="{{ old('formeo_schema', '') }}">
                                <!-- Hidden content field (will store minimal HTML for backward compatibility) -->
                                <textarea class="d-none" id="content" name="content">{{ old('content', '<!-- Formeo Form -->') }}</textarea>
                                @error('formeo_schema')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Use the drag-and-drop form builder to create your form. Add fields, configure labels, and set validation rules.
                                </small>
                            </div>
                        @else
                            <!-- Quill Editor for Letters -->
                            <div class="mb-3">
                                <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                <!-- Quill Editor Container -->
                                <div id="quill-editor" style="min-height: 400px;"></div>
                                <!-- Hidden textarea for form submission -->
                                <textarea class="form-control @error('content') is-invalid @enderror d-none"
                                          id="content" name="content" required>{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-save me-2"></i>Create Template
                    </button>
                    <a href="{{ route('staff.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="doctor-card mb-3">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-code me-2"></i>Data Placeholders
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="text-muted small">Auto-filled patient/doctor data:</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach(\App\Models\Template::DEFAULT_PLACEHOLDERS as $placeholder)
                                <button type="button" class="btn btn-sm btn-outline-secondary placeholder-btn"
                                        data-placeholder="{{ $placeholder }}">
                                    {{ $placeholder }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Form Builder Info (shown when type is 'form') -->
                @php $isForm = ($selectedType ?? 'letter') === 'form'; @endphp
                <div class="doctor-card mb-3" id="formBuilderInfo" style="{{ $isForm ? '' : 'display: none;' }}">
                    <div class="doctor-card-header bg-success text-white">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-wand-magic-sparkles me-2"></i>Form Builder
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="text-muted small mb-2">
                            Use the drag-and-drop form builder on the left to design your form.
                        </p>
                        <ul class="small text-muted ps-3 mb-0">
                            <li>Drag fields from the sidebar to add them</li>
                            <li>Click fields to edit their properties</li>
                            <li>Configure labels, placeholders, and validation</li>
                            <li>Set required fields as needed</li>
                        </ul>
                    </div>
                </div>

                <!-- Legacy Form Fields Helper (hidden when using Formeo) -->
                <div class="doctor-card mb-3" id="formFieldsCard" style="display: none;">
                    <div class="doctor-card-header bg-success text-white">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-edit me-2"></i>Fillable Form Fields
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="text-muted small">Fields patients can fill out online:</p>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Field Name</label>
                            <input type="text" class="form-control form-control-sm" id="fieldName" placeholder="e.g., patient_signature">
                            <small class="text-muted">Use lowercase with underscores</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Field Label</label>
                            <input type="text" class="form-control form-control-sm" id="fieldLabel" placeholder="e.g., Patient Signature">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary insert-field-btn" data-type="input">
                                <i class="fas fa-font me-1"></i>Text Input
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary insert-field-btn" data-type="textarea">
                                <i class="fas fa-align-left me-1"></i>Text Area
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary insert-field-btn" data-type="date">
                                <i class="fas fa-calendar me-1"></i>Date Input
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary insert-field-btn" data-type="checkbox">
                                <i class="fas fa-check-square me-1"></i>Checkbox
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success insert-field-btn" data-type="signature">
                                <i class="fas fa-signature me-1"></i>Signature Pad
                            </button>
                        </div>

                        <hr>
                        <p class="text-muted small mb-2"><strong>Quick Insert (common fields):</strong></p>
                        <div class="d-grid gap-1">
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-field-btn"
                                    data-field="@{{signature:patient_signature:Patient Signature}}">
                                Patient Signature
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-field-btn"
                                    data-field="@{{input:signature_date:Date:date}}">
                                Signature Date
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-field-btn"
                                    data-field="@{{checkbox:consent_given:I agree to the terms above}}">
                                Consent Checkbox
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary quick-field-btn"
                                    data-field="@{{textarea:additional_notes:Additional Notes or Comments}}">
                                Additional Notes
                            </button>
                        </div>
                    </div>
                </div>

                <div class="doctor-card" id="formFieldsHelp" style="display: none;">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Field Syntax
                        </h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="small text-muted mb-2">Form fields use this syntax:</p>
                        <code class="small d-block mb-2">@{{type:name:label}}</code>
                        <ul class="small text-muted ps-3 mb-0">
                            <li><code>input</code> - Text input</li>
                            <li><code>textarea</code> - Multi-line text</li>
                            <li><code>checkbox</code> - Checkbox</li>
                            <li><code>signature</code> - Signature pad</li>
                            <li><code>select</code> - Dropdown (add :opt1,opt2)</li>
                            <li><code>radio</code> - Radio buttons</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
@if(!$isForm)
<!-- Quill Editor CSS (only for letters) -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@else
<!-- Formeo Form Builder CSS -->
<link rel="stylesheet" href="https://unpkg.com/formeo@latest/dist/formeo.min.css">
@endif
@endpush

@push('scripts')
@if(!$isForm)
<!-- Quill Editor JS (only for letters) -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<!-- Shared Quill Initialization -->
<script src="{{ asset('js/quill-init.js') }}"></script>
@else
<!-- Formeo Form Builder JS -->
<script src="https://unpkg.com/formeo@latest/dist/formeo.umd.js"></script>
<!-- Formeo Initialization -->
<script src="{{ asset('js/formeo-init.js') }}" onerror="console.warn('formeo-init.js failed to load, using fallback initialization')"></script>
@endif

<script>
    @if($isForm)
    let formeoBuilder;
    @else
    let quill;
    @endif

    function normalizeToken(token) {
        // Tokens are written with a leading '@' in Blade to avoid parsing; strip the leading '@' before inserting.
        if (!token) return '';
        return (token.startsWith('@{{')) ? token.substring(1) : token;
    }

    @if($isForm)
    // Fallback Formeo initialization if formeo-init.js didn't load
    if (typeof window.initFormeoBuilder === 'undefined') {
        window.initFormeoBuilder = async function(containerSelector, formData = null) {
            // Formeo UMD exposes FormeoEditor globally
            let FormeoEditorClass = null;
            
            if (typeof FormeoEditor !== 'undefined') {
                FormeoEditorClass = FormeoEditor;
            } else if (typeof window.FormeoEditor !== 'undefined') {
                FormeoEditorClass = window.FormeoEditor;
            } else if (typeof Formeo !== 'undefined' && Formeo.FormeoEditor) {
                FormeoEditorClass = Formeo.FormeoEditor;
            } else {
                console.error('FormeoEditor is not loaded. Please include Formeo JS library.');
                return null;
            }

            const container = typeof containerSelector === 'string' 
                ? document.querySelector(containerSelector) 
                : containerSelector;

            if (!container) {
                console.error('Formeo container not found: ' + containerSelector);
                return null;
            }

            try {
                const editorOptions = {
                    editorContainer: containerSelector,
                };
                const formeo = new FormeoEditorClass(editorOptions);
                
                if (formData) {
                    try {
                        const schema = typeof formData === 'string' ? JSON.parse(formData) : formData;
                        if (typeof formeo.render === 'function') {
                            formeo.render(schema);
                        } else if (formeo.formData !== undefined) {
                            formeo.formData = schema;
                        }
                    } catch (error) {
                        console.error('Error loading Formeo form data:', error);
                    }
                }
                return formeo;
            } catch (error) {
                console.error('Failed to initialize Formeo builder:', error);
                return null;
            }
        };
        
        window.getFormeoSchema = function(formeo) {
            if (!formeo) return null;
            try {
                return formeo.formData || null;
            } catch (error) {
                console.error('Error getting Formeo schema:', error);
                return null;
            }
        };
        
        window.setFormeoSchema = function(formeo, schema) {
            if (!formeo) return;
            try {
                const formSchema = typeof schema === 'string' ? JSON.parse(schema) : schema;
                if (typeof formeo.render === 'function') {
                    formeo.render(formSchema);
                }
            } catch (error) {
                console.error('Error setting Formeo schema:', error);
            }
        };
    }
    @endif

    document.addEventListener('DOMContentLoaded', async function() {
        @if($isForm)
        // Initialize Formeo form builder for forms
        const existingSchema = document.getElementById('formeo_schema').value;
        const schemaData = existingSchema ? JSON.parse(existingSchema) : null;
        
        formeoBuilder = await window.initFormeoBuilder('#formeo-builder', schemaData);
        
        if (formeoBuilder) {
            // Listen for form changes and update hidden field
            formeoBuilder.on('update', function() {
                const schema = window.getFormeoSchema(formeoBuilder);
                if (schema) {
                    document.getElementById('formeo_schema').value = JSON.stringify(schema);
                    document.getElementById('content').value = '<!-- Formeo Form -->';
                }
            });
        }
        @else
        // Initialize Quill editor for letters
        quill = window.initQuillEditor('#quill-editor', {
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link'],
                    ['clean']
                ]
            },
            placeholder: 'Start typing your template content...'
        });

        // Load existing content if any
        const textarea = document.getElementById('content');
        if (textarea && textarea.value) {
            window.setQuillContent(quill, textarea.value);
        }

        // Sync Quill content to hidden textarea
        window.syncQuillToTextarea(quill, '#content');
        
        // Create editor reference for placeholder insertion
        const editor = {
            insertContent: function(content) {
                const range = quill.getSelection(true);
                quill.insertText(range.index, content, 'user');
                quill.setSelection(range.index + content.length);
            }
        };
        @endif


    function makeTokenDraggable(el, getToken) {
        if (!el) return;
        el.setAttribute('draggable', 'true');
        el.style.cursor = 'grab';

        el.addEventListener('dragstart', function(e) {
            const token = normalizeToken(typeof getToken === 'function' ? (getToken() || '') : '');
            if (!token) {
                if (e && typeof e.preventDefault === 'function') e.preventDefault();
                return;
            }

            try {
                e.dataTransfer.effectAllowed = 'copy';
                e.dataTransfer.setData('text/plain', token);
                e.dataTransfer.setData('text', token);
            } catch (err) {
                // Ignore drag payload errors
            }
        });
    }

        // Data placeholder buttons (only for letters)
        @if(!$isForm)
        document.querySelectorAll('.placeholder-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const placeholder = normalizeToken(this.dataset.placeholder);
                if (quill && editor) {
                    editor.insertContent(placeholder);
                }
            });

            makeTokenDraggable(btn, function() {
                return btn.dataset.placeholder || '';
            });
        });
        @endif

    // Show/hide form builder info based on type selection (for dynamic type switching)
    const typeSelect = document.getElementById('type');
    if (typeSelect) {
        const formBuilderInfo = document.getElementById('formBuilderInfo');
        const formFieldsCard = document.getElementById('formFieldsCard');
        const formFieldsHelp = document.getElementById('formFieldsHelp');

        function toggleFormFields() {
            const isForm = typeSelect.value === 'form';
            if (formBuilderInfo) formBuilderInfo.style.display = isForm ? 'block' : 'none';
            if (formFieldsCard) formFieldsCard.style.display = 'none'; // Legacy helper, always hidden with Formeo
            if (formFieldsHelp) formFieldsHelp.style.display = 'none'; // Legacy helper, always hidden with Formeo
        }

        typeSelect.addEventListener('change', toggleFormFields);
    }

    // Insert field buttons
    document.querySelectorAll('.insert-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const fieldType = this.dataset.type;
            const fieldName = document.getElementById('fieldName').value.trim();
            const fieldLabel = document.getElementById('fieldLabel').value.trim();

            if (!fieldName || !fieldLabel) {
                alert('Please enter both Field Name and Field Label');
                return;
            }

            // Convert field name to snake_case
            const safeName = fieldName.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');

            let placeholder;
            if (fieldType === 'date') {
                placeholder = `@{{input:${safeName}:${fieldLabel}:date}}`;
            } else if (fieldType === 'input') {
                placeholder = `@{{input:${safeName}:${fieldLabel}:text}}`;
            } else {
                placeholder = `@{{${fieldType}:${safeName}:${fieldLabel}}}`;
            }

            @if(!$isForm)
            if (quill && editor) {
                editor.insertContent(placeholder);
            }
            @endif

            // Clear inputs
            document.getElementById('fieldName').value = '';
            document.getElementById('fieldLabel').value = '';
        });
    });

        // Quick field buttons (legacy, not used with Formeo)
        @if(!$isForm)
        document.querySelectorAll('.quick-field-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const field = normalizeToken(this.dataset.field);
                if (quill && editor) {
                    editor.insertContent(field);
                }
            });

            makeTokenDraggable(btn, function() {
                return btn.dataset.field || '';
            });
        });
        @endif

        // Sync content before form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                @if($isForm)
                // For forms, ensure Formeo schema is saved
                if (formeoBuilder) {
                    const schema = window.getFormeoSchema(formeoBuilder);
                    if (schema) {
                        document.getElementById('formeo_schema').value = JSON.stringify(schema);
                        document.getElementById('content').value = '<!-- Formeo Form -->';
                    } else {
                        e.preventDefault();
                        alert('Please add at least one field to your form.');
                        return false;
                    }
                }
                @else
                // For letters, sync Quill content
                if (quill) {
                    const html = window.getQuillContent(quill);
                    document.getElementById('content').value = html;
                }
                @endif
            });
        }
    });
</script>
@endpush
