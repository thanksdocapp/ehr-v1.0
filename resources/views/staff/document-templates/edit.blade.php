@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Document Template - ' . $documentTemplate->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">{{ ucfirst(auth()->user()->role) }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.document-templates.index') }}">Document Templates</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<!-- SortableJS for drag-and-drop -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.css">
<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .template-builder {
        background: #f8f9fa;
        border-radius: 8px;
        min-height: 500px;
    }
    
    .builder-toolbar {
        background: white;
        border-radius: 8px 8px 0 0;
        padding: 15px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .block-palette {
        background: white;
        border-radius: 8px;
        padding: 15px;
        max-height: 600px;
        overflow-y: auto;
    }
    
    .block-item {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        cursor: move;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .block-item:hover {
        border-color: #0d6efd;
        background: #e7f1ff;
        transform: translateX(5px);
    }
    
    .block-item i {
        font-size: 1.2rem;
        color: #0d6efd;
        width: 24px;
    }
    
    .builder-canvas {
        background: white;
        border-radius: 8px;
        padding: 20px;
        min-height: 600px;
        position: relative;
    }
    
    .builder-block {
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        padding: 15px;
        margin-bottom: 15px;
        cursor: move;
        transition: all 0.3s;
        position: relative;
    }
    
    .builder-block:hover {
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
    }
    
    .builder-block.sortable-ghost {
        opacity: 0.4;
        border-color: #0d6efd;
    }
    
    .builder-block.sortable-drag {
        opacity: 0.8;
    }
    
    .block-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .block-header h6 {
        margin: 0;
        font-weight: 600;
        color: #495057;
        flex: 1;
    }
    
    .block-actions {
        display: flex;
        gap: 5px;
    }
    
    .block-content {
        padding: 10px 0;
    }
    
    .empty-canvas {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
    }
    
    .empty-canvas i {
        font-size: 3rem;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .preview-panel {
        background: white;
        border-radius: 8px;
        padding: 20px;
        max-height: 600px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
    }
    
    .section-block {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    
    .section-fields {
        margin-top: 15px;
        padding-left: 20px;
    }
    
    .field-block {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 12px;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-edit me-2 text-primary"></i>Edit Document Template
            </h1>
            <p class="text-muted mb-0">{{ $documentTemplate->name }}</p>
        </div>
        <a href="{{ route('staff.document-templates.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
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

    <form action="{{ route('staff.document-templates.update', $documentTemplate) }}" method="POST" id="templateForm">
        @csrf
        @method('PUT')
        
        <div class="row mb-4">
            <!-- Basic Information -->
            <div class="col-12">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $documentTemplate->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Template Type <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="letter" {{ old('type', $documentTemplate->type) == 'letter' ? 'selected' : '' }}>Letter</option>
                                        <option value="form" {{ old('type', $documentTemplate->type) == 'form' ? 'selected' : '' }}>Form</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" 
                                           class="form-control @error('slug') is-invalid @enderror" 
                                           id="slug" 
                                           name="slug" 
                                           value="{{ old('slug', $documentTemplate->slug) }}">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Block Palette (Left Sidebar) -->
            <div class="col-lg-3">
                <div class="block-palette sticky-top" style="top: 20px;">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-cubes me-2"></i>Blocks
                    </h6>
                    
                    <div id="letterBlocks" style="display: {{ $documentTemplate->type === 'letter' ? 'block' : 'none' }};">
                        <div class="block-item" data-block-type="heading" draggable="true">
                            <i class="fas fa-heading"></i>
                            <div>
                                <strong>Heading</strong>
                                <small class="d-block text-muted">Title or section header</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="paragraph" draggable="true">
                            <i class="fas fa-paragraph"></i>
                            <div>
                                <strong>Paragraph</strong>
                                <small class="d-block text-muted">Text content</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="patient_field" draggable="true">
                            <i class="fas fa-user"></i>
                            <div>
                                <strong>Patient Field</strong>
                                <small class="d-block text-muted">Insert patient data</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="doctor_field" draggable="true">
                            <i class="fas fa-user-md"></i>
                            <div>
                                <strong>Doctor Field</strong>
                                <small class="d-block text-muted">Insert doctor data</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="date_block" draggable="true">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <strong>Date</strong>
                                <small class="d-block text-muted">Insert date</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="divider" draggable="true">
                            <i class="fas fa-minus"></i>
                            <div>
                                <strong>Divider</strong>
                                <small class="d-block text-muted">Horizontal line</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="logo_block" draggable="true">
                            <i class="fas fa-image"></i>
                            <div>
                                <strong>Logo</strong>
                                <small class="d-block text-muted">Clinic/doctor logo</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="signature_block" draggable="true">
                            <i class="fas fa-signature"></i>
                            <div>
                                <strong>Signature</strong>
                                <small class="d-block text-muted">Doctor signature</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="text_placeholder" draggable="true">
                            <i class="fas fa-tag"></i>
                            <div>
                                <strong>Placeholder</strong>
                                <small class="d-block text-muted">Custom placeholder</small>
                            </div>
                        </div>
                    </div>
                    
                    <div id="formBlocks" style="display: {{ $documentTemplate->type === 'form' ? 'block' : 'none' }};">
                        <div class="block-item" data-block-type="section" draggable="true">
                            <i class="fas fa-folder"></i>
                            <div>
                                <strong>Section</strong>
                                <small class="d-block text-muted">Group of fields</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="text" draggable="true">
                            <i class="fas fa-font"></i>
                            <div>
                                <strong>Text Field</strong>
                                <small class="d-block text-muted">Single line text</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="textarea" draggable="true">
                            <i class="fas fa-align-left"></i>
                            <div>
                                <strong>Textarea</strong>
                                <small class="d-block text-muted">Multi-line text</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="select" draggable="true">
                            <i class="fas fa-list"></i>
                            <div>
                                <strong>Select</strong>
                                <small class="d-block text-muted">Dropdown options</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="checkbox" draggable="true">
                            <i class="fas fa-check-square"></i>
                            <div>
                                <strong>Checkbox</strong>
                                <small class="d-block text-muted">Yes/No option</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="radio_group" draggable="true">
                            <i class="fas fa-dot-circle"></i>
                            <div>
                                <strong>Radio Group</strong>
                                <small class="d-block text-muted">Multiple choice</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="date" draggable="true">
                            <i class="fas fa-calendar-alt"></i>
                            <div>
                                <strong>Date Field</strong>
                                <small class="d-block text-muted">Date picker</small>
                            </div>
                        </div>
                        
                        <div class="block-item" data-block-type="number" draggable="true">
                            <i class="fas fa-hashtag"></i>
                            <div>
                                <strong>Number</strong>
                                <small class="d-block text-muted">Numeric input</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Builder Canvas (Center) -->
            <div class="col-lg-6">
                <div class="doctor-card">
                    <div class="builder-toolbar">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Template Builder
                            </h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" id="previewBtn">
                                    <i class="fas fa-eye me-1"></i>Preview
                                </button>
                                <button type="button" class="btn btn-outline-info" id="previewWithPatientBtn">
                                    <i class="fas fa-user me-1"></i>Preview with Sample Patient
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="clearCanvasBtn">
                                    <i class="fas fa-trash me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="builder-canvas" id="builderCanvas">
                        <div id="builderBlocks"></div>
                    </div>
                </div>
                
                <input type="hidden" name="builder_config" id="builderConfig" value="{{ json_encode($documentTemplate->builder_config ?? []) }}">
                <input type="hidden" name="render_mode" value="builder">
            </div>

            <!-- Preview Panel (Right Sidebar) -->
            <div class="col-lg-3">
                <div class="preview-panel sticky-top" style="top: 20px;">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-eye me-2"></i>Preview
                    </h6>
                    <div id="templatePreview" class="text-muted text-center py-5">
                        <i class="fas fa-eye-slash fa-2x mb-2"></i>
                        <p>Click Preview to see template</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="doctor-card">
                    <div class="doctor-card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $documentTemplate->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active (Template will be available for use)
                                </label>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('staff.document-templates.show', $documentTemplate) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-eye me-2"></i>View
                                </a>
                                <a href="{{ route('staff.document-templates.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Block Configuration Modal -->
<div class="modal fade" id="blockConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blockConfigModalTitle">Configure Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="blockConfigModalBody">
                <!-- Configuration form will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBlockConfig">Save Configuration</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SortableJS for drag-and-drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!-- Quill Rich Text Editor -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
let builderBlocks = @json($documentTemplate->builder_config ?? []);
let currentEditingBlock = null;
let sortable = null;
let quillEditors = {};

// Initialize blocks with IDs if they don't have them
builderBlocks = builderBlocks.map((block, index) => {
    if (!block.id) {
        block.id = 'block_' + Date.now() + '_' + index;
    }
    if (block.children) {
        block.children = block.children.map((child, childIndex) => {
            if (!child.id) {
                child.id = 'field_' + Date.now() + '_' + index + '_' + childIndex;
            }
            return child;
        });
    }
    return block;
});

$(document).ready(function() {
    const typeSelect = $('#type');
    const letterBlocks = $('#letterBlocks');
    const formBlocks = $('#formBlocks');
    const builderBlocksContainer = $('#builderBlocks');
    const previewPanel = $('#templatePreview');
    
    // Initialize builder based on type
    if ($('#type').val() === 'letter') {
        initializeLetterBuilder();
    } else {
        initializeFormBuilder();
    }
    
    // Handle type change
    typeSelect.on('change', function() {
        const type = $(this).val();
        if (type === 'letter') {
            letterBlocks.show();
            formBlocks.hide();
            initializeLetterBuilder();
        } else {
            letterBlocks.hide();
            formBlocks.show();
            initializeFormBuilder();
        }
    });
    
    // Initialize letter builder
    function initializeLetterBuilder() {
        // Render existing blocks
        renderAllBlocks();
        
        if (!sortable || sortable.el !== builderBlocksContainer[0]) {
            sortable = new Sortable(builderBlocksContainer[0], {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                onEnd: function(evt) {
                    updateBuilderConfig();
                    updatePreview();
                }
            });
        }
        
        // Make palette blocks draggable
        $('.block-item[data-block-type]').each(function() {
            $(this).on('dragstart', function(e) {
                e.originalEvent.dataTransfer.setData('blockType', $(this).data('block-type'));
            });
        });
        
        // Enable drop zone
        builderBlocksContainer.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        builderBlocksContainer.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const blockType = e.originalEvent.dataTransfer.getData('blockType');
            if (blockType) {
                addBlock(blockType);
            }
        });
    }
    
    // Initialize form builder
    function initializeFormBuilder() {
        renderAllBlocks();
        
        if (!sortable || sortable.el !== builderBlocksContainer[0]) {
            sortable = new Sortable(builderBlocksContainer[0], {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                group: 'formBuilder',
                onEnd: function(evt) {
                    updateBuilderConfig();
                    updatePreview();
                }
            });
        }
        
        // Make palette blocks draggable
        $('.block-item[data-block-type]').each(function() {
            $(this).on('dragstart', function(e) {
                e.originalEvent.dataTransfer.setData('blockType', $(this).data('block-type'));
            });
        });
        
        // Enable drop zone
        builderBlocksContainer.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('drag-over');
        });
        
        builderBlocksContainer.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('drag-over');
            
            const blockType = e.originalEvent.dataTransfer.getData('blockType');
            if (blockType) {
                if (blockType === 'section') {
                    addSection();
                } else {
                    const lastSection = builderBlocksContainer.find('.section-block:last');
                    if (lastSection.length) {
                        addFieldToSection(blockType, lastSection);
                    } else {
                        alert('Please add a Section block first for form fields.');
                    }
                }
            }
        });
        
        // Initialize section field sortables
        setTimeout(() => {
            builderBlocksContainer.find('.section-fields').each(function() {
                new Sortable(this, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    group: 'formFields',
                    onEnd: function() {
                        updateBuilderConfig();
                        updatePreview();
                    }
                });
            });
        }, 100);
    }
    
    // Render all blocks
    function renderAllBlocks() {
        builderBlocksContainer.empty();
        
        builderBlocks.forEach(block => {
            if (block.type === 'section') {
                renderSection(block);
            } else {
                renderBlock(block);
            }
        });
        
        // Initialize Quill editors
        setTimeout(() => {
            builderBlocks.forEach(block => {
                if (block.type === 'paragraph') {
                    initQuillEditor(block.id);
                }
            });
        }, 200);
    }
    
    // Add block
    function addBlock(blockType) {
        const blockId = 'block_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        const block = {
            id: blockId,
            type: blockType,
            props: getDefaultProps(blockType),
            children: []
        };
        
        builderBlocks.push(block);
        renderBlock(block);
        updateBuilderConfig();
        updatePreview();
        
        if (block.type === 'paragraph') {
            setTimeout(() => {
                initQuillEditor(block.id);
            }, 100);
        }
    }
    
    // Add section
    function addSection() {
        const sectionId = 'section_' + Date.now();
        const section = {
            id: sectionId,
            type: 'section',
            props: {
                title: 'New Section',
                description: ''
            },
            children: []
        };
        
        builderBlocks.push(section);
        renderSection(section);
        updateBuilderConfig();
        updatePreview();
        
        setTimeout(() => {
            const sectionEl = $(`#${sectionId}`);
            new Sortable(sectionEl.find('.section-fields')[0], {
                animation: 150,
                ghostClass: 'sortable-ghost',
                group: 'formFields',
                onEnd: function() {
                    updateBuilderConfig();
                    updatePreview();
                }
            });
        }, 100);
    }
    
    // Add field to section
    function addFieldToSection(fieldType, sectionElement) {
        const sectionId = sectionElement.data('section-id');
        const fieldId = 'field_' + Date.now();
        
        const section = builderBlocks.find(b => b.id === sectionId);
        if (!section) return;
        
        const field = {
            id: fieldId,
            type: fieldType,
            props: getDefaultFieldProps(fieldType)
        };
        
        if (!section.children) section.children = [];
        section.children.push(field);
        
        renderField(field, sectionElement.find('.section-fields'));
        updateBuilderConfig();
        updatePreview();
    }
    
    // Get default props
    function getDefaultProps(blockType) {
        const defaults = {
            heading: { level: 'h2', text: 'Heading', align: 'left' },
            paragraph: { html: '<p>Enter your text here...</p>' },
            patient_field: { field: 'full_name' },
            doctor_field: { field: 'name' },
            date_block: { format: 'M d, Y' },
            divider: { style: 'solid' },
            logo_block: { source: 'clinic', align: 'center', max_width: '150px' },
            signature_block: { signer: 'doctor', show_name: true, show_role: true, use_signature_image: false },
            text_placeholder: { name: 'placeholder_name', label: 'Placeholder Label', value: '[Value]' }
        };
        return defaults[blockType] || {};
    }
    
    function getDefaultFieldProps(fieldType) {
        const defaults = {
            text: { name: 'field_name', label: 'Field Label', required: false, placeholder: '' },
            textarea: { name: 'field_name', label: 'Field Label', required: false, placeholder: '' },
            select: { name: 'field_name', label: 'Field Label', required: false, options: ['Option 1', 'Option 2'] },
            checkbox: { name: 'field_name', label: 'Field Label', required: false },
            radio_group: { name: 'field_name', label: 'Field Label', required: false, options: ['Option 1', 'Option 2'] },
            date: { name: 'field_name', label: 'Field Label', required: false },
            number: { name: 'field_name', label: 'Field Label', required: false, min: null, max: null }
        };
        return defaults[fieldType] || {};
    }
    
    function renderBlock(block) {
        const blockHtml = getBlockHtml(block);
        builderBlocksContainer.append(blockHtml);
    }
    
    function renderSection(section) {
        const sectionHtml = `
            <div class="section-block builder-block" data-section-id="${section.id}" id="${section.id}">
                <div class="block-header">
                    <h6><i class="fas fa-folder me-2"></i>${section.props.title || 'Section'}</h6>
                    <div class="block-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBlock('${section.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock('${section.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content">
                    ${section.props.description ? '<p class="text-muted">' + section.props.description + '</p>' : ''}
                    <div class="section-fields" style="min-height: 50px; padding: 10px; border: 1px dashed #dee2e6; border-radius: 4px;">
                        ${section.children && section.children.length > 0 
                            ? section.children.map(f => renderFieldHtml(f)).join('')
                            : '<p class="text-muted text-center mb-0"><small>Drag form fields here</small></p>'}
                    </div>
                </div>
            </div>
        `;
        builderBlocksContainer.append(sectionHtml);
    }
    
    function renderField(field, container) {
        const fieldHtml = renderFieldHtml(field);
        container.append(fieldHtml);
    }
    
    function renderFieldHtml(field) {
        const fieldIcons = {
            text: 'fa-font',
            textarea: 'fa-align-left',
            select: 'fa-list',
            checkbox: 'fa-check-square',
            radio_group: 'fa-dot-circle',
            date: 'fa-calendar-alt',
            number: 'fa-hashtag'
        };
        const icon = fieldIcons[field.type] || 'fa-question';
        
        return `
            <div class="field-block builder-block" data-field-id="${field.id}" id="${field.id}">
                <div class="block-header">
                    <h6><i class="fas ${icon} me-2"></i>${field.props.label || field.props.name || field.type}</h6>
                    <div class="block-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBlock('${field.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock('${field.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content">
                    <small class="text-muted">${field.type.charAt(0).toUpperCase() + field.type.slice(1)}</small>
                    ${field.props.required ? '<span class="badge bg-danger ms-2">Required</span>' : ''}
                </div>
            </div>
        `;
    }
    
    function getBlockHtml(block) {
        const blockIcons = {
            heading: 'fa-heading',
            paragraph: 'fa-paragraph',
            patient_field: 'fa-user',
            doctor_field: 'fa-user-md',
            date_block: 'fa-calendar',
            divider: 'fa-minus',
            logo_block: 'fa-image',
            signature_block: 'fa-signature',
            text_placeholder: 'fa-tag'
        };
        const icon = blockIcons[block.type] || 'fa-question';
        
        let content = '';
        if (block.type === 'heading') {
            content = `<${block.props.level || 'h2'}>${block.props.text || 'Heading'}</${block.props.level || 'h2'}>`;
        } else if (block.type === 'paragraph') {
            content = `<div class="quill-editor" id="quill_${block.id}">${block.props.html || ''}</div>`;
        } else if (block.type === 'patient_field') {
            content = `<p><strong>Patient:</strong> [${block.props.field || 'field'}]</p>`;
        } else if (block.type === 'doctor_field') {
            content = `<p><strong>Doctor:</strong> [${block.props.field || 'field'}]</p>`;
        } else if (block.type === 'date_block') {
            content = `<p>[Date: ${block.props.format || 'M d, Y'}]</p>`;
        } else if (block.type === 'divider') {
            content = '<hr>';
        } else if (block.type === 'logo_block') {
            content = '<p>[Logo]</p>';
        } else if (block.type === 'signature_block') {
            content = '<p>[Signature]</p>';
        } else if (block.type === 'text_placeholder') {
            content = `<p><strong>${block.props.label || 'Placeholder'}:</strong> ${block.props.value || '[Value]'}</p>`;
        }
        
        return `
            <div class="builder-block" data-block-id="${block.id}" id="${block.id}">
                <div class="block-header">
                    <h6><i class="fas ${icon} me-2"></i>${block.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                    <div class="block-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editBlock('${block.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBlock('${block.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="block-content">${content}</div>
            </div>
        `;
    }
    
    function initQuillEditor(blockId) {
        const editorElement = document.getElementById(`quill_${blockId}`);
        if (!editorElement || quillEditors[blockId]) return;
        
        const block = builderBlocks.find(b => b.id === blockId);
        if (!block) return;
        
        const quill = new Quill(editorElement, {
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
        if (block.props.html) {
            quill.root.innerHTML = block.props.html;
        }
        
        quillEditors[blockId] = quill;
        
        quill.on('text-change', function() {
            const block = builderBlocks.find(b => b.id === blockId);
            if (block) {
                block.props.html = quill.root.innerHTML;
                updateBuilderConfig();
                updatePreview();
            }
        });
    }
    
    function updateBuilderConfig() {
        const config = [];
        
        builderBlocks.forEach(block => {
            const blockData = {
                type: block.type,
                props: block.props,
                children: block.children || []
            };
            config.push(blockData);
        });
        
        $('#builderConfig').val(JSON.stringify(config));
    }
    
    function updatePreview() {
        const type = typeSelect.val();
        if (type !== 'letter') {
            previewPanel.html('<p class="text-muted">Form preview coming soon...</p>');
            return;
        }
        
        let previewHtml = '<div style="font-family: Times New Roman, serif; padding: 20px;">';
        
        builderBlocks.forEach(block => {
            if (block.type === 'heading') {
                previewHtml += `<${block.props.level || 'h2'}>${block.props.text || 'Heading'}</${block.props.level || 'h2'}>`;
            } else if (block.type === 'paragraph') {
                previewHtml += block.props.html || '<p>Paragraph text...</p>';
            } else if (block.type === 'patient_field') {
                previewHtml += `<p><strong>Patient ${block.props.field}:</strong> [Sample Value]</p>`;
            } else if (block.type === 'doctor_field') {
                previewHtml += `<p><strong>Doctor ${block.props.field}:</strong> [Sample Value]</p>`;
            } else if (block.type === 'date_block') {
                previewHtml += `<p>${new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</p>`;
            } else if (block.type === 'divider') {
                previewHtml += '<hr>';
            } else if (block.type === 'logo_block') {
                previewHtml += '<p style="text-align: center;"><strong>[LOGO]</strong></p>';
            } else if (block.type === 'signature_block') {
                previewHtml += '<p><strong>Signature:</strong> [Doctor Name]</p>';
            } else if (block.type === 'text_placeholder') {
                previewHtml += `<p><strong>${block.props.label}:</strong> ${block.props.value || '[Value]'}</p>`;
            }
        });
        
        previewHtml += '</div>';
        previewPanel.html(previewHtml);
    }
    
    // Preview with sample patient
    $('#previewWithPatientBtn').on('click', function() {
        const type = typeSelect.val();
        if (type !== 'letter') {
            alert('Patient preview is only available for letter templates.');
            return;
        }
        
        // Make AJAX call to preview with sample patient
        $.ajax({
            url: '{{ route("staff.document-templates.show", $documentTemplate) }}',
            method: 'GET',
            data: {
                preview: true,
                builder_config: JSON.stringify(builderBlocks)
            },
            success: function(response) {
                // This would show a modal with the preview
                // For now, we'll update the preview panel
                alert('Full patient preview would be shown here with actual data rendering.');
            }
        });
    });
    
    window.editBlock = function(blockId) {
        const block = builderBlocks.find(b => b.id === blockId) || 
                     builderBlocks.find(b => b.children && b.children.some(c => c.id === blockId))?.children.find(c => c.id === blockId);
        if (!block) return;
        
        currentEditingBlock = block;
        const configHtml = getBlockConfigHtml(block);
        
        $('#blockConfigModalTitle').text(`Configure ${block.type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}`);
        $('#blockConfigModalBody').html(configHtml);
        $('#blockConfigModal').modal('show');
    };
    
    window.removeBlock = function(blockId) {
        if (!confirm('Are you sure you want to remove this block?')) return;
        
        // Check if it's a section's child
        let removed = false;
        builderBlocks.forEach(section => {
            if (section.type === 'section' && section.children) {
                const childIndex = section.children.findIndex(c => c.id === blockId);
                if (childIndex !== -1) {
                    section.children.splice(childIndex, 1);
                    removed = true;
                }
            }
        });
        
        if (!removed) {
            builderBlocks = builderBlocks.filter(b => b.id !== blockId);
        }
        
        $(`#${blockId}`).remove();
        
        if (quillEditors[blockId]) {
            delete quillEditors[blockId];
        }
        
        updateBuilderConfig();
        updatePreview();
    };
    
    function getBlockConfigHtml(block) {
        let html = '';
        
        switch(block.type) {
            case 'heading':
                html = `
                    <div class="mb-3">
                        <label class="form-label">Heading Level</label>
                        <select class="form-control" id="config_level">
                            <option value="h1" ${block.props.level === 'h1' ? 'selected' : ''}>H1</option>
                            <option value="h2" ${block.props.level === 'h2' ? 'selected' : ''}>H2</option>
                            <option value="h3" ${block.props.level === 'h3' ? 'selected' : ''}>H3</option>
                            <option value="h4" ${block.props.level === 'h4' ? 'selected' : ''}>H4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Heading Text</label>
                        <input type="text" class="form-control" id="config_text" value="${block.props.text || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alignment</label>
                        <select class="form-control" id="config_align">
                            <option value="left" ${block.props.align === 'left' ? 'selected' : ''}>Left</option>
                            <option value="center" ${block.props.align === 'center' ? 'selected' : ''}>Center</option>
                            <option value="right" ${block.props.align === 'right' ? 'selected' : ''}>Right</option>
                        </select>
                    </div>
                `;
                break;
            case 'patient_field':
                html = `
                    <div class="mb-3">
                        <label class="form-label">Patient Field</label>
                        <select class="form-control" id="config_field">
                            <option value="full_name" ${block.props.field === 'full_name' ? 'selected' : ''}>Full Name</option>
                            <option value="patient_id" ${block.props.field === 'patient_id' ? 'selected' : ''}>Patient ID</option>
                            <option value="date_of_birth" ${block.props.field === 'date_of_birth' ? 'selected' : ''}>Date of Birth</option>
                            <option value="age" ${block.props.field === 'age' ? 'selected' : ''}>Age</option>
                            <option value="gender" ${block.props.field === 'gender' ? 'selected' : ''}>Gender</option>
                            <option value="email" ${block.props.field === 'email' ? 'selected' : ''}>Email</option>
                            <option value="phone" ${block.props.field === 'phone' ? 'selected' : ''}>Phone</option>
                            <option value="address" ${block.props.field === 'address' ? 'selected' : ''}>Address</option>
                        </select>
                    </div>
                `;
                break;
            case 'section':
                html = `
                    <div class="mb-3">
                        <label class="form-label">Section Title</label>
                        <input type="text" class="form-control" id="config_title" value="${block.props.title || ''}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section Description</label>
                        <textarea class="form-control" id="config_description" rows="3">${block.props.description || ''}</textarea>
                    </div>
                `;
                break;
            case 'text':
            case 'textarea':
            case 'select':
            case 'checkbox':
            case 'radio_group':
            case 'date':
            case 'number':
                html = `
                    <div class="mb-3">
                        <label class="form-label">Field Name</label>
                        <input type="text" class="form-control" id="config_name" value="${block.props.name || ''}" placeholder="e.g., patient_complaint">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Field Label</label>
                        <input type="text" class="form-control" id="config_label" value="${block.props.label || ''}" placeholder="e.g., Patient Complaint">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="config_required" ${block.props.required ? 'checked' : ''}>
                            <label class="form-check-label" for="config_required">Required</label>
                        </div>
                    </div>
                `;
                if (block.type === 'select' || block.type === 'radio_group') {
                    html += `
                        <div class="mb-3">
                            <label class="form-label">Options (one per line)</label>
                            <textarea class="form-control" id="config_options" rows="4">${(block.props.options || []).join('\n')}</textarea>
                        </div>
                    `;
                }
                if (block.type === 'text' || block.type === 'textarea') {
                    html += `
                        <div class="mb-3">
                            <label class="form-label">Placeholder</label>
                            <input type="text" class="form-control" id="config_placeholder" value="${block.props.placeholder || ''}">
                        </div>
                    `;
                }
                break;
            default:
                html = '<p class="text-muted">No configuration options available for this block type.</p>';
        }
        
        return html;
    }
    
    $('#saveBlockConfig').on('click', function() {
        if (!currentEditingBlock) return;
        
        const block = currentEditingBlock;
        
        switch(block.type) {
            case 'heading':
                block.props.level = $('#config_level').val();
                block.props.text = $('#config_text').val();
                block.props.align = $('#config_align').val();
                break;
            case 'patient_field':
            case 'doctor_field':
                block.props.field = $('#config_field').val();
                break;
            case 'section':
                block.props.title = $('#config_title').val();
                block.props.description = $('#config_description').val();
                break;
            case 'text':
            case 'textarea':
            case 'select':
            case 'checkbox':
            case 'radio_group':
            case 'date':
            case 'number':
                block.props.name = $('#config_name').val();
                block.props.label = $('#config_label').val();
                block.props.required = $('#config_required').is(':checked');
                if ($('#config_options').length) {
                    block.props.options = $('#config_options').val().split('\n').filter(o => o.trim());
                }
                if ($('#config_placeholder').length) {
                    block.props.placeholder = $('#config_placeholder').val();
                }
                break;
        }
        
        // Re-render block
        const blockElement = $(`#${block.id}`);
        if (block.type === 'section') {
            blockElement.replaceWith(renderSection(block));
        } else if (builderBlocks.find(b => b.children && b.children.some(c => c.id === block.id))) {
            // It's a field in a section
            const section = builderBlocks.find(b => b.children && b.children.some(c => c.id === block.id));
            const sectionEl = $(`#${section.id}`);
            blockElement.replaceWith(renderFieldHtml(block));
        } else {
            blockElement.replaceWith(getBlockHtml(block));
            if (block.type === 'paragraph') {
                setTimeout(() => {
                    initQuillEditor(block.id);
                }, 100);
            }
        }
        
        updateBuilderConfig();
        updatePreview();
        $('#blockConfigModal').modal('hide');
    });
    
    $('#clearCanvasBtn').on('click', function() {
        if (!confirm('Are you sure you want to clear all blocks?')) return;
        builderBlocks = [];
        builderBlocksContainer.empty();
        quillEditors = {};
        updateBuilderConfig();
        updatePreview();
    });
    
    $('#previewBtn').on('click', function() {
        updatePreview();
    });
    
    // Form submission
    $('#templateForm').on('submit', function(e) {
        // Save Quill content
        Object.keys(quillEditors).forEach(blockId => {
            const block = builderBlocks.find(b => b.id === blockId);
            if (block && quillEditors[blockId]) {
                block.props.html = quillEditors[blockId].root.innerHTML;
            }
        });
        
        updateBuilderConfig();
    });
    
    // Initial preview
    updatePreview();
});
</script>
@endpush

