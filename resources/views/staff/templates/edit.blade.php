@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Template')
@section('page-title', 'Edit Template')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('staff.templates.show', $template) }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Template
            </a>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">
                <i class="fas fa-edit me-2 text-primary"></i>Edit: {{ $template->name }}
            </h1>
        </div>
    </div>

    <form action="{{ route('staff.templates.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
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
                                   id="name" name="name" value="{{ old('name', $template->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="letter" {{ old('type', $template->type) == 'letter' ? 'selected' : '' }}>Letter</option>
                                <option value="form" {{ old('type', $template->type) == 'form' ? 'selected' : '' }}>Form</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="20">{{ old('content', $template->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">
                        <i class="fas fa-save me-2"></i>Update Template
                    </button>
                    <a href="{{ route('staff.templates.show', $template) }}" class="btn btn-outline-secondary">Cancel</a>
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

                <!-- Fillable Form Fields (shown when type is 'form') -->
                <div class="doctor-card mb-3" id="formFieldsCard" style="{{ $template->type === 'form' ? '' : 'display: none;' }}">
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

                <div class="doctor-card" id="formFieldsHelp" style="{{ $template->type === 'form' ? '' : 'display: none;' }}">
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

@push('scripts')
<script src="{{ getTinyMceCdnUrl() }}" referrerpolicy="origin"></script>
<script>
    let editor;
    tinymce.init({
        selector: '#content',
        height: 500,
        plugins: 'lists link table code',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | table | code',
        setup: function(ed) {
            editor = ed;
        }
    });

    // Data placeholder buttons
    document.querySelectorAll('.placeholder-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const placeholder = this.dataset.placeholder;
            if (editor) {
                editor.insertContent(placeholder);
            }
        });
    });

    // Show/hide form fields panel based on type selection
    const typeSelect = document.getElementById('type');
    const formFieldsCard = document.getElementById('formFieldsCard');
    const formFieldsHelp = document.getElementById('formFieldsHelp');

    function toggleFormFields() {
        const isForm = typeSelect.value === 'form';
        formFieldsCard.style.display = isForm ? 'block' : 'none';
        formFieldsHelp.style.display = isForm ? 'block' : 'none';
    }

    typeSelect.addEventListener('change', toggleFormFields);

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

            if (editor) {
                editor.insertContent(placeholder);
            }

            // Clear inputs
            document.getElementById('fieldName').value = '';
            document.getElementById('fieldLabel').value = '';
        });
    });

    // Quick field buttons
    document.querySelectorAll('.quick-field-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const field = this.dataset.field;
            if (editor) {
                editor.insertContent(field);
            }
        });
    });
</script>
@endpush
