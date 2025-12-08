@extends('admin.layouts.app')

@section('title', 'Edit Template - ' . $template->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.templates.show', $template) }}">{{ $template->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<style>
    .editor-container {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        overflow: hidden;
    }
    .editor-toolbar {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    .editor-toolbar .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .editor-content {
        min-height: 400px;
        padding: 1rem;
    }
    .editor-content:focus {
        outline: none;
    }
    .placeholder-list {
        max-height: 300px;
        overflow-y: auto;
    }
    .placeholder-item {
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 0.25rem;
        transition: background-color 0.15s;
    }
    .placeholder-item:hover {
        background-color: #e9ecef;
    }
    .placeholder-item code {
        font-size: 0.875rem;
        color: #667eea;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.templates.update', $template) }}" method="POST" id="templateForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" value="{{ $template->type }}">

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas {{ $template->type_icon }} me-2 text-primary"></i>
                            Edit {{ ucfirst($template->type) }} Template
                        </h5>
                        <span class="badge {{ $template->type_badge_class }}">{{ ucfirst($template->type) }}</span>
                    </div>
                    <div class="card-body">
                        <!-- Template Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $template->name) }}"
                                   placeholder="e.g., Referral Letter, Consent Form" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="2"
                                      placeholder="Brief description of when to use this template">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content Editor -->
                        <div class="mb-3">
                            <label class="form-label">Template Content <span class="text-danger">*</span></label>
                            <div class="editor-container">
                                <div class="editor-toolbar">
                                    <div class="btn-group me-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('bold')" title="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('italic')" title="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('underline')" title="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group me-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('justifyLeft')" title="Align Left">
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('justifyCenter')" title="Align Center">
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('justifyRight')" title="Align Right">
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group me-2">
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('insertUnorderedList')" title="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="formatDoc('insertOrderedList')" title="Numbered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group me-2">
                                        <select class="form-select form-select-sm" onchange="formatBlock(this.value); this.value='';" style="width: auto;">
                                            <option value="">Heading</option>
                                            <option value="h1">Heading 1</option>
                                            <option value="h2">Heading 2</option>
                                            <option value="h3">Heading 3</option>
                                            <option value="p">Paragraph</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" onclick="insertSignatureLine()" title="Insert Signature Line">
                                        <i class="fas fa-signature me-1"></i>Signature
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="insertDateLine()" title="Insert Date Line">
                                        <i class="fas fa-calendar me-1"></i>Date
                                    </button>
                                </div>
                                <div class="editor-content" contenteditable="true" id="editor">
                                    {!! old('content', $template->content) !!}
                                </div>
                            </div>
                            <input type="hidden" name="content" id="contentInput">
                            @error('content')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Use placeholders like <code>@{{patient_name}}</code> to insert dynamic data
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Available Placeholders -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-code me-2 text-primary"></i>Available Placeholders
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="placeholder-list">
                            @foreach($placeholders as $placeholder => $description)
                                <div class="placeholder-item border-bottom" onclick="insertPlaceholder('{{ $placeholder }}')">
                                    <code>{{ $placeholder }}</code>
                                    <small class="text-muted d-block">{{ $description }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cog me-2 text-primary"></i>Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                                <small class="text-muted d-block">Template can be used to generate documents</small>
                            </label>
                        </div>

                        @if(auth()->user()->is_admin || auth()->user()->role === 'admin')
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_system" name="is_system" value="1"
                                       {{ old('is_system', $template->is_system) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_system">
                                    System Template
                                    <small class="text-muted d-block">Visible to all doctors</small>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Template Info
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Created:</strong> {{ $template->created_at->format('M d, Y H:i') }}<br>
                            <strong>By:</strong> {{ $template->creator->name ?? 'Unknown' }}<br>
                            @if($template->updated_by)
                                <strong>Last Updated:</strong> {{ $template->updated_at->format('M d, Y H:i') }}<br>
                            @endif
                            <strong>Usage Count:</strong> {{ $template->usage_count }}
                        </small>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
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
    const editor = document.getElementById('editor');
    const contentInput = document.getElementById('contentInput');
    const templateForm = document.getElementById('templateForm');

    // Sync editor content to hidden input before submit
    templateForm.addEventListener('submit', function(e) {
        contentInput.value = editor.innerHTML;
    });

    function formatDoc(cmd, value = null) {
        document.execCommand(cmd, false, value);
        editor.focus();
    }

    function formatBlock(block) {
        if (block) {
            document.execCommand('formatBlock', false, block);
            editor.focus();
        }
    }

    function insertPlaceholder(placeholder) {
        editor.focus();
        document.execCommand('insertText', false, placeholder);
    }

    function insertSignatureLine() {
        const html = `
            <div class="signature-block" style="margin-top: 40px;">
                <div style="border-top: 1px solid #333; width: 250px; padding-top: 5px;">
                    <strong>Signature:</strong> _______________________
                </div>
                <div style="margin-top: 10px;">
                    <strong>Name:</strong> {{doctor_name}}
                </div>
                <div>
                    <strong>Date:</strong> {{current_date}}
                </div>
            </div>
        `;
        document.execCommand('insertHTML', false, html);
        editor.focus();
    }

    function insertDateLine() {
        document.execCommand('insertText', false, '{{current_date}}');
        editor.focus();
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        contentInput.value = editor.innerHTML;

        editor.addEventListener('input', function() {
            contentInput.value = editor.innerHTML;
        });
    });
</script>
@endpush
